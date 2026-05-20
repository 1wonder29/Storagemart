# Foreign Key Constraint Violation - FIX APPLIED

## Problem
You were getting this error when creating tickets:
```
Fatal error: Uncaught PDOException: SQLSTATE[23000]: Integrity constraint violation: 1452 
Cannot add or update a child row: a foreign key constraint fails (`howard_tms`.`tbltickets`, 
CONSTRAINT `tbltickets_ibfk_2` FOREIGN KEY (`inventory_id`) 
REFERENCES `tblassets_inventory` (`inventory_id`))
```

## Root Cause
1. When creating a ticket without selecting an inventory_id, the code was defaulting to `0`
2. The value `0` doesn't exist in the `tblassets_inventory` table
3. The foreign key constraint prevented the insert because `0` is not a valid inventory_id

## Fix Applied

### ✅ Step 1: Updated All Controllers (DONE)
Changed all ticket creation controllers to use **NULL** instead of **0** for missing inventory_id:

- ✅ `app/Controllers/head/headTicketController.php` (line ~155)
- ✅ `app/Controllers/om/OMTicketController.php` (line 159)
- ✅ `app/Controllers/it/TicketController.php` (line 99)
- ✅ `app/Controllers/hr/HrTicketController.php` (line 155)
- ✅ `app/Controllers/employee/TicketController.php` (line 116)
- ✅ `app/Controllers/admin/TicketController.php` (line 306)

**Changed pattern:**
```php
// BEFORE (incorrect):
'inventory_id' => (int)($_POST['inventory_id'] ?? 0)

// AFTER (correct):
'inventory_id' => !empty($_POST['inventory_id']) ? (int)$_POST['inventory_id'] : null
```

### ⚠️ Step 2: Apply Database Migration (YOU NEED TO DO THIS)
The database column `inventory_id` in `tbltickets` currently does NOT allow NULL. You must run this migration:

**Option A: Via PHP Script**
1. Open your browser and go to: `http://localhost:8000/run_migration_allow_null_inventory.php`
2. Click execute or wait for the script to run
3. You should see: "✓ Migration completed successfully!"

**Option B: Via PhpMyAdmin or Direct SQL**
Execute this SQL command:
```sql
ALTER TABLE `tbltickets` MODIFY COLUMN `inventory_id` int(11) NULL;
```

**Option C: Via Command Line (if available)**
```bash
mysql -u root -p howard_tms < scripts/migration_allow_null_inventory.sql
```

## Verification
After applying the migration, verify it worked:

**In PhpMyAdmin:**
- Go to `tbltickets` table structure
- Look for `inventory_id` column
- The "Null" column should show "Yes" instead of "No"

**Or run this query:**
```sql
DESCRIBE tbltickets;
```
Look for inventory_id row - the "Null" column should show "YES"

## How It Works Now
- When a user creates a ticket **without** selecting an asset: `inventory_id` = NULL ✅
- When a user creates a ticket **with** an asset: `inventory_id` = valid asset ID ✅
- Foreign key constraint is satisfied because:
  - NULL values are always allowed in foreign key columns
  - Non-NULL values must reference valid records in `tblassets_inventory` ✅

## Next Steps
1. **Apply the database migration** (Step 2 above) - CRITICAL
2. Test creating a ticket without selecting an asset - should work now
3. Test creating a ticket with an asset - should still work
4. The error should be resolved!

## Files Modified
- `app/Controllers/head/headTicketController.php`
- `app/Controllers/om/OMTicketController.php`
- `app/Controllers/it/TicketController.php`
- `app/Controllers/hr/HrTicketController.php`
- `app/Controllers/employee/TicketController.php`
- `app/Controllers/admin/TicketController.php`
- Created: `run_migration_allow_null_inventory.php` (for easy migration)

## Related Files
- Migration script: `scripts/migration_allow_null_inventory.sql`
- Ticket model: `app/Models/employee/Ticket.php`
- Database: `howard_tms` database, `tbltickets` table

---
**Status:** Code fixes ✅ COMPLETE | Database migration ⏳ PENDING
