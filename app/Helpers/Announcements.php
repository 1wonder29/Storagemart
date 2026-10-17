<?php

/**
 * Announcements (tblannouncements): Admin posts to everyone (or one department),
 * a department head (HEAD / HOM) posts to their own department only.
 */
class Announcements
{
    public const POSTER_ROLES = ['ADMIN', 'HEAD', 'HOM'];

    /** The signed-in account's department (from tblemployee). */
    public static function departmentOf(PDO $pdo, int $accountId): string
    {
        $stmt = $pdo->prepare('SELECT department FROM tblemployee WHERE account_id = ? LIMIT 1');
        $stmt->execute([$accountId]);
        return trim((string) $stmt->fetchColumn());
    }

    /** Approval columns exist once the database update has run. */
    public static function hasApproval(PDO $pdo): bool
    {
        static $has = null;
        if ($has === null) {
            $has = (int) $pdo->query("SELECT COUNT(*) FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tblannouncements' AND COLUMN_NAME = 'approval_status'")
                ->fetchColumn() === 1;
        }
        return $has;
    }

    /** SQL condition (and params) for announcements this account may see. */
    private static function visibleWhere(string $department): array
    {
        global $pdo;
        $approved = self::hasApproval($pdo) ? "a.approval_status = 'APPROVED' AND " : '';
        $where = $approved . "a.is_active = 1 AND a.starts_at <= NOW() AND (a.ends_at IS NULL OR a.ends_at > NOW())
                  AND (a.audience = 'ALL' OR (a.audience = 'DEPARTMENT' AND a.department = ?))";
        return [$where, [$department]];
    }

    /** Live announcements for a user, newest first, with a read flag. */
    public static function feed(PDO $pdo, int $accountId, int $limit = 20): array
    {
        [$where, $params] = self::visibleWhere(self::departmentOf($pdo, $accountId));
        $stmt = $pdo->prepare("SELECT a.announcement_id, a.title, a.body, a.audience, a.department, a.importance,
                    a.starts_at, a.created_by_name, a.created_by_role,
                    (r.account_id IS NOT NULL OR a.created_by = ?) AS is_read
                FROM tblannouncements a
                LEFT JOIN tblannouncement_reads r ON r.announcement_id = a.announcement_id AND r.account_id = ?
                WHERE {$where}
                ORDER BY a.importance = 'important' DESC, a.starts_at DESC
                LIMIT " . max(1, min(50, $limit)));
        $stmt->execute(array_merge([$accountId, $accountId], $params));
        return array_map(static function (array $row): array {
            $row['announcement_id'] = (int) $row['announcement_id'];
            $row['is_read'] = (bool) $row['is_read'];
            return $row;
        }, $stmt->fetchAll(PDO::FETCH_ASSOC) ?: []);
    }

    public static function canSee(PDO $pdo, int $accountId, int $announcementId): bool
    {
        [$where, $params] = self::visibleWhere(self::departmentOf($pdo, $accountId));
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM tblannouncements a WHERE a.announcement_id = ? AND {$where}");
        $stmt->execute(array_merge([$announcementId], $params));
        return (int) $stmt->fetchColumn() > 0;
    }

    public static function markRead(PDO $pdo, int $accountId, int $announcementId): void
    {
        if (!self::canSee($pdo, $accountId, $announcementId)) {
            return;
        }
        $pdo->prepare('INSERT IGNORE INTO tblannouncement_reads (announcement_id, account_id, read_at) VALUES (?, ?, NOW())')
            ->execute([$announcementId, $accountId]);
        // Its bell notification is read too.
        $pdo->prepare('UPDATE notifications SET is_read = 1 WHERE user_id = ? AND action_url = ? AND is_read = 0')
            ->execute([$accountId, '/announcements/open?id=' . $announcementId]);
    }

    /** Announcements a poster manages: Admin sees all, a head only their department's. */
    public static function managed(PDO $pdo, string $role, string $department): array
    {
        $sql = 'SELECT a.*, (SELECT COUNT(*) FROM tblannouncement_reads r WHERE r.announcement_id = a.announcement_id) AS read_count
                FROM tblannouncements a';
        $params = [];
        if ($role !== 'ADMIN') {
            $sql .= " WHERE a.audience = 'DEPARTMENT' AND a.department = ?";
            $params[] = $department;
        }
        $order = self::hasApproval($pdo) ? "(a.approval_status = 'PENDING') DESC, " : '';
        $stmt = $pdo->prepare($sql . ' ORDER BY ' . $order . 'a.starts_at DESC LIMIT 200');
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /** System administrators who approve department announcements (not the General Manager). */
    public static function approverIds(PDO $pdo): array
    {
        try {
            $rows = $pdo->query("SELECT account_id FROM tblaccounts
                WHERE usertype = 'ADMIN' AND UPPER(status) = 'ACTIVE' AND COALESCE(is_superuser, 0) = 0")->fetchAll(PDO::FETCH_COLUMN);
        } catch (Throwable $e) {
            $rows = $pdo->query("SELECT account_id FROM tblaccounts WHERE usertype = 'ADMIN' AND UPPER(status) = 'ACTIVE'")
                ->fetchAll(PDO::FETCH_COLUMN);
        }
        return array_map('intval', $rows ?: []);
    }

    /** Bell notification for everyone the announcement is for (only when it is live now). */
    public static function notifyRecipients(PDO $pdo, array $row, int $excludeAccountId): int
    {
        if ((string) $row['starts_at'] > date('Y-m-d H:i:s')) {
            return 0; // scheduled: it pops up for each person when it starts
        }
        require_once __DIR__ . '/../Models/NotificationModel.php';
        $notifications = new NotificationModel();
        $message = 'Announcement: ' . mb_substr((string) $row['title'], 0, 120);
        $sent = 0;
        foreach (self::recipients($pdo, (string) $row['audience'], $row['department'] ?? null, $excludeAccountId) as $recipient) {
            $notifications->create($recipient, $message, 'fa-bullhorn', $row['importance'] === 'important' ? 'danger' : 'info',
                '/announcements/open?id=' . (int) $row['announcement_id'], (int) $row['announcement_id']);
            $sent++;
        }
        return $sent;
    }

    /** Accounts that should see an announcement (for the bell notification). */
    public static function recipients(PDO $pdo, string $audience, ?string $department, int $excludeAccountId): array
    {
        if ($audience === 'DEPARTMENT') {
            $stmt = $pdo->prepare("SELECT a.account_id FROM tblaccounts a JOIN tblemployee e ON e.account_id = a.account_id
                    WHERE UPPER(a.status) = 'ACTIVE' AND e.department = ? AND a.account_id <> ?");
            $stmt->execute([(string) $department, $excludeAccountId]);
        } else {
            $stmt = $pdo->prepare("SELECT account_id FROM tblaccounts WHERE UPPER(status) = 'ACTIVE' AND account_id <> ?");
            $stmt->execute([$excludeAccountId]);
        }
        return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN) ?: []);
    }

    /** All departments (for Admin targeting one department). */
    public static function departments(PDO $pdo): array
    {
        $rows = $pdo->query("SELECT DISTINCT department FROM tblemployee WHERE department IS NOT NULL AND department <> '' ORDER BY department")
            ->fetchAll(PDO::FETCH_COLUMN);
        return array_values(array_filter(array_map('trim', $rows ?: [])));
    }
}
