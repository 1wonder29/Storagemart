<?php
// app/Models/om/OMModel.php

require_once __DIR__ . '/../employee/Employee.php';

/**
 * OMModel - Operation Manager Model
 * Handles employee assignments to AOMs
 */
class OMModel
{
    private $pdo;

    public function __construct()
    {
        global $pdo;
        $this->pdo = $pdo;
    }

    /**
     * Get all employees with AOM assignments
     * 
     * @param int|null $omEmployeeId Filter by OM (optional)
     * @return array List of employees with their AOM assignments
     */
    public function getAllEmployeesWithAOMAssignments($omEmployeeId = null)
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
                oea.assignment_id as om_assignment_id,
                oea.is_active,
                oea.assignment_date
            FROM tblemployee e
            LEFT JOIN tblbranch b ON e.branch_id = b.branch_id
            LEFT JOIN tblom_employee_assignments oea ON e.employee_id = oea.employee_id AND oea.is_active = 1
            LEFT JOIN tblemployee aom ON oea.aom_id = aom.employee_id
        ";

        if ($omEmployeeId) {
            $query .= " WHERE oea.om_employee_id = :om_employee_id";
        }

        $query .= " ORDER BY e.firstname, e.lastname";

        $stmt = $this->pdo->prepare($query);
        
        if ($omEmployeeId) {
            $stmt->execute([':om_employee_id' => $omEmployeeId]);
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
            LEFT JOIN tblom_employee_assignments oea ON e.employee_id = oea.employee_id AND oea.is_active = 1
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
     * @param int $omEmployeeId OM's employee ID
     * @param int $employeeId Employee to assign
     * @param int $aomId AOM to assign to
     * @param string $notes Assignment notes
     * @param int $assignedBy Who assigned this
     * @return int|false Assignment ID or false on failure
     */
    public function createAssignment($omEmployeeId, $employeeId, $aomId, $notes = null, $assignedBy = null)
    {
        try {
            // Check if assignment already exists
            $checkQuery = "
                SELECT assignment_id FROM tblom_employee_assignments 
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
                INSERT INTO tblom_employee_assignments 
                (om_employee_id, employee_id, aom_id, notes, assigned_by, is_active)
                VALUES (:om_employee_id, :employee_id, :aom_id, :notes, :assigned_by, 1)
            ";

            $stmt = $this->pdo->prepare($query);
            $result = $stmt->execute([
                ':om_employee_id' => $omEmployeeId,
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
                UPDATE tblom_employee_assignments 
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
                UPDATE tblom_employee_assignments 
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
                oea.om_employee_id,
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
            FROM tblom_employee_assignments oea
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
                oea.om_employee_id,
                om.firstname as om_firstname,
                om.lastname as om_lastname,
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
            FROM tblom_employee_assignments oea
            JOIN tblemployee om ON oea.om_employee_id = om.employee_id
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
     * Get assignments managed by a specific OM
     * 
     * @param int $omEmployeeId OM's employee ID
     * @return array Assignments managed by the OM
     */
    public function getOMAssignments($omEmployeeId)
    {
        $query = "
            SELECT 
                oea.assignment_id,
                oea.om_employee_id,
                om.firstname as om_firstname,
                om.lastname as om_lastname,
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
            FROM tblom_employee_assignments oea
            JOIN tblemployee om ON oea.om_employee_id = om.employee_id
            JOIN tblemployee e ON oea.employee_id = e.employee_id
            JOIN tblemployee aom ON oea.aom_id = aom.employee_id
            LEFT JOIN tblbranch b ON CASE 
                WHEN oea.notes LIKE 'Branch ID:%' THEN CAST(TRIM(SUBSTRING_INDEX(SUBSTRING(oea.notes, 12), '|', 1)) AS UNSIGNED)
                ELSE NULL
            END = b.branch_id
            WHERE oea.om_employee_id = :om_employee_id
            ORDER BY oea.assignment_date DESC
        ";

        $stmt = $this->pdo->prepare($query);
        $stmt->execute([':om_employee_id' => $omEmployeeId]);
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
                COUNT(DISTINCT oea.om_employee_id) as om_count,
                COUNT(DISTINCT oea.aom_id) as aom_count,
                COUNT(DISTINCT oea.employee_id) as assigned_employee_count
            FROM tblom_employee_assignments oea
        ";

        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
