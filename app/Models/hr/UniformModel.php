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
                        COALESCE((
                            SELECT SUM(ur.quantity_returned)
                            FROM tbluniform_returns ur
                            WHERE ur.uniform_id = i.uniform_id
                              AND ur.return_status = 'PENDING'
                        ), 0) AS quantity_returned,
                        COALESCE(quantity_damaged, 0) AS quantity_damaged,
                        COALESCE(quantity_lost, 0) AS quantity_lost,
                        CASE WHEN quantity_in_stock <= reorder_level THEN 'NEEDS_REORDER' ELSE 'OK' END as stock_status
                    FROM {$this->tbluniform_inventory} i
                    ORDER BY CASE WHEN status = 'ACTIVE' THEN 0 ELSE 1 END, uniform_type, size, color
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
            $sql = "SELECT COUNT(*) FROM {$this->tbluniform_inventory}";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            return (int) $stmt->fetchColumn();
        } catch (\Throwable $e) {
            error_log('UniformModel::getTotalUniformCount error: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get full uniform inventory for export (no pagination).
     * @return array
     */
    public function getUniformInventorySummary(): array
    {
        try {
            $sql = "SELECT 
                        uniform_id,
                        uniform_type,
                        size,
                        color,
                        quantity_in_stock,
                        reorder_level,
                        status,
                        supplier,
                        cost_per_unit,
                        datecreated,
                        COALESCE((
                            SELECT SUM(ur.quantity_returned)
                            FROM tbluniform_returns ur
                            WHERE ur.uniform_id = i.uniform_id
                              AND ur.return_status = 'PENDING'
                        ), 0) AS quantity_returned,
                        COALESCE(quantity_damaged, 0) AS quantity_damaged,
                        COALESCE(quantity_lost, 0) AS quantity_lost,
                        CASE WHEN quantity_in_stock <= reorder_level THEN 'NEEDS_REORDER' ELSE 'OK' END AS stock_status
                    FROM {$this->tbluniform_inventory} i
                    ORDER BY CASE WHEN status = 'ACTIVE' THEN 0 ELSE 1 END, uniform_type, size, color";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
            error_log('UniformModel::getUniformInventorySummary error: ' . $e->getMessage());
            return [];
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
     * @param array $data - uniform_type, size, quantity_in_stock, createdby (required); color, cost_per_unit, supplier, reorder_level, status (optional)
     * @return int|false - uniform_id on success, false on failure
     */
    public function addUniform(array $data): int|false {
        try {
            $required = ['uniform_type', 'size', 'quantity_in_stock', 'createdby'];
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
                $data['color'] ?? '',
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
     * Reactivate a discontinued uniform
     * @param int $uniformId
     * @param string $updatedBy
     * @return bool
     */
    public function reactivateUniform(int $uniformId, string $updatedBy = 'system'): bool {
        try {
            $sql = "UPDATE {$this->tbluniform_inventory}
                    SET status = 'ACTIVE', date_updated = NOW(), updated_by = ?
                    WHERE uniform_id = ? AND status = 'DISCONTINUED'";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([$updatedBy, $uniformId]);
        } catch (\Throwable $e) {
            error_log('UniformModel::reactivateUniform error: ' . $e->getMessage());
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
                $sql = "SELECT 
                    ui.*,
                    (SELECT COUNT(*)
                     FROM {$this->tbluniform_assignment} ua
                     WHERE ua.uniform_id = ui.uniform_id
                       AND ua.date_returned IS NULL) AS return_count
                    FROM {$this->tbluniform_inventory} ui
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
     * Get active (not returned) assignments for a given uniform
     * @param int $uniformId
     * @return array
     */
    

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
     * Get assignment by ID
     * @param int $assignmentId
     * @return array|null
     */
    public function getAssignmentById(int $assignmentId): ?array {
        try {
            $sql = "SELECT ua.*, ui.uniform_type, ui.size, ui.color, ui.uniform_id, ua.quantity_issued,
                        CONCAT(e.firstname, ' ', e.lastname) AS employee_name
                    FROM {$this->tbluniform_assignment} ua
                    LEFT JOIN {$this->tbluniform_inventory} ui ON ua.uniform_id = ui.uniform_id
                    LEFT JOIN {$this->tblemployee} e ON ua.employee_id = e.employee_id
                    WHERE ua.assignment_id = ? LIMIT 1";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$assignmentId]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (\Throwable $e) {
            error_log('UniformModel::getAssignmentById error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Mark assignment as returned and apply inventory updates immediately.
     * Supports mixed-condition returns via $returnBreakdown.
     *
     * @param int $assignmentId
     * @param int $processedBy
     * @param string $condition Backward-compatible fallback condition
     * @param string $remarks Optional remarks
     * @param array<string,int> $returnBreakdown ['GOOD'=>x,'DAMAGED'=>x,'LOST'=>x]
     * @return bool
     */
    public function returnAssignment(
        int $assignmentId,
        int $processedBy,
        string $condition = 'GOOD',
        string $remarks = '',
        array $returnBreakdown = []
    ): bool {
        try {
            $this->pdo->beginTransaction();

            $assignment = $this->getAssignmentById($assignmentId);
            if (!$assignment) {
                $this->pdo->rollBack();
                return false;
            }

            if (!empty($assignment['date_returned'])) {
                // already returned
                $this->pdo->rollBack();
                return false;
            }

            $quantity = (int) ($assignment['quantity_issued'] ?? 0);
            $uniformId = (int) $assignment['uniform_id'];
            $employeeId = (int) ($assignment['employee_id'] ?? 0);
            $condition = strtoupper(trim($condition)) ?: 'GOOD';
            $remarks = trim($remarks);

            $allowedConditions = ['GOOD', 'DAMAGED', 'LOST'];
            $normalizedBreakdown = [];
            foreach ($allowedConditions as $allowedCondition) {
                $normalizedBreakdown[$allowedCondition] = max(0, (int) ($returnBreakdown[$allowedCondition] ?? 0));
            }
            $returnQuantity = array_sum($normalizedBreakdown);
            $hasBreakdown = $returnQuantity > 0;

            if ($hasBreakdown && ($returnQuantity <= 0 || $returnQuantity > $quantity)) {
                $this->pdo->rollBack();
                return false;
            }

            if (!$hasBreakdown) {
                $normalizedBreakdown[$condition] = $quantity;
                $returnQuantity = $quantity;
            }

            // Pick overall assignment condition by severity when mixed return is used.
            $overallCondition = 'GOOD';
            if ($normalizedBreakdown['LOST'] > 0) {
                $overallCondition = 'LOST';
            } elseif ($normalizedBreakdown['DAMAGED'] > 0) {
                $overallCondition = 'DAMAGED';
            }

            if ($remarks === '') {
                $remarks = sprintf(
                    'Uniform returned on %s. Condition: %s. Processed by HR.',
                    date('F j, Y'),
                    $overallCondition
                );
            }

            // 1) Mark assignment as fully returned or reduce outstanding quantity for partial return.
            if ($returnQuantity >= $quantity) {
                $sql = "UPDATE {$this->tbluniform_assignment}
                        SET date_returned = CURDATE(), condition_upon_return = ?, remarks = ?
                        WHERE assignment_id = ?";
                $stmt = $this->pdo->prepare($sql);
                $ok = $stmt->execute([$overallCondition, $remarks, $assignmentId]);
            } else {
                $sql = "UPDATE {$this->tbluniform_assignment}
                        SET quantity_issued = GREATEST(0, quantity_issued - ?)
                        WHERE assignment_id = ?";
                $stmt = $this->pdo->prepare($sql);
                $ok = $stmt->execute([$returnQuantity, $assignmentId]);
            }

            if (!$ok) {
                $this->pdo->rollBack();
                return false;
            }

            // 2) Apply inventory counts immediately based on return condition breakdown.
            foreach ($normalizedBreakdown as $conditionKey => $conditionQty) {
                if ($conditionQty <= 0) {
                    continue;
                }

                if ($conditionKey === 'DAMAGED') {
                    $sqlInv = "UPDATE {$this->tbluniform_inventory}
                               SET quantity_damaged = COALESCE(quantity_damaged, 0) + ?, date_updated = NOW()
                               WHERE uniform_id = ?";
                } elseif ($conditionKey === 'LOST') {
                    $sqlInv = "UPDATE {$this->tbluniform_inventory}
                               SET quantity_lost = COALESCE(quantity_lost, 0) + ?, date_updated = NOW()
                               WHERE uniform_id = ?";
                } else {
                    $sqlInv = "UPDATE {$this->tbluniform_inventory}
                               SET quantity_in_stock = quantity_in_stock + ?, date_updated = NOW()
                               WHERE uniform_id = ?";
                }

                $stmtInv = $this->pdo->prepare($sqlInv);
                $okInv = $stmtInv->execute([$conditionQty, $uniformId]);
                if (!$okInv) {
                    $this->pdo->rollBack();
                    return false;
                }
            }

            // 3) Record per-condition return rows as already approved (no pending queue).
            $sql3 = "INSERT INTO tbluniform_returns
                    (assignment_id, uniform_id, employee_id, quantity_returned, condition_upon_return, remarks, date_returned, processed_by, return_status, approved_by, processed_at, createdby, datecreated)
                    VALUES (?, ?, ?, ?, ?, ?, CURDATE(), ?, 'APPROVED', ?, NOW(), ?, NOW())";
            $stmt3 = $this->pdo->prepare($sql3);
            $ok3 = true;
            foreach ($normalizedBreakdown as $conditionKey => $conditionQty) {
                if ($conditionQty <= 0) {
                    continue;
                }

                $rowRemarks = $remarks;
                if (count(array_filter($normalizedBreakdown, static fn ($qty) => $qty > 0)) > 1) {
                    $rowRemarks = trim(($remarks !== '' ? $remarks . ' | ' : '') . 'Split return');
                }

                $insertOk = $stmt3->execute([
                    $assignmentId,
                    $uniformId,
                    $employeeId,
                    $conditionQty,
                    $conditionKey,
                    $rowRemarks,
                    $processedBy,
                    $processedBy,
                    'system'
                ]);

                if (!$insertOk) {
                    $ok3 = false;
                    break;
                }
            }

            if (!$ok3) {
                error_log('UniformModel::returnAssignment warning: Failed to create return record(s) for assignment ' . $assignmentId);
            }

            $this->pdo->commit();
            return true;
        } catch (\Throwable $e) {
            error_log('UniformModel::returnAssignment error: ' . $e->getMessage());
            try { $this->pdo->rollBack(); } catch (\Throwable $_) {}
            return false;
        }
    }

    /**
     * Approve a return and move uniform to appropriate inventory category
     * @param int $returnId
     * @param string $approvalStatus APPROVED or REJECTED
     * @param int $approvedBy
     * @return bool
     */
    public function approveReturn(int $returnId, string $approvalStatus = 'APPROVED', int $approvedBy = 0): bool {
        try {
            $this->pdo->beginTransaction();

            // Get the return record
            $sql = "SELECT * FROM tbluniform_returns WHERE return_id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$returnId]);
            $return = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$return) {
                $this->pdo->rollBack();
                return false;
            }

            if (strtoupper((string) ($return['return_status'] ?? '')) !== 'PENDING') {
                $this->pdo->rollBack();
                return false;
            }

            $uniformId = (int) $return['uniform_id'];
            $quantity = max(0, (int) $return['quantity_returned']);
            $condition = strtoupper(trim($return['condition_upon_return']));

            if ($approvalStatus === 'APPROVED') {
                // Remove from pending returns
                $sqlRemove = "UPDATE {$this->tbluniform_inventory}
                              SET quantity_returned = GREATEST(0, COALESCE(quantity_returned, 0) - ?)
                              WHERE uniform_id = ?";
                $stmtRemove = $this->pdo->prepare($sqlRemove);
                $ok1 = $stmtRemove->execute([$quantity, $uniformId]);

                if (!$ok1) {
                    $this->pdo->rollBack();
                    return false;
                }

                // Add to appropriate category based on condition
                if (in_array($condition, ['GOOD', 'FAIR', 'USED'])) {
                    // Return to stock
                    $sqlAdd = "UPDATE {$this->tbluniform_inventory} SET quantity_in_stock = quantity_in_stock + ?, date_updated = NOW() WHERE uniform_id = ?";
                } elseif ($condition === 'DAMAGED') {
                    // Mark as damaged
                    $sqlAdd = "UPDATE {$this->tbluniform_inventory} SET quantity_damaged = COALESCE(quantity_damaged, 0) + ?, date_updated = NOW() WHERE uniform_id = ?";
                } elseif ($condition === 'LOST') {
                    // Mark as lost
                    $sqlAdd = "UPDATE {$this->tbluniform_inventory} SET quantity_lost = COALESCE(quantity_lost, 0) + ?, date_updated = NOW() WHERE uniform_id = ?";
                } else {
                    // Default: return to stock
                    $sqlAdd = "UPDATE {$this->tbluniform_inventory} SET quantity_in_stock = quantity_in_stock + ?, date_updated = NOW() WHERE uniform_id = ?";
                }

                $stmtAdd = $this->pdo->prepare($sqlAdd);
                $ok2 = $stmtAdd->execute([$quantity, $uniformId]);

                if (!$ok2) {
                    $this->pdo->rollBack();
                    return false;
                }
            } else {
                // REJECTED: Remove from pending returns only
                $sqlRemove = "UPDATE {$this->tbluniform_inventory}
                              SET quantity_returned = GREATEST(0, COALESCE(quantity_returned, 0) - ?)
                              WHERE uniform_id = ?";
                $stmtRemove = $this->pdo->prepare($sqlRemove);
                $ok1 = $stmtRemove->execute([$quantity, $uniformId]);

                if (!$ok1) {
                    $this->pdo->rollBack();
                    return false;
                }
            }

            // Update return record
            $sqlUpdate = "UPDATE tbluniform_returns SET return_status = ?, approved_by = ?, processed_at = NOW() WHERE return_id = ?";
            $stmtUpdate = $this->pdo->prepare($sqlUpdate);
            $stmtUpdate->execute([$approvalStatus, $approvedBy, $returnId]);

            $this->pdo->commit();
            return true;
        } catch (\Throwable $e) {
            error_log('UniformModel::approveReturn error: ' . $e->getMessage());
            try { $this->pdo->rollBack(); } catch (\Throwable $_) {}
            return false;
        }
    }

    /**
     * Realign stored pending-return counts with pending return records.
     */
    public function syncPendingReturnCounts(): void
    {
        try {
            $sql = "UPDATE {$this->tbluniform_inventory} i
                    SET quantity_returned = COALESCE((
                        SELECT SUM(ur.quantity_returned)
                        FROM tbluniform_returns ur
                        WHERE ur.uniform_id = i.uniform_id
                          AND ur.return_status = 'PENDING'
                    ), 0)";
            $this->pdo->exec($sql);
        } catch (\Throwable $e) {
            error_log('UniformModel::syncPendingReturnCounts error: ' . $e->getMessage());
        }
    }

    /**
     * Get pending returns for review
     * @return array
     */
    public function getPendingReturns(): array {
        try {
            $sql = "SELECT 
                        ur.return_id,
                        ur.assignment_id,
                        ur.uniform_id,
                        ur.employee_id,
                        ur.quantity_returned,
                        ur.condition_upon_return,
                        ur.remarks,
                        ur.date_returned,
                        ur.processed_by,
                        ui.uniform_type,
                        ui.size,
                        ui.color,
                        CONCAT(e.firstname, ' ', e.lastname) AS employee_name,
                        e.email
                    FROM tbluniform_returns ur
                    LEFT JOIN {$this->tbluniform_inventory} ui ON ur.uniform_id = ui.uniform_id
                    LEFT JOIN {$this->tblemployee} e ON ur.employee_id = e.employee_id
                    WHERE ur.return_status = 'PENDING'
                    ORDER BY ur.date_returned DESC";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
            error_log('UniformModel::getPendingReturns error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get employees with assigned uniforms (active assignments only)
     * @param int $limit
     * @return array
     */
    public function getEmployeesWithUniforms(int $limit = 20): array {
        try {
            $sql = "SELECT 
                        e.employee_id,
                        e.firstname,
                        e.lastname,
                        e.position,
                        e.department,
                        COUNT(ua.assignment_id) as uniform_count,
                        GROUP_CONCAT(CONCAT(ui.uniform_type, ' (', ui.size, ' - ', ui.color, ')') SEPARATOR ', ') as uniforms_assigned
                    FROM {$this->tblemployee} e
                    INNER JOIN {$this->tbluniform_assignment} ua ON e.employee_id = ua.employee_id
                    INNER JOIN {$this->tbluniform_inventory} ui ON ua.uniform_id = ui.uniform_id
                    WHERE ua.date_returned IS NULL
                    GROUP BY e.employee_id
                    ORDER BY e.lastname, e.firstname
                    LIMIT ?";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(1, $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
            error_log('UniformModel::getEmployeesWithUniforms error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get total count of employees with assigned uniforms
     * @return int
     */
    public function getTotalEmployeesWithUniforms(): int {
        try {
            $sql = "SELECT COUNT(DISTINCT e.employee_id) 
                    FROM {$this->tblemployee} e
                    INNER JOIN {$this->tbluniform_assignment} ua ON e.employee_id = ua.employee_id
                    WHERE ua.date_returned IS NULL";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            return (int) $stmt->fetchColumn();
        } catch (\Throwable $e) {
            error_log('UniformModel::getTotalEmployeesWithUniforms error: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get all assignments for a specific uniform (both active and returned)
     * @param int $uniformId
     * @return array
     */
    public function getAssignmentsByUniformId(int $uniformId, ?string $condition = null): array {
        try {
            $condition = strtoupper(trim((string) $condition));

            // For return-condition views (DAMAGED/LOST), read from return records.
            if (in_array($condition, ['DAMAGED', 'LOST'], true)) {
                $sql = "SELECT
                            ur.assignment_id,
                            ur.employee_id,
                            ur.uniform_id,
                            ur.quantity_returned AS quantity_issued,
                            ua.date_issued,
                            ur.date_returned AS date_returned,
                            ua.condition_upon_issue,
                            ur.condition_upon_return,
                            ur.remarks,
                            e.firstname,
                            e.lastname,
                            CONCAT(e.firstname, ' ', e.lastname) AS employee_name
                        FROM tbluniform_returns ur
                        LEFT JOIN {$this->tbluniform_assignment} ua ON ur.assignment_id = ua.assignment_id
                        LEFT JOIN {$this->tblemployee} e ON ur.employee_id = e.employee_id
                        WHERE ur.uniform_id = ?
                          AND UPPER(ur.condition_upon_return) = ?
                        ORDER BY ur.date_returned DESC, ur.return_id DESC";

                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([$uniformId, $condition]);
                return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
            }

            $sql = "SELECT 
                        ua.assignment_id,
                        ua.employee_id,
                        ua.uniform_id,
                        ua.quantity_issued,
                        ua.date_issued,
                        ua.date_returned,
                        ua.condition_upon_issue,
                        ua.condition_upon_return,
                        ua.remarks,
                        e.firstname,
                        e.lastname,
                        CONCAT(e.firstname, ' ', e.lastname) as employee_name
                    FROM {$this->tbluniform_assignment} ua
                    LEFT JOIN {$this->tblemployee} e ON ua.employee_id = e.employee_id
                    WHERE ua.uniform_id = ?";
            $params = [$uniformId];

            $sql .= " ORDER BY ua.date_issued DESC, ua.assignment_id DESC";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?? [];
        } catch (\Throwable $e) {
            error_log('UniformModel::getAssignmentsByUniformId error: ' . $e->getMessage());
            return [];
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
