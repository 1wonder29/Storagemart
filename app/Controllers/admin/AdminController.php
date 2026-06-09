<?php

require_once __DIR__ . '/../AuthController.php';
require_once __DIR__ . '/../../Models/admin/Account.php';
require_once __DIR__ . '/../../Models/admin/Logger.php';
require_once __DIR__ . '/../../Models/admin/AuditTrail.php';
require_once __DIR__ . '/../../Models/DashboardModel.php';
require_once __DIR__ . '/../../Helpers/Session.php';
require_once __DIR__ . '/../../Helpers/ActivityLogger.php';

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
        
        // Get ticket resolution times for SLA chart
        $dashboardModel = new DashboardModel();
        $ticketCategoryCounts = $dashboardModel->getTicketCountsByCategory();
        $ticketStatusCounts = $dashboardModel->getTicketCountsByStatus();
        $resolutionRows = $dashboardModel->getItTicketResolutionTimes();
        $resolutionLabels = [];
        $resolutionData = [];
        foreach ($resolutionRows as $row) {
            $resolutionLabels[] = 'Ticket #' . $row['ticket_number'];
            $resolutionData[] = (int)$row['resolution_hours'];
        }
        
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
                    // Fetch account details before deletion for audit trail
                    $accountDetails = $accountModel->fetchAccountById($id);
                    
                    $ok = $accountModel->deleteById($id);

                    if ($ok) {
                        // Log deletion via ActivityLogger
                        $username = $accountDetails['username'] ?? 'Unknown';
                        ActivityLogger::delete('Admin - Accounts', (string)$id,
                            "Account deleted: {$username} ({$accountDetails['usertype']})",
                            $_SESSION['username'] ?? 'Unknown', [
                                'account_id' => $id,
                                'username' => $username,
                                'usertype' => $accountDetails['usertype'] ?? 'Unknown',
                                'status' => $accountDetails['status'] ?? 'Unknown',
                                'deleted_at' => date('Y-m-d H:i:s'),
                                'deleted_by' => $_SESSION['username'] ?? 'Unknown'
                            ]);
                        
                        $_SESSION['flash'] = "Account #{$id} has been permanently deleted and logged in audit trail.";
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

                // Log update via ActivityLogger
                ActivityLogger::update('Admin - Accounts', (string)$dataAcc['account_id'],
                    "Account updated: {$dataAcc['username']} ({$dataAcc['usertype']})",
                    $_SESSION['username'] ?? 'Unknown', [
                        'username' => $dataAcc['username'],
                        'usertype' => $dataAcc['usertype'],
                        'status' => $dataAcc['status'],
                        'email' => $dataEmp['email'],
                        'department' => $dataEmp['department']
                    ]);

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
                ActivityLogger::create('Admin - Accounts', (string)$newAccountId,
                    "New account created: {$old['username']} ({$old['usertype']})",
                    $_SESSION['username'] ?? 'Unknown', [
                        'username' => $old['username'],
                        'usertype' => $old['usertype'],
                        'employee_id' => $newEmployeeId,
                        'firstname' => $old['firstname'],
                        'lastname' => $old['lastname'],
                        'email' => $old['email'],
                        'department' => $old['department']
                    ]);

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

        // Handle deletion
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (($_POST['action'] ?? '') === 'delete') {
                $employeeId = (int)($_POST['employee_id'] ?? 0);

                if ($employeeId > 0) {
                    // Fetch employee details before deletion for audit trail
                    $employees = $accountModel->fetchEmployee();
                    $employeeDetails = null;
                    foreach ($employees as $emp) {
                        if ($emp['employee_id'] == $employeeId) {
                            $employeeDetails = $emp;
                            break;
                        }
                    }
                    
                    $ok = $accountModel->deleteEmployeeByEmployeeId($employeeId);

                    if ($ok) {
                        // Log deletion via ActivityLogger
                        $empName = ($employeeDetails['firstname'] ?? 'Unknown') . ' ' . ($employeeDetails['lastname'] ?? '');
                        ActivityLogger::delete('Admin - Employees', (string)$employeeId,
                            "Employee deleted: {$empName}",
                            $_SESSION['username'] ?? 'Unknown', [
                                'employee_id' => $employeeId,
                                'firstname' => $employeeDetails['firstname'] ?? 'Unknown',
                                'lastname' => $employeeDetails['lastname'] ?? 'Unknown',
                                'email' => $employeeDetails['email'] ?? 'Unknown',
                                'department' => $employeeDetails['department'] ?? 'Unknown',
                                'deleted_at' => date('Y-m-d H:i:s'),
                                'deleted_by' => $_SESSION['username'] ?? 'Unknown'
                            ]);
                        
                        $_SESSION['flash'] = "Employee #{$employeeId} has been permanently deleted and logged in audit trail.";
                    } else {
                        $_SESSION['flash'] = "Failed to delete employee #{$employeeId}.";
                    }
                }

                $this->redirect('/admin/employee');
            }
        }

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

    public function profile()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (empty($_SESSION['account_id']) || strtoupper($_SESSION['usertype'] ?? '') !== 'ADMIN') {
            $_SESSION['loginMessage'] = 'Please log in as admin.';
            $this->redirect('/login');
            return;
        }

        $accountModel = $this->model ?? new Account();
        $profile = $accountModel->fetchAccountById((int)$_SESSION['account_id']) ?? [];

        $ctx = $this->getLoggedUserContext();
        $base = $ctx['base'];
        $loggedFirstname = $ctx['loggedFirstname'];
        $loggedPosition  = $ctx['loggedPosition'];

        $notificationData = $this->loadNotifications();
        $count = $notificationData['count'];
        $notifications = $notificationData['notifications'];

        require __DIR__ . '/../../Views/admin/profile/profile.php';
    }

    /* ------------------------------------------------------
     * AUDIT TRAIL / ACTIVITY LOG
     * ------------------------------------------------------*/
    public function auditTrail()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (empty($_SESSION['account_id']) || strtoupper($_SESSION['usertype'] ?? '') !== 'ADMIN') {
            $_SESSION['loginMessage'] = 'Please log in as admin.';
            $this->redirect('/login');
            return;
        }

        $auditTrail = new AuditTrail();
        
        // Pagination
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $limit = isset($_GET['limit']) ? min(100, (int)$_GET['limit']) : 50;
        $offset = ($page - 1) * $limit;
        
        // Filter type
        $filterType = $_GET['type'] ?? 'all'; // 'all', 'deletes', 'by-module', 'by-user', 'by-date'
        $module = $_GET['module'] ?? null;
        $performer = $_GET['performer'] ?? null;
        $startDate = $_GET['start_date'] ?? null;
        $endDate = $_GET['end_date'] ?? null;
        $searchTerm = $_GET['search'] ?? null;

        // Fetch logs based on filter
        $logs = [];
        $totalCount = 0;

        if ($filterType === 'deletes') {
            $logs = $auditTrail->getAdminDeleteLogs($limit, $offset);
            $totalCount = $auditTrail->countDeleteLogs();
        } elseif ($filterType === 'by-module' && $module) {
            $logs = $auditTrail->getAuditLogsByModule($module, $limit, $offset);
            $totalCount = $auditTrail->countAuditLogs($module);
        } elseif ($filterType === 'by-user' && $performer) {
            $logs = $auditTrail->getAuditsByPerformer($performer, $limit, $offset);
        } elseif ($filterType === 'by-date' && $startDate && $endDate) {
            $logs = $auditTrail->getAuditsByDateRange($startDate, $endDate, $limit, $offset);
        } elseif ($filterType === 'search' && $searchTerm) {
            $logs = $auditTrail->searchAuditLogs($searchTerm, $limit, $offset);
        } else {
            $logs = $auditTrail->getAllAuditLogs($limit, $offset);
            $totalCount = $auditTrail->countAuditLogs();
        }

        // Get summaries for dashboard
        $deletesSummary = $auditTrail->getDeleteLogsSummary();
        $recentDeletes = $auditTrail->getRecentDeleteActions(7);

        // Calculate pagination
        $totalPages = ceil($totalCount / $limit);

        // Layout context
        $ctx = $this->getLoggedUserContext();
        $base = $ctx['base'];
        $loggedFirstname = $ctx['loggedFirstname'];
        $loggedPosition = $ctx['loggedPosition'];
        $notificationData = $this->loadNotifications();

        $count = $notificationData['count'];
        $notifications = $notificationData['notifications'];

        require __DIR__ . '/../../Views/admin/audit/audit_trail.php';
    }

    /**
     * API endpoint to fetch audit log details via AJAX
     */
    public function auditDetail()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        header('Content-Type: application/json; charset=utf-8');

        if (empty($_SESSION['account_id']) || strtoupper($_SESSION['usertype'] ?? '') !== 'ADMIN') {
            http_response_code(403);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }

        $recordId = $_GET['record_id'] ?? null;
        if (!$recordId) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing record_id parameter']);
            exit;
        }

        $auditTrail = new AuditTrail();
        $trail = $auditTrail->getRecordAuditTrail($recordId);

        echo json_encode(['success' => true, 'data' => $trail]);
        exit;
    }

    public function ratings()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['account_id']) || strtoupper($_SESSION['usertype'] ?? '') !== 'ADMIN') {
            $this->redirect('/login');
            return;
        }

        require_once __DIR__ . '/../../Models/admin/RatingsModel.php';

        $ratingsModel = new RatingsModel();
        $ratings = $ratingsModel->getAllRatings();
        $stats = $ratingsModel->getOverallStats();
        $itStaffPerformance = $ratingsModel->getItStaffPerformance();
        $itStaffList = $ratingsModel->getAllItStaff();

        $ctx = $this->getLoggedUserContext();
        $base = $ctx['base'];
        $loggedFirstname = $ctx['loggedFirstname'];
        $loggedPosition = $ctx['loggedPosition'];

        $notificationData = $this->loadNotifications();
        $count = $notificationData['count'];
        $notifications = $notificationData['notifications'];

        require __DIR__ . '/../../Views/admin/ratings-dashboard.php';
    }

    public function ratingsData()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['account_id']) || strtoupper($_SESSION['usertype'] ?? '') !== 'ADMIN') {
            http_response_code(403);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }

        require_once __DIR__ . '/../../Models/admin/RatingsModel.php';

        $ratingsModel = new RatingsModel();
        
        $filters = [
            'start_date' => $_GET['start_date'] ?? '',
            'end_date' => $_GET['end_date'] ?? '',
            'it_id' => $_GET['it_id'] ?? '',
            'rating' => $_GET['rating'] ?? ''
        ];

        $ratings = $ratingsModel->getAllRatings($filters);
        
        header('Content-Type: application/json');
        echo json_encode($ratings);
        exit;
    }

    /* ------------------------------------------------------
     * MONTHLY TICKET REPORT
     * ------------------------------------------------------*/
    public function monthlyReport()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['account_id']) || strtoupper($_SESSION['usertype'] ?? '') !== 'ADMIN') {
            $this->redirect('/login');
            return;
        }

        require_once __DIR__ . '/../../Models/admin/Ticket.php';

        $year = isset($_GET['year']) ? (int) $_GET['year'] : (int) date('Y');
        $month = isset($_GET['month']) ? (int) $_GET['month'] : (int) date('n');

        if ($year < 2000 || $year > 2100) {
            $year = (int) date('Y');
        }
        if ($month < 1 || $month > 12) {
            $month = (int) date('n');
        }

        $ticketModel = new Ticket();
        $tickets = $ticketModel->fetchTicketsByMonth($year, $month);
        $ticketCount = count($tickets);

        $ctx = $this->getLoggedUserContext();
        $base = $ctx['base'];
        $loggedFirstname = $ctx['loggedFirstname'];
        $loggedPosition = $ctx['loggedPosition'];

        $notificationData = $this->loadNotifications();
        $count = $notificationData['count'];
        $notifications = $notificationData['notifications'];

        $selectedYear = $year;
        $selectedMonth = $month;
        $monthLabel = date('F Y', mktime(0, 0, 0, $month, 1, $year));

        require __DIR__ . '/../../Views/admin/reports/monthly_tickets.php';
    }

    public function monthlyReportExport()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['account_id']) || strtoupper($_SESSION['usertype'] ?? '') !== 'ADMIN') {
            http_response_code(403);
            echo 'Unauthorized';
            exit;
        }

        require_once __DIR__ . '/../../Models/admin/Ticket.php';
        require_once __DIR__ . '/../../Services/ExcelExportService.php';

        $year = isset($_GET['year']) ? (int) $_GET['year'] : (int) date('Y');
        $month = isset($_GET['month']) ? (int) $_GET['month'] : (int) date('n');

        if ($year < 2000 || $year > 2100 || $month < 1 || $month > 12) {
            http_response_code(400);
            echo 'Invalid month or year.';
            exit;
        }

        $ticketModel = new Ticket();
        $tickets = $ticketModel->fetchTicketsByMonth($year, $month);

        $headers = [
            'Ticket #',
            'Employee',
            'Employee Department',
            'Branch',
            'Ticket Department',
            'Category',
            'Asset',
            'Priority',
            'Status',
            'Concern Details',
            'Remarks',
            'Assigned To',
            'Date Filed',
            'Last Updated',
            'Date Approved',
            'Decline Reason',
        ];

        $rows = [];
        foreach ($tickets as $ticket) {
            $rows[] = [
                $ticket['ticket_number'] ?? '',
                trim($ticket['employee_name'] ?? ''),
                $ticket['employee_department'] ?? '',
                $ticket['branchName'] ?? '',
                $ticket['department'] ?? '',
                $ticket['category'] ?? '',
                $ticket['asset_info'] ?? '',
                $ticket['priority'] ?? '',
                $ticket['status'] ?? '',
                $ticket['concern_details'] ?? '',
                $ticket['remarks'] ?? '',
                trim($ticket['assigned_to_name'] ?? '') ?: 'Unassigned',
                $ticket['date_filed'] ?? '',
                $ticket['last_updated'] ?? '',
                $ticket['date_approved'] ?? '',
                $ticket['decline_reason'] ?? '',
            ];
        }

        $filename = sprintf('tickets_%04d_%02d.xls', $year, $month);

        try {
            (new ExcelExportService())->download($headers, $rows, $filename);
        } catch (Throwable $e) {
            error_log('Monthly ticket export failed: ' . $e->getMessage());
            http_response_code(500);
            echo 'Failed to generate Excel file.';
            exit;
        }
    }
}

