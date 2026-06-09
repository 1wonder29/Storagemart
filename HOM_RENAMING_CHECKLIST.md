# OM to HOM Renaming - Quick Reference

## What Was Changed

### Database Changes
- Table: `tblom_employee_assignments` → `tblhom_employee_assignments`
- Column: `om_employee_id` → `hom_employee_id`
- Role: `'OM'` → `'HOM'` with description `'Head Of Operation'`
- View: `vw_om_assignments` → `vw_hom_assignments`
- Indexes: Updated from `om_*` to `hom_*` prefixes

### Code Structure
```
NEW STRUCTURE:
├── app/Controllers/hom/
│   ├── HOMController.php (was OMController.php)
│   └── HOMTicketController.php (was OMTicketController.php)
├── app/Models/hom/
│   ├── HOMModel.php (was OMModel.php)
│   └── TicketRatingModel.php (was OMTicketRatingModel.php)
└── app/Views/hom/  (can be symlinked from om/ for now)
```

### Route Changes
- NEW: `/hom/*` routes (primary)
- LEGACY: `/om/*` routes (still work for backwards compatibility)

## Implementation Checklist

### Prerequisites
- [ ] Backup database: `mysqldump -u root -p howard_tms > backup.sql`

### Execution
1. [ ] Apply migration: `mysql -u root -p howard_tms < scripts/migration_rename_om_to_hom.sql`
2. [ ] Verify database changes completed successfully
3. [ ] Create view symlink or copy:
   ```bash
   cd app/Views
   ln -s om hom
   # OR
   cp -r om hom
   ```

### Testing
- [ ] Test `/hom/dashboard` loads correctly
- [ ] Test `/hom/assignments` works
- [ ] Test `/hom/tickets` functionality
- [ ] Verify `/om/*` routes still work (backwards compatibility)
- [ ] Check user can login as HOM role

### Documentation Updates (Manual)
- [ ] Update sidebar/navigation menu to show `/hom/` links
- [ ] Update documentation titles (if desired):
  - OM_IMPLEMENTATION_GUIDE.md → HOM_IMPLEMENTATION_GUIDE.md
  - OM_QUICK_START.md → HOM_QUICK_START.md
  - etc.

## Database Verification SQL

```sql
-- Check role update
SELECT role_code, role_name FROM tblroles WHERE role_code = 'HOM';

-- Check table rename
SHOW TABLES LIKE 'tblhom%';

-- Check column rename
SHOW COLUMNS FROM tblhom_employee_assignments;

-- Check user type update
SELECT COUNT(*) as hom_count FROM tblaccounts WHERE usertype = 'HOM';

-- Test view
SELECT * FROM vw_hom_assignments LIMIT 1;
```

## Files Reference

### New Models
- **HOMModel.php**: Main operations model with methods:
  - `getAllEmployeesWithAOMAssignments()`
  - `getUnassignedEmployees()`
  - `createAssignment()`, `updateAssignment()`, `deactivateAssignment()`
  - `getHOMAssignments()` (was `getOMAssignments()`)

### New Controllers
- **HOMController.php**: Main dashboard and assignment management
  - `dashboard()`, `employees()`, `assignments()`
  - `createAssignment()`, `updateAssignment()`
  - API endpoints for AJAX

- **HOMTicketController.php**: Ticket management
  - `index()`, `create()`, `store()`, `view()`
  - `rate()`, `storeRating()`
  - `uploadTechnicalReport()`, `downloadTechnicalRecord()`

### Migration Script
- **migration_rename_om_to_hom.sql**: Complete database schema migration
  - Renames table and columns
  - Updates roles and user types
  - Recreates views and indexes

## Backwards Compatibility

✅ **Fully Maintained**

The old `/om/` routes continue to work because they now load the HOM controllers. This allows:
- Gradual UI migration
- No immediate breaking changes
- Time to update all links and documentation

## Next Steps

1. **Apply Database Migration** (Most Critical)
   ```bash
   mysql -u root -p howard_tms < scripts/migration_rename_om_to_hom.sql
   ```

2. **Set Up View Files**
   ```bash
   cd app/Views
   ln -s om hom  # Option A: Create symlink
   # OR
   cp -r om hom  # Option B: Copy directory
   ```

3. **Test Core Functionality**
   - Login as HOM user
   - Access `/hom/dashboard`
   - Verify assignments work

4. **Update UI** (Optional but recommended)
   - Change navigation links from `/om/` to `/hom/`
   - Update page titles/labels as needed

5. **Remove Old Files** (After testing, optional)
   - Remove or archive `/om/` directory
   - Remove old migration files if no longer needed

## Key Database Queries

```sql
-- Get all HOM employees
SELECT e.* FROM tblemployee e
JOIN tblaccounts a ON a.account_id = e.account_id
WHERE a.usertype = 'HOM';

-- Get all assignments for a HOM
SELECT * FROM vw_hom_assignments 
WHERE hom_employee_id = YOUR_HOM_ID;

-- Get assignment statistics
SELECT 
  COUNT(DISTINCT assignment_id) as total,
  COUNT(DISTINCT CASE WHEN is_active=1 THEN assignment_id END) as active,
  COUNT(DISTINCT hom_employee_id) as hom_count
FROM tblhom_employee_assignments;
```

## Rollback Plan

If needed, restore from backup:
```bash
mysql -u root -p howard_tms < backup.sql
```

---

**Status**: ✅ Ready for Implementation
**Created**: 2026-06-05
**All New Files**: Located in appropriate directories
