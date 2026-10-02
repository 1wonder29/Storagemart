# OM (Operation Manager) Role Implementation Guide

## Overview

The **OM (Operation Manager)** role has been added to the Storage Mart system to manage employee assignments to Area Operation Managers (AOMs). OMs handle the assignment of employees to AOMs and can track these assignments throughout the system.

## Implementation Date
**May 13, 2026**

## Components Implemented

### 1. Database Schema ✅
- **Table: `tblom_employee_assignments`**
  - Tracks which employees are assigned to which AOMs
  - Includes assignment date, notes, and active status
  - Unique constraint on employee-AOM pairs
  - Foreign keys to tblemployee table

- **View: `vw_om_assignments`**
  - Quick query view for OM assignments with full employee and AOM details
  - Filters active assignments only

- **Role Entry: `tblroles`**
  - Added OM role with description and permissions
  - Permissions: assign_employees_to_aom, manage_aom_assignments, etc.

### 2. Database Migrations ✅
- **`scripts/migration_add_om_role.sql`**
  - Creates OM role in tblroles
  - Creates tblom_employee_assignments table
  - Creates vw_om_assignments view
  - Creates necessary indexes for performance

### 3. Models ✅
- **`app/Models/om/OMModel.php`**
  - `getAllEmployeesWithAOMAssignments()` - Get all employees with their assignments
  - `getUnassignedEmployees()` - Get employees not assigned to any AOM
  - `getAllActiveAOMs()` - Get list of active AOMs
  - `createAssignment()` - Create new employee-AOM assignment
  - `updateAssignment()` - Update existing assignment
  - `deactivateAssignment()` - Deactivate an assignment
  - `getAssignmentById()` - Get specific assignment details
  - `getEmployeeAssignments()` - Get all assignments for an employee
  - `getOMAssignments()` - Get assignments managed by a specific OM
  - `getAssignmentStats()` - Get summary statistics

### 4. Controller ✅
- **`app/Controllers/om/OMController.php`**
  - `requireOM()` - Role verification
  - `dashboard()` - OM dashboard with statistics
  - `employees()` - Manage employees view
  - `assignments()` - View all assignments
  - `createAssignment()` - Create new assignment (GET/POST)
  - `updateAssignment()` - Edit assignment (GET/POST)
  - `deactivateAssignment()` - Deactivate assignment
  - AJAX endpoints for dynamic data loading

### 5. Views (UI) ✅
- **`app/Views/om/dashboard.php`**
  - Dashboard with assignment statistics
  - Quick action buttons
  - Recent assignments display
  - Summary cards showing total, active assignments and assigned employees

- **`app/Views/om/employees.php`**
  - Employee list with search functionality
  - Shows current AOM assignments
  - Quick assignment buttons

- **`app/Views/om/assignments.php`**
  - All employee-AOM assignments
  - Search and filter functionality
  - Edit/deactivate assignment options

- **`app/Views/om/create-assignment.php`**
  - Form to create new employee-AOM assignment
  - Dropdown lists for unassigned employees and active AOMs
  - Notes field for assignment details

- **`app/Views/om/edit-assignment.php`**
  - Edit existing assignment
  - Change assigned AOM
  - Deactivate assignment option
  - Display assignment metadata

### 6. RBAC System ✅
- **`app/Helpers/RBAC.php`** - Updated with OM role
  - Added `ROLE_OM` constant
  - Added OM to available roles list
  - Defined OM permissions:
    - view_all_employees
    - view_all_aoms
    - assign_employees_to_aom
    - manage_aom_assignments
    - view_assignment_history
    - create_assignments
    - update_assignments
    - deactivate_assignments
    - view_aom_branches
    - access_assignment_records

### 7. Routing ✅
- **`public/index.php`** - Added OM routes
  - `/om` - OM dashboard
  - `/om/dashboard` - Dashboard view
  - `/om/employees` - Employee management
  - `/om/assignments` - View assignments
  - `/om/new-assignment` - Create assignment (GET/POST)
  - `/om/edit-assignment` - Edit assignment (GET/POST)
  - `/om/deactivate-assignment` - Deactivate assignment (POST)
  - `/om/api/unassigned-employees` - AJAX endpoint
  - `/om/api/aoms` - AJAX endpoint
  - `/om/api/employee-assignments` - AJAX endpoint

### 8. Test Data ✅
- **`scripts/seed_om_test_data.sql`**
  - Sample OM account creation scripts
  - Example assignment data
  - Verification queries

## User Workflow

### OM Dashboard
1. OM logs in and sees dashboard
2. View statistics: total assignments, active assignments, assigned employees
3. See recent assignments
4. Quick action buttons to create assignments or manage employees

### Create Assignment
1. Click "Create New Assignment" or "New Assignment" button
2. Select an unassigned employee
3. Select the AOM to assign them to
4. Add optional notes
5. Submit to create the assignment

### Edit Assignment
1. Go to "My Assignments" or edit from employees list
2. Click "Edit" on any assignment
3. Change the assigned AOM
4. Update notes if needed
5. Submit to save changes

### Deactivate Assignment
1. Click "Edit" on assignment
2. Click "Deactivate Assignment" button
3. Confirm deactivation in modal
4. Assignment moves to inactive status

## API Endpoints (AJAX)

### Get Unassigned Employees
```
GET /om/api/unassigned-employees
```
Returns JSON list of employees not assigned to any AOM

### Get Active AOMs
```
GET /om/api/aoms
```
Returns JSON list of all active Area Operation Managers

### Get Employee Assignments
```
GET /om/api/employee-assignments?employee_id=123
```
Returns JSON list of assignments for a specific employee

## Database Relationships

```
tblemployee (OM)
    ↓
tblom_employee_assignments
    ↓
tblemployee (assigned employee)
tblemployee (AOM)
```

## Setup Instructions

### 1. Run Migration
```bash
mysql howard_tms < scripts/migration_add_om_role.sql
```

### 2. Load Test Data (Optional)
```bash
mysql howard_tms < scripts/seed_om_test_data.sql
```

### 3. Create OM User
```sql
INSERT INTO tblaccounts (username, password, usertype, status, createdby, datecreated)
VALUES ('om_user', PASSWORD('password123'), 'OM', 'ACTIVE', 'admin', NOW());
```

### 4. Create Corresponding Employee Record
```sql
INSERT INTO tblemployee (firstname, lastname, email, position, department, usertype)
VALUES ('John', 'Manager', 'john@example.com', 'Operation Manager', 'Operations', 'OM');
```

### 5. Access OM Dashboard
```
http://localhost/om/dashboard
```

## Features

1. **Employee Management**
   - View all employees with their current AOM assignments
   - Filter and search employees
   - Quick assignment creation from employee list

2. **Assignment Management**
   - Create employee-AOM assignments
   - Edit existing assignments (change AOM)
   - Deactivate assignments
   - Add notes to assignments

3. **Dashboard & Analytics**
   - Total assignment count
   - Active assignment count
   - Employee count under management
   - Recent assignments display

4. **Conflict Prevention**
   - Prevents duplicate employee-AOM assignments
   - Unique constraint on database level
   - Validation on application level

5. **Audit Trail**
   - Track who created/modified assignments
   - Assignment date timestamp
   - Notes field for context

## Permission Matrix

| Action | OM | AOM | HEAD | HR | ADMIN |
|--------|----|----|------|----|----|
| View all employees | ✅ | ✓ | ✓ | ✅ | ✅ |
| View all AOMs | ✅ | ✗ | ✗ | ✗ | ✅ |
| Assign employees to AOM | ✅ | ✗ | ✗ | ✗ | ✅ |
| Manage assignments | ✅ | ✗ | ✗ | ✗ | ✅ |
| View assignment history | ✅ | ✓ | ✗ | ✓ | ✅ |

Legend: ✅ = Full Access, ✓ = Limited Access, ✗ = No Access

## Key Files Modified

### New Files
- `app/Models/om/OMModel.php`
- `app/Controllers/om/OMController.php`
- `app/Views/om/dashboard.php`
- `app/Views/om/employees.php`
- `app/Views/om/assignments.php`
- `app/Views/om/create-assignment.php`
- `app/Views/om/edit-assignment.php`
- `scripts/migration_add_om_role.sql`
- `scripts/seed_om_test_data.sql`

### Modified Files
- `app/Helpers/RBAC.php` - Added OM role and permissions
- `public/index.php` - Added OM routing

## Error Handling

- Invalid role checks
- Non-existent assignment handling
- Duplicate assignment prevention
- Database transaction safety
- Permission validation on all endpoints

## Future Enhancements

1. Bulk assignment operations
2. Assignment templates
3. Assignment transfer between AOMs
4. Performance reports and metrics
5. Email notifications on assignment changes
6. Assignment history/audit log view
7. Integration with payroll system
8. Mobile app support
9. Automated assignment rules
10. Manager approval workflow

## Testing

### Manual Testing Steps

1. **Create OM User**
   - Create OM account in tblaccounts
   - Create employee record with OM usertype
   - Login as OM

2. **View Dashboard**
   - Verify statistics display correctly
   - Check recent assignments show

3. **Create Assignment**
   - Select unassigned employee
   - Select AOM
   - Verify assignment created

4. **Edit Assignment**
   - Edit assignment
   - Change AOM
   - Verify update successful

5. **Deactivate Assignment**
   - Deactivate active assignment
   - Verify status changes to inactive

## Database Queries for Reporting

### Get OM's Assignments
```sql
SELECT * FROM vw_om_assignments 
WHERE om_employee_id = 123;
```

### Get Unassigned Employees
```sql
SELECT * FROM tblemployee 
WHERE employee_id NOT IN (
    SELECT employee_id FROM tblom_employee_assignments 
    WHERE is_active = 1
);
```

### Get Employee's Current AOM
```sql
SELECT aom.* FROM tblemployee aom
JOIN tblom_employee_assignments oea ON aom.employee_id = oea.aom_id
WHERE oea.employee_id = 456 AND oea.is_active = 1;
```

## Troubleshooting

### Issue: OM role not appearing in dropdown
**Solution:** Run migration and verify tblroles table

### Issue: Can't create assignment
**Solution:** 
- Check employee is not already assigned
- Verify AOM exists and is active
- Check usertype is 'OM' for logged-in user

### Issue: Missing permissions
**Solution:** 
- Clear browser cache
- Re-login to refresh session
- Verify RBAC.php has OM role

## Contact & Support

For issues or questions regarding the OM role implementation, please refer to:
- System Administrator
- Implementation Documentation
- Database Logs in `/app/logs/`

## Version History

| Version | Date | Changes |
|---------|------|---------|
| 1.0 | 2026-05-13 | Initial OM role implementation |

---

**Last Updated:** May 13, 2026
**Implemented By:** Development Team
**Status:** ✅ Complete and Ready for Testing
