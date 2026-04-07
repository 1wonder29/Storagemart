<?php
require_once __DIR__ . '/../AuthController.php';
require_once __DIR__ . '/../../Models/admin/Account.php';
require_once __DIR__ . '/../../Models/admin/Logger.php';
require_once __DIR__ . '/../../Helpers/Session.php';
require_once __DIR__ . '/../../Models/admin/Ticket.php';

class TicketController extends AuthController
{
    public function ticket()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Auth check – only ADMIN allowed
        if (empty($_SESSION['account_id']) || strtoupper($_SESSION['usertype'] ?? '') !== 'ADMIN') {
            $this->redirect('/login');
            return;
        }

        // --- USE TICKET MODEL HERE ---
        $ticketModel = new Ticket();

        $tickets = $ticketModel->fetchTicket();
        $itStaff = $ticketModel->fetchEmployeesByDepartment('IT');

        // CSRF token (for future forms)
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
        }
        $csrf_token = $_SESSION['csrf_token'];

        // layout/context helpers (from AuthController/AdminController)
        $ctx = $this->getLoggedUserContext();
        $base = $ctx['base'];
        $loggedFirstname = $ctx['loggedFirstname'];
        $loggedPosition  = $ctx['loggedPosition'];
        $notificationData = $this->loadNotifications();

        $count = $notificationData['count'];
        $notifications = $notificationData['notifications'];
        // render view
        require __DIR__ . '/../../Views/admin/ticket/ticket.php';

    }
    // fetch ticket history
    public function history()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Auth check – only ADMIN allowed (same rule as ticket())
        if (empty($_SESSION['account_id']) || strtoupper($_SESSION['usertype'] ?? '') !== 'ADMIN') {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }

        header('Content-Type: application/json');

        $ticketId = isset($_GET['ticket_id']) ? (int) $_GET['ticket_id'] : 0;

        if ($ticketId <= 0) {
            http_response_code(400);
            echo json_encode([]);
            return;
        }

        $ticketModel = new Ticket();
        $history = $ticketModel->fetchTicketHistory($ticketId); // we’ll add this next

        echo json_encode($history);
    }

    public function updateAssignment()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Only ADMIN (or adjust as needed)
        if (empty($_SESSION['account_id']) || strtoupper($_SESSION['usertype'] ?? '') !== 'ADMIN') {
            $this->redirect('/login');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo 'Method Not Allowed';
            return;
        }

        // CSRF
        if (empty($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
            $_SESSION['error'] = 'Invalid form token.';
            $this->redirect('/admin/tickets');
            return;
        }

        $ticketId    = isset($_POST['ticket_id']) ? (int)$_POST['ticket_id'] : 0;
        $assignedTo  = isset($_POST['assigned_to']) && $_POST['assigned_to'] !== '' ? (int)$_POST['assigned_to'] : 0;
        $remarks     = trim($_POST['remarks'] ?? '');
        $accountId   = (int)($_SESSION['account_id'] ?? 0);
        $username    = $_SESSION['username'] ?? 'Unknown';

        $ticketModel = new Ticket();

        try {
            [$ok, $message] = $ticketModel->reassignTicket(
                $ticketId,
                $assignedTo,
                $remarks,
                $accountId,
                $username
            );

            if ($ok) {
                $_SESSION['success'] = $message;
            } else {
                $_SESSION['error'] = $message;
            }
        } catch (\Throwable $e) {
            $_SESSION['error'] = 'Failed to update ticket: ' . $e->getMessage();
        }

        $this->redirect('/admin/tickets');
    }

    public function add(){
        if (session_status() === PHP_SESSION_NONE) {
        session_start();
        }

        if (empty($_SESSION['account_id']) || strtoupper($_SESSION['usertype'] ?? '') !== 'ADMIN') {
            $this->redirect('/login');
            return;
        }

        $ctx = $this->getLoggedUserContext();
        $base = $ctx['base'];
        $loggedFirstname = $ctx['loggedFirstname'];
        $loggedPosition  = $ctx['loggedPosition'];
                $notificationData = $this->loadNotifications();

        $count = $notificationData['count'];
        $notifications = $notificationData['notifications'];
        require __DIR__ . '/../../Views/admin/ticket/add.php'; 
    }
    public function searchEmployee(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Optional: same auth limitation
        if (empty($_SESSION['account_id']) || strtoupper($_SESSION['usertype'] ?? '') !== 'ADMIN') {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }

        header('Content-Type: application/json');

        $q = isset($_GET['q']) ? trim($_GET['q']) : '';

        if ($q === '') {
            echo json_encode([
                'success' => false,
                'message' => 'Empty search query',
            ]);
            return;
        }

        $ticketModel = new Ticket();
        $employee = $ticketModel->searchEmployee($q);

        if ($employee) {
            echo json_encode([
                'success'     => true,
                'employee_id' => $employee['employee_id'],
                'full_name'   => $employee['full_name'],
                'branchName'  => $employee['branchName'],
                'department'  => $employee['department'],
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'No employee found',
            ]);
        }
    }


    public function getAssets(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Optional: same auth rule (ADMIN only)
        if (empty($_SESSION['account_id']) || strtoupper($_SESSION['usertype'] ?? '') !== 'ADMIN') {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }

        header('Content-Type: application/json');

        $employeeId = isset($_GET['employee_id']) ? (int)$_GET['employee_id'] : 0;

        if ($employeeId <= 0) {
            echo json_encode([
                'success' => false,
                'message' => 'Invalid employee ID',
            ]);
            return;
        }

        $ticketModel = new Ticket();
        $assets = $ticketModel->fetchAssetsByEmployee($employeeId);

        echo json_encode([
            'success' => true,
            'data'    => $assets,
        ]);

    }

    public function fileTicket()
    {
    if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['account_id']) || strtoupper($_SESSION['usertype'] ?? '') !== 'ADMIN') {
            $this->redirect('/login');
            return;
        }

        $inventoryId = isset($_GET['inventory_id']) ? (int)$_GET['inventory_id'] : 0;
        if ($inventoryId <= 0) {
            // you can redirect back or show error
            $this->redirect('/admin/tickets');
            return;
        }

        $ticketModel = new Ticket();
        $inventory   = $ticketModel->getInventoryDetailsByInventoryId($inventoryId);

        if (!$inventory) {
            // no matching record
            $this->redirect('/admin/tickets');
            return;
        }

        // IT staff list for "Assign to" dropdown
        $itStaff = $ticketModel->fetchEmployeesByDepartment('IT');

        // layout context
        $ctx = $this->getLoggedUserContext();
        $base = $ctx['base'];
        $loggedFirstname = $ctx['loggedFirstname'];
        $loggedPosition  = $ctx['loggedPosition'];
        $notificationData = $this->loadNotifications();

        $count = $notificationData['count'];
        $notifications = $notificationData['notifications'];
        // you can pass notification via session if needed
        $notificationMessage = $_SESSION['notification'] ?? '';
        unset($_SESSION['notification']);

        require __DIR__ . '/../../Views/admin/ticket/file_ticket.php'; // new view file
    }

    public function storeFile()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['account_id']) || strtoupper($_SESSION['usertype'] ?? '') !== 'ADMIN') {
            $this->redirect('/login');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo 'Method Not Allowed';
            return;
        }

        $ticketModel = new Ticket();

        // from POST
        $employee_id      = (int)($_POST['employee_id'] ?? 0);
        $inventory_id     = (int)($_POST['inventory_id'] ?? 0);
        $branch_id        = (int)($_POST['branch_id'] ?? 0);
        $department       = trim($_POST['department'] ?? '');
        $category         = trim($_POST['category'] ?? '');
        $concern_details  = trim($_POST['concern_details'] ?? '');
        $ticket_assign    = trim($_POST['ticket_assign'] ?? '');   // employee_id of IT staff
        $technical_purpose= trim($_POST['technical_purpose'] ?? '');
        $actionTaken      = trim($_POST['action'] ?? '');
        $resultDetails    = trim($_POST['result'] ?? '');
        $priorityInput    = trim($_POST['priority'] ?? 'Low');
        $remarks          = trim($_POST['remarks'] ?? '');
        $created_by       = (int)($_SESSION['account_id'] ?? 0);

        // normalize priority to match enum ('Low','Medium','High')
        $priority = ucfirst(strtolower($priorityInput));
        if (!in_array($priority, ['Low', 'Medium', 'High'], true)) {
            $priority = 'Low';
        }

        $status = 'Pending'; // must match enum case

        // assigned_to is the IT employee id or null
        $assigned_to = $ticket_assign !== '' ? (int)$ticket_assign : null;

        try {
            // 1) Insert main ticket
            $ticketId = $ticketModel->createTicket([
                'employee_id'     => $employee_id,
                'inventory_id'    => $inventory_id,
                'branch_id'       => $branch_id ?: null,
                'department'      => $department ?: null,
                'category'        => $category ?: null,
                'concern_details' => $concern_details ?: null,
                'priority'        => $priority,
                'status'          => $status,
                'remarks'         => $remarks ?: null,
                'assigned_to'     => $assigned_to,
                'created_by'      => $created_by,
            ]);

            // who "performed" the technical action?
            // If you want it to be the IT staff you assigned to:
            $performedByEmployeeId = $assigned_to;

            // If you prefer the current user’s employee_id, use:
            // $performedByEmployeeId = $ticketModel->getEmployeeIdByAccountId($created_by);

            if ($performedByEmployeeId) {
                // 2) Insert technical details
                $ticketModel->addTechnicalDetails([
                    'ticket_id'        => $ticketId,
                    'performed_by'     => $performedByEmployeeId,
                    'technical_purpose'=> $technical_purpose,
                    'action_taken'     => $actionTaken,
                    'result'           => $resultDetails,
                    'remarks'          => $remarks,
                ]);
            }

            // 3) Log with Logger
            $logger = new Logger();
            $logger->log(
                'Create',
                'Ticket Management',
                $ticketId,
                $_SESSION['username'] ?? 'Unknown'
            );

            // 4) Flash + redirect
            $_SESSION['flash_success'] = 'New Ticket successfully created!';
            $this->redirect('/admin/tickets');

        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = 'Error creating ticket: ' . $e->getMessage();
            $this->redirect('/admin/tickets');
        }
    }

    public function pendings()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (empty($_SESSION['account_id']) || strtoupper($_SESSION['usertype'] ?? '') !== 'ADMIN') {
            $this->redirect('/login'); return;
        }

        $ticketModel = new Ticket();
        $tickets = $ticketModel->fetchPendingTickets();
        $itStaff = $ticketModel->fetchITStaff();

        if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
        $csrf_token = $_SESSION['csrf_token'];

        $ctx = $this->getLoggedUserContext();
        $base = $ctx['base'];
        $loggedFirstname = $ctx['loggedFirstname'];
        $loggedPosition  = $ctx['loggedPosition'];
        $notificationData = $this->loadNotifications();

        $count = $notificationData['count'];
        $notifications = $notificationData['notifications'];
        require_once __DIR__ . '/../../Views/admin/ticket/pending.php';
    }

    // Approve & assign (POST)
    public function approveAssign()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (empty($_SESSION['account_id']) || strtoupper($_SESSION['usertype'] ?? '') !== 'ADMIN') {
            $this->redirect('/login'); return;
        }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405); echo 'Method Not Allowed'; return;
        }

        // basic CSRF
        $posted = $_POST['csrf_token'] ?? '';
        if (empty($posted) || $posted !== ($_SESSION['csrf_token'] ?? '')) {
            $_SESSION['flash_error'] = 'Invalid CSRF token.'; $this->redirect('/admin/tickets/pending'); return;
        }

        $ticket_id = (int)($_POST['ticket_id'] ?? 0);
        $assigned_to = (int)($_POST['assigned_to'] ?? 0);
        $remarks = trim($_POST['remarks'] ?? '');
        $accountID = $_SESSION['account_id'];

        if ($ticket_id <= 0 || $assigned_to <= 0) {
            $_SESSION['flash_error'] = 'Invalid input.'; $this->redirect('/admin/tickets/pending'); return;
        }

        $ticketModel = new Ticket();
        $ok = $ticketModel->approveAndAssign($ticket_id, $assigned_to, $accountID, $remarks);

    if ($ok) {
        require_once __DIR__ . '/../../Models/NotificationModel.php';

        $targets = $ticketModel->getApprovalNotificationTargets($ticket_id);
        $notificationModel = new NotificationModel();
        $base = $this->getLoggedUserContext()['base'];

        // 👤 Notify ticket owner
        if (!empty($targets['employee_account_id'])) {
            $notificationModel->create(
                (int)$targets['employee_account_id'],
                "Your ticket {$targets['ticket_number']} has been APPROVED.",
                'fa-check-circle',
                'success',
                $base . '/employee/tickets',
                $ticket_id
            );
        }

        // 👔 Notify department head
        if (!empty($targets['head_account_id'])) {
            $notificationModel->create(
                (int)$targets['head_account_id'],
                "Ticket {$targets['ticket_number']} has been APPROVED.",
                'fa-check-circle',
                'success',
                $base . '/head/dashboard',
                $ticket_id
            );
        }

        $_SESSION['flash_success'] = 'Ticket approved and assigned.';
    } else {
            $_SESSION['flash_error'] = 'Failed to approve ticket.';
        }

        $this->redirect('/admin/tickets/pending');
    }

    // Decline (POST)
    public function decline()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (empty($_SESSION['account_id']) || strtoupper($_SESSION['usertype'] ?? '') !== 'ADMIN') {
            $this->redirect('/login'); return;
        }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405); echo 'Method Not Allowed'; return;
        }

        // basic CSRF
        $posted = $_POST['csrf_token'] ?? '';
        if (empty($posted) || $posted !== ($_SESSION['csrf_token'] ?? '')) {
            $_SESSION['flash_error'] = 'Invalid CSRF token.'; $this->redirect('/admin/tickets/pending'); return;
        }

        $ticket_id = (int)($_POST['ticket_id'] ?? 0);
        $decline_reason = trim($_POST['decline_reason'] ?? '');
        $remarks = trim($_POST['remarks'] ?? '');
        $accountID = $_SESSION['account_id'];

        if ($ticket_id <= 0) {
            $_SESSION['flash_error'] = 'Invalid ticket id.'; $this->redirect('/admin/tickets/pending'); return;
        }

        $ticketModel = new Ticket();
        $ok = $ticketModel->declineTicket($ticket_id, $decline_reason, $remarks, $accountID);

    if ($ok) {
        require_once __DIR__ . '/../../Models/NotificationModel.php';

        $targets = $ticketModel->getApprovalNotificationTargets($ticket_id);
        $notificationModel = new NotificationModel();
        $base = $this->getLoggedUserContext()['base'];

        // 👤 Notify ticket owner
        if (!empty($targets['employee_account_id'])) {
            $notificationModel->create(
                (int)$targets['employee_account_id'],
                "Your ticket {$targets['ticket_number']} has been DECLINED.",
                'fa-times-circle',
                'danger',
                $base . '/employee/tickets',
                $ticket_id
            );
        }

        // 👔 Notify department head
        if (!empty($targets['head_account_id'])) {
            $notificationModel->create(
                (int)$targets['head_account_id'],
                "Ticket {$targets['ticket_number']} has been DECLINED.",
                'fa-times-circle',
                'danger',
                $base . '/head/dashboard',
                $ticket_id
            );
        }

        $_SESSION['flash_success'] = 'Ticket declined and closed.';
    } else {
            $_SESSION['flash_error'] = 'Failed to decline ticket.';
        }

        $this->redirect('/admin/tickets/pending');
    }
}
