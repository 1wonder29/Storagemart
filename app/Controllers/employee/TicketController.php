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

        /* ✅ GET EMPLOYEE DETAILS FIRST */
        $employee = $employeeModel->getEmployeeById($employeeId);
        $department = $employee['department'] ?? null;
        $employeeBranchId = $employee['branch_id'] ?? 0;

        if (!$department) {
            $_SESSION['flash_error'] = "Unable to determine department.";
            $this->redirect('/employee/assets');
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
            'inventory_id'    => !empty($_POST['inventory_id']) ? (int)$_POST['inventory_id'] : null,
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
        $filerName = $employeeModel->formatDisplayName($employee);

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
                'New Ticket Filed by ' . $filerName,
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

        $ticketStats = [];
        foreach ($tickets as $t) {
            $s = (string) ($t['status'] ?? 'Unknown');
            $ticketStats[$s] = ($ticketStats[$s] ?? 0) + 1;
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
            $this->redirect('/employee/tickets');
            return;
        }

        $employeeModel = new Employee();
        $employeeId = $employeeModel->getEmployeeIdByAccountId((int) $_SESSION['account_id']);
        if (!$employeeId) {
            $_SESSION['flash_error'] = 'No employee record linked to your account.';
            $this->redirect('/employee/tickets');
            return;
        }

        $ticketModel = new EmployeeTicket();
        $ticket = $ticketModel->fetchTicketById($ticketId);

        if (!$ticket || (int) ($ticket['employee_id'] ?? 0) !== (int) $employeeId) {
            $_SESSION['flash_error'] = 'Ticket not found.';
            $this->redirect('/employee/tickets');
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

        require __DIR__ . '/../../Views/employee/ticket/ticket-detail.php';
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

public function downloadTechnicalRecord()
{
    if (session_status() === PHP_SESSION_NONE) session_start();

    // Authenticate user
    if (empty($_SESSION['account_id'])) {
        http_response_code(401);
        echo 'Unauthorized';
        exit;
    }

    // Validate ticket ID
    $ticketId = (int) ($_GET['id'] ?? 0);
    if (!$ticketId) {
        http_response_code(400);
        echo 'Invalid ticket ID';
        exit;
    }

    // Get employee ID from account
    $employeeModel = new Employee();
    $employeeId = $employeeModel->getEmployeeIdByAccountId((int)$_SESSION['account_id']);

    if (!$employeeId) {
        http_response_code(401);
        echo 'Employee not found';
        exit;
    }

    // Load PDF generation service
    require_once __DIR__ . '/../../Services/PdfGeneratorService.php';
    $pdfService = new PdfGeneratorService();

    // Generate technical record on-demand
    $result = $pdfService->generateTechnicalRecordDocx($ticketId, $employeeId);

    if (!$result || !$result['success']) {
        http_response_code(404);
        echo 'Unable to generate technical record. Please ensure the ticket is resolved.';
        exit;
    }

    // Stream file download
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

        // Verify ticket exists and belongs to employee
        $ticketModel = new EmployeeTicket();
        $ticket = $ticketModel->fetchTicketById($ticketId);

        if (!$ticket) {
            echo json_encode(['success' => false, 'message' => 'Ticket not found']);
            exit;
        }

        if ((int)$ticket['employee_id'] !== (int)$employeeId) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'You do not have permission to upload for this ticket']);
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

}