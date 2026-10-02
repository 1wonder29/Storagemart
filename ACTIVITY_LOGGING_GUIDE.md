# System-Wide Activity Logging Guide

## Overview
The new ActivityLogger system provides comprehensive audit trail tracking across all modules (Admin, HR, Employee, IT, Head) and operations (Create, Read, Update, Delete, Login, Logout).

All activities are logged to the `tbllogs` database table with the following information:
- **Date & Time**: When the activity occurred (Asia/Manila timezone)
- **Operation Type**: CREATE, READ, UPDATE, DELETE, LOGIN, LOGOUT, etc.
- **Module**: Which part of the system (Admin - Accounts, HR - Uniforms, etc.)
- **Record ID**: The affected record's ID
- **Description**: Detailed description of the activity
- **Performed By**: Username who performed the action
- **Metadata**: Additional data (changes, details, IP address, etc.)

## Quick Start

### 1. Import the ActivityLogger Helper
Add this at the top of any controller:
```php
require_once __DIR__ . '/../Helpers/ActivityLogger.php';
```

### 2. Log Activities with Simple Calls
```php
// Log a CREATE operation
ActivityLogger::create('HR - Uniforms', $uniformId, 'New uniform added: ' . $uniformName, $username, [
    'name' => $uniformName,
    'size' => $size,
    'color' => $color
]);

// Log an UPDATE operation
ActivityLogger::update('Admin - Accounts', $accountId, 'Account status changed', $username, [
    'before' => ['status' => 'active'],
    'after' => ['status' => 'inactive']
]);

// Log a DELETE operation
ActivityLogger::delete('Admin - Assets', $assetId, 'Asset deleted', $username, [
    'asset_name' => $assetData['name'],
    'model' => $assetData['model'],
    'serialnumber' => $assetData['serialnumber']
]);
```

## Usage Examples by Module

### Authentication (Login/Logout)
```php
// In AuthController.php

// Successful login
ActivityLogger::login($username, true);

// Failed login
ActivityLogger::login($username, false, 'Invalid credentials');

// Logout
ActivityLogger::logout($username);

// Password reset
ActivityLogger::action('PASSWORD_RESET', 'Authentication', $accountId, 
    "Password reset for {$username}", $username);
```

### HR Module (Uniforms)
```php
// In HrController.php or UniformController.php

// New uniform added
ActivityLogger::create('HR - Uniforms', $uniformId, 
    "New uniform item: {$uniformName}", $_SESSION['username'], [
        'name' => $uniformName,
        'size' => $size,
        'color' => $color,
        'quantity' => $quantity
    ]);

// Uniform assigned to employee
ActivityLogger::action('ASSIGN', 'HR - Uniforms', $uniformId, 
    "Uniform assigned to employee {$employeeId}", $_SESSION['username']);

// Uniform returned by employee
ActivityLogger::action('RETURN', 'HR - Uniforms', $uniformId, 
    "Uniform returned by employee {$employeeId}", $_SESSION['username']);
```

### Admin Module (Accounts, Assets, Tickets)
```php
// Account management
ActivityLogger::delete('Admin - Accounts', $accountId, 
    "Account deleted: {$username}", $_SESSION['username'], [
        'account_id' => $accountId,
        'username' => $username,
        'usertype' => $usertype,
        'status' => $status
    ]);

// Asset management
ActivityLogger::create('Admin - Assets', $assetId, 
    "New asset added: {$assetName}", $_SESSION['username'], [
        'model' => $model,
        'serialnumber' => $serialnumber,
        'category' => $category
    ]);

// Ticket management
ActivityLogger::update('Admin - Tickets', $ticketId, 
    "Ticket status updated to: {$newStatus}", $_SESSION['username'], [
        'ticket_id' => $ticketId,
        'old_status' => $oldStatus,
        'new_status' => $newStatus
    ]);
```

### Employee Module (Requests, Tickets)
```php
// Asset request submitted
ActivityLogger::create('Employee - Requests', $requestId, 
    "Asset request submitted for: {$assetType}", $_SESSION['username'], [
        'asset_type' => $assetType,
        'quantity' => $quantity,
        'reason' => $reason
    ]);

// Employee ticket submitted
ActivityLogger::create('Employee - Tickets', $ticketId, 
    "Support ticket submitted", $_SESSION['username'], [
        'issue_type' => $issueType,
        'priority' => $priority,
        'description' => substr($description, 0, 100)
    ]);
```

### IT Module (Tickets, Assets)
```php
// IT ticket resolution
ActivityLogger::action('RESOLVE', 'IT - Tickets', $ticketId, 
    "Ticket resolved: {$resolution}", $_SESSION['username'], [
        'ticket_id' => $ticketId,
        'resolution_time' => $resolutionTime,
        'status' => 'RESOLVED'
    ]);

// Asset assigned to IT
ActivityLogger::action('ASSIGN_IT', 'IT - Assets', $assetId, 
    "Asset assigned to IT department", $_SESSION['username']);
```

### Head Module (Approvals, Reports)
```php
// Overtime approval
ActivityLogger::action('APPROVE', 'Head - Overtime', $overtimeId, 
    "Overtime request approved for employee {$employeeId}", $_SESSION['username'], [
        'employee_id' => $employeeId,
        'hours' => $hours,
        'date' => $date
    ]);

// Report generated
ActivityLogger::action('GENERATE', 'Head - Reports', $reportId, 
    "Report generated: {$reportType}", $_SESSION['username'], [
        'report_type' => $reportType,
        'date_range' => $dateRange
    ]);
```

## Viewing the Audit Trail

### Admin Dashboard
Admins can view all system activities at:
- **URL**: `/admin/audit-trail`
- **View**: [app/Views/admin/audit/audit_trail.php](../Views/admin/audit/audit_trail.php)

Features:
- View all activities across all modules
- Filter by:
  - Activity type (LOGIN, DELETE, CREATE, UPDATE, etc.)
  - Module (Admin, HR, Employee, etc.)
  - Date range
  - Search by record ID or description
  - Performer (user)
- Pagination with 50 items per page
- Summary statistics
- View detailed record history

### Accessing Audit Data Programmatically
```php
require_once __DIR__ . '/../Helpers/ActivityLogger.php';

// Get recent activities
$recent = ActivityLogger::getRecent(100);

// Get activities by type (e.g., all LOGINs)
$logins = ActivityLogger::getByType('LOGIN', 50, 0);

// Get activities by user
$userActivities = ActivityLogger::getByPerformer('john.doe', 50, 0);

// Get all activities for a specific record
$recordTrail = ActivityLogger::getRecordTrail('123');
```

## Database Structure

The `tbllogs` table stores all activities:

```sql
CREATE TABLE tbllogs (
    logid INT AUTO_INCREMENT PRIMARY KEY,
    datelog DATE NOT NULL,
    timelog TIME NOT NULL,
    action TEXT NOT NULL,          -- [OPERATION] Description | Data: {...}
    module VARCHAR(100) NOT NULL,  -- Module/Feature name
    ID VARCHAR(50) NOT NULL,       -- Record ID
    performedby VARCHAR(100),      -- Username
    INDEX idx_datelog (datelog),
    INDEX idx_action (action(50)),
    INDEX idx_module (module),
    INDEX idx_performedby (performedby)
);
```

## Best Practices

1. **Always include username**: Pass `$_SESSION['username']` or `$performedby` parameter
2. **Be specific with modules**: Use format "Role - Feature" (e.g., "HR - Uniforms")
3. **Include metadata**: Pass relevant data for complex operations
4. **Log before/after for updates**: Include old and new values
5. **Log deletions with full details**: Store deleted data for audit trail
6. **Use consistent naming**: Keep operation types consistent across the system
7. **Include record IDs**: Always provide the ID of affected records

## Common Operation Types

- **CREATE**: New record created
- **READ**: Record viewed/accessed
- **UPDATE**: Record modified
- **DELETE**: Record deleted
- **LOGIN**: User logged in
- **LOGOUT**: User logged out
- **FAILED_LOGIN**: Failed login attempt
- **PASSWORD_RESET**: Password changed
- **APPROVE**: Request/document approved
- **REJECT**: Request/document rejected
- **SUBMIT**: Form/request submitted
- **ASSIGN**: Item assigned to user/role
- **RETURN**: Item returned
- **RESOLVE**: Ticket/issue resolved
- **GENERATE**: Report/document generated

## Implementation Checklist

- [x] Logger.php enhanced with new methods
- [x] ActivityLogger.php helper created
- [x] AuthController.php updated with login/logout logging
- [ ] AdminController.php - Add logging to account/asset/ticket operations
- [ ] HrController.php - Add logging to HR operations
- [ ] UniformController.php - Add logging to uniform operations
- [ ] EmployeeController.php - Add logging to employee operations
- [ ] EmployeeAssetController.php - Add logging to asset requests
- [ ] HeadController.php - Add logging to head operations
- [ ] ItTicketController.php - Add logging to IT operations
- [ ] Audit trail view updated
- [ ] Testing of all logging points

## Troubleshooting

**Logs not appearing?**
1. Verify `ActivityLogger.php` is in `app/Helpers/` directory
2. Check that config.php has timezone set to Asia/Manila
3. Verify database connection is working
4. Check user permissions to insert into tbllogs table

**Timezone issues?**
1. Verify config.php sets `date_default_timezone_set('Asia/Manila')`
2. Verify database session timezone: `SET time_zone = '+08:00'`
3. Check system server timezone

**Performance issues with large logs?**
1. Consider archiving old logs (older than 1 year)
2. Add database indexes on commonly searched fields
3. Implement log cleanup/rotation script
