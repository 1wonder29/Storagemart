# AOM Implementation Validation Checklist

## Pre-Implementation Verification

- [ ] Backup existing database: `mysqldump -u root -p howard_tms > backup_`date +%Y%m%d_%H%M%S`.sql`
- [ ] Verify Apache/XAMPP running: `http://localhost`
- [ ] Verify PHP version supports JSON functions: `php -v`
- [ ] Confirm MySQL has JSON support: `mysql --version`
- [ ] Check write permissions in app/ and public/ directories

## Database Validation

### Migration Execution
```bash
# Execute main migration
mysql -u root -p howard_tms < scripts/migration_add_aom_role.sql

✅ Check:
- [ ] No errors reported
- [ ] All tables created
- [ ] All columns added
- [ ] All indexes created
- [ ] View created successfully
```

### Verification Queries
```sql
-- Table 1: Check tblroles exists and contains data
SELECT COUNT(*) as role_count FROM tblroles;
Expected: 6 or more

-- Table 2: Check tblbranch_assignments exists
SHOW COLUMNS FROM tblbranch_assignments;
Expected: assignment_id, aom_employee_id, branch_id, is_active, etc.

-- Table 3: Check columns added to tblaccounts
SHOW COLUMNS FROM tblaccounts LIKE 'role_id';
Expected: row with role_id INT

-- Table 4: Check columns added to tblemployee
SHOW COLUMNS FROM tblemployee LIKE 'role_id';
Expected: row with role_id INT

-- Table 5: Check columns added to tbltickets
SHOW COLUMNS FROM tbltickets LIKE 'aom_id';
Expected: row with aom_id INT

-- View: Check vw_aom_branches exists
SELECT * FROM vw_aom_branches LIMIT 1;
Expected: Returns one or more rows
```

- [ ] All tables exist
- [ ] All columns exist
- [ ] All relationships established
- [ ] Indexes created
- [ ] View accessible

## Test Data Validation

```bash
# Load test data
mysql -u root -p howard_tms < scripts/seed_aom_test_data.sql
```

### Verification
```sql
-- Check roles populated
SELECT role_code, role_name FROM tblroles;
Expected: ADMIN, EMPLOYEE, HEAD, HR, IT, AOM

-- Check AOM assignments
SELECT ba.*, e.firstname, e.lastname, b.branchName 
FROM tblbranch_assignments ba
JOIN tblemployee e ON ba.aom_employee_id = e.employee_id
JOIN tblbranch b ON ba.branch_id = b.branch_id
WHERE ba.is_active = 1;
Expected: 6 rows with 3 AOMs assigned to branches

-- Check specific AOM
SELECT * FROM vw_aom_branches 
WHERE aom_employee_id = 230005133;
Expected: Julie An Tangunan with Yakal and Fairview branches
```

- [ ] All roles created
- [ ] All AOM assignments created
- [ ] Employee records updated
- [ ] Branch assignments active

## File System Validation

### Models
- [ ] `app/Models/aom/AOMModel.php` exists
  ```bash
  ls -la app/Models/aom/AOMModel.php
  ```
  
- [ ] `app/Models/aom/AOMTicketModel.php` exists
  ```bash
  ls -la app/Models/aom/AOMTicketModel.php
  ```

- [ ] Both files have correct syntax
  ```bash
  php -l app/Models/aom/AOMModel.php
  php -l app/Models/aom/AOMTicketModel.php
  ```

### Controller
- [ ] `app/Controllers/aom/AOMController.php` exists
  ```bash
  ls -la app/Controllers/aom/AOMController.php
  ```

- [ ] File has correct syntax
  ```bash
  php -l app/Controllers/aom/AOMController.php
  ```

### Helper
- [ ] `app/Helpers/RBAC.php` exists
  ```bash
  ls -la app/Helpers/RBAC.php
  ```

- [ ] File has correct syntax
  ```bash
  php -l app/Helpers/RBAC.php
  ```

### Views
- [ ] `app/Views/aom/dashboard.php` exists
- [ ] `app/Views/aom/employees.php` exists
- [ ] `app/Views/aom/create-ticket.php` exists
- [ ] `app/Views/aom/tickets.php` exists

### Migrations
- [ ] `scripts/migration_add_aom_role.sql` exists
- [ ] `scripts/seed_aom_test_data.sql` exists

### Documentation
- [ ] `AOM_IMPLEMENTATION_GUIDE.md` exists
- [ ] `AOM_QUICK_START.md` exists
- [ ] `AOM_IMPLEMENTATION_SUMMARY.md` exists

## Routing Validation

### Check public/index.php

```bash
# Verify AOM routes added
grep -n "aom" public/index.php
```

Expected output shows:
```
- if (preg_match('/^\/aom\//', $_SERVER['REQUEST_URI']))
- AOMController routes mapped
- Multiple /aom/* patterns found
```

- [ ] AOM route prefix exists
- [ ] All endpoints mapped
- [ ] AJAX endpoint included

### Test Routes
```php
// In browser or curl:
GET http://localhost/aom/dashboard     // Should load (with login)
GET http://localhost/aom/employees     // Should load
GET http://localhost/aom/tickets       // Should load
```

- [ ] All routes accessible

## User Account Configuration

### Create AOM Account (if needed)

```sql
-- Find an employee to make AOM
SELECT employee_id, firstname, lastname FROM tblemployee LIMIT 1;

-- Get their account ID
SELECT account_id FROM tblaccounts 
WHERE employee_id = 230005133;

-- Make them AOM
UPDATE tblaccounts 
SET usertype = 'AOM' 
WHERE account_id = 2200540;

-- Assign to branches
INSERT INTO tblbranch_assignments (aom_employee_id, branch_id, assigned_by)
VALUES (230005133, 17, 2200426);

-- Verify
SELECT * FROM tblaccounts WHERE usertype = 'AOM';
```

- [ ] At least one AOM account exists
- [ ] AOM account has assigned branches
- [ ] AOM can log in

## Login & Access Testing

### Test AOM Login

1. Navigate to: `http://localhost/`
2. Log in with AOM credentials
3. Navigate to: `http://localhost/aom/dashboard`

Verify:
- [ ] Dashboard loads without errors
- [ ] Assigned branches display correctly
- [ ] Employee count shows
- [ ] Pending tickets show
- [ ] Navigation menu visible

### Test Employee Filtering

1. Go to **Employees** tab
2. Verify employee list shows

Verify:
- [ ] Only employees from assigned branches show
- [ ] Branch filter works
- [ ] Employee details accessible
- [ ] No unauthorized employees visible

### Test Ticket Creation

1. Go to **Tickets** → **Create New Ticket**
2. Check branch dropdown

Verify:
- [ ] Only assigned branches in dropdown
- [ ] Employee field optional
- [ ] Employee selector works after branch selection
- [ ] Form validation works
- [ ] Can submit ticket

### Test Ticket Management

1. Go to **Tickets** tab
2. View ticket list

Verify:
- [ ] Only tickets from assigned branches show
- [ ] Status filtering works
- [ ] Priority filtering works
- [ ] Branch filtering works
- [ ] Ticket details accessible

## Permission & Access Control Testing

### Test Access Denial

```php
// Try to access unauthorized branch/employee
// Should result in 403 Forbidden
```

Test cases:
- [ ] Cannot access branch not assigned
- [ ] Cannot view employees from other branches
- [ ] Cannot create ticket for other branches
- [ ] Cannot update tickets from other branches

### Test RBAC Helper

```php
// Test in controller or test script
RBAC::hasRole('AOM', 'AOM');           // Should return true
RBAC::hasPermission('AOM', 'create_tickets');  // Should return true
RBAC::hasPermission('EMPLOYEE', 'create_tickets');  // Should return false
```

- [ ] Role checks work
- [ ] Permission checks work
- [ ] Access enforcement works

## Browser Compatibility

Test on:
- [ ] Chrome
- [ ] Firefox
- [ ] Edge
- [ ] Safari (if available)

Verify:
- [ ] Responsive layout works
- [ ] Navigation functions
- [ ] DataTables work
- [ ] Forms submit correctly
- [ ] AJAX calls succeed
- [ ] No console errors

## Performance Testing

### Dashboard Load
```
Time: < 1 second
Expected: Quick load with statistics
```

### Employee List Load
```
Time: < 2 seconds
Expected: Full table with pagination
```

### Ticket Creation
```
Time: < 1 second
Expected: Form loads and responds to changes
```

- [ ] Dashboard loads quickly
- [ ] Employee list responsive
- [ ] Ticket creation fast
- [ ] No timeout errors

## Error Handling

Test error scenarios:
- [ ] Invalid branch ID shows error
- [ ] Missing required fields show validation
- [ ] Database errors handled gracefully
- [ ] Unauthorized access shows 403
- [ ] Not found shows 404

## Security Testing

```sql
-- Verify data isolation
SELECT * FROM tbltickets WHERE aom_id IS NOT NULL;
Expected: Only tickets created by AOMs have aom_id

-- Verify branch assignments
SELECT COUNT(*) FROM tblbranch_assignments WHERE is_active = 1;
Expected: At least one active assignment
```

- [ ] Data isolation verified
- [ ] No data leaks between branches
- [ ] Session management working
- [ ] CSRF protection active
- [ ] SQL injection prevented

## Documentation Review

- [ ] Read `AOM_QUICK_START.md`
- [ ] Review `AOM_IMPLEMENTATION_GUIDE.md`
- [ ] Check `AOM_IMPLEMENTATION_SUMMARY.md`
- [ ] All documentation current
- [ ] All examples working

## Final Checklist

- [ ] Database migrations complete
- [ ] Test data loaded
- [ ] All files in place
- [ ] Routing configured
- [ ] AOM account created
- [ ] Can login as AOM
- [ ] Dashboard accessible
- [ ] Features working
- [ ] Access control verified
- [ ] No errors in logs
- [ ] Documentation reviewed

## Sign-Off

```
Date: _______________
Tested By: _______________
Status: ✅ APPROVED / ❌ NEEDS FIXES

Issues Found (if any):
1. _______________
2. _______________
3. _______________

Notes:
_________________________________________________________________
_________________________________________________________________
_________________________________________________________________
```

## Troubleshooting

If any checks fail, refer to:
1. `AOM_IMPLEMENTATION_GUIDE.md` - Troubleshooting section
2. `AOM_QUICK_START.md` - Troubleshooting table
3. Check PHP error logs: `tail error_log`
4. Check MySQL logs for migration errors
5. Verify file permissions: `chmod 755 app/Models/aom/`

## Quick Recovery

If something fails:

```bash
# Restore backup
mysql -u root -p howard_tms < backup_YYYYMMDD_HHMMSS.sql

# Re-run migrations
mysql -u root -p howard_tms < scripts/migration_add_aom_role.sql

# Re-seed test data
mysql -u root -p howard_tms < scripts/seed_aom_test_data.sql
```

---

**Total Checks**: 50+
**Estimated Time**: 30-45 minutes
**Difficulty**: Low
**Success Rate Expected**: 95%+
