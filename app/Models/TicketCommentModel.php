<?php

require_once __DIR__ . '/admin/BaseModel.php';

class TicketCommentModel extends BaseModel
{
    protected $tblComments = 'tblticket_comments';
    protected $tblTickets = 'tbltickets';
    protected $tblEmployee = 'tblemployee';

    /** Attachment columns exist once the database update has run. */
    private function hasAttachmentColumns(): bool
    {
        static $has = null;
        if ($has === null) {
            $stmt = $this->pdo->query("SELECT COUNT(*) FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tblticket_comments' AND COLUMN_NAME = 'attachment_path'");
            $has = (int) $stmt->fetchColumn() === 1;
        }
        return $has;
    }

    private function attachmentSelect(): string
    {
        return $this->hasAttachmentColumns()
            ? ', c.attachment_path, c.attachment_name, c.attachment_mime, c.attachment_size'
            : ', NULL AS attachment_path, NULL AS attachment_name, NULL AS attachment_mime, NULL AS attachment_size';
    }

    /** Adds attachment_url / is_image for the browser and hides the stored path. */
    public static function presentComment(array $row): array
    {
        $hasFile = !empty($row['attachment_path']);
        $base = defined('BASE_URL') ? rtrim(BASE_URL, '/') : '';
        $row['attachment_url'] = $hasFile ? $base . '/ticket-comments/file?id=' . (int) $row['comment_id'] : null;
        $row['attachment_is_image'] = $hasFile && in_array((string) $row['attachment_mime'], ['image/jpeg', 'image/png', 'image/gif', 'image/webp'], true);
        $row['attachment_size'] = $hasFile ? (int) $row['attachment_size'] : null;
        unset($row['attachment_path']);
        return $row;
    }

    public function getCommentById(int $commentId): ?array
    {
        $stmt = $this->pdo->prepare("SELECT c.comment_id, c.ticket_id" . $this->attachmentSelect() . "
                FROM {$this->tblComments} c WHERE c.comment_id = ? LIMIT 1");
        $stmt->execute([$commentId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function getCommentsByTicketId(int $ticketId): array
    {
        $sql = "
            SELECT
                c.comment_id,
                c.ticket_id,
                c.account_id,
                c.author_role,
                CASE WHEN UPPER(c.author_role) = 'ADMIN' THEN 'Admin' ELSE c.author_name END AS author_name,
                c.comment_text,
                c.created_at" . $this->attachmentSelect() . "
            FROM {$this->tblComments} c
            WHERE c.ticket_id = :ticket_id
            ORDER BY c.created_at ASC
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':ticket_id' => $ticketId]);

        return array_map([self::class, 'presentComment'], $stmt->fetchAll(PDO::FETCH_ASSOC) ?: []);
    }

    public function getCommentsSince(int $ticketId, int $sinceCommentId): array
    {
        if ($sinceCommentId <= 0) {
            return $this->getCommentsByTicketId($ticketId);
        }

        $sql = "
            SELECT
                c.comment_id,
                c.ticket_id,
                c.account_id,
                c.author_role,
                CASE WHEN UPPER(c.author_role) = 'ADMIN' THEN 'Admin' ELSE c.author_name END AS author_name,
                c.comment_text,
                c.created_at" . $this->attachmentSelect() . "
            FROM {$this->tblComments} c
            WHERE c.ticket_id = :ticket_id
              AND c.comment_id > :since_id
            ORDER BY c.created_at ASC
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':ticket_id' => $ticketId,
            ':since_id'  => $sinceCommentId,
        ]);

        return array_map([self::class, 'presentComment'], $stmt->fetchAll(PDO::FETCH_ASSOC) ?: []);
    }

    /**
     * @param array|null $attachment ['path', 'name', 'mime', 'size'] from TicketAttachments::store()
     */
    public function addComment(int $ticketId, int $accountId, string $authorRole, string $authorName, string $commentText, ?array $attachment = null): int
    {
        $params = [
            ':ticket_id'     => $ticketId,
            ':account_id'    => $accountId,
            ':author_role'   => $authorRole,
            ':author_name'   => $authorName,
            ':comment_text'  => $commentText,
        ];
        if ($attachment !== null && $this->hasAttachmentColumns()) {
            $sql = "
                INSERT INTO {$this->tblComments}
                    (ticket_id, account_id, author_role, author_name, comment_text, created_at,
                     attachment_path, attachment_name, attachment_mime, attachment_size)
                VALUES
                    (:ticket_id, :account_id, :author_role, :author_name, :comment_text, NOW(),
                     :path, :name, :mime, :size)
            ";
            $params += [
                ':path' => $attachment['path'],
                ':name' => $attachment['name'],
                ':mime' => $attachment['mime'],
                ':size' => (int) $attachment['size'],
            ];
        } else {
            $sql = "
                INSERT INTO {$this->tblComments}
                    (ticket_id, account_id, author_role, author_name, comment_text, created_at)
                VALUES
                    (:ticket_id, :account_id, :author_role, :author_name, :comment_text, NOW())
            ";
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return (int) $this->pdo->lastInsertId();
    }

    public function ticketExists(int $ticketId): bool
    {
        $stmt = $this->pdo->prepare("SELECT 1 FROM {$this->tblTickets} WHERE ticket_id = :id LIMIT 1");
        $stmt->execute([':id' => $ticketId]);
        return (bool) $stmt->fetchColumn();
    }

    public function getTicketOwnerEmployeeId(int $ticketId): ?int
    {
        $stmt = $this->pdo->prepare("SELECT employee_id FROM {$this->tblTickets} WHERE ticket_id = :id LIMIT 1");
        $stmt->execute([':id' => $ticketId]);
        $val = $stmt->fetchColumn();
        return $val !== false ? (int) $val : null;
    }

    public function getEmployeeIdByAccountId(int $accountId): ?int
    {
        $stmt = $this->pdo->prepare("SELECT employee_id FROM {$this->tblEmployee} WHERE account_id = :account_id LIMIT 1");
        $stmt->execute([':account_id' => $accountId]);
        $val = $stmt->fetchColumn();
        return $val !== false ? (int) $val : null;
    }

    public function getAuthorDisplayInfo(int $accountId): array
    {
        $sql = "
            SELECT a.usertype, a.username, e.firstname, e.lastname
            FROM tblaccounts a
            LEFT JOIN {$this->tblEmployee} e ON a.account_id = e.account_id
            WHERE a.account_id = :account_id
            LIMIT 1
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':account_id' => $accountId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return ['role' => 'User', 'name' => 'Unknown'];
        }

        $role = strtoupper((string) ($row['usertype'] ?? 'User'));
        if ($role === 'ADMIN') {
            return ['role' => $role, 'name' => 'Admin'];
        }

        $first = trim((string) ($row['firstname'] ?? ''));
        $last = trim((string) ($row['lastname'] ?? ''));
        $name = trim($first . ' ' . $last);

        if ($name === '') {
            $name = (string) ($row['username'] ?? 'User');
        }

        return ['role' => $role, 'name' => $name];
    }
}
