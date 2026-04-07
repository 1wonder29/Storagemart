<?php
// app/Controllers/AuthController.php

require_once __DIR__ . '/../Helpers/Session.php';
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
        // if $path already starts with '/', append to base
        if ($path[0] === '/') {
            $target = rtrim($this->base, '/') . $path;
        } else {
            // relative path: join with base
            $target = rtrim($this->base, '/') . '/' . $path;
        }
        header('Location: ' . $target);
        exit;
    }

    public function show() {
        $loginMessage = $_SESSION['loginMessage'] ?? null;
        unset($_SESSION['loginMessage']);
        require __DIR__ . '/../Views/auth/login.php';
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


        if (!$account) {
            $_SESSION['loginMessage'] = "<font color='red'><br>Incorrect login details</font>";
            $this->log('Login failed for ' . $username . ' — redirecting back to /login');
            $this->redirect('/login');
        }

        if (isset($account['status']) && strtolower($account['status']) === "inactive") {
            $_SESSION['loginMessage'] = "<font color='red'><br>Your account is inactive. Please contact admin.</font>";
            $this->log('Account inactive for ' . $username);
            $this->redirect('/login');
        }

        // success: set session and redirect
        Session::regenerate();
        $_SESSION['account_id'] = $account['account_id'];
        $_SESSION['username']   = $account['username'];
        $_SESSION['usertype']   = $account['usertype'] ?? '';

        $this->log("Login successful for {$username}; usertype=" . ($_SESSION['usertype'] ?? 'N/A'));

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
            default:
                $this->redirect('/accounts');
                break;
        }
    }

    public function logout() {
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
