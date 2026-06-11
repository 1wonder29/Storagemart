<?php
// app/Models/hom/HOMModel.php

require_once __DIR__ . '/../employee/Employee.php';

/**
 * HOMModel - Head Of Operation Model
 * Handles employee assignments to AOMs
 */
class HOMModel
{
    private $pdo;

    public function __construct()
    {
        global $pdo;
        $this->pdo = $pdo;
    }

    /**
     * Get all Operations employees and HOM staff for branch management.
     *
     * @return array
     */
    public function getOperationsEmployees()
    {
        $query = "
            SELECT
                e.employee_id,
                e.firstname,
                e.lastname,
                e.email,
                e.position,
                e.department,
                e.branch_id,
                b.branchName,
                b.branchCode,
                a.usertype,
                a.status,
                aom.employee_id AS aom_id,
                aom.firstname AS aom_firstname,
                aom.lastname AS aom_lastname,
                oea.assignment_id AS hom_assignment_id,
                oea.is_active,
                oea.assignment_date
            FROM tblemployee e
            JOIN tblaccounts a ON e.account_id = a.account_id
            LEFT JOIN tblbranch b ON e.branch_id = b.branch_id
            LEFT JOIN tblhom_employee_assignments oea ON e.employee_id = oea.employee_id AND oea.is_active = 1
            LEFT JOIN tblemployee aom ON oea.aom_id = aom.employee_id
            WHERE UPPER(a.status) = 'ACTIVE'
              AND (
                    e.department = 'Operations'
                    OR UPPER(a.usertype) = 'HOM'
              )
            ORDER BY e.lastname, e.firstname
        ";

        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Transfer an employee to a different branch.
     *
     * @param int $employeeId
     * @param int $newBranchId
     * @return array{success:bool,message:string,old_branch_id?:int,new_branch_id?:int,employee_name?:string,old_branch_name?:string,new_branch_name?:string}
     */
    public function transferEmployeeBranch(int $employeeId, int $newBranchId)
    {
        if ($employeeId <= 0 || $newBranchId <= 0) {
            return ['success' => false, 'message' => 'Invalid employee or branch.'];
        }

        $employeeQuery = "
            SELECT
                e.employee_id,
                e.firstname,
                e.lastname,
                e.branch_id,
                e.department,
                b.branchName AS current_branch_name,
                a.usertype
            FROM tblemployee e
            JOIN tblaccounts a ON e.account_id = a.account_id
            LEFT JOIN tblbranch b ON e.branch_id = b.branch_id
            WHERE e.employee_id = :employee_id
              AND UPPER(a.status) = 'ACTIVE'
              AND (
                    e.department = 'Operations'
                    OR UPPER(a.usertype) = 'HOM'
              )
            LIMIT 1
        ";

        $stmt = $this->pdo->prepare($employeeQuery);
        $stmt->execute([':employee_id' => $employeeId]);
        $employee = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$employee) {
            return ['success' => false, 'message' => 'Employee not found or not eligible for branch transfer.'];
        }

        $oldBranchId = (int) ($employee['branch_id'] ?? 0);
        if ($oldBranchId === $newBranchId) {
            return ['success' => false, 'message' => 'Employee is already assigned to this branch.'];
        }

        $branchStmt = $this->pdo->prepare('SELECT branch_id, branchName FROM tblbranch WHERE branch_id = :branch_id LIMIT 1');
        $branchStmt->execute([':branch_id' => $newBranchId]);
        $newBranch = $branchStmt->fetch(PDO::FETCH_ASSOC);

        if (!$newBranch) {
            return ['success' => false, 'message' => 'Selected branch does not exist.'];
        }

        try {
            $updateStmt = $this->pdo->prepare('UPDATE tblemployee SET branch_id = :branch_id WHERE employee_id = :employee_id LIMIT 1');
            $updated = $updateStmt->execute([
                ':branch_id' => $newBranchId,
                ':employee_id' => $employeeId,
            ]);

            if (!$updated) {
                return ['success' => false, 'message' => 'Failed to update employee branch.'];
            }

            return [
                'success' => true,
                'message' => 'Employee transferred successfully.',
                'old_branch_id' => $oldBranchId,
                'new_branch_id' => $newBranchId,
                'employee_name' => trim(($employee['firstname'] ?? '') . ' ' . ($employee['lastname'] ?? '')),
                'old_branch_name' => $employee['current_branch_name'] ?? 'N/A',
                'new_branch_name' => $newBranch['branchName'] ?? 'N/A',
            ];
        } catch (Exception $e) {
            error_log('HOMModel::transferEmployeeBranch error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred while transferring the employee.'];
        }
    }

    /**
     * Get all employees with AOM assignments
     * 
     * @param int|null $homEmployeeId Filter by HOM (optional)
     * @return array List of employees with their AOM assignments
     */
    public function getAllEmployeesWithAOMAssignments($homEmployeeId = null)
    {
        $query = "
            SELECT 
                e.employee_id,
                e.firstname,
                e.lastname,
                e.email,
                e.position,
                e.department,
                e.branch_id,
                b.branchName,
                aom.employee_id as aom_id,
                aom.firstname as aom_firstname,
                aom.lastname as aom_lastname,
                oea.assignment_id as hom_assignment_id,
                oea.is_active,
                oea.assignment_date
            FROM tblemployee e
            LEFT JOIN tblbranch b ON e.branch_id = b.branch_id
            LEFT JOIN tblhom_employee_assignments oea ON e.employee_id = oea.employee_id AND oea.is_active = 1
            LEFT JOIN tblemployee aom ON oea.aom_id = aom.employee_id
        ";

        if ($homEmployeeId) {
            $query .= " WHERE oea.hom_employee_id = :hom_employee_id";
        }

        $query .= " ORDER BY e.firstname, e.lastname";

        $stmt = $this->pdo->prepare($query);
        
        if ($homEmployeeId) {
            $stmt->execute([':hom_employee_id' => $homEmployeeId]);
        } else {
            $stmt->execute();
        }

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get unassigned employees (not assigned to any AOM)
     * 
     * @return array List of employees without AOM assignments
     */
    public function getUnassignedEmployees()
    {
        $query = "
            SELECT 
                e.employee_id,
                e.firstname,
                e.lastname,
                e.email,
                e.position,
                e.department,
                b.branchName
            FROM tblemployee e
            LEFT JOIN tblbranch b ON e.branch_id = b.branch_id
            LEFT JOIN tblaccounts a ON a.account_id = e.account_id
            LEFT JOIN tblhom_employee_assignments oea ON e.employee_id = oea.employee_id AND oea.is_active = 1
            WHERE oea.assignment_id IS NULL
            AND a.usertype = 'EMPLOYEE'
            AND UPPER(a.status) = 'ACTIVE'
            ORDER BY e.firstname, e.lastname
        ";

        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get all active AOMs for assignment dropdown
     * 
     * @return array List of active AOMs
     */
    public function getAllActiveAOMs()
    {
        $query = "
            SELECT 
                e.employee_id,
                e.firstname,
                e.lastname,
                e.email
            FROM tblemployee e
            LEFT JOIN tblaccounts a ON a.account_id = e.account_id
            WHERE a.usertype = 'AOM'
            AND UPPER(a.status) = 'ACTIVE'
            ORDER BY e.firstname, e.lastname
        ";

        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get all active branches
     * 
     * @return array List of active branches
     */
    public function getAllBranches()
    {
        $query = "
            SELECT 
                branch_id,
                branchCode,
                branchName
            FROM tblbranch
            ORDER BY branchName ASC
        ";

        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Assign an employee to an AOM
     * 
     * @param int $homEmployeeId HOM's employee ID
     * @param int $employeeId Employee to assign
     * @param int $aomId AOM to assign to
     * @param string $notes Assignment notes
     * @param int $assignedBy Who assigned this
     * @return int|false Assignment ID or false on failure
     */
    public function createAssignment($homEmployeeId, $employeeId, $aomId, $notes = null, $assignedBy = null)
    {
        try {
            // Check if assignment already exists
            $checkQuery = "
                SELECT assignment_id FROM tblhom_employee_assignments 
                WHERE employee_id = :employee_id AND aom_id = :aom_id AND is_active = 1
            ";
            $stmt = $this->pdo->prepare($checkQuery);
            $stmt->execute([
                ':employee_id' => $employeeId,
                ':aom_id' => $aomId
            ]);

            if ($stmt->rowCount() > 0) {
                return false; // Assignment already exists
            }

            // Create new assignment
            $query = "
                INSERT INTO tblhom_employee_assignments 
                (hom_employee_id, employee_id, aom_id, notes, assigned_by, is_active)
                VALUES (:hom_employee_id, :employee_id, :aom_id, :notes, :assigned_by, 1)
            ";

            $stmt = $this->pdo->prepare($query);
            $result = $stmt->execute([
                ':hom_employee_id' => $homEmployeeId,
                ':employee_id' => $employeeId,
                ':aom_id' => $aomId,
                ':notes' => $notes,
                ':assigned_by' => $assignedBy
            ]);

            return $result ? $this->pdo->lastInsertId() : false;
        } catch (Exception $e) {
            error_log('Error creating assignment: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Update an assignment
     * 
     * @param int $assignmentId Assignment ID
     * @param int $aomId New AOM ID
     * @param string $notes Updated notes
     * @return bool Success status
     */
    public function updateAssignment($assignmentId, $aomId, $notes = null)
    {
        try {
            $query = "
                UPDATE tblhom_employee_assignments 
                SET aom_id = :aom_id, notes = :notes, updated_at = NOW()
                WHERE assignment_id = :assignment_id
            ";

            $stmt = $this->pdo->prepare($query);
            return $stmt->execute([
                ':aom_id' => $aomId,
                ':notes' => $notes,
                ':assignment_id' => $assignmentId
            ]);
        } catch (Exception $e) {
            error_log('Error updating assignment: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Deactivate an assignment
     * 
     * @param int $assignmentId Assignment ID
     * @return bool Success status
     */
    public function deactivateAssignment($assignmentId)
    {
        try {
            $query = "
                UPDATE tblhom_employee_assignments 
                SET is_active = 0, updated_at = NOW()
                WHERE assignment_id = :assignment_id
            ";

            $stmt = $this->pdo->prepare($query);
            return $stmt->execute([':assignment_id' => $assignmentId]);
        } catch (Exception $e) {
            error_log('Error deactivating assignment: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get assignment by ID
     * 
     * @param int $assignmentId Assignment ID
     * @return array|false Assignment details or false
     */
    public function getAssignmentById($assignmentId)
    {
        $query = "
            SELECT 
                oea.assignment_id,
                oea.hom_employee_id,
                oea.employee_id,
                oea.aom_id,
                oea.assignment_date,
                oea.is_active,
                oea.notes,
                e.firstname as employee_firstname,
                e.lastname as employee_lastname,
                e.email as employee_email,
                aom.firstname as aom_firstname,
                aom.lastname as aom_lastname,
                aom.email as aom_email,
                CASE 
                    WHEN oea.notes LIKE 'Branch ID:%' THEN CAST(TRIM(SUBSTRING_INDEX(SUBSTRING(oea.notes, 12), '|', 1)) AS UNSIGNED)
                    ELSE NULL
                END as branch_id,
                COALESCE(b.branchName, 'N/A') as branch_name,
                COALESCE(b.branchCode, '') as branch_code
            FROM tblhom_employee_assignments oea
            JOIN tblemployee e ON oea.employee_id = e.employee_id
            JOIN tblemployee aom ON oea.aom_id = aom.employee_id
            LEFT JOIN tblbranch b ON CASE 
                WHEN oea.notes LIKE 'Branch ID:%' THEN CAST(TRIM(SUBSTRING_INDEX(SUBSTRING(oea.notes, 12), '|', 1)) AS UNSIGNED)
                ELSE NULL
            END = b.branch_id
            WHERE oea.assignment_id = :assignment_id
        ";

        $stmt = $this->pdo->prepare($query);
        $stmt->execute([':assignment_id' => $assignmentId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get assignments for a specific employee
     * 
     * @param int $employeeId Employee ID
     * @return array Assignments for the employee
     */
    public function getEmployeeAssignments($employeeId)
    {
        $query = "
            SELECT 
                oea.assignment_id,
                oea.hom_employee_id,
                hom.firstname as hom_firstname,
                hom.lastname as hom_lastname,
                oea.employee_id,
                e.firstname as employee_firstname,
                e.lastname as employee_lastname,
                oea.aom_id,
                aom.firstname as aom_firstname,
                aom.lastname as aom_lastname,
                aom.email as aom_email,
                oea.is_active,
                oea.assignment_date,
                oea.notes
            FROM tblhom_employee_assignments oea
            JOIN tblemployee hom ON oea.hom_employee_id = hom.employee_id
            JOIN tblemployee e ON oea.employee_id = e.employee_id
            JOIN tblemployee aom ON oea.aom_id = aom.employee_id
            WHERE oea.employee_id = :employee_id
            ORDER BY oea.assignment_date DESC
        ";

        $stmt = $this->pdo->prepare($query);
        $stmt->execute([':employee_id' => $employeeId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get assignments managed by a specific HOM
     * 
     * @param int $homEmployeeId HOM's employee ID
     * @return array Assignments managed by the HOM
     */
    public function getHOMAssignments($homEmployeeId)
    {
        $query = "
            SELECT 
                oea.assignment_id,
                oea.hom_employee_id,
                hom.firstname as hom_firstname,
                hom.lastname as hom_lastname,
                oea.employee_id,
                e.firstname as employee_firstname,
                e.lastname as employee_lastname,
                e.email as employee_email,
                e.position,
                oea.aom_id,
                aom.firstname as aom_firstname,
                aom.lastname as aom_lastname,
                aom.email as aom_email,
                CASE 
                    WHEN oea.notes LIKE 'Branch ID:%' THEN CAST(TRIM(SUBSTRING_INDEX(SUBSTRING(oea.notes, 12), '|', 1)) AS UNSIGNED)
                    ELSE NULL
                END as branch_id,
                COALESCE(b.branchName, 'N/A') as branch_name,
                COALESCE(b.branchCode, '') as branch_code,
                oea.is_active,
                oea.assignment_date,
                CASE 
                    WHEN oea.notes LIKE 'Branch ID:%' THEN TRIM(SUBSTRING(oea.notes, POSITION('|' IN oea.notes) + 2))
                    ELSE oea.notes
                END as notes
            FROM tblhom_employee_assignments oea
            JOIN tblemployee hom ON oea.hom_employee_id = hom.employee_id
            JOIN tblemployee e ON oea.employee_id = e.employee_id
            JOIN tblemployee aom ON oea.aom_id = aom.employee_id
            LEFT JOIN tblbranch b ON CASE 
                WHEN oea.notes LIKE 'Branch ID:%' THEN CAST(TRIM(SUBSTRING_INDEX(SUBSTRING(oea.notes, 12), '|', 1)) AS UNSIGNED)
                ELSE NULL
            END = b.branch_id
            WHERE oea.hom_employee_id = :hom_employee_id
            ORDER BY oea.assignment_date DESC
        ";

        $stmt = $this->pdo->prepare($query);
        $stmt->execute([':hom_employee_id' => $homEmployeeId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get assignment statistics
     * 
     * @return array Statistics about assignments
     */
    public function getAssignmentStats()
    {
        $query = "
            SELECT 
                COUNT(DISTINCT oea.assignment_id) as total_assignments,
                COUNT(DISTINCT CASE WHEN oea.is_active = 1 THEN oea.assignment_id END) as active_assignments,
                COUNT(DISTINCT oea.hom_employee_id) as hom_count,
                COUNT(DISTINCT oea.aom_id) as aom_count,
                COUNT(DISTINCT oea.employee_id) as assigned_employee_count
            FROM tblhom_employee_assignments oea
        ";

        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
