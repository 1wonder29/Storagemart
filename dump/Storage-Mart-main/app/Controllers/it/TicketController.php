<?php
// app/Controllers/employee/TicketController.php

require_once __DIR__ . '/../AuthController.php';
require_once __DIR__ . '/../../Models/it/IT.php';
require_once __DIR__ . '/../../Models/it/ItTicketModel.php';
require_once __DIR__ . '/../../Helpers/Session.php';
require_once __DIR__ . '/../../Models/admin/Logger.php';
class TicketController extends AuthController
{
    public function create()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (empty($_SESSION['account_id'])) {
            $this->redirect('/login');
            return;
        }

        $inventory_id = (int)($_GET['inventory_id'] ?? 0);

        $model = new ItTicketModel();
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
        require __DIR__ . '/../../Views/it/asset/file_ticket.php';
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
            $this->redirect('/it/assets');
            return;
        }

        $accountId = (int)($_SESSION['account_id'] ?? 0);

        // ✅ use IT model (employee/account responsibility)
        $itModel = new IT();
        $employeeId = $itModel->getEmployeeIdByAccountId($accountId);

        if (!$employeeId) {
            $_SESSION['flash_error'] = "Unable to determine your employee record.";
            $this->redirect('/it/assets');
            return;
        }

        // ✅ ticket operations use ItTicketModel
        $ticketModel = new ItTicketModel();

        // normalize priority
        $priority = ucfirst(strtolower(trim($_POST['priority'] ?? 'Low')));
        if (!in_array($priority, ['Low','Medium','High'], true)) $priority = 'Low';

        $ticketId = $ticketModel->createTicket([
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
            $_SESSION['flash_error'] = "Unable to determine your employee record.";
            $this->redirect('/employee/assets');
            return;
        }

        /* ✅ GET EMPLOYEE DEPARTMENT SAFELY */
        $employee = $itModel->getEmployeeById($employeeId);
        $department = $employee['department'] ?? null;

        if (!$department) {
            $_SESSION['flash_error'] = "Unable to determine department.";
            $this->redirect('/employee/assets');
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
        $ticket_number = $ticketModel->getTicketNumberById($ticketId) ?? 'N/A';


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
        $this->redirect('/it/tickets');

    }

    public function index()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (empty($_SESSION['account_id'])) {
            $this->redirect('/login');
            return;
        }

        $ItModel = new IT();
        $employeeId = $ItModel->getEmployeeIdByAccountId((int)$_SESSION['account_id']);

        if (!$employeeId) {
            $_SESSION['flash_error'] = 'No employee record linked to your account.';
            $tickets = [];
        } else {
            $ticketModel = new ItTicketModel();
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
        require __DIR__ . '/../../Views/it/ticket/ticket.php';
    }

    public function fetchHistory()
    {
        if (!isset($_GET['ticket_id'])) {
            echo json_encode([]);
            return;
        }

        $ticketId = (int)$_GET['ticket_id'];

        $model = new ItTicketModel();
        $history = $model->getTicketHistory($ticketId);

        header('Content-Type: application/json');
        echo json_encode($history);
    }


    public function in_progress()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (empty($_SESSION['account_id'])) {
            $this->redirect('/login');
            return;
        }

        $accountId = (int) $_SESSION['account_id'];
        $itModel = new IT();

        $employeeId = $itModel->getEmployeeIdByAccountId($accountId);
        if (!$employeeId) {
            die('Employee not found');
        }

        $ticketModel = new ItTicketModel();

        $tickets = $ticketModel->getInProgressTickets();
        $ctx = $this->getLoggedUserContext();
        $base = $ctx['base'];
        $loggedFirstname = $ctx['loggedFirstname'];
        $loggedPosition = $ctx['loggedPosition'];
                $notificationData = $this->loadNotifications();

        $count = $notificationData['count'];
        $notifications = $notificationData['notifications'];
        require __DIR__ . '/../../Views/it/ticket/in_progress.php';
    }
    public function update()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (empty($_SESSION['account_id'])) {
            $this->redirect('/login');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit;
        }

        $ticketId = (int)($_POST['ticket_id'] ?? 0);
        $action   = trim($_POST['action'] ?? '');
        $remarks  = trim($_POST['remarks'] ?? '');

        if (!$ticketId || !$action) {
            $_SESSION['flash_error'] = 'Invalid request.';
            $this->redirect('/it/tickets/in_progress');
            return;
        }

        // 🔑 resolve employee_id
        $itModel = new IT();
        $employeeId = $itModel->getEmployeeIdByAccountId((int)$_SESSION['account_id']);

        if (!$employeeId) {
            $_SESSION['flash_error'] = 'Employee not found.';
            $this->redirect('/it/tickets/in_progress');
            return;
        }

        $ticketModel = new ItTicketModel();

        // 🔐 EXACT vanilla ownership check
        $assignedTo = $ticketModel->getAssignedTo($ticketId);
        if ($assignedTo === null || (int)$assignedTo !== (int)$employeeId) {
            $_SESSION['flash_error'] = 'You are not allowed to modify this ticket.';
            $this->redirect('/it/tickets/in_progress');
            return;
        }

        // ✅ action → status
        switch ($action) {
            case 'Resolve':    $status = 'Resolved';   break;
            case 'On Hold':    $status = 'On Hold';    break;
            case 'Unresolved': $status = 'Unresolved'; break;
            default:           $status = 'In Progress';
        }
        // 🔔 Notify ticket owner (employee) when resolved
        if ($status === 'Resolved') {

            // This MUST return the ACCOUNT ID of the ticket owner
            $receiverAccountId = $ticketModel->getEmployeeAccountIdByTicketId($ticketId);

            if ($receiverAccountId) {
                require_once __DIR__ . '/../../Models/NotificationModel.php';

                $notificationModel = new NotificationModel();
                $base = $this->getLoggedUserContext()['base'];
                $notificationModel->create(
                    (int) $receiverAccountId,
                    'Your ticket has been resolved. Click to rate IT support.',
                    'fa-star',
                    'success',
                    $base . '/employee/tickets/rate?id=' . $ticketId,
                    $ticketId
                );

            }
        }


        // =============================
        // 1️⃣ Update tbltickets
        // =============================
        $ticketModel->updateTicket($ticketId, $status, $remarks);

        // =============================
        // 2️⃣ Insert tblticket_technical
        // =============================
        $ticketModel->insertTechnical([
            'ticket_id'          => $ticketId,
            'performed_by'       => $employeeId,
            'technical_purpose'  => trim($_POST['technical_purpose'] ?? ''),
            'action_taken'       => trim($_POST['action_taken'] ?? ''),
            'result'             => trim($_POST['result'] ?? ''),
            'remarks'            => $remarks
        ]);

        // =============================
        // 3️⃣ Insert tblticket_history
        // =============================
        $ticketModel->insertHistory([
            'ticket_id'       => $ticketId,
            'action_type'     => $status,
            'action_details'  => "Ticket {$status} by IT Staff (Account ID: {$_SESSION['account_id']})",
            'old_status'      => 'In Progress',
            'new_status'      => $status,
            'performed_by'    => $_SESSION['account_id'],
            'performed_role'  => 'IT Staff'
        ]);

        $_SESSION['flash_success'] = "Ticket marked as {$status}.";
        $this->redirect('/it/tickets/in_progress');
    }


    public function resolve(){
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (empty($_SESSION['account_id'])) {
            $this->redirect('/login');
            return;
        }

        $technicalModel = new ItTicketModel();
        $tickets = $technicalModel->getResolvedTechnicalTickets();

        $ctx = $this->getLoggedUserContext();
        $base = $ctx['base'];
        $loggedFirstname = $ctx['loggedFirstname'];
        $loggedPosition  = $ctx['loggedPosition'];
        $notificationData = $this->loadNotifications();

        $count = $notificationData['count'];
        $notifications = $notificationData['notifications'];

        require __DIR__ . '/../../Views/it/ticket/resolve.php';
    }
}
