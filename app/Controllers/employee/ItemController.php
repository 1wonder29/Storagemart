<?php
require_once __DIR__ . '/../AuthController.php';
require_once __DIR__ . '/../../Models/employee/Employee.php';
require_once __DIR__ . '/../../Models/hr/UniformReportModel.php';
require_once __DIR__ . '/../../Models/NotificationModel.php';
require_once __DIR__ . '/../../Helpers/ActivityLogger.php';

/**
 * Items issued by HR (uniforms, ID badges, ...) and lost/damaged reports.
 */
class EmployeeItemController extends AuthController
{
    private function requireEmployeeId(): int
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (empty($_SESSION['account_id'])) {
            $this->redirect('/login');
        }

        $user = (new Employee())->fetchUserDetails((int) $_SESSION['account_id']);
        $employeeId = (int) ($user['employee_id'] ?? 0);
        if ($employeeId <= 0) {
            $_SESSION['flash_error'] = 'Employee profile not found.';
            $this->redirect('/employee/dashboard');
        }
        return $employeeId;
    }

    /** GET /employee/items */
    public function index()
    {
        $employeeId = $this->requireEmployeeId();

        $reportModel = new UniformReportModel();
        $items = $reportModel->getActiveIssuancesForEmployee($employeeId);
        $reports = $reportModel->getReportsForEmployee($employeeId);

        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
        }
        $csrf_token = $_SESSION['csrf_token'];

        $ctx = $this->getLoggedUserContext();
        $base = $ctx['base'];
        $loggedFirstname = $ctx['loggedFirstname'];
        $loggedPosition  = $ctx['loggedPosition'];
        $notificationData = $this->loadNotifications();
        $count = $notificationData['count'];
        $notifications = $notificationData['notifications'];

        require __DIR__ . '/../../Views/employee/items/items.php';
    }

    /** POST /employee/items/report */
    public function report()
    {
        $employeeId = $this->requireEmployeeId();

        if (empty($_POST['csrf_token']) || !hash_equals((string) ($_SESSION['csrf_token'] ?? ''), (string) $_POST['csrf_token'])) {
            $_SESSION['flash_error'] = 'Invalid form token. Please try again.';
            $this->redirect('/employee/items');
        }

        $assignmentId = (int) ($_POST['assignment_id'] ?? 0);
        $type = (string) ($_POST['report_type'] ?? '');
        $quantity = (int) ($_POST['quantity'] ?? 1);
        $description = trim((string) ($_POST['description'] ?? ''));

        if ($description === '') {
            $_SESSION['flash_error'] = 'Please describe what happened to the item.';
            $this->redirect('/employee/items');
        }

        $reportModel = new UniformReportModel();
        [$ok, $message, $reportId] = $reportModel->createReport($employeeId, $assignmentId, $type, $quantity, $description);

        if (!$ok) {
            $_SESSION['flash_error'] = $message;
            $this->redirect('/employee/items');
        }

        $assignment = $reportModel->getAssignmentById($assignmentId);
        $itemLabel = trim(($assignment['uniform_type'] ?? 'Item') . ' (' . ($assignment['size'] ?? '') . ')');
        $employeeName = (string) ($assignment['employee_name'] ?? 'An employee');
        $typeLabel = strtoupper($type) === 'LOST' ? 'lost' : 'damaged';

        $notificationModel = new NotificationModel();
        foreach ($reportModel->getHrAccountIds() as $hrAccountId) {
            $notificationModel->create(
                $hrAccountId,
                "{$employeeName} reported {$quantity} x {$itemLabel} as {$typeLabel}.",
                'fa-exclamation-triangle',
                'warning',
                '/hr/uniforms/reports',
                $reportId
            );
        }

        ActivityLogger::create('Employee - Items', (string) $reportId,
            "Reported {$quantity} x {$itemLabel} as {$typeLabel}",
            $_SESSION['username'] ?? 'Unknown', [
                'assignment_id' => $assignmentId,
                'report_type' => strtoupper($type),
                'quantity' => $quantity,
            ]);

        $_SESSION['flash_success'] = 'Your report was sent to HR. You will be notified once it is reviewed.';
        $this->redirect('/employee/items');
    }
}
