<?php

require_once __DIR__ . '/AuthController.php';
require_once __DIR__ . '/../Models/UserRequestModel.php';
require_once __DIR__ . '/../Models/NotificationModel.php';
require_once __DIR__ . '/../Models/admin/Department.php';
require_once __DIR__ . '/../Helpers/ActivityLogger.php';
require_once __DIR__ . '/../Helpers/AppSettings.php';

/**
 * HR requests new user accounts; the HR Head approves, then the Admin approves and the account
 * is created. HR:    /hr/user-requests, /hr/user-requests/new, POST /hr/user-requests/decide
 *         Admin: /admin/user-requests, POST /admin/user-requests/decide, POST /admin/user-requests/hr-head
 */
class UserRequestController extends AuthController
{
    private UserRequestModel $requests;
    private NotificationModel $notifications;

    public function __construct()
    {
        parent::__construct();
        $this->requests = new UserRequestModel();
        $this->notifications = new NotificationModel();
    }

    /* ======================= HR ======================= */

    /** GET /hr/user-requests */
    public function hrList(): void
    {
        $this->requireHrUser();
        $me = (int) $_SESSION['account_id'];
        $this->renderList('hr', [
            'rows' => $this->requests->ready() ? $this->requests->recent() : [],
            'setupMissing' => !$this->requests->ready(),
            'isHrHead' => $this->requests->ready() && $this->requests->isHrHead($me),
            'me' => $me,
        ]);
    }

    /** GET / POST /hr/user-requests/new */
    public function hrForm(): void
    {
        $this->requireHrUser();
        $old = [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->checkCsrf('/hr/user-requests/new');
            if (!$this->requests->ready()) {
                $_SESSION['flash_error'] = 'Please run the database update (update O) first.';
                $this->redirect('/hr/user-requests');
                return;
            }
            [$data, $error] = $this->requests->validate($_POST);
            if ($error === null) {
                $me = (int) $_SESSION['account_id'];
                $id = $this->requests->create($data, $me);
                $request = $this->requests->get($id);
                ActivityLogger::create('HR - User requests', (string) $id, 'New user requested: ' . $request['full_name'] . ' (' . $data['usertype'] . ')',
                    $_SESSION['username'] ?? 'Unknown', ['username' => $data['username'], 'employee_id' => $data['employee_id']]);
                if ($request['status'] === 'HR_HEAD') {
                    $this->notifyMany($this->requests->hrHeadAccountIds(), 'New user request for ' . $request['full_name'] . ' is waiting for your approval (HR Head).',
                        '/hr/user-requests', $id);
                    $_SESSION['flash_success'] = 'Request sent. The HR Head approves it first, then the Admin creates the account.';
                } else {
                    $this->notifyMany($this->requests->adminAccountIds(), 'New user request for ' . $request['full_name'] . ' (approved by the HR Head) is waiting for you.',
                        '/admin/user-requests', $id);
                    $_SESSION['flash_success'] = 'Request sent. As HR Head your approval is already included; the Admin creates the account next.';
                }
                $this->redirect('/hr/user-requests');
                return;
            }
            $_SESSION['flash_error'] = $error;
            $old = $data;
        }
        $accountModel = new Account();
        $branches = $accountModel->fetchBranches();
        $departments = (new Department())->fetchAll();
        $personalReady = EmployeePersonal::ready($GLOBALS['pdo']);
        $personal = $old['personal'] ?? [];
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
        }
        $base = rtrim(BASE_URL, '/');
        $roleOptions = UserRequestModel::roleOptions();
        require __DIR__ . '/../Views/hr/users/request_form.php';
    }

    /** POST /hr/user-requests/decide : HR Head approves / rejects, the requester cancels. */
    public function hrDecide(): void
    {
        $this->requireHrUser();
        $this->checkCsrf('/hr/user-requests');
        $me = (int) $_SESSION['account_id'];
        $request = $this->requests->get((int) ($_POST['request_id'] ?? 0));
        $action = (string) ($_POST['decision'] ?? '');
        $note = mb_substr(trim((string) ($_POST['note'] ?? '')), 0, 255);
        if (!$request) {
            $_SESSION['flash_error'] = 'Request not found.';
        } elseif ($action === 'cancel') {
            if ((int) $request['requested_by'] !== $me || !in_array($request['status'], ['HR_HEAD', 'ADMIN'], true)) {
                $_SESSION['flash_error'] = 'Only the person who sent a waiting request can cancel it.';
            } elseif ($this->requests->move((int) $request['request_id'], $request['status'], 'CANCELLED')) {
                $this->logRequest($request, 'cancelled');
                $_SESSION['flash_success'] = 'Request cancelled.';
            }
        } elseif ($request['status'] !== 'HR_HEAD' || !$this->requests->isHrHead($me)) {
            $_SESSION['flash_error'] = 'Only the HR Head can approve or reject this step.';
        } elseif ($action === 'approve') {
            if ($this->requests->move((int) $request['request_id'], 'HR_HEAD', 'ADMIN', ['hr_head_by' => $me, 'hr_head_at' => 'NOW()'])) {
                $this->logRequest($request, 'approved by the HR Head');
                $this->notifyMany($this->requests->adminAccountIds(), 'New user request for ' . $request['full_name'] . ' (approved by the HR Head) is waiting for you.',
                    '/admin/user-requests', (int) $request['request_id']);
                $this->notifyOne((int) $request['requested_by'], 'The HR Head approved your request for ' . $request['full_name'] . '. The Admin creates the account next.', 'success');
                $_SESSION['flash_success'] = 'Approved. The Admin was notified to create the account.';
            }
        } elseif ($action === 'reject') {
            if ($note === '') {
                $_SESSION['flash_error'] = 'Please write the reason for rejecting.';
            } elseif ($this->requests->move((int) $request['request_id'], 'HR_HEAD', 'REJECTED', ['hr_head_by' => $me, 'hr_head_at' => 'NOW()', 'decision_note' => $note])) {
                $this->logRequest($request, 'rejected by the HR Head: ' . $note);
                $this->notifyOne((int) $request['requested_by'], 'The HR Head rejected your request for ' . $request['full_name'] . ': ' . $note, 'danger');
                $_SESSION['flash_success'] = 'Rejected. The sender was notified.';
            }
        }
        $this->redirect('/hr/user-requests');
    }

    /* ======================= Admin ======================= */

    /** GET /admin/user-requests */
    public function adminList(): void
    {
        $this->requireAdminUser();
        $ready = $this->requests->ready();
        $this->renderList('admin', [
            'rows' => $ready ? $this->requests->recent() : [],
            'setupMissing' => !$ready,
            'hrAccounts' => $ready ? $this->requests->hrAccounts() : [],
            'hrHeadIds' => $ready ? $this->requests->hrHeadAccountIds() : [],
            'hrHeadChosen' => (int) AppSettings::get(UserRequestModel::HR_HEAD_SETTING, '0'),
            'me' => (int) $_SESSION['account_id'],
        ]);
    }

    /** POST /admin/user-requests/decide : create the account, or reject. */
    public function adminDecide(): void
    {
        $this->requireAdminUser();
        $this->checkCsrf('/admin/user-requests');
        $me = (int) $_SESSION['account_id'];
        $request = $this->requests->get((int) ($_POST['request_id'] ?? 0));
        $action = (string) ($_POST['decision'] ?? '');
        $note = mb_substr(trim((string) ($_POST['note'] ?? '')), 0, 255);
        if (!$request) {
            $_SESSION['flash_error'] = 'Request not found.';
        } elseif ($action === 'approve') {
            if ($request['status'] !== 'ADMIN') {
                $_SESSION['flash_error'] = $request['status'] === 'HR_HEAD' ? 'The HR Head has to approve this request first.' : 'This request was already handled.';
            } else {
                [$ok, $message] = $this->requests->createAccount($request, $me, (string) ($_SESSION['username'] ?? 'admin'));
                if ($ok) {
                    $this->logRequest($request, 'approved by the Admin, account created');
                    $msg = 'The Admin approved your request: ' . $request['full_name'] . ' can now sign in with the username ' . $request['username'] . '.';
                    $this->notifyOne((int) $request['requested_by'], $msg, 'success');
                    if (!empty($request['hr_head_by']) && (int) $request['hr_head_by'] !== (int) $request['requested_by']) {
                        $this->notifyOne((int) $request['hr_head_by'], $msg, 'success');
                    }
                    $_SESSION['flash_success'] = $message . ' Give the first password to the person (HR has it).';
                } else {
                    $_SESSION['flash_error'] = $message;
                }
            }
        } elseif ($action === 'reject') {
            if ($note === '') {
                $_SESSION['flash_error'] = 'Please write the reason for rejecting.';
            } elseif (in_array($request['status'], ['HR_HEAD', 'ADMIN'], true)
                && $this->requests->move((int) $request['request_id'], $request['status'], 'REJECTED', ['admin_by' => $me, 'admin_at' => 'NOW()', 'decision_note' => $note])) {
                $this->logRequest($request, 'rejected by the Admin: ' . $note);
                $this->notifyOne((int) $request['requested_by'], 'The Admin rejected your request for ' . $request['full_name'] . ': ' . $note, 'danger');
                $_SESSION['flash_success'] = 'Rejected. HR was notified.';
            } else {
                $_SESSION['flash_error'] = 'This request was already handled.';
            }
        }
        $this->redirect('/admin/user-requests');
    }

    /** POST /admin/user-requests/hr-head : who approves first. */
    public function adminSetHrHead(): void
    {
        $this->requireAdminUser();
        $this->checkCsrf('/admin/user-requests');
        $id = (int) ($_POST['hr_head_account_id'] ?? 0);
        $valid = array_map('intval', array_column($this->requests->hrAccounts(), 'account_id'));
        AppSettings::set(UserRequestModel::HR_HEAD_SETTING, ($id > 0 && in_array($id, $valid, true)) ? (string) $id : null);
        $_SESSION['flash_success'] = 'Saved who approves new user requests as HR Head.';
        $this->redirect('/admin/user-requests');
    }

    /* ======================= helpers ======================= */

    private function renderList(string $area, array $vars): void
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
        }
        $ctx = $this->getLoggedUserContext();
        $base = $ctx['base'];
        $loggedFirstname = $ctx['loggedFirstname'];
        $loggedPosition = $ctx['loggedPosition'];
        $notificationData = $this->loadNotifications();
        $count = $notificationData['count'];
        $notifications = $notificationData['notifications'];
        extract($vars, EXTR_SKIP);
        require __DIR__ . '/../Views/users/user_requests.php';
    }

    private function requireHrUser(): void
    {
        if (empty($_SESSION['account_id'])) {
            $this->redirect('/login');
            exit;
        }
        require_once __DIR__ . '/../Helpers/HrDepartmentAccess.php';
        if (!HrDepartmentAccess::canAccessHr()) {
            $_SESSION['loginMessage'] = 'Access denied. HR only.';
            $this->redirect('/login');
            exit;
        }
    }

    private function requireAdminUser(): void
    {
        if (empty($_SESSION['account_id']) || strtoupper((string) ($_SESSION['usertype'] ?? '')) !== 'ADMIN') {
            $this->redirect('/login');
            exit;
        }
    }

    private function checkCsrf(string $backTo): void
    {
        $posted = (string) ($_POST['csrf_token'] ?? '');
        if ($posted === '' || !hash_equals((string) ($_SESSION['csrf_token'] ?? ''), $posted)) {
            $_SESSION['flash_error'] = 'Invalid form token. Please try again.';
            $this->redirect($backTo);
            exit;
        }
    }

    private function notifyMany(array $accountIds, string $message, string $url, int $requestId): void
    {
        foreach (array_unique($accountIds) as $id) {
            if ((int) $id !== (int) ($_SESSION['account_id'] ?? 0)) {
                $this->notifications->create((int) $id, $message, 'fa-user-plus', 'warning', $url, $requestId);
            }
        }
    }

    private function notifyOne(int $accountId, string $message, string $color): void
    {
        if ($accountId > 0 && $accountId !== (int) ($_SESSION['account_id'] ?? 0)) {
            $this->notifications->create($accountId, $message, 'fa-user-plus', $color, '/hr/user-requests');
        }
    }

    private function logRequest(array $request, string $what): void
    {
        ActivityLogger::update('User requests', (string) $request['request_id'], 'User request for ' . $request['full_name'] . ' ' . $what,
            $_SESSION['username'] ?? 'Unknown', ['username' => $request['username'], 'usertype' => $request['usertype']]);
    }
}
