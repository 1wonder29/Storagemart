<?php

require_once __DIR__ . '/admin/BaseModel.php';

class TicketCommentModel extends BaseModel
{
    protected $tblComments = 'tblticket_comments';
    protected $tblTickets = 'tbltickets';
    protected $tblEmployee = 'tblemployee';

    public function getCommentsByTicketId(int $ticketId): array
    {
        $sql = "
            SELECT
                c.comment_id,
                c.ticket_id,
                c.account_id,
                c.author_role,
                c.author_name,
                c.comment_text,
                c.created_at
            FROM {$this->tblComments} c
            WHERE c.ticket_id = :ticket_id
            ORDER BY c.created_at ASC
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':ticket_id' => $ticketId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
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
                c.author_name,
                c.comment_text,
                c.created_at
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

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function addComment(int $ticketId, int $accountId, string $authorRole, string $authorName, string $commentText): int
    {
        $sql = "
            INSERT INTO {$this->tblComments}
                (ticket_id, account_id, author_role, author_name, comment_text, created_at)
            VALUES
                (:ticket_id, :account_id, :author_role, :author_name, :comment_text, NOW())
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':ticket_id'     => $ticketId,
            ':account_id'    => $accountId,
            ':author_role'   => $authorRole,
            ':author_name'   => $authorName,
            ':comment_text'  => $commentText,
        ]);

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
        $first = trim((string) ($row['firstname'] ?? ''));
        $last = trim((string) ($row['lastname'] ?? ''));
        $name = trim($first . ' ' . $last);

        if ($name === '') {
            $name = (string) ($row['username'] ?? 'User');
        }

        return ['role' => $role, 'name' => $name];
    }
}
