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

        $rows = $dashboardModel->getItTicketResolutionTimes($employeeId);

        $resolutionLabels = [];
        $resolutionData   = [];

        foreach ($rows as $row) {
            $resolutionLabels[] = 'Ticket #' . $row['ticket_number'];
            $resolutionData[]   = (int)$row['resolution_hours'];
        }
        require_once __DIR__ . '/../../Views/it/dashboard/dashboard.php';
    }

}