<?php

require_once __DIR__ . '/HRModel.php';

/**
 * HR EmployeeModel - View employee data and accountability tracking
 * HR can view all employees and their assigned assets
 */
class EmployeeModel extends HRModel {

    public function __construct() {
        parent::__construct();
    }

    /**
     * Get all employees with pagination
     * @param int $offset - pagination offset
     * @param int $limit - records per page
     * @return array
     */
    public function getAllEmployees(int $offset = 0, int $limit = 20): array {
        try {
            $sql = "SELECT 
                        e.employee_id,
                        e.account_id,
                        e.firstname,
                        e.lastname,
                        e.middlename,
                        e.position,
                        e.department,
                        e.email,
                        b.branchName,
                        a.usertype,
                        a.status
                    FROM {$this->tblemployee} e
                    LEFT JOIN {$this->tblbranch} b ON e.branch_id = b.branch_id
                    LEFT JOIN {$this->tblaccounts} a ON e.account_id = a.account_id
                    ORDER BY e.lastname, e.firstname
                    LIMIT ? OFFSET ?";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(1, $limit, PDO::PARAM_INT);
            $stmt->bindValue(2, $offset, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
            error_log('EmployeeModel::getAllEmployees error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get total employee count for pagination
     * @return int
     */
    public function getTotalEmployeeCount(): int {
        try {
            $sql = "SELECT COUNT(*) FROM {$this->tblemployee}";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            return (int) $stmt->fetchColumn();
        } catch (\Throwable $e) {
            error_log('EmployeeModel::getTotalEmployeeCount error: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get detailed employee information
     * @param int $employeeId
     * @return array|null
     */
    public function getEmployeeDetail(int $employeeId): ?array {
        try {
            $sql = "SELECT 
                        e.employee_id,
                        e.account_id,
                        e.firstname,
                        e.lastname,
                        e.middlename,
                        e.position,
                        e.department,
                        e.email,
                        e.datecreated,
                        b.branchName,
                        b.branch_id,
                        a.usertype,
                        a.status,
                        a.username
                    FROM {$this->tblemployee} e
                    LEFT JOIN {$this->tblbranch} b ON e.branch_id = b.branch_id
                    LEFT JOIN {$this->tblaccounts} a ON e.account_id = a.account_id
                    WHERE e.employee_id = ?
                    LIMIT 1";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$employeeId]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (\Throwable $e) {
            error_log('EmployeeModel::getEmployeeDetail error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get all IT assets assigned to an employee
     * Grouped by asset type/category
     * @param int $employeeId
     * @return array
     */
    public function getEmployeeAssets(int $employeeId): array {
        try {
            $sql = "SELECT 
                        ai.inventory_id,
                        ai.assetCode,
                        ai.assetNumber,
                        ai.itemInfo,
                        ai.serialNumber,
                        ai.status as asset_status,
                        ai.year_purchased,
                        ag.groupName,
                        ac.categoryName,
                        aa.dateIssued,
                        aa.dateReturned
                    FROM {$this->tblassets_inventory} ai
                    LEFT JOIN {$this->tblassets_assignment} aa ON ai.inventory_id = aa.inventory_id
                    LEFT JOIN {$this->tblassets_group} ag ON ai.group_id = ag.group_id
                    LEFT JOIN {$this->tblassets_category} ac ON ag.category_id = ac.category_id
                    WHERE ai.employee_id = ? OR (aa.employee_id = ? AND aa.dateReturned IS NULL)
                    ORDER BY ac.categoryName, ag.groupName";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$employeeId, $employeeId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
            error_log('EmployeeModel::getEmployeeAssets error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get all uniform assignments for an employee (current and past)
     * @param int $employeeId
     * @return array
     */
    public function getEmployeeUniforms(int $employeeId): array {
        try {
            $sql = "SELECT 
                        ua.assignment_id,
                        ua.uniform_id,
                        ua.date_issued,
                        ua.date_returned,
                        ua.quantity_issued,
                        ua.condition_upon_issue,
                        ua.condition_upon_return,
                        ua.remarks,
                        ui.uniform_type,
                        ui.size,
                        ui.color,
                        ui.cost_per_unit
                    FROM {$this->tbluniform_assignment} ua
                    LEFT JOIN {$this->tbluniform_inventory} ui ON ua.uniform_id = ui.uniform_id
                    WHERE ua.employee_id = ?
                    ORDER BY ua.date_issued DESC";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$employeeId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
            error_log('EmployeeModel::getEmployeeUniforms error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get current/active uniform assignments for an employee
     * @param int $employeeId
     * @return array
     */
    public function getEmployeeCurrentUniforms(int $employeeId): array {
        try {
            $sql = "SELECT 
                        ua.assignment_id,
                        ua.uniform_id,
                        ua.date_issued,
                        ua.quantity_issued,
                        ua.condition_upon_issue,
                        ui.uniform_type,
                        ui.size,
                        ui.color
                    FROM {$this->tbluniform_assignment} ua
                    LEFT JOIN {$this->tbluniform_inventory} ui ON ua.uniform_id = ui.uniform_id
                    WHERE ua.employee_id = ? AND ua.date_returned IS NULL
                    ORDER BY ua.date_issued DESC";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$employeeId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
            error_log('EmployeeModel::getEmployeeCurrentUniforms error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Search employees by name or email
     * @param string $searchTerm
     * @param int $limit
     * @return array
     */
    public function searchEmployees(string $searchTerm, int $limit = 20): array {
        try {
            $term = '%' . $searchTerm . '%';
            $sql = "SELECT 
                        e.employee_id,
                        e.account_id,
                        e.firstname,
                        e.lastname,
                        e.middlename,
                        e.position,
                        e.department,
                        e.email,
                        b.branchName
                    FROM {$this->tblemployee} e
                    LEFT JOIN {$this->tblbranch} b ON e.branch_id = b.branch_id
                    WHERE e.firstname LIKE ? 
                       OR e.lastname LIKE ? 
                       OR e.email LIKE ?
                       OR CONCAT(e.firstname, ' ', e.lastname) LIKE ?
                    ORDER BY e.lastname, e.firstname
                    LIMIT ?";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(1, $term, PDO::PARAM_STR);
            $stmt->bindValue(2, $term, PDO::PARAM_STR);
            $stmt->bindValue(3, $term, PDO::PARAM_STR);
            $stmt->bindValue(4, $term, PDO::PARAM_STR);
            $stmt->bindValue(5, $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
            error_log('EmployeeModel::searchEmployees error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get employees by department
     * @param string $department
     * @return array
     */
    public function getEmployeesByDepartment(string $department): array {
        try {
            $sql = "SELECT 
                        e.employee_id,
                        e.account_id,
                        e.firstname,
                        e.lastname,
                        e.middlename,
                        e.position,
                        e.department,
                        e.email,
                        b.branchName
                    FROM {$this->tblemployee} e
                    LEFT JOIN {$this->tblbranch} b ON e.branch_id = b.branch_id
                    WHERE e.department = ?
                    ORDER BY e.lastname, e.firstname";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$department]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
            error_log('EmployeeModel::getEmployeesByDepartment error: ' . $e->getMessage());
            return [];
        }
    }
}
