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
        $this->requireAdmin();
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
        $home = $role === 'IT' ? '/it/manuals' : '/admin/manuals';
        global $pdo;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->requireCsrf($home);
            $action = (string) ($_POST['action'] ?? 'save');
            $manualId = (int) ($_POST['manual_id'] ?? 0);
            try {
                if ($action === 'delete') {
                    $stmt = $pdo->prepare('SELECT title, is_default FROM tblit_manuals WHERE manual_id = ?');
                    $stmt->execute([$manualId]);
                    $row = $stmt->fetch(PDO::FETCH_ASSOC);
                    if (!$row) {
                        $_SESSION['flash_error'] = 'Manual not found.';
                    } elseif ((int) $row['is_default'] === 1) {
                        $_SESSION['flash_error'] = 'The default manual cannot be deleted. Make another manual the default first.';
                    } else {
                        $pdo->prepare('DELETE FROM tblit_manuals WHERE manual_id = ?')->execute([$manualId]);
                        ActivityLogger::delete('Troubleshooting Manuals', (string) $manualId, 'Deleted manual: ' . $row['title'],
                            $_SESSION['username'] ?? 'Unknown');
                        $_SESSION['flash_success'] = 'Manual deleted.';
                    }
                    $this->redirect($home);
                    return;
                }

                $title = trim((string) ($_POST['title'] ?? ''));
                $url = trim((string) ($_POST['url'] ?? ''));
                $steps = trim(str_replace("\r\n", "\n", (string) ($_POST['steps'] ?? '')));
                $categories = array_values(array_intersect(
                    (array) ($_POST['categories'] ?? []),
                    array_merge(TicketFormFields::HEAD_OFFICE_CATEGORIES, TicketFormFields::BRANCH_CATEGORIES)
                ));
                $keywords = implode(', ', array_slice(array_filter(array_map('trim',
                    preg_split('/[,\n;]+/', strtolower((string) ($_POST['keywords'] ?? ''))) ?: []), 'strlen'), 0, 40));
                $description = trim((string) ($_POST['description'] ?? ''));
                $makeDefault = $role === 'ADMIN' && !empty($_POST['is_default']);

                $errors = [];
                if ($title === '' || mb_strlen($title) > 150) {
                    $errors[] = 'Title is required (max 150 characters).';
                }
                if ($url !== '' && (!filter_var($url, FILTER_VALIDATE_URL) || !preg_match('#^https?://#i', $url))) {
                    $errors[] = 'The link must start with http:// or https://.';
                }
                if ($url === '' && $steps === '') {
                    $errors[] = 'Add the steps, a link, or both.';
                }
                if ($makeDefault && $url === '') {
                    $errors[] = 'The default manual needs a link (it is sent with every ticket).';
                }
                if ($errors) {
                    $_SESSION['flash_error'] = implode(' ', $errors);
                    $_SESSION['manual_old_input'] = $_POST;
                    $this->redirect($home . ($manualId ? '?edit=' . $manualId : ''));
                    return;
                }

                $params = [
                    ':title' => $title,
                    ':url' => $url !== '' ? mb_substr($url, 0, 500) : null,
                    ':description' => $description !== '' ? mb_substr($description, 0, 255) : null,
                    ':steps' => $steps !== '' ? $steps : null,
                    ':categories' => implode(', ', $categories),
                    ':keywords' => $keywords,
                    ':by' => (int) $_SESSION['account_id'],
                ];
                $pdo->beginTransaction();
                if ($manualId > 0) {
                    $params[':id'] = $manualId;
                    $pdo->prepare('UPDATE tblit_manuals SET title = :title, url = :url, description = :description, steps = :steps,
                            categories = :categories, keywords = :keywords, updated_by = :by, updated_at = NOW()
                            WHERE manual_id = :id')->execute($params);
                } else {
                    $pdo->prepare('INSERT INTO tblit_manuals (title, url, description, steps, categories, keywords, is_default, created_by, created_at)
                            VALUES (:title, :url, :description, :steps, :categories, :keywords, 0, :by, NOW())')->execute($params);
                    $manualId = (int) $pdo->lastInsertId();
                }
                if ($makeDefault) {
                    $pdo->prepare('UPDATE tblit_manuals SET is_default = (manual_id = ?)')->execute([$manualId]);
                    AppSettings::set('it_manual_url', $url);
                }
                $pdo->commit();
                ActivityLogger::update('Troubleshooting Manuals', (string) $manualId, 'Saved manual: ' . $title,
                    $_SESSION['username'] ?? 'Unknown');
                $_SESSION['flash_success'] = 'Manual saved.';
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
        $this->renderAdmin('system/manuals.php', compact(
            'role', 'home', 'manuals', 'setupMissing', 'editing', 'old', 'allCategories', 'testCategory', 'testText', 'testResult'
        ));
    }

    /**
     * Admin > System > Seasonal Themes: preview a theme in your own browser, schedule its
     * release (start / end date and time), turn schedules off or delete them.
     */
    public function themes(): void
    {
        $this->requireAdmin();
        global $pdo;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->requireCsrf('/admin/themes');
            $action = (string) ($_POST['action'] ?? 'schedule');
            $scheduleId = (int) ($_POST['schedule_id'] ?? 0);
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
                } else {
                    $key = (string) ($_POST['theme_key'] ?? '');
                    $startsAt = strtotime(str_replace('T', ' ', (string) ($_POST['starts_at'] ?? '')));
                    $endsAt = strtotime(str_replace('T', ' ', (string) ($_POST['ends_at'] ?? '')));
                    $message = trim((string) ($_POST['message'] ?? ''));
                    if ($action === 'now') {
                        $startsAt = time();
                        $endsAt = time() + max(1, min(60, (int) ($_POST['days'] ?? 7))) * 86400;
                    }
                    if (!SeasonalThemes::exists($key)) {
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
                            ->execute([$key, SeasonalThemes::CATALOG[$key]['name'], $message !== '' ? $message : null,
                                date('Y-m-d H:i:s', $startsAt), date('Y-m-d H:i:s', $endsAt), (int) $_SESSION['account_id']]);
                        ActivityLogger::update('Admin - Themes', $key, 'Scheduled theme ' . SeasonalThemes::CATALOG[$key]['name'],
                            $_SESSION['username'] ?? 'Unknown', ['from' => date('Y-m-d H:i', $startsAt), 'to' => date('Y-m-d H:i', $endsAt)]);
                        $_SESSION['flash_success'] = SeasonalThemes::CATALOG[$key]['name'] . ' theme scheduled.'
                            . ((int) $overlap->fetchColumn() > 0 ? ' It overlaps another schedule; the one that started last is shown.' : '');
                    }
                }
            } catch (Throwable $e) {
                error_log('SystemController::themes failed: ' . $e->getMessage());
                $_SESSION['flash_error'] = 'Could not save. Please run the database update first.';
            }
            $this->redirect('/admin/themes');
            return;
        }

        $schedules = [];
        $setupMissing = false;
        try {
            $schedules = $pdo->query('SELECT s.*, (s.is_active = 1 AND s.starts_at <= NOW() AND s.ends_at > NOW()) AS is_live,
                        (s.starts_at > NOW()) AS is_upcoming
                    FROM tblthemes_schedule s ORDER BY s.ends_at < NOW(), s.starts_at')->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            $setupMissing = true;
        }
        $current = SeasonalThemes::current($pdo);
        $this->renderAdmin('admin/system/themes.php', compact('schedules', 'setupMissing', 'current'));
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
