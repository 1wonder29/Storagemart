<?php
// app/Controllers/employee/TicketController.php

require_once __DIR__ . '/../AuthController.php';
require_once __DIR__ . '/../../Models/employee/Employee.php';
require_once __DIR__ . '/../../Models/employee/Ticket.php';
require_once __DIR__ . '/../../Helpers/Session.php';
require_once __DIR__ . '/../../Models/admin/Logger.php';
require_once __DIR__ . '/../../Models/employee/TicketRatingModel.php';


class EmployeeTicketController extends AuthController
{
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
        require __DIR__ . '/../../Views/employee/asset/file_ticket.php';
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
            $this->redirect('/employee/assets');
            return;
        }

        $accountId = (int)($_SESSION['account_id'] ?? 0);
        $employeeModel = new Employee();
        $employeeId = $employeeModel->getEmployeeIdByAccountId($accountId);

        if (!$employeeId) {
            $_SESSION['flash_error'] = "Unable to determine your employee record.";
            $this->redirect('/employee/assets');
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

        /* ✅ GET EMPLOYEE DEPARTMENT SAFELY */
        $employee = $employeeModel->getEmployeeById($employeeId);
        $department = $employee['department'] ?? null;

        if (!$department) {
            $_SESSION['flash_error'] = "Unable to determine department.";
            $this->redirect('/employee/assets');
            return;
        }

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
                'New Ticket Filed by Employee',
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
        $this->redirect('/employee/tickets');

    }

    public function index()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (empty($_SESSION['account_id'])) {
            $this->redirect('/login');
            return;
        }

        $employeeModel = new Employee();
        $employeeId = $employeeModel->getEmployeeIdByAccountId((int)$_SESSION['account_id']);

        if (!$employeeId) {
            $_SESSION['flash_error'] = 'No employee record linked to your account.';
            $tickets = [];
        } else {
            $ticketModel = new EmployeeTicket();
            $tickets = $ticketModel->fetchAllTicketsByEmployee((int)$employeeId);
        }

        // supply variables to view
        $ctx = $this->getLoggedUserContext();
        $base = $ctx['base'];
        $loggedFirstname = $ctx['loggedFirstname'];
        $loggedPosition = $ctx['loggedPosition'];
        $notificationData = $this->loadNotifications();

        $count = $notificationData['count'];
        $notifications = $notificationData['notifications'];
        require __DIR__ . '/../../Views/employee/ticket/ticket.php';
    }

    public function fetchHistory()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        // BUG-11 fix: authenticate before returning any ticket data
        if (empty($_SESSION['account_id'])) {
            header('Content-Type: application/json');
            http_response_code(401);
            echo json_encode([]);
            return;
        }

        if (!isset($_GET['ticket_id'])) {
            header('Content-Type: application/json');
            echo json_encode([]);
            return;
        }

        $ticketId = (int)$_GET['ticket_id'];

        $model = new EmployeeTicket();
        $history = $model->getTicketHistory($ticketId);

        header('Content-Type: application/json');
        echo json_encode($history);
    }

 public function rate()
{
    if (session_status() === PHP_SESSION_NONE) session_start();

    $ticketId = (int) ($_GET['id'] ?? 0);
    if (!$ticketId) {
        http_response_code(400);
        echo 'Invalid ticket.';
        return;
    }

    $employeeModel = new Employee();
    $employeeId = $employeeModel->getEmployeeIdByAccountId((int)$_SESSION['account_id']);

    $ratingModel = new TicketRatingModel();
    $alreadyRated = $ratingModel->hasRated($ticketId, $employeeId);

    $base = rtrim(BASE_URL, '/');

    require __DIR__ . '/../../Views/employee/ticket/rate.php';
}



public function storeRating()
{
    if (session_status() === PHP_SESSION_NONE) session_start();

    require_once __DIR__ . '/../../Models/employee/Employee.php';
    require_once __DIR__ . '/../../Models/employee/TicketRatingModel.php';
    require_once __DIR__ . '/../../Models/employee/Ticket.php';

    header('Content-Type: application/json');

    $accountId = (int) ($_SESSION['account_id'] ?? 0);
    $ticketId  = (int) ($_POST['ticket_id'] ?? 0);

    if (!$ticketId) {
        echo json_encode(['success' => false, 'message' => 'Invalid ticket.']);
        exit;
    }

    // account_id → employee_id
    $employeeModel = new Employee();
    $employeeId = $employeeModel->getEmployeeIdByAccountId($accountId);

    if (!$employeeId) {
        echo json_encode(['success' => false, 'message' => 'Employee not found.']);
        exit;
    }

    // get IT assigned to ticket
    $ticketModel = new EmployeeTicket();
    $itId = $ticketModel->getAssignedTo($ticketId);

    if (!$itId) {
        echo json_encode(['success' => false, 'message' => 'Ticket is not assigned yet.']);
        exit;
    }

    $ratingModel = new TicketRatingModel();

    // prevent double rating
    if ($ratingModel->hasRated($ticketId, $employeeId)) {
        echo json_encode(['success' => false, 'message' => 'You already rated this ticket.']);
        exit;
    }

    // save rating
    $ratingModel->create(
        $ticketId,
        $employeeId,
        $itId,
        $_POST['rating'],
        $_POST['comment'] ?? ''
    );

    echo json_encode([
        'success' => true,
        'message' => 'Thank you for rating IT support!'
    ]);
    exit;
}

}