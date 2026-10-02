<?php

require_once __DIR__ . '/../admin/BaseModel.php';

/**
 * HRModel - Base model for HR role
 * Extends BaseModel with table name constants
 */
class HRModel extends BaseModel {
    protected $tblaccounts = 'tblaccounts';
    protected $tblemployee = 'tblemployee';
    protected $tblbranch = 'tblbranch';
    protected $tblassets_inventory = 'tblassets_inventory';
    protected $tblassets_assignment = 'tblassets_assignment';
    protected $tblassets_group = 'tblassets_group';
    protected $tblassets_category = 'tblassets_category';
    protected $tbluniform_inventory = 'tbluniform_inventory';
    protected $tbluniform_assignment = 'tbluniform_assignment';
    protected $tblfir_logs = 'tblfir_logs';

    public function __construct() {
        parent::__construct();
    }

    /**
     * Log HR action to audit trail
     */
    public function logAction(string $action, ?int $employeeId, ?int $uniformId, int $performedByAccountId, ?string $details = null): bool {
        try {
            $sql = "INSERT INTO {$this->tblfir_logs} 
                    (action, employee_id, uniform_id, performed_by, performed_role, details, date_logged)
                    VALUES (?, ?, ?, ?, 'HR', ?, NOW())";
            
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([$action, $employeeId, $uniformId, $performedByAccountId, $details]);
        } catch (\Throwable $e) {
            error_log('HRModel::logAction error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get recent HR activity logs
     */
    public function getRecentLogs(int $days = 7): array {
        try {
            $sql = "SELECT * FROM {$this->tblfir_logs}
                    WHERE date_logged >= DATE_SUB(NOW(), INTERVAL ? DAY)
                    ORDER BY date_logged DESC
                    LIMIT 100";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$days]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
            error_log('HRModel::getRecentLogs error: ' . $e->getMessage());
            return [];
        }
    }
}
