<?php
// app/Models/admin/AuditTrail.php

require_once __DIR__ . '/BaseModel.php';
require_once __DIR__ . '/Logger.php';

class AuditTrail extends BaseModel {
    protected $table = 'tbllogs';
    protected $logger = null;

    public function __construct() {
        parent::__construct();
        $this->logger = new Logger();
    }

    /**
     * Get all delete operations for admin role
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getAdminDeleteLogs($limit = 50, $offset = 0) {
        return $this->logger->getDeleteLogs(null, $limit, $offset);
    }

    /**
     * Get delete logs by module
     * @param string $module Module name
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getDeleteLogsByModule($module, $limit = 50, $offset = 0) {
        return $this->logger->getDeleteLogs($module, $limit, $offset);
    }

    /**
     * Get all audit logs with pagination
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getAllAuditLogs($limit = 50, $offset = 0) {
        return $this->logger->getAuditLogs(null, null, $limit, $offset);
    }

    /**
     * Get audit logs for specific module
     * @param string $module Module name
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getAuditLogsByModule($module, $limit = 50, $offset = 0) {
        return $this->logger->getAuditLogs(null, $module, $limit, $offset);
    }

    /**
     * Get audit trail for specific record
     * @param string $recordId
     * @return array
     */
    public function getRecordAuditTrail($recordId) {
        return $this->logger->getRecordAuditTrail($recordId);
    }

    /**
     * Get audit logs by performer
     * @param string $performedby Username or ID
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getAuditsByPerformer($performedby, $limit = 50, $offset = 0) {
        return $this->logger->getLogsByPerformer($performedby, $limit, $offset);
    }

    /**
     * Get audit logs within date range
     * @param string $startDate YYYY-MM-DD
     * @param string $endDate YYYY-MM-DD
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getAuditsByDateRange($startDate, $endDate, $limit = 50, $offset = 0) {
        if ($this->pdo) {
            $sql = "SELECT * FROM {$this->table} 
                    WHERE datelog BETWEEN ? AND ? 
                    ORDER BY datelog DESC, timelog DESC 
                    LIMIT " . (int)$limit . " OFFSET " . (int)$offset;
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$startDate, $endDate]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        return [];
    }

    /**
     * Get summary of delete operations by module
     * @return array
     */
    public function getDeleteLogsSummary() {
        if ($this->pdo) {
            $sql = "SELECT module, COUNT(*) as delete_count, MAX(datelog) as last_delete 
                    FROM {$this->table} 
                    WHERE action LIKE '[DELETE]%'
                    GROUP BY module 
                    ORDER BY delete_count DESC";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        return [];
    }

    /**
     * Get summary of all actions by module
     * @return array
     */
    public function getActionsSummary() {
        if ($this->pdo) {
            $sql = "SELECT module, action, COUNT(*) as action_count 
                    FROM {$this->table} 
                    GROUP BY module, action 
                    ORDER BY module ASC, action_count DESC";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        return [];
    }

    /**
     * Get total count of delete logs
     * @param string|null $module
     * @return int
     */
    public function countDeleteLogs($module = null) {
        return $this->logger->countLogs($module, '[DELETE]');
    }

    /**
     * Get total count of all audit logs
     * @param string|null $module
     * @return int
     */
    public function countAuditLogs($module = null) {
        return $this->logger->countLogs($module);
    }

    /**
     * Search audit logs
     * @param string $searchTerm
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function searchAuditLogs($searchTerm, $limit = 50, $offset = 0) {
        if ($this->pdo) {
            $sql = "SELECT * FROM {$this->table} 
                    WHERE action LIKE ? 
                    OR module LIKE ? 
                    OR ID LIKE ? 
                    OR performedby LIKE ?
                    ORDER BY datelog DESC, timelog DESC 
                    LIMIT " . (int)$limit . " OFFSET " . (int)$offset;
            $searchWildcard = "%{$searchTerm}%";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$searchWildcard, $searchWildcard, $searchWildcard, $searchWildcard]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        return [];
    }

    /**
     * Get high-risk actions (deletes)
     * @param int $days Number of days to look back
     * @return array
     */
    public function getRecentDeleteActions($days = 7) {
        if ($this->pdo) {
            $startDate = date('Y-m-d', strtotime("-{$days} days"));
            $sql = "SELECT * FROM {$this->table} 
                    WHERE action LIKE '[DELETE]%'
                    AND datelog >= ?
                    ORDER BY datelog DESC, timelog DESC";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$startDate]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        return [];
    }

    /**
     * Log a delete action with enhanced details
     * @param string $action Action description
     * @param string $module Module name
     * @param string $recordId Record ID being deleted
     * @param array|string $details Additional details
     * @param string $performedby Who performed the action
     * @return bool
     */
    public function logDeleteAction($action, $module, $recordId, $details = '', $performedby = '') {
        return $this->logger->logDelete($action, $module, $recordId, $details, $performedby);
    }
}
