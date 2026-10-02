<?php

require_once __DIR__ . '/../AuthController.php';
require_once __DIR__ . '/../../Models/admin/Asset.php';
require_once __DIR__ . '/../../Models/hr/HRModel.php';
require_once __DIR__ . '/../../Models/NotificationModel.php';

/**
 * HrAssetController - HR asset transfer operations
 */
class HrAssetController extends AuthController
{
    protected $hrModel;

    public function __construct()
    {
        parent::__construct();
        $this->hrModel = new HRModel();
    }

    protected function requireHR()
    {
        if (empty($_SESSION['account_id'])) {
            $_SESSION['loginMessage'] = 'Please log in to continue.';
            $this->redirect('/login');
        }

        if (strtoupper($_SESSION['usertype'] ?? '') !== 'HR') {
            $_SESSION['loginMessage'] = 'Access denied. HR only.';
            $this->redirect('/login');
        }
    }

    protected function redirectAfterTransfer(int $returnEmployeeId): void
    {
        if ($returnEmployeeId > 0) {
            $this->redirect('/hr/employees/detail/' . $returnEmployeeId);
            return;
        }

        $this->redirect('/hr/employees');
    }

    public function transferItem()
    {
        $this->requireHR();

        $assetModel = new Asset();
        $base = rtrim(BASE_URL, '/');

        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $inventoryId = isset($_GET['inventory_id']) ? (int) $_GET['inventory_id'] : 0;
            $returnEmployeeId = isset($_GET['return_employee_id']) ? (int) $_GET['return_employee_id'] : 0;

            if ($inventoryId <= 0) {
                $_SESSION['errorMessage'] = 'Invalid inventory id.';
                $this->redirectAfterTransfer($returnEmployeeId);
                return;
            }

            $inventory = $assetModel->fetchInventoryById($inventoryId);
            if (!$inventory) {
                $_SESSION['errorMessage'] = 'Item not found.';
                $this->redirectAfterTransfer($returnEmployeeId);
                return;
            }

            if (empty($_SESSION['csrf_token'])) {
                $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
            }

            $notifications = (new NotificationModel())->getLatest((int) $_SESSION['account_id'], 10);
            $csrf_token = $_SESSION['csrf_token'];
            $return_employee_id = $returnEmployeeId;

            require __DIR__ . '/../../Views/hr/asset/transfer.php';
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $postedToken = $_POST['csrf_token'] ?? '';
            if (empty($postedToken) || $postedToken !== ($_SESSION['csrf_token'] ?? '')) {
                $_SESSION['errorMessage'] = 'Invalid CSRF token.';
                $this->redirect('/hr/employees');
                return;
            }

            $inventoryId = isset($_POST['item_id']) ? (int) $_POST['item_id'] : 0;
            $employeeId = isset($_POST['employee_id']) ? (int) $_POST['employee_id'] : 0;
            $transferDetails = trim($_POST['transferDetails'] ?? '');
            $returnEmployeeId = isset($_POST['return_employee_id']) ? (int) $_POST['return_employee_id'] : 0;
            $performedBy = $_SESSION['account_id'] ?? ($_SESSION['username'] ?? 'SYSTEM');

            if ($inventoryId <= 0 || $employeeId <= 0 || $transferDetails === '') {
                $_SESSION['errorMessage'] = 'Please complete required fields.';
                $this->redirect('/hr/assets/transfer?inventory_id=' . $inventoryId . '&return_employee_id=' . $returnEmployeeId);
                return;
            }

            $result = $assetModel->transferAssetToEmployee($inventoryId, $employeeId, $transferDetails, $performedBy);

            if ($result['ok']) {
                $targets = $assetModel->getAssetNotificationTargets($inventoryId);
                $notificationModel = new NotificationModel();

                if (!empty($targets['employee_account_id'])) {
                    $notificationModel->create(
                        (int) $targets['employee_account_id'],
                        "A new asset ({$targets['assetNumber']}) has been assigned to you.",
                        'fa-box',
                        'info',
                        $base . '/employee/assets',
                        $inventoryId
                    );
                }

                if (!empty($targets['head_account_id'])) {
                    $notificationModel->create(
                        (int) $targets['head_account_id'],
                        "Asset {$targets['assetNumber']} has been transferred to your department.",
                        'fa-exchange-alt',
                        'primary',
                        $base . '/head/dashboard',
                        $inventoryId
                    );
                }

                $this->hrModel->logAction(
                    'TRANSFERRED_ASSET',
                    $employeeId,
                    null,
                    (int) $_SESSION['account_id'],
                    'Transferred asset to employee ID ' . $employeeId . '. New asset number: ' . $result['newAssetNumber']
                );

                $_SESSION['successMessage'] = 'Asset successfully transferred. New Asset Number: ' . $result['newAssetNumber'];
            } else {
                $_SESSION['errorMessage'] = 'Transfer failed: ' . $result['message'];
            }

            $this->redirectAfterTransfer($returnEmployeeId > 0 ? $returnEmployeeId : $employeeId);
            return;
        }

        http_response_code(405);
        echo 'Method Not Allowed';
    }

    public function searchEmployee()
    {
        header('Content-Type: application/json');

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        try {
            if (empty($_SESSION['account_id']) || strtoupper($_SESSION['usertype'] ?? '') !== 'HR') {
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'Not authenticated']);
                return;
            }

            $q = trim($_GET['q'] ?? '');
            if ($q === '') {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Empty query']);
                return;
            }

            $assetModel = new Asset();
            $row = $assetModel->findEmployeeByQuery($q);

            if ($row) {
                echo json_encode([
                    'success' => true,
                    'employee_id' => (int) $row['employee_id'],
                    'full_name' => $row['fullname'] ?? ($row['full_name'] ?? ''),
                    'branchName' => $row['branchName'] ?? '',
                    'branchCode' => $row['branchCode'] ?? ''
                ]);
                return;
            }

            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Employee not found']);
        } catch (\Throwable $e) {
            error_log('HrAssetController::searchEmployee error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Server error']);
        }
    }

    public function transferHistory()
    {
        $this->requireHR();

        $assetModel = new Asset();
        $inventoryId = isset($_GET['inventory_id']) ? (int) $_GET['inventory_id'] : 0;
        $returnEmployeeId = isset($_GET['return_employee_id']) ? (int) $_GET['return_employee_id'] : 0;

        if ($inventoryId <= 0) {
            $_SESSION['errorMessage'] = 'Invalid inventory id.';
            $this->redirectAfterTransfer($returnEmployeeId);
            return;
        }

        $inventory = $assetModel->fetchInventoryById($inventoryId);
        if (!$inventory) {
            $_SESSION['errorMessage'] = 'Item not found.';
            $this->redirectAfterTransfer($returnEmployeeId);
            return;
        }

        $assignments = $assetModel->fetchAssignmentsByInventoryId($inventoryId);
        $notifications = (new NotificationModel())->getLatest((int) $_SESSION['account_id'], 10);
        $return_employee_id = $returnEmployeeId;

        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
        }
        $csrf_token = $_SESSION['csrf_token'];
        $base = rtrim(BASE_URL, '/');
        $remarksFormAction = $base . '/hr/assets/accountability-remarks';
        $returnUrl = $base . '/hr/assets/transfer-history?inventory_id=' . $inventoryId;
        if ($returnEmployeeId > 0) {
            $returnUrl .= '&return_employee_id=' . $returnEmployeeId;
        }

        require __DIR__ . '/../../Views/hr/asset/transfer_history.php';
    }

    public function returnAsset()
    {
        $this->requireHR();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo 'Method Not Allowed';
            return;
        }

        $postedToken = $_POST['csrf_token'] ?? '';
        if (empty($postedToken) || $postedToken !== ($_SESSION['csrf_token'] ?? '')) {
            $_SESSION['errorMessage'] = 'Invalid CSRF token.';
            $this->redirect('/hr/employees');
            return;
        }

        $inventoryId = (int) ($_POST['inventory_id'] ?? 0);
        $employeeId = (int) ($_POST['employee_id'] ?? 0);
        $remarks = trim((string) ($_POST['remarks'] ?? ''));

        if ($inventoryId <= 0 || $employeeId <= 0) {
            $_SESSION['errorMessage'] = 'Invalid asset or employee.';
            $this->redirect('/hr/employees/detail/' . max($employeeId, 0));
            return;
        }

        $assetModel = new Asset();
        $ok = $assetModel->returnAssetFromEmployee(
            $inventoryId,
            $employeeId,
            $remarks,
            (int) ($_SESSION['account_id'] ?? 0),
            'HR'
        );

        if ($ok) {
            $this->hrModel->logAction(
                'RETURNED_ASSET',
                $employeeId,
                null,
                (int) ($_SESSION['account_id'] ?? 0),
                'Returned inventory ID ' . $inventoryId . ' and updated accountability form.'
            );
            $_SESSION['successMessage'] = 'Asset returned successfully. Accountability form has been updated.';
        } else {
            $_SESSION['errorMessage'] = 'Could not return asset. It may no longer be assigned to this employee.';
        }

        $this->redirect('/hr/employees/detail/' . $employeeId . '#accountability-form');
    }

    public function updateAccountabilityRemarks()
    {
        $this->requireHR();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo 'Method Not Allowed';
            return;
        }

        $postedToken = $_POST['csrf_token'] ?? '';
        if (empty($postedToken) || $postedToken !== ($_SESSION['csrf_token'] ?? '')) {
            $_SESSION['errorMessage'] = 'Invalid CSRF token.';
            $this->redirect('/hr/employees');
            return;
        }

        $assignmentId = (int) ($_POST['assignment_id'] ?? 0);
        $employeeId = (int) ($_POST['employee_id'] ?? 0);
        $remarks = trim((string) ($_POST['remarks'] ?? ''));
        $returnUrl = trim((string) ($_POST['return_url'] ?? ''));

        if ($assignmentId <= 0 || $remarks === '') {
            $_SESSION['errorMessage'] = 'Remarks are required.';
            $this->redirect($returnUrl !== '' ? $returnUrl : '/hr/employees/detail/' . max($employeeId, 0) . '#accountability-form');
            return;
        }

        $assetModel = new Asset();
        if ($assetModel->updateAccountabilityRemarks($assignmentId, $remarks, (int) ($_SESSION['account_id'] ?? 0))) {
            $this->hrModel->logAction(
                'UPDATED_ACCOUNTABILITY',
                $employeeId > 0 ? $employeeId : null,
                null,
                (int) ($_SESSION['account_id'] ?? 0),
                'Updated accountability remarks for assignment ID ' . $assignmentId
            );
            $_SESSION['successMessage'] = 'Accountability remarks updated.';
        } else {
            $_SESSION['errorMessage'] = 'Unable to update accountability remarks.';
        }

        if ($returnUrl !== '') {
            $this->redirect($returnUrl);
            return;
        }

        $this->redirect('/hr/employees/detail/' . $employeeId . '#accountability-form');
    }
}
