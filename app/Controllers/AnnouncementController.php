<?php

require_once __DIR__ . '/AuthController.php';
require_once __DIR__ . '/../Helpers/Announcements.php';
require_once __DIR__ . '/../Helpers/ActivityLogger.php';
require_once __DIR__ . '/../Models/NotificationModel.php';

/**
 * Announcements: /admin/announcements (everyone or one department),
 * /head/announcements and /hom/announcements (own department only),
 * plus the JSON feed and read receipts used by the header megaphone.
 */
class AnnouncementController extends AuthController
{
    private const AREA_ROLES = ['admin' => 'ADMIN', 'head' => 'HEAD', 'hom' => 'HOM'];

    private const HOMES = [
        'ADMIN' => '/admin', 'EMPLOYEE' => '/employee', 'HEAD' => '/head', 'HR' => '/hr',
        'IT' => '/it', 'AOM' => '/aom', 'HOM' => '/hom', 'OM' => '/om',
    ];

    public function manage(string $area): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $role = strtoupper((string) ($_SESSION['usertype'] ?? ''));
        $expected = self::AREA_ROLES[$area] ?? '';
        if (empty($_SESSION['account_id']) || $expected === '' || $role !== $expected) {
            $this->redirect('/login');
            return;
        }
        require_once __DIR__ . '/../Helpers/AdminAccess.php';
        if ($area === 'admin' && !AdminAccess::canManageSystem()) {
            $_SESSION['flash_error'] = 'Announcements are managed by the system administrator.';
            $this->redirect('/admin/dashboard');
            return;
        }
        global $pdo;
        $accountId = (int) $_SESSION['account_id'];
        $home = '/' . $area . '/announcements';
        $department = Announcements::departmentOf($pdo, $accountId);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $posted = (string) ($_POST['csrf_token'] ?? '');
            if ($posted === '' || !hash_equals((string) ($_SESSION['csrf_token'] ?? ''), $posted)) {
                $_SESSION['flash_error'] = 'Invalid CSRF token.';
                $this->redirect($home);
                return;
            }
            try {
                $this->handlePost($pdo, $role, $accountId, $department, $home);
            } catch (Throwable $e) {
                error_log('AnnouncementController save failed: ' . $e->getMessage());
                $_SESSION['flash_error'] = 'Could not save the announcement. Please run the database update first.';
                $this->redirect($home);
            }
            return;
        }

        $setupMissing = false;
        $items = $departments = [];
        try {
            $items = Announcements::managed($pdo, $role, $department);
            $departments = Announcements::departments($pdo);
        } catch (Throwable $e) {
            $setupMissing = true;
        }
        $editing = null;
        foreach ($items as $item) {
            if ((int) $item['announcement_id'] === (int) ($_GET['edit'] ?? 0)) {
                $editing = $item;
            }
        }

        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
        }
        $csrf_token = $_SESSION['csrf_token'];
        $ctx = $this->getLoggedUserContext();
        $loggedFirstname = $ctx['loggedFirstname'];
        $loggedPosition = $ctx['loggedPosition'];
        $notificationData = $this->loadNotifications();
        $count = $notificationData['count'];
        $notifications = $notificationData['notifications'];
        require __DIR__ . '/../Views/system/announcements.php';
    }

    private function handlePost(PDO $pdo, string $role, int $accountId, string $department, string $home): void
    {
        $action = (string) ($_POST['action'] ?? 'save');
        $id = (int) ($_POST['announcement_id'] ?? 0);

        if ($id > 0 && !$this->mayManage($pdo, $role, $department, $id)) {
            $_SESSION['flash_error'] = 'You can only manage announcements for your own department.';
            $this->redirect($home);
            return;
        }

        if (($action === 'approve' || $action === 'reject') && $role === 'ADMIN') {
            $this->review($pdo, $id, $action, $accountId, trim((string) ($_POST['review_note'] ?? '')));
            $this->redirect($home);
            return;
        }

        if ($action === 'delete' || $action === 'toggle') {
            if ($action === 'delete') {
                $pdo->prepare('DELETE FROM tblannouncement_reads WHERE announcement_id = ?')->execute([$id]);
                $pdo->prepare('DELETE FROM tblannouncements WHERE announcement_id = ?')->execute([$id]);
                $_SESSION['flash_success'] = 'Announcement deleted.';
            } else {
                $pdo->prepare('UPDATE tblannouncements SET is_active = 1 - is_active, updated_at = NOW() WHERE announcement_id = ?')->execute([$id]);
                $_SESSION['flash_success'] = 'Announcement updated.';
            }
            ActivityLogger::update('Announcements', (string) $id, ucfirst($action) . ' announcement', $_SESSION['username'] ?? 'Unknown');
            $this->redirect($home);
            return;
        }

        $title = trim((string) ($_POST['title'] ?? ''));
        $body = trim(str_replace("\r\n", "\n", (string) ($_POST['body'] ?? '')));
        $importance = ($_POST['importance'] ?? '') === 'important' ? 'important' : 'normal';
        $startsAt = $this->parseDateTime((string) ($_POST['starts_at'] ?? '')) ?? date('Y-m-d H:i:s');
        $endsAt = $this->parseDateTime((string) ($_POST['ends_at'] ?? ''));

        if ($role === 'ADMIN') {
            $targetDepartment = trim((string) ($_POST['department'] ?? ''));
            $audience = $targetDepartment === '' ? 'ALL' : 'DEPARTMENT';
            if ($audience === 'DEPARTMENT' && !in_array($targetDepartment, Announcements::departments($pdo), true)) {
                $targetDepartment = '';
                $audience = 'ALL';
            }
        } else {
            if ($department === '') {
                $_SESSION['flash_error'] = 'Your account has no department, so you cannot post announcements.';
                $this->redirect($home);
                return;
            }
            $audience = 'DEPARTMENT';
            $targetDepartment = $department;
        }

        $errors = [];
        if ($title === '' || mb_strlen($title) > 150) {
            $errors[] = 'Title is required (max 150 characters).';
        }
        if ($body === '' || mb_strlen($body) > 5000) {
            $errors[] = 'Message is required (max 5000 characters).';
        }
        if ($endsAt !== null && $endsAt <= $startsAt) {
            $errors[] = 'The end date must be after the start date.';
        }
        if ($errors) {
            $_SESSION['flash_error'] = implode(' ', $errors);
            $this->redirect($home . ($id ? '?edit=' . $id : ''));
            return;
        }

        $author = $this->authorName($pdo, $accountId, $role);
        // A department head's announcement (new or edited) waits for Admin approval.
        $needsApproval = $role !== 'ADMIN' && Announcements::hasApproval($pdo);
        if ($id > 0) {
            $pdo->prepare('UPDATE tblannouncements SET title = ?, body = ?, audience = ?, department = ?, importance = ?,
                    starts_at = ?, ends_at = ?, updated_at = NOW() WHERE announcement_id = ?')
                ->execute([$title, $body, $audience, $audience === 'ALL' ? null : $targetDepartment, $importance, $startsAt, $endsAt, $id]);
            if ($needsApproval) {
                $pdo->prepare("UPDATE tblannouncements SET approval_status = 'PENDING', reviewed_by = NULL, reviewed_at = NULL,
                        review_note = NULL WHERE announcement_id = ?")->execute([$id]);
                $this->notifyApprovers($pdo, $id, $author, $title, $targetDepartment, true);
            }
            $_SESSION['flash_success'] = $needsApproval
                ? 'Announcement updated and sent to Admin for approval again.'
                : 'Announcement updated.';
        } else {
            if ($needsApproval) {
                $pdo->prepare("INSERT INTO tblannouncements (title, body, audience, department, importance, starts_at, ends_at,
                        is_active, created_by, created_by_role, created_by_name, created_at, approval_status)
                        VALUES (?, ?, ?, ?, ?, ?, ?, 1, ?, ?, ?, NOW(), 'PENDING')")
                    ->execute([$title, $body, $audience, $targetDepartment, $importance, $startsAt, $endsAt,
                        $accountId, $role, $author]);
                $id = (int) $pdo->lastInsertId();
                $this->notifyApprovers($pdo, $id, $author, $title, $targetDepartment, false);
                $_SESSION['flash_success'] = 'Announcement sent to Admin for approval. It is shown to the '
                    . $targetDepartment . ' department once approved.';
            } else {
                $pdo->prepare('INSERT INTO tblannouncements (title, body, audience, department, importance, starts_at, ends_at,
                        is_active, created_by, created_by_role, created_by_name, created_at)
                        VALUES (?, ?, ?, ?, ?, ?, ?, 1, ?, ?, ?, NOW())')
                    ->execute([$title, $body, $audience, $audience === 'ALL' ? null : $targetDepartment, $importance, $startsAt, $endsAt,
                        $accountId, $role, $author]);
                $id = (int) $pdo->lastInsertId();
                Announcements::notifyRecipients($pdo, [
                    'announcement_id' => $id, 'title' => $title, 'audience' => $audience,
                    'department' => $targetDepartment, 'importance' => $importance, 'starts_at' => $startsAt,
                ], $accountId);
                $_SESSION['flash_success'] = $audience === 'ALL'
                    ? 'Announcement posted to everyone.'
                    : 'Announcement posted to the ' . $targetDepartment . ' department.';
            }
        }
        ActivityLogger::update('Announcements', (string) $id, 'Saved announcement: ' . $title, $_SESSION['username'] ?? 'Unknown',
            ['audience' => $audience, 'department' => $targetDepartment]);
        $this->redirect($home);
    }

    /** Admin approves or rejects a department head's announcement. */
    private function review(PDO $pdo, int $id, string $action, int $accountId, string $note): void
    {
        $stmt = $pdo->prepare('SELECT * FROM tblannouncements WHERE announcement_id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row || ($row['approval_status'] ?? '') !== 'PENDING') {
            $_SESSION['flash_error'] = 'This announcement is not waiting for approval.';
            return;
        }
        if ($action === 'reject' && $note === '') {
            $_SESSION['flash_error'] = 'Please give a reason so the department head can fix it.';
            return;
        }
        $status = $action === 'approve' ? 'APPROVED' : 'REJECTED';
        $pdo->prepare('UPDATE tblannouncements SET approval_status = ?, reviewed_by = ?, reviewed_at = NOW(), review_note = ?
                WHERE announcement_id = ? AND approval_status = \'PENDING\'')
            ->execute([$status, $accountId, $note !== '' ? mb_substr($note, 0, 255) : null, $id]);

        $notifications = new NotificationModel();
        $title = mb_substr((string) $row['title'], 0, 100);
        $headPath = strtoupper((string) $row['created_by_role']) === 'HOM' ? '/hom/announcements' : '/head/announcements';
        if ($status === 'APPROVED') {
            $sent = Announcements::notifyRecipients($pdo, $row, (int) $row['created_by']);
            $notifications->create((int) $row['created_by'], 'Your announcement "' . $title . '" was approved and posted.',
                'fa-check-circle', 'success', $headPath, $id);
            $_SESSION['flash_success'] = 'Announcement approved' . ($sent ? " and sent to {$sent} people." : '.');
        } else {
            $notifications->create((int) $row['created_by'], 'Your announcement "' . $title . '" was not approved: ' . $note,
                'fa-times-circle', 'danger', $headPath, $id);
            $_SESSION['flash_success'] = 'Announcement rejected. The department head was told why.';
        }
        ActivityLogger::update('Announcements', (string) $id, ucfirst(strtolower($status)) . ' announcement: ' . $row['title'],
            $_SESSION['username'] ?? 'Unknown', ['note' => $note]);
    }

    private function notifyApprovers(PDO $pdo, int $id, string $author, string $title, string $department, bool $edited): void
    {
        $notifications = new NotificationModel();
        foreach (Announcements::approverIds($pdo) as $adminId) {
            $notifications->create($adminId,
                $author . ($edited ? ' edited' : ' posted') . ' an announcement for ' . $department . ' that needs approval: ' . mb_substr($title, 0, 80),
                'fa-bullhorn', 'warning', '/admin/announcements', $id);
        }
    }

    /** GET /announcements/feed (JSON) */
    public function feed(): void
    {
        $accountId = $this->jsonAuth();
        global $pdo;
        try {
            $items = Announcements::feed($pdo, $accountId);
        } catch (Throwable $e) {
            $items = [];
        }
        $unread = count(array_filter($items, static function ($i) { return !$i['is_read']; }));
        echo json_encode(['success' => true, 'unread' => $unread, 'items' => $items]);
    }

    /** POST /announcements/read (JSON): id, or all=1 */
    public function read(): void
    {
        $accountId = $this->jsonAuth();
        global $pdo;
        try {
            if (!empty($_POST['all'])) {
                foreach (Announcements::feed($pdo, $accountId, 50) as $item) {
                    if (!$item['is_read']) {
                        Announcements::markRead($pdo, $accountId, $item['announcement_id']);
                    }
                }
            } else {
                Announcements::markRead($pdo, $accountId, (int) ($_POST['id'] ?? 0));
            }
            echo json_encode(['success' => true]);
        } catch (Throwable $e) {
            echo json_encode(['success' => false]);
        }
    }

    /** GET /announcements/open?id= (from a bell notification): home page with that announcement open. */
    public function open(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION['account_id'])) {
            $this->redirect('/login');
            return;
        }
        $home = self::HOMES[strtoupper((string) ($_SESSION['usertype'] ?? ''))] ?? '/';
        $this->redirect($home . '?announcement=' . (int) ($_GET['id'] ?? 0));
    }

    private function jsonAuth(): int
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
        $accountId = (int) ($_SESSION['account_id'] ?? 0);
        if ($accountId <= 0) {
            http_response_code(401);
            echo json_encode(['success' => false]);
            exit;
        }
        return $accountId;
    }

    private function mayManage(PDO $pdo, string $role, string $department, int $id): bool
    {
        $stmt = $pdo->prepare('SELECT audience, department FROM tblannouncements WHERE announcement_id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return false;
        }
        return $role === 'ADMIN' || ($row['audience'] === 'DEPARTMENT' && $row['department'] === $department);
    }

    private function parseDateTime(string $value): ?string
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }
        $ts = strtotime(str_replace('T', ' ', $value));
        return $ts === false ? null : date('Y-m-d H:i:s', $ts);
    }

    private function authorName(PDO $pdo, int $accountId, string $role): string
    {
        if ($role === 'ADMIN') {
            return 'Admin';
        }
        $stmt = $pdo->prepare("SELECT CONCAT(firstname, ' ', lastname) FROM tblemployee WHERE account_id = ? LIMIT 1");
        $stmt->execute([$accountId]);
        return trim((string) $stmt->fetchColumn()) ?: $role;
    }
}
