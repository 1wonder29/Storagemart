<?php
require_once __DIR__ . '/admin/BaseModel.php';

/**
 * "Forgot password" requests. The login page never changes a password itself:
 * it files a request, and an Admin verifies the person and sets a temporary
 * password from Users & Employees (TMS policy: IT/Admin restore access after
 * verifying the user's identity).
 */
class PasswordResetRequest extends BaseModel
{
    private static bool $ready = false;

    public function hasPending(int $accountId): bool
    {
        $this->ensureTable();
        $stmt = $this->pdo->prepare("SELECT 1 FROM tblpassword_reset_requests WHERE account_id = ? AND status = 'PENDING' LIMIT 1");
        $stmt->execute([$accountId]);
        return (bool) $stmt->fetchColumn();
    }

    public function create(int $accountId, string $note, string $ip): int
    {
        $this->ensureTable();
        $stmt = $this->pdo->prepare(
            "INSERT INTO tblpassword_reset_requests (account_id, note, ip_address, status, requested_at)
             VALUES (?, ?, ?, 'PENDING', NOW())"
        );
        $note = function_exists('mb_substr') ? mb_substr($note, 0, 255) : substr($note, 0, 255);
        $stmt->execute([$accountId, $note, substr($ip, 0, 45)]);
        return (int) $this->pdo->lastInsertId();
    }

    /** Pending requests with the person's name, newest first. */
    public function fetchPending(): array
    {
        try {
            $this->ensureTable();
            $stmt = $this->pdo->query(
                "SELECT r.request_id, r.account_id, r.note, r.requested_at, a.username, a.status,
                        e.employee_id, e.firstname, e.lastname, e.position
                 FROM tblpassword_reset_requests r
                 JOIN tblaccounts a ON a.account_id = r.account_id
                 LEFT JOIN tblemployee e ON e.account_id = r.account_id
                 WHERE r.status = 'PENDING'
                 ORDER BY r.requested_at DESC"
            );
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Throwable $e) {
            error_log('PasswordResetRequest::fetchPending: ' . $e->getMessage());
            return [];
        }
    }

    /** Marks every pending request for this account as done (a new password was set). */
    public function resolveForAccount(int $accountId, string $resolvedBy): void
    {
        try {
            $this->ensureTable();
            $this->pdo->prepare(
                "UPDATE tblpassword_reset_requests SET status = 'RESOLVED', resolved_by = ?, resolved_at = NOW()
                 WHERE account_id = ? AND status = 'PENDING'"
            )->execute([$resolvedBy, $accountId]);
        } catch (Throwable $e) {
            error_log('PasswordResetRequest::resolveForAccount: ' . $e->getMessage());
        }
    }

    public function dismiss(int $requestId, string $resolvedBy): bool
    {
        $this->ensureTable();
        $stmt = $this->pdo->prepare(
            "UPDATE tblpassword_reset_requests SET status = 'DISMISSED', resolved_by = ?, resolved_at = NOW()
             WHERE request_id = ? AND status = 'PENDING'"
        );
        $stmt->execute([$resolvedBy, $requestId]);
        return $stmt->rowCount() > 0;
    }

    /** @return int[] active Administrator accounts, who receive the request notifications */
    public function adminAccountIds(): array
    {
        $stmt = $this->pdo->query("SELECT account_id FROM tblaccounts WHERE UPPER(usertype) = 'ADMIN' AND UPPER(status) = 'ACTIVE'");
        return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
    }

    private function ensureTable(): void
    {
        if (self::$ready) {
            return;
        }
        $this->pdo->exec(
            "CREATE TABLE IF NOT EXISTS tblpassword_reset_requests (
                request_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                account_id INT NOT NULL,
                note VARCHAR(255) NOT NULL DEFAULT '',
                ip_address VARCHAR(45) NOT NULL DEFAULT '',
                status VARCHAR(20) NOT NULL DEFAULT 'PENDING',
                requested_at DATETIME NOT NULL,
                resolved_by VARCHAR(100) NULL,
                resolved_at DATETIME NULL,
                KEY idx_reset_account (account_id),
                KEY idx_reset_status (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
        );
        self::$ready = true;
    }
}
