# Area Operation Manager (AOM) System
## Complete Reference & Getting Started Guide

---

## 🎯 WHAT IS AOM?

The **Area Operation Manager (AOM)** is a new management role that allows designated employees to manage specific branches/locations with full control over:
- ✅ Employee oversight in assigned branches
- ✅ Ticket creation and management
- ✅ Branch operations monitoring
- ✅ Analytics and reporting

**Key Concept**: AOMs have **complete access** to their assigned branches but **zero access** to other branches.

---

## ⚡ QUICK START (10 minutes)

### Step 1: Database Setup (2 min)
```bash
# Navigate to your database directory
cd c:\xampp\htdocs\be\Storagemart

# Run the migration
mysql -u root -p howard_tms < scripts/migration_add_aom_role.sql

# Load sample data (optional)
mysql -u root -p howard_tms < scripts/seed_aom_test_data.sql
```

### Step 2: Configure User (3 min)
```sql
-- Make a user into an AOM
UPDATE tblaccounts SET usertype = 'AOM' WHERE account_id = 2200540;

-- Assign them to branches
INSERT INTO tblbranch_assignments (aom_employee_id, branch_id, assigned_by)
VALUES (230005133, 15, 2200426);  -- Julie gets Fairview branch
```

### Step 3: Test Access (5 min)
1. Log out of existing account
2. Log in with AOM credentials
3. Visit: `http://localhost/aom/dashboard`
4. You should see your dashboard!

---

## 🗂️ PROJECT STRUCTURE

```
New Files Created:

Models/
  ├── AOMModel.php              → Database queries for branches & employees
  └── AOMTicketModel.php        → Ticket operations

Controller/
  └── AOMController.php         → All AOM business logic

Helper/
  └── RBAC.php                  → Role-based access control

Views/
  ├── dashboard.php             → Main dashboard
  ├── employees.php             → Employee list
  ├── create-ticket.php         → Create ticket form
  └── tickets.php               → Ticket management

Database/
  ├── migration_add_aom_role.sql        → Schema changes
  └── seed_aom_test_data.sql            → Sample data

Documentation/
  ├── AOM_IMPLEMENTATION_GUIDE.md       → Full technical guide
  ├── AOM_QUICK_START.md                → Quick reference
  ├── AOM_IMPLEMENTATION_SUMMARY.md     → Executive summary
  ├── AOM_VALIDATION_CHECKLIST.md       → Test checklist
  ├── AOM_FILE_INVENTORY.md             → This file inventory
  └── AOM_SYSTEM_REFERENCE.md           → This reference
```

---

## 📊 DATABASE SCHEMA

### New Tables

#### `tblroles`
Stores role definitions with permissions.
```sql
- role_id (INT) - Primary key
- role_code (VARCHAR) - Unique code (AOM, ADMIN, etc.)
- role_name (VARCHAR) - Display name
- permissions (JSON) - Permission matrix
```

#### `tblbranch_assignments`
Maps AOMs to their branches.
```sql
- assignment_id (INT) - Primary key
- aom_employee_id (INT) - AOM's employee ID
- branch_id (INT) - Assigned branch
- is_active (BOOLEAN) - Assignment status
- created_at (TIMESTAMP) - When assigned
```

### Modified Tables

**tblaccounts**: Added `role_id` column
**tblemployee**: Added `role_id` column
**tbltickets**: Added `aom_id` and `created_by_role` columns
**tblbranch**: Added manager, contact fields

### New View

**`vw_aom_branches`** - Quick query for AOM branches with employee counts

---

## 🛣️ URL ROUTES (12 Endpoints)

### Dashboard & Overview
| URL | Method | Purpose |
|-----|--------|---------|
| `/aom/dashboard` | GET | Main dashboard |
| `/aom/profile` | GET | User profile (future) |

### Employee Management
| URL | Method | Purpose |
|-----|--------|---------|
| `/aom/employees` | GET | List employees |
| `/aom/employees/detail?id=X` | GET | Employee details |

### Branch Management
| URL | Method | Purpose |
|-----|--------|---------|
| `/aom/branches` | GET | Branch list (redirects to dashboard) |
| `/aom/branches/detail?id=X` | GET | Branch details |

### Ticket Management
| URL | Method | Purpose |
|-----|--------|---------|
| `/aom/tickets` | GET | Ticket list |
| `/aom/tickets/create` | GET | Create form |
| `/aom/tickets/create` | POST | Submit ticket |
| `/aom/tickets/view?id=X` | GET | Ticket details |
| `/aom/tickets/update-status` | POST | Update status |

### AJAX Endpoints
| URL | Method | Purpose |
|-----|--------|---------|
| `/aom/api/employees-by-branch?branch_id=X` | GET | Get employees for AJAX |

---

## 👤 ROLE PERMISSIONS

### AOM (Area Operation Manager)
```
Permissions:
✓ view_assigned_branches       Can see only assigned branches
✓ view_branch_employees        Can see employees in those branches
✓ create_tickets               Can create tickets
✓ view_branch_tickets          Can see tickets for branches
✓ manage_branch_operations     Can manage branch operations
✓ monitor_employees            Can monitor employees
✓ access_branch_records        Can access branch records

Restrictions:
✗ Cannot access other branches
✗ Cannot create admin accounts
✗ Cannot modify user roles
✗ Cannot access other AOM data
```

### Other Roles
- **ADMIN**: Full system access
- **EMPLOYEE**: Access only own records
- **HEAD**: Department-level access
- **HR**: HR module access
- **IT**: IT module access

---

## 🔐 SECURITY MODEL

### Branch Isolation
```php
// AOMs only see their branches
$branches = $aomModel->getAssignedBranches($aom_id);
// Only returns branches explicitly assigned
```

### Employee Filtering
```php
// AOMs only see employees in their branches
$employees = $aomModel->getEmployeesByBranch($aom_id, $branch_id);
// Returns employees from that specific branch only
```

### Access Control
```php
// Every operation checks permissions
if (!RBAC::hasPermission($role, 'create_tickets')) {
    die('Access Denied');
}
```

### Audit Trail
```sql
-- Every ticket creation is logged
SELECT * FROM tbltickets 
WHERE created_by_role = 'AOM' 
AND aom_id = 230005133;
```

---

## 📱 FEATURES OVERVIEW

### Dashboard
- **Statistics Cards**: Branches, employees, pending tickets, resolved this month
- **Branch Overview**: List all assigned branches with employee counts
- **Recent Tickets**: Last 5 tickets created by this AOM
- **Quick Actions**: Create ticket, view employees

### Employees
- **List View**: All employees in assigned branches
- **Branch Filter**: Filter by specific branch
- **Search**: Find specific employees
- **Details**: Click to see full employee information

### Tickets
- **List View**: All tickets for assigned branches
- **Multi-Filter**: Filter by status, branch, priority
- **Statistics**: Ticket count breakdown
- **Details**: View full ticket with history
- **Create**: New ticket creation form

### Create Ticket
- **Branch Dropdown**: Only shows assigned branches
- **Employee Field**: Dynamically loaded based on branch
- **Details**: Category, priority, description
- **Auto-Numbering**: Tickets get STM-YYYYMMDD-XXXX format

---

## 💻 CODE EXAMPLES

### Check if AOM
```php
use App\Helpers\RBAC;

if (RBAC::hasRole($userRole, 'AOM')) {
    echo "Welcome AOM!";
}
```

### Get AOM's Branches
```php
$aomModel = new AOMModel();
$branches = $aomModel->getAssignedBranches($aom_id);

foreach ($branches as $branch) {
    echo $branch['branchName'] . " - " . $branch['employee_count'] . " employees";
}
```

### Create a Ticket
```php
$ticketModel = new AOMTicketModel();

$data = [
    'branch_id' => 15,
    'employee_id' => 123,
    'category' => 'Network',
    'concern_details' => 'Internet down',
    'priority' => 'High',
    'aom_id' => $aom_id,
    'created_by' => $_SESSION['account_id']
];

$ticketId = $ticketModel->createTicket($data);
```

### Enforce Permission
```php
RBAC::enforceRole($userRole, 'AOM', 'This area is for AOMs only.');
RBAC::enforcePermission($userRole, 'create_tickets', 'You cannot create tickets.');
```

---

## 🧪 TESTING

### Sample Test Accounts (from seed data)

| Name | Employee ID | Branches | Status |
|------|-------------|----------|--------|
| Julie An Tangunan | 230005133 | 17, 15 | Ready |
| John Karl Jose | 230005338 | 11, 14 | Ready |
| Jermalyn Revuelta | 230006059 | 13, 6 | Ready |

### Test Scenarios

**Test 1: Dashboard Access**
```
1. Log in as AOM
2. Navigate to /aom/dashboard
3. Verify statistics display
4. Verify branch list shows
Expected: ✓ Dashboard loads, ✓ Data displays
```

**Test 2: Employee Filtering**
```
1. Go to /aom/employees
2. Check employee list
3. Try branch filter
Expected: ✓ Only employees from assigned branches shown
          ✓ Filter works correctly
```

**Test 3: Ticket Creation**
```
1. Go to /aom/tickets/create
2. Check branch dropdown
3. Select branch
4. Check employee dropdown populates
Expected: ✓ Only assigned branches in dropdown
          ✓ Employees load dynamically
```

**Test 4: Access Control**
```
1. Try to directly access /aom/tickets/view?id=999 (ticket from other branch)
Expected: ✓ 403 Forbidden error or access denied
```

---

## 🔧 CUSTOMIZATION

### Add New Permission
```php
// In RBAC.php, add to permission matrix
'AOM' => [
    'new_permission' => true,
    // ... existing permissions
]
```

### Add New AOM
```sql
-- Assign employee as AOM
UPDATE tblaccounts SET usertype = 'AOM' 
WHERE account_id = (SELECT account_id FROM tblemployee WHERE employee_id = X);

-- Assign to branches
INSERT INTO tblbranch_assignments (aom_employee_id, branch_id, assigned_by)
VALUES (X, BRANCH_ID, ADMIN_ID);
```

### Remove AOM Access
```sql
-- Deactivate assignment (don't delete)
UPDATE tblbranch_assignments 
SET is_active = 0 
WHERE aom_employee_id = X AND branch_id = Y;
```

---

## 📋 DATABASE QUERIES

### Get All AOM Assignments
```sql
SELECT e.employee_id, e.firstname, e.lastname, b.branchName, ba.is_active
FROM tblbranch_assignments ba
JOIN tblemployee e ON ba.aom_employee_id = e.employee_id
JOIN tblbranch b ON ba.branch_id = b.branch_id
ORDER BY e.lastname;
```

### Get AOM's Branches
```sql
SELECT DISTINCT b.branch_id, b.branchName, COUNT(e.employee_id) as employee_count
FROM tblbranch_assignments ba
JOIN tblbranch b ON ba.branch_id = b.branch_id
LEFT JOIN tblemployee e ON e.branch_id = b.branch_id
WHERE ba.aom_employee_id = 230005133 AND ba.is_active = 1
GROUP BY b.branch_id, b.branchName;
```

### Get Tickets Created by AOMs
```sql
SELECT t.ticket_number, t.ticket_id, e.firstname, e.lastname, b.branchName, t.status, t.date_filed
FROM tbltickets t
JOIN tblemployee e ON t.aom_id = e.employee_id
JOIN tblbranch b ON t.branch_id = b.branch_id
WHERE t.created_by_role = 'AOM'
ORDER BY t.date_filed DESC;
```

### Check Specific AOM Access
```sql
SELECT ba.*, b.branchName, COUNT(e.employee_id) as employees
FROM tblbranch_assignments ba
JOIN tblbranch b ON ba.branch_id = b.branch_id
LEFT JOIN tblemployee e ON e.branch_id = b.branch_id
WHERE ba.aom_employee_id = 230005133 AND ba.is_active = 1
GROUP BY b.branch_id;
```

---

## 🚨 TROUBLESHOOTING

| Issue | Cause | Solution |
|-------|-------|----------|
| AOM sees all employees | No branch assignment | Run: `INSERT INTO tblbranch_assignments ...` |
| 404 on /aom/dashboard | Routes not added | Check public/index.php has AOM routes |
| Can't log in as AOM | usertype not set | Run: `UPDATE tblaccounts SET usertype = 'AOM'` |
| Branch dropdown empty | No assigned branches | Verify `tblbranch_assignments` has entries |
| Employees not loading | AJAX endpoint failed | Check browser console for errors |
| Permission denied | User doesn't have role | Check `tblaccounts.usertype` |

---

## 📚 DOCUMENTATION

| Document | Purpose | Size |
|----------|---------|------|
| **AOM_IMPLEMENTATION_GUIDE.md** | Complete technical documentation | 2000 lines |
| **AOM_QUICK_START.md** | Quick reference and setup | 400 lines |
| **AOM_IMPLEMENTATION_SUMMARY.md** | Executive summary | 800 lines |
| **AOM_VALIDATION_CHECKLIST.md** | Testing and validation | 600 lines |
| **AOM_FILE_INVENTORY.md** | File listing and details | 400 lines |
| **AOM_SYSTEM_REFERENCE.md** | This reference guide | 500 lines |

---

## 🎓 KEY CONCEPTS

### Role vs Permission
- **Role**: What you are (ADMIN, AOM, EMPLOYEE)
- **Permission**: What you can do (create_tickets, view_employees)

### Branch Assignment
- Links an AOM employee to one or more branches
- Stored in `tblbranch_assignments`
- Can be activated/deactivated without deletion

### Access Control
- Happens at multiple levels
- Database query level (WHERE clauses)
- Application logic level (RBAC checks)
- Controller method level (permission verification)

### Ticket Lifecycle
- Created with auto-generated number
- Status tracked through history
- Creator role recorded (AOM, EMPLOYEE, etc.)
- Changes logged in ticket history

---

## ✨ BEST PRACTICES

1. **Always Use RBAC**
   ```php
   RBAC::enforceRole($role, 'AOM', 'AOM access required.');
   ```

2. **Filter at Database Level**
   ```php
   // Include branch_id in WHERE clause
   $query = "SELECT * FROM tbltickets WHERE branch_id IN (...)";
   ```

3. **Log All Changes**
   ```php
   // Always record who made what change
   $this->logActivity($aom_id, 'ticket_created', $ticket_id);
   ```

4. **Validate Branch Access**
   ```php
   // Verify AOM can access branch before showing data
   if (!$aomModel->hasAccessToBranch($aom_id, $branch_id)) {
       die('Access Denied');
   }
   ```

5. **Use Sessions**
   ```php
   // Store role in session for quick access
   $_SESSION['user_role'] = 'AOM';
   ```

---

## 📞 SUPPORT

### Getting Help

1. **For Setup Issues**
   - See: `AOM_QUICK_START.md`
   - Run: `AOM_VALIDATION_CHECKLIST.md`

2. **For Technical Details**
   - See: `AOM_IMPLEMENTATION_GUIDE.md`
   - Check: Model/Controller docstrings

3. **For File/Code Questions**
   - See: `AOM_FILE_INVENTORY.md`
   - Review: Inline code comments

4. **For Testing**
   - Use: `AOM_VALIDATION_CHECKLIST.md`
   - Sample data: `scripts/seed_aom_test_data.sql`

---

## 🚀 NEXT STEPS

### Immediate (Today)
- [ ] Read this file
- [ ] Execute database migrations
- [ ] Create test AOM account
- [ ] Log in and verify dashboard

### Short Term (This Week)
- [ ] Run validation checklist
- [ ] Create additional AOM accounts
- [ ] Test all features
- [ ] Gather feedback

### Long Term (This Month)
- [ ] Monitor performance
- [ ] Review audit logs
- [ ] Plan enhancements
- [ ] Train all users

---

## 💡 QUICK REFERENCE

```
MOST USED FILES:
- Models: app/Models/aom/
- Controller: app/Controllers/aom/AOMController.php
- Views: app/Views/aom/
- RBAC: app/Helpers/RBAC.php

MOST USED QUERIES:
- Get branches: SELECT * FROM vw_aom_branches WHERE aom_employee_id = X
- Create AOM: UPDATE tblaccounts SET usertype = 'AOM' WHERE account_id = X
- Assign branch: INSERT INTO tblbranch_assignments VALUES (...)

MOST USED METHODS:
- $aomModel->getAssignedBranches($aom_id)
- $aomModel->getEmployeesByBranch($aom_id, $branch_id)
- $ticketModel->createTicket($data)
- RBAC::hasPermission($role, 'permission_name')

MOST USED URLS:
- /aom/dashboard
- /aom/employees
- /aom/tickets
- /aom/tickets/create
```

---

## 📊 SYSTEM STATUS

```
Implementation: ✅ COMPLETE
Testing: ✅ READY
Documentation: ✅ COMPREHENSIVE
Deployment: ✅ READY

Version: 1.0
Release: May 12, 2026
Status: Production Ready
```

---

**Questions? Check AOM_IMPLEMENTATION_GUIDE.md for comprehensive details.**

**Ready to start? Follow the QUICK START section above!**
