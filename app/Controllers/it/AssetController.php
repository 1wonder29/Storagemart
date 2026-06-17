<?php
require_once __DIR__ . '/../AuthController.php';
require_once __DIR__ . '/../../Models/it/ItAssetModel.php';
require_once __DIR__ . '/../../Models/it/IT.php';
require_once __DIR__ . '/../../Models/admin/Asset.php';
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
        $ItModel = new IT();
        $user = $ItModel->fetchUserDetails($accountId);

        // Get employee_id from employee table
        $employee_id = isset($user['employee_id']) ? (int)$user['employee_id'] : null;

        if (!$employee_id) {
            $_SESSION['flash_error'] = 'Employee profile not found.';
            $this->redirect('/it/dashboard');
            return;
        }

        // Fetch assets of this employee
        $assetModel = new ItAssetModel();
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
        require __DIR__ . '/../../Views/it/asset/asset.php';
    }

    public function updateAccountabilityRemarks()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo 'Method Not Allowed';
            return;
        }

        if (empty($_SESSION['account_id']) || strtoupper($_SESSION['usertype'] ?? '') !== 'IT') {
            $this->redirect('/login');
            return;
        }

        $postedToken = $_POST['csrf_token'] ?? '';
        if (empty($postedToken) || $postedToken !== ($_SESSION['csrf_token'] ?? '')) {
            $_SESSION['flash_error'] = 'Invalid CSRF token.';
            $this->redirect('/it/dashboard');
            return;
        }

        $assignmentId = (int) ($_POST['assignment_id'] ?? 0);
        $remarks = trim((string) ($_POST['remarks'] ?? ''));
        $returnUrl = trim((string) ($_POST['return_url'] ?? '/it/dashboard'));

        if ($assignmentId <= 0 || $remarks === '') {
            $_SESSION['flash_error'] = 'Remarks are required.';
            $this->redirect($returnUrl !== '' ? $returnUrl : '/it/dashboard');
            return;
        }

        $assetModel = new Asset();
        if ($assetModel->updateAccountabilityRemarks($assignmentId, $remarks, $_SESSION['account_id'] ?? null)) {
            $_SESSION['flash_success'] = 'Accountability remarks updated.';
        } else {
            $_SESSION['flash_error'] = 'Unable to update accountability remarks.';
        }

        $this->redirect($returnUrl !== '' ? $returnUrl : '/it/dashboard');
    }

    public function transferHistory()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['account_id']) || strtoupper($_SESSION['usertype'] ?? '') !== 'IT') {
            $this->redirect('/login');
            return;
        }

        $inventoryId = isset($_GET['inventory_id']) ? (int) $_GET['inventory_id'] : 0;
        if ($inventoryId <= 0) {
            $_SESSION['flash_error'] = 'Invalid inventory id.';
            $this->redirect('/it/dashboard');
            return;
        }

        $assetModel = new Asset();
        $inventory = $assetModel->fetchInventoryById($inventoryId);
        if (!$inventory) {
            $_SESSION['flash_error'] = 'Item not found.';
            $this->redirect('/it/dashboard');
            return;
        }

        $assignments = $assetModel->fetchAssignmentsByInventoryId($inventoryId);

        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
        }
        $csrf_token = $_SESSION['csrf_token'];

        $base = rtrim(BASE_URL, '/');
        $remarksFormAction = $base . '/it/assets/accountability-remarks';
        $returnUrl = $base . '/it/assets/transfer-history?inventory_id=' . $inventoryId;

        $ctx = $this->getLoggedUserContext();
        $loggedFirstname = $ctx['loggedFirstname'];
        $loggedPosition = $ctx['loggedPosition'];
        $notificationData = $this->loadNotifications();
        $count = $notificationData['count'];
        $notifications = $notificationData['notifications'];

        require __DIR__ . '/../../Views/it/asset/transfer_history.php';
    }
}
