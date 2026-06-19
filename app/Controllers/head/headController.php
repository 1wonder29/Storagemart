<?php

require_once __DIR__ . '/../AuthController.php';
require_once __DIR__ . '/../../Models/employee/Employee.php';
require_once __DIR__ . '/../../Helpers/Session.php';
require_once __DIR__ . '/../../Models/head/headModel.php';
require_once __DIR__ . '/../../Models/employee/Ticket.php';
class HeadController extends AuthController
{
    public function dashboard()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (empty($_SESSION['account_id'])) {
            $this->redirect('/login');
            return;
        }

        $employeeModel = new Employee();

        // 🔐 Auth
        $user = $employeeModel->fetchUserDetails((int)$_SESSION['account_id']);
        if (!$user || strtoupper($user['usertype']) !== 'HEAD') {
            http_response_code(403);
            exit('Unauthorized');
        }

        // 🧑 HEAD record
        $headEmployee = $employeeModel->getEmployeeById((int)$user['employee_id']);
        $department   = $headEmployee['department'] ?? null;

        if (!$department) {
            $_SESSION['flash_error'] = 'Department not found.';
            $this->redirect('/login');
            return;
        }

        /* ===========================
           INITIALIZE COUNTERS
        =========================== */
        $totalAssets               = 0;
        $totalTickets              = 0;
        $pendingTickets            = 0;
        $resolvedTickets           = 0;

        $departmentAssets          = 0;
        $totalDepartmentTickets    = 0;
        $pendingDepartmentTickets  = 0;
        $resolvedDepartmentTickets = 0;

        /* ===========================
           HEAD PERSONAL TOTALS
        =========================== */
        $headId = (int)$headEmployee['employee_id'];

        $totalAssets    = $employeeModel->countAssetsByEmployee($headId);
        $totalTickets   = $employeeModel->countTicketsByEmployee($headId);
        $pendingTickets = $employeeModel->countTicketsByEmployee($headId, 'Pending');
        $resolvedTickets= $employeeModel->countTicketsByEmployee($headId, 'Resolved');

        /* ===========================
           DEPARTMENT TOTALS
        =========================== */
        $employees = $employeeModel->fetchDepartmentStaffForHead($department, $headId);

        foreach ($employees as $emp) {
            $eid = (int)$emp['employee_id'];

            $departmentAssets          += $employeeModel->countAssetsByEmployee($eid);
            $totalDepartmentTickets    += $employeeModel->countTicketsByEmployee($eid);
            $pendingDepartmentTickets  += $employeeModel->countTicketsByEmployee($eid, 'Pending');
            $resolvedDepartmentTickets += $employeeModel->countTicketsByEmployee($eid, 'Resolved');
        }

        // Layout
        $notificationData = $this->loadNotifications();
        $count = $notificationData['count'];
        $notifications = $notificationData['notifications'];

        $ctx = $this->getLoggedUserContext();
        $base = $ctx['base'];
        $loggedFirstname = $ctx['loggedFirstname'];
        $loggedPosition  = $ctx['loggedPosition'];

        $ticketModel = new EmployeeTicket();
        $tickets = $ticketModel->fetchTicketsByDepartment($department);

        require __DIR__ . '/../../Views/head/dashboard/dashboard.php';
    }



    public function department()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (empty($_SESSION['account_id'])) {
            $this->redirect('/login');
            return;
        }

        $employeeModel = new Employee();
        $user = $employeeModel->fetchUserDetails((int)$_SESSION['account_id']);

        if (!$user || strtoupper($user['usertype']) !== 'HEAD') {
            http_response_code(403);
            exit('Unauthorized');
        }

        $headEmployee = $employeeModel->getEmployeeById((int)$user['employee_id']);
        $department = $headEmployee['department'] ?? null;

        if (!$department) {
            $_SESSION['flash_error'] = 'Department not found.';
            $this->redirect('/head/dashboard');
            return;
        }

        // 👥 Employees under this HEAD
        $employees = $employeeModel->fetchDepartmentStaffForHead(
            $department,
            (int) ($headEmployee['employee_id'] ?? 0)
        );

        // Layout context
        $ctx = $this->getLoggedUserContext();
        $base = $ctx['base'];
        $loggedFirstname = $ctx['loggedFirstname'];
        $loggedPosition  = $ctx['loggedPosition'];

        $notificationData = $this->loadNotifications();
        $count = $notificationData['count'];
        $notifications = $notificationData['notifications'];

        require __DIR__ . '/../../Views/head/department/employee.php';
    }

    public function profile()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (empty($_SESSION['account_id']) || strtoupper($_SESSION['usertype'] ?? '') !== 'HEAD') {
            $_SESSION['loginMessage'] = 'Please log in as head user.';
            $this->redirect('/login');
            return;
        }

        $employeeModel = new Employee();
        $profile = $employeeModel->fetchProfileByAccountId((int)$_SESSION['account_id']) ?? [];

        $ctx = $this->getLoggedUserContext();
        $base = $ctx['base'];
        $loggedFirstname = $ctx['loggedFirstname'];
        $loggedPosition  = $ctx['loggedPosition'];

        $notificationData = $this->loadNotifications();
        $count = $notificationData['count'];
        $notifications = $notificationData['notifications'];

        require __DIR__ . '/../../Views/head/profile/profile.php';
    }

}
