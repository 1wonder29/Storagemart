<?php
// app/Controllers/aom/AOMController.php

require_once __DIR__ . '/../AuthController.php';
require_once __DIR__ . '/../../Models/employee/Employee.php';
require_once __DIR__ . '/../../Models/aom/AOMModel.php';
require_once __DIR__ . '/../../Models/aom/AOMTicketModel.php';
require_once __DIR__ . '/../../Helpers/Session.php';
require_once __DIR__ . '/../../Models/NotificationModel.php';

/**
 * AOMController - Area Operation Manager Controller
 * Manages AOM dashboard, branch operations, employee management, and ticket creation
 */
class AOMController extends AuthController
{
    protected $aomModel;
    protected $aomTicketModel;
    protected $employeeModel;
    protected $notificationModel;

    public function __construct()
    {
        parent::__construct();
        $this->aomModel = new AOMModel();
        $this->aomTicketModel = new AOMTicketModel();
        $this->employeeModel = new Employee();
        $this->notificationModel = new NotificationModel();
    }

    /**
     * Check if user is AOM
     */
    protected function requireAOM()
    {
        if (empty($_SESSION['account_id'])) {
            $_SESSION['loginMessage'] = 'Please log in to continue.';
            $this->redirect('/login');
            return false;
        }

        $user = $this->employeeModel->fetchUserDetails($_SESSION['account_id']);
        if (!$user || strtoupper($user['usertype'] ?? '') !== 'AOM') {
            http_response_code(403);
            exit('Unauthorized: This area requires AOM access.');
        }

        return $user;
    }

    /**
     * Load user context
     */
    protected function getLoggedUserContext(): array
    {
        $user = $this->employeeModel->fetchUserDetails($_SESSION['account_id']);
        $employee = $this->employeeModel->getEmployeeById($user['employee_id']);

        return [
            'account_id' => $_SESSION['account_id'],
            'employee_id' => $user['employee_id'],
            'usertype' => $user['usertype'],
            'loggedFirstname' => $employee['firstname'] ?? '',
            'loggedLastname' => $employee['lastname'] ?? '',
            'loggedPosition' => $employee['position'] ?? '',
            'base' => BASE_URL,
            'user' => $user
        ];
    }

    /**
     * Load notifications
     */
    protected function loadNotifications(): array
    {
        $userId = $_SESSION['account_id'] ?? null;
        if (!$userId) return ['count' => 0, 'notifications' => []];

        try {
            $notifications = $this->notificationModel->getLatest($userId, 10);
            $count = count($notifications);
            return ['count' => $count, 'notifications' => $notifications];
        } catch (Exception $e) {
            error_log("Error loading notifications: " . $e->getMessage());
            return ['count' => 0, 'notifications' => []];
        }
    }

    /**
     * AOM Dashboard
     */
    public function dashboard()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        // Authentication check
        $user = $this->requireAOM();
        if (!$user) return;

        $aom_employee_id = $user['employee_id'];

        // Get context
        $ctx = $this->getLoggedUserContext();
        $base = $ctx['base'];

        // Get dashboard statistics
        $stats = $this->aomModel->getDashboardStats($aom_employee_id);
        $branches = $this->aomModel->getAssignedBranches($aom_employee_id);
        $tickets = $this->aomModel->getAOMTickets($aom_employee_id, 10);
        $ticketStats = $this->aomModel->getTicketStatsByStatus($aom_employee_id);

        // Get notifications
        $notificationData = $this->loadNotifications();
        $count = $notificationData['count'];
        $notifications = $notificationData['notifications'];

        // Page context
        $activePage = 'dashboard';

        // Render view
        require __DIR__ . '/../../Views/aom/dashboard.php';
    }

    /**
     * View all employees in assigned branches
     */
    public function employees()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        $user = $this->requireAOM();
        if (!$user) return;

        $aom_employee_id = $user['employee_id'];
        $ctx = $this->getLoggedUserContext();
        $base = $ctx['base'];

        // Get all employees in assigned branches
        $employees = $this->aomModel->getAssignedEmployees($aom_employee_id);
        $branches = $this->aomModel->getAssignedBranches($aom_employee_id);

        // Get notifications
        $notificationData = $this->loadNotifications();
        $count = $notificationData['count'];
        $notifications = $notificationData['notifications'];

        $activePage = 'employees';

        require __DIR__ . '/../../Views/aom/employees.php';
    }

    /**
     * View employee details
     */
    public function employeeDetail()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        $user = $this->requireAOM();
        if (!$user) return;

        $aom_employee_id = $user['employee_id'];
        $employee_id = (int)($_GET['id'] ?? 0);

        if ($employee_id <= 0) {
            $_SESSION['flash_error'] = 'Invalid employee ID.';
            $this->redirect('/aom/employees');
            return;
        }

        // Verify AOM has access to this employee
        if (!$this->aomModel->hasAccessToEmployee($aom_employee_id, $employee_id)) {
            http_response_code(403);
            exit('Unauthorized: You do not have access to this employee.');
        }

        $ctx = $this->getLoggedUserContext();
        $base = $ctx['base'];

        $employee = $this->employeeModel->getEmployeeById($employee_id);
        
        // Get notifications
        $notificationData = $this->loadNotifications();
        $count = $notificationData['count'];
        $notifications = $notificationData['notifications'];

        $activePage = 'employees';

        require __DIR__ . '/../../Views/aom/employee-detail.php';
    }

    /**
     * View branch details and employees
     */
    public function branchDetail()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        $user = $this->requireAOM();
        if (!$user) return;

        $aom_employee_id = $user['employee_id'];
        $branch_id = (int)($_GET['id'] ?? 0);

        if ($branch_id <= 0) {
            $_SESSION['flash_error'] = 'Invalid branch ID.';
            $this->redirect('/aom/dashboard');
            return;
        }

        // Verify access
        if (!$this->aomModel->hasAccessToBranch($aom_employee_id, $branch_id)) {
            http_response_code(403);
            exit('Unauthorized: You do not have access to this branch.');
        }

        $ctx = $this->getLoggedUserContext();
        $base = $ctx['base'];

        $employees = $this->aomModel->getEmployeesByBranch($aom_employee_id, $branch_id);
        $tickets = $this->aomTicketModel->getTicketsByBranch($branch_id, null, 20);
        $branches = $this->aomModel->getAssignedBranches($aom_employee_id);

        // Get notifications
        $notificationData = $this->loadNotifications();
        $count = $notificationData['count'];
        $notifications = $notificationData['notifications'];

        $activePage = 'branches';

        require __DIR__ . '/../../Views/aom/branch-detail.php';
    }

    /**
     * Ticket management
     */
    public function tickets()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        $user = $this->requireAOM();
        if (!$user) return;

        $aom_employee_id = $user['employee_id'];
        $ctx = $this->getLoggedUserContext();
        $base = $ctx['base'];

        // Get all tickets for AOM's branches
        $tickets = $this->aomModel->getAOMTickets($aom_employee_id, 50);
        $ticketStats = $this->aomModel->getTicketStatsByStatus($aom_employee_id);
        $branches = $this->aomModel->getAssignedBranches($aom_employee_id);

        // Get notifications
        $notificationData = $this->loadNotifications();
        $count = $notificationData['count'];
        $notifications = $notificationData['notifications'];

        $activePage = 'tickets';

        require __DIR__ . '/../../Views/aom/tickets.php';
    }

    /**
     * Create ticket page
     */
    public function createTicketForm()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        $user = $this->requireAOM();
        if (!$user) return;

        $aom_employee_id = $user['employee_id'];
        $ctx = $this->getLoggedUserContext();
        $base = $ctx['base'];

        $branches = $this->aomModel->getAssignedBranches($aom_employee_id);

        // Get notifications
        $notificationData = $this->loadNotifications();
        $count = $notificationData['count'];
        $notifications = $notificationData['notifications'];

        $activePage = 'create-ticket';

        require __DIR__ . '/../../Views/aom/create-ticket.php';
    }

    /**
     * Get employees in a branch (AJAX)
     */
    public function getEmployeesByBranchAjax()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        header('Content-Type: application/json');

        if (empty($_SESSION['account_id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }

        $user = $this->employeeModel->fetchUserDetails($_SESSION['account_id']);
        if (!$user || strtoupper($user['usertype'] ?? '') !== 'AOM') {
            http_response_code(403);
            echo json_encode(['error' => 'Forbidden']);
            return;
        }

        $aom_employee_id = $user['employee_id'];
        $branch_id = (int)($_GET['branch_id'] ?? 0);

        if ($branch_id <= 0) {
            echo json_encode(['error' => 'Invalid branch ID']);
            return;
        }

        // Verify access
        if (!$this->aomModel->hasAccessToBranch($aom_employee_id, $branch_id)) {
            http_response_code(403);
            echo json_encode(['error' => 'Unauthorized access to this branch']);
            return;
        }

        $employees = $this->aomModel->getEmployeesByBranch($aom_employee_id, $branch_id);
        echo json_encode(['data' => $employees]);
    }

    /**
     * Create new ticket (POST)
     */
    public function submitTicket()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit('Method not allowed');
        }

        $user = $this->requireAOM();
        if (!$user) return;

        $aom_employee_id = $user['employee_id'];

        try {
            // Get form data
            $branch_id = (int)($_POST['branch_id'] ?? 0);
            $employee_id = (int)($_POST['employee_id'] ?? 0);
            $department = $_POST['department'] ?? null;
            $category = $_POST['category'] ?? null;
            $concern_details = $_POST['concern_details'] ?? null;
            $priority = $_POST['priority'] ?? 'Low';

            // Validation
            if ($branch_id <= 0) {
                $_SESSION['flash_error'] = 'Please select a branch.';
                $this->redirect('/aom/tickets/create');
                return;
            }

            if (empty($concern_details)) {
                $_SESSION['flash_error'] = 'Ticket description is required.';
                $this->redirect('/aom/tickets/create');
                return;
            }

            // Verify AOM has access to branch
            if (!$this->aomModel->hasAccessToBranch($aom_employee_id, $branch_id)) {
                $_SESSION['flash_error'] = 'Unauthorized: You do not have access to this branch.';
                $this->redirect('/aom/tickets/create');
                return;
            }

            // Validate priority
            $valid_priorities = ['Low', 'Medium', 'High'];
            if (!in_array($priority, $valid_priorities)) {
                $priority = 'Low';
            }

            // Create ticket
            $ticketData = [
                'branch_id' => $branch_id,
                'employee_id' => $employee_id ?: null,
                'department' => $department,
                'category' => $category,
                'concern_details' => $concern_details,
                'priority' => $priority,
                'aom_id' => $aom_employee_id,
                'created_by' => $_SESSION['account_id'],
                // performed_by for ticket history (employee_id matches existing logic)
                'performed_by' => $aom_employee_id,
                'inventory_id' => null,
            ];

            $ticketId = $this->aomTicketModel->createTicket($ticketData);

            if ($ticketId) {
                $_SESSION['flash_success'] = 'Ticket created successfully!';
                $this->redirect('/aom/tickets');
            } else {
                $_SESSION['flash_error'] = 'Failed to create ticket. Please try again.';
                $this->redirect('/aom/tickets/create');
            }
        } catch (Exception $e) {
            error_log("Error creating ticket: " . $e->getMessage());
            $_SESSION['flash_error'] = 'An error occurred while creating the ticket.';
            $this->redirect('/aom/tickets/create');
        }
    }

    /**
     * View ticket details
     */
    public function ticketDetail()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        $user = $this->requireAOM();
        if (!$user) return;

        $aom_employee_id = $user['employee_id'];
        $ticket_id = (int)($_GET['id'] ?? 0);

        if ($ticket_id <= 0) {
            $_SESSION['flash_error'] = 'Invalid ticket ID.';
            $this->redirect('/aom/tickets');
            return;
        }

        $ctx = $this->getLoggedUserContext();
        $base = $ctx['base'];

        // Get ticket with authorization check
        $ticket = $this->aomTicketModel->getTicketByIdForAOM($ticket_id, $aom_employee_id);

        if (!$ticket) {
            $_SESSION['flash_error'] = 'Ticket not found or unauthorized access.';
            $this->redirect('/aom/tickets');
            return;
        }

        $ticketHistory = $this->aomTicketModel->getTicketHistory($ticket_id);

        // Get notifications
        $notificationData = $this->loadNotifications();
        $count = $notificationData['count'];
        $notifications = $notificationData['notifications'];

        $activePage = 'tickets';

        require __DIR__ . '/../../Views/aom/ticket-detail.php';
    }

    /**
     * Update ticket status
     */
    public function updateTicketStatus()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit('Method not allowed');
        }

        header('Content-Type: application/json');

        $user = $this->requireAOM();
        if (!$user) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }

        $aom_employee_id = $user['employee_id'];
        $ticket_id = (int)($_POST['ticket_id'] ?? 0);
        $new_status = $_POST['status'] ?? null;

        if ($ticket_id <= 0 || !$new_status) {
            echo json_encode(['success' => false, 'message' => 'Invalid data']);
            return;
        }

        // Verify authorization
        $ticket = $this->aomTicketModel->getTicketByIdForAOM($ticket_id, $aom_employee_id);
        if (!$ticket) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }

        // Update status
        $success = $this->aomTicketModel->updateTicketStatus(
            $ticket_id,
            $new_status,
            // use employee_id (tblticket_history.performed_by joins to tblemployee)
            $aom_employee_id,
            $_POST['remarks'] ?? null
        );

        echo json_encode([
            'success' => $success,
            'message' => $success ? 'Ticket status updated.' : 'Failed to update ticket status.'
        ]);
    }
}
