<?php

require_once __DIR__ . '/../AuthController.php';
require_once __DIR__ . '/../../Models/employee/Employee.php';
require_once __DIR__ . '/../../Models/admin/Logger.php';
require_once __DIR__ . '/../../Helpers/Session.php';
require_once __DIR__ . '/../NotificationController.php';
require_once __DIR__ . '/../../Models/DashboardModel.php';
class EmployeeController extends AuthController
{
    public function dashboard()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        // auth check: ensure user logged in (adjust usertype if needed)
        if (empty($_SESSION['account_id'])) {
            $this->redirect('/login');
            return;
        }

        $accountId = (int) $_SESSION['account_id'];

        $model = new Employee(); // or new Employee() depending on your BaseModel constructor

        $user = $model->fetchUserDetails($accountId);
        // fallback if not found
        $loggedFirstname = $user['firstname'] ?? '';
        $loggedPosition  = $user['position']  ?? '';

        // if tblemployee has employee_id mapping
        $employeeId = isset($user['employee_id']) ? (int)$user['employee_id'] : null;

        // use model methods to get counts
        $assetsCount       = $model->countAssetsByEmployee($employeeId);
        $totalTickets      = $model->countTicketsByEmployee($employeeId);
        $pendingTickets    = $model->countTicketsByEmployee($employeeId, 'Pending');
        $inProgressTickets = $model->countTicketsByEmployee($employeeId, 'In Progress');
        $resolvedTickets   = $model->countTicketsByEmployee($employeeId, 'Resolved');

        // base URL from config or helper in AuthController
        $ctx = $this->getLoggedUserContext();
        $base = $ctx['base'];
        $loggedFirstname = $ctx['loggedFirstname'];
        $loggedPosition  = $ctx['loggedPosition'];

        // CSRF if needed
        if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
        $csrf_token = $_SESSION['csrf_token'];
        // 🔔 Notifications
        $notificationData = $this->loadNotifications();

        $count = $notificationData['count'];
        $notifications = $notificationData['notifications'];

        $dashboardModel = new DashboardModel();

        // 👤 INDIVIDUAL EMPLOYEE ID
        $employeeId = (int) $employeeId; // already defined earlier in your controller

        $rows = $dashboardModel->getEmployeeTicketResolutionTimes($employeeId);

        $resolutionLabels = [];
        $resolutionData   = [];

        foreach ($rows as $row) {
            $resolutionLabels[] = 'Ticket #' . $row['ticket_number'];
            $resolutionData[]   = (int)$row['resolution_hours'];
        }


        // pass to view
        require __DIR__ . '/../../Views/employee/dashboard/dashboard.php';
    }

    public function profile()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (empty($_SESSION['account_id']) || strtoupper($_SESSION['usertype'] ?? '') !== 'EMPLOYEE') {
            $_SESSION['loginMessage'] = 'Please log in as employee.';
            $this->redirect('/login');
            return;
        }

        $model = new Employee();
        $profile = $model->fetchProfileByAccountId((int)$_SESSION['account_id']) ?? [];

        $ctx = $this->getLoggedUserContext();
        $base = $ctx['base'];
        $loggedFirstname = $ctx['loggedFirstname'];
        $loggedPosition  = $ctx['loggedPosition'];

        if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
        $csrf_token = $_SESSION['csrf_token'];

        $notificationData = $this->loadNotifications();
        $count = $notificationData['count'];
        $notifications = $notificationData['notifications'];

        require __DIR__ . '/../../Views/employee/profile/profile.php';
    }

    
}