# Area Operation Manager (AOM) Role Implementation

## Overview

The AOM (Area Operation Manager) role is a new role-based management system enhancement that allows designated employees to manage specific branches/locations. AOMs have controlled access to employees and operations within their assigned branches only.

## Features

### 1. Role-Based Access Control (RBAC)
- **Role Definition**: AOM is a new role that sits between employees and admin
- **Permissions**:
  - View assigned branches
  - Manage employees in assigned branches
  - Create tickets for assigned branches
  - Monitor employee details and tasks
  - Access branch-specific records

### 2. Branch Assignment
- One AOM can manage multiple branches
- Each branch can have multiple AOMs (managers)
- Dynamic assignment/reassignment possible
- Access control enforced at database level

### 3. Employee Management
- View all employees in assigned branches
- Access employee details and information
- Filter employees by branch
- Monitor employee positions and departments

### 4. Ticket Creation & Management
- AOMs can create tickets for their branches
- Dropdown shows only assigned branches
- Automatic ticket numbering (STM-YYYYMMDD-XXXX)
- Track ticket status through workflow
- View ticket history and details

### 5. Dashboard & Analytics
- Display assigned branches count
- Total employees overview
- Pending/in-progress ticket counts
- Monthly resolved tickets statistics
- Quick access to recent tickets
- Branch statistics

## Database Schema

### New Tables

#### `tblroles`
Stores available system roles and their permissions.

```sql
CREATE TABLE tblroles (
  role_id INT PRIMARY KEY AUTO_INCREMENT,
  role_code VARCHAR(50) UNIQUE,
  role_name VARCHAR(100),
  description TEXT,
  permissions JSON,
  created_at TIMESTAMP,
  updated_at TIMESTAMP
);
```

#### `tblbranch_assignments`
Maps AOMs to their assigned branches.

```sql
CREATE TABLE tblbranch_assignments (
  assignment_id INT PRIMARY KEY AUTO_INCREMENT,
  aom_employee_id INT NOT NULL,
  branch_id INT NOT NULL,
  assignment_date DATETIME,
  is_active BOOLEAN DEFAULT 1,
  assigned_by INT,
  created_at TIMESTAMP,
  updated_at TIMESTAMP,
  UNIQUE(aom_employee_id, branch_id),
  FOREIGN KEY (aom_employee_id) REFERENCES tblemployee(employee_id),
  FOREIGN KEY (branch_id) REFERENCES tblbranch(branch_id)
);
```

### Modified Tables

#### `tblaccounts`
Added `role_id` column to link accounts to roles.

```sql
ALTER TABLE tblaccounts
ADD COLUMN role_id INT,
ADD FOREIGN KEY (role_id) REFERENCES tblroles(role_id);
```

#### `tblemployee`
Added `role_id` column to store employee role information.

```sql
ALTER TABLE tblemployee
ADD COLUMN role_id INT,
ADD FOREIGN KEY (role_id) REFERENCES tblroles(role_id);
```

#### `tbltickets`
Added columns to track ticket creator role and AOM association.

```sql
ALTER TABLE tbltickets
ADD COLUMN created_by_role VARCHAR(50),
ADD COLUMN aom_id INT,
ADD FOREIGN KEY (aom_id) REFERENCES tblemployee(employee_id);
```

#### `tblbranch`
Enhanced branch information with manager and contact details.

```sql
ALTER TABLE tblbranch
ADD COLUMN manager_id INT,
ADD COLUMN contact_person VARCHAR(100),
ADD COLUMN contact_email VARCHAR(100),
ADD COLUMN contact_phone VARCHAR(20),
ADD COLUMN status ENUM('ACTIVE', 'INACTIVE') DEFAULT 'ACTIVE',
ADD COLUMN updated_at TIMESTAMP;
```

### New View

#### `vw_aom_branches`
Provides quick access to AOM assignments with employee counts.

```sql
SELECT 
  assignment_id, aom_employee_id, firstname, lastname, email,
  branch_id, branchCode, branchName, branchAddress,
  is_active, assignment_date, employee_count
FROM vw_aom_branches
```

## File Structure

### Controllers
```
app/Controllers/aom/
  └── AOMController.php          # Main AOM controller
```

### Models
```
app/Models/aom/
  ├── AOMModel.php              # AOM database operations
  └── AOMTicketModel.php        # AOM ticket operations
```

### Views
```
app/Views/aom/
  ├── dashboard.php             # AOM dashboard
  ├── employees.php             # Employee list
  ├── employee-detail.php       # Employee details
  ├── branches/
  │   └── detail.php           # Branch details
  ├── tickets.php               # Ticket list
  ├── create-ticket.php         # Create ticket form
  └── ticket-detail.php         # Ticket details
```

### Helpers
```
app/Helpers/
  └── RBAC.php                 # Role-Based Access Control helper
```

### Migrations
```
scripts/
  ├── migration_add_aom_role.sql      # Database schema
  └── seed_aom_test_data.sql          # Test data
```

## URL Routes

### Dashboard
- `GET /aom/dashboard` - Main AOM dashboard

### Employees
- `GET /aom/employees` - List all employees
- `GET /aom/employees/detail?id={id}` - Employee details

### Branches
- `GET /aom/branches/detail?id={id}` - Branch details and employees

### Tickets
- `GET /aom/tickets` - List tickets
- `GET /aom/tickets/create` - Create ticket form
- `POST /aom/tickets/create` - Submit new ticket
- `GET /aom/tickets/view?id={id}` - Ticket details
- `POST /aom/tickets/update-status` - Update ticket status

### API Endpoints
- `GET /aom/api/employees-by-branch?branch_id={id}` - Get employees for AJAX

## Installation & Setup

### Step 1: Run Database Migrations

```bash
# Run the main migration
mysql -u root -p howard_tms < scripts/migration_add_aom_role.sql

# Load test data (optional)
mysql -u root -p howard_tms < scripts/seed_aom_test_data.sql
```

### Step 2: Verify Database Changes

```sql
-- Check if tables were created
SHOW TABLES LIKE 'tblroles';
SHOW TABLES LIKE 'tblbranch_assignments';

-- Check if columns were added
DESCRIBE tblaccounts;
DESCRIBE tblemployee;
DESCRIBE tbltickets;
DESCRIBE tblbranch;
```

### Step 3: Set Up AOM Assignments

Use the admin panel or SQL to assign AOMs to branches:

```sql
INSERT INTO tblbranch_assignments (aom_employee_id, branch_id, assigned_by)
VALUES (123, 5, 2200426);  -- Assign employee 123 to branch 5
```

### Step 4: Update User Account Type (Optional)

```sql
UPDATE tblaccounts 
SET usertype = 'AOM'
WHERE account_id IN (
  SELECT account_id FROM tblemployee WHERE employee_id = 123
);
```

## Usage

### For AOMs

1. **Login** as an AOM account
2. **Dashboard** shows overview of assigned branches and employees
3. **View Employees** to see all staff in assigned branches
4. **Create Ticket** to file a new ticket for a branch
5. **Manage Tickets** to track and update ticket status

### For Admins

1. **Assign AOMs** to branches via database or future admin UI
2. **Monitor AOM activity** through ticket creation
3. **Manage AOM permissions** via the RBAC system
4. **Generate reports** on branch-specific operations

## Security Features

### 1. Access Control
- **Branch-level access**: AOMs only see employees and data from assigned branches
- **Row-level filtering**: Database queries filter by assigned branches
- **Authorization checks**: Every sensitive operation verifies access rights

### 2. Permission Matrix
```php
AOM Permissions:
- view_assigned_branches: true
- view_branch_employees: true
- create_tickets: true
- view_branch_tickets: true
- manage_branch_operations: true
- monitor_employees: true
- access_branch_records: true
```

### 3. Audit Trail
- All ticket creations logged with AOM ID
- Role stored in `created_by_role` column
- Ticket history tracks all changes
- Activity logs record AOM actions

## API Examples

### Get AOM's Assigned Branches
```php
$aomModel = new AOMModel();
$branches = $aomModel->getAssignedBranches($aom_employee_id);
```

### Get Employees for a Branch
```php
$employees = $aomModel->getEmployeesByBranch($aom_employee_id, $branch_id);
```

### Create a Ticket
```php
$aomTicketModel = new AOMTicketModel();
$ticketData = [
    'branch_id' => 5,
    'employee_id' => 123,
    'category' => 'Network',
    'concern_details' => 'Internet connection down',
    'priority' => 'High',
    'aom_id' => $aom_employee_id,
    'created_by' => $_SESSION['account_id']
];
$ticketId = $aomTicketModel->createTicket($ticketData);
```

### Get Dashboard Statistics
```php
$stats = $aomModel->getDashboardStats($aom_employee_id);
// Returns: total_branches, total_employees, pending_tickets, resolved_this_month
```

## RBAC Helper Usage

### Check Role
```php
require_once 'app/Helpers/RBAC.php';

if (RBAC::hasRole($userRole, 'AOM')) {
    // User is an AOM
}
```

### Check Permission
```php
if (RBAC::hasPermission($userRole, 'create_tickets')) {
    // User can create tickets
}
```

### Enforce Access
```php
RBAC::enforceRole($userRole, 'AOM', 'This area requires AOM access.');
RBAC::enforcePermission($userRole, 'create_tickets', 'You lack permission.');
```

## Testing Checklist

- [ ] Migrations execute without errors
- [ ] AOM roles created in database
- [ ] AOM assignments functional
- [ ] Dashboard displays correct statistics
- [ ] Employee filtering works by branch
- [ ] Ticket creation shows only assigned branches
- [ ] Employees from other branches not visible
- [ ] Ticket history logs properly
- [ ] RBAC enforces access controls
- [ ] Authorization checks work

## Troubleshooting

### Issue: AOM sees all employees
**Solution**: Verify branch assignments are set correctly:
```sql
SELECT * FROM tblbranch_assignments WHERE is_active = 1;
```

### Issue: Ticket dropdown empty
**Solution**: Ensure AOM has assigned branches:
```sql
SELECT * FROM vw_aom_branches WHERE aom_employee_id = YOUR_AOM_ID;
```

### Issue: Access denied errors
**Solution**: Check RBAC helper and usertype in tblaccounts:
```sql
SELECT a.usertype, e.employee_id FROM tblaccounts a
JOIN tblemployee e ON a.account_id = e.account_id
WHERE a.account_id = YOUR_ACCOUNT_ID;
```

## Future Enhancements

1. **Admin UI** for managing AOM assignments
2. **Reports** on branch performance metrics
3. **Escalation workflow** for high-priority tickets
4. **Branch-specific templates** for tickets
5. **Employee performance tracking** by AOM
6. **Bulk operations** for multiple branches
7. **Mobile app** for AOM management
8. **Real-time notifications** for tickets

## Support & Documentation

- **RBAC System**: See `app/Helpers/RBAC.php` for full API
- **Models**: Check `app/Models/aom/` for database operations
- **Controller**: Review `app/Controllers/aom/AOMController.php` for business logic
- **Views**: Examine `app/Views/aom/` for UI patterns

## Changelog

### Version 1.0 (2026-05-12)
- Initial AOM role implementation
- Branch assignment system
- Ticket creation workflow
- Employee management interface
- Dashboard with analytics
- RBAC helper utility
- Complete documentation
