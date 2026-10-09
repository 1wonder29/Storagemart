<?php
require_once __DIR__ . '/../AuthController.php';
require_once __DIR__ . '/../../Helpers/Session.php';
require_once __DIR__ . '/../../Helpers/ActivityLogger.php';
require_once __DIR__ . '/../../Helpers/AppSettings.php';
require_once __DIR__ . '/../../Helpers/TicketAssigner.php';
require_once __DIR__ . '/../../Helpers/TicketManuals.php';
require_once __DIR__ . '/../../Helpers/TicketFormFields.php';
require_once __DIR__ . '/../../Helpers/SeasonalThemes.php';

/**
 * Admin pages added with the Oct 2026 update: ticket auto-assignment (zones, IT
 * availability), plus the IT "I'm away" switch used by the assignment rules.
 */
class SystemController extends AuthController
{
    /** GET shows / POST saves Admin > Ticket Auto-Assignment. */
    public function ticketAssignment(): void
    {
        $this->requireSystemAdmin();
        global $pdo;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->requireCsrf('/admin/ticket-assignment');

            AppSettings::set('auto_assign_enabled', !empty($_POST['auto_assign_enabled']) ? '1' : '0');
            $minutes = max(5, min(480, (int) ($_POST['reassign_after_minutes'] ?? 30)));
            AppSettings::set('reassign_after_minutes', (string) $minutes);

            try {
                $pdo->beginTransaction();
                $zoneStmt = $pdo->prepare('INSERT INTO tblit_zones (zone, label, it_employee_id, updated_at)
                        VALUES (:zone, :label, :it, NOW())
                        ON DUPLICATE KEY UPDATE label = VALUES(label), it_employee_id = VALUES(it_employee_id), updated_at = NOW()');
                foreach ((array) ($_POST['zone_it'] ?? []) as $zone => $itEmployeeId) {
                    $zone = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', (string) $zone), 0, 10));
                    if ($zone === '') {
                        continue;
                    }
                    $zoneStmt->execute([':zone' => $zone, ':label' => 'Zone ' . $zone, ':it' => (int) $itEmployeeId ?: null]);
                }
                $newZone = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', (string) ($_POST['new_zone'] ?? '')), 0, 10));
                if ($newZone !== '') {
                    $zoneStmt->execute([':zone' => $newZone, ':label' => 'Zone ' . $newZone, ':it' => (int) ($_POST['new_zone_it'] ?? 0) ?: null]);
                }

                $branchStmt = $pdo->prepare('UPDATE tblbranch SET zone = :zone WHERE branch_id = :branch_id');
                foreach ((array) ($_POST['branch_zone'] ?? []) as $branchId => $zone) {
                    $zone = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', (string) $zone), 0, 10));
                    $branchStmt->execute([':zone' => $zone !== '' ? $zone : null, ':branch_id' => (int) $branchId]);
                }

                $awayStmt = $pdo->prepare('INSERT INTO tblit_availability (employee_id, is_away, updated_at)
                        VALUES (:employee_id, :away, NOW())
                        ON DUPLICATE KEY UPDATE is_away = VALUES(is_away), updated_at = NOW()');
                foreach ((array) ($_POST['it_staff'] ?? []) as $employeeId) {
                    $awayStmt->execute([
                        ':employee_id' => (int) $employeeId,
                        ':away' => !empty($_POST['it_away'][$employeeId]) ? 1 : 0,
                    ]);
                }
                $pdo->commit();
                ActivityLogger::update('Admin - Tickets', 'auto-assignment', 'Ticket auto-assignment settings saved',
                    $_SESSION['username'] ?? 'Unknown', ['reassign_after_minutes' => $minutes]);
                $_SESSION['flash_success'] = 'Ticket auto-assignment settings saved.';
            } catch (Throwable $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                error_log('SystemController::ticketAssignment save failed: ' . $e->getMessage());
                $_SESSION['flash_error'] = 'Could not save the settings. Please run the database update (G2) first.';
            }
            $this->redirect('/admin/ticket-assignment');
            return;
        }

        $zones = $branches = $staff = [];
        $setupMissing = false;
        try {
            $zones = $pdo->query("SELECT z.zone, z.label, z.it_employee_id
                    FROM tblit_zones z ORDER BY z.zone")->fetchAll(PDO::FETCH_ASSOC);
            $branches = $pdo->query("SELECT b.branch_id, b.branchCode, b.branchName, b.zone,
                        (SELECT COUNT(*) FROM tblemployee e WHERE e.branch_id = b.branch_id) AS staff_count
                    FROM tblbranch b ORDER BY b.branchName")->fetchAll(PDO::FETCH_ASSOC);
            $staff = $pdo->query("SELECT e.employee_id, CONCAT(e.firstname, ' ', e.lastname) AS name,
                        COALESCE(av.is_away, 0) AS is_away, av.updated_at AS away_updated,
                        (SELECT COUNT(*) FROM tbltickets t WHERE t.assigned_to = e.employee_id
                           AND t.status IN ('In Progress', 'Pending', 'On Hold')) AS active_load
                    FROM tblaccounts a
                    JOIN tblemployee e ON e.account_id = a.account_id
                    LEFT JOIN tblit_availability av ON av.employee_id = e.employee_id
                    WHERE a.usertype = 'IT' AND UPPER(a.status) = 'ACTIVE'
                    ORDER BY e.firstname")->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            $setupMissing = true;
        }
        $autoAssignEnabled = AppSettings::get('auto_assign_enabled', '1') === '1';
        $reassignMinutes = AppSettings::getInt('reassign_after_minutes', 30);

        $this->renderAdmin('admin/system/ticket_assignment.php', compact(
            'zones', 'branches', 'staff', 'setupMissing', 'autoAssignEnabled', 'reassignMinutes'
        ));
    }

    /** POST /it/availability — IT marks themselves away / available. */
    public function itAvailability(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION['account_id']) || strtoupper((string) ($_SESSION['usertype'] ?? '')) !== 'IT'
            || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/login');
            return;
        }
        $this->requireCsrf('/it/dashboard');
        global $pdo;
        try {
            $stmt = $pdo->prepare('SELECT employee_id FROM tblemployee WHERE account_id = ? LIMIT 1');
            $stmt->execute([(int) $_SESSION['account_id']]);
            $employeeId = (int) $stmt->fetchColumn();
            $away = !empty($_POST['away']) ? 1 : 0;
            $pdo->prepare('INSERT INTO tblit_availability (employee_id, is_away, away_note, updated_at)
                    VALUES (:employee_id, :away, :note, NOW())
                    ON DUPLICATE KEY UPDATE is_away = VALUES(is_away), away_note = VALUES(away_note), updated_at = NOW()')
                ->execute([':employee_id' => $employeeId, ':away' => $away, ':note' => substr(trim((string) ($_POST['note'] ?? '')), 0, 150)]);
            $_SESSION['flash_success'] = $away
                ? 'You are marked as away. New tickets go to other IT staff, and tickets you have not opened will be moved.'
                : 'Welcome back! You will receive new tickets again.';
            if ($away) {
                AppSettings::set('reassign_last_run', '0');
                TicketAssigner::reassignStale($pdo);
            }
        } catch (Throwable $e) {
            error_log('SystemController::itAvailability failed: ' . $e->getMessage());
            $_SESSION['flash_error'] = 'Could not update your availability.';
        }
        $this->redirect('/it/dashboard');
    }

    /**
     * Admin > System > Troubleshooting Manuals, and IT > Manuals (IT adds and edits steps too).
     * GET lists / edits; POST action=save|delete.
     */
    public function manuals(): void
    {
        $role = $this->requireRoles(['ADMIN', 'IT']);
        if ($role === 'ADMIN') {
            $this->requireSystemAdmin();
        }
        $home = $role === 'IT' ? '/it/manuals' : '/admin/manuals';
        global $pdo;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->requireCsrf($home);
            $action = (string) ($_POST['action'] ?? 'save');
            $manualId = (int) ($_POST['manual_id'] ?? 0);
            try {
                if ($action === 'delete') {
                    $stmt = $pdo->prepare('SELECT * FROM tblit_manuals WHERE manual_id = ?');
                    $stmt->execute([$manualId]);
                    $row = $stmt->fetch(PDO::FETCH_ASSOC);
                    if (!$row) {
                        $_SESSION['flash_error'] = 'Manual not found.';
                    } elseif ((int) $row['is_default'] === 1) {
                        $_SESSION['flash_error'] = 'The default manual cannot be deleted. Make another manual the default first.';
                    } else {
                        $pdo->prepare('DELETE FROM tblit_manuals WHERE manual_id = ?')->execute([$manualId]);
                        $oldFile = !empty($row['file_path']) ? TicketManuals::filePath((string) $row['file_path']) : null;
                        if ($oldFile !== null) {
                            @unlink($oldFile);
                        }
                        foreach (ManualPresentation::stepPhotos($row) as $photo) {
                            if (($path = TicketManuals::stepPhotoPath($photo)) !== null) {
                                @unlink($path);
                            }
                        }
                        ActivityLogger::delete('Troubleshooting Manuals', (string) $manualId, 'Deleted manual: ' . $row['title'],
                            $_SESSION['username'] ?? 'Unknown');
                        $_SESSION['flash_success'] = 'Manual deleted.';
                    }
                    $this->redirect($home);
                    return;
                }

                $text = static function (string $name, int $max = 20000): string {
                    $value = trim(str_replace("\r\n", "\n", (string) ($_POST[$name] ?? '')));
                    return mb_substr($value, 0, $max);
                };
                $title = trim((string) ($_POST['title'] ?? ''));
                $url = trim((string) ($_POST['url'] ?? ''));
                $steps = $text('steps');
                $intro = $text('intro');
                $mainIssue = $text('main_issue');
                $factors = $text('factors');
                $actionText = $text('action_text');
                $solution = $text('solution');
                $note = $text('note');
                $group = mb_substr(trim((string) ($_POST['manual_group'] ?? '')), 0, 100);
                $itOnly = !empty($_POST['it_only']) ? 1 : 0;
                $categories = array_values(array_intersect(
                    (array) ($_POST['categories'] ?? []),
                    array_merge(TicketFormFields::HEAD_OFFICE_CATEGORIES, TicketFormFields::BRANCH_CATEGORIES)
                ));
                $keywords = implode(', ', array_slice(array_filter(array_map('trim',
                    preg_split('/[,\n;]+/', strtolower((string) ($_POST['keywords'] ?? ''))) ?: []), 'strlen'), 0, 40));
                $description = trim((string) ($_POST['description'] ?? ''));
                if ($description === '') {
                    $description = ManualPresentation::plain($mainIssue !== '' ? $mainIssue : $intro);
                }
                if (mb_strlen($description) > 255) {
                    $description = mb_substr($description, 0, 252) . '...';
                }
                $makeDefault = $role === 'ADMIN' && !empty($_POST['is_default']);

                $errors = [];
                if ($title === '' || mb_strlen($title) > 150) {
                    $errors[] = 'Title is required (max 150 characters).';
                }
                if ($url !== '' && (!filter_var($url, FILTER_VALIDATE_URL) || !preg_match('#^https?://#i', $url))) {
                    $errors[] = 'The link must start with http:// or https://.';
                }
                $upload = $_FILES['manual_file'] ?? null;
                $hasUpload = $upload && ($upload['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;
                $hadFile = false;
                if ($manualId > 0) {
                    $stmt = $pdo->prepare('SELECT file_path FROM tblit_manuals WHERE manual_id = ?');
                    $stmt->execute([$manualId]);
                    $hadFile = (string) $stmt->fetchColumn() !== '' && empty($_POST['remove_file']);
                }
                if ($url === '' && $steps === '' && $intro === '' && $mainIssue === '' && $solution === '' && !$hasUpload && !$hadFile) {
                    $errors[] = 'Add the steps or the other slide texts (slides are made from them), upload a PowerPoint / PDF, or both.';
                }
                if ($errors) {
                    $_SESSION['flash_error'] = implode(' ', $errors);
                    $_SESSION['manual_old_input'] = $_POST;
                    $this->redirect($home . ($manualId ? '?edit=' . $manualId : ''));
                    return;
                }

                $null = static function (string $value): ?string {
                    return $value !== '' ? $value : null;
                };
                $params = [
                    ':title' => $title,
                    ':url' => $url !== '' ? mb_substr($url, 0, 500) : null,
                    ':description' => $null($description),
                    ':steps' => $null($steps),
                    ':categories' => implode(', ', $categories),
                    ':keywords' => $keywords,
                    ':intro' => $null($intro),
                    ':main_issue' => $null($mainIssue),
                    ':factors' => $null($factors),
                    ':action_text' => $null($actionText),
                    ':solution' => $null($solution),
                    ':note' => $null($note),
                    ':manual_group' => $null($group),
                    ':it_only' => $makeDefault ? 0 : $itOnly,
                    ':by' => (int) $_SESSION['account_id'],
                ];
                $pdo->beginTransaction();
                if ($manualId > 0) {
                    $params[':id'] = $manualId;
                    $pdo->prepare('UPDATE tblit_manuals SET title = :title, url = :url, description = :description, steps = :steps,
                            categories = :categories, keywords = :keywords, intro = :intro, main_issue = :main_issue, factors = :factors,
                            action_text = :action_text, solution = :solution, note = :note, manual_group = :manual_group, it_only = :it_only,
                            updated_by = :by, updated_at = NOW()
                            WHERE manual_id = :id')->execute($params);
                } else {
                    $pdo->prepare('INSERT INTO tblit_manuals (title, url, description, steps, categories, keywords, intro, main_issue, factors,
                                action_text, solution, note, manual_group, it_only, is_default, created_by, created_at)
                            VALUES (:title, :url, :description, :steps, :categories, :keywords, :intro, :main_issue, :factors,
                                :action_text, :solution, :note, :manual_group, :it_only, 0, :by, NOW())')->execute($params);
                    $manualId = (int) $pdo->lastInsertId();
                }
                if ($makeDefault) {
                    $pdo->prepare('UPDATE tblit_manuals SET is_default = (manual_id = ?)')->execute([$manualId]);
                    if ($url !== '') {
                        AppSettings::set('it_manual_url', $url);
                    }
                }
                $pdo->commit();

                $current = $pdo->prepare('SELECT file_path, step_photos FROM tblit_manuals WHERE manual_id = ?');
                $current->execute([$manualId]);
                $currentRow = $current->fetch(PDO::FETCH_ASSOC) ?: [];

                // Uploaded PowerPoint / PDF (replaces the previous one), or remove it.
                $fileNote = '';
                $oldFile = (string) ($currentRow['file_path'] ?? '');
                if ($hasUpload) {
                    try {
                        $stored = TicketManuals::storeFile($upload, $manualId);
                        $pdo->prepare('UPDATE tblit_manuals SET file_path = ?, file_name = ?, file_mime = ?, file_size = ? WHERE manual_id = ?')
                            ->execute([$stored['path'], $stored['name'], $stored['mime'], $stored['size'], $manualId]);
                        if ($oldFile !== '' && ($old = TicketManuals::filePath($oldFile)) !== null) {
                            @unlink($old);
                        }
                        $fileNote = ' File uploaded.';
                    } catch (RuntimeException $e) {
                        $fileNote = ' The file was not uploaded: ' . $e->getMessage();
                    }
                } elseif (!empty($_POST['remove_file']) && $oldFile !== '') {
                    $pdo->prepare('UPDATE tblit_manuals SET file_path = NULL, file_name = NULL, file_mime = NULL, file_size = NULL WHERE manual_id = ?')
                        ->execute([$manualId]);
                    if (($old = TicketManuals::filePath($oldFile)) !== null) {
                        @unlink($old);
                    }
                    $fileNote = ' File removed.';
                }

                // Step photos: new uploads replace, ticked ones are removed, photos of steps that no longer exist are dropped.
                $photos = ManualPresentation::stepPhotos($currentRow);
                $before = $photos;
                $stepCount = count(ManualPresentation::stepTexts($steps));
                $drop = static function (int $index) use (&$photos): void {
                    if (isset($photos[$index]) && ($path = TicketManuals::stepPhotoPath($photos[$index])) !== null) {
                        @unlink($path);
                    }
                    unset($photos[$index]);
                };
                foreach (array_keys((array) ($_POST['remove_step_photo'] ?? [])) as $index) {
                    $drop((int) $index);
                }
                $photoErrors = [];
                $uploads = $_FILES['step_photo'] ?? null;
                if ($uploads && is_array($uploads['name'] ?? null)) {
                    foreach ($uploads['name'] as $index => $name) {
                        $index = (int) $index;
                        if (($uploads['error'][$index] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE || $index < 1 || $index > $stepCount) {
                            continue;
                        }
                        try {
                            $stored = TicketManuals::storeStepPhoto([
                                'name' => $name,
                                'tmp_name' => $uploads['tmp_name'][$index] ?? '',
                                'error' => $uploads['error'][$index] ?? UPLOAD_ERR_NO_FILE,
                                'size' => $uploads['size'][$index] ?? 0,
                            ], $manualId, $index);
                            $drop($index);
                            $photos[$index] = $stored;
                        } catch (RuntimeException $e) {
                            $photoErrors[] = $e->getMessage();
                        }
                    }
                }
                foreach (array_keys($photos) as $index) {
                    if ($index > $stepCount) {
                        $drop($index);
                    }
                }
                if ($photos !== $before) {
                    ksort($photos);
                    $pdo->prepare('UPDATE tblit_manuals SET step_photos = ? WHERE manual_id = ?')
                        ->execute([$photos ? json_encode($photos) : null, $manualId]);
                }
                if ($photoErrors) {
                    $fileNote .= ' ' . implode(' ', $photoErrors);
                }

                ActivityLogger::update('Troubleshooting Manuals', (string) $manualId, 'Saved manual: ' . $title,
                    $_SESSION['username'] ?? 'Unknown');
                $_SESSION['flash_success'] = 'Guide saved. Its slides and PowerPoint use the IT manual design.' . $fileNote;
                $this->redirect($home . '?edit=' . $manualId . '#manual-form');
                return;
            } catch (PDOException $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                error_log('SystemController::manuals save failed: ' . $e->getMessage());
                $_SESSION['flash_error'] = $e->getCode() === '23000'
                    ? 'A manual with that title already exists.'
                    : 'Could not save the manual. Please run the database update first.';
                $_SESSION['manual_old_input'] = $_POST;
            }
            $this->redirect($home);
            return;
        }

        $manuals = TicketManuals::all($pdo);
        $setupMissing = !$manuals && !$this->tableExists('tblit_manuals');
        $editId = (int) ($_GET['edit'] ?? 0);
        $editing = null;
        foreach ($manuals as $manual) {
            if ((int) $manual['manual_id'] === $editId) {
                $editing = $manual;
            }
        }
        $old = $_SESSION['manual_old_input'] ?? null;
        unset($_SESSION['manual_old_input']);

        $testCategory = (string) ($_GET['test_category'] ?? '');
        $testText = (string) ($_GET['test_text'] ?? '');
        $testResult = ($testCategory !== '' || $testText !== '')
            ? TicketManuals::matchesFor($pdo, $testCategory, $testText, 3)
            : null;

        $allCategories = array_values(array_unique(array_merge(TicketFormFields::HEAD_OFFICE_CATEGORIES, TicketFormFields::BRANCH_CATEGORIES)));
        $groupNames = TicketManuals::GROUPS;
        foreach ($manuals as $manual) {
            $groupNames[] = TicketManuals::groupOf($manual);
        }
        $groupNames = array_values(array_unique($groupNames));
        $this->renderAdmin('system/manuals.php', compact(
            'role', 'home', 'manuals', 'setupMissing', 'editing', 'old', 'allCategories', 'testCategory', 'testText', 'testResult', 'groupNames'
        ));
    }
    /**
     * Admin > System > Seasonal Themes: preview a theme in your own browser, schedule its
     * release (start / end date and time), turn schedules off or delete them.
     */
    public function themes(): void
    {
        $this->requireSystemAdmin();
        global $pdo;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->requireCsrf('/admin/themes');
            $action = (string) ($_POST['action'] ?? 'schedule');
            $scheduleId = (int) ($_POST['schedule_id'] ?? 0);
            $back = '/admin/themes';
            try {
                if ($action === 'delete') {
                    $pdo->prepare('DELETE FROM tblthemes_schedule WHERE schedule_id = ?')->execute([$scheduleId]);
                    $_SESSION['flash_success'] = 'Schedule deleted.';
                } elseif ($action === 'toggle') {
                    $pdo->prepare('UPDATE tblthemes_schedule SET is_active = 1 - is_active WHERE schedule_id = ?')->execute([$scheduleId]);
                    $_SESSION['flash_success'] = 'Schedule updated.';
                } elseif ($action === 'stop') {
                    $pdo->prepare('UPDATE tblthemes_schedule SET ends_at = NOW() WHERE schedule_id = ? AND ends_at > NOW()')->execute([$scheduleId]);
                    $_SESSION['flash_success'] = 'Theme ended now.';
                } elseif ($action === 'save_theme') {
                    $back = $this->saveTheme($pdo);
                } elseif ($action === 'delete_theme') {
                    $key = (string) ($_POST['theme_key'] ?? '');
                    $theme = SeasonalThemes::get($pdo, $key);
                    if (!$theme) {
                        $_SESSION['flash_error'] = 'Theme not found.';
                    } else {
                        $pdo->prepare('DELETE FROM tblthemes_schedule WHERE theme_key = ?')->execute([$key]);
                        $pdo->prepare('DELETE FROM tblthemes WHERE theme_key = ?')->execute([$key]);
                        ActivityLogger::delete('Admin - Themes', $key, 'Deleted theme ' . $theme['name'], $_SESSION['username'] ?? 'Unknown');
                        $_SESSION['flash_success'] = $theme['name'] . ' theme deleted (with its schedules).';
                    }
                } elseif ($action === 'restore_builtin') {
                    $added = 0;
                    $insert = $pdo->prepare('INSERT IGNORE INTO tblthemes (theme_key, name, icon, items, color_main, color_accent, message,
                            date_from, date_to, effects, is_builtin, sort_order, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, ?, NOW())');
                    $order = 10;
                    foreach (SeasonalThemes::CATALOG as $key => $theme) {
                        $insert->execute([$key, $theme['name'], $theme['icon'], implode(' ', $theme['items']), $theme['colors'][0], $theme['colors'][1],
                            $theme['message'], $theme['from'] ?: null, $theme['to'] ?: null, implode(',', $theme['effects']), $order]);
                        $added += $insert->rowCount();
                        $order += 10;
                    }
                    $_SESSION['flash_success'] = $added ? $added . ' built-in theme(s) restored.' : 'All built-in themes are already in the list.';
                } else {
                    $key = (string) ($_POST['theme_key'] ?? '');
                    $theme = SeasonalThemes::get($pdo, $key);
                    $startsAt = strtotime(str_replace('T', ' ', (string) ($_POST['starts_at'] ?? '')));
                    $endsAt = strtotime(str_replace('T', ' ', (string) ($_POST['ends_at'] ?? '')));
                    $message = trim((string) ($_POST['message'] ?? ''));
                    if ($action === 'now') {
                        $startsAt = time();
                        $endsAt = time() + max(1, min(60, (int) ($_POST['days'] ?? 7))) * 86400;
                    }
                    if (!$theme) {
                        $_SESSION['flash_error'] = 'Choose a theme.';
                    } elseif (!$startsAt || !$endsAt || $endsAt <= $startsAt) {
                        $_SESSION['flash_error'] = 'Set a release date / time and an end after it.';
                    } elseif (mb_strlen($message) > 200) {
                        $_SESSION['flash_error'] = 'The greeting is too long (max 200 characters).';
                    } else {
                        $overlap = $pdo->prepare('SELECT COUNT(*) FROM tblthemes_schedule
                                WHERE is_active = 1 AND starts_at < ? AND ends_at > ?');
                        $overlap->execute([date('Y-m-d H:i:s', $endsAt), date('Y-m-d H:i:s', $startsAt)]);
                        $pdo->prepare('INSERT INTO tblthemes_schedule (theme_key, title, message, starts_at, ends_at, is_active, created_by, created_at)
                                VALUES (?, ?, ?, ?, ?, 1, ?, NOW())')
                            ->execute([$key, $theme['name'], $message !== '' ? $message : null,
                                date('Y-m-d H:i:s', $startsAt), date('Y-m-d H:i:s', $endsAt), (int) $_SESSION['account_id']]);
                        ActivityLogger::update('Admin - Themes', $key, 'Scheduled theme ' . $theme['name'],
                            $_SESSION['username'] ?? 'Unknown', ['from' => date('Y-m-d H:i', $startsAt), 'to' => date('Y-m-d H:i', $endsAt)]);
                        $_SESSION['flash_success'] = $theme['name'] . ' theme scheduled.'
                            . ((int) $overlap->fetchColumn() > 0 ? ' It overlaps another schedule; the one that started last is shown.' : '');
                    }
                }
            } catch (Throwable $e) {
                error_log('SystemController::themes failed: ' . $e->getMessage());
                $_SESSION['flash_error'] = 'Could not save. Please run the database update first.';
            }
            $this->redirect($back);
            return;
        }

        $schedules = [];
        $setupMissing = false;
        try {
            $schedules = $pdo->query('SELECT s.*, (s.is_active = 1 AND s.starts_at <= NOW() AND s.ends_at > NOW()) AS is_live,
                        (s.starts_at > NOW()) AS is_upcoming
                    FROM tblthemes_schedule s ORDER BY s.ends_at < NOW(), s.starts_at')->fetchAll(PDO::FETCH_ASSOC);
            $pdo->query('SELECT 1 FROM tblthemes LIMIT 1');
        } catch (Throwable $e) {
            $setupMissing = true;
        }
        $themes = SeasonalThemes::all($pdo);
        $current = SeasonalThemes::current($pdo);
        $editKey = (string) ($_GET['edit_theme'] ?? '');
        $editTheme = $editKey !== '' ? SeasonalThemes::get($pdo, $editKey) : null;
        $themeOld = $_SESSION['theme_old_input'] ?? null;
        unset($_SESSION['theme_old_input']);
        $this->renderAdmin('admin/system/themes.php', compact('schedules', 'setupMissing', 'current', 'themes', 'editKey', 'editTheme', 'themeOld'));
    }

    /** Add or edit a theme (Admin > Seasonal Themes). Returns the page to go back to. */
    private function saveTheme(PDO $pdo): string
    {
        $key = (string) ($_POST['theme_key'] ?? '');
        $existing = $key !== '' ? SeasonalThemes::get($pdo, $key) : null;
        $name = trim((string) ($_POST['name'] ?? ''));
        $icon = (string) ($_POST['icon'] ?? '');
        $items = array_slice(array_values(array_filter(preg_split('/[\s,]+/u', trim((string) ($_POST['items'] ?? ''))) ?: [], 'strlen')), 0, 6);
        $main = strtolower((string) ($_POST['color_main'] ?? ''));
        $accent = strtolower((string) ($_POST['color_accent'] ?? ''));
        $message = trim((string) ($_POST['message'] ?? ''));
        $effects = array_values(array_intersect(array_keys(SeasonalThemes::EFFECTS), (array) ($_POST['effects'] ?? [])));
        $from = (string) ($_POST['date_from'] ?? '');
        $to = (string) ($_POST['date_to'] ?? '');
        $md = static function (string $value): ?string {
            // <input type="date"> value (any year) -> MM-DD
            return preg_match('/^\d{4}-(\d{2})-(\d{2})$/', $value, $m) ? $m[1] . '-' . $m[2] : null;
        };

        $errors = [];
        if ($name === '' || mb_strlen($name) > 60) {
            $errors[] = 'Give the theme a name (max 60 characters).';
        }
        if (!in_array($icon, SeasonalThemes::ICONS, true)) {
            $icon = 'fa-palette';
        }
        if (!preg_match('/^#[0-9a-f]{6}$/', $main) || !preg_match('/^#[0-9a-f]{6}$/', $accent)) {
            $errors[] = 'Pick the two theme colors.';
        }
        if (mb_strlen(implode(' ', $items)) > 200 || mb_strlen($message) > 200) {
            $errors[] = 'The icons and the greeting can have up to 200 characters each.';
        }
        if (!$effects) {
            $errors[] = 'Tick at least one effect.';
        }
        if (($from === '') !== ($to === '')) {
            $errors[] = 'Set both suggested dates, or leave both empty.';
        }
        if ($key !== '' && !$existing) {
            $errors[] = 'Theme not found.';
        }
        if ($errors) {
            $_SESSION['flash_error'] = implode(' ', $errors);
            $_SESSION['theme_old_input'] = $_POST;
            return '/admin/themes?' . ($key !== '' ? 'edit_theme=' . rawurlencode($key) : 'new_theme=1') . '#theme-form';
        }

        $values = [$name, $icon, implode(' ', $items), $main, $accent, $message !== '' ? $message : null,
            $from !== '' ? $md($from) : null, $to !== '' ? $md($to) : null, implode(',', $effects)];
        if ($existing) {
            $pdo->prepare('UPDATE tblthemes SET name = ?, icon = ?, items = ?, color_main = ?, color_accent = ?, message = ?,
                    date_from = ?, date_to = ?, effects = ?, updated_at = NOW() WHERE theme_key = ?')
                ->execute(array_merge($values, [$key]));
            $pdo->prepare('UPDATE tblthemes_schedule SET title = ? WHERE theme_key = ?')->execute([$name, $key]);
            ActivityLogger::update('Admin - Themes', $key, 'Edited theme ' . $name, $_SESSION['username'] ?? 'Unknown');
            $_SESSION['flash_success'] = $name . ' theme saved.';
        } else {
            $key = SeasonalThemes::newKey($pdo, $name);
            $order = (int) $pdo->query('SELECT COALESCE(MAX(sort_order), 0) + 10 FROM tblthemes')->fetchColumn();
            $pdo->prepare('INSERT INTO tblthemes (theme_key, name, icon, items, color_main, color_accent, message, date_from, date_to,
                    effects, is_builtin, sort_order, created_by, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, ?, ?, NOW())')
                ->execute(array_merge([$key], $values, [$order, (int) $_SESSION['account_id']]));
            ActivityLogger::update('Admin - Themes', $key, 'Added theme ' . $name, $_SESSION['username'] ?? 'Unknown');
            $_SESSION['flash_success'] = $name . ' theme added. Preview it, then schedule its release.';
        }
        SeasonalThemes::forget();
        return '/admin/themes#theme-' . $key;
    }
    /**
     * /admin/assets/replacement and /it/asset-replacement: every asset purchased the set number
     * of years ago or earlier (TV, CCTV, PC set, laptop... all types). Admin can change the years.
     */
    public function assetReplacement(): void
    {
        $role = $this->requireRoles(['ADMIN', 'IT']);
        $home = $role === 'IT' ? '/it/asset-replacement' : '/admin/assets/replacement';
        global $pdo;
        require_once __DIR__ . '/../../Helpers/AssetAge.php';
        require_once __DIR__ . '/../../Models/admin/Asset.php';

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $role === 'ADMIN') {
            $this->requireCsrf($home);
            $years = max(1, min(30, (int) ($_POST['years'] ?? AssetAge::DEFAULT_YEARS)));
            AppSettings::set('asset_replacement_years', (string) $years);
            ActivityLogger::update('Admin - Assets', 'replacement-years', "Replacement age set to {$years} years",
                $_SESSION['username'] ?? 'Unknown');
            $_SESSION['flash_success'] = "Items {$years} or more years old are now tagged for replacement.";
            $this->redirect($home);
            return;
        }

        $items = [];
        try {
            $items = (new Asset())->fetchReplacementDue();
        } catch (Throwable $e) {
            error_log('SystemController::assetReplacement failed: ' . $e->getMessage());
        }
        $years = AssetAge::threshold();
        $this->renderAdmin('system/asset_replacement.php', compact('role', 'home', 'items', 'years'));
    }

    /**
     * /admin/assets/import: upload the IT inventory sheet, preview what changes, then apply.
     * POST step=preview|apply|cancel; GET ?template=1 downloads the template.
     */
    public function assetImport(): void
    {
        $this->requireAdmin();
        global $pdo;
        require_once __DIR__ . '/../../Services/AssetImportService.php';
        require_once __DIR__ . '/../../Models/admin/Asset.php';
        $home = '/admin/assets/import';

        if ($_SERVER['REQUEST_METHOD'] === 'GET' && !empty($_GET['template'])) {
            $csv = AssetImportService::templateCsv();
            header('Content-Type: text/csv; charset=UTF-8');
            header('Content-Disposition: attachment; filename="asset_import_template.csv"');
            echo $csv;
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->requireCsrf($home);
            $step = (string) ($_POST['step'] ?? '');
            if ($step === 'cancel') {
                unset($_SESSION['asset_import']);
            } elseif ($step === 'preview') {
                unset($_SESSION['asset_import']);
                $file = $_FILES['import_file'] ?? null;
                $ext = strtolower(pathinfo((string) ($file['name'] ?? ''), PATHINFO_EXTENSION));
                if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
                    $_SESSION['flash_error'] = 'Please choose an .xlsx or .csv file.';
                } elseif ($file['size'] > 5 * 1024 * 1024) {
                    $_SESSION['flash_error'] = 'The file is larger than 5 MB.';
                } elseif (!in_array($ext, ['xlsx', 'csv', 'txt'], true)) {
                    $_SESSION['flash_error'] = 'Only .xlsx and .csv files can be imported (in Google Sheets: File > Download > .xlsx).';
                } else {
                    try {
                        $result = AssetImportService::parse((string) $file['tmp_name'], (string) $file['name'], $this->assetLookups($pdo));
                        $_SESSION['asset_import'] = [
                            'token' => bin2hex(random_bytes(16)),
                            'filename' => basename((string) $file['name']),
                        ] + $result;
                    } catch (Throwable $e) {
                        $_SESSION['flash_error'] = $e->getMessage();
                    }
                }
            } elseif ($step === 'apply') {
                $import = $_SESSION['asset_import'] ?? null;
                if (!$import || !hash_equals($import['token'], (string) ($_POST['import_token'] ?? ''))) {
                    $_SESSION['flash_error'] = 'This preview has expired. Please upload the file again.';
                } else {
                    $summary = $this->applyAssetImport($pdo, $import['rows']);
                    unset($_SESSION['asset_import']);
                    ActivityLogger::create('Admin - Assets', 'import',
                        "Imported assets from {$import['filename']}: {$summary['created']} new, {$summary['updated']} updated"
                            . ($summary['groups'] ? ", {$summary['groups']} new model(s)" : ''),
                        $_SESSION['username'] ?? 'Unknown', ['failed' => $summary['failed']]);
                    $_SESSION['flash_success'] = "Asset import done: {$summary['created']} new item(s), {$summary['updated']} updated"
                        . ($summary['groups'] ? ", {$summary['groups']} new model(s)" : '')
                        . ($summary['failed'] ? '. ' . count($summary['failed']) . ' row(s) failed: rows ' . implode(', ', $summary['failed']) : '.');
                    $this->redirect('/admin/assets');
                    return;
                }
            }
            $this->redirect($home);
            return;
        }

        $import = $_SESSION['asset_import'] ?? null;
        $this->renderAdmin('admin/asset/import.php', compact('import'));
    }

    private function assetLookups(PDO $pdo): array
    {
        $lookups = ['groups' => [], 'categories' => [], 'branches' => [], 'byAsset' => [], 'bySerial' => []];
        foreach ($pdo->query('SELECT group_id, groupName, category_id FROM tblassets_group')->fetchAll(PDO::FETCH_ASSOC) as $g) {
            $lookups['groups'][mb_strtolower(trim($g['groupName']))] = $g;
        }
        foreach ($pdo->query('SELECT category_id, ic_code, categoryName FROM tblassets_category')->fetchAll(PDO::FETCH_ASSOC) as $c) {
            $lookups['categories'][mb_strtolower(trim($c['categoryName']))] = $c;
        }
        foreach ($pdo->query('SELECT branch_id, branchCode, branchName FROM tblbranch')->fetchAll(PDO::FETCH_ASSOC) as $b) {
            $lookups['branches'][mb_strtolower(trim($b['branchCode']))] = (int) $b['branch_id'];
            $lookups['branches'][mb_strtolower(trim($b['branchName']))] = (int) $b['branch_id'];
        }
        $serialCount = [];
        $items = $pdo->query('SELECT i.inventory_id, i.assetNumber, i.serialNumber, i.itemInfo, i.year_purchased, i.branch_id, i.status,
                    g.groupName FROM tblassets_inventory i JOIN tblassets_group g ON g.group_id = i.group_id')->fetchAll(PDO::FETCH_ASSOC);
        foreach ($items as $item) {
            $lookups['byAsset'][strtoupper(trim($item['assetNumber']))] = $item;
            $key = AssetImportService::serialKey((string) $item['serialNumber']);
            if ($key !== '') {
                $serialCount[$key] = ($serialCount[$key] ?? 0) + 1;
                $lookups['bySerial'][$key] = $item;
            }
        }
        // A serial number used by two items in the system cannot identify one of them.
        foreach ($serialCount as $key => $count) {
            if ($count > 1) {
                unset($lookups['bySerial'][$key]);
            }
        }
        return $lookups;
    }

    /** @return array{created: int, updated: int, groups: int, failed: int[]} */
    private function applyAssetImport(PDO $pdo, array $rows): array
    {
        $asset = new Asset();
        $by = $_SESSION['username'] ?? 'import';
        $summary = ['created' => 0, 'updated' => 0, 'groups' => 0, 'failed' => []];
        $lookups = $this->assetLookups($pdo);
        $allowed = ['itemInfo', 'serialNumber', 'year_purchased', 'branch_id'];

        foreach ($rows as $row) {
            try {
                if ($row['action'] === 'update') {
                    $sets = [];
                    $params = [];
                    foreach ($row['changes'] as $column => $value) {
                        if (in_array($column, $allowed, true)) {
                            $sets[] = "{$column} = ?";
                            $params[] = $value;
                        }
                    }
                    if ($sets) {
                        $params[] = (int) $row['inventory_id'];
                        $pdo->prepare('UPDATE tblassets_inventory SET ' . implode(', ', $sets) . ' WHERE inventory_id = ?')->execute($params);
                        $summary['updated']++;
                    }
                } elseif ($row['action'] === 'new') {
                    $groupId = (int) ($row['group_id'] ?? 0);
                    $modelKey = mb_strtolower(trim($row['model']));
                    if ($groupId <= 0 && isset($lookups['groups'][$modelKey])) {
                        $groupId = (int) $lookups['groups'][$modelKey]['group_id']; // created by an earlier row
                    }
                    if ($groupId <= 0) {
                        $category = $lookups['categories'][mb_strtolower(trim($row['category']))] ?? null;
                        if (!$category) {
                            $summary['failed'][] = $row['excel_row'];
                            continue;
                        }
                        $groupId = (int) $asset->addGroup($row['model'], 'Added by asset import', (int) $category['category_id'],
                            (string) $category['ic_code'], $by);
                        if ($groupId <= 0) {
                            $summary['failed'][] = $row['excel_row'];
                            continue;
                        }
                        $lookups['groups'][$modelKey] = ['group_id' => $groupId];
                        $summary['groups']++;
                    }
                    $newId = $asset->addItem($groupId, $row['serial'], $row['info'], $row['year'], $by, $row['branch_id'] ?? null);
                    if ($newId) {
                        $summary['created']++;
                    } else {
                        $summary['failed'][] = $row['excel_row'];
                    }
                }
            } catch (Throwable $e) {
                error_log('Asset import row ' . $row['excel_row'] . ' failed: ' . $e->getMessage());
                $summary['failed'][] = $row['excel_row'];
            }
        }
        return $summary;
    }

    /** GET /it/service-status (JSON): DNS / mail / website check for email tickets. */
    public function serviceStatus(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
        $role = strtoupper((string) ($_SESSION['usertype'] ?? ''));
        if (empty($_SESSION['account_id']) || !in_array($role, ['IT', 'ADMIN'], true)) {
            http_response_code(403);
            echo json_encode(['success' => false]);
            return;
        }
        // One live check per minute per user is plenty.
        $cached = $_SESSION['service_status_cache'] ?? null;
        if (empty($_GET['refresh']) && is_array($cached) && ($cached['time'] ?? 0) > time() - 60) {
            echo json_encode(['success' => true, 'cached' => true] + $cached['data']);
            return;
        }
        require_once __DIR__ . '/../../Helpers/ServiceStatus.php';
        $data = ServiceStatus::run();
        $_SESSION['service_status_cache'] = ['time' => time(), 'data' => $data];
        echo json_encode(['success' => true] + $data);
    }

    /* ------------------------------------------------------------------ */

    /** @param string[] $roles @return string the signed-in role */
    protected function requireRoles(array $roles): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $role = strtoupper((string) ($_SESSION['usertype'] ?? ''));
        if (empty($_SESSION['account_id']) || !in_array($role, $roles, true)) {
            $this->redirect('/login');
            exit;
        }
        return $role;
    }

    protected function tableExists(string $table): bool
    {
        global $pdo;
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?');
        $stmt->execute([$table]);
        return (int) $stmt->fetchColumn() > 0;
    }

    /** System pages: Admin accounts except the General Manager. */
    protected function requireSystemAdmin(): void
    {
        $this->requireAdmin();
        require_once __DIR__ . '/../../Helpers/AdminAccess.php';
        if (!AdminAccess::canManageSystem()) {
            $_SESSION['flash_error'] = 'System settings are for the system administrator only.';
            $this->redirect('/admin/dashboard');
            exit;
        }
    }

    protected function requireAdmin(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION['account_id']) || strtoupper((string) ($_SESSION['usertype'] ?? '')) !== 'ADMIN') {
            $this->redirect('/login');
            exit;
        }
    }

    protected function requireCsrf(string $backTo): void
    {
        $posted = (string) ($_POST['csrf_token'] ?? '');
        if ($posted === '' || !hash_equals((string) ($_SESSION['csrf_token'] ?? ''), $posted)) {
            $_SESSION['flash_error'] = 'Invalid CSRF token.';
            $this->redirect($backTo);
            exit;
        }
    }

    protected function renderAdmin(string $view, array $vars = []): void
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
        }
        $csrf_token = $_SESSION['csrf_token'];
        $ctx = $this->getLoggedUserContext();
        $base = $ctx['base'];
        $loggedFirstname = $ctx['loggedFirstname'];
        $loggedPosition = $ctx['loggedPosition'];
        $notificationData = $this->loadNotifications();
        $count = $notificationData['count'];
        $notifications = $notificationData['notifications'];
        extract($vars, EXTR_SKIP);
        require __DIR__ . '/../../Views/' . $view;
    }
}
