<?php 

require_once __DIR__ . '/../admin/BaseModel.php';

class IT extends BaseModel{
    protected $table = 'tblaccounts';
    protected $tblemployee = 'tblemployee';
    protected $tbltickets = 'tbltickets';
    protected $tblassets = 'tblassets_inventory';
    protected $tblbranch = 'tblbranch';
    protected $tblgroup = 'tblassets_group';

    public function fetchUserDetails(int $accountID): ?array {
        try {
            $sql = "SELECT e.employee_id, e.firstname, e.position, a.usertype
                    FROM {$this->table} a
                    LEFT JOIN {$this->tblemployee} e ON a.account_id = e.account_id
                    WHERE a.account_id = ? LIMIT 1";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$accountID]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (\Throwable $e) {
            error_log('Employee::fetchUserDetails error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Convenience: get employee_id from account_id
     */
    public function getEmployeeIdByAccountId(int $accountId): ?int {
        try {
            $stmt = $this->pdo->prepare("SELECT employee_id FROM {$this->tblemployee} WHERE account_id = ? LIMIT 1");
            $stmt->execute([$accountId]);
            $val = $stmt->fetchColumn();
            return $val ? (int)$val : null;
        } catch (\Throwable $e) {
            error_log('Employee::getEmployeeIdByAccountId error: ' . $e->getMessage());
            return null;
        }
    }

    public function countTicketsAssignedToMe(int $employeeId, ?string $status = null): int
    {
        if ($employeeId <= 0) {
            return 0;
        }

        try {
            $sql = "SELECT COUNT(*) 
                    FROM {$this->tbltickets}
                    WHERE assigned_to = ?";

            $params = [$employeeId];

            // optional status filter
            if ($status !== null) {
                $sql .= " AND status = ?";
                $params[] = $status;
            }

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);

            return (int) $stmt->fetchColumn();
        } catch (\Throwable $e) {
            error_log('IT::countTicketsAssignedToMe error: ' . $e->getMessage());
            return 0;
        }
    }

    public function countAssetbyEmployeeId(int $employeeId): int
    {
        if ($employeeId <= 0) {
            return 0;
        }

        try {
            $sql = "SELECT COUNT(*) 
                    FROM {$this->tblassets}
                    WHERE employee_id = ?";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$employeeId]);

            return (int) $stmt->fetchColumn();
        } catch (\Throwable $e) {
            error_log('IT::countAssetbyEmployeeId error: ' . $e->getMessage());
            return 0;
        }
    }

    public function countTicketByEmployeeId(int $employeeId, ?string $status = null): int{
        if ($employeeId <= 0) {
            return 0;
        }

        try {
            $sql = "SELECT COUNT(*) 
                    FROM {$this->tbltickets}
                    WHERE employee_id = ?";

            $params = [$employeeId];

            // optional status filter
            if ($status !== null) {
                $sql .= " AND status = ?";
                $params[] = $status;
            }

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);

            return (int) $stmt->fetchColumn();
        } catch (\Throwable $e) {
            error_log('IT::countTicketByEmployeeId error: ' . $e->getMessage());
            return 0;
        }
    }
    
    public function getEmployeeById(int $employeeId): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT *
            FROM tblemployee
            WHERE employee_id = ?
            LIMIT 1
        ");
        $stmt->execute([$employeeId]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function fetchProfileByAccountId(int $accountId): ?array
    {
        try {
            $sql = "SELECT
                        a.account_id,
                        a.username,
                        a.usertype,
                        a.status,
                        a.datecreated AS account_datecreated,
                        e.employee_id,
                        e.firstname,
                        e.lastname,
                        e.middlename,
                        e.department,
                        e.position,
                        e.email,
                        b.branchName
                    FROM {$this->table} a
                    LEFT JOIN {$this->tblemployee} e ON a.account_id = e.account_id
                    LEFT JOIN {$this->tblbranch} b ON e.branch_id = b.branch_id
                    WHERE a.account_id = ?
                    LIMIT 1";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$accountId]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (\Throwable $e) {
            error_log('IT::fetchProfileByAccountId error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get all uploaded files organized by date
     * @return array Grouped by DATE(date_uploaded) with files and counts
     */
    public function getUploadsByDate(): array {
        try {
            $sql = "SELECT 
                        DATE(tu.date_uploaded) as upload_date,
                        tu.upload_id,
                        tu.ticket_id,
                        tu.original_filename,
                        tu.stored_filename,
                        tu.file_size,
                        tu.file_type,
                        tu.date_uploaded,
                        e.firstname as uploaded_by_name,
                        e.employee_id as uploaded_by_id,
                        t.ticket_number
                    FROM tblticket_uploads tu
                    JOIN tblemployee e ON tu.uploaded_by = e.employee_id
                    JOIN tbltickets t ON tu.ticket_id = t.ticket_id
                    WHERE tu.is_active = 1
                    ORDER BY tu.date_uploaded DESC";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Group by date
            $grouped = [];
            foreach ($results as $row) {
                $date = $row['upload_date'];
                if (!isset($grouped[$date])) {
                    $grouped[$date] = [];
                }
                $grouped[$date][] = $row;
            }

            return $grouped;
        } catch (\Throwable $e) {
            error_log('IT::getUploadsByDate error: ' . $e->getMessage());
            return [];
        }
    }
}