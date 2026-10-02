# Audit Trail Quick Reference Guide

## Access the Audit Trail
**URL**: `/admin/audit-trail`
**Permission**: Admin role only

## Dashboard Overview
The audit trail dashboard displays:
- **Total Deletions (Last 7 Days)**: High-risk activity count
- **Total Log Entries**: Complete audit log size
- **Modules with Deletions**: Count of affected system areas

## Filtering Options

### 1. View All Entries
Default view shows all audit logs with newest first.

### 2. Delete Operations Only
Filter to show only `[DELETE]` flagged operations.

### 3. By Module
Filter by specific module:
- Account Management
- Asset Inventory
- Ticket Management
- Group Management
- Category Management
- Branch Management

### 4. By User
Show all actions performed by a specific admin.

### 5. By Date Range
View logs between specific dates for historical analysis.

### 6. Search
Full-text search across:
- Action descriptions
- Module names
- Record IDs
- Performer usernames

## Log Entry Information

Each log entry shows:
| Field | Description |
|-------|-------------|
| **Date** | YYYY-MM-DD format |
| **Time** | HH:MM:SS format |
| **Action** | What was done (prefixed with [DELETE] if deletion) |
| **Module** | Which system area affected |
| **Record ID** | ID of affected record |
| **Performed By** | Username of admin who performed action |
| **Actions** | View details button |

## Delete Operations Summary
Shows aggregated deletion data by module:
- Total deletions per module
- When each module's last deletion occurred
- Direct link to filter by that module

## Adding Audit Logging to New Delete Functions

### Step 1: Import Logger
```php
require_once __DIR__ . '/../../Models/admin/Logger.php';
```

### Step 2: Capture Record Details Before Deletion
```php
$model = new YourModel();
$recordDetails = $model->fetchById($id);
```

### Step 3: Perform Deletion
```php
$ok = $model->deleteById($id);
```

### Step 4: Log the Delete Operation
```php
if ($ok) {
    $logger = new Logger();
    
    $auditDetails = [
        'record_id' => $id,
        'type' => 'YourRecordType',
        'details' => $recordDetails,
        'deleted_at' => date('Y-m-d H:i:s'),
        'deleted_by' => $_SESSION['username'] ?? 'Unknown'
    ];
    
    $logger->logDelete(
        "Record #{$id} deleted",
        "Your Module Name",
        (string)$id,
        $auditDetails,
        $_SESSION['username'] ?? 'Unknown'
    );
    
    $_SESSION['flash_success'] = 'Record deleted and logged.';
}
```

## Key Files Modified/Created

### New Files
- `app/Models/admin/AuditTrail.php` - Audit log query model
- `app/Views/admin/audit/audit_trail.php` - Audit dashboard UI

### Modified Files
- `app/Models/admin/Logger.php` - Enhanced with delete logging methods
- `app/Controllers/admin/AdminController.php` - Added audit trail route and enhanced delete logging

## Common Tasks

### View Recent Deletions
1. Click **Audit Trail** in admin menu
2. Set Filter Type to **Delete Operations Only**
3. Click **Apply Filter**

### Find Who Deleted Something
1. Use **Search** and enter Record ID or username
2. Filter results by **By User** dropdown
3. Review the complete action history

### Generate Date Range Report
1. Set Filter Type to **By Date Range**
2. Enter Start Date and End Date
3. Click **Apply Filter**
4. Review results and pagination

### Monitor Module Activity
1. Set Filter Type to **By Module**
2. Select module from dropdown
3. View deletion summary at bottom of page

## API Endpoints

### Get Audit Trail (JSON)
```
GET /admin/audit-detail?record_id=123
```

Response:
```json
{
  "success": true,
  "data": [
    {
      "datelog": "2026-05-05",
      "timelog": "14:30:45",
      "action": "[DELETE] Account deleted",
      "module": "Account Management",
      "ID": "123",
      "performedby": "admin.user"
    }
  ]
}
```

## Sample Log Entries

### Account Deletion
```
Date: 2026-05-05 | Time: 14:30:45
Action: [DELETE] Account #123 (john.doe) deleted | Details: username, status, department
Module: Account Management
Record ID: 123
Performed By: admin.user
```

### Asset Deletion
```
Date: 2026-05-05 | Time: 15:45:12
Action: [DELETE] Asset Item #456 deleted
Module: Asset Inventory
Record ID: 456
Performed By: admin.user
```

## Compliance & Security

- All deletions are permanently logged
- Audit logs cannot be modified or deleted
- Each log entry includes timestamp and performer
- Filtered views help track compliance
- Data suitable for audit reports

## Troubleshooting

### Deletions not appearing in audit trail?
1. Check database connection
2. Verify `logDelete()` method is called in delete function
3. Check for PHP errors in application logs
4. Verify `tbllogs` table exists

### Filters not working?
1. Clear browser cache (Ctrl+Shift+Delete)
2. Check URL parameters are correct
3. Verify table has data matching filter criteria
4. Check browser console for JavaScript errors

### Performance slow?
1. Try narrower date range
2. Use specific module filter instead of all logs
3. Reduce page limit (default is 50)
4. Check for index on `datelog` column

## Best Practices

✅ **DO**
- Check audit trail regularly for suspicious activity
- Archive logs older than 1 year
- Include detailed information when deleting records
- Document reasons for deletions in remarks

❌ **DON'T**
- Allow non-admin users to access audit trail
- Delete audit logs directly from database
- Store sensitive passwords in audit details
- Ignore multiple deletions by same user

## Contact & Support
For issues or questions about the audit trail system, refer to:
- `AUDIT_TRAIL_SETUP.md` - Full setup documentation
- Application logs - Located in `app/logs/`
- Database error logs - MySQL/MariaDB logs
