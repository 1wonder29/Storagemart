<?php
    require_once __DIR__. '/../admin/BaseModel.php';

class Employee extends BaseModel{
    protected $table = 'tblaccounts';
    protected $tblemployee = 'tblemployee';
    protected $tbltickets = 'tbltickets';
    protected $tblassets = 'tblassets_inventory';
    protected $tblbranch = 'tblbranch';
    protected $tblgroup = 'tblassets_group';

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }
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

    /**
     * Count assets assigned to a given employee_id
     */
    public function countAssetsByEmployee(?int $employeeId): int {
        if ($employeeId === null || $employeeId <= 0) return 0;
        try {
            $sql = "SELECT COUNT(*) FROM {$this->tblassets} WHERE employee_id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$employeeId]);
            return (int) $stmt->fetchColumn();
        } catch (\Throwable $e) {
            error_log('Employee::countAssetsByEmployee error: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Count tickets for an employee, optionally filtered by status.
     * Pass the exact status string used in DB (case sensitive depends on your DB contents).
     */
    public function countTicketsByEmployee(?int $employeeId, ?string $status = null): int {
        if ($employeeId === null || $employeeId <= 0) return 0;
        try {
            if ($status === null) {
                $sql = "SELECT COUNT(*) FROM {$this->tbltickets} WHERE employee_id = ?";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([$employeeId]);
            } else {
                $sql = "SELECT COUNT(*) FROM {$this->tbltickets} WHERE employee_id = ? AND status = ?";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([$employeeId, trim($status)]);
            }
            return (int) $stmt->fetchColumn();
        } catch (\Throwable $e) {
            error_log('Employee::countTicketsByEmployee error: ' . $e->getMessage());
            return 0;
        }
    }

    public function fetchAssetsByEmployeeId(int $employeeId): array
    {
        $sql = "
            SELECT 
                i.inventory_id,
                i.assetNumber,
                i.serialNumber,
                i.itemInfo,
                i.status,
                g.description,
                i.year_purchased,
                g.groupName
            FROM {$this->tblassets} i
            LEFT JOIN {$this->tblgroup} g ON g.group_id = i.group_id
            WHERE i.employee_id = ?
            ORDER BY i.inventory_id ASC
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$employeeId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Fetch tickets filed by a particular employee
     */
    public function fetchTicketsByEmployeeId(int $employeeId): array
    {
        $sql = "
            SELECT 
                ticket_id,
                ticket_number,
                category,
                priority,
                status,
                concern_details,
                date_filed
            FROM {$this->tbltickets}
            WHERE employee_id = ?
            ORDER BY date_filed DESC
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$employeeId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
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
    /**
     * Fetch all employees under a specific department
     * (Used by HEAD role)
     */
    public function fetchEmployeesByDepartment(string $department): array
    {
        $sql = "
            SELECT 
                e.employee_id,
                e.account_id,
                e.firstname,
                e.lastname,
                e.middlename,
                e.department,
                e.position,
                b.branchName,
                e.email,
                e.createdby,
                e.datecreated
            FROM {$this->tblemployee} e
            JOIN {$this->table} a ON a.account_id = e.account_id
            LEFT JOIN {$this->tblbranch} b ON b.branch_id = e.branch_id
            WHERE e.department = ?
              AND UPPER(a.usertype) = 'EMPLOYEE'
            ORDER BY e.lastname ASC
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$department]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function fetchTicketsByAsset(int $inventoryId): array
    {
        $sql = "
            SELECT ticket_number, category, priority, status, date_filed
            FROM {$this->tbltickets}
            WHERE inventory_id = ?
            ORDER BY date_filed DESC
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$inventoryId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }


    public function getDepartmentEmployees(string $department): array
    {
        $sql = "
            SELECT 
                e.employee_id,
                e.account_id,
                e.firstname,
                e.lastname,
                e.middlename,
                e.department,
                e.position,
                b.branchName,
                a.email,
                e.createdby,
                e.datecreated
            FROM tblemployee e
            JOIN tblaccounts a ON a.account_id = e.account_id
            LEFT JOIN tblbranch b ON b.branch_id = e.branch_id
            WHERE e.department = ?
            AND UPPER(a.usertype) = 'EMPLOYEE'
            ORDER BY e.lastname ASC
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$department]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /* =========================================================
       ASSETS & TICKETS (MODALS)
    ========================================================= */


}