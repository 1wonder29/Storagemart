<?php
// app/Controllers/om/OMController.php

require_once __DIR__ . '/../AuthController.php';
require_once __DIR__ . '/../../Models/employee/Employee.php';
require_once __DIR__ . '/../../Models/om/OMModel.php';
require_once __DIR__ . '/../../Models/employee/Ticket.php';
require_once __DIR__ . '/../../Helpers/Session.php';

/**
 * OMController - Operation Manager Controller
 * Manages employee assignments to AOMs
 */
class OMController extends AuthController
{
    protected $omModel;
    protected $employeeModel;

    public function __construct()
    {
        parent::__construct();
        $this->omModel = new OMModel();
        $this->employeeModel = new Employee();
    }

    /**
     * Check if user is OM
     */
    protected function requireOM()
    {
        if (empty($_SESSION['account_id'])) {
            $_SESSION['loginMessage'] = 'Please log in to continue.';
            $this->redirect('/login');
            return false;
        }

        $user = $this->employeeModel->fetchUserDetails($_SESSION['account_id']);
        if (!$user || strtoupper($user['usertype'] ?? '') !== 'OM') {
            http_response_code(403);
            exit('Unauthorized: This area requires OM access.');
        }

        return $user;
    }

    /**
     * OM Dashboard - View assignments overview
     */
    public function dashboard()
    {
        $user = $this->requireOM();
        if (!$user) return;

        $accountId = (int) $_SESSION['account_id'];
        $omId = $user['employee_id'];
        
        // Get statistics
        $stats = $this->omModel->getAssignmentStats();
        
        // Get ticket data
        $ticketModel = new EmployeeTicket();
        $tickets = $ticketModel->getTicketsByCreatedBy($accountId);
        
        // Count tickets by status
        $ticketStats = ['total' => 0, 'open' => 0, 'in_progress' => 0, 'completed' => 0];
        $ticketStats['total'] = count($tickets);
        foreach ($tickets as $t) {
            $status = strtolower($t['status'] ?? 'open');
            if ($status === 'completed') {
                $ticketStats['completed']++;
            } elseif ($status === 'in progress') {
                $ticketStats['in_progress']++;
            } else {
                $ticketStats['open']++;
            }
        }
        
        // Get recent assignments
        $assignments = $this->omModel->getOMAssignments($omId);
        
        // Get recent tickets (last 5)
        $recentTickets = array_slice($tickets, 0, 5);

        $data = [
            'page_title' => 'OM Dashboard',
            'user' => $user,
            'stats' => $stats,
            'assignments' => $assignments,
            'ticketStats' => $ticketStats,
            'recentTickets' => $recentTickets,
            'user_role' => 'OM'
        ];

        extract($data);
        require __DIR__ . '/../../Views/om/dashboard.php';
    }

    /**
     * View all employees for assignment
     */
    public function employees()
    {
        $user = $this->requireOM();
        if (!$user) return;

        $omId = $user['employee_id'];
        $employees = $this->omModel->getAllEmployeesWithAOMAssignments($omId);

        $data = [
            'page_title' => 'Manage Employee Assignments',
            'user' => $user,
            'employees' => $employees,
            'user_role' => 'OM'
        ];

        extract($data);
        require __DIR__ . '/../../Views/om/employees.php';
    }

    /**
     * View all assignments managed by this OM
     */
    public function assignments()
    {
        $user = $this->requireOM();
        if (!$user) return;

        $omId = $user['employee_id'];
        $assignments = $this->omModel->getOMAssignments($omId);

        $data = [
            'page_title' => 'My Assignments',
            'user' => $user,
            'assignments' => $assignments,
            'user_role' => 'OM'
        ];

        extract($data);
        require __DIR__ . '/../../Views/om/assignments.php';
    }

    /**
     * Create new employee assignment
     */
    public function createAssignment()
    {
        $user = $this->requireOM();
        if (!$user) return;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $employeeId = $_POST['employee_id'] ?? null;
            $branchId = $_POST['branch_id'] ?? null;
            $aomId = $_POST['aom_id'] ?? null;
            $notes = $_POST['notes'] ?? null;

            if (!$employeeId || !$aomId || !$branchId) {
                $_SESSION['error_message'] = 'Employee, Branch, and AOM are required.';
                $this->redirect('/om/new-assignment');
                return;
            }

            // Append branch info to notes for tracking
            $fullNotes = "Branch ID: $branchId | $notes";

            // Create the assignment
            $assignmentId = $this->omModel->createAssignment(
                $user['employee_id'],
                $employeeId,
                $aomId,
                $fullNotes,
                $user['employee_id']
            );

            if ($assignmentId) {
                $_SESSION['success_message'] = 'Employee assignment created successfully.';
                $this->redirect('/om/assignments');
            } else {
                $_SESSION['error_message'] = 'Failed to create assignment or employee already assigned to this AOM.';
                $this->redirect('/om/new-assignment');
            }
            return;
        }

        // GET - Show form
        $unassignedEmployees = $this->omModel->getUnassignedEmployees();
        $activeAOMs = $this->omModel->getAllActiveAOMs();
        $branches = $this->omModel->getAllBranches();

        $data = [
            'page_title' => 'Create Employee Assignment',
            'user' => $user,
            'unassigned_employees' => $unassignedEmployees,
            'active_aoms' => $activeAOMs,
            'branches' => $branches,
            'user_role' => 'OM'
        ];

        extract($data);
        require __DIR__ . '/../../Views/om/create-assignment.php';
    }

    /**
     * Update an assignment
     */
    public function updateAssignment()
    {
        $user = $this->requireOM();
        if (!$user) return;

        $assignmentId = $_GET['id'] ?? $_POST['assignment_id'] ?? null;

        if (!$assignmentId) {
            http_response_code(404);
            exit('Assignment not found.');
        }

        $assignment = $this->omModel->getAssignmentById($assignmentId);
        if (!$assignment) {
            http_response_code(404);
            exit('Assignment not found.');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $aomId = $_POST['aom_id'] ?? null;
            $branchId = $_POST['branch_id'] ?? null;
            $notes = $_POST['notes'] ?? null;

            if (!$aomId || !$branchId) {
                $_SESSION['error_message'] = 'AOM and Branch are required.';
                $this->redirect("/om/edit-assignment?id=$assignmentId");
                return;
            }

            // Append branch info to notes for tracking
            $fullNotes = "Branch ID: $branchId | $notes";

            $success = $this->omModel->updateAssignment($assignmentId, $aomId, $fullNotes);

            if ($success) {
                $_SESSION['success_message'] = 'Assignment updated successfully.';
                $this->redirect('/om/assignments');
            } else {
                $_SESSION['error_message'] = 'Failed to update assignment.';
                $this->redirect("/om/edit-assignment?id=$assignmentId");
            }
            return;
        }

        // GET - Show edit form
        $activeAOMs = $this->omModel->getAllActiveAOMs();
        $branches = $this->omModel->getAllBranches();

        $data = [
            'page_title' => 'Edit Assignment',
            'user' => $user,
            'assignment' => $assignment,
            'active_aoms' => $activeAOMs,
            'branches' => $branches,
            'user_role' => 'OM'
        ];

        extract($data);
        require __DIR__ . '/../../Views/om/edit-assignment.php';
    }

    /**
     * Deactivate an assignment
     */
    public function deactivateAssignment()
    {
        $user = $this->requireOM();
        if (!$user) return;

        $assignmentId = $_POST['assignment_id'] ?? $_GET['id'] ?? null;

        if (!$assignmentId) {
            http_response_code(400);
            exit('Assignment ID is required.');
        }

        $success = $this->omModel->deactivateAssignment($assignmentId);

        if ($success) {
            $_SESSION['success_message'] = 'Assignment deactivated successfully.';
        } else {
            $_SESSION['error_message'] = 'Failed to deactivate assignment.';
        }

        $this->redirect('/om/assignments');
    }

    /**
     * Get unassigned employees via AJAX
     */
    public function getUnassignedEmployees()
    {
        $user = $this->requireOM();
        if (!$user) {
            http_response_code(403);
            exit('Unauthorized');
        }

        header('Content-Type: application/json');
        
        $employees = $this->omModel->getUnassignedEmployees();
        echo json_encode([
            'success' => true,
            'data' => $employees
        ]);
    }

    /**
     * Get all AOMs via AJAX
     */
    public function getAOMs()
    {
        $user = $this->requireOM();
        if (!$user) {
            http_response_code(403);
            exit('Unauthorized');
        }

        header('Content-Type: application/json');
        
        $aoms = $this->omModel->getAllActiveAOMs();
        echo json_encode([
            'success' => true,
            'data' => $aoms
        ]);
    }

    /**
     * Get employee assignments via AJAX
     */
    public function getEmployeeAssignments()
    {
        $user = $this->requireOM();
        if (!$user) {
            http_response_code(403);
            exit('Unauthorized');
        }

        $employeeId = $_GET['employee_id'] ?? null;

        if (!$employeeId) {
            http_response_code(400);
            exit(json_encode(['error' => 'Employee ID required']));
        }

        header('Content-Type: application/json');
        
        $assignments = $this->omModel->getEmployeeAssignments($employeeId);
        echo json_encode([
            'success' => true,
            'data' => $assignments
        ]);
    }
}
