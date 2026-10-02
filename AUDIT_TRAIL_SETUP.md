# Audit Trail System for Admin Role - Implementation Guide

## Overview
This audit trail system provides comprehensive logging and tracking of all delete operations performed by administrators. It captures detailed information about who performed the action, what was deleted, when it happened, and why (through additional details).

## System Components

### 1. Enhanced Logger Model (`app/Models/admin/Logger.php`)
Enhanced with new methods for detailed audit logging:

#### Methods:
- `log($action, $module, $id, $performedby)` - Legacy logging method
- `logDelete($action, $module, $recordId, $recordDetails, $performedby)` - Enhanced delete logging
- `getAuditLogs($action, $module, $limit, $offset)` - Retrieve audit logs with filters
- `getDeleteLogs($module, $limit, $offset)` - Get only delete operations
- `getRecordAuditTrail($recordId)` - Get complete history of a specific record
- `getLogsByPerformer($performedby, $limit, $offset)` - Track actions by user
- `countLogs($module, $action)` - Get count of audit logs

#### Usage Example:
```php
$logger = new Logger();

// Log a delete operation with details
$logger->logDelete(
    "Account deleted",
    "Account Management",
    "123",
    [
        'username' => 'john.doe',
        'usertype' => 'EMPLOYEE',
        'status' => 'ACTIVE',
        'deleted_at' => date('Y-m-d H:i:s'),
        'deleted_by' => 'admin.user'
    ],
    'admin.user'
);

// Retrieve delete logs
$deleteLogs = $logger->getDeleteLogs(null, 50, 0);

// Get audit trail for a specific record
$trail = $logger->getRecordAuditTrail('123');
```

### 2. AuditTrail Model (`app/Models/admin/AuditTrail.php`)
Specialized model for querying and analyzing audit logs:

#### Key Methods:
- `getAdminDeleteLogs($limit, $offset)` - Get all admin delete operations
- `getDeleteLogsByModule($module, $limit, $offset)` - Filter deletes by module
- `getAllAuditLogs($limit, $offset)` - Get all audit entries
- `getRecordAuditTrail($recordId)` - Complete history of a record
- `getAuditsByDateRange($startDate, $endDate, $limit, $offset)` - Historical analysis
- `getDeleteLogsSummary()` - Summary statistics of deletions by module
- `getRecentDeleteActions($days)` - High-risk activity within time period
- `searchAuditLogs($searchTerm, $limit, $offset)` - Full-text search capability

#### Usage Example:
```php
$auditTrail = new AuditTrail();

// Get recent deletions in last 7 days
$recentDeletes = $auditTrail->getRecentDeleteActions(7);

// Get deletion summary by module
$summary = $auditTrail->getDeleteLogsSummary();

// Search audit logs
$results = $auditTrail->searchAuditLogs('account deletion', 50, 0);
```

### 3. AdminController Audit Methods
New endpoints for viewing and managing audit trails:

#### Routes:
- `GET /admin/audit-trail` - View audit trail dashboard
- `GET /admin/audit-detail` - AJAX endpoint for record details

#### Features:
- **Pagination**: Browse through logs efficiently
- **Filtering**: By date range, module, user, action type
- **Search**: Full-text search across all fields
- **Summaries**: Statistics on delete operations
- **Export-ready**: Clean data structure for reporting

#### Usage in Controller:
```php
public function auditTrail()
{
    $auditTrail = new AuditTrail();
    
    // Handle filters
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = 50;
    $offset = ($page - 1) * $limit;
    
    // Get logs based on filter type
    $logs = $auditTrail->getDeleteLogsByModule('Account Management', $limit, $offset);
    
    // Pass to view
    require __DIR__ . '/../../Views/admin/audit/audit_trail.php';
}
```

### 4. Audit Trail View (`app/Views/admin/audit/audit_trail.php`)
Professional UI for viewing and analyzing audit logs:

**Features:**
- Dashboard with summary cards (recent deletions, total logs, affected modules)
- Advanced filtering interface
- Responsive data table with record details
- Pagination controls
- Summary statistics by module
- Direct action links for investigation

**Key Sections:**
- Summary Cards: Quick statistics at a glance
- Filter Panel: Multiple filtering options
- Activity Log Table: Detailed log entries
- Pagination: Navigate through records
- Deletion Summary: Aggregated data by module

## Implementation Checklist

### ✅ Step 1: Database Verification
The `tbllogs` table should already exist with this structure:
```sql
CREATE TABLE `tbllogs` (
  `datelog` varchar(20) NOT NULL,
  `timelog` varchar(20) NOT NULL,
  `action` varchar(255) NOT NULL,
  `module` varchar(50) NOT NULL,
  `ID` varchar(20) NOT NULL,
  `performedby` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
```

### ✅ Step 2: Update Delete Functions
To add audit logging to any delete function:

```php
// Before deletion - capture details
$recordDetails = $model->fetchById($id);

// Perform deletion
$ok = $model->deleteById($id);

if ($ok) {
    // Log the delete with enhanced information
    $logger = new Logger();
    $logger->logDelete(
        "Record deleted",
        "Module Name",
        (string)$id,
        $recordDetails,  // Full record data for reference
        $_SESSION['username'] ?? 'Unknown'
    );
}
```

### ✅ Step 3: Access Audit Trail
In your navigation, add a link to the audit trail:
```html
<a href="/admin/audit-trail">
    <i class="fas fa-history"></i> Audit Trail
</a>
```

### ✅ Step 4: Configure Permissions
Ensure only ADMIN users can access audit trail:
```php
if (strtoupper($_SESSION['usertype'] ?? '') !== 'ADMIN') {
    $this->redirect('/login');
}
```

## Best Practices

### Logging Details
Always include relevant information in the audit log:
```php
$details = [
    'record_id' => $id,
    'record_type' => 'Account',
    'username' => $deleted_account['username'],
    'status_before' => $deleted_account['status'],
    'department' => $deleted_account['department'],
    'timestamp' => date('Y-m-d H:i:s'),
    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown',
    'reason' => $_POST['reason'] ?? 'Not specified'
];

$logger->logDelete($action, $module, (string)$id, $details, $performer);
```

### Performance Considerations
- Archive old logs: Move logs older than 1 year to archive table
- Index the table:
  ```sql
  ALTER TABLE tbllogs ADD INDEX idx_action (action);
  ALTER TABLE tbllogs ADD INDEX idx_module (module);
  ALTER TABLE tbllogs ADD INDEX idx_datelog (datelog);
  ALTER TABLE tbllogs ADD INDEX idx_performedby (performedby);
  ```

### Security Recommendations
1. **Restrict Access**: Only grant audit trail access to senior admins
2. **Log Viewers**: Track who views audit logs
3. **Read-Only**: Never allow editing of audit logs
4. **Encryption**: Consider encrypting sensitive data in logs
5. **Retention**: Set a log retention policy

## Monitoring and Alerts

### High-Risk Activities to Monitor
- Mass deletions in short time period
- Deletions by new admin accounts
- After-hours deletion activities
- Deletions of sensitive records

### Recommended Queries
```php
// Get all deletes in last 24 hours
$recentDeletes = $auditTrail->getRecentDeleteActions(1);

// Get summary of delete activity
$summary = $auditTrail->getDeleteLogsSummary();

// Find who deleted what
$userDeletes = $auditTrail->getAuditsByPerformer('admin.user');
```

## Future Enhancements

1. **Advanced Reporting**
   - PDF export of audit reports
   - Email summaries of deletion activity
   - Custom date range reports

2. **Real-time Alerts**
   - Slack/Email notifications for mass deletions
   - Suspicious activity detection
   - Unauthorized access attempts

3. **Data Recovery**
   - Point-in-time snapshots
   - Rollback capabilities
   - Soft delete implementation

4. **Enhanced Analytics**
   - Deletion patterns analysis
   - User behavior tracking
   - Compliance reporting

## Troubleshooting

### Logs Not Appearing
1. Verify `tbllogs` table exists
2. Check database connection in Logger class
3. Ensure `logDelete()` is called after successful deletion

### Performance Issues
1. Add indexes to frequently queried columns
2. Archive old logs (>1 year)
3. Implement pagination with reasonable limits

### Access Issues
1. Verify user session has `account_id` and `usertype=ADMIN`
2. Check URL routing matches controller method
3. Review browser console for JavaScript errors

## Support and Maintenance

For questions or issues with the audit trail system:
1. Check application logs at `app/logs/`
2. Review database error logs
3. Test with sample delete operation
4. Verify file permissions on audit trail files

## Version History
- **v1.0** - Initial audit trail implementation
  - Delete operation logging
  - Audit trail dashboard
  - Filter and search capabilities
  - Summary statistics
