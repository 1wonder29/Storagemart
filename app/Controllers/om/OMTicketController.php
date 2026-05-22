<?php

require_once __DIR__ . '/../AuthController.php';
require_once __DIR__ . '/../../Models/employee/Employee.php';
require_once __DIR__ . '/../../Models/employee/Ticket.php';
require_once __DIR__ . '/../../Helpers/Session.php';
require_once __DIR__ . '/../../Models/admin/Logger.php';
require_once __DIR__ . '/../../Models/NotificationModel.php';

class OMTicketController extends AuthController
{
    protected $employeeModel;

    public function __construct()
    {
        parent::__construct();
        $this->employeeModel = new Employee();
    }

    /**
     * Check if user is OM
     */
    protected function requireOM()
    {
        if (empty($_SESSION['account_id'])) {
            $_SESSION['loginMessage'] = 'Please log in to continue.';
            $this->redirect('/login');
            return false;
        }

        $user = $this->employeeModel->fetchUserDetails($_SESSION['account_id']);
        if (!$user || strtoupper($user['usertype'] ?? '') !== 'OM') {
            http_response_code(403);
            exit('Unauthorized: This area requires OM access.');
        }

        return $user;
    }

    public function index()
    {
        $this->requireOM();

        $accountId = (int) $_SESSION['account_id'];
        $ticketModel = new EmployeeTicket();
        $tickets = $ticketModel->getTicketsByCreatedBy($accountId);

        $ticketStats = [];
        foreach ($tickets as $t) {
            $s = (string) ($t['status'] ?? 'Unknown');
            $ticketStats[$s] = ($ticketStats[$s] ?? 0) + 1;
        }

        $ctx = $this->getLoggedUserContext();
        $ctx['loggedLastname'] = $ctx['loggedLastname'] ?? '';

        $notificationData = $this->loadNotifications();
        $count = $notificationData['count'];
        $notifications = $notificationData['notifications'];

        $activePage = 'tickets';

        require __DIR__ . '/../../Views/om/ticket/ticket.php';
    }

    public function create()
    {
        $this->requireOM();

        $employeeModel = new Employee();
        $employees = [];
        // Get all employees (can be customized per branch/department)
        $stmt = $employeeModel->getPDO()->query("SELECT employee_id, firstname, lastname FROM tblemployee ORDER BY lastname, firstname");
        if ($stmt) {
            $employees = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        }

        // Get current logged-in user's employee ID for default
        $accountId = (int) $_SESSION['account_id'];
        $defaultEmployeeId = $employeeModel->getEmployeeIdByAccountId($accountId);

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

        require __DIR__ . '/../../Views/om/ticket/create.php';
    }

    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit('Invalid method.');
        }
        $this->requireOM();

        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
            $_SESSION['flash_error'] = 'Invalid form token.';
            $this->redirect('/om/tickets/create');
            return;
        }

        $accountId = (int) ($_SESSION['account_id'] ?? 0);
        $employeeModel = new Employee();
        $omEmployeeId = $employeeModel->getEmployeeIdByAccountId($accountId);

        if (!$omEmployeeId) {
            $_SESSION['flash_error'] = 'Unable to determine your employee record.';
            $this->redirect('/om/tickets/create');
            return;
        }

        $targetEmployeeId = (int) ($_POST['employee_id'] ?? 0);
        if ($targetEmployeeId <= 0) {
            $_SESSION['flash_error'] = 'Please select an employee.';
            $this->redirect('/om/tickets/create');
            return;
        }

        $empRow = $employeeModel->getEmployeeById($targetEmployeeId);
        if (!$empRow) {
            $_SESSION['flash_error'] = 'Invalid employee selected.';
            $this->redirect('/om/tickets/create');
            return;
        }

        $department = trim((string) ($_POST['department'] ?? $empRow['department'] ?? ''));
        if ($department === '') {
            $_SESSION['flash_error'] = 'Department is required.';
            $this->redirect('/om/tickets/create');
            return;
        }

        $concern = trim((string) ($_POST['concern_details'] ?? ''));
        if ($concern === '') {
            $_SESSION['flash_error'] = 'Ticket description is required.';
            $this->redirect('/om/tickets/create');
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
            'branch_id' => (int) ($empRow['branch_id'] ?? 0),
            'department' => $department,
            'category' => trim((string) ($_POST['category'] ?? '')),
            'concern_details' => $concern,
            'priority' => $priority,
            'created_by' => $accountId,
        ]);

        $notificationModel = new NotificationModel();
        $recipients = $notificationModel->getTicketRecipientsWithType($department);
        $currentAccountId = $accountId;

        foreach ($recipients as $recipient) {
            $receiverAccountId = (int) $recipient['account_id'];
            $receiverType = strtoupper($recipient['usertype'] ?? '');
            if ($receiverAccountId === $currentAccountId) {
                continue;
            }
            if ($receiverType === 'ADMIN') {
                $actionUrl = '/admin/tickets';
            } elseif ($receiverType === 'HEAD') {
                $actionUrl = '/head/tickets';
            } else {
                $actionUrl = '/it/tickets';
            }
            $notificationModel->create(
                $receiverAccountId,
                'New Ticket Filed by OM',
                'fa-ticket-alt',
                'primary',
                $actionUrl,
                (int) $ticketId
            );
        }

        $ticket_number = $model->getTicketNumberById((int) $ticketId) ?? 'N/A';
        $logger = new Logger();
        $logger->log('Create', 'Ticket Management', (string) $ticketId, $_SESSION['username'] ?? 'Unknown');

        $_SESSION['flash_success'] = 'Ticket created successfully! Your Ticket Number: ' . $ticket_number;
        $this->redirect('/om/tickets');
    }

    public function view()
    {
        $this->requireOM();

        $ticketId = (int) ($_GET['id'] ?? 0);
        if ($ticketId <= 0) {
            $_SESSION['flash_error'] = 'Invalid ticket ID.';
            $this->redirect('/om/tickets');
            return;
        }

        $ticketModel = new EmployeeTicket();
        $ticket = $ticketModel->fetchTicketById($ticketId);

        if (!$ticket) {
            $_SESSION['flash_error'] = 'Ticket not found.';
            $this->redirect('/om/tickets');
            return;
        }

        $history = $ticketModel->getTicketHistory($ticketId);

        $ctx = $this->getLoggedUserContext();
        $ctx['loggedLastname'] = $ctx['loggedLastname'] ?? '';

        $notificationData = $this->loadNotifications();
        $count = $notificationData['count'];
        $notifications = $notificationData['notifications'];

        $activePage = 'tickets';

        require __DIR__ . '/../../Views/om/ticket/ticket-detail.php';
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

            $this->requireOM();

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

            $uploadId = $uploadModel->recordUpload(
                $ticketId,
                $employeeId,
                $originalName,
                $storedFilename,
                filesize($uploadPath),
                $fileMime ?: 'application/octet-stream'
            );

            if (!$uploadId) {
                @unlink($uploadPath);
                echo json_encode(['success' => false, 'message' => 'Failed to record upload']);
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
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Server error']);
            exit;
        }
    }

    public function rate()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        $this->requireOM();

        $ticketId = (int) ($_GET['id'] ?? 0);
        if (!$ticketId) {
            http_response_code(400);
            echo 'Invalid ticket.';
            return;
        }

        $employeeModel = new Employee();
        $omId = $employeeModel->getEmployeeIdByAccountId((int)$_SESSION['account_id']);

        require_once __DIR__ . '/../../Models/om/TicketRatingModel.php';
        $ratingModel = new OMTicketRatingModel();
        $alreadyRated = $ratingModel->hasRated($ticketId, $omId);

        // Debug: if already rated, log database row
        if ($alreadyRated) {
            try {
                $stmt = (new Employee())->getPDO()->prepare('SELECT * FROM ticket_ratings WHERE ticket_id = ? AND employee_id = ?');
                $stmt->execute([$ticketId, $omId]);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $logDir = __DIR__ . '/../../logs';
                if (!is_dir($logDir)) @mkdir($logDir, 0755, true);
                @file_put_contents($logDir . '/rating_debug.log', '[' . date('Y-m-d H:i:s') . "] OM alreadyRated row: " . json_encode($row) . "\n", FILE_APPEND);
            } catch (Exception $e) {
                @file_put_contents(__DIR__ . '/../../logs/rating_debug.log', '[' . date('Y-m-d H:i:s') . "] OM alreadyRated query failed: " . $e->getMessage() . "\n", FILE_APPEND);
            }
        }

        $base = rtrim(BASE_URL, '/');

        require __DIR__ . '/../../Views/om/ticket/rate.php';
    }

    public function storeRating()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        $this->requireOM();

        require_once __DIR__ . '/../../Models/om/TicketRatingModel.php';

        header('Content-Type: application/json');

        $accountId = (int) ($_SESSION['account_id'] ?? 0);
        $ticketId  = (int) ($_POST['ticket_id'] ?? 0);

        if (!$ticketId) {
            echo json_encode(['success' => false, 'message' => 'Invalid ticket.']);
            exit;
        }

        $employeeModel = new Employee();
        $omId = $employeeModel->getEmployeeIdByAccountId($accountId);

        if (!$omId) {
            echo json_encode(['success' => false, 'message' => 'OM record not found.']);
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

        $ratingModel = new OMTicketRatingModel();

        if ($ratingModel->hasRated($ticketId, $omId)) {
            echo json_encode(['success' => false, 'message' => 'You already rated this ticket.']);
            exit;
        }

        $ratingModel->create(
            $ticketId,
            $omId,
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

        $this->requireOM();

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
        $omId = $employeeModel->getEmployeeIdByAccountId((int)$_SESSION['account_id']);

        if (!$omId) {
            http_response_code(401);
            echo 'OM record not found';
            exit;
        }

        require_once __DIR__ . '/../../Services/PdfGeneratorService.php';
        $pdfService = new PdfGeneratorService();

        // OM should be allowed to generate records for tickets they manage
        $result = $pdfService->generateTechnicalRecordDocx($ticketId, $omId, true);

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
