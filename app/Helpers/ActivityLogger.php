<?php
/**
 * ActivityLogger Helper
 * Simplifies activity logging across the entire application
 * 
 * Usage Examples:
 *   ActivityLogger::log('CREATE', 'HR - Uniforms', '123', 'Added new uniform', $username);
 *   ActivityLogger::log('UPDATE', 'Admin - Accounts', '456', 'Updated account status', $username, ['status_before' => 'active', 'status_after' => 'inactive']);
 *   ActivityLogger::log('DELETE', 'Admin - Assets', '789', 'Deleted asset', $username);
 *   ActivityLogger::logLogin($username, true);
 *   ActivityLogger::logLogout($username);
 */

class ActivityLogger {
    private static $logger = null;

    /**
     * Initialize the logger (called automatically if needed)
     */
    private static function initialize() {
        if (self::$logger === null) {
            require_once __DIR__ . '/../Models/admin/Logger.php';
            self::$logger = new Logger();
        }
    }

    /**
     * Log a general activity
     * 
     * @param string $operationType CREATE, READ, UPDATE, DELETE, LOGIN, LOGOUT, etc.
     * @param string $module Module name (e.g., "HR - Uniforms", "Admin - Assets", "Employee - Requests")
     * @param string $recordId The ID of the affected record
     * @param string $description Description of the activity
     * @param string $performedby Username of who performed the action
     * @param array $metadata Optional metadata to track (changes, details, etc.)
     * @return bool
     */
    public static function log($operationType, $module, $recordId, $description, $performedby = '', $metadata = []) {
        self::initialize();
        return self::$logger->logActivity($operationType, $module, $recordId, $description, $performedby, $metadata);
    }

    /**
     * Log a CREATE operation
     * 
     * @param string $module Module name
     * @param string $recordId New record ID
     * @param string $description What was created
     * @param string $performedby Username
     * @param array $data The created data
     * @return bool
     */
    public static function create($module, $recordId, $description, $performedby, $data = []) {
        return self::log('CREATE', $module, $recordId, $description, $performedby, $data);
    }

    /**
     * Log an UPDATE operation
     * 
     * @param string $module Module name
     * @param string $recordId Record ID that was updated
     * @param string $description What was updated
     * @param string $performedby Username
     * @param array $changes Array with 'before' and 'after' keys
     * @return bool
     */
    public static function update($module, $recordId, $description, $performedby, $changes = []) {
        return self::log('UPDATE', $module, $recordId, $description, $performedby, $changes);
    }

    /**
     * Log a DELETE operation
     * 
     * @param string $module Module name
     * @param string $recordId Deleted record ID
     * @param string $description What was deleted
     * @param string $performedby Username
     * @param array $deletedData The deleted data (for audit trail)
     * @return bool
     */
    public static function delete($module, $recordId, $description, $performedby, $deletedData = []) {
        return self::log('DELETE', $module, $recordId, $description, $performedby, $deletedData);
    }

    /**
     * Log a TRANSFER operation (asset assignment, branch transfer, etc.)
     */
    public static function transfer($module, $recordId, $description, $performedby, $details = []) {
        self::initialize();
        return self::$logger->logTransfer($description, $module, $recordId, $details, $performedby);
    }

    /**
     * Log a READ/VIEW operation
     * 
     * @param string $module Module name
     * @param string $recordId Record ID that was viewed
     * @param string $description What was viewed
     * @param string $performedby Username
     * @return bool
     */
    public static function view($module, $recordId, $description, $performedby) {
        return self::log('READ', $module, $recordId, $description, $performedby);
    }

    /**
     * Log a LOGIN event
     * 
     * @param string $username Username logging in
     * @param bool $success Whether login was successful
     * @param string $reason Reason for failure (if applicable)
     * @return bool
     */
    public static function login($username, $success = true, $reason = '') {
        self::initialize();
        return self::$logger->logLogin($username, $success, $reason);
    }

    /**
     * Log a LOGOUT event
     * 
     * @param string $username Username logging out
     * @return bool
     */
    public static function logout($username) {
        self::initialize();
        return self::$logger->logLogout($username);
    }

    /**
     * Log a custom action (for special operations not covered above)
     * 
     * @param string $action The action type (e.g., "SUBMIT", "APPROVE", "REJECT")
     * @param string $module Module name
     * @param string $recordId Record ID
     * @param string $description Description
     * @param string $performedby Username
     * @param array $metadata Additional data
     * @return bool
     */
    public static function action($action, $module, $recordId, $description, $performedby, $metadata = []) {
        return self::log($action, $module, $recordId, $description, $performedby, $metadata);
    }

    /**
     * Get recent activities (admin dashboard)
     * 
     * @param int $limit Number of recent entries
     * @return array
     */
    public static function getRecent($limit = 100) {
        self::initialize();
        return self::$logger->getRecentActivities($limit);
    }

    /**
     * Get activities by type
     * 
     * @param string $operationType LOGIN, DELETE, CREATE, etc.
     * @param int $limit Number of entries
     * @param int $offset Pagination offset
     * @return array
     */
    public static function getByType($operationType, $limit = 50, $offset = 0) {
        self::initialize();
        return self::$logger->getLogsByActivityType($operationType, $limit, $offset);
    }

    /**
     * Get activities by performer
     * 
     * @param string $username Username
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public static function getByPerformer($username, $limit = 50, $offset = 0) {
        self::initialize();
        return self::$logger->getLogsByPerformer($username, $limit, $offset);
    }

    /**
     * Get record audit trail (all activities for a specific record)
     * 
     * @param string $recordId
     * @return array
     */
    public static function getRecordTrail($recordId) {
        self::initialize();
        return self::$logger->getRecordAuditTrail($recordId);
    }
}
