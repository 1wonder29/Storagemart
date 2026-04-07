<?php
require_once __DIR__ . '/../AuthController.php';
require_once __DIR__ . '/../../Models/employee/Asset.php';
require_once __DIR__ . '/../../Models/employee/Employee.php';
require_once __DIR__ . '/../../Helpers/Session.php';

class AssetController extends AuthController
{
    /**
     * GET  /employee/assets
     * Displays the employee’s assigned assets list.
     */
    public function asset()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        // Must be logged in
        if (empty($_SESSION['account_id'])) {
            $this->redirect('/login');
            return;
        }

        $accountId = (int) $_SESSION['account_id'];

        // Load employee information
        $employeeModel = new Employee();
        $user = $employeeModel->fetchUserDetails($accountId);

        // Get employee_id from employee table
        $employee_id = isset($user['employee_id']) ? (int)$user['employee_id'] : null;

        if (!$employee_id) {
            $_SESSION['flash_error'] = 'Employee profile not found.';
            $this->redirect('/employee/dashboard');
            return;
        }

        // Fetch assets of this employee
        $assetModel = new Asset();
        $assets = $assetModel->fetchAssetsByEmployee($employee_id);

        // For layout / navbar
        $ctx = $this->getLoggedUserContext();
        $base = $ctx['base'];
        $loggedFirstname = $ctx['loggedFirstname'];
        $loggedPosition  = $ctx['loggedPosition'];

        $notificationData = $this->loadNotifications();

        $count = $notificationData['count'];
        $notifications = $notificationData['notifications'];

        // Pass variables to view
        require __DIR__ . '/../../Views/employee/asset/asset.php';
    }
}
