<?php
// app/Models/aom/AOMModel.php

require_once __DIR__ . '/../admin/BaseModel.php';

/**
 * AOMModel - Manages Area Operation Manager (AOM) data and operations
 * Handles AOM dashboard, branch management, and employee oversight
 */
class AOMModel extends BaseModel
{
    private const OPERATIONS_DEPARTMENT = 'Operations';

    protected $tblemployee = 'tblemployee';
    protected $tblbranch = 'tblbranch';
    protected $tblbranch_assignments = 'tblbranch_assignments';
    protected $tblhom_employee_assignments = 'tblhom_employee_assignments';
    protected $tbltickets = 'tbltickets';
    protected $tblticket_history = 'tblticket_history';
    protected $tblaccounts = 'tblaccounts';
    protected $tbllogs = 'tbllogs';

    /**
     * Get all branches assigned to an AOM
     * 
     * @param int $aom_employee_id The AOM's employee ID
     * @return array Array of assigned branches
     */
    public function getAssignedBranches($aom_employee_id)
    {
        // Accessible branches are:
        // 1) branches explicitly assigned to AOM via tblbranch_assignments (active)
        // 2) branches that contain employees assigned to AOM via tblhom_employee_assignments (active)
        $sql = "
            SELECT
                x.branch_id,
                x.branchCode,
                x.branchName,
                x.branchAddress,
                MIN(x.assignment_date) AS assignment_date,
                COUNT(DISTINCT x.employee_id) AS employee_count
            FROM (
                SELECT
                    b.branch_id,
                    b.branchCode,
                    b.branchName,
                    b.branchAddress,
                    ba.assignment_date,
                    e.employee_id
                FROM {$this->tblbranch_assignments} ba
                JOIN {$this->tblbranch} b ON ba.branch_id = b.branch_id
                LEFT JOIN {$this->tblemployee} e
                    ON e.branch_id = b.branch_id
                   AND e.department = :operations_dept
                WHERE ba.aom_employee_id = :aom_employee_id
                  AND ba.is_active = 1

                UNION ALL

                SELECT
                    b.branch_id,
                    b.branchCode,
                    b.branchName,
                    b.branchAddress,
                    oea.assignment_date,
                    e.employee_id
                FROM tblhom_employee_assignments oea
                JOIN {$this->tblemployee} e ON oea.employee_id = e.employee_id
                JOIN {$this->tblbranch} b ON e.branch_id = b.branch_id
                WHERE oea.aom_id = :aom_employee_id_2
                  AND oea.is_active = 1
                  AND e.department = :operations_dept_2
            ) x
            GROUP BY x.branch_id, x.branchCode, x.branchName, x.branchAddress
            ORDER BY x.branchName ASC
        ";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'aom_employee_id' => $aom_employee_id,
            'aom_employee_id_2' => $aom_employee_id,
            'operations_dept' => self::OPERATIONS_DEPARTMENT,
            'operations_dept_2' => self::OPERATIONS_DEPARTMENT,
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get all employees in branches assigned to an AOM + directly assigned employees
     * 
     * @param int $aom_employee_id The AOM's employee ID
     * @return array Array of employees
     */
    public function getAssignedEmployees($aom_employee_id)
    {
        $sql = "
            SELECT 
                e.employee_id,
                e.firstname,
                e.lastname,
                e.middlename,
                e.email,
                e.position,
                e.department,
                b.branch_id,
                b.branchCode,
                b.branchName,
                a.usertype,
                a.status
            FROM {$this->tblemployee} e
            JOIN {$this->tblbranch} b ON e.branch_id = b.branch_id
            LEFT JOIN {$this->tblaccounts} a ON a.account_id = e.account_id
            WHERE b.branch_id IN (
                SELECT branch_id FROM {$this->tblbranch_assignments}
                WHERE aom_employee_id = :aom_employee_id AND is_active = 1
            )
            AND e.department = :operations_dept
            
            UNION
            
            SELECT 
                e.employee_id,
                e.firstname,
                e.lastname,
                e.middlename,
                e.email,
                e.position,
                e.department,
                b.branch_id,
                b.branchCode,
                b.branchName,
                a.usertype,
                a.status
            FROM {$this->tblemployee} e
            LEFT JOIN {$this->tblbranch} b ON e.branch_id = b.branch_id
            LEFT JOIN {$this->tblaccounts} a ON a.account_id = e.account_id
            WHERE e.employee_id IN (
                SELECT employee_id FROM tblhom_employee_assignments
                WHERE aom_id = :aom_employee_id_2 AND is_active = 1
            )
            AND e.department = :operations_dept_2
            
            ORDER BY branchName ASC, lastname ASC, firstname ASC
        ";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'aom_employee_id' => $aom_employee_id,
            'aom_employee_id_2' => $aom_employee_id,
            'operations_dept' => self::OPERATIONS_DEPARTMENT,
            'operations_dept_2' => self::OPERATIONS_DEPARTMENT,
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get employees in a specific assigned branch
     * 
     * @param int $aom_employee_id The AOM's employee ID
     * @param int $branch_id The branch ID
     * @return array Array of employees in that branch
     */
    public function getEmployeesByBranch($aom_employee_id, $branch_id)
    {
        // Determine whether branch access is via direct branch assignment or via OM employee assignments.
        $sql_is_branch_assigned = "
            SELECT 1 FROM {$this->tblbranch_assignments}
            WHERE aom_employee_id = :aom_employee_id
              AND branch_id = :branch_id
              AND is_active = 1
            LIMIT 1
        ";
        $stmt = $this->pdo->prepare($sql_is_branch_assigned);
        $stmt->execute(['aom_employee_id' => $aom_employee_id, 'branch_id' => $branch_id]);
        $isBranchAssigned = (bool)$stmt->fetch();

        if (!$isBranchAssigned) {
            // Verify this AOM has at least one OM-assigned employee in this branch
            $sql_verify_om = "
                SELECT 1
                FROM tblhom_employee_assignments oea
                JOIN {$this->tblemployee} e ON oea.employee_id = e.employee_id
                WHERE oea.aom_id = :aom_employee_id
                  AND oea.is_active = 1
                  AND e.branch_id = :branch_id
                  AND e.department = :operations_dept
                LIMIT 1
            ";
            $stmt = $this->pdo->prepare($sql_verify_om);
            $stmt->execute([
                'aom_employee_id' => $aom_employee_id,
                'branch_id' => $branch_id,
                'operations_dept' => self::OPERATIONS_DEPARTMENT,
            ]);
            if (!$stmt->fetch()) {
                return []; // Unauthorized access
            }
        }

        // Operations employees only. Branches via OM assignments are limited to employees assigned to this AOM.
        $sql = "
            SELECT
                e.employee_id,
                e.firstname,
                e.lastname,
                e.middlename,
                e.email,
                e.position,
                e.department,
                e.branch_id,
                b.branchName,
                a.usertype,
                a.status
            FROM {$this->tblemployee} e
            JOIN {$this->tblbranch} b ON e.branch_id = b.branch_id
            LEFT JOIN {$this->tblaccounts} a ON a.account_id = e.account_id
            WHERE e.branch_id = :branch_id
              AND e.department = :operations_dept
        ";

        if (!$isBranchAssigned) {
            $sql .= "
                AND e.employee_id IN (
                    SELECT employee_id FROM tblhom_employee_assignments
                    WHERE aom_id = :aom_employee_id AND is_active = 1
                )
            ";
        }

        $sql .= " ORDER BY e.lastname ASC, e.firstname ASC";
        
        $stmt = $this->pdo->prepare($sql);
        $params = [
            'branch_id' => $branch_id,
            'operations_dept' => self::OPERATIONS_DEPARTMENT,
        ];
        if (!$isBranchAssigned) {
            $params['aom_employee_id'] = $aom_employee_id;
        }
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get dashboard statistics for AOM
     * 
     * @param int $aom_employee_id The AOM's employee ID
     * @return array Dashboard statistics
     */
    public function getDashboardStats($aom_employee_id)
    {
        $stats = [];

        // Total assigned branches
        $sql = "SELECT COUNT(*) as total FROM {$this->tblbranch_assignments} 
                WHERE aom_employee_id = :aom_employee_id AND is_active = 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['aom_employee_id' => $aom_employee_id]);
        $stats['total_branches'] = (int)$stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Total employees across assigned branches + directly assigned
        $sql = "
            SELECT COUNT(DISTINCT employee_id) as total FROM (
                SELECT DISTINCT e.employee_id FROM {$this->tblemployee} e
                WHERE e.branch_id IN (
                    SELECT branch_id FROM {$this->tblbranch_assignments}
                    WHERE aom_employee_id = :aom_employee_id AND is_active = 1
                )
                AND e.department = :operations_dept
                
                UNION
                
                SELECT DISTINCT oea.employee_id
                FROM {$this->tblhom_employee_assignments} oea
                JOIN {$this->tblemployee} e ON oea.employee_id = e.employee_id
                WHERE oea.aom_id = :aom_employee_id_2
                  AND oea.is_active = 1
                  AND e.department = :operations_dept_2
            ) as all_employees
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'aom_employee_id' => $aom_employee_id,
            'aom_employee_id_2' => $aom_employee_id,
            'operations_dept' => self::OPERATIONS_DEPARTMENT,
            'operations_dept_2' => self::OPERATIONS_DEPARTMENT,
        ]);
        $stats['total_employees'] = (int)$stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Pending tickets
        $sql = "SELECT COUNT(*) as total FROM {$this->tbltickets}
                WHERE aom_id = :aom_employee_id AND status = 'Open'";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['aom_employee_id' => $aom_employee_id]);
        $stats['pending_tickets'] = (int)$stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // In-progress tickets
        $sql = "SELECT COUNT(*) as total FROM {$this->tbltickets}
                WHERE aom_id = :aom_employee_id AND status = 'In Progress'";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['aom_employee_id' => $aom_employee_id]);
        $stats['in_progress_tickets'] = (int)$stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Resolved tickets (this month)
        $sql = "SELECT COUNT(*) as total FROM {$this->tbltickets}
                WHERE aom_id = :aom_employee_id AND status = 'Resolved'
                AND YEAR(last_updated) = YEAR(NOW()) 
                AND MONTH(last_updated) = MONTH(NOW())";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['aom_employee_id' => $aom_employee_id]);
        $stats['resolved_this_month'] = (int)$stmt->fetch(PDO::FETCH_ASSOC)['total'];

        return $stats;
    }

    /**
     * Get all tickets for the AOM's branches
     * 
     * @param int $aom_employee_id The AOM's employee ID
     * @param int $limit Limit results
     * @param int $offset Offset
     * @return array Array of tickets
     */
    public function getAOMTickets($aom_employee_id, $limit = 50, $offset = 0)
    {
        $sql = "
            SELECT 
                t.ticket_id,
                t.ticket_number,
                t.employee_id,
                COALESCE(e.firstname, '') as firstname,
                COALESCE(e.lastname, '') as lastname,
                t.branch_id,
                b.branchName,
                t.priority,
                t.status,
                t.date_filed,
                t.last_updated,
                t.concern_details,
                t.department,
                t.category
            FROM {$this->tbltickets} t
            LEFT JOIN {$this->tblemployee} e ON t.employee_id = e.employee_id
            LEFT JOIN {$this->tblbranch} b ON b.branch_id = COALESCE(NULLIF(t.branch_id, 0), e.branch_id)
            WHERE t.branch_id IN (
                SELECT branch_id FROM {$this->tblbranch_assignments}
                WHERE aom_employee_id = :aom_employee_id AND is_active = 1
            )
            AND COALESCE(e.department, t.department) = :operations_dept
            
            UNION
            
            SELECT 
                t.ticket_id,
                t.ticket_number,
                t.employee_id,
                COALESCE(e.firstname, '') as firstname,
                COALESCE(e.lastname, '') as lastname,
                t.branch_id,
                b.branchName,
                t.priority,
                t.status,
                t.date_filed,
                t.last_updated,
                t.concern_details,
                t.department,
                t.category
            FROM {$this->tbltickets} t
            JOIN {$this->tblemployee} e ON t.employee_id = e.employee_id
            LEFT JOIN {$this->tblbranch} b ON b.branch_id = COALESCE(NULLIF(t.branch_id, 0), e.branch_id)
            WHERE t.employee_id IN (
                SELECT oea.employee_id
                FROM tblhom_employee_assignments oea
                JOIN {$this->tblemployee} emp ON oea.employee_id = emp.employee_id
                WHERE oea.aom_id = :aom_employee_id_2
                  AND oea.is_active = 1
                  AND emp.department = :operations_dept_2
            )
            
            ORDER BY date_filed DESC
            LIMIT :limit OFFSET :offset
        ";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':aom_employee_id', $aom_employee_id, PDO::PARAM_INT);
        $stmt->bindValue(':aom_employee_id_2', $aom_employee_id, PDO::PARAM_INT);
        $stmt->bindValue(':operations_dept', self::OPERATIONS_DEPARTMENT);
        $stmt->bindValue(':operations_dept_2', self::OPERATIONS_DEPARTMENT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get ticket statistics by status
     * 
     * @param int $aom_employee_id The AOM's employee ID
     * @return array Ticket statistics
     */
    public function getTicketStatsByStatus($aom_employee_id)
    {
        $sql = "
            SELECT 
                status,
                COUNT(*) as count
            FROM (
                SELECT t.status
                FROM {$this->tbltickets} t
                LEFT JOIN {$this->tblemployee} e ON t.employee_id = e.employee_id
                WHERE t.branch_id IN (
                    SELECT branch_id FROM {$this->tblbranch_assignments}
                    WHERE aom_employee_id = :aom_employee_id AND is_active = 1
                )
                AND COALESCE(e.department, t.department) = :operations_dept
                
                UNION ALL
                
                SELECT t.status
                FROM {$this->tbltickets} t
                JOIN {$this->tblemployee} e ON t.employee_id = e.employee_id
                WHERE t.employee_id IN (
                    SELECT oea.employee_id
                    FROM tblhom_employee_assignments oea
                    JOIN {$this->tblemployee} emp ON oea.employee_id = emp.employee_id
                    WHERE oea.aom_id = :aom_employee_id_2
                      AND oea.is_active = 1
                      AND emp.department = :operations_dept_2
                )
            ) as all_tickets
            GROUP BY status
        ";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'aom_employee_id' => $aom_employee_id,
            'aom_employee_id_2' => $aom_employee_id,
            'operations_dept' => self::OPERATIONS_DEPARTMENT,
            'operations_dept_2' => self::OPERATIONS_DEPARTMENT,
        ]);
        
        $result = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $result[$row['status']] = (int)$row['count'];
        }
        return $result;
    }

    /**
     * Verify if AOM has access to a specific branch
     * 
     * @param int $aom_employee_id The AOM's employee ID
     * @param int $branch_id The branch ID to verify
     * @return bool True if AOM has access
     */
    public function hasAccessToBranch($aom_employee_id, $branch_id)
    {
        // Access via direct branch assignment OR via OM-assigned employees in the branch
        $sql = "
            SELECT 1
            FROM {$this->tblbranch} b
            WHERE b.branch_id = :branch_id
              AND (
                  EXISTS (
                      SELECT 1 FROM {$this->tblbranch_assignments} ba
                      WHERE ba.aom_employee_id = :aom_employee_id
                        AND ba.branch_id = b.branch_id
                        AND ba.is_active = 1
                  )
                  OR EXISTS (
                      SELECT 1
                      FROM tblhom_employee_assignments oea
                      JOIN {$this->tblemployee} e ON oea.employee_id = e.employee_id
                      WHERE oea.aom_id = :aom_employee_id_2
                        AND oea.is_active = 1
                        AND e.branch_id = b.branch_id
                        AND e.department = :operations_dept
                  )
              )
            LIMIT 1
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'aom_employee_id' => $aom_employee_id,
            'aom_employee_id_2' => $aom_employee_id,
            'branch_id' => $branch_id,
            'operations_dept' => self::OPERATIONS_DEPARTMENT,
        ]);
        return (bool)$stmt->fetch();
    }

    /**
     * Verify if AOM has access to an employee
     * 
     * @param int $aom_employee_id The AOM's employee ID
     * @param int $employee_id The employee ID to verify
     * @return bool True if AOM has access
     */
    public function hasAccessToEmployee($aom_employee_id, $employee_id)
    {
        $sql = "
            SELECT 1
            FROM {$this->tblemployee} e
            WHERE e.employee_id = :employee_id
            AND e.department = :operations_dept
            AND (
                e.branch_id IN (
                    SELECT branch_id FROM {$this->tblbranch_assignments}
                    WHERE aom_employee_id = :aom_employee_id AND is_active = 1
                )
                OR e.employee_id IN (
                    SELECT employee_id FROM tblhom_employee_assignments
                    WHERE aom_id = :aom_employee_id_2 AND is_active = 1
                )
            )
        ";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'aom_employee_id' => $aom_employee_id,
            'aom_employee_id_2' => $aom_employee_id,
            'employee_id' => $employee_id,
            'operations_dept' => self::OPERATIONS_DEPARTMENT,
        ]);
        return (bool)$stmt->fetch();
    }

    /**
     * Update AOM branch assignments
     * Replaces all existing assignments with new ones
     * 
     * @param int $aom_employee_id The AOM's employee ID
     * @param array $branch_ids Array of branch IDs to assign
     * @param int $assigned_by Admin employee ID who made the assignment
     * @return array{success:bool,message?:string,added_branch_ids?:int[],removed_branch_ids?:int[],old_branch_ids?:int[],new_branch_ids?:int[]}
     */
    public function updateAOMBranchAssignments($aom_employee_id, $branch_ids = [], $assigned_by = null)
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT branch_id FROM {$this->tblbranch_assignments}
                WHERE aom_employee_id = :aom_employee_id AND is_active = 1
            ");
            $stmt->execute(['aom_employee_id' => $aom_employee_id]);
            $oldBranchIds = array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN) ?: []);

            $newBranchIds = array_values(array_unique(array_filter(
                array_map('intval', is_array($branch_ids) ? $branch_ids : [])
            )));

            // Deactivate all existing assignments
            $sql_deactivate = "
                UPDATE {$this->tblbranch_assignments}
                SET is_active = 0
                WHERE aom_employee_id = :aom_employee_id
            ";
            $stmt = $this->pdo->prepare($sql_deactivate);
            $stmt->execute(['aom_employee_id' => $aom_employee_id]);

            // Insert new assignments or reactivate existing ones
            if (!empty($newBranchIds)) {
                foreach ($newBranchIds as $branch_id) {
                    // Check if assignment already exists
                    $sql_check = "
                        SELECT assignment_id FROM {$this->tblbranch_assignments}
                        WHERE aom_employee_id = :aom_employee_id AND branch_id = :branch_id
                    ";
                    $stmt = $this->pdo->prepare($sql_check);
                    $stmt->execute(['aom_employee_id' => $aom_employee_id, 'branch_id' => $branch_id]);
                    $existing = $stmt->fetch(PDO::FETCH_ASSOC);

                    if ($existing) {
                        $sql_update = "
                            UPDATE {$this->tblbranch_assignments}
                            SET is_active = 1, updated_at = NOW()
                            WHERE aom_employee_id = :aom_employee_id AND branch_id = :branch_id
                        ";
                        $stmt = $this->pdo->prepare($sql_update);
                        $stmt->execute(['aom_employee_id' => $aom_employee_id, 'branch_id' => $branch_id]);
                    } else {
                        $sql_insert = "
                            INSERT INTO {$this->tblbranch_assignments}
                            (aom_employee_id, branch_id, assignment_date, is_active, assigned_by, created_at)
                            VALUES (:aom_employee_id, :branch_id, NOW(), 1, :assigned_by, NOW())
                        ";
                        $stmt = $this->pdo->prepare($sql_insert);
                        $stmt->execute([
                            'aom_employee_id' => $aom_employee_id,
                            'branch_id' => $branch_id,
                            'assigned_by' => $assigned_by
                        ]);
                    }
                }
            }

            return [
                'success' => true,
                'added_branch_ids' => array_values(array_diff($newBranchIds, $oldBranchIds)),
                'removed_branch_ids' => array_values(array_diff($oldBranchIds, $newBranchIds)),
                'old_branch_ids' => $oldBranchIds,
                'new_branch_ids' => $newBranchIds,
            ];
        } catch (Exception $e) {
            error_log("updateAOMBranchAssignments error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to update branch assignments.'];
        }
    }

    /**
     * Resolve branch names for a list of branch IDs.
     *
     * @param int[] $branchIds
     * @return string[]
     */
    public function getBranchNamesByIds(array $branchIds): array
    {
        $branchIds = array_values(array_filter(array_map('intval', $branchIds)));
        if (empty($branchIds)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($branchIds), '?'));
        $stmt = $this->pdo->prepare("
            SELECT branchName FROM {$this->tblbranch}
            WHERE branch_id IN ({$placeholders})
            ORDER BY branchName ASC
        ");
        $stmt->execute($branchIds);

        return array_map('strval', $stmt->fetchAll(PDO::FETCH_COLUMN) ?: []);
    }

    /**
     * Transfer history when HOM/OM updates AOM branch coverage.
     *
     * @return array<int, array{datelog:string,timelog:string,action:string,performedby:string,logged_at:string}>
     */
    public function getBranchAssignmentHistory(int $aomEmployeeId, int $limit = 20): array
    {
        if ($aomEmployeeId <= 0) {
            return [];
        }

        $sql = "
            SELECT datelog, timelog, action, performedby
            FROM {$this->tbllogs}
            WHERE ID = :aom_id
              AND module IN ('HOM - AOM Branches', 'OM - AOM Branches')
              AND action LIKE '[TRANSFER]%'
            ORDER BY datelog DESC, timelog DESC
            LIMIT " . (int) $limit;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['aom_id' => (string) $aomEmployeeId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        foreach ($rows as &$row) {
            $action = (string) ($row['action'] ?? '');
            $action = preg_replace('/^\[TRANSFER\]\s*/', '', $action);
            $action = preg_replace('/\s*\|\s*Details:.*$/', '', $action);
            $row['action'] = trim($action);
            $row['logged_at'] = trim(($row['datelog'] ?? '') . ' ' . ($row['timelog'] ?? ''));
        }
        unset($row);

        return $rows;
    }

    /**
     * Record branch-assignment changes on open tickets in affected branches.
     */
    public function logBranchAssignmentTicketHistory(
        array $addedBranchIds,
        array $removedBranchIds,
        int $performedByEmployeeId,
        string $performedRole,
        string $aomDisplayName,
        string $performedByDisplayName
    ): void {
        $this->logBranchTicketHistoryForIds(
            $addedBranchIds,
            'added',
            $performedByEmployeeId,
            $performedRole,
            $aomDisplayName,
            $performedByDisplayName
        );
        $this->logBranchTicketHistoryForIds(
            $removedBranchIds,
            'removed',
            $performedByEmployeeId,
            $performedRole,
            $aomDisplayName,
            $performedByDisplayName
        );
    }

    /**
     * @param int[] $branchIds
     */
    private function logBranchTicketHistoryForIds(
        array $branchIds,
        string $changeType,
        int $performedByEmployeeId,
        string $performedRole,
        string $aomDisplayName,
        string $performedByDisplayName
    ): void {
        $branchIds = array_values(array_filter(array_map('intval', $branchIds)));
        if (empty($branchIds)) {
            return;
        }

        $branchNameMap = [];
        $placeholders = implode(',', array_fill(0, count($branchIds), '?'));
        $stmt = $this->pdo->prepare("
            SELECT branch_id, branchName FROM {$this->tblbranch}
            WHERE branch_id IN ({$placeholders})
        ");
        $stmt->execute($branchIds);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) ?: [] as $branch) {
            $branchNameMap[(int) $branch['branch_id']] = (string) ($branch['branchName'] ?? 'Branch');
        }

        $ticketStmt = $this->pdo->prepare("
            SELECT t.ticket_id, t.status
            FROM {$this->tbltickets} t
            LEFT JOIN {$this->tblemployee} e ON t.employee_id = e.employee_id
            WHERE COALESCE(NULLIF(t.branch_id, 0), e.branch_id) = :branch_id
              AND t.status NOT IN ('Closed', 'Cancelled')
        ");

        $historyStmt = $this->pdo->prepare("
            INSERT INTO {$this->tblticket_history}
                (ticket_id, action_type, action_details, old_status, new_status, performed_by, performed_role, date_logged)
            VALUES
                (:ticket_id, :action_type, :action_details, :old_status, :new_status, :performed_by, :performed_role, NOW())
        ");

        foreach ($branchIds as $branchId) {
            $branchName = $branchNameMap[$branchId] ?? 'Branch';
            $actionType = 'Branch Assignment';
            $actionDetails = $changeType === 'added'
                ? sprintf(
                    'AOM branch coverage added: %s assigned to %s by %s',
                    $branchName,
                    $aomDisplayName,
                    $performedByDisplayName
                )
                : sprintf(
                    'AOM branch coverage removed: %s unassigned from %s by %s',
                    $branchName,
                    $aomDisplayName,
                    $performedByDisplayName
                );

            $ticketStmt->execute(['branch_id' => $branchId]);
            $tickets = $ticketStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

            foreach ($tickets as $ticket) {
                $status = (string) ($ticket['status'] ?? 'Open');
                $historyStmt->execute([
                    'ticket_id' => (int) $ticket['ticket_id'],
                    'action_type' => $actionType,
                    'action_details' => $actionDetails,
                    'old_status' => $status,
                    'new_status' => $status,
                    'performed_by' => $performedByEmployeeId,
                    'performed_role' => $performedRole,
                ]);
            }
        }
    }

    /**
     * Get all branch assignments for an AOM (including inactive)
     * 
     * @param int $aom_employee_id The AOM's employee ID
     * @return array Array of branch assignments
     */
    public function getAllBranchAssignments($aom_employee_id)
    {
        $sql = "
            SELECT 
                ba.assignment_id,
                b.branch_id,
                b.branchCode,
                b.branchName,
                ba.assignment_date,
                ba.is_active
            FROM {$this->tblbranch_assignments} ba
            JOIN {$this->tblbranch} b ON ba.branch_id = b.branch_id
            WHERE ba.aom_employee_id = :aom_employee_id
            ORDER BY b.branchName ASC
        ";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['aom_employee_id' => $aom_employee_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get assets assigned to employees within the AOM's scope (excludes the AOM).
     *
     * @param int $aom_employee_id
     * @param int|null $branch_id Optional branch filter
     * @return array
     */
    public function getAOMTeamAssets(int $aom_employee_id, ?int $branch_id = null): array
    {
        $sql = "
            SELECT
                i.inventory_id,
                i.assetNumber,
                i.serialNumber,
                i.itemInfo,
                i.status,
                g.description,
                g.groupName,
                e.employee_id,
                e.firstname,
                e.lastname,
                e.department,
                b.branch_id,
                b.branchName
            FROM tblassets_inventory i
            LEFT JOIN tblassets_group g ON g.group_id = i.group_id
            INNER JOIN tblemployee e ON e.employee_id = i.employee_id
            LEFT JOIN {$this->tblbranch} b ON e.branch_id = b.branch_id
            WHERE i.employee_id != :exclude_aom_id
              AND i.employee_id IN (
                    SELECT scoped.employee_id FROM (
                        SELECT e2.employee_id
                        FROM {$this->tblemployee} e2
                        WHERE e2.branch_id IN (
                            SELECT branch_id FROM {$this->tblbranch_assignments}
                            WHERE aom_employee_id = :aom_id AND is_active = 1
                        )
                        AND e2.department = :dept1

                        UNION

                        SELECT e3.employee_id
                        FROM {$this->tblemployee} e3
                        WHERE e3.employee_id IN (
                            SELECT employee_id FROM {$this->tblhom_employee_assignments}
                            WHERE aom_id = :aom_id_2 AND is_active = 1
                        )
                        AND e3.department = :dept2
                    ) scoped
                )
        ";

        $params = [
            'exclude_aom_id' => $aom_employee_id,
            'aom_id' => $aom_employee_id,
            'aom_id_2' => $aom_employee_id,
            'dept1' => self::OPERATIONS_DEPARTMENT,
            'dept2' => self::OPERATIONS_DEPARTMENT,
        ];

        if ($branch_id !== null && $branch_id > 0) {
            $sql .= ' AND b.branch_id = :branch_id';
            $params['branch_id'] = $branch_id;
        }

        $sql .= ' ORDER BY b.branchName ASC, e.lastname ASC, e.firstname ASC, i.assetNumber ASC';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}
