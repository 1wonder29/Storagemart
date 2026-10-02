<?php
    require_once __DIR__ . '/admin/BaseModel.php';

class NotificationModel extends BaseModel
{
    protected $tblemployee = 'tblemployee';
    protected $tbltickets = 'tbltickets';
    protected $tblassets = 'tblassets_inventory';
    protected $tblbranch = 'tblbranch';
    protected $tblgroup = 'tblassets_group';
    protected $tblaccounts = 'tblaccounts';

    public function create(
        int $userId,
        string $message,
        string $icon = 'fa-bell',
        string $bgColor = 'primary',
        ?string $actionUrl = null,
        ?int $relatedId = null
    ): bool {
        $stmt = $this->pdo->prepare(
            "INSERT INTO notifications
            (user_id, message, icon, bg_color, action_url, related_id, is_read, created_at)
            VALUES (?, ?, ?, ?, ?, ?, 0, NOW())"
        );

        return $stmt->execute([
            $userId,
            $message,
            $icon,
            $bgColor,
            $actionUrl,
            $relatedId
        ]);
    }


    public function getUnreadCount($userId)
    {
        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0"
        );
        $stmt->execute([$userId]);
        return (int) $stmt->fetchColumn();
    }

    public function getLatest($userId, $limit = 5)
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM notifications
             WHERE user_id = ?
             ORDER BY created_at DESC
             LIMIT $limit"
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function markAsRead(int $id, int $userId): bool
    {
        $stmt = $this->pdo->prepare(
            "UPDATE notifications
             SET is_read = 1
             WHERE id = ? AND user_id = ?"
        );
        return $stmt->execute([$id, $userId]);
    }

    public function getTicketRecipients(string $department): array
    {
        $sql = "
            SELECT DISTINCT a.account_id
            FROM {$this->tblaccounts} a
            JOIN {$this->tblemployee} e ON a.account_id = e.account_id
            WHERE 
                a.usertype IN ('IT', 'ADMIN')
                OR (a.usertype = 'HEAD' AND e.department = :department)
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':department' => $department]);

        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

}
