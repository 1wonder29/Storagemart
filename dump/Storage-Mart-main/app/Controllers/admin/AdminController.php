<?php

require_once __DIR__ . '/../AuthController.php';
require_once __DIR__ . '/../../Models/admin/Account.php';
require_once __DIR__ . '/../../Models/admin/Logger.php';
require_once __DIR__ . '/../../Helpers/Session.php';

class AdminController extends AuthController
{
    public function __construct()
    {
        parent::__construct();
    }

    /* ------------------------------------------------------
     * DASHBOARD
     * ------------------------------------------------------*/
    public function dashboard()
    {
        if (empty($_SESSION['account_id'])) {
            $_SESSION['loginMessage'] = 'Please log in to access the admin dashboard.';
            $this->redirect('/login');
        }

        if (strtoupper($_SESSION['usertype'] ?? '') !== 'ADMIN') {
            $_SESSION['loginMessage'] = 'Please log in as admin to access the admin dashboard.';
            $this->redirect('/login');
        }

        $accountModel = $this->model ?? new Account();

        $ctx = $this->getLoggedUserContext();
        $base = $ctx['base'];
        $loggedFirstname = $ctx['loggedFirstname'];
        $loggedPosition  = $ctx['loggedPosition'];

        if (method_exists($accountModel, 'fetchUserDetails') && !empty($_SESSION['account_id'])) {
            $details = $accountModel->fetchUserDetails((int)$_SESSION['account_id']);
            if (!empty($details['firstname'])) $loggedFirstname = $details['firstname'];
            if (!empty($details['position']))  $loggedUsertype  = $details['position'];
        }

        // Dashboard stats
        $users = method_exists($accountModel, 'fetchAll') ? $accountModel->fetchAll() : [];
        $ticketCount = method_exists($accountModel, 'countTicket') ? $accountModel->countTicket() : 0;
        $userCount = method_exists($accountModel, 'countUser') ? $accountModel->countUser() : count($users);
        $assetCount = method_exists($accountModel, 'countAssets') ? $accountModel->countAssets() : 0;
        $ticketOngoing = method_exists($accountModel, 'countOngoingTickets') ? $accountModel->countOngoingTickets() : 0;
        $notificationData = $this->loadNotifications();
        $count = $notificationData['count'];
        $notifications = $notificationData['notifications'];

        require __DIR__ . '/../../Views/admin/dashboard.php';
    }

    /* ------------------------------------------------------
     * ACCOUNT LIST
     * ------------------------------------------------------*/
    public function account()
    {
        if (empty($_SESSION['account_id']) ||
            strtoupper($_SESSION['usertype'] ?? '') !== 'ADMIN') {

            $_SESSION['loginMessage'] = 'Please log in as admin.';
            $this->redirect('/login');
        }

        $accountModel = $this->model ?? new Account();

        // Handle deletion
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (($_POST['action'] ?? '') === 'delete') {
                $id = (int)($_POST['id'] ?? 0);

                if ($id > 0) {
                    $logger = new Logger();
                    $ok = $accountModel->deleteById($id);

                    if ($ok) {
                        $logger->log("Deleted Account", "Account Management", $id, $_SESSION['username'] ?? 'Unknown');
                        $_SESSION['flash'] = "Account #{$id} deleted.";
                    } else {
                        $_SESSION['flash'] = "Failed to delete account #{$id}.";
                    }
                }

                $this->redirect('/admin/account');
            }
        }

        $users = method_exists($accountModel, 'fetchAll') ? $accountModel->fetchAll() : [];

        $ctx = $this->getLoggedUserContext();
        $base = $ctx['base'];
        $loggedFirstname = $ctx['loggedFirstname'];
        $loggedPosition  = $ctx['loggedPosition'];
        $notificationData = $this->loadNotifications();

        $count = $notificationData['count'];
        $notifications = $notificationData['notifications'];
        require __DIR__ . '/../../Views/admin/account/account.php';
    }

    /* ------------------------------------------------------
     * EDIT ACCOUNT
     * ------------------------------------------------------*/
    public function editAccount()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (empty($_SESSION['account_id']) ||
            strtoupper($_SESSION['usertype'] ?? '') !== 'ADMIN') {

            $_SESSION['loginMessage'] = 'Please log in as admin.';
            $this->redirect('/login');
        }

        $accountModel = $this->model ?? new Account();
        $id = (int)($_GET['account_id'] ?? 0);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            if (empty($_POST['csrf_token']) ||
                !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {

                $_SESSION['flash'] = 'Invalid CSRF token.';
                $this->redirect('/admin/account');
            }

            $dataAcc = [
                'account_id' => (int)($_POST['account_id'] ?? 0),
                'username' => trim($_POST['username'] ?? ''),
                'password' => $_POST['password'] ?? '',
                'usertype' => $_POST['usertype'] ?? '',
                'status'   => $_POST['status'] ?? '',
            ];


            $dataEmp = [
                'employee_id' => (int)($_POST['employee_id'] ?? 0),
                'lastname'    => trim($_POST['last-name'] ?? ''),
                'firstname'   => trim($_POST['first-name'] ?? ''),
                'middlename'  => trim($_POST['middle-name'] ?? ''),
                'department'  => $_POST['department'] ?? '',
                'branch_id'   => (int)($_POST['branch_id'] ?? 0),
                'email'       => trim($_POST['email'] ?? ''),
            ];

            try {
                $pdo = $accountModel->getPDO();
                if ($pdo instanceof PDO) $pdo->beginTransaction();

                $rawPw = trim($dataAcc['password']);
                if ($rawPw !== '') {
                    if (strpos($rawPw, '$2y$') === 0) {
                        $dataAcc['password'] = $rawPw;
                    } else {
                        $dataAcc['password'] = password_hash($rawPw, PASSWORD_DEFAULT);
                    }
                } else {
                    $old = $accountModel->getById($dataAcc['account_id']);
                    $dataAcc['password'] = $old['password'] ?? '';
                }

                $okAcc = $accountModel->updateAccount($dataAcc);
                $okEmp = $accountModel->updateEmployee($dataEmp);

                if (!$okAcc || !$okEmp) {
                    throw new Exception("Failed updating records.");
                }

                $logger = new Logger($pdo);
                $logger->log("Updated Account", "Employee Management", $dataEmp['employee_id'], $_SESSION['username']);

                if ($pdo instanceof PDO) $pdo->commit();

                $_SESSION['flash'] = "Account updated successfully!";
                $this->redirect('/admin/account');

            } catch (Exception $e) {
                if ($pdo instanceof PDO) $pdo->rollBack();
                error_log("editAccount error: " . $e->getMessage());
                $_SESSION['flash'] = "Error updating account.";
                $this->redirect('/admin/account');
            }
        }

        // GET: load existing data
        $full = $accountModel->fetchAccountById($id);

        $account = [
            'account_id' => $full['account_id'] ?? '',
            'username'   => $full['username'] ?? '',
            'password'   => '',
            'usertype'   => $full['usertype'] ?? '',
            'status'     => $full['status'] ?? '',
        ];

        $employee = [
            'employee_id' => $full['employee_id'] ?? '',
            'lastname'    => $full['lastname'] ?? '',
            'firstname'   => $full['firstname'] ?? '',
            'middlename'  => $full['middlename'] ?? '',
            'department'  => $full['department'] ?? '',
            'branch_id'   => $full['branch_id'] ?? '',
            'email'       => $full['email'] ?? '',
            'position'    => $full['position'] ?? '',
        ];

        // Branches
        $branches = method_exists($accountModel, 'fetchBranches')
            ? $accountModel->fetchBranches()
            : [];

        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
        }

        $base = $this->base ?? '/';
        $loggedFirstname = $_SESSION['display_firstname'] ?? ($_SESSION['username'] ?? '');
        $loggedPosition  = $_SESSION['display_position'] ?? ($_SESSION['usertype'] ?? '');
        $notificationData = $this->loadNotifications();

        $count = $notificationData['count'];
        $notifications = $notificationData['notifications'];
        require __DIR__ . '/../../Views/admin/account/edit.php';
    }

    /* ------------------------------------------------------
     * ADD ACCOUNT
     * ------------------------------------------------------*/
    public function addAccount()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (empty($_SESSION['account_id']) || strtoupper($_SESSION['usertype'] ?? '') !== 'ADMIN') {
            $this->redirect('/login');
            return;
        }

        $accountModel = $this->model ?? new Account();

        // Helper to load layout variables consistently
        $loadLayout = function () {
            $ctx = $this->getLoggedUserContext();
            $notif = $this->loadNotifications();

            return [
                'base'            => $ctx['base'],
                'loggedFirstname' => $ctx['loggedFirstname'],
                'loggedPosition'  => $ctx['loggedPosition'],
                'count'           => $notif['count'],
                'notifications'   => $notif['notifications'],
            ];
        };

        /* =========================
        * POST
        * ========================= */
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            // CSRF check
            if (
                empty($_POST['csrf_token']) ||
                !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')
            ) {
                $_SESSION['flash_error'] = 'Invalid CSRF token.';
                $this->redirect('/admin/account/add');
                return;
            }

            // collect inputs
            $old = [
                'username'    => trim($_POST['username'] ?? ''),
                'usertype'    => trim($_POST['usertype'] ?? ''),
                'employee_id' => trim($_POST['employee_id'] ?? ''),
                'branch_id'   => (int)($_POST['branch_id'] ?? 0),
                'lastname'    => trim($_POST['lastname'] ?? ''),
                'firstname'   => trim($_POST['firstname'] ?? ''),
                'middlename'  => trim($_POST['middlename'] ?? ''),
                'department'  => trim($_POST['department'] ?? ''),
                'email'       => trim($_POST['email'] ?? ''),
                'position'    => trim($_POST['position'] ?? ''),
            ];

            $password = (string)($_POST['password'] ?? '');

            // basic validation
            if ($old['username'] === '' || $password === '' || $old['usertype'] === '') {
                $_SESSION['flash_error'] = 'Username, password and user type are required.';
                $branches = $accountModel->fetchBranches();
                extract($loadLayout());
                require __DIR__ . '/../../Views/admin/account/add.php';
                return;
            }

            // employee_id: REQUIRED, INT ONLY, NO SPACES
            if (
                $old['employee_id'] === '' ||
                preg_match('/\s/', $old['employee_id']) ||
                !ctype_digit($old['employee_id']) ||
                (int)$old['employee_id'] <= 0
            ) {
                $_SESSION['flash_error'] = 'Employee ID is required and must be a positive number with no spaces.';
                $branches = $accountModel->fetchBranches();
                extract($loadLayout());
                require __DIR__ . '/../../Views/admin/account/add.php';
                return;
            }

            if ($accountModel->isUsernameExists($old['username'])) {
                $_SESSION['flash_error'] = 'Account username is already in use.';
                $branches = $accountModel->fetchBranches();
                extract($loadLayout());
                require __DIR__ . '/../../Views/admin/account/add.php';
                return;
            }

            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $pdo = $accountModel->getPDO();

            // force PDO errors
            if ($pdo instanceof PDO) {
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            }

            try {
                if ($pdo instanceof PDO) $pdo->beginTransaction();

                // create account
                $accountData = [
                    'username'    => $old['username'],
                    'password'    => $passwordHash,
                    'usertype'    => $old['usertype'],
                    'status'      => 'ACTIVE',
                    'createdby'   => $_SESSION['username'] ?? 'SYSTEM',
                    'datecreated' => date('Y-m-d H:i:s'),
                ];

                $newAccountId = $accountModel->createAccount($accountData);
                if (!$newAccountId) {
                    throw new Exception('ACCOUNT INSERT FAILED.');
                }

                // create employee (MANUAL employee_id)
                $employeeData = [
                    'employee_id' => (int)$old['employee_id'],
                    'account_id'  => (int)$newAccountId,
                    'lastname'    => $old['lastname'],
                    'firstname'   => $old['firstname'],
                    'middlename'  => $old['middlename'],
                    'department'  => $old['department'],
                    'branch_id'   => $old['branch_id'] ?: null,
                    'email'       => $old['email'],
                    'position'    => $old['position'],
                    'createdby'   => $_SESSION['username'] ?? 'SYSTEM',
                    'datecreated' => date('Y-m-d H:i:s'),
                ];

                $newEmployeeId = $accountModel->createEmployee($employeeData);
                if (!$newEmployeeId) {
                    throw new Exception('EMPLOYEE INSERT FAILED.');
                }

                // log
                $logger = new Logger($pdo);
                $logger->log(
                    'Create Account',
                    'Employee Management',
                    $newEmployeeId,
                    $_SESSION['username'] ?? 'SYSTEM'
                );

                if ($pdo instanceof PDO) $pdo->commit();

                $_SESSION['flash_success'] = 'New Account successfully created!';
                $this->redirect('/admin/account');
                return;

            } catch (Throwable $e) {
                if ($pdo instanceof PDO) $pdo->rollBack();
                error_log('addAccount error: ' . $e->getMessage());

                $_SESSION['flash_error'] = 'Error creating account: ' . $e->getMessage();
                $branches = $accountModel->fetchBranches();
                extract($loadLayout());
                require __DIR__ . '/../../Views/admin/account/add.php';
                return;
            }
        }

        /* =========================
        * GET
        * ========================= */
        $branches = $accountModel->fetchBranches();

        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
        }

        extract($loadLayout());
        require __DIR__ . '/../../Views/admin/account/add.php';
    }


    public function employee()
    {
        if (empty($_SESSION['account_id']) ||
            strtoupper($_SESSION['usertype'] ?? '') !== 'ADMIN') {

            $_SESSION['loginMessage'] = 'Please log in as admin.';
            $this->redirect('/login');
        }

        $accountModel = $this->model ?? new Account();

        // Use the tailored fetchEmployee() that returns branchName etc.
        $employees = method_exists($accountModel, 'fetchEmployee')
            ? $accountModel->fetchEmployee()
            : [];

        // Build the usual layout context
        $ctx = $this->getLoggedUserContext();
        $base = $ctx['base'];
        $loggedFirstname = $ctx['loggedFirstname'];
        $loggedPosition  = $ctx['loggedPosition'];
        $notificationData = $this->loadNotifications();

        $count = $notificationData['count'];
        $notifications = $notificationData['notifications'];
        // Pass $employees (plural) to the view — your view expects $employees
        require __DIR__ . '/../../Views/admin/account/employee.php';
    }

    public function view_asset(){
            if (empty($_SESSION['account_id']) ||
            strtoupper($_SESSION['usertype'] ?? '') !== 'ADMIN') {

            $_SESSION['loginMessage'] = 'Please log in as admin.';
            $this->redirect('/login');
        }

        $accountModel = $this->model ?? new Account();
        $employee_id = (int)($_GET['employee_id'] ?? 0);

        if ($employee_id <= 0) {
            // no employee specified — show message or redirect back
            $_SESSION['flash'] = 'No employee specified.';
            $this->redirect('/admin/employee'); // change target as appropriate
            return;
        }
        $assets = method_exists($accountModel, 'fetchAssetsByEmployeeId')
            ? $accountModel->fetchAssetsByEmployeeId($employee_id)
            : [];
        // Build the usual layout context
        $ctx = $this->getLoggedUserContext();
        $base = $ctx['base'];
        $loggedFirstname = $ctx['loggedFirstname'];
        $loggedPosition  = $ctx['loggedPosition'];
        $notificationData = $this->loadNotifications();

        $count = $notificationData['count'];
        $notifications = $notificationData['notifications'];
        // Pass $assets (plural) to the view — your view expects $assets
        require __DIR__ . '/../../Views/admin/account/asset.php';
    }
}

