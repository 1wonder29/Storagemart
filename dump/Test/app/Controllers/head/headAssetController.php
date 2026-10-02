<?php

require_once __DIR__ . '/../AuthController.php';
require_once __DIR__ . '/../../Models/employee/Asset.php';
require_once __DIR__ . '/../../Models/employee/Employee.php';
require_once __DIR__ . '/../../Helpers/Session.php';

class HeadAssetController extends AuthController
{
    /**
     * GET /head/assets
     * Displays the logged-in HEAD’s assigned assets
     */
    public function asset()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        // Must be logged in
        if (empty($_SESSION['account_id'])) {
            $this->redirect('/login');
            return;
        }

        $accountId = (int)$_SESSION['account_id'];

        // Load employee info
        $employeeModel = new Employee();
        $user = $employeeModel->fetchUserDetails($accountId);

        if (!$user) {
            $_SESSION['flash_error'] = 'User profile not found.';
            $this->redirect('/login');
            return;
        }

        // HEAD ONLY
        if (strtoupper($user['usertype'] ?? '') !== 'HEAD') {
            http_response_code(403);
            exit('Unauthorized');
        }

        // Employee ID is REQUIRED
        $employeeId = (int)($user['employee_id'] ?? 0);
        if ($employeeId <= 0) {
            $_SESSION['flash_error'] = 'Employee profile not found.';
            $this->redirect('/head/dashboard');
            return;
        }

        // Fetch HEAD's own assets
        $assetModel = new Asset();
        $assets = $assetModel->fetchAssetsByEmployee($employeeId);
        $employee_id = $employeeId;

        // Layout context
        $ctx = $this->getLoggedUserContext();
        $base = $ctx['base'];
        $loggedFirstname = $ctx['loggedFirstname'];
        $loggedPosition  = $ctx['loggedPosition'];

        // Sidebar highlight
        $activePage = 'assets';

        // Notifications
        $notificationData = $this->loadNotifications();
        $count = $notificationData['count'];
        $notifications = $notificationData['notifications'];

        // Render view
        require __DIR__ . '/../../Views/head/asset/asset.php';
    }
}
