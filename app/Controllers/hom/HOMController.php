<?php
// app/Controllers/hom/HOMController.php

require_once __DIR__ . '/../AuthController.php';
require_once __DIR__ . '/../../Models/employee/Employee.php';
require_once __DIR__ . '/../../Models/hom/HOMModel.php';
require_once __DIR__ . '/../../Models/aom/AOMModel.php';
require_once __DIR__ . '/../../Models/employee/Ticket.php';
require_once __DIR__ . '/../../Helpers/Session.php';
require_once __DIR__ . '/../../Helpers/ActivityLogger.php';

/**
 * HOMController - Head Of Operation Controller
 * Manages employee assignments to AOMs
 */
class HOMController extends AuthController
{
    protected $homModel;
    protected $employeeModel;

    public function __construct()
    {
        parent::__construct();
        $this->homModel = new HOMModel();
        $this->employeeModel = new Employee();
    }

    /**
     * Check if user is HOM
     */
    protected function requireHOM()
    {
        if (empty($_SESSION['account_id'])) {
            $_SESSION['loginMessage'] = 'Please log in to continue.';
            $this->redirect('/login');
            return false;
        }

        $user = $this->employeeModel->fetchUserDetails($_SESSION['account_id']);
        $role = strtoupper($user['usertype'] ?? '');
        if (!$user || !in_array($role, ['HOM', 'OM'], true)) {
            http_response_code(403);
            exit('Unauthorized: This area requires HOM access.');
        }

        return $user;
    }

    /**
     * HOM Dashboard - View assignments overview
     */
    public function dashboard()
    {
        $user = $this->requireHOM();
        if (!$user) return;

        $accountId = (int) $_SESSION['account_id'];
        $homId = $user['employee_id'];
        
        // Get statistics
        $stats = $this->homModel->getAssignmentStats();
        
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
        $assignments = $this->homModel->getHOMAssignments($homId);
        
        // Get recent tickets (last 5)
        $recentTickets = array_slice($tickets, 0, 5);

        $data = [
            'page_title' => 'HOM Dashboard',
            'user' => $user,
            'stats' => $stats,
            'assignments' => $assignments,
            'ticketStats' => $ticketStats,
            'recentTickets' => $recentTickets,
            'user_role' => 'HOM'
        ];

        extract($data);
        require __DIR__ . '/../../Views/om/dashboard.php';
    }

    /**
     * View Operations employees and manage branch transfers
     */
    public function employees()
    {
        $user = $this->requireHOM();
        if (!$user) return;

        $role = strtoupper($user['usertype'] ?? '');
        $employees = $this->homModel->getOperationsEmployees();
        $branches = $this->homModel->getAllBranches();

        $data = [
            'page_title' => 'Operations Employees',
            'user' => $user,
            'employees' => $employees,
            'branches' => $branches,
            'user_role' => $role === 'OM' ? 'OM' : 'HOM',
            'routePrefix' => $role === 'OM' ? 'om' : 'hom',
        ];

        extract($data);
        require __DIR__ . '/../../Views/om/employees.php';
    }

    /**
     * Transfer an employee from their current branch to another branch
     */
    public function transferEmployee()
    {
        $user = $this->requireHOM();
        if (!$user) return;

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit('Method not allowed.');
        }

        $role = strtoupper($user['usertype'] ?? '');
        $routePrefix = $role === 'OM' ? 'om' : 'hom';
        $employeeId = (int) ($_POST['employee_id'] ?? 0);
        $newBranchId = (int) ($_POST['branch_id'] ?? 0);

        $result = $this->homModel->transferEmployeeBranch($employeeId, $newBranchId);

        if ($result['success']) {
            $performedBy = trim(($user['firstname'] ?? '') . ' ' . ($user['lastname'] ?? ''));
            ActivityLogger::update(
                'HOM - Employees',
                (string) $employeeId,
                sprintf(
                    'Transferred %s from %s to %s',
                    $result['employee_name'] ?? 'employee',
                    $result['old_branch_name'] ?? 'previous branch',
                    $result['new_branch_name'] ?? 'new branch'
                ),
                $performedBy,
                [
                    'old_branch_id' => $result['old_branch_id'] ?? null,
                    'new_branch_id' => $result['new_branch_id'] ?? null,
                ]
            );
            $_SESSION['success_message'] = $result['message'];
        } else {
            $_SESSION['error_message'] = $result['message'];
        }

        $this->redirect("/$routePrefix/employees");
    }

    /**
     * View all assignments managed by this HOM
     */
    public function assignments()
    {
        $user = $this->requireHOM();
        if (!$user) return;

        $homId = $user['employee_id'];
        $assignments = $this->homModel->getHOMAssignments($homId);

        $data = [
            'page_title' => 'My Assignments',
            'user' => $user,
            'assignments' => $assignments,
            'user_role' => 'HOM'
        ];

        extract($data);
        require __DIR__ . '/../../Views/om/assignments.php';
    }

    /**
     * Create new employee assignment
     */
    public function createAssignment()
    {
        $user = $this->requireHOM();
        if (!$user) return;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $employeeId = $_POST['employee_id'] ?? null;
            $branchId = $_POST['branch_id'] ?? null;
            $aomId = $_POST['aom_id'] ?? null;
            $notes = $_POST['notes'] ?? null;

            if (!$employeeId || !$aomId || !$branchId) {
                $_SESSION['error_message'] = 'Employee, Branch, and AOM are required.';
                $this->redirect('/hom/new-assignment');
                return;
            }

            // Append branch info to notes for tracking
            $fullNotes = "Branch ID: $branchId | $notes";

            // Create the assignment
            $assignmentId = $this->homModel->createAssignment(
                $user['employee_id'],
                $employeeId,
                $aomId,
                $fullNotes,
                $user['employee_id']
            );

            if ($assignmentId) {
                $_SESSION['success_message'] = 'Employee assignment created successfully.';
                $this->redirect('/hom/assignments');
            } else {
                $_SESSION['error_message'] = 'Failed to create assignment or employee already assigned to this AOM.';
                $this->redirect('/hom/new-assignment');
            }
            return;
        }

        // GET - Show form
        $unassignedEmployees = $this->homModel->getUnassignedEmployees();
        $activeAOMs = $this->homModel->getAllActiveAOMs();
        $branches = $this->homModel->getAllBranches();

        $data = [
            'page_title' => 'Create Employee Assignment',
            'user' => $user,
            'unassigned_employees' => $unassignedEmployees,
            'active_aoms' => $activeAOMs,
            'branches' => $branches,
            'user_role' => 'HOM'
        ];

        extract($data);
        require __DIR__ . '/../../Views/om/create-assignment.php';
    }

    /**
     * Update an assignment
     */
    public function updateAssignment()
    {
        $user = $this->requireHOM();
        if (!$user) return;

        $assignmentId = $_GET['id'] ?? $_POST['assignment_id'] ?? null;

        if (!$assignmentId) {
            http_response_code(404);
            exit('Assignment not found.');
        }

        $assignment = $this->homModel->getAssignmentById($assignmentId);
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
                $this->redirect("/hom/edit-assignment?id=$assignmentId");
                return;
            }

            // Append branch info to notes for tracking
            $fullNotes = "Branch ID: $branchId | $notes";

            $success = $this->homModel->updateAssignment($assignmentId, $aomId, $fullNotes);

            if ($success) {
                $_SESSION['success_message'] = 'Assignment updated successfully.';
                $this->redirect('/hom/assignments');
            } else {
                $_SESSION['error_message'] = 'Failed to update assignment.';
                $this->redirect("/hom/edit-assignment?id=$assignmentId");
            }
            return;
        }

        // GET - Show edit form
        $activeAOMs = $this->homModel->getAllActiveAOMs();
        $branches = $this->homModel->getAllBranches();

        $data = [
            'page_title' => 'Edit Assignment',
            'user' => $user,
            'assignment' => $assignment,
            'active_aoms' => $activeAOMs,
            'branches' => $branches,
            'user_role' => 'HOM'
        ];

        extract($data);
        require __DIR__ . '/../../Views/om/edit-assignment.php';
    }

    /**
     * Deactivate an assignment
     */
    public function deactivateAssignment()
    {
        $user = $this->requireHOM();
        if (!$user) return;

        $assignmentId = $_POST['assignment_id'] ?? $_GET['id'] ?? null;

        if (!$assignmentId) {
            http_response_code(400);
            exit('Assignment ID is required.');
        }

        $success = $this->homModel->deactivateAssignment($assignmentId);

        if ($success) {
            $_SESSION['success_message'] = 'Assignment deactivated successfully.';
        } else {
            $_SESSION['error_message'] = 'Failed to deactivate assignment.';
        }

        $this->redirect('/hom/assignments');
    }

    /**
     * Get unassigned employees via AJAX
     */
    public function getUnassignedEmployees()
    {
        $user = $this->requireHOM();
        if (!$user) {
            http_response_code(403);
            exit('Unauthorized');
        }

        header('Content-Type: application/json');
        
        $employees = $this->homModel->getUnassignedEmployees();
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
        $user = $this->requireHOM();
        if (!$user) {
            http_response_code(403);
            exit('Unauthorized');
        }

        header('Content-Type: application/json');
        
        $aoms = $this->homModel->getAllActiveAOMs();
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
        $user = $this->requireHOM();
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
        
        $assignments = $this->homModel->getEmployeeAssignments($employeeId);
        echo json_encode([
            'success' => true,
            'data' => $assignments
        ]);
    }

    /**
     * List AOMs and their assigned branches
     */
    public function aomBranches()
    {
        $user = $this->requireHOM();
        if (!$user) return;

        $aomModel = new AOMModel();
        $activeAOMs = $this->homModel->getAllActiveAOMs();
        $aoms = [];

        foreach ($activeAOMs as $aom) {
            $branches = $aomModel->getAssignedBranches($aom['employee_id']);
            $aoms[] = [
                'employee_id' => $aom['employee_id'],
                'firstname' => $aom['firstname'],
                'lastname' => $aom['lastname'],
                'email' => $aom['email'],
                'branches' => $branches,
                'branch_count' => count($branches),
            ];
        }

        $data = [
            'page_title' => 'AOM Branch Assignments',
            'user' => $user,
            'aoms' => $aoms,
            'user_role' => 'HOM',
            'routePrefix' => (strpos($_SERVER['REQUEST_URI'] ?? '', '/om/') !== false) ? 'om' : 'hom',
        ];

        extract($data);
        require __DIR__ . '/../../Views/om/aom-branches.php';
    }

    /**
     * Edit branch assignments for an AOM
     */
    public function editAOMBranches()
    {
        $user = $this->requireHOM();
        if (!$user) return;

        $aomEmployeeId = (int)($_GET['id'] ?? $_POST['aom_employee_id'] ?? 0);
        if ($aomEmployeeId <= 0) {
            http_response_code(404);
            exit('AOM not found.');
        }

        $activeAOMs = $this->homModel->getAllActiveAOMs();
        $aom = null;
        foreach ($activeAOMs as $candidate) {
            if ((int)$candidate['employee_id'] === $aomEmployeeId) {
                $aom = $candidate;
                break;
            }
        }

        if (!$aom) {
            http_response_code(404);
            exit('AOM not found.');
        }

        $aomModel = new AOMModel();
        $branches = $this->homModel->getAllBranches();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $branchIds = isset($_POST['aom_branch_ids']) ? (array)$_POST['aom_branch_ids'] : [];
            $ok = $aomModel->updateAOMBranchAssignments(
                $aomEmployeeId,
                $branchIds,
                $user['employee_id']
            );

            if ($ok) {
                $_SESSION['success_message'] = 'Branch assignments updated successfully.';
                $routePrefix = (strpos($_SERVER['REQUEST_URI'] ?? '', '/om/') !== false) ? 'om' : 'hom';
                $this->redirect("/$routePrefix/aom-branches");
            }

            $_SESSION['error_message'] = 'Failed to update branch assignments.';
            $routePrefix = (strpos($_SERVER['REQUEST_URI'] ?? '', '/om/') !== false) ? 'om' : 'hom';
            $this->redirect("/$routePrefix/edit-aom-branches?id=$aomEmployeeId");
            return;
        }

        $assignedBranches = $aomModel->getAssignedBranches($aomEmployeeId);

        $data = [
            'page_title' => 'Edit AOM Branch Assignments',
            'user' => $user,
            'aom' => $aom,
            'branches' => $branches,
            'assigned_branches' => $assignedBranches,
            'user_role' => 'HOM',
            'routePrefix' => (strpos($_SERVER['REQUEST_URI'] ?? '', '/om/') !== false) ? 'om' : 'hom',
        ];

        extract($data);
        require __DIR__ . '/../../Views/om/edit-aom-branches.php';
    }
}
