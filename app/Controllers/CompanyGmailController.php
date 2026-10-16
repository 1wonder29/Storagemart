<?php

require_once __DIR__ . '/AuthController.php';
require_once __DIR__ . '/../Models/CompanyGmailModel.php';
require_once __DIR__ . '/../Helpers/ActivityLogger.php';

/**
 * Company Gmail page of one employee, for the Admin and HR:
 *   GET  /admin/company-gmail?employee_id=N   (and /hr/company-gmail?employee_id=N)
 *   POST /admin/company-gmail/save            save address, Gmail password and checklist
 *   POST /admin/company-gmail/reveal          JSON: the saved Gmail password (logged in the Audit Trail)
 */
class CompanyGmailController extends AuthController
{
    private CompanyGmailModel $gmail;

    public function __construct()
    {
        parent::__construct();
        $this->gmail = new CompanyGmailModel();
    }

    public function showPage(string $area): void
    {
        $this->requireArea($area);
        $employeeId = (int) ($_GET['employee_id'] ?? 0);
        $employee = $employeeId > 0 ? $this->gmail->employee($employeeId) : null;
        if (!$employee) {
            $_SESSION['flash_error'] = 'Employee not found.';
            $this->redirect($area === 'admin' ? '/admin/account' : '/hr/employees');
            return;
        }
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
        }
        $ready = $this->gmail->ready();
        $gmail = $employee['gmail'] ?? null;
        $suggested = CompanyGmailModel::suggestEmail((string) $employee['firstname'], (string) $employee['lastname']);
        $email = (string) ($gmail['email'] ?? '');
        if ($email === '') {
            $email = preg_match('/@gmail\.com$/i', (string) $employee['email']) ? (string) $employee['email'] : $suggested;
        }
        $updatedBy = $this->gmail->updatedByName(isset($gmail['updated_by']) ? (int) $gmail['updated_by'] : null);
        $sameEmail = $this->gmail->othersWithEmail($email, $employeeId);
        $ctx = $this->getLoggedUserContext();
        $base = $ctx['base'];
        $loggedFirstname = $ctx['loggedFirstname'];
        $loggedPosition = $ctx['loggedPosition'];
        $notificationData = $this->loadNotifications();
        $count = $notificationData['count'];
        $notifications = $notificationData['notifications'];
        $checks = CompanyGmailModel::CHECKS;
        require __DIR__ . '/../Views/users/company_gmail.php';
    }

    public function savePage(string $area): void
    {
        $this->requireArea($area);
        $employeeId = (int) ($_POST['employee_id'] ?? 0);
        $page = '/' . $area . '/company-gmail?employee_id=' . $employeeId;
        $this->checkCsrf($page);
        $employee = $employeeId > 0 ? $this->gmail->employee($employeeId) : null;
        if (!$employee) {
            $_SESSION['flash_error'] = 'Employee not found.';
            $this->redirect($page);
            return;
        }
        $password = (string) ($_POST['gmail_password'] ?? '');
        $error = $this->gmail->save($employeeId, (string) ($_POST['gmail_email'] ?? ''), $password !== '' ? $password : null,
            (array) ($_POST['checks'] ?? []), (string) ($_POST['notes'] ?? ''), (int) $_SESSION['account_id']);
        if ($error !== null) {
            $_SESSION['flash_error'] = $error;
        } else {
            $name = trim($employee['firstname'] . ' ' . $employee['lastname']);
            ActivityLogger::update('Company Gmail', (string) $employeeId,
                'Company Gmail of ' . $name . ' saved' . ($password !== '' ? ' (Gmail password changed)' : ''),
                $_SESSION['username'] ?? 'Unknown', ['employee_id' => $employeeId, 'email' => (string) ($_POST['gmail_email'] ?? '')]);
            $_SESSION['flash_success'] = 'Company Gmail details saved.';
        }
        $this->redirect($page);
    }

    public function revealPassword(string $area): void
    {
        header('Content-Type: application/json; charset=utf-8');
        if (!$this->allowed($area)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Not allowed.']);
            return;
        }
        $posted = (string) ($_POST['csrf_token'] ?? '');
        if ($posted === '' || !hash_equals((string) ($_SESSION['csrf_token'] ?? ''), $posted)) {
            echo json_encode(['success' => false, 'message' => 'Please reload the page and try again.']);
            return;
        }
        $employeeId = (int) ($_POST['employee_id'] ?? 0);
        $password = $employeeId > 0 ? $this->gmail->password($employeeId) : null;
        if ($password === null) {
            echo json_encode(['success' => false, 'message' => 'No Gmail password is saved (or it can no longer be read). Type it again and save.']);
            return;
        }
        ActivityLogger::update('Company Gmail', (string) $employeeId, 'Viewed the company Gmail password of employee ' . $employeeId,
            $_SESSION['username'] ?? 'Unknown', ['employee_id' => $employeeId]);
        echo json_encode(['success' => true, 'password' => $password]);
    }

    private function allowed(string $area): bool
    {
        if (empty($_SESSION['account_id'])) {
            return false;
        }
        $type = strtoupper((string) ($_SESSION['usertype'] ?? ''));
        return ($area === 'admin' && $type === 'ADMIN') || ($area === 'hr' && $type === 'HR');
    }

    private function requireArea(string $area): void
    {
        if (!$this->allowed($area)) {
            $_SESSION['loginMessage'] = 'Access denied.';
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
}
