# OM (Operation Manager) Quick Start Guide

## What is an OM?

An **Operation Manager (OM)** handles the assignment of employees to Area Operation Managers (AOMs). OMs manage the relationship between employees and their assigned AOMs.

## Quick Navigation

| Page | URL | Purpose |
|------|-----|---------|
| Dashboard | `/om/dashboard` | View statistics and quick actions |
| Employees | `/om/employees` | Manage all employees and their assignments |
| Assignments | `/om/assignments` | View and manage all assignments |
| New Assignment | `/om/new-assignment` | Create new employee-AOM assignment |

## Getting Started

### 1. Login as OM
- Navigate to `/login`
- Enter OM credentials
- Click "Login"

### 2. View Dashboard
- You'll see the OM Dashboard with:
  - **Total Assignments**: Count of all assignments
  - **Active Assignments**: Count of currently active assignments
  - **Assigned Employees**: Count of employees assigned to AOMs

### 3. Create Your First Assignment

**Step 1:** Click "Create New Assignment" button or go to `/om/new-assignment`

**Step 2:** Select an Employee
- Choose from list of unassigned employees
- Only employees without an AOM assignment appear

**Step 3:** Select an AOM
- Choose the AOM to assign the employee to
- AOM shows number of branches they manage

**Step 4:** Add Notes (Optional)
- Add any special instructions
- Context about the assignment

**Step 5:** Submit
- Click "Create Assignment"
- You'll be redirected to your assignments list

## Managing Assignments

### View All Assignments
1. Click "My Assignments" button or go to `/om/assignments`
2. See all your employee-AOM assignments
3. Use search to find specific assignments

### Edit an Assignment
1. Click "Edit" button on any assignment
2. Change the assigned AOM if needed
3. Update notes
4. Click "Update Assignment"

### Deactivate an Assignment
1. Click "Edit" on the assignment
2. Click "Deactivate Assignment" button at the bottom
3. Confirm in the modal dialog
4. Assignment becomes inactive

## Managing Employees

### View Employees
1. Click "Manage Employees" button or go to `/om/employees`
2. See all employees with their:
   - Name, email, position
   - Department and branch
   - Currently assigned AOM
   - Assignment status

### Quick Assignment from Employee List
1. On Employees page, find the employee
2. Click "Assign" button in the Actions column
3. Follow the assignment form steps

### Search Employees
- Use the search box on Employees page
- Search by name, email, position, or department
- Results filter in real-time

## Dashboard Overview

### Statistics Cards
- **Total Assignments**: All-time count
- **Active Assignments**: Currently active
- **Assigned Employees**: Under management

### Quick Action Buttons
- **Create New Assignment**: Go to assignment form
- **View All Assignments**: Go to assignments list
- **Manage Employees**: Go to employee management

### Recent Assignments Table
- Shows 5 most recent assignments
- Click "Edit" to modify
- Status indicator (Active/Inactive)

## Common Tasks

### Task: Assign an Employee to an AOM
1. Go to `/om/new-assignment`
2. Select employee from dropdown
3. Select AOM from dropdown
4. Add notes if needed
5. Click "Create Assignment"

**Time to complete:** ~1 minute

### Task: Change an Employee's AOM
1. Go to `/om/assignments`
2. Find the assignment
3. Click "Edit"
4. Select new AOM
5. Click "Update Assignment"

**Time to complete:** ~1 minute

### Task: View All Employees Under Management
1. Go to `/om/employees`
2. See list of all employees
3. Filter by search if needed
4. View their assigned AOM in the "Assigned AOM" column

**Time to complete:** ~30 seconds

### Task: Deactivate an Assignment
1. Go to `/om/assignments`
2. Find the assignment
3. Click "Edit"
4. Click "Deactivate Assignment"
5. Confirm in modal

**Time to complete:** ~1 minute

## Tips & Best Practices

### ✅ Do
- Add notes to assignments for context
- Regularly review employee assignments
- Keep assignments updated when organizational changes occur
- Use search to quickly find employees

### ❌ Don't
- Assign the same employee to multiple AOMs (system prevents this)
- Delete assignment history (use deactivate instead)
- Assign employees without verifying their details

## Frequently Asked Questions

**Q: Can I assign an employee to multiple AOMs?**
A: No, each employee can only be assigned to one AOM. The system prevents duplicate assignments.

**Q: What happens when I deactivate an assignment?**
A: The assignment becomes inactive but remains in the history. You can reactivate by creating a new assignment.

**Q: Can I see which employees are unassigned?**
A: Yes, on the "New Assignment" page, only unassigned employees appear in the dropdown.

**Q: Can I edit notes on an assignment?**
A: Yes, click "Edit" on the assignment and update the notes.

**Q: How do I know how many employees each AOM manages?**
A: Each AOM shows "(X branches)" when you select them - this indicates their branch coverage.

## Keyboard Shortcuts

| Key | Action |
|-----|--------|
| `Ctrl+F` | Open browser search to find on page |
| `Tab` | Navigate between form fields |
| `Enter` | Submit form |
| `Esc` | Close modal dialogs |

## Help & Support

### Getting Help
- Click your profile icon (top right)
- See "Help" or "Support" option
- Contact system administrator

### Reporting Issues
- Document the problem
- Note the URL and what you were trying to do
- Contact IT support with details

## User Interface Elements

### Buttons
- **Blue buttons** (Primary): Main actions like "Create", "Submit"
- **Yellow buttons**: Modify actions like "Edit", "Update"
- **Red buttons**: Destructive actions like "Deactivate", "Delete"
- **Gray buttons**: Secondary actions like "Cancel"

### Status Badges
- **Green**: Active/Success status
- **Gray**: Inactive/Disabled status
- **Red**: Error/Alert status
- **Blue**: Information

### Messages
- **Green alerts**: Success messages
- **Red alerts**: Error messages
- **Yellow alerts**: Warning messages
- **Blue alerts**: Information

## Performance Tips

1. **Use Search**: If you have many employees, search rather than scrolling
2. **Batch Operations**: Create multiple assignments in one session
3. **Regular Updates**: Keep assignments current to avoid confusion
4. **Archive Old Data**: Regularly review and deactivate old assignments

## What's Next?

- Learn about AOM roles and their responsibilities
- Understand branch structure
- Review employee management system
- Explore reporting features

---

**Last Updated:** May 13, 2026
**Version:** 1.0
**Ready to use:** Yes ✅
