# AOM Quick Start Guide

## Quick Setup (5 minutes)

### 1. Execute Database Migration
```bash
mysql -u root -p howard_tms < scripts/migration_add_aom_role.sql
```

### 2. Load Test Data (Optional)
```bash
mysql -u root -p howard_tms < scripts/seed_aom_test_data.sql
```

### 3. Update User Type
```sql
-- Make user an AOM
UPDATE tblaccounts SET usertype = 'AOM' WHERE account_id = 2200540;

-- Or assign to branch
INSERT INTO tblbranch_assignments (aom_employee_id, branch_id, assigned_by)
VALUES (230005133, 15, 2200426);
```

### 4. Clear Cache & Restart
- Clear browser cache
- Restart PHP server if needed

## Login & Access

**URL**: `http://localhost/aom/dashboard`

**Username**: Use existing employee account

**Required**: User must be set as AOM role and have assigned branches

## Features at a Glance

| Feature | URL | Description |
|---------|-----|-------------|
| Dashboard | `/aom/dashboard` | Overview, stats, recent activity |
| Employees | `/aom/employees` | List all employees in branches |
| Branches | `/aom/branches/detail?id=X` | Branch-specific data |
| Tickets | `/aom/tickets` | All tickets for assigned branches |
| Create Ticket | `/aom/tickets/create` | File new ticket with branch dropdown |
| Ticket Detail | `/aom/tickets/view?id=X` | Full ticket information |

## Common Tasks

### Create a Ticket
1. Go to **Tickets** → **Create New Ticket**
2. Select **Branch** (only your assigned branches shown)
3. Fill in **Description**, **Priority**, **Category**
4. Optional: Assign to specific **Employee**
5. Click **Create Ticket**

### View Employee
1. Go to **Employees**
2. Click **View** on any employee
3. See full profile with contact info

### Track Tickets
1. Go to **Tickets**
2. Filter by **Status** (Pending, In Progress, Resolved)
3. Click ticket to see **Full Details** and **History**

### Access by Branch
1. Dashboard shows **Assigned Branches**
2. Click branch name for **Branch Detail**
3. See employees and recent tickets for that branch

## Database Quick Reference

### Check AOM Assignments
```sql
SELECT e.firstname, e.lastname, b.branchName 
FROM tblbranch_assignments ba
JOIN tblemployee e ON ba.aom_employee_id = e.employee_id
JOIN tblbranch b ON ba.branch_id = b.branch_id
WHERE ba.is_active = 1;
```

### Add AOM Assignment
```sql
INSERT INTO tblbranch_assignments (aom_employee_id, branch_id, assigned_by)
VALUES (EMPLOYEE_ID, BRANCH_ID, ADMIN_ID);
```

### Remove AOM Assignment
```sql
UPDATE tblbranch_assignments 
SET is_active = 0 
WHERE aom_employee_id = EMPLOYEE_ID AND branch_id = BRANCH_ID;
```

### Get AOM's Branches
```sql
SELECT * FROM vw_aom_branches WHERE aom_employee_id = EMPLOYEE_ID;
```

### View Tickets Created by AOMs
```sql
SELECT t.ticket_number, e.firstname, e.lastname, b.branchName, t.status
FROM tbltickets t
JOIN tblemployee e ON t.aom_id = e.employee_id
JOIN tblbranch b ON t.branch_id = b.branch_id
WHERE t.created_by_role = 'AOM'
ORDER BY t.date_filed DESC;
```

## Troubleshooting

| Problem | Solution |
|---------|----------|
| 404 on `/aom/dashboard` | Check routing added to `public/index.php` |
| User can't access AOM | Set `usertype = 'AOM'` in tblaccounts |
| No branches showing | Verify `tblbranch_assignments` has entries |
| Dashboard blank | Check employee_id is set in tblemployee |
| Ticket creation fails | Verify branch_id matches assigned branches |

## Key Files

- **Controller**: `app/Controllers/aom/AOMController.php`
- **Models**: `app/Models/aom/AOMModel.php`, `AOMTicketModel.php`
- **RBAC**: `app/Helpers/RBAC.php`
- **Views**: `app/Views/aom/`
- **Database**: `scripts/migration_add_aom_role.sql`
- **Documentation**: `AOM_IMPLEMENTATION_GUIDE.md`

## Sample Test Data

After running seed script, use these logins:

**AOM 1**: Julie An Tangunan
- ID: 230005133
- Branches: Yakal (17), Fairview (15)

**AOM 2**: John Karl Jose
- ID: 230005338
- Branches: Delta (11), Katipunan (14)

**AOM 3**: Jermalyn Revuelta
- ID: 230006059
- Branches: Eran (13), Sucat (6)

## Next Steps

1. ✅ Run migrations
2. ✅ Load test data
3. ✅ Configure AOM users
4. ✅ Test dashboard access
5. ✅ Create sample tickets
6. ✅ Review ticket history
7. → Deploy to production

## Support

For detailed information, see `AOM_IMPLEMENTATION_GUIDE.md`
