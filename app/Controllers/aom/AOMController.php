<?php
// app/Controllers/aom/AOMController.php

require_once __DIR__ . '/../AuthController.php';
require_once __DIR__ . '/../../Models/employee/Employee.php';
require_once __DIR__ . '/../../Models/aom/AOMModel.php';
require_once __DIR__ . '/../../Models/aom/AOMTicketModel.php';
require_once __DIR__ . '/../../Models/employee/Ticket.php';
require_once __DIR__ . '/../../Helpers/Session.php';
require_once __DIR__ . '/../../Helpers/ActivityLogger.php';
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
     * AOM Profile page
     */
    public function profile()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        $user = $this->requireAOM();
        if (!$user) return;

        $aom_employee_id = $user['employee_id'];
        $profile = $this->employeeModel->fetchProfileByAccountId((int)$_SESSION['account_id']) ?? [];

        $ctx = $this->getLoggedUserContext();
        $base = $ctx['base'];
        $loggedFirstname = $ctx['loggedFirstname'];
        $loggedLastname = $ctx['loggedLastname'];
        $loggedPosition = $ctx['loggedPosition'];

        $stats = $this->aomModel->getDashboardStats($aom_employee_id);
        $branches = $this->aomModel->getAssignedBranches($aom_employee_id);
        $assignment_history = $this->aomModel->getBranchAssignmentHistory($aom_employee_id);

        $notificationData = $this->loadNotifications();
        $count = $notificationData['count'];
        $notifications = $notificationData['notifications'];

        $activePage = 'profile';

        require __DIR__ . '/../../Views/aom/profile/profile.php';
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

        // Get Operations employees in assigned branches
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
     * View assets assigned to employees within AOM scope
     */
    public function assets()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        $user = $this->requireAOM();
        if (!$user) return;

        $aom_employee_id = (int) $user['employee_id'];
        $ctx = $this->getLoggedUserContext();
        $base = $ctx['base'];

        $myAssets = $this->employeeModel->fetchAssetDetailsByEmployeeId($aom_employee_id);
        $teamAssets = $this->aomModel->getAOMTeamAssets($aom_employee_id);
        $branches = $this->aomModel->getAssignedBranches($aom_employee_id);

        $notificationData = $this->loadNotifications();
        $count = $notificationData['count'];
        $notifications = $notificationData['notifications'];

        $activePage = 'assets';
        $teamEmptyMessage = 'No assets found for employees in your assigned branches.';

        require __DIR__ . '/../../Views/aom/asset/assets.php';
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
        $operationsEmployees = $this->employeeModel->fetchEmployeesByDepartment('Operations');

        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
        }
        $csrf_token = $_SESSION['csrf_token'];

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
     * Get employees in a branch who have tickets (AJAX) — for bulk transfer "Transfer From"
     */
    public function getEmployeesWithTicketsByBranchAjax()
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

        $aom_employee_id = (int) $user['employee_id'];
        $branch_id = (int) ($_GET['branch_id'] ?? 0);

        if ($branch_id <= 0) {
            echo json_encode(['error' => 'Invalid branch ID']);
            return;
        }

        if (!$this->aomModel->hasAccessToBranch($aom_employee_id, $branch_id)) {
            http_response_code(403);
            echo json_encode(['error' => 'Unauthorized access to this branch']);
            return;
        }

        try {
            $employees = $this->aomTicketModel->getEmployeesWithTicketsInBranch($branch_id, $aom_employee_id);
            echo json_encode(['data' => $employees]);
        } catch (\Throwable $e) {
            error_log('getEmployeesWithTicketsByBranchAjax error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Failed to load employees']);
        }
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
     * Transfer all open tickets from one employee to another within a branch (POST)
     */
    public function transferTicket()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit('Method not allowed');
        }

        $user = $this->requireAOM();
        if (!$user) return;

        $aom_employee_id = (int) $user['employee_id'];
        $branchId = (int) ($_POST['branch_id'] ?? 0);
        $sourceEmployeeId = (int) ($_POST['source_employee_id'] ?? 0);
        $newEmployeeId = (int) ($_POST['employee_id'] ?? 0);
        $remarks = trim((string) ($_POST['remarks'] ?? ''));

        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
            $_SESSION['flash_error'] = 'Invalid form token.';
            $this->redirect('/aom/tickets');
            return;
        }

        if ($branchId <= 0 || $sourceEmployeeId <= 0 || $newEmployeeId <= 0) {
            $_SESSION['flash_error'] = 'Please select a branch and both employees.';
            $this->redirect('/aom/tickets');
            return;
        }

        if ($sourceEmployeeId === $newEmployeeId) {
            $_SESSION['flash_error'] = 'Source and destination employee must be different.';
            $this->redirect('/aom/tickets');
            return;
        }

        if (!$this->aomModel->hasAccessToBranch($aom_employee_id, $branchId)) {
            $_SESSION['flash_error'] = 'You do not have access to the selected branch.';
            $this->redirect('/aom/tickets');
            return;
        }

        $branchEmployees = $this->aomTicketModel->getEmployeesWithTicketsInBranch($branchId, $aom_employee_id);
        $employeeIds = array_map(static fn(array $emp): int => (int) ($emp['employee_id'] ?? 0), $branchEmployees);

        if (!in_array($sourceEmployeeId, $employeeIds, true)) {
            $_SESSION['flash_error'] = 'Selected source employee has no tickets in the chosen branch.';
            $this->redirect('/aom/tickets');
            return;
        }

        $validTarget = false;
        foreach ($this->employeeModel->fetchEmployeesByDepartment('Operations') as $emp) {
            if ((int) ($emp['employee_id'] ?? 0) === $newEmployeeId) {
                $validTarget = true;
                break;
            }
        }

        if (!$validTarget) {
            $_SESSION['flash_error'] = 'Selected destination employee is not a valid Operations staff member.';
            $this->redirect('/aom/tickets');
            return;
        }

        $transferableTickets = $this->aomTicketModel->getTransferableTicketsForEmployee(
            $sourceEmployeeId,
            $aom_employee_id,
            $branchId
        );
        $ticketIds = array_map(static fn(array $row): int => (int) ($row['ticket_id'] ?? 0), $transferableTickets);

        if (empty($ticketIds)) {
            $_SESSION['flash_error'] = 'No tickets found for this employee in the selected branch.';
            $this->redirect('/aom/tickets');
            return;
        }

        $ticketModel = new EmployeeTicket();
        [$ok, $message, $transferredCount] = $ticketModel->transferAllTicketsToEmployee(
            $ticketIds,
            $newEmployeeId,
            $aom_employee_id,
            'AOM',
            $remarks !== '' ? $remarks : null
        );

        if ($ok) {
            $performedBy = trim(($user['firstname'] ?? '') . ' ' . ($user['lastname'] ?? ''));
            ActivityLogger::transfer(
                'AOM - Ticket Management',
                (string) $sourceEmployeeId,
                $message,
                $performedBy,
                [
                    'branch_id' => $branchId,
                    'ticket_ids' => $ticketIds,
                    'transferred_count' => $transferredCount,
                    'new_employee_id' => $newEmployeeId,
                    'remarks' => $remarks,
                ]
            );
            $_SESSION['flash_success'] = $message;
        } else {
            $_SESSION['flash_error'] = $message;
        }

        $this->redirect('/aom/tickets');
    }

    /**
     * Get transferable ticket count for bulk transfer preview (AJAX)
     */
    public function getTransferableTicketCountAjax()
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

        $aom_employee_id = (int) $user['employee_id'];
        $branchId = (int) ($_GET['branch_id'] ?? 0);
        $employeeId = (int) ($_GET['employee_id'] ?? 0);

        if ($branchId <= 0 || $employeeId <= 0) {
            echo json_encode(['count' => 0]);
            return;
        }

        if (!$this->aomModel->hasAccessToBranch($aom_employee_id, $branchId)) {
            http_response_code(403);
            echo json_encode(['error' => 'Unauthorized access to this branch']);
            return;
        }

        $allowed = false;
        foreach ($this->aomTicketModel->getEmployeesWithTicketsInBranch($branchId, $aom_employee_id) as $emp) {
            if ((int) ($emp['employee_id'] ?? 0) === $employeeId) {
                $allowed = true;
                break;
            }
        }

        if (!$allowed) {
            http_response_code(403);
            echo json_encode(['error' => 'Employee has no tickets in selected branch']);
            return;
        }

        $tickets = $this->aomTicketModel->getTransferableTicketsForEmployee(
            $employeeId,
            $aom_employee_id,
            $branchId
        );

        echo json_encode(['count' => count($tickets)]);
    }

    /**
     * Download technical record (DOCX) for a ticket — allow AOM override
     */
    public function downloadTechnicalRecord()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        $user = $this->requireAOM();
        if (!$user) return;

        $aom_employee_id = $user['employee_id'];

        $ticketId = (int)($_GET['id'] ?? 0);
        if ($ticketId <= 0) {
            http_response_code(400);
            echo 'Invalid ticket ID';
            exit;
        }

        require_once __DIR__ . '/../../Services/PdfGeneratorService.php';
        $pdfService = new PdfGeneratorService();

        // Allow AOM to generate record for tickets in their branches
        $result = $pdfService->generateTechnicalRecordDocx($ticketId, $aom_employee_id, true);

        if (!$result || !$result['success']) {
            http_response_code(404);
            echo 'Unable to generate technical record. Please ensure the ticket is resolved.';
            exit;
        }

        $filepath = $result['filepath'];
        $filename = $result['filename'];

        if (!file_exists($filepath)) {
            http_response_code(404);
            echo 'File not found';
            exit;
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        header('Content-Disposition: attachment; filename="' . basename($filename) . '"');
        header('Content-Length: ' . filesize($filepath));
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');

        readfile($filepath);
        exit;
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

    /**
     * Show rating form (GET) for AOM and handle form submission (POST)
     */
    public function rate()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        // Debug log for rating form access
        $debugLogDir = __DIR__ . '/../../logs';
        if (!is_dir($debugLogDir)) @mkdir($debugLogDir, 0755, true);
        $dbgFile = $debugLogDir . '/rating_debug.log';
        $dbgMsg = '[' . date('Y-m-d H:i:s') . '] rate() called. account_id=' . ($_SESSION['account_id'] ?? 'null') . ' REQUEST_URI=' . ($_SERVER['REQUEST_URI'] ?? '') . "\n";
        @file_put_contents($dbgFile, $dbgMsg, FILE_APPEND);

        $user = $this->requireAOM();
        if (!$user) {
            @file_put_contents($dbgFile, '[' . date('Y-m-d H:i:s') . '] requireAOM failed\n', FILE_APPEND);
            return;
        }

        $aom_employee_id = $user['employee_id'];

        $ticketId = (int)($_GET['id'] ?? 0);
        if (!$ticketId) {
            http_response_code(400);
            echo 'Invalid ticket.';
            return;
        }

        require_once __DIR__ . '/../../Models/aom/TicketRatingModel.php';
        $ratingModel = new AOMTicketRatingModel();
        $alreadyRated = $ratingModel->hasRated($ticketId, $aom_employee_id);
        $existingRating = null;

        // If already rated, fetch the existing rating row for edit and log for debugging
        if ($alreadyRated) {
            try {
                $existingRating = $ratingModel->getByTicketAndEmployee($ticketId, $aom_employee_id);
                @file_put_contents($dbgFile, '[' . date('Y-m-d H:i:s') . "] alreadyRated row: " . json_encode($existingRating) . "\n", FILE_APPEND);
            } catch (Exception $e) {
                @file_put_contents($dbgFile, '[' . date('Y-m-d H:i:s') . "] alreadyRated query failed: " . $e->getMessage() . "\n", FILE_APPEND);
            }
        }

        $base = rtrim(BASE_URL, '/');

        require __DIR__ . '/../../Views/aom/ticket/rate.php';
    }

    public function storeRating()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        $user = $this->requireAOM();
        if (!$user) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }

        require_once __DIR__ . '/../../Models/aom/TicketRatingModel.php';

        header('Content-Type: application/json');

        $accountId = (int) ($_SESSION['account_id'] ?? 0);
        $ticketId  = (int) ($_POST['ticket_id'] ?? 0);

        if (!$ticketId) {
            echo json_encode(['success' => false, 'message' => 'Invalid ticket.']);
            exit;
        }

        $employeeModel = new Employee();
        $aomId = $employeeModel->getEmployeeIdByAccountId($accountId);

        if (!$aomId) {
            echo json_encode(['success' => false, 'message' => 'AOM record not found.']);
            exit;
        }

        $ticketModel = new AOMTicketModel();
        $ticket = $ticketModel->getTicketByIdForAOM($ticketId, $aomId);

        if (!$ticket) {
            echo json_encode(['success' => false, 'message' => 'Ticket not found or unauthorized.']);
            exit;
        }

        // Get assigned IT person
        require_once __DIR__ . '/../../Models/employee/Ticket.php';
        $empTicket = new EmployeeTicket();
        $itId = $empTicket->getAssignedTo($ticketId);
        if (!$itId) {
            echo json_encode(['success' => false, 'message' => 'Ticket is not assigned yet.']);
            exit;
        }

        $ratingModel = new AOMTicketRatingModel();
        $existing = $ratingModel->getByTicketAndEmployee($ticketId, $aomId);
        if ($existing) {
            // update existing rating
            $updated = $ratingModel->updateById($existing['id'], $_POST['rating'], $_POST['comment'] ?? '');
            if ($updated) {
                echo json_encode(['success' => true, 'message' => 'Your rating has been updated.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to update rating.']);
            }
            exit;
        }

        $ratingModel->create(
            $ticketId,
            $aomId,
            $itId,
            $_POST['rating'],
            $_POST['comment'] ?? ''
        );

        echo json_encode([
            'success' => true,
            'message' => 'Thank you for your feedback!'
        ]);
        exit;
    }

    /**
     * Upload technical report for a ticket
     */
    public function uploadReport()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        header('Content-Type: application/json');

        try {
            // Authenticate AOM user
            $user = $this->requireAOM();
            if (!$user) {
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'Unauthorized']);
                exit;
            }

            // Validate request method
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Invalid request method']);
                exit;
            }

            $aomId = $user['employee_id'];

            // Validate ticket ID
            $ticketId = (int) ($_POST['ticket_id'] ?? 0);
            if (!$ticketId) {
                echo json_encode(['success' => false, 'message' => 'Invalid ticket ID']);
                exit;
            }

            // Verify ticket exists and AOM can access it
            $ticket = $this->aomTicketModel->getTicketByIdForAOM($ticketId, $aomId);

            if (!$ticket) {
                echo json_encode(['success' => false, 'message' => 'Ticket not found or unauthorized']);
                exit;
            }

            // Verify ticket is resolved
            if (strtolower($ticket['status']) !== 'resolved') {
                echo json_encode(['success' => false, 'message' => 'Only resolved tickets can have reports uploaded']);
                exit;
            }

            // Validate file upload
            if (!isset($_FILES['report_file']) || $_FILES['report_file']['error'] !== UPLOAD_ERR_OK) {
                $errorCode = $_FILES['report_file']['error'] ?? 'unknown';
                error_log("Upload error - Error code: {$errorCode}");
                echo json_encode(['success' => false, 'message' => 'No file uploaded or upload error occurred']);
                exit;
            }

            $uploadedFile = $_FILES['report_file'];
            $allowedMimes = [
                'application/pdf',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.ms-word.document.macroEnabled.12',
                'application/msword',
                'application/zip',
                'image/jpeg',
                'image/jpg',
                'image/png'
            ];
            $allowedExtensions = ['pdf', 'docx', 'doc', 'jpg', 'jpeg', 'png'];
            $maxFileSize = 10 * 1024 * 1024;

            // Get file extension
            $originalName = basename($uploadedFile['name']);
            $fileExt = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

            // Validate file extension
            if (!in_array($fileExt, $allowedExtensions)) {
                echo json_encode(['success' => false, 'message' => 'Invalid file type']);
                exit;
            }

            // Validate MIME type
            $fileMime = '';
            if (function_exists('finfo_file')) {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $fileMime = finfo_file($finfo, $uploadedFile['tmp_name']);
                finfo_close($finfo);
            } else {
                $fileMime = $uploadedFile['type'] ?? '';
            }

            // Validate file size
            if ($uploadedFile['size'] > $maxFileSize) {
                echo json_encode(['success' => false, 'message' => 'File size exceeds 10MB']);
                exit;
            }

            if ($uploadedFile['size'] === 0) {
                echo json_encode(['success' => false, 'message' => 'File is empty']);
                exit;
            }

            // Generate filename
            $sanitizedTicketNumber = preg_replace('/[^A-Za-z0-9_-]/', '', $ticket['ticket_number']);
            $timestamp = date('YmdHis');
            $randomId = substr(md5(uniqid()), 0, 8);
            $storedFilename = "report_{$sanitizedTicketNumber}_{$timestamp}_{$randomId}.{$fileExt}";

            // Setup directory
            $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/assets/generatePDF';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $uploadPath = $uploadDir . '/' . $storedFilename;

            // Move file
            if (!move_uploaded_file($uploadedFile['tmp_name'], $uploadPath)) {
                error_log("Upload move failed: {$uploadPath}");
                echo json_encode(['success' => false, 'message' => 'Failed to save file']);
                exit;
            }

            // Record in database
            require_once __DIR__ . '/../../Models/employee/UploadModel.php';
            $uploadModel = new TicketUploadModel();

            try {
                $uploadId = $uploadModel->recordUpload(
                    $ticketId,
                    $aomId,
                    $originalName,
                    $storedFilename,
                    filesize($uploadPath),
                    $fileMime ?: 'application/octet-stream'
                );
            } catch (Exception $dbErr) {
                error_log("Database error recording upload: " . $dbErr->getMessage());
                @unlink($uploadPath);
                echo json_encode(['success' => false, 'message' => 'Database error: ' . $dbErr->getMessage()]);
                exit;
            }

            if (!$uploadId) {
                @unlink($uploadPath);
                error_log("Upload recording returned no ID for ticket $ticketId");
                echo json_encode(['success' => false, 'message' => 'Failed to record upload in database']);
                exit;
            }

            echo json_encode([
                'success' => true,
                'message' => 'Report uploaded successfully',
                'upload_id' => $uploadId,
                'filename' => $originalName
            ]);
            exit;

        } catch (Exception $e) {
            error_log("Exception in uploadReport: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
            exit;
        }
    }
}
