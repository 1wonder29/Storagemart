<?php
// app/Controllers/AuthController.php

require_once __DIR__ . '/../Helpers/Session.php';
require_once __DIR__ . '/../Helpers/ActivityLogger.php';
require_once __DIR__ . '/../Helpers/LoginThrottle.php';
require_once __DIR__ . '/../Helpers/PasswordPolicy.php';
require_once __DIR__ . '/../Models/admin/Account.php';

class AuthController {

    protected $logFile;
    protected $model;
    protected $base;

    public function __construct() {
        $this->model = new Account();
        $this->logFile = __DIR__ . '/../../app/logs/login_debug.log';
        if (!is_dir(dirname($this->logFile))) {
            @mkdir(dirname($this->logFile), 0755, true);
        }

        // compute base path (e.g. /Storage-Mart-copy/storagemart/public)
        $this->base = BASE_URL;
        if ($this->base === '') $this->base = '/';
    }

    protected function log($msg) {
        $time = date('Y-m-d H:i:s');
        @file_put_contents($this->logFile, "[$time] " . $msg . PHP_EOL, FILE_APPEND);
    }

    // Base-aware redirect helper
    protected function redirect($path) {
        // Use relative redirect to preserve current domain and port
        // This ensures that redirects work correctly whether accessed via localhost, localhost:8000, or any other domain
        if ($this->base === '/' || $this->base === '') {
            // No base path, use relative URL
            header('Location: ' . $path);
        } else {
            // With base path, prepend base
            if ($path[0] === '/') {
                $target = rtrim($this->base, '/') . $path;
            } else {
                $target = rtrim($this->base, '/') . '/' . $path;
            }
            header('Location: ' . $target);
        }
        exit;
    }

    public function show() {
        $loginMessage = $_SESSION['loginMessage'] ?? null;
        unset($_SESSION['loginMessage']);
        require __DIR__ . '/../Views/auth/login.php';
    }

    public function showForgotPassword() {
        if (empty($_SESSION['forgot_csrf'])) {
            $_SESSION['forgot_csrf'] = bin2hex(random_bytes(16));
        }

        $forgotMessage = $_SESSION['forgotMessage'] ?? null;
        $oldUsername = $_SESSION['forgot_old_username'] ?? '';
        $oldEmail = $_SESSION['forgot_old_email'] ?? '';

        unset($_SESSION['forgotMessage'], $_SESSION['forgot_old_username'], $_SESSION['forgot_old_email']);

        require __DIR__ . '/../Views/auth/forgot_password.php';
    }

    /**
     * Forgot password: files a request for an Admin, who verifies the person and
     * sets a temporary password. The page never changes a password itself, and
     * it gives the same answer whether or not the details match an account.
     */
    public function requestPasswordReset() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/forgot-password');
        }

        $csrf = $_POST['csrf_token'] ?? '';
        if (empty($_SESSION['forgot_csrf']) || !hash_equals($_SESSION['forgot_csrf'], $csrf)) {
            $_SESSION['forgotMessage'] = "<span style='color:red'>Invalid form token. Please try again.</span>";
            $this->redirect('/forgot-password');
        }

        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $note = trim($_POST['note'] ?? '');

        $_SESSION['forgot_old_username'] = $username;
        $_SESSION['forgot_old_email'] = $email;

        if ($username === '' || $email === '') {
            $_SESSION['forgotMessage'] = "<span style='color:red'>Please enter your username and registered email.</span>";
            $this->redirect('/forgot-password');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['forgotMessage'] = "<span style='color:red'>Please enter a valid email address.</span>";
            $this->redirect('/forgot-password');
        }

        $account = $this->model->findByUsernameAndEmail($username, $email);
        if ($account) {
            require_once __DIR__ . '/../Models/PasswordResetRequest.php';
            require_once __DIR__ . '/../Models/NotificationModel.php';
            try {
                $requests = new PasswordResetRequest();
                $accountId = (int) $account['account_id'];
                if (!$requests->hasPending($accountId)) {
                    $requestId = $requests->create($accountId, $note, LoginThrottle::clientIp());
                    $notifications = new NotificationModel();
                    foreach ($requests->adminAccountIds() as $adminId) {
                        $notifications->create($adminId, "Password reset requested by {$username}", 'fa-key', 'warning',
                            '/admin/account#reset-requests', $requestId);
                    }
                    ActivityLogger::action('PASSWORD_RESET_REQUEST', 'Authentication', (string) $accountId,
                        "Password reset requested for {$username}", $username);
                }
            } catch (Throwable $e) {
                error_log('requestPasswordReset: ' . $e->getMessage());
            }
        }

        unset($_SESSION['forgot_old_username'], $_SESSION['forgot_old_email']);
        $_SESSION['loginMessage'] = "<span style='color:green'>Request sent. If the username and email match an account, IT will verify "
            . "your identity and give you a temporary password. You can also contact the IT Department directly.</span>";
        $this->redirect('/login');
    }

    /** Signed-in users change their own password (e.g. after IT gives them a temporary one). */
    public function changePassword() {
        if (empty($_SESSION['account_id'])) {
            $_SESSION['loginMessage'] = 'Please log in to change your password.';
            $this->redirect('/login');
        }
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
        }

        $accountId = (int) $_SESSION['account_id'];
        $homePath = $this->homePathFor((string) ($_SESSION['superuser_real_usertype'] ?? $_SESSION['usertype'] ?? ''));
        $changeMessage = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $current = (string) ($_POST['current_password'] ?? '');
            $new = (string) ($_POST['new_password'] ?? '');
            $confirm = (string) ($_POST['confirm_password'] ?? '');
            $account = $this->model->getById($accountId);

            if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], (string) $_POST['csrf_token'])) {
                $changeMessage = 'Invalid form token. Please try again.';
            } elseif (!$account || !$this->passwordMatches($current, (string) $account['password'])) {
                $changeMessage = 'Your current password is incorrect.';
            } elseif ($new !== $confirm) {
                $changeMessage = 'The new passwords do not match.';
            } elseif ($new === $current) {
                $changeMessage = 'Choose a password different from your current one.';
            } elseif (($problem = PasswordPolicy::problem($new)) !== null) {
                $changeMessage = $problem;
            } elseif (!$this->model->updatePasswordByAccountId($accountId, password_hash($new, PASSWORD_DEFAULT))) {
                $changeMessage = 'Could not change your password. Please try again.';
            } else {
                ActivityLogger::action('PASSWORD_CHANGE', 'Authentication', (string) $accountId,
                    'Password changed by the account owner', (string) ($_SESSION['username'] ?? ''));
                $this->redirect('/change-password?done=1');
            }
        }

        $changeDone = isset($_GET['done']);
        require __DIR__ . '/../Views/auth/change_password.php';
    }

    private function passwordMatches(string $password, string $stored): bool {
        if (strpos($stored, '$2y$') === 0 || strpos($stored, '$argon2') === 0) {
            return password_verify($password, $stored);
        }
        return $stored !== '' && hash_equals($stored, $password); // legacy plain-text rows
    }

    /** Landing page for each role (same as after login). */
    protected function homePathFor(string $usertype): string {
        $paths = [
            'EMPLOYEE' => '/employee/dashboard',
            'HEAD'     => '/head/dashboard',
            'ADMIN'    => '/admin',
            'IT'       => '/it/dashboard',
            'HR'       => '/hr/dashboard',
            'AOM'      => '/aom/dashboard',
            'HOM'      => '/hom/dashboard',
            'OM'       => '/om/dashboard',
        ];
        return $paths[strtoupper($usertype)] ?? '/login';
    }

    public function login() {
        $this->log('Entered login() method. METHOD=' . ($_SERVER['REQUEST_METHOD'] ?? 'NA') . ' POST=' . json_encode(array_keys($_POST)));

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->log('Not POST request — redirecting to /login');
            $this->redirect('/login');
        }

        if (!isset($_POST['btnLogin'])) {
            $this->log('btnLogin not set in POST — possible form issue. POST=' . json_encode($_POST));
            $this->redirect('/login');
        }

        $username = trim($_POST['txtUsername'] ?? '');
        $password = $_POST['txtPassword'] ?? '';

        $this->log("Attempt login for username='{$username}'");

        if ($username === '' || $password === '') {
            $_SESSION['loginMessage'] = "<span style='color:red'>Please fill both fields.</span>";
            $this->log('Missing username or password — redirecting back.');
            $this->redirect('/login');
        }

        // Locked after too many wrong passwords from this address (lifts by itself).
        $pdo = $this->model->getPDO();
        $clientIp = LoginThrottle::clientIp();
        $lockedMinutes = LoginThrottle::minutesLeft($pdo, $username, $clientIp);
        if ($lockedMinutes > 0) {
            ActivityLogger::login($username, false, 'Blocked: locked after ' . LoginThrottle::MAX_ATTEMPTS . ' failed attempts');
            $_SESSION['loginMessage'] = "<font color='red'><br>Too many failed attempts. Please try again in {$lockedMinutes} minute"
                . ($lockedMinutes === 1 ? '' : 's') . ", or contact IT.</font>";
            $this->redirect('/login');
        }

        // Secure login: always fetch user by username, then password_verify()
        // (This handles both legacy plain-text rows if any and proper hashed rows.)
        $user = $this->model->findByUsername($username);
        $this->log('findByUsername returned: ' . ($user ? 'FOUND' : 'NOT FOUND'));

        $account = null;
        if ($user) {
            // If stored password looks like a bcrypt hash, use password_verify
            if (!empty($user['password']) && (strpos($user['password'], '$2y$') === 0 || strpos($user['password'], '$argon2') === 0)) {
                $this->log('Stored password appears hashed; using password_verify');
                if (password_verify($password, $user['password'])) {
                    $account = $user;
                    $this->log('password_verify succeeded');
                } else {
                    $this->log('password_verify failed');
                }
            } else {
                // fallback: stored password looks plain text — compare directly (temporary support only)
                $this->log('Stored password appears plain; performing direct compare (legacy)');
                if ($user['password'] === $password) {
                    $account = $user;
                    $this->log('Plaintext compare succeeded');
                } else {
                    $this->log('Plaintext compare failed');
                }
            }
        }

        // ✅ HANDLE FAILED LOGIN ATTEMPTS
        if (!$account) {
            $maxAttempts = LoginThrottle::MAX_ATTEMPTS;
            $failedAttempts = LoginThrottle::recordFailure($pdo, $username, $clientIp);
            $this->model->recordFailedAttempt($username);

            // Log failed login to audit trail
            ActivityLogger::login($username, false, "Invalid credentials (Attempt {$failedAttempts}/{$maxAttempts})");

            if ($failedAttempts >= $maxAttempts) {
                $_SESSION['loginMessage'] = "<font color='red'><br>Too many failed attempts. Login is locked for "
                    . LoginThrottle::LOCK_MINUTES . " minutes. Contact IT if you need access sooner.</font>";
                $this->log("Login locked for {$username} from {$clientIp} — {$failedAttempts} failed attempts");
            } else {
                $_SESSION['loginMessage'] = "<font color='red'><br>Incorrect login details (Attempt {$failedAttempts}/{$maxAttempts})</font>";
                $this->log("Login failed for {$username} — attempt {$failedAttempts}/{$maxAttempts}");
            }
            $this->redirect('/login');
        }

        if (isset($account['status']) && strtolower($account['status']) === "inactive") {
            $_SESSION['loginMessage'] = "<font color='red'><br>Your account is inactive. Please contact admin.</font>";
            $this->log('Account inactive for ' . $username);
            $this->redirect('/login');
        }

        // ✅ RESET FAILED ATTEMPTS ON SUCCESSFUL LOGIN
        LoginThrottle::clear($pdo, $username, $clientIp);
        $this->model->resetFailedAttempts($username);

        // success: set session and redirect
        Session::regenerate();
        $_SESSION['account_id'] = $account['account_id'];
        $_SESSION['username']   = $account['username'];
        $_SESSION['usertype']   = $account['usertype'] ?? '';

        $this->log("Login successful for {$username}; usertype=" . ($_SESSION['usertype'] ?? 'N/A'));
        
        // Log login to audit trail
        ActivityLogger::login($username, true);

        // BASE-AWARE redirects to routes (not to view files)
        switch (strtoupper($_SESSION['usertype'] ?? '')) {
            case 'EMPLOYEE':
                $this->redirect('/employee/dashboard');
                break;
            case 'HEAD':
                $this->redirect('/head/dashboard');
                break;
            case 'ADMIN':
                $this->redirect('/admin');
                break;
            case 'IT':
                $this->redirect('/it/dashboard');
                break;
            case 'HR':
                $this->redirect('/hr/dashboard');
                break;
            case 'AOM':
                $this->redirect('/aom/dashboard');
                break;
            case 'HOM':
                $this->redirect('/hom/dashboard');
                break;
            case 'OM':
                $this->redirect('/om/dashboard');
                break;
            default:
                // Unknown user type - redirect back to login
                $_SESSION['loginMessage'] = 'Unknown user type. Please contact administrator.';
                $this->redirect('/login');
                break;
        }
    }

    public function logout() {
        $username = $_SESSION['username'] ?? 'unknown';
        // Log logout to audit trail before destroying session
        ActivityLogger::logout($username);
        Session::destroy();
        $this->redirect('/login');
    }

    protected function getLoggedUserContext(): array
    {
        $base = $this->base ?? '/';

        // default values from session (fall back to username/usertype)
        $firstname = $_SESSION['firstname'] ?? ($_SESSION['firstname'] ?? '');
        $position  = $_SESSION['position'] ?? ($_SESSION['position'] ?? '');

        // If we already cached nicer values, use them
        if (!empty($_SESSION['display_firstname'])) {
            $firstname = $_SESSION['display_firstname'];
        }
        if (!empty($_SESSION['display_position'])) {
            $position = $_SESSION['display_position'];
        }

        // Try fetching from model once and cache (only if we have account_id and model supports it)
        if (empty($firstname) && !empty($_SESSION['account_id'])) {
            $accountModel = $this->model ?? new Account();
            if (method_exists($accountModel, 'fetchUserDetails')) {
                try {
                    $details = $accountModel->fetchUserDetails((int)$_SESSION['account_id']);
                    if (!empty($details['firstname'])) {
                        $firstname = $details['firstname'];
                        $_SESSION['display_firstname'] = $firstname; // cache
                    }
                    if (!empty($details['position'])) {
                        $position = $details['position'];
                        $_SESSION['display_position'] = $position; // cache
                    }
                } catch (Throwable $e) {
                    // optional: log error, but don't break page
                    // error_log($e->getMessage());
                }
            }
        }

        if ($position === '' && !empty($_SESSION['usertype'])) {
            $position = (string) $_SESSION['usertype'];
        }

        return [
            'base' => $base,
            // names below match how you later read them: loggedFirstname, loggedPosition
            'loggedFirstname' => $firstname,
            'loggedPosition'  => $position,
        ];
    }

    protected function requireAdmin()
    {
        if (empty($_SESSION['account_id'])) {
            $_SESSION['loginMessage'] = 'Please log in to continue.';
            $this->redirect('/login');
        }

        if (strtoupper($_SESSION['usertype'] ?? '') !== 'ADMIN') {
            $_SESSION['loginMessage'] = 'Access denied. Admins only.';
            $this->redirect('/login');
        }
    }

    protected function requireHR()
    {
        if (empty($_SESSION['account_id'])) {
            $_SESSION['loginMessage'] = 'Please log in to continue.';
            $this->redirect('/login');
        }

        require_once __DIR__ . '/../Helpers/HrDepartmentAccess.php';
        if (!HrDepartmentAccess::canAccessHr()) {
            $_SESSION['loginMessage'] = 'Access denied. HR only.';
            $this->redirect('/login');
        }
    }

    protected function loadNotifications(): array
    {
        if (empty($_SESSION['account_id'])) {
            return [
                'count' => 0,
                'notifications' => []
            ];
        }

        require_once __DIR__ . '/../Models/NotificationModel.php';

        $model = new NotificationModel();
        $userId = (int) $_SESSION['account_id'];

        return [
            'count' => $model->getUnreadCount($userId),
            'notifications' => $model->getLatest($userId, 10)
        ];
    }

}
