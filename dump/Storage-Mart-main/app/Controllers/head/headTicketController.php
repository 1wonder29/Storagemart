<?php
// app/Controllers/head/HeadTicketController.php

require_once __DIR__ . '/../AuthController.php';
require_once __DIR__ . '/../../Models/employee/Employee.php';
require_once __DIR__ . '/../../Models/employee/Ticket.php';
require_once __DIR__ . '/../../Helpers/Session.php';
require_once __DIR__ . '/../../Models/admin/Logger.php';
class headTicketController extends AuthController
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

        if (!$user || strtoupper($user['usertype']) !== 'HEAD') {
            http_response_code(403);
            exit('Unauthorized');
        }

        $employee = $employeeModel->getEmployeeById((int)$user['employee_id']);
        $department = $employee['department'] ?? null;

        if (!$department) {
            $_SESSION['flash_error'] = 'Department not found.';
            $this->redirect('/head/dashboard');
            return;
        }

        $ticketModel = new EmployeeTicket();
        $tickets = $ticketModel->fetchTicketsByDepartment($department);

        $ctx = $this->getLoggedUserContext();
        $base = $ctx['base'];
        $loggedFirstname = $ctx['loggedFirstname'];
        $loggedPosition = $ctx['loggedPosition'];

        $notificationData = $this->loadNotifications();
        $count = $notificationData['count'];
        $notifications = $notificationData['notifications'];

        require __DIR__ . '/../../Views/head/ticket/ticket.php';
    }

        public function create()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (empty($_SESSION['account_id'])) {
            $this->redirect('/login');
            return;
        }

        $inventory_id = (int)($_GET['inventory_id'] ?? 0);

        $model = new EmployeeTicket();
        $inventory = $model->getInventoryDetailsByInventoryId($inventory_id);

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
        require __DIR__ . '/../../Views/head/asset/file_ticket.php';
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
            $this->redirect('/head/assets');
            return;
        }

        $accountId = (int)($_SESSION['account_id'] ?? 0);
        $employeeModel = new Employee();
        $employeeId = $employeeModel->getEmployeeIdByAccountId($accountId);

        if (!$employeeId) {
            $_SESSION['flash_error'] = "Unable to determine your head record.";
            $this->redirect('/head/assets');
            return;
        }

        $model = new EmployeeTicket();

        // normalize priority
        $priority = ucfirst(strtolower(trim($_POST['priority'] ?? 'Low')));
        if (!in_array($priority, ['Low','Medium','High'], true)) $priority = 'Low';

        $ticketId = $model->createTicket([
            'employee_id'     => (int)$employeeId,
            'inventory_id'    => (int)($_POST['inventory_id'] ?? 0),
            'branch_id'       => (int)($_POST['branch_id'] ?? 0),
            'department'      => trim($_POST['department'] ?? ''),
            'category'        => trim($_POST['category'] ?? ''),
            'concern_details' => trim($_POST['concern_details'] ?? ''),
            'priority'        => $priority,
            'created_by'      => $accountId
        ]);

        if (!$employeeId) {
            $_SESSION['flash_error'] = "Unable to determine your head record.";
            $this->redirect('/head/assets');
            return;
        }

        /* ✅ GET EMPLOYEE DEPARTMENT SAFELY */
        $employee = $employeeModel->getEmployeeById($employeeId);
        $department = $employee['department'] ?? null;

        if (!$department) {
            $_SESSION['flash_error'] = "Unable to determine department.";
            $this->redirect('/head/assets');
            return;
        }

        require_once __DIR__ . '/../../Models/NotificationModel.php';

        $notificationModel = new NotificationModel();

        // 🔔 Get recipients
        $recipients = $notificationModel->getTicketRecipients($department);

        // 🔗 Link IT users will click
        $base = $this->getLoggedUserContext()['base'];
        $actionUrl = $base . '/it/tickets';

        // 🔕 Do not notify the ticket filer
        $currentAccountId = (int) $_SESSION['account_id'];

        foreach ($recipients as $receiverAccountId) {

            if ((int)$receiverAccountId === $currentAccountId) {
                continue;
            }

            $notificationModel->create(
                (int)$receiverAccountId,
                'New IT Ticket Filed',
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
        $this->redirect('/head/tickets');

    }
}
