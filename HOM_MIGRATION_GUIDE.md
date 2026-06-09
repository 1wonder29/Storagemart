# OM to HOM (Head Of Operation) Migration Guide

## Overview
This guide provides step-by-step instructions for migrating from OM (Operations Manager) to HOM (Head Of Operation) naming convention throughout the TMS system.

## Summary of Changes

### 1. **New Files Created** ✅
The following new files have been created in the `/hom/` directories:

#### Models
- `app/Models/hom/HOMModel.php` - Replaces OMModel with updated database references
- `app/Models/hom/TicketRatingModel.php` - Replaces OMTicketRatingModel

#### Controllers  
- `app/Controllers/hom/HOMController.php` - Replaces OMController
- `app/Controllers/hom/HOMTicketController.php` - Replaces OMTicketController

#### Database
- `scripts/migration_rename_om_to_hom.sql` - Migration script to update database schema

### 2. **Updated Files** ✅
- `public/index.php` - Updated routes to support `/hom/` prefix (old `/om/` routes kept for backwards compatibility)

### 3. **Key Changes in Database Schema**

The migration script (`scripts/migration_rename_om_to_hom.sql`) will perform the following changes:

#### Table Renaming
- `tblom_employee_assignments` → `tblhom_employee_assignments`

#### Column Renaming
- `om_employee_id` → `hom_employee_id`

#### Role Update
- Role code: `'OM'` → `'HOM'`
- Role name: `'Operation Manager'` → `'Head Of Operation'`

#### View Renaming
- `vw_om_assignments` → `vw_hom_assignments`

#### Index Updates
- `idx_om_employee_id` → `idx_hom_employee_id`
- `idx_om_employee_active` → `idx_hom_employee_active`

## Installation Steps

### Step 1: Back Up Your Database
```bash
# Backup the current database before applying migration
mysqldump -u root -p howard_tms > howard_tms_backup_$(date +%Y%m%d_%H%M%S).sql
```

### Step 2: Apply the Database Migration
```bash
# Run the migration script
mysql -u root -p howard_tms < scripts/migration_rename_om_to_hom.sql
```

### Step 3: Verify Database Changes
```sql
-- Check the updated role
SELECT * FROM tblroles WHERE role_code = 'HOM';

-- Check the renamed table exists
DESCRIBE tblhom_employee_assignments;

-- Check updated usertype
SELECT account_id, username, usertype FROM tblaccounts WHERE usertype = 'HOM';
```

### Step 4: Test the New Routes
After deployment, test the following URLs:

- `/hom/dashboard` - HOM Dashboard
- `/hom/employees` - Manage employees
- `/hom/assignments` - View assignments
- `/hom/tickets` - View tickets
- `/hom/new-assignment` - Create assignment

## Backwards Compatibility

The `/om/` routes have been retained for **backwards compatibility**. Both `/om/` and `/hom/` routes will function identically and use the new HOM controllers/models. This means:

- Existing links to `/om/*` will continue to work
- New code should use `/hom/*` routes
- You can gradually update UI links to use `/hom/*`

## Important Notes

### For View Files
The views still reference the `/om/` directory paths. You have several options:

**Option A: Create Symbolic Links (Recommended)**
```bash
# In app/Views directory
cd app/Views
ln -s om hom
```

**Option B: Copy View Files**
```bash
cp -r app/Views/om app/Views/hom
```

**Option C: Keep Using Old Paths**
The old `/om/` view paths will continue to work since the controllers load them by name.

### For Updating Navigation/Links
When updating UI templates, change links like:
```html
<!-- OLD -->
<a href="/om/dashboard">Dashboard</a>

<!-- NEW -->
<a href="/hom/dashboard">Dashboard</a>
```

## User Type Changes

After running the migration, all accounts with `usertype = 'OM'` will be changed to `usertype = 'HOM'`. 

To verify:
```sql
SELECT COUNT(*) as hom_users FROM tblaccounts WHERE usertype = 'HOM';
```

## View Queries

The migration creates/updates the view `vw_hom_assignments`. You can query it like:

```sql
-- View all HOM assignments
SELECT * FROM vw_hom_assignments;

-- View assignments for a specific HOM
SELECT * FROM vw_hom_assignments WHERE hom_employee_id = 230005133;
```

## Rollback Instructions (If Needed)

If you need to rollback to the old OM naming:

1. Restore from backup:
```bash
mysql -u root -p howard_tms < howard_tms_backup_YYYYMMDD_HHMMSS.sql
```

2. Remove the new HOM files:
```bash
rm -rf app/Models/hom
rm -rf app/Controllers/hom
rm scripts/migration_rename_om_to_hom.sql
```

3. Revert index.php changes (if you prefer)

## Next Steps

### 1. Update View Files
- Copy or symlink views from `/om/` to `/hom/`
- Update page titles and labels from "Operation Manager" to "Head Of Operation"

### 2. Update Navigation/Sidebar
- Change menu items from `/om/` to `/hom/`
- Update labels and descriptions

### 3. Update Documentation
- The following documentation files may need updates (if they reference OM):
  - OM_IMPLEMENTATION_GUIDE.md → Consider renaming to HOM_IMPLEMENTATION_GUIDE.md
  - OM_QUICK_START.md → HOM_QUICK_START.md
  - OM_VALIDATION_CHECKLIST.md → HOM_VALIDATION_CHECKLIST.md
  - OM_IMPLEMENTATION_SUMMARY.md → HOM_IMPLEMENTATION_SUMMARY.md

### 4. Update Test Files
- Update test files that reference OM role (e.g., `test_om_rate_download.php`)

## Verification Checklist

- [ ] Database migration completed without errors
- [ ] Users with HOM role can log in successfully
- [ ] Dashboard loads at `/hom/dashboard`
- [ ] Employee assignments page works (`/hom/assignments`)
- [ ] Tickets can be created (`/hom/tickets/create`)
- [ ] Old `/om/` routes still work (backwards compatibility)
- [ ] Views display correctly
- [ ] No errors in PHP error log

## Database Queries for Verification

```sql
-- Count HOM users
SELECT COUNT(*) FROM tblaccounts WHERE usertype = 'HOM';

-- Check table exists
SHOW TABLES LIKE 'tblhom%';

-- Check column names
DESCRIBE tblhom_employee_assignments;

-- Verify view
SHOW CREATE VIEW vw_hom_assignments\G

-- Check indexes
SHOW INDEX FROM tblhom_employee_assignments;
```

## Support

For any issues:
1. Check the error logs at `app/logs/php_errors.log`
2. Verify database migration ran successfully
3. Ensure all files are in correct directories
4. Check file permissions (755 for directories, 644 for files)

## Files Modified/Created Summary

| File | Type | Status |
|------|------|--------|
| `app/Models/hom/HOMModel.php` | New | ✅ Created |
| `app/Models/hom/TicketRatingModel.php` | New | ✅ Created |
| `app/Controllers/hom/HOMController.php` | New | ✅ Created |
| `app/Controllers/hom/HOMTicketController.php` | New | ✅ Created |
| `scripts/migration_rename_om_to_hom.sql` | New | ✅ Created |
| `public/index.php` | Modified | ✅ Updated |
| `app/Views/om/*` | Existing | ℹ️ Can be symlinked to `/hom/` |

---

**Created**: 2026-06-05
**Migration Version**: 1.0
