<?php
// app/Controllers/employee/TicketController.php

require_once __DIR__ . '/../AuthController.php';
require_once __DIR__ . '/../../Models/it/IT.php';
require_once __DIR__ . '/../../Models/it/ItTicketModel.php';
require_once __DIR__ . '/../../Models/TicketCancelModel.php';
require_once __DIR__ . '/../../Helpers/Session.php';
require_once __DIR__ . '/../../Models/admin/Logger.php';
require_once __DIR__ . '/../../Helpers/TicketStatus.php';
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

        $itModel = new IT();
        $model = new ItTicketModel();
        $inventory = $model->getInventoryDetailsByInventoryId($inventory_id);

        // If no inventory selected, populate with logged-in user's employee details
        if (empty($inventory) && !empty($_SESSION['account_id'])) {
            $employeeId = $itModel->getEmployeeIdByAccountId((int)$_SESSION['account_id']);
            if ($employeeId) {
                $empData = $itModel->getEmployeeById($employeeId);
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
            'inventory_id'    => !empty($_POST['inventory_id']) ? (int)$_POST['inventory_id'] : null,
            'branch_id'       => (int)($_POST['branch_id'] ?? 0),
            'department'      => trim($_POST['department'] ?? ''),
            'category'        => trim($_POST['category'] ?? ''),
            'concern_details' => trim($_POST['concern_details'] ?? ''),
            'priority'        => $priority,
            'created_by'      => $accountId
        ]);

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

        // 🔔 Get recipients with their usertype for role-based redirect
        $recipients = $notificationModel->getTicketRecipientsWithType($department);

        // 🔕 Do not notify the ticket filer
        $currentAccountId = (int) $_SESSION['account_id'];

        foreach ($recipients as $recipient) {
            $receiverAccountId = (int) ($recipient['account_id'] ?? 0);
            $receiverType = strtoupper($recipient['usertype'] ?? '');

            if ($receiverAccountId === $currentAccountId) {
                continue;
            }

            $notificationModel->create(
                $receiverAccountId,
                'New IT Ticket Filed',
                'fa-ticket-alt',
                'primary',
                $notificationModel->getTicketViewUrlForRole($receiverType, (int) $ticketId),
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
            $summaryTicketStats = (new ItTicketModel())->getTicketStatusCounts();
        } else {
            $ticketModel = new ItTicketModel();
            $tickets = $ticketModel->fetchAllFiledTickets();
            $summaryTicketStats = $ticketModel->getTicketStatusCounts();
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

    public function view()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (empty($_SESSION['account_id'])) {
            $this->redirect('/login');
            return;
        }

        $ticketId = (int) ($_GET['id'] ?? 0);
        if ($ticketId <= 0) {
            $_SESSION['flash_error'] = 'Invalid ticket ID.';
            $this->redirect('/it/tickets');
            return;
        }

        $itModel = new IT();
        $employeeId = $itModel->getEmployeeIdByAccountId((int) $_SESSION['account_id']);
        if (!$employeeId) {
            $_SESSION['flash_error'] = 'No employee record linked to your account.';
            $this->redirect('/it/tickets');
            return;
        }

        require_once __DIR__ . '/../../Models/employee/Ticket.php';
        $ticketModel = new EmployeeTicket();
        $ticket = $ticketModel->fetchTicketById($ticketId);

        if (!$ticket) {
            $_SESSION['flash_error'] = 'Ticket not found.';
            $redirectTo = ($_GET['from'] ?? '') === 'in_progress' ? '/it/tickets/in_progress' : '/it/tickets';
            $this->redirect($redirectTo);
            return;
        }

        $history = $ticketModel->getTicketHistory($ticketId);

        $ctx = $this->getLoggedUserContext();
        $loggedFirstname = $ctx['loggedFirstname'] ?? '';
        $loggedPosition = $ctx['loggedPosition'] ?? '';
        $notificationData = $this->loadNotifications();
        $count = $notificationData['count'];
        $notifications = $notificationData['notifications'];
        $activePage = 'tickets';
        $from = $_GET['from'] ?? '';
        $backUrl = match ($from) {
            'open'        => '/it/tickets/open',
            'in_progress' => '/it/tickets/in_progress',
            'pending'     => '/it/tickets/pending',
            'resolve'     => '/it/tickets/resolve',
            'closed'      => '/it/tickets/closed',
            'cancelled'   => '/it/tickets/cancelled',
            default       => '/it/tickets',
        };

        require __DIR__ . '/../../Views/it/ticket/ticket-detail.php';
    }

    public function fetchHistory()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        header('Content-Type: application/json; charset=utf-8');

        // Authenticate before returning any data
        if (empty($_SESSION['account_id'])) {
            http_response_code(401);
            echo json_encode([]);
            return;
        }

        if (!isset($_GET['ticket_id'])) {
            echo json_encode([]);
            return;
        }

        $ticketId = (int)$_GET['ticket_id'];

        $model = new ItTicketModel();
        $history = $model->getTicketHistory($ticketId);

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

        // All in-progress tickets (IT may act only on tickets assigned to them).
        $tickets = $ticketModel->getInProgressTickets();
        $summaryTicketStats = $ticketModel->getTicketStatusCounts();
        $ctx = $this->getLoggedUserContext();
        $base = $ctx['base'];
        $loggedFirstname = $ctx['loggedFirstname'];
        $loggedPosition = $ctx['loggedPosition'];
                $notificationData = $this->loadNotifications();

        $count = $notificationData['count'];
        $notifications = $notificationData['notifications'];
        $ticketMode = 'in_progress';

        if (!empty($_GET['realtime_rows'])) {
            header('Content-Type: text/html; charset=utf-8');
            require __DIR__ . '/../../Views/partials/it/in_progress_ticket_rows.php';
            exit;
        }

        require __DIR__ . '/../../Views/it/ticket/in_progress.php';
    }

    public function open()
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
        $tickets = $ticketModel->getOpenTickets();
        $summaryTicketStats = $ticketModel->getTicketStatusCounts();

        $ctx = $this->getLoggedUserContext();
        $base = $ctx['base'];
        $loggedFirstname = $ctx['loggedFirstname'];
        $loggedPosition = $ctx['loggedPosition'];
        $notificationData = $this->loadNotifications();

        $count = $notificationData['count'];
        $notifications = $notificationData['notifications'];
        $ticketMode = 'open';

        if (!empty($_GET['realtime_rows'])) {
            header('Content-Type: text/html; charset=utf-8');
            require __DIR__ . '/../../Views/partials/it/in_progress_ticket_rows.php';
            exit;
        }

        require __DIR__ . '/../../Views/it/ticket/in_progress.php';
    }

    public function closed()
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
        $tickets = $ticketModel->getClosedTickets();
        $summaryTicketStats = $ticketModel->getTicketStatusCounts();

        $ctx = $this->getLoggedUserContext();
        $base = $ctx['base'];
        $loggedFirstname = $ctx['loggedFirstname'];
        $loggedPosition = $ctx['loggedPosition'];
        $notificationData = $this->loadNotifications();

        $count = $notificationData['count'];
        $notifications = $notificationData['notifications'];
        $ticketMode = 'closed';

        if (!empty($_GET['realtime_rows'])) {
            header('Content-Type: text/html; charset=utf-8');
            require __DIR__ . '/../../Views/partials/it/in_progress_ticket_rows.php';
            exit;
        }

        require __DIR__ . '/../../Views/it/ticket/in_progress.php';
    }

    public function pending()
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
        $tickets = $ticketModel->getPendingTickets();
        $summaryTicketStats = $ticketModel->getTicketStatusCounts();

        $ctx = $this->getLoggedUserContext();
        $base = $ctx['base'];
        $loggedFirstname = $ctx['loggedFirstname'];
        $loggedPosition = $ctx['loggedPosition'];
        $notificationData = $this->loadNotifications();

        $count = $notificationData['count'];
        $notifications = $notificationData['notifications'];
        $ticketMode = 'pending';

        if (!empty($_GET['realtime_rows'])) {
            header('Content-Type: text/html; charset=utf-8');
            require __DIR__ . '/../../Views/partials/it/in_progress_ticket_rows.php';
            exit;
        }

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

        $existingTicket = $ticketModel->fetchTicketById($ticketId);
        if (!$existingTicket) {
            $_SESSION['flash_error'] = 'Ticket not found.';
            $this->redirect('/it/tickets/in_progress');
            return;
        }
        $oldStatus = (string) ($existingTicket['status'] ?? 'In Progress');

        // ✅ action → status
        switch ($action) {
            case 'Resolve': $status = 'Resolved'; break;
            case 'In Progress': $status = 'In Progress'; break;
            case 'Pending':
            case 'On Hold': $status = TicketStatus::PENDING; break;
            case 'Open': $status = TicketStatus::OPEN; break;
            default:        $status = 'In Progress';
        }
        // 🔔 Notify ticket owner (employee) when resolved
        if ($status === 'Resolved') {

            // This MUST return the ACCOUNT ID of the ticket owner
            $receiverAccountId = $ticketModel->getEmployeeAccountIdByTicketId($ticketId);

            if ($receiverAccountId) {
                require_once __DIR__ . '/../../Models/NotificationModel.php';

                $notificationModel = new NotificationModel();
                $receiverUsertype = $notificationModel->getAccountUsertype((int) $receiverAccountId);
                $notificationModel->create(
                    (int) $receiverAccountId,
                    'Your ticket has been resolved. Click to view details.',
                    'fa-check-circle',
                    'success',
                    $notificationModel->getTicketViewUrlForRole($receiverUsertype, $ticketId),
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
            'old_status'      => $oldStatus,
            'new_status'      => $status,
            'performed_by'    => $_SESSION['account_id'],
            'performed_role'  => 'IT Staff'
        ]);

        // =============================
        // 4️⃣ Generate and store resolution document
        // =============================
        if ($status === 'Resolved') {
            try {
                require_once __DIR__ . '/../../Services/PdfGeneratorService.php';
                $pdfService = new PdfGeneratorService();
                $pdfResult = $pdfService->generateResolutionPdf(
                    $ticketId,
                    (int) $_SESSION['account_id'],
                    'IT'
                );

                if ($pdfResult && !empty($pdfResult['success'])) {
                    $ticketModel->insertTicketPdf(
                        $ticketId,
                        $pdfResult['filename'],
                        $pdfResult['path'],
                        (int) $_SESSION['account_id'],
                        'IT',
                        $pdfResult['file_size'] ?? null
                    );
                }
            } catch (\Exception $e) {
                error_log('Resolution document generation failed for ticket ' . $ticketId . ': ' . $e->getMessage());
            }
        }

        // =============================
        // 5️⃣ Handle technical report upload if provided
        // =============================
        if ($status === 'Resolved' && isset($_FILES['report_file']) && $_FILES['report_file']['error'] === UPLOAD_ERR_OK) {
            try {
                $uploadedFile = $_FILES['report_file'];
                $allowedExtensions = ['pdf', 'docx', 'doc', 'jpg', 'jpeg', 'png'];
                $maxFileSize = 10 * 1024 * 1024;

                // Get file extension
                $originalName = basename($uploadedFile['name']);
                $fileExt = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

                // Validate file extension
                if (in_array($fileExt, $allowedExtensions)) {
                    // Validate file size
                    if ($uploadedFile['size'] <= $maxFileSize && $uploadedFile['size'] > 0) {
                        // Get ticket number
                        $ticketNumber = (string) ($existingTicket['ticket_number'] ?? "TICKET_{$ticketId}");
                        
                        // Generate filename
                        $sanitizedTicketNumber = preg_replace('/[^A-Za-z0-9_-]/', '', $ticketNumber);
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
                        if (move_uploaded_file($uploadedFile['tmp_name'], $uploadPath)) {
                            // Record in database
                            require_once __DIR__ . '/../../Models/employee/UploadModel.php';
                            $uploadModel = new TicketUploadModel();

                            $fileMime = '';
                            if (function_exists('finfo_file')) {
                                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                                $fileMime = finfo_file($finfo, $uploadPath);
                                finfo_close($finfo);
                            }

                            $uploadModel->recordUpload(
                                $ticketId,
                                $employeeId,
                                $originalName,
                                $storedFilename,
                                filesize($uploadPath),
                                $fileMime ?: 'application/octet-stream'
                            );
                        }
                    }
                }
            } catch (\Exception $e) {
                error_log("Error uploading technical report: " . $e->getMessage());
                // Don't block ticket update if upload fails
            }
        }

        $_SESSION['flash_success'] = "Ticket marked as {$status}.";
        $this->redirect('/it/tickets/in_progress');
    }


    public function cancelled()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['account_id'])) {
            $this->redirect('/login');
            return;
        }

        $accountId = (int) $_SESSION['account_id'];
        $itModel = new IT();
        $employeeId = (int) ($itModel->getEmployeeIdByAccountId($accountId) ?: 0);

        $cancelModel = new TicketCancelModel();
        $tickets = $cancelModel->getCancelledTicketsForIt($employeeId, $accountId);
        $summaryTicketStats = (new ItTicketModel())->getTicketStatusCounts();

        $ctx = $this->getLoggedUserContext();
        $base = $ctx['base'];
        $loggedFirstname = $ctx['loggedFirstname'];
        $loggedPosition = $ctx['loggedPosition'];
        $notificationData = $this->loadNotifications();

        $count = $notificationData['count'];
        $notifications = $notificationData['notifications'];

        require __DIR__ . '/../../Views/it/ticket/cancelled.php';
    }

    public function resolve(){
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (empty($_SESSION['account_id'])) {
            $this->redirect('/login');
            return;
        }

        $technicalModel = new ItTicketModel();
        $tickets = $technicalModel->getResolvedTechnicalTickets();
        $summaryTicketStats = $technicalModel->getTicketStatusCounts();

        $ctx = $this->getLoggedUserContext();
        $base = $ctx['base'];
        $loggedFirstname = $ctx['loggedFirstname'];
        $loggedPosition  = $ctx['loggedPosition'];
        $notificationData = $this->loadNotifications();

        $count = $notificationData['count'];
        $notifications = $notificationData['notifications'];

        require __DIR__ . '/../../Views/it/ticket/resolve.php';
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

            // Verify ticket exists and is assigned to this IT person
            $ticketModel = new ItTicketModel();
            $ticket = $ticketModel->fetchTicketById($ticketId);

            if (!$ticket) {
                echo json_encode(['success' => false, 'message' => 'Ticket not found']);
                exit;
            }

            // Check if IT person is assigned to this ticket
            $assignedTo = $ticketModel->getAssignedTo($ticketId);
            if ($assignedTo === null || (int)$assignedTo !== (int)$employeeId) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'You are not assigned to this ticket']);
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
            } catch (\Exception $dbErr) {
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

        } catch (\Exception $e) {
            error_log("Exception in uploadTechnicalReport: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
            exit;
        }
    }
}
