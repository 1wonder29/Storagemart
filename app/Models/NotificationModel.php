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
        $limit = (int)$limit;
        $stmt = $this->pdo->prepare(
            "SELECT * FROM notifications
             WHERE user_id = ?
             ORDER BY created_at DESC
             LIMIT $limit"
        );
        $stmt->execute([$userId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        return $this->normalizeNotificationsForUser(
            $rows,
            $this->getAccountUsertype((int) $userId)
        );
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

    /**
     * Same as getTicketRecipients but returns account_id + usertype
     * so callers can send role-specific action_urls.
     */
    public function getTicketRecipientsWithType(string $department): array
    {
        $sql = "
            SELECT DISTINCT a.account_id, a.usertype
            FROM {$this->tblaccounts} a
            JOIN {$this->tblemployee} e ON a.account_id = e.account_id
            WHERE 
                a.usertype IN ('IT', 'ADMIN')
                OR (a.usertype = 'HEAD' AND e.department = :department)
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':department' => $department]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Build role-specific ticket detail URL for comment notifications.
     */
    public function getAccountUsertype(int $accountId): string
    {
        $stmt = $this->pdo->prepare(
            "SELECT usertype FROM {$this->tblaccounts} WHERE account_id = ? LIMIT 1"
        );
        $stmt->execute([$accountId]);

        return strtoupper((string) ($stmt->fetchColumn() ?: 'EMPLOYEE'));
    }

    /**
     * Rewrite legacy resolved-ticket rate links to role-specific ticket detail pages.
     */
    public function normalizeNotificationsForUser(array $notifications, string $usertype): array
    {
        foreach ($notifications as &$notification) {
            $actionUrl = (string) ($notification['action_url'] ?? '');
            $ticketId = (int) ($notification['related_id'] ?? 0);
            $message = (string) ($notification['message'] ?? '');

            if ($ticketId <= 0) {
                continue;
            }

            if (strpos($actionUrl, '/tickets/rate') !== false) {
                $notification['action_url'] = $this->getTicketViewUrlForRole($usertype, $ticketId);
                continue;
            }

            if ($this->isNewTicketFiledNotification($message) && $this->isTicketListActionUrl($actionUrl)) {
                $notification['action_url'] = $this->getTicketViewUrlForRole($usertype, $ticketId);
            }
        }
        unset($notification);

        return $notifications;
    }

    private function isNewTicketFiledNotification(string $message): bool
    {
        return str_starts_with($message, 'New Ticket Filed')
            || str_starts_with($message, 'New IT Ticket Filed');
    }

    private function isTicketListActionUrl(string $actionUrl): bool
    {
        return (bool) preg_match('#^/[a-z]+/tickets/?$#', $actionUrl);
    }

    public function getTicketViewUrlForRole(string $usertype, int $ticketId): string
    {
        $role = strtoupper(trim($usertype));
        $map = [
            'ADMIN'    => '/admin/tickets/view?id=',
            'EMPLOYEE' => '/employee/tickets/view?id=',
            'HEAD'     => '/head/tickets/view?id=',
            'HR'       => '/hr/tickets/view?id=',
            'IT'       => '/it/tickets/view?id=',
            'AOM'      => '/aom/tickets/view?id=',
            'HOM'      => '/hom/tickets/view?id=',
            'OM'       => '/om/tickets/view?id=',
        ];

        $prefix = $map[$role] ?? '/employee/tickets/view?id=';

        return $prefix . $ticketId;
    }

    /**
     * Collect everyone who should be notified about a new ticket comment
     * (ticket owner, filer, dept head, IT/admin, prior commenters).
     */
    public function getCommentNotificationRecipients(int $ticketId, int $excludeAccountId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT
                t.ticket_number,
                t.department,
                t.created_by,
                emp.account_id AS owner_account_id,
                owner_acc.usertype AS owner_usertype,
                head.account_id AS head_account_id,
                head.usertype AS head_usertype,
                creator.usertype AS creator_usertype
            FROM {$this->tbltickets} t
            JOIN {$this->tblemployee} emp ON emp.employee_id = t.employee_id
            JOIN {$this->tblaccounts} owner_acc ON owner_acc.account_id = emp.account_id
            LEFT JOIN {$this->tblemployee} head_emp
                ON head_emp.department = t.department AND head_emp.position = 'HEAD'
            LEFT JOIN {$this->tblaccounts} head ON head.account_id = head_emp.account_id
            LEFT JOIN {$this->tblaccounts} creator ON creator.account_id = t.created_by
            WHERE t.ticket_id = :ticket_id
            LIMIT 1
        ");
        $stmt->execute([':ticket_id' => $ticketId]);
        $ticket = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$ticket) {
            return ['ticket_number' => null, 'recipients' => []];
        }

        $recipients = [];

        $addRecipient = static function (?int $accountId, ?string $usertype) use (&$recipients): void {
            if ($accountId === null || $accountId <= 0) {
                return;
            }
            $recipients[$accountId] = strtoupper((string) ($usertype ?? 'EMPLOYEE'));
        };

        $addRecipient(
            isset($ticket['owner_account_id']) ? (int) $ticket['owner_account_id'] : null,
            $ticket['owner_usertype'] ?? 'EMPLOYEE'
        );
        $addRecipient(
            isset($ticket['head_account_id']) ? (int) $ticket['head_account_id'] : null,
            $ticket['head_usertype'] ?? 'HEAD'
        );
        $addRecipient(
            isset($ticket['created_by']) ? (int) $ticket['created_by'] : null,
            $ticket['creator_usertype'] ?? 'EMPLOYEE'
        );

        $department = (string) ($ticket['department'] ?? '');
        foreach ($this->getTicketRecipientsWithType($department) as $row) {
            $addRecipient((int) ($row['account_id'] ?? 0), $row['usertype'] ?? 'IT');
        }

        $commentStmt = $this->pdo->prepare("
            SELECT DISTINCT c.account_id, a.usertype
            FROM tblticket_comments c
            JOIN {$this->tblaccounts} a ON a.account_id = c.account_id
            WHERE c.ticket_id = :ticket_id
        ");
        $commentStmt->execute([':ticket_id' => $ticketId]);
        foreach ($commentStmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $addRecipient((int) ($row['account_id'] ?? 0), $row['usertype'] ?? 'EMPLOYEE');
        }

        unset($recipients[$excludeAccountId]);

        $formatted = [];
        foreach ($recipients as $accountId => $usertype) {
            $formatted[] = [
                'account_id' => $accountId,
                'usertype'   => $usertype,
            ];
        }

        return [
            'ticket_number' => $ticket['ticket_number'] ?? null,
            'recipients'    => $formatted,
        ];
    }

    /**
     * Notify all ticket stakeholders when a comment is posted (except the author).
     */
    public function notifyTicketComment(
        int $ticketId,
        int $commenterAccountId,
        string $authorName,
        string $authorRole,
        string $commentText
    ): void {
        $data = $this->getCommentNotificationRecipients($ticketId, $commenterAccountId);
        $recipients = $data['recipients'] ?? [];

        if ($recipients === []) {
            return;
        }

        $ticketNumber = $data['ticket_number'] ?: ('#' . $ticketId);
        $preview = $commentText;
        if (mb_strlen($preview) > 100) {
            $preview = mb_substr($preview, 0, 97) . '...';
        }

        $message = sprintf(
            '%s (%s) commented on ticket %s: %s',
            $authorName,
            $authorRole,
            $ticketNumber,
            $preview
        );

        foreach ($recipients as $recipient) {
            $receiverId = (int) ($recipient['account_id'] ?? 0);
            if ($receiverId <= 0) {
                continue;
            }

            $usertype = (string) ($recipient['usertype'] ?? 'EMPLOYEE');
            $this->create(
                $receiverId,
                $message,
                'fa-comment',
                'info',
                $this->getTicketViewUrlForRole($usertype, $ticketId),
                $ticketId
            );
        }
    }

}
