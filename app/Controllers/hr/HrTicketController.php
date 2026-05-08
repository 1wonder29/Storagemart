<?php
// app/Controllers/hr/HrTicketController.php

require_once __DIR__ . '/../AuthController.php';
require_once __DIR__ . '/../../Models/employee/Employee.php';
require_once __DIR__ . '/../../Models/employee/Ticket.php';
require_once __DIR__ . '/../../Helpers/Session.php';
require_once __DIR__ . '/../../Models/admin/Logger.php';

class HrTicketController extends AuthController
{
    public function index()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (empty($_SESSION['account_id'])) {
            $this->redirect('/login');
            return;
        }

        $employeeModel = new Employee();
        $user = $employeeModel->fetchUserDetails((int)$_SESSION['account_id']);

        if (!$user || strtoupper($user['usertype']) !== 'HR') {
            http_response_code(403);
            exit('Unauthorized');
        }

        // Get only tickets created by this HR account
        $ticketModel = new EmployeeTicket();
        $tickets = $ticketModel->getTicketsByCreatedBy((int)$_SESSION['account_id']);

        $ctx = $this->getLoggedUserContext();
        $base = $ctx['base'];
        $loggedFirstname = $ctx['loggedFirstname'];
        $loggedPosition = $ctx['loggedPosition'];

        $notificationData = $this->loadNotifications();
        $count = $notificationData['count'];
        $notifications = $notificationData['notifications'];

        require __DIR__ . '/../../Views/hr/ticket/ticket.php';
    }

    public function create()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (empty($_SESSION['account_id'])) {
            $this->redirect('/login');
            return;
        }

        $inventory_id = (int)($_GET['inventory_id'] ?? 0);

        $empModel = new Employee();
        $model = new EmployeeTicket();
        $inventory = $model->getInventoryDetailsByInventoryId($inventory_id);

        // If no inventory selected, populate with logged-in user's employee details
        if (empty($inventory) && !empty($_SESSION['account_id'])) {
            $employeeId = $empModel->getEmployeeIdByAccountId((int)$_SESSION['account_id']);
            if ($employeeId) {
                $empData = $empModel->getEmployeeById($employeeId);
                if ($empData) {
                    $inventory = [
                        'employee_id' => $empData['employee_id'] ?? '',
                        'fullname' => ($empData['lastname'] ?? '') . ', ' . ($empData['firstname'] ?? '') . ' ' . ($empData['middlename'] ?? ''),
                        'department' => $empData['department'] ?? '',
                        'branch_id' => $empData['branch_id'] ?? '',
                        'branchName' => '',
                        'inventory_id' => '',
                        'assetNumber' => '',
                        'groupName' => ''
                    ];
                }
            }
        }

        // Prepare base + loggeduser
        $ctx = $this->getLoggedUserContext();
        $base = $ctx['base'];
        $loggedFirstname = $ctx['loggedFirstname'];
        $loggedPosition  = $ctx['loggedPosition'];

        // Create CSRF
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
        }
        $csrf_token = $_SESSION['csrf_token'];
        $notificationData = $this->loadNotifications();

        $count = $notificationData['count'];
        $notifications = $notificationData['notifications'];
        require __DIR__ . '/../../Views/hr/asset/file_ticket.php';
    }

    public function store()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            die("Invalid method.");
        }

        // CSRF
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
            $_SESSION['flash_error'] = "Invalid form token.";
            $this->redirect('/hr/tickets');
            return;
        }

        $accountId = (int)($_SESSION['account_id'] ?? 0);
        $employeeModel = new Employee();
        $employeeId = $employeeModel->getEmployeeIdByAccountId($accountId);

        if (!$employeeId) {
            $_SESSION['flash_error'] = "Unable to determine your HR record.";
            $this->redirect('/hr/tickets');
            return;
        }

        /* ✅ GET HR EMPLOYEE DETAILS FIRST */
        $employee = $employeeModel->getEmployeeById($employeeId);
        $department = $employee['department'] ?? null;
        $employeeBranchId = $employee['branch_id'] ?? 0;

        if (!$department) {
            $_SESSION['flash_error'] = "Unable to determine department.";
            $this->redirect('/hr/tickets');
            return;
        }

        $model = new EmployeeTicket();

        // normalize priority
        $priority = ucfirst(strtolower(trim($_POST['priority'] ?? 'Low')));
        if (!in_array($priority, ['Low','Medium','High'], true)) $priority = 'Low';

        // Use employee's branch if not provided in POST
        $branchId = (int)($_POST['branch_id'] ?? 0);
        if ($branchId === 0) {
            $branchId = $employeeBranchId;
        }

        $ticketId = $model->createTicket([
            'employee_id'     => (int)$employeeId,
            'inventory_id'    => (int)($_POST['inventory_id'] ?? 0),
            'branch_id'       => $branchId,
            'department'      => trim($_POST['department'] ?? $department),
            'category'        => trim($_POST['category'] ?? ''),
            'concern_details' => trim($_POST['concern_details'] ?? ''),
            'priority'        => $priority,
            'created_by'      => $accountId
        ]);

        require_once __DIR__ . '/../../Models/NotificationModel.php';

        $notificationModel = new NotificationModel();

        // 🔔 Get recipients with their usertype for role-based redirect
        $recipients = $notificationModel->getTicketRecipientsWithType($department);

        // 🔕 Do not notify the ticket filer
        $currentAccountId = (int) $_SESSION['account_id'];

        foreach ($recipients as $recipient) {
            $receiverAccountId = (int)$recipient['account_id'];
            $receiverType      = strtoupper($recipient['usertype'] ?? '');

            if ($receiverAccountId === $currentAccountId) {
                continue;
            }

            // Route each role to their own tickets page
            if ($receiverType === 'ADMIN') {
                $actionUrl = '/admin/tickets';
            } elseif ($receiverType === 'HEAD') {
                $actionUrl = '/head/tickets';
            } else {
                $actionUrl = '/it/tickets';
            }

            $notificationModel->create(
                $receiverAccountId,
                'New Ticket Filed by HR',
                'fa-ticket-alt',
                'primary',
                $actionUrl,
                $ticketId
            );
        }

        $ticket_number = $model->getTicketNumberById((int)$ticketId) ?? 'N/A';

        // log creation
        $logger = new Logger();
        $logger->log(
            'Create',
            'Ticket Management',
            $ticketId,
            $_SESSION['username'] ?? 'Unknown'
        );

        // Compose success message with ticket number
        $_SESSION['flash_success'] = "Ticket created successfully! Your Ticket Number: " . $ticket_number;

        // redirect
        $this->redirect('/hr/tickets');
    }

    public function fetchHistory()
    {
        header('Content-Type: application/json');
        
        if (!isset($_GET['ticket_id'])) {
            echo json_encode(['success' => false, 'data' => []]);
            return;
        }

        try {
            $ticketId = (int)$_GET['ticket_id'];

            $model = new EmployeeTicket();
            $history = $model->getTicketHistory($ticketId);

            echo json_encode([
                'success' => true,
                'data' => $history ?? []
            ]);
        } catch (\Throwable $e) {
            error_log('HrTicketController::fetchHistory error: ' . $e->getMessage());
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage(),
                'data' => []
            ]);
        }
    }
}
?>
