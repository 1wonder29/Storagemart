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

        $employee = $employeeModel->getEmployeeById((int)$user['employee_id']);
        $department = $employee['department'] ?? null;

        if (!$department) {
            $_SESSION['flash_error'] = 'Department not found.';
            $this->redirect('/hr/dashboard');
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
        if (!isset($_GET['ticket_id'])) {
            echo json_encode([]);
            return;
        }

        $ticketId = (int)$_GET['ticket_id'];

        $model = new EmployeeTicket();
        $history = $model->getTicketHistory($ticketId);

        header('Content-Type: application/json');
        echo json_encode($history);
    }

    public function ticketDetail()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (empty($_SESSION['account_id'])) {
            $this->redirect('/login');
            return;
        }

        $ticketId = (int)($_GET['id'] ?? 0);
        if ($ticketId === 0) {
            $_SESSION['flash_error'] = 'Invalid ticket ID.';
            $this->redirect('/hr/tickets');
            return;
        }

        $model = new EmployeeTicket();
        $ticket = $model->fetchTicketById($ticketId);

        if (!$ticket) {
            $_SESSION['flash_error'] = 'Ticket not found.';
            $this->redirect('/hr/tickets');
            return;
        }

        $ctx = $this->getLoggedUserContext();
        $base = $ctx['base'];
        $loggedFirstname = $ctx['loggedFirstname'];
        $loggedPosition = $ctx['loggedPosition'];

        $notificationData = $this->loadNotifications();
        $count = $notificationData['count'];
        $notifications = $notificationData['notifications'];

        $history = $model->getTicketHistory($ticketId);

        require __DIR__ . '/../../Views/hr/ticket/ticket-detail.php';
    }

    public function employeesByBranchAjax()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }

        $branchId = (int)($_GET['branch_id'] ?? 0);
        if ($branchId === 0) {
            echo json_encode(['error' => 'Branch ID is required']);
            return;
        }

        $empModel = new Employee();
        $employees = $empModel->listEmployeesByBranchId($branchId);

        header('Content-Type: application/json');
        echo json_encode($employees ?: []);
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

            // Verify ticket exists
            $ticketModel = new EmployeeTicket();
            $ticket = $ticketModel->fetchTicketById($ticketId);

            if (!$ticket) {
                echo json_encode(['success' => false, 'message' => 'Ticket not found']);
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

            $file = $_FILES['report_file'];
            $allowedMimes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'image/jpeg', 'image/png'];
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $fileMime = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);

            if (!in_array($fileMime, $allowedMimes)) {
                echo json_encode(['success' => false, 'message' => 'Invalid file type. Allowed: PDF, DOCX, DOC, JPG, PNG']);
                exit;
            }

            if ($file['size'] > 10485760) { // 10MB
                echo json_encode(['success' => false, 'message' => 'File size exceeds 10MB limit']);
                exit;
            }

            // Create uploads directory if it doesn't exist
            $uploadsDir = __DIR__ . '/../../uploads/technical_reports';
            if (!is_dir($uploadsDir)) {
                mkdir($uploadsDir, 0755, true);
            }

            // Generate unique filename
            $filename = 'ticket_' . $ticketId . '_' . time() . '_' . basename($file['name']);
            $filepath = $uploadsDir . '/' . $filename;

            // Move uploaded file
            if (!move_uploaded_file($file['tmp_name'], $filepath)) {
                echo json_encode(['success' => false, 'message' => 'Failed to save uploaded file']);
                exit;
            }

            // Store in database
            $relativeFilepath = 'uploads/technical_reports/' . $filename;
            $ticketModel->updateTechnicalReportPath($ticketId, $relativeFilepath);

            echo json_encode(['success' => true, 'message' => 'Technical report uploaded successfully']);
            exit;

        } catch (Exception $e) {
            error_log("Error uploading technical report: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'An error occurred during upload']);
            exit;
        }
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

        require_once __DIR__ . '/../../Models/hr/TicketRatingModel.php';
        $ratingModel = new HRTicketRatingModel();
        $alreadyRated = $ratingModel->hasRated($ticketId, $employeeId);

        if ($alreadyRated) {
            http_response_code(200);
            echo '<div class="alert alert-info"><i class="fas fa-check-circle"></i> You have already rated this ticket.</div>';
            return;
        }

        // Return just the form HTML for modal
        echo '
        <form id="rateTicketForm" method="POST">
            <input type="hidden" name="ticket_id" value="' . (int)$ticketId . '">
            
            <div class="form-group">
                <label class="font-weight-bold mb-3">How would you rate your experience?</label>
                <div style="display: flex; gap: 10px; justify-content: center; margin-bottom: 15px;">
                    <span class="star" data-value="1" style="font-size: 2rem; cursor: pointer; color: #ddd; transition: all 0.2s;" title="Poor">★</span>
                    <span class="star" data-value="2" style="font-size: 2rem; cursor: pointer; color: #ddd; transition: all 0.2s;" title="Fair">★</span>
                    <span class="star" data-value="3" style="font-size: 2rem; cursor: pointer; color: #ddd; transition: all 0.2s;" title="Good">★</span>
                    <span class="star" data-value="4" style="font-size: 2rem; cursor: pointer; color: #ddd; transition: all 0.2s;" title="Very Good">★</span>
                    <span class="star" data-value="5" style="font-size: 2rem; cursor: pointer; color: #ddd; transition: all 0.2s;" title="Excellent">★</span>
                </div>
                <p style="text-align: center; color: #999; font-size: 0.9rem;" id="ratingText">Click to select rating</p>
                <select name="rating" id="ratingSelect" style="display: none;" required></select>
            </div>

            <div class="form-group">
                <label class="font-weight-bold">Comments (optional)</label>
                <textarea name="comment" class="form-control" placeholder="Share your feedback..." style="min-height: 80px;"></textarea>
            </div>

            <div class="text-right">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-warning">
                    <i class="fas fa-paper-plane"></i> Submit Rating
                </button>
            </div>
        </form>

        <script>
            $(".star").on("click", function() {
                const rating = $(this).data("value");
                $("#ratingSelect").val(rating);
                $(".star").css("color", "#ddd");
                $(this).prevAll(".star").andSelf().css("color", "#ffc107");
                $("#ratingText").text(rating + " star" + (rating > 1 ? "s" : ""));
            });
        </script>
        ';
    }

    public function storeRating()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        header('Content-Type: application/json');

        require_once __DIR__ . '/../../Models/employee/Employee.php';
        require_once __DIR__ . '/../../Models/hr/TicketRatingModel.php';
        require_once __DIR__ . '/../../Models/employee/Ticket.php';

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

        $ratingModel = new HRTicketRatingModel();

        // prevent double rating
        if ($ratingModel->hasRated($ticketId, $employeeId)) {
            echo json_encode(['success' => false, 'message' => 'You already rated this ticket.']);
            exit;
        }

        // validate rating
        $rating = (int)($_POST['rating'] ?? 0);
        if ($rating < 1 || $rating > 5) {
            echo json_encode(['success' => false, 'message' => 'Invalid rating value.']);
            exit;
        }

        // save rating
        try {
            $ratingModel->create(
                $ticketId,
                $employeeId,
                $itId,
                $rating,
                $_POST['comment'] ?? ''
            );
            echo json_encode(['success' => true, 'message' => 'Thank you for rating IT support!']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error saving rating: ' . $e->getMessage()]);
        }
        exit;
    }
}
?>
