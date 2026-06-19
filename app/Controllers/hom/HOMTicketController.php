<?php

require_once __DIR__ . '/../AuthController.php';
require_once __DIR__ . '/../../Models/employee/Employee.php';
require_once __DIR__ . '/../../Models/employee/Ticket.php';
require_once __DIR__ . '/../../Helpers/Session.php';
require_once __DIR__ . '/../../Models/admin/Logger.php';
require_once __DIR__ . '/../../Models/NotificationModel.php';
require_once __DIR__ . '/../../Models/hom/HOMModel.php';
require_once __DIR__ . '/../../Helpers/ActivityLogger.php';

class HOMTicketController extends AuthController
{
    protected $employeeModel;

    public function __construct()
    {
        parent::__construct();
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

    protected function routePrefixForUser(array $user): string
    {
        return strtoupper($user['usertype'] ?? '') === 'OM' ? 'om' : 'hom';
    }

    protected function sendTicketNotifications(int $ticketId, string $department, int $accountId): void
    {
        $notificationModel = new NotificationModel();
        $employeeModel = new Employee();
        $recipients = $notificationModel->getTicketRecipientsWithType($department);
        $filerName = $employeeModel->getDisplayNameByAccountId($accountId);

        foreach ($recipients as $recipient) {
            $receiverAccountId = (int) $recipient['account_id'];
            $receiverType = strtoupper($recipient['usertype'] ?? '');
            if ($receiverAccountId === $accountId) {
                continue;
            }
            $actionUrl = $notificationModel->getTicketViewUrlForRole($receiverType, (int) $ticketId);
            $notificationModel->create(
                $receiverAccountId,
                'New Ticket Filed by ' . $filerName,
                'fa-ticket-alt',
                'primary',
                $actionUrl,
                $ticketId
            );
        }
    }

    public function index()
    {
        $user = $this->requireHOM();

        $ticketModel = new EmployeeTicket();
        $role = strtoupper($user['usertype'] ?? '');
        if ($role === 'HOM') {
            $tickets = $ticketModel->fetchTicketsByDepartment('Operations');
        } else {
            $tickets = $ticketModel->getTicketsByCreatedBy((int) $_SESSION['account_id']);
        }

        $ticketStats = [];
        foreach ($tickets as $t) {
            $s = (string) ($t['status'] ?? 'Unknown');
            $ticketStats[$s] = ($ticketStats[$s] ?? 0) + 1;
        }

        $branches = [];
        $operationsEmployees = [];
        $enableBulkTransfer = ($role === 'HOM');
        if ($enableBulkTransfer) {
            $homModel = new HOMModel();
            $branches = $homModel->getAllBranches();
            $operationsEmployees = $this->employeeModel->fetchEmployeesByDepartment('Operations');
            if (empty($_SESSION['csrf_token'])) {
                $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
            }
            $csrf_token = $_SESSION['csrf_token'];
        }

        $ctx = $this->getLoggedUserContext();
        $ctx['loggedLastname'] = $ctx['loggedLastname'] ?? '';

        $notificationData = $this->loadNotifications();
        $count = $notificationData['count'];
        $notifications = $notificationData['notifications'];

        $activePage = 'tickets';
        $user_role = $role === 'OM' ? 'OM' : 'HOM';

        require __DIR__ . '/../../Views/hom/ticket/ticket.php';
    }

    public function createMy()
    {
        $user = $this->requireHOM();
        if (!$user) return;

        $routePrefix = $this->routePrefixForUser($user);
        $accountId = (int) $_SESSION['account_id'];
        $employeeId = (int) ($this->employeeModel->getEmployeeIdByAccountId($accountId) ?? 0);
        $profile = $employeeId > 0 ? ($this->employeeModel->getEmployeeById($employeeId) ?: []) : [];
        $myAssets = $employeeId > 0 ? $this->employeeModel->fetchAssetDetailsByEmployeeId($employeeId) : [];

        if (!empty($profile) && $employeeId > 0) {
            $profile['employee_id'] = $employeeId;
            if (!empty($profile['branch_id'])) {
                foreach ((new HOMModel())->getAllBranches() as $branch) {
                    if ((int) ($branch['branch_id'] ?? 0) === (int) $profile['branch_id']) {
                        $profile['branchName'] = $branch['branchName'] ?? '';
                        break;
                    }
                }
            }
        }

        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
        }
        $csrf_token = $_SESSION['csrf_token'];

        $ctx = $this->getLoggedUserContext();
        $ctx['loggedLastname'] = $ctx['loggedLastname'] ?? '';
        $notificationData = $this->loadNotifications();
        $count = $notificationData['count'];
        $notifications = $notificationData['notifications'];

        $activePage = 'create-my-ticket';
        $user_role = strtoupper($user['usertype'] ?? '') === 'OM' ? 'OM' : 'HOM';
        $formAction = '/' . $routePrefix . '/tickets/create/my';
        $cancelUrl = '/' . $routePrefix . '/tickets';

        require __DIR__ . '/../../Views/hom/ticket/create-my.php';
    }

    public function storeMy()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit('Invalid method.');
        }

        $user = $this->requireHOM();
        if (!$user) return;

        $routePrefix = $this->routePrefixForUser($user);

        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
            $_SESSION['flash_error'] = 'Invalid form token.';
            $this->redirect('/' . $routePrefix . '/tickets/create/my');
            return;
        }

        $accountId = (int) ($_SESSION['account_id'] ?? 0);
        $employeeId = (int) ($this->employeeModel->getEmployeeIdByAccountId($accountId) ?? 0);
        if ($employeeId <= 0) {
            $_SESSION['flash_error'] = 'Unable to determine your employee record.';
            $this->redirect('/' . $routePrefix . '/tickets/create/my');
            return;
        }

        if ($this->employeeModel->countAssetsByEmployee($employeeId) === 0) {
            $_SESSION['flash_error'] = 'You need at least one assigned asset before creating a ticket.';
            $this->redirect('/' . $routePrefix . '/tickets/create/my');
            return;
        }

        $inventoryId = (int) ($_POST['inventory_id'] ?? 0);
        if ($inventoryId <= 0) {
            $_SESSION['flash_error'] = 'Please select an asset.';
            $this->redirect('/' . $routePrefix . '/tickets/create/my');
            return;
        }

        $ticketModel = new EmployeeTicket();
        $inventory = $ticketModel->getInventoryDetailsByInventoryId($inventoryId);
        if (!$inventory || (int) ($inventory['employee_id'] ?? 0) !== $employeeId) {
            $_SESSION['flash_error'] = 'Invalid asset selected.';
            $this->redirect('/' . $routePrefix . '/tickets/create/my');
            return;
        }

        $employee = $this->employeeModel->getEmployeeById($employeeId);
        $department = trim((string) ($employee['department'] ?? ''));
        $concern = trim((string) ($_POST['concern_details'] ?? ''));
        if ($concern === '') {
            $_SESSION['flash_error'] = 'Ticket description is required.';
            $this->redirect('/' . $routePrefix . '/tickets/create/my');
            return;
        }

        $priority = ucfirst(strtolower(trim((string) ($_POST['priority'] ?? 'Low'))));
        if (!in_array($priority, ['Low', 'Medium', 'High'], true)) {
            $priority = 'Low';
        }

        $branchId = (int) ($inventory['branch_id'] ?? $employee['branch_id'] ?? 0);
        $ticketId = $ticketModel->createTicket([
            'employee_id' => $employeeId,
            'inventory_id' => $inventoryId,
            'branch_id' => $branchId,
            'department' => $department,
            'category' => trim((string) ($_POST['category'] ?? '')),
            'concern_details' => $concern,
            'priority' => $priority,
            'created_by' => $accountId,
        ]);

        $this->sendTicketNotifications((int) $ticketId, $department, $accountId);

        $ticket_number = $ticketModel->getTicketNumberById((int) $ticketId) ?? 'N/A';
        $logger = new Logger();
        $logger->log('Create', 'Ticket Management', (string) $ticketId, $_SESSION['username'] ?? 'Unknown');

        $_SESSION['flash_success'] = 'Ticket created successfully! Your Ticket Number: ' . $ticket_number;
        $this->redirect('/' . $routePrefix . '/tickets');
    }

    public function create()
    {
        $user = $this->requireHOM();
        if (!$user) return;

        $routePrefix = $this->routePrefixForUser($user);
        $employeeModel = new Employee();
        $homModel = new HOMModel();
        $viewerEmployeeId = (int) ($employeeModel->getEmployeeIdByAccountId((int) $_SESSION['account_id']) ?? 0);
        $employees = array_values(array_filter(
            $employeeModel->fetchEmployeesByDepartment('Operations'),
            static function ($emp) use ($viewerEmployeeId) {
                return (int) ($emp['employee_id'] ?? 0) !== $viewerEmployeeId;
            }
        ));
        foreach ($employees as &$emp) {
            $emp['has_assets'] = ((int) $employeeModel->countAssetsByEmployee((int) ($emp['employee_id'] ?? 0))) > 0;
        }
        unset($emp);
        $branches = $homModel->getAllBranches();

        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
        }
        $csrf_token = $_SESSION['csrf_token'];

        $ctx = $this->getLoggedUserContext();
        $ctx['loggedLastname'] = $ctx['loggedLastname'] ?? '';

        $notificationData = $this->loadNotifications();
        $count = $notificationData['count'];
        $notifications = $notificationData['notifications'];

        $activePage = 'create-employee-ticket';
        $user_role = strtoupper($user['usertype'] ?? '') === 'OM' ? 'OM' : 'HOM';
        $routePrefix = $routePrefix;

        require __DIR__ . '/../../Views/hom/ticket/create.php';
    }

    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit('Invalid method.');
        }
        $this->requireHOM();
        $user = $this->employeeModel->fetchUserDetails($_SESSION['account_id']);
        $routePrefix = $this->routePrefixForUser($user ?: []);

        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
            $_SESSION['flash_error'] = 'Invalid form token.';
            $this->redirect('/' . $routePrefix . '/tickets/create/employee');
            return;
        }

        $accountId = (int) ($_SESSION['account_id'] ?? 0);
        $employeeModel = new Employee();
        $homEmployeeId = $employeeModel->getEmployeeIdByAccountId($accountId);

        if (!$homEmployeeId) {
            $_SESSION['flash_error'] = 'Unable to determine your employee record.';
            $this->redirect('/' . $routePrefix . '/tickets/create/employee');
            return;
        }

        $targetEmployeeId = (int) ($_POST['employee_id'] ?? 0);
        if ($targetEmployeeId <= 0) {
            $_SESSION['flash_error'] = 'Please select an employee.';
            $this->redirect('/' . $routePrefix . '/tickets/create/employee');
            return;
        }

        if ($targetEmployeeId === (int) $homEmployeeId) {
            $_SESSION['flash_error'] = 'Use My Ticket to file a ticket for yourself.';
            $this->redirect('/' . $routePrefix . '/tickets/create/my');
            return;
        }

        $empRow = $employeeModel->getEmployeeById($targetEmployeeId);
        if (!$empRow) {
            $_SESSION['flash_error'] = 'Invalid employee selected.';
            $this->redirect('/' . $routePrefix . '/tickets/create/employee');
            return;
        }

        if (strcasecmp((string) ($empRow['department'] ?? ''), 'Operations') !== 0) {
            $_SESSION['flash_error'] = 'Tickets can only be filed for Operations employees.';
            $this->redirect('/' . $routePrefix . '/tickets/create/employee');
            return;
        }

        if ((int) $employeeModel->countAssetsByEmployee($targetEmployeeId) <= 0) {
            $_SESSION['flash_error'] = 'Cannot create a ticket: selected employee has no assigned asset.';
            $this->redirect('/' . $routePrefix . '/tickets/create/employee');
            return;
        }

        $branchId = (int) ($empRow['branch_id'] ?? 0);
        if ($branchId <= 0) {
            $_SESSION['flash_error'] = 'Selected employee has no branch assigned.';
            $this->redirect('/' . $routePrefix . '/tickets/create/employee');
            return;
        }

        $department = trim((string) ($empRow['department'] ?? 'Operations'));

        $concern = trim((string) ($_POST['concern_details'] ?? ''));
        if ($concern === '') {
            $_SESSION['flash_error'] = 'Ticket description is required.';
            $this->redirect('/' . $routePrefix . '/tickets/create/employee');
            return;
        }

        $priority = ucfirst(strtolower(trim((string) ($_POST['priority'] ?? 'Low'))));
        if (!in_array($priority, ['Low', 'Medium', 'High'], true)) {
            $priority = 'Low';
        }

        $model = new EmployeeTicket();
        $ticketId = $model->createTicket([
            'employee_id' => $targetEmployeeId,
            'inventory_id' => !empty($_POST['inventory_id']) ? (int)$_POST['inventory_id'] : null,
            'branch_id' => $branchId,
            'department' => $department,
            'category' => trim((string) ($_POST['category'] ?? '')),
            'concern_details' => $concern,
            'priority' => $priority,
            'created_by' => $accountId,
        ]);

        $this->sendTicketNotifications((int) $ticketId, $department, $accountId);

        $ticket_number = $model->getTicketNumberById((int) $ticketId) ?? 'N/A';
        $logger = new Logger();
        $logger->log('Create', 'Ticket Management', (string) $ticketId, $_SESSION['username'] ?? 'Unknown');

        $_SESSION['flash_success'] = 'Ticket created successfully! Your Ticket Number: ' . $ticket_number;
        $this->redirect('/' . $routePrefix . '/tickets');
    }

    public function view()
    {
        $user = $this->requireHOM();

        $ticketId = (int) ($_GET['id'] ?? 0);
        if ($ticketId <= 0) {
            $_SESSION['flash_error'] = 'Invalid ticket ID.';
            $this->redirect('/hom/tickets');
            return;
        }

        $ticketModel = new EmployeeTicket();
        $ticket = $ticketModel->fetchTicketById($ticketId);

        if (!$ticket) {
            $_SESSION['flash_error'] = 'Ticket not found.';
            $this->redirect('/hom/tickets');
            return;
        }

        if (!$ticketModel->isOperationsTicket($ticketId)) {
            $_SESSION['flash_error'] = 'This ticket is not an Operations ticket.';
            $this->redirect('/hom/tickets');
            return;
        }

        $history = $ticketModel->getTicketHistory($ticketId);
        $role = strtoupper($user['usertype'] ?? '');
        $routePrefix = $role === 'OM' ? 'om' : 'hom';
        $roleLabel = $role === 'OM' ? 'OM' : 'HOM';

        $ticketStatus = strtolower((string) ($ticket['status'] ?? ''));
        $canTransferTicket = $role === 'HOM'
            && !in_array($ticketStatus, ['resolved', 'cancelled', 'closed'], true);
        $transferEmployees = [];
        if ($canTransferTicket) {
            $currentEmployeeId = (int) ($ticket['employee_id'] ?? 0);
            foreach ($this->employeeModel->fetchEmployeesByDepartment('Operations') as $emp) {
                if ((int) ($emp['employee_id'] ?? 0) !== $currentEmployeeId) {
                    $transferEmployees[] = $emp;
                }
            }
        }

        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
        }
        $csrf_token = $_SESSION['csrf_token'];

        $ctx = $this->getLoggedUserContext();
        $ctx['loggedLastname'] = $ctx['loggedLastname'] ?? '';

        $notificationData = $this->loadNotifications();
        $count = $notificationData['count'];
        $notifications = $notificationData['notifications'];

        $activePage = 'tickets';
        $user_role = $role === 'OM' ? 'OM' : 'HOM';

        require __DIR__ . '/../../Views/hom/ticket/ticket-detail.php';
    }

    /**
     * Transfer Operations ticket to another employee (HOM only)
     */
    public function transferTicket()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit('Invalid method.');
        }

        $user = $this->requireHOM();
        $role = strtoupper($user['usertype'] ?? '');
        $routePrefix = $role === 'OM' ? 'om' : 'hom';

        if ($role !== 'HOM') {
            $_SESSION['flash_error'] = 'Only HOM can transfer tickets.';
            $this->redirect('/' . $routePrefix . '/tickets');
            return;
        }

        $ticketId = (int) ($_POST['ticket_id'] ?? 0);
        $newEmployeeId = (int) ($_POST['employee_id'] ?? 0);
        $remarks = trim((string) ($_POST['remarks'] ?? ''));

        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
            $_SESSION['flash_error'] = 'Invalid form token.';
            $this->redirect('/hom/tickets/view?id=' . $ticketId);
            return;
        }

        if ($ticketId <= 0 || $newEmployeeId <= 0) {
            $_SESSION['flash_error'] = 'Invalid ticket or employee selected.';
            $this->redirect('/hom/tickets');
            return;
        }

        $ticketModel = new EmployeeTicket();
        $ticket = $ticketModel->fetchTicketById($ticketId);

        if (!$ticket || !$ticketModel->isOperationsTicket($ticketId)) {
            $_SESSION['flash_error'] = 'Ticket not found or not an Operations ticket.';
            $this->redirect('/hom/tickets');
            return;
        }

        $validEmployee = false;
        foreach ($this->employeeModel->fetchEmployeesByDepartment('Operations') as $emp) {
            if ((int) ($emp['employee_id'] ?? 0) === $newEmployeeId) {
                $validEmployee = true;
                break;
            }
        }

        if (!$validEmployee) {
            $_SESSION['flash_error'] = 'Selected employee is not a valid Operations staff member.';
            $this->redirect('/hom/tickets/view?id=' . $ticketId);
            return;
        }

        $homEmployeeId = (int) $this->employeeModel->getEmployeeIdByAccountId((int) $_SESSION['account_id']);
        [$ok, $message] = $ticketModel->transferTicketToEmployee(
            $ticketId,
            $newEmployeeId,
            $homEmployeeId,
            'HOM',
            $remarks !== '' ? $remarks : null
        );

        if ($ok) {
            $performedBy = trim(($user['firstname'] ?? '') . ' ' . ($user['lastname'] ?? ''));
            ActivityLogger::transfer(
                'HOM - Ticket Management',
                (string) $ticketId,
                $message,
                $performedBy,
                [
                    'ticket_number' => $ticket['ticket_number'] ?? '',
                    'new_employee_id' => $newEmployeeId,
                    'remarks' => $remarks,
                ]
            );
            $_SESSION['flash_success'] = $message;
        } else {
            $_SESSION['flash_error'] = $message;
        }

        $this->redirect('/hom/tickets/view?id=' . $ticketId);
    }

    /**
     * Bulk transfer all tickets from one employee to another within a branch (HOM only)
     */
    public function bulkTransferTicket()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit('Invalid method.');
        }

        $user = $this->requireHOM();
        $role = strtoupper($user['usertype'] ?? '');
        if ($role !== 'HOM') {
            $_SESSION['flash_error'] = 'Only HOM can transfer tickets.';
            $this->redirect('/hom/tickets');
            return;
        }

        $branchId = (int) ($_POST['branch_id'] ?? 0);
        $sourceEmployeeId = (int) ($_POST['source_employee_id'] ?? 0);
        $newEmployeeId = (int) ($_POST['employee_id'] ?? 0);
        $remarks = trim((string) ($_POST['remarks'] ?? ''));

        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
            $_SESSION['flash_error'] = 'Invalid form token.';
            $this->redirect('/hom/tickets');
            return;
        }

        if ($branchId <= 0 || $sourceEmployeeId <= 0 || $newEmployeeId <= 0) {
            $_SESSION['flash_error'] = 'Please select a branch and both employees.';
            $this->redirect('/hom/tickets');
            return;
        }

        if ($sourceEmployeeId === $newEmployeeId) {
            $_SESSION['flash_error'] = 'Source and destination employee must be different.';
            $this->redirect('/hom/tickets');
            return;
        }

        $ticketModel = new EmployeeTicket();
        $branchEmployees = $ticketModel->getOperationsEmployeesWithTicketsInBranch($branchId);
        $employeeIds = array_map(static fn(array $emp): int => (int) ($emp['employee_id'] ?? 0), $branchEmployees);

        if (!in_array($sourceEmployeeId, $employeeIds, true)) {
            $_SESSION['flash_error'] = 'Selected source employee has no tickets in the chosen branch.';
            $this->redirect('/hom/tickets');
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
            $this->redirect('/hom/tickets');
            return;
        }

        $tickets = $ticketModel->getOperationsTicketsForEmployee($sourceEmployeeId, $branchId);
        $ticketIds = array_map(static fn(array $row): int => (int) ($row['ticket_id'] ?? 0), $tickets);

        if (empty($ticketIds)) {
            $_SESSION['flash_error'] = 'No tickets found for this employee in the selected branch.';
            $this->redirect('/hom/tickets');
            return;
        }

        $homEmployeeId = (int) $this->employeeModel->getEmployeeIdByAccountId((int) $_SESSION['account_id']);
        [$ok, $message, $transferredCount] = $ticketModel->transferAllTicketsToEmployee(
            $ticketIds,
            $newEmployeeId,
            $homEmployeeId,
            'HOM',
            $remarks !== '' ? $remarks : null
        );

        if ($ok) {
            $performedBy = trim(($user['firstname'] ?? '') . ' ' . ($user['lastname'] ?? ''));
            ActivityLogger::transfer(
                'HOM - Ticket Management',
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

        $this->redirect('/hom/tickets');
    }

    /**
     * Get employees in a branch who have tickets (AJAX) — HOM bulk transfer
     */
    public function getEmployeesWithTicketsByBranchAjax()
    {
        header('Content-Type: application/json');

        $user = $this->requireHOM();
        if (!$user) {
            return;
        }

        if (strtoupper($user['usertype'] ?? '') !== 'HOM') {
            http_response_code(403);
            echo json_encode(['error' => 'Forbidden']);
            return;
        }

        $branchId = (int) ($_GET['branch_id'] ?? 0);
        if ($branchId <= 0) {
            echo json_encode(['error' => 'Invalid branch ID']);
            return;
        }

        $ticketModel = new EmployeeTicket();
        try {
            $employees = $ticketModel->getOperationsEmployeesWithTicketsInBranch($branchId);
            echo json_encode(['data' => $employees]);
        } catch (\Throwable $e) {
            error_log('HOM getEmployeesWithTicketsByBranchAjax error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Failed to load employees']);
        }
    }

    /**
     * Get ticket count for bulk transfer preview (AJAX) — HOM
     */
    public function getTransferableTicketCountAjax()
    {
        header('Content-Type: application/json');

        $user = $this->requireHOM();
        if (!$user) {
            return;
        }

        if (strtoupper($user['usertype'] ?? '') !== 'HOM') {
            http_response_code(403);
            echo json_encode(['error' => 'Forbidden']);
            return;
        }

        $branchId = (int) ($_GET['branch_id'] ?? 0);
        $employeeId = (int) ($_GET['employee_id'] ?? 0);

        if ($branchId <= 0 || $employeeId <= 0) {
            echo json_encode(['count' => 0]);
            return;
        }

        $ticketModel = new EmployeeTicket();
        $allowed = false;
        foreach ($ticketModel->getOperationsEmployeesWithTicketsInBranch($branchId) as $emp) {
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

        $tickets = $ticketModel->getOperationsTicketsForEmployee($employeeId, $branchId);
        echo json_encode(['count' => count($tickets)]);
    }

    /**
     * Upload technical report for a ticket
     */
    public function uploadTechnicalReport()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        header('Content-Type: application/json');

        try {
            // Authenticate user
            if (empty($_SESSION['account_id'])) {
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

            $this->requireHOM();

            // Get employee ID
            $employeeModel = new Employee();
            $employeeId = $employeeModel->getEmployeeIdByAccountId((int)$_SESSION['account_id']);

            if (!$employeeId) {
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'Employee not found']);
                exit;
            }

            // Validate ticket ID
            $ticketId = (int) ($_POST['ticket_id'] ?? 0);
            if (!$ticketId) {
                echo json_encode(['success' => false, 'message' => 'Invalid ticket ID']);
                exit;
            }

            // Verify ticket exists
            $ticketModel = new EmployeeTicket();
            $ticket = $ticketModel->fetchTicketById($ticketId);

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
                    $employeeId,
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
            error_log("Exception in uploadTechnicalReport: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
            exit;
        }
    }

    public function rate()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        $this->requireHOM();

        $ticketId = (int) ($_GET['id'] ?? 0);
        if (!$ticketId) {
            http_response_code(400);
            echo 'Invalid ticket.';
            return;
        }

        $employeeModel = new Employee();
        $homId = $employeeModel->getEmployeeIdByAccountId((int)$_SESSION['account_id']);

        require_once __DIR__ . '/../../Models/hom/TicketRatingModel.php';
        $ratingModel = new HOMTicketRatingModel();
        $alreadyRated = $ratingModel->hasRated($ticketId, $homId);

        // Debug: if already rated, log database row
        if ($alreadyRated) {
            try {
                $stmt = (new Employee())->getPDO()->prepare('SELECT * FROM ticket_ratings WHERE ticket_id = ? AND employee_id = ?');
                $stmt->execute([$ticketId, $homId]);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $logDir = __DIR__ . '/../../logs';
                if (!is_dir($logDir)) @mkdir($logDir, 0755, true);
                @file_put_contents($logDir . '/rating_debug.log', '[' . date('Y-m-d H:i:s') . "] HOM alreadyRated row: " . json_encode($row) . "\n", FILE_APPEND);
            } catch (Exception $e) {
                @file_put_contents(__DIR__ . '/../../logs/rating_debug.log', '[' . date('Y-m-d H:i:s') . "] HOM alreadyRated query failed: " . $e->getMessage() . "\n", FILE_APPEND);
            }
        }

        $base = rtrim(BASE_URL, '/');
        $user = $this->employeeModel->fetchUserDetails((int) $_SESSION['account_id']);
        $user_role = strtoupper($user['usertype'] ?? '') === 'OM' ? 'OM' : 'HOM';

        require __DIR__ . '/../../Views/hom/ticket/rate.php';
    }

    public function storeRating()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        $this->requireHOM();

        require_once __DIR__ . '/../../Models/hom/TicketRatingModel.php';

        header('Content-Type: application/json');

        $accountId = (int) ($_SESSION['account_id'] ?? 0);
        $ticketId  = (int) ($_POST['ticket_id'] ?? 0);

        if (!$ticketId) {
            echo json_encode(['success' => false, 'message' => 'Invalid ticket.']);
            exit;
        }

        $employeeModel = new Employee();
        $homId = $employeeModel->getEmployeeIdByAccountId($accountId);

        if (!$homId) {
            echo json_encode(['success' => false, 'message' => 'HOM record not found.']);
            exit;
        }

        $ticketModel = new EmployeeTicket();
        $ticket = $ticketModel->fetchTicketById($ticketId);

        if (!$ticket) {
            echo json_encode(['success' => false, 'message' => 'Ticket not found.']);
            exit;
        }

        // Get the assigned IT person
        $itId = $ticketModel->getAssignedTo($ticketId);
        if (!$itId) {
            echo json_encode(['success' => false, 'message' => 'Ticket is not assigned yet.']);
            exit;
        }

        $ratingModel = new HOMTicketRatingModel();

        if ($ratingModel->hasRated($ticketId, $homId)) {
            echo json_encode(['success' => false, 'message' => 'You already rated this ticket.']);
            exit;
        }

        $ratingModel->create(
            $ticketId,
            $homId,
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

    public function downloadTechnicalRecord()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        $this->requireHOM();

        if (empty($_SESSION['account_id'])) {
            http_response_code(401);
            echo 'Unauthorized';
            exit;
        }

        $ticketId = (int) ($_GET['id'] ?? 0);
        if (!$ticketId) {
            http_response_code(400);
            echo 'Invalid ticket ID';
            exit;
        }

        $employeeModel = new Employee();
        $homId = $employeeModel->getEmployeeIdByAccountId((int)$_SESSION['account_id']);

        if (!$homId) {
            http_response_code(401);
            echo 'HOM record not found';
            exit;
        }

        require_once __DIR__ . '/../../Services/PdfGeneratorService.php';
        $pdfService = new PdfGeneratorService();

        // HOM should be allowed to generate records for tickets they manage
        $result = $pdfService->generateTechnicalRecordDocx($ticketId, $homId, true);

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
}
