<?php
    require_once __DIR__ . '/../AuthController.php';
    require_once __DIR__ . '/../../Models/admin/Department.php';
    require_once __DIR__ . '/../../Helpers/Session.php';
    require_once __DIR__ . '/../../Helpers/ActivityLogger.php';

class DepartmentController extends AuthController {

    public function manage() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['account_id']) || strtoupper($_SESSION['usertype'] ?? '') !== 'ADMIN') {
            $this->redirect('/login');
            return;
        }

        $departmentModel = new Department();

        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            if (empty($_SESSION['csrf_token'])) {
                $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
            }
            $csrf_token = $_SESSION['csrf_token'];
            $departments = $departmentModel->fetchAllWithCounts();
            $ctx = $this->getLoggedUserContext();
            $base = $ctx['base'];
            $loggedFirstname = $ctx['loggedFirstname'];
            $loggedPosition  = $ctx['loggedPosition'];
            $notificationData = $this->loadNotifications();
            $count = $notificationData['count'];
            $notifications = $notificationData['notifications'];
            require_once __DIR__ . '/../../Views/admin/department/manage.php';
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo 'Method Not Allowed';
            return;
        }

        if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
            $_SESSION['flash_error'] = 'Invalid CSRF token.';
            $this->redirect('/admin/department');
            return;
        }

        $code  = trim($_POST['code'] ?? '');
        $label = trim($_POST['label'] ?? '');

        if ($code === '' || $label === '') {
            $_SESSION['flash_error'] = 'Both department code and label are required.';
            $this->redirect('/admin/department');
            return;
        }

        if ($departmentModel->codeExists($code)) {
            $_SESSION['flash_error'] = 'A department with that code already exists.';
            $this->redirect('/admin/department');
            return;
        }

        try {
            $id = $departmentModel->addDepartment($code, $label);
            if ($id) {
                ActivityLogger::create('Admin - Departments', (string) $id,
                    "New department added: {$label} ({$code})",
                    $_SESSION['username'] ?? 'Unknown', [
                        'code' => $code,
                        'label' => $label,
                    ]);
                $_SESSION['flash_success'] = 'Department added successfully.';
                $this->redirect('/admin/department');
                return;
            }
            throw new \Exception('Failed to insert department.');
        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = 'Error adding department: ' . $e->getMessage();
            $this->redirect('/admin/department');
            return;
        }
    }

    public function update() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['account_id']) || strtoupper($_SESSION['usertype'] ?? '') !== 'ADMIN') {
            $this->redirect('/login');
            return;
        }

        $departmentModel = new Department();

        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $departmentId = isset($_GET['department_id']) ? (int) $_GET['department_id'] : 0;
            if ($departmentId <= 0) {
                $_SESSION['flash_error'] = 'Invalid department id.';
                $this->redirect('/admin/department');
                return;
            }

            $department = $departmentModel->fetchById($departmentId);
            if (!$department) {
                $_SESSION['flash_error'] = 'Department not found.';
                $this->redirect('/admin/department');
                return;
            }

            if (empty($_SESSION['csrf_token'])) {
                $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
            }
            $csrf_token = $_SESSION['csrf_token'];
            $ctx = $this->getLoggedUserContext();
            $base = $ctx['base'];
            $loggedFirstname = $ctx['loggedFirstname'];
            $loggedPosition  = $ctx['loggedPosition'];
            $notificationData = $this->loadNotifications();
            $count = $notificationData['count'];
            $notifications = $notificationData['notifications'];
            require_once __DIR__ . '/../../Views/admin/department/update.php';
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo 'Method Not Allowed';
            return;
        }

        if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
            $_SESSION['flash_error'] = 'Invalid CSRF token.';
            $this->redirect('/admin/department');
            return;
        }

        $departmentId = isset($_POST['department_id']) ? (int) $_POST['department_id'] : 0;
        $code  = trim($_POST['code'] ?? '');
        $label = trim($_POST['label'] ?? '');

        if ($departmentId <= 0 || $code === '' || $label === '') {
            $_SESSION['flash_error'] = 'Department, code and label are required.';
            $this->redirect('/admin/department');
            return;
        }

        $existing = $departmentModel->fetchById($departmentId);
        if (!$existing) {
            $_SESSION['flash_error'] = 'Department not found.';
            $this->redirect('/admin/department');
            return;
        }

        if ($departmentModel->codeExists($code, $departmentId)) {
            $_SESSION['flash_error'] = 'Another department already uses that code.';
            $this->redirect('/admin/department/update?department_id=' . $departmentId);
            return;
        }

        try {
            // If the code itself changes, existing employees' stored department
            // value needs to move with it so they don't silently fall off the list.
            if ($code !== $existing['code']) {
                $pdo = $departmentModel->getPDO();
                $stmt = $pdo->prepare("UPDATE tblemployee SET department = ? WHERE department = ?");
                $stmt->execute([$code, $existing['code']]);
            }

            $ok = $departmentModel->updateDepartment($departmentId, $code, $label);
            if ($ok) {
                ActivityLogger::update('Admin - Departments', (string) $departmentId,
                    "Department updated: {$label} ({$code})",
                    $_SESSION['username'] ?? 'Unknown', [
                        'department_id' => $departmentId,
                        'code' => $code,
                        'label' => $label,
                    ]);
                $_SESSION['flash_success'] = 'Department updated successfully.';
                $this->redirect('/admin/department');
                return;
            }
            throw new \Exception('No rows updated.');
        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = 'Error updating department: ' . $e->getMessage();
            $this->redirect('/admin/department/update?department_id=' . $departmentId);
            return;
        }
    }

    public function delete() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['account_id']) || strtoupper($_SESSION['usertype'] ?? '') !== 'ADMIN') {
            $_SESSION['flash_error'] = 'Unauthorized access.';
            $this->redirect('/login');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo 'Method Not Allowed';
            return;
        }

        if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
            $_SESSION['flash_error'] = 'Invalid CSRF token.';
            $this->redirect('/admin/department');
            return;
        }

        $departmentId = isset($_POST['department_id']) ? (int) $_POST['department_id'] : 0;
        $moveTo = trim((string) ($_POST['move_to'] ?? ''));
        if ($departmentId <= 0) {
            $_SESSION['flash_error'] = 'Invalid department ID.';
            $this->redirect('/admin/department');
            return;
        }

        $departmentModel = new Department();
        $department = $departmentModel->fetchById($departmentId);
        if (!$department) {
            $_SESSION['flash_error'] = 'Department not found.';
            $this->redirect('/admin/department');
            return;
        }

        $inUse = $departmentModel->countEmployeesUsingCode($department['code']);
        $target = null;
        if ($inUse > 0) {
            $target = $moveTo !== '' ? $departmentModel->fetchByCode($moveTo) : null;
            if (!$target || $target['code'] === $department['code']) {
                $_SESSION['flash_error'] = "{$inUse} employee(s) are still in {$department['label']}. Choose another department to move them to before deleting.";
                $this->redirect('/admin/department');
                return;
            }
        }

        $pdo = $departmentModel->getPDO();
        try {
            $pdo->beginTransaction();
            $moved = $target ? $departmentModel->moveEmployees($department['code'], $target['code']) : 0;
            if (!$departmentModel->deleteDepartment($departmentId)) {
                throw new \Exception('Failed to delete department.');
            }
            $pdo->commit();

            ActivityLogger::delete('Admin - Departments', (string) $departmentId,
                "Department deleted: {$department['label']} ({$department['code']})"
                    . ($target ? " — {$moved} employee(s) moved to {$target['label']}" : ''),
                $_SESSION['username'] ?? 'Unknown', [
                    'department_id' => $departmentId,
                    'code' => $department['code'],
                    'label' => $department['label'],
                    'employees_moved' => $moved,
                    'moved_to' => $target['code'] ?? null,
                ]);
            $_SESSION['flash_success'] = $target
                ? "Department deleted. {$moved} employee(s) moved to {$target['label']}."
                : 'Department deleted successfully.';
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $_SESSION['flash_error'] = 'Error deleting department: ' . $e->getMessage();
        }
        $this->redirect('/admin/department');
    }
}
