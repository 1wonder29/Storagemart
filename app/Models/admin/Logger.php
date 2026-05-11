<?php
// app/Models/admin/Logger.php

class Logger {
    protected $pdo = null;
    protected $link = null;
    protected $table = 'tbllogs'; // match your DB

    public function __construct($pdo = null, $link = null) {
        if ($pdo instanceof PDO) $this->pdo = $pdo;
        elseif (isset($GLOBALS['pdo']) && $GLOBALS['pdo'] instanceof PDO) $this->pdo = $GLOBALS['pdo'];

        if ($link) $this->link = $link;
        elseif (isset($GLOBALS['link'])) $this->link = $GLOBALS['link'];
    }

    /**
     * Basic log function (legacy compatibility)
     */
    public function log($action, $module, $id, $performedby) {
        $date = date('Y-m-d');
        $time = date('H:i:s');  
        if ($this->pdo) {
            $sql = "INSERT INTO {$this->table} (datelog, timelog, action, module, ID, performedby) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([$date, $time, $action, $module, $id, $performedby]);
        }
        if ($this->link) {
            $sql = "INSERT INTO {$this->table} (datelog, timelog, action, module, ID, performedby) VALUES (?, ?, ?, ?, ?, ?)";
            if ($stmt = mysqli_prepare($this->link, $sql)) {
                mysqli_stmt_bind_param($stmt, 'ssssss', $date, $time, $action, $module, $id, $performedby);
                $ok = mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
                return (bool)$ok;
            }
        }
        return false;
    }

    /**
     * Enhanced log for delete operations with more context
     * @param string $action Description of the delete action
     * @param string $module The module/section where delete occurred
     * @param string $recordId The ID of the deleted record
     * @param string $recordDetails Additional details about what was deleted (JSON or string)
     * @param string $performedby Username or ID of who performed the delete
     * @return bool
     */
    public function logDelete($action, $module, $recordId, $recordDetails = '', $performedby = '') {
        $date = date('Y-m-d');
        $time = date('H:i:s');
        
        // Enhance action with DELETE indicator
        $enhancedAction = "[DELETE] " . $action;
        
        // If recordDetails is an array, convert to JSON
        if (is_array($recordDetails)) {
            $recordDetails = json_encode($recordDetails);
        }
        
        // Append details to action if provided
        if (!empty($recordDetails)) {
            $enhancedAction .= " | Details: " . substr($recordDetails, 0, 150);
        }
        
        if ($this->pdo) {
            $sql = "INSERT INTO {$this->table} (datelog, timelog, action, module, ID, performedby) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([$date, $time, $enhancedAction, $module, $recordId, $performedby]);
        }
        if ($this->link) {
            $sql = "INSERT INTO {$this->table} (datelog, timelog, action, module, ID, performedby) VALUES (?, ?, ?, ?, ?, ?)";
            if ($stmt = mysqli_prepare($this->link, $sql)) {
                mysqli_stmt_bind_param($stmt, 'ssssss', $date, $time, $enhancedAction, $module, $recordId, $performedby);
                $ok = mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
                return (bool)$ok;
            }
        }
        return false;
    }

    /**
     * Get all audit logs, optionally filtered
     * @param string|null $action Filter by action (partial match)
     * @param string|null $module Filter by module
     * @param int $limit Number of records to return
     * @param int $offset Pagination offset
     * @return array
     */
    public function getAuditLogs($action = null, $module = null, $limit = 50, $offset = 0) {
        $sql = "SELECT * FROM {$this->table} WHERE 1=1";
        $params = [];
        
        if ($action) {
            $sql .= " AND action LIKE ?";
            $params[] = "%{$action}%";
        }
        
        if ($module) {
            $sql .= " AND module = ?";
            $params[] = $module;
        }
        
        $sql .= " ORDER BY datelog DESC, timelog DESC LIMIT " . (int)$limit . " OFFSET " . (int)$offset;
        
        if ($this->pdo) {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        return [];
    }

    /**
     * Get delete logs only
     * @param string|null $module Filter by module
     * @param int $limit Number of records
     * @param int $offset Pagination offset
     * @return array
     */
    public function getDeleteLogs($module = null, $limit = 50, $offset = 0) {
        return $this->getAuditLogs('[DELETE]', $module, $limit, $offset);
    }

    /**
     * Get audit logs for a specific record ID
     * @param string $recordId
     * @return array
     */
    public function getRecordAuditTrail($recordId) {
        if ($this->pdo) {
            $sql = "SELECT * FROM {$this->table} WHERE ID = ? ORDER BY datelog DESC, timelog DESC";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$recordId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        return [];
    }

    /**
     * Get logs by performer (user)
     * @param string $performedby Username or ID
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getLogsByPerformer($performedby, $limit = 50, $offset = 0) {
        if ($this->pdo) {
            $sql = "SELECT * FROM {$this->table} WHERE performedby = ? ORDER BY datelog DESC, timelog DESC LIMIT " . (int)$limit . " OFFSET " . (int)$offset;
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$performedby]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        return [];
    }

    /**
     * Get total count of audit logs (optionally filtered)
     * @param string|null $module
     * @param string|null $action
     * @return int
     */
    public function countLogs($module = null, $action = null) {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE 1=1";
        $params = [];
        
        if ($module) {
            $sql .= " AND module = ?";
            $params[] = $module;
        }
        
        if ($action) {
            $sql .= " AND action LIKE ?";
            $params[] = "%{$action}%";
        }
        
        if ($this->pdo) {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)($result['total'] ?? 0);
        }
        
        return 0;
    }

    /**
     * Generic activity logging - works for all types of activities
     * Replaces the need for specific methods - use this for all operations
     * @param string $operationType CREATE, READ, UPDATE, DELETE, LOGIN, LOGOUT, FAILED_LOGIN, etc.
     * @param string $module Module/feature name (Admin, HR, Employee, IT, Head, Authentication, etc.)
     * @param string $recordId ID of the affected record (or user ID for auth events)
     * @param string $description Detailed description of the action
     * @param string $performedby Username or ID of who performed the action
     * @param array $metadata Optional additional data to track (JSON stored)
     * @return bool
     */
    public function logActivity($operationType, $module, $recordId, $description, $performedby = '', $metadata = []) {
        $date = date('Y-m-d');
        $time = date('H:i:s');
        
        // Format action with operation type marker
        $action = "[{$operationType}] {$description}";
        
        // Append metadata if provided
        if (!empty($metadata)) {
            if (is_array($metadata)) {
                $metadata = json_encode($metadata);
            }
            $action .= " | Data: " . substr($metadata, 0, 150);
        }
        
        if ($this->pdo) {
            $sql = "INSERT INTO {$this->table} (datelog, timelog, action, module, ID, performedby) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([$date, $time, $action, $module, $recordId, $performedby]);
        }
        if ($this->link) {
            $sql = "INSERT INTO {$this->table} (datelog, timelog, action, module, ID, performedby) VALUES (?, ?, ?, ?, ?, ?)";
            if ($stmt = mysqli_prepare($this->link, $sql)) {
                mysqli_stmt_bind_param($stmt, 'ssssss', $date, $time, $action, $module, $recordId, $performedby);
                $ok = mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
                return (bool)$ok;
            }
        }
        return false;
    }

    /**
     * Specialized logging for LOGIN events
     * @param string $username Username
     * @param bool $success Whether login was successful
     * @param string $reason Reason for failure (if applicable)
     * @param array $details Additional details (IP, browser, etc.)
     * @return bool
     */
    public function logLogin($username, $success = true, $reason = '', $details = []) {
        $operationType = $success ? 'LOGIN' : 'FAILED_LOGIN';
        $description = $success ? "Successful login for user {$username}" : "Failed login attempt for user {$username}" . ($reason ? " - {$reason}" : "");
        return $this->logActivity($operationType, 'Authentication', $username, $description, $username, $details);
    }

    /**
     * Specialized logging for LOGOUT events
     * @param string $username Username
     * @return bool
     */
    public function logLogout($username) {
        return $this->logActivity('LOGOUT', 'Authentication', $username, "User {$username} logged out", $username);
    }

    /**
     * Specialized logging for CRUD operations
     * @param string $operation CREATE, UPDATE, DELETE
     * @param string $module Module name
     * @param string|int $recordId Record ID
     * @param string $description Description of the operation
     * @param string $performedby User who performed the action
     * @param array $changes For UPDATE: old values and new values
     * @return bool
     */
    public function logCRUDOperation($operation, $module, $recordId, $description, $performedby, $changes = []) {
        return $this->logActivity($operation, $module, $recordId, $description, $performedby, $changes);
    }

    /**
     * Get logs by activity type
     * @param string $operationType (LOGIN, DELETE, CREATE, etc.)
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getLogsByActivityType($operationType, $limit = 50, $offset = 0) {
        return $this->getAuditLogs("[{$operationType}]", null, $limit, $offset);
    }

    /**
     * Get recent activities across all modules
     * @param int $limit Number of recent entries
     * @return array
     */
    public function getRecentActivities($limit = 100) {
        if ($this->pdo) {
            $sql = "SELECT * FROM {$this->table} ORDER BY datelog DESC, timelog DESC LIMIT " . (int)$limit;
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        return [];
    }
}
