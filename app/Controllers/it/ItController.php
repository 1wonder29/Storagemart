<?php
    require_once __DIR__ . '/../AuthController.php';
    require_once __DIR__ . '/../../Models/it/IT.php';
    require_once __DIR__ . '/../../Models/it/ItAssetModel.php';
    require_once __DIR__ . '/../../Helpers/Session.php';
require_once __DIR__ . '/../../Models/DashboardModel.php';
class itController extends AuthController{

    public function dashboard()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (empty($_SESSION['account_id'])) {
            $this->redirect('/login');
            return;
        }

        $accountId = (int) $_SESSION['account_id'];

        $itModel = new IT();

        // 🔑 THIS WAS MISSING
        $employeeId = $itModel->getEmployeeIdByAccountId($accountId);

    if ($employeeId === null) {
        $assignedCount  = 0;
        $pendingTickets = 0;
        $resolveTickets = 0;
        $myAssets = 0;
        $myTickets= 0;
        $myOngoingTickets= 0;
    } else {
        $assignedCount  = $itModel->countTicketsAssignedToMe($employeeId);
        $pendingTickets = $itModel->countTicketsAssignedToMe($employeeId, 'In Progress');
        $resolveTickets = $itModel->countTicketsAssignedToMe($employeeId, 'Resolved');
        $myAssets  = $itModel->countAssetbyEmployeeId($employeeId);
        $myTickets = $itModel->countTicketByEmployeeId($employeeId);
        $myOngoingTickets = $itModel->countTicketByEmployeeId($employeeId, 'In Progress');
    }


        $ctx = $this->getLoggedUserContext();
        $base = $ctx['base'];
        $loggedFirstname = $ctx['loggedFirstname'];
        $loggedPosition  = $ctx['loggedPosition'];
        $notificationData = $this->loadNotifications();

        $count = $notificationData['count'];
        $notifications = $notificationData['notifications'];
        $dashboardModel = new DashboardModel();
        $employeeId = (int) $employeeId; // already defined earlier in your controller

        $rows = ($employeeId !== null && (int) $employeeId > 0)
            ? $dashboardModel->getItTicketResolutionTimes((int) $employeeId)
            : [];

        $resolutionLabels = [];
        $resolutionData   = [];

        foreach ($rows as $row) {
            $resolutionLabels[] = (string) $row['ticket_number'];
            $resolutionData[]   = round(((float) $row['resolution_hours']) / 24, 1);
        }
        require_once __DIR__ . '/../../Views/it/dashboard/dashboard.php';
    }

    public function profile()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (empty($_SESSION['account_id']) || strtoupper($_SESSION['usertype'] ?? '') !== 'IT') {
            $_SESSION['loginMessage'] = 'Please log in as IT user.';
            $this->redirect('/login');
            return;
        }

        $itModel = new IT();
        $profile = $itModel->fetchProfileByAccountId((int)$_SESSION['account_id']) ?? [];

        $ctx = $this->getLoggedUserContext();
        $base = $ctx['base'];
        $loggedFirstname = $ctx['loggedFirstname'];
        $loggedPosition  = $ctx['loggedPosition'];

        $notificationData = $this->loadNotifications();
        $count = $notificationData['count'];
        $notifications = $notificationData['notifications'];

        require __DIR__ . '/../../Views/it/profile/profile.php';
    }

    public function viewUploads()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (empty($_SESSION['account_id']) || strtoupper($_SESSION['usertype'] ?? '') !== 'IT') {
            $_SESSION['loginMessage'] = 'Please log in as IT user.';
            $this->redirect('/login');
            return;
        }

        $itModel = new IT();
        $uploadsByDate = $itModel->getUploadsByDate();

        $ctx = $this->getLoggedUserContext();
        $base = $ctx['base'];
        $loggedFirstname = $ctx['loggedFirstname'];
        $loggedPosition  = $ctx['loggedPosition'];

        $notificationData = $this->loadNotifications();
        $count = $notificationData['count'];
        $notifications = $notificationData['notifications'];

        require __DIR__ . '/../../Views/it/uploads.php';
    }

    public function ratings()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['account_id']) || strtoupper($_SESSION['usertype'] ?? '') !== 'IT') {
            $this->redirect('/login');
            return;
        }

        require_once __DIR__ . '/../../Models/it/IT.php';
        require_once __DIR__ . '/../../Models/it/ItRatingsModel.php';

        $itModel = new IT();
        $itId = $itModel->getEmployeeIdByAccountId((int)$_SESSION['account_id']);

        if (!$itId) {
            $_SESSION['flash_error'] = 'Unable to load your ratings.';
            $this->redirect('/it/dashboard');
            return;
        }

        $ratingsModel = new ItRatingsModel();
        $ratings = $ratingsModel->getRatingsForItPerson($itId);
        $stats = $ratingsModel->getStatsForItPerson($itId);
        $distribution = $ratingsModel->getRatingDistribution($itId);

        $ctx = $this->getLoggedUserContext();
        $base = $ctx['base'];
        $loggedFirstname = $ctx['loggedFirstname'];
        $loggedPosition = $ctx['loggedPosition'];

        $notificationData = $this->loadNotifications();
        $count = $notificationData['count'];
        $notifications = $notificationData['notifications'];

        require __DIR__ . '/../../Views/it/ratings-dashboard.php';
    }

}