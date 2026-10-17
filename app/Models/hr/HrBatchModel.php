<?php

/**
 * HR uploads waiting for approval (tblhr_batches):
 *  - STOCK: an inventory spreadsheet (add to stock / overwrite the list);
 *  - ASSIGN: a spreadsheet that issues items to employees.
 * The uploader sends the checked preview for approval; another HR account or a full-access
 * Admin approves or rejects it. Only an approved batch changes the stock or the employees' items.
 */
class HrBatchModel
{
    public const TYPES = ['STOCK', 'ASSIGN'];
    public const STATUSES = ['PENDING', 'APPLYING', 'APPROVED', 'REJECTED', 'CANCELLED', 'FAILED'];

    private PDO $pdo;

    public function __construct()
    {
        global $pdo;
        $this->pdo = $pdo;
    }

    /** False until the database update (tblhr_batches) has been imported. */
    public function ready(): bool
    {
        try {
            $this->pdo->query('SELECT 1 FROM tblhr_batches LIMIT 1');
            return true;
        } catch (Throwable $e) {
            return false;
        }
    }

    public function create(string $type, ?string $mode, string $filename, array $rows, int $skipped, string $summary, int $accountId): int
    {
        $this->pdo->prepare('INSERT INTO tblhr_batches
                (batch_type, import_mode, filename, rows_json, row_count, skipped_count, summary, status, submitted_by, submitted_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, \'PENDING\', ?, NOW())')
            ->execute([$type, $mode, mb_substr($filename, 0, 255), json_encode(array_values($rows), JSON_UNESCAPED_UNICODE),
                count($rows), $skipped, mb_substr($summary, 0, 255), $accountId]);
        return (int) $this->pdo->lastInsertId();
    }

    public function get(int $batchId): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM tblhr_batches WHERE batch_id = ? LIMIT 1');
        $stmt->execute([$batchId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return null;
        }
        $row['rows'] = json_decode((string) $row['rows_json'], true) ?: [];
        $row['submitted_name'] = $this->nameOf((int) $row['submitted_by']);
        $row['decided_name'] = $row['decided_by'] ? $this->nameOf((int) $row['decided_by']) : '';
        return $row;
    }

    /** @return array<int, array> newest first; PENDING ones first */
    public function recent(int $limit = 50): array
    {
        $stmt = $this->pdo->prepare("SELECT batch_id, batch_type, import_mode, filename, row_count, skipped_count, summary, status,
                    submitted_by, submitted_at, decided_by, decided_at, decision_note, result_note
                FROM tblhr_batches ORDER BY status = 'PENDING' DESC, batch_id DESC LIMIT " . max(1, min($limit, 200)));
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        foreach ($rows as &$row) {
            $row['submitted_name'] = $this->nameOf((int) $row['submitted_by']);
            $row['decided_name'] = $row['decided_by'] ? $this->nameOf((int) $row['decided_by']) : '';
        }
        return $rows;
    }

    public function pendingCount(): int
    {
        try {
            return (int) $this->pdo->query("SELECT COUNT(*) FROM tblhr_batches WHERE status = 'PENDING'")->fetchColumn();
        } catch (Throwable $e) {
            return 0;
        }
    }

    /** Moves a PENDING batch to a final status; false when it was no longer pending (someone else decided). */
    public function finish(int $batchId, string $status, int $accountId, string $note = '', string $result = ''): bool
    {
        $stmt = $this->pdo->prepare("UPDATE tblhr_batches
                SET status = ?, decided_by = ?, decided_at = NOW(), decision_note = ?, result_note = ?
                WHERE batch_id = ? AND status = 'PENDING'");
        $stmt->execute([$status, $accountId, $note !== '' ? mb_substr($note, 0, 255) : null, $result !== '' ? mb_substr($result, 0, 500) : null, $batchId]);
        return $stmt->rowCount() > 0;
    }

    /** Marks a batch as being processed so two approvers cannot apply it twice. */
    public function claim(int $batchId, int $accountId): bool
    {
        $stmt = $this->pdo->prepare("UPDATE tblhr_batches SET status = 'APPLYING', decided_by = ?, decided_at = NOW()
                WHERE batch_id = ? AND status = 'PENDING'");
        $stmt->execute([$accountId, $batchId]);
        return $stmt->rowCount() > 0;
    }

    public function complete(int $batchId, string $status, string $note, string $result): void
    {
        $this->pdo->prepare("UPDATE tblhr_batches SET status = ?, decision_note = ?, result_note = ?, decided_at = NOW()
                WHERE batch_id = ? AND status = 'APPLYING'")
            ->execute([$status, $note !== '' ? mb_substr($note, 0, 255) : null, $result !== '' ? mb_substr($result, 0, 500) : null, $batchId]);
    }

    /**
     * Who may approve: active HR accounts other than the uploader, and full-access Admins.
     * @return int[] account ids
     */
    public function approverAccountIds(int $exceptAccountId): array
    {
        try {
            $stmt = $this->pdo->prepare("SELECT account_id FROM tblaccounts
                    WHERE UPPER(status) = 'ACTIVE' AND account_id <> ?
                      AND (UPPER(usertype) = 'HR' OR (UPPER(usertype) = 'ADMIN' AND is_superuser = 1))");
            $stmt->execute([$exceptAccountId]);
        } catch (Throwable $e) {
            $stmt = $this->pdo->prepare("SELECT account_id FROM tblaccounts WHERE UPPER(status) = 'ACTIVE' AND account_id <> ? AND UPPER(usertype) = 'HR'");
            $stmt->execute([$exceptAccountId]);
        }
        return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN) ?: []);
    }

    /** The signed-in person may approve this batch (not their own upload). */
    public static function canDecide(array $batch): bool
    {
        $me = (int) ($_SESSION['account_id'] ?? 0);
        $role = strtoupper((string) ($_SESSION['superuser_real_usertype'] ?? $_SESSION['usertype'] ?? ''));
        return $batch['status'] === 'PENDING' && $me > 0 && $me !== (int) $batch['submitted_by'] && in_array($role, ['HR', 'ADMIN'], true);
    }

    public function nameOf(int $accountId): string
    {
        $stmt = $this->pdo->prepare("SELECT TRIM(CONCAT(COALESCE(e.firstname, ''), ' ', COALESCE(e.lastname, ''))) AS name, a.username
                FROM tblaccounts a LEFT JOIN tblemployee e ON e.account_id = a.account_id WHERE a.account_id = ? LIMIT 1");
        $stmt->execute([$accountId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return 'Unknown';
        }
        return $row['name'] !== '' ? $row['name'] : (string) $row['username'];
    }
}
