# Pending Tickets Not Appearing in Admin - FIX DOCUMENTATION

## Issue Summary
When tickets were marked as pending (or newly created tickets which default to 'Pending' status), they did not appear in the Admin Pending Tickets module at `/admin/pendings`.

## Root Cause Analysis

### The Problem
The `fetchPendingTickets()` method in the Ticket model was using an INNER JOIN with the assets table:

```php
// OLD CODE (BROKEN)
JOIN {$this->tblassets} i ON t.inventory_id = i.inventory_id
```

When a ticket was created **without selecting an asset** (i.e., `inventory_id = NULL`), the INNER JOIN would filter it out completely because NULL values don't match in INNER JOINs.

### Why inventory_id Could Be NULL
In the admin TicketController's `storeFile()` method (line 305):
```php
$inventory_id = !empty($_POST['inventory_id']) ? (int)$_POST['inventory_id'] : null;
```

The inventory_id is optional and can be NULL when:
- Admin creates a ticket without assigning an asset
- Employee creates a ticket without specifying an asset
- General service tickets that aren't asset-specific

## Solution Implemented

### Changes Made
Changed the INNER JOIN to LEFT JOIN and added NULL handling:

**File: [app/Models/admin/Ticket.php](app/Models/admin/Ticket.php#L485)**

```php
// NEW CODE (FIXED)
LEFT JOIN {$this->tblassets} i ON t.inventory_id = i.inventory_id
LEFT JOIN {$this->tblgroup} g ON i.group_id = g.group_id
```

Also updated the asset_info column to handle NULL values:
```php
// Handle NULL asset values
CONCAT(IFNULL(i.assetNumber, 'N/A'), ' - ', IFNULL(g.groupName, 'N/A')) AS asset_info
```

### Files Updated
1. ✅ `app/Models/admin/Ticket.php` - Main working model
2. ✅ `dump/Storage-Mart-main/app/Models/admin/Ticket.php` - Backup version
3. ✅ `dump/Test/app/Models/admin/Ticket.php` - Test version

## Testing the Fix

### Manual Testing
Run this PHP script to test:
```php
require 'config/config.php';
require 'app/Models/BaseModel.php';
require 'app/Models/admin/Ticket.php';

$model = new Ticket();
$tickets = $model->fetchPendingTickets();
echo "Pending tickets found: " . count($tickets);
```

### Browser Testing
1. Navigate to `/admin/pendings`
2. Create a new ticket without selecting an asset
3. The ticket should now appear in the pending tickets table with "N/A - N/A" for asset info

## Impact
- ✅ Pending tickets without assets will now display correctly
- ✅ Pending tickets with assets will continue to work as before
- ✅ Admin dashboard pending count will be more accurate
- ✅ No breaking changes to existing functionality

## Related Issues
This same pattern (INNER JOIN on optional asset table) may exist in other queries. If other ticket lists aren't working correctly, check for the same issue.
