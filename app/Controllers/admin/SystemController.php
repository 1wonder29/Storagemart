<?php
require_once __DIR__ . '/../AuthController.php';
require_once __DIR__ . '/../../Helpers/Session.php';
require_once __DIR__ . '/../../Helpers/ActivityLogger.php';
require_once __DIR__ . '/../../Helpers/AppSettings.php';
require_once __DIR__ . '/../../Helpers/TicketAssigner.php';

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

    /* ------------------------------------------------------------------ */

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
