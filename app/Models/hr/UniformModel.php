<?php

require_once __DIR__ . '/HRModel.php';

/**
 * HR UniformModel - Manage uniform inventory
 * HR can add, edit, and delete uniform records
 */
class UniformModel extends HRModel {

    public function __construct() {
        parent::__construct();
    }

    /**
     * Get all uniforms with pagination
     * @param int $offset
     * @param int $limit
     * @return array
     */
    public function getAllUniforms(int $offset = 0, int $limit = 20): array {
        try {
            $sql = "SELECT 
                        uniform_id,
                        uniform_type,
                        size,
                        color,
                        quantity_in_stock,
                        cost_per_unit,
                        supplier,
                        reorder_level,
                        status,
                        datecreated,
                        createdby,
                        CASE WHEN quantity_in_stock <= reorder_level THEN 'NEEDS_REORDER' ELSE 'OK' END as stock_status
                    FROM {$this->tbluniform_inventory}
                    ORDER BY uniform_type, size, color
                    LIMIT ? OFFSET ?";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(1, $limit, PDO::PARAM_INT);
            $stmt->bindValue(2, $offset, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
            error_log('UniformModel::getAllUniforms error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get total uniform count for pagination
     * @return int
     */
    public function getTotalUniformCount(): int {
        try {
            $sql = "SELECT COUNT(*) FROM {$this->tbluniform_inventory} WHERE status = 'ACTIVE'";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            return (int) $stmt->fetchColumn();
        } catch (\Throwable $e) {
            error_log('UniformModel::getTotalUniformCount error: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get uniform by ID
     * @param int $uniformId
     * @return array|null
     */
    public function getUniformById(int $uniformId): ?array {
        try {
            $sql = "SELECT * FROM {$this->tbluniform_inventory} WHERE uniform_id = ? LIMIT 1";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$uniformId]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (\Throwable $e) {
            error_log('UniformModel::getUniformById error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Add new uniform record
     * @param array $data - uniform_type, size, color, quantity_in_stock, cost_per_unit, supplier, reorder_level, status, createdby
     * @return int|false - uniform_id on success, false on failure
     */
    public function addUniform(array $data): int|false {
        try {
            $required = ['uniform_type', 'size', 'color', 'quantity_in_stock', 'createdby'];
            foreach ($required as $field) {
                if (!isset($data[$field]) || $data[$field] === '') {
                    error_log("UniformModel::addUniform missing field: $field");
                    return false;
                }
            }

            $sql = "INSERT INTO {$this->tbluniform_inventory} 
                    (uniform_type, size, color, quantity_in_stock, cost_per_unit, supplier, reorder_level, status, createdby, datecreated)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            
            $stmt = $this->pdo->prepare($sql);
            $success = $stmt->execute([
                $data['uniform_type'],
                $data['size'],
                $data['color'],
                (int) $data['quantity_in_stock'],
                $data['cost_per_unit'] ?? null,
                $data['supplier'] ?? null,
                (int) ($data['reorder_level'] ?? 5),
                $data['status'] ?? 'ACTIVE',
                $data['createdby']
            ]);

            if ($success) {
                return (int) $this->pdo->lastInsertId();
            }
            return false;
        } catch (\Throwable $e) {
            error_log('UniformModel::addUniform error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Update uniform record
     * @param int $uniformId
     * @param array $data - fields to update
     * @return bool
     */
    public function updateUniform(int $uniformId, array $data): bool {
        try {
            // Build dynamic UPDATE query
            $updates = [];
            $params = [];

            $allowedFields = ['uniform_type', 'size', 'color', 'quantity_in_stock', 'cost_per_unit', 'supplier', 'reorder_level', 'status'];
            
            foreach ($allowedFields as $field) {
                if (isset($data[$field])) {
                    $updates[] = "$field = ?";
                    $params[] = $data[$field];
                }
            }

            if (empty($updates)) {
                return false;
            }

            $updates[] = "date_updated = NOW()";
            $updates[] = "updated_by = ?";
            $params[] = $data['updated_by'] ?? 'system';
            $params[] = $uniformId;

            $sql = "UPDATE {$this->tbluniform_inventory} SET " . implode(', ', $updates) . " WHERE uniform_id = ?";
            
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute($params);
        } catch (\Throwable $e) {
            error_log('UniformModel::updateUniform error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete uniform (soft delete by marking as DISCONTINUED)
     * @param int $uniformId
     * @return bool
     */
    public function deleteUniform(int $uniformId): bool {
        try {
            // Soft delete - just mark as DISCONTINUED
            $sql = "UPDATE {$this->tbluniform_inventory} SET status = 'DISCONTINUED', date_updated = NOW() WHERE uniform_id = ?";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([$uniformId]);
        } catch (\Throwable $e) {
            error_log('UniformModel::deleteUniform error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if uniform has active assignments
     * @param int $uniformId
     * @return bool
     */
    public function isUniformInUse(int $uniformId): bool {
        try {
            $sql = "SELECT COUNT(*) FROM {$this->tbluniform_assignment} WHERE uniform_id = ? AND date_returned IS NULL";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$uniformId]);
            return (int) $stmt->fetchColumn() > 0;
        } catch (\Throwable $e) {
            error_log('UniformModel::isUniformInUse error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get uniforms needing reorder
     * @return array
     */
    public function getUniformsNeedingReorder(): array {
        try {
            $sql = "SELECT * FROM {$this->tbluniform_inventory} 
                    WHERE status = 'ACTIVE' AND quantity_in_stock <= reorder_level
                    ORDER BY quantity_in_stock ASC";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
            error_log('UniformModel::getUniformsNeedingReorder error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Search uniforms
     * @param string $searchTerm
     * @return array
     */
    public function searchUniforms(string $searchTerm): array {
        try {
            $term = '%' . $searchTerm . '%';
            $sql = "SELECT * FROM {$this->tbluniform_inventory}
                    WHERE uniform_type LIKE ? OR color LIKE ? OR size LIKE ?
                    ORDER BY uniform_type, size, color";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$term, $term, $term]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
            error_log('UniformModel::searchUniforms error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get assignment statistics
     * @return array
     */
    public function getAssignmentStats(): array {
        try {
            $sql = "SELECT 
                        COUNT(DISTINCT uniform_id) as total_uniform_types,
                        SUM(quantity_in_stock) as total_stock,
                        COUNT(DISTINCT CASE WHEN quantity_in_stock <= reorder_level THEN uniform_id END) as needs_reorder
                    FROM {$this->tbluniform_inventory}
                    WHERE status = 'ACTIVE'";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
            error_log('UniformModel::getAssignmentStats error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get distinct uniform types
     * @return array
     */
    public function getUniformTypes(): array {
        try {
            $sql = "SELECT DISTINCT uniform_type FROM {$this->tbluniform_inventory} WHERE status = 'ACTIVE' ORDER BY uniform_type";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_COLUMN) ?: [];
        } catch (\Throwable $e) {
            error_log('UniformModel::getUniformTypes error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get uniforms by type
     * @param string $uniformType
     * @return array
     */
    public function getUniformsByType(string $uniformType): array {
        try {
            $sql = "SELECT 
                        uniform_id,
                        uniform_type,
                        size,
                        color,
                        quantity_in_stock
                    FROM {$this->tbluniform_inventory}
                    WHERE uniform_type = ? AND status = 'ACTIVE'
                    ORDER BY size, color";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$uniformType]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
            error_log('UniformModel::getUniformsByType error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Assign uniform to employee
     * @param int $employeeId
     * @param int $uniformId
     * @param int $quantityIssued
     * @param string $conditionUponIssue
     * @param string $remarks
     * @param int $createdBy
     * @return bool
     */
    public function assignUniform(int $employeeId, int $uniformId, int $quantityIssued, string $conditionUponIssue = 'GOOD', string $remarks = '', int $createdBy = 0): bool {
        try {
            $sql = "INSERT INTO {$this->tbluniform_assignment}
                    (employee_id, uniform_id, date_issued, quantity_issued, condition_upon_issue, remarks, createdby, datecreated)
                    VALUES (?, ?, NOW(), ?, ?, ?, ?, NOW())";
            
            $stmt = $this->pdo->prepare($sql);
            $result = $stmt->execute([$employeeId, $uniformId, $quantityIssued, $conditionUponIssue, $remarks, $createdBy]);
            
            // Decrease stock if assignment successful
            if ($result) {
                $this->decreaseUniformStock($uniformId, $quantityIssued);
            }
            
            return $result;
        } catch (\Throwable $e) {
            error_log('UniformModel::assignUniform error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Decrease uniform stock after assignment
     * @param int $uniformId
     * @param int $quantity
     * @return bool
     */
    private function decreaseUniformStock(int $uniformId, int $quantity): bool {
        try {
            $sql = "UPDATE {$this->tbluniform_inventory} 
                    SET quantity_in_stock = quantity_in_stock - ?, 
                        date_updated = NOW() 
                    WHERE uniform_id = ? AND quantity_in_stock >= ?";
            
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([$quantity, $uniformId, $quantity]);
        } catch (\Throwable $e) {
            error_log('UniformModel::decreaseUniformStock error: ' . $e->getMessage());
            return false;
        }
    }
}
