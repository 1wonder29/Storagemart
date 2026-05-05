<?php

require_once __DIR__ . '/../AuthController.php';
require_once __DIR__ . '/../../Models/hr/HRModel.php';
require_once __DIR__ . '/../../Models/hr/EmployeeModel.php';
require_once __DIR__ . '/../../Models/hr/UniformModel.php';
require_once __DIR__ . '/../../Models/NotificationModel.php';

/**
 * HrController - Manages HR Dashboard and Employee accountability
 */
class HrController extends AuthController {

    protected $hrModel;
    protected $employeeModel;
    protected $uniformModel;
    protected $notificationModel;

    public function __construct() {
        parent::__construct();
        $this->hrModel = new HRModel();
        $this->employeeModel = new EmployeeModel();
        $this->uniformModel = new UniformModel();
        $this->notificationModel = new NotificationModel();
    }

    /**
     * Check if user is HR role
     */
    protected function requireHR() {
        if (empty($_SESSION['account_id'])) {
            $_SESSION['loginMessage'] = 'Please log in to continue.';
            $this->redirect('/login');
        }

        if (strtoupper($_SESSION['usertype'] ?? '') !== 'HR') {
            $_SESSION['loginMessage'] = 'Access denied. HR only.';
            $this->redirect('/login');
        }
    }

    /**
     * HR Dashboard
     */
    public function dashboard() {
        $this->requireHR();

        try {
            $accountId = (int) $_SESSION['account_id'];
            
            // Get dashboard stats
            $totalEmployees = $this->employeeModel->getTotalEmployeeCount();
            $uniformStats = $this->uniformModel->getAssignmentStats();
            $uniformsNeedingReorder = count($this->uniformModel->getUniformsNeedingReorder());
            $recentLogs = $this->hrModel->getRecentLogs(7);
            $notifications = $this->notificationModel->getLatest($accountId, 10);

            require __DIR__ . '/../../Views/hr/dashboard.php';
        } catch (\Throwable $e) {
            error_log('HrController::dashboard error: ' . $e->getMessage());
            $_SESSION['errorMessage'] = 'Error loading dashboard: ' . $e->getMessage();
            $this->redirect('/login');
        }
    }

    /**
     * List all employees with pagination
     */
    public function employees() {
        $this->requireHR();

        try {
            $page = max(1, (int) ($_GET['page'] ?? 1));
            $limit = 20;
            $offset = ($page - 1) * $limit;

            $employees = $this->employeeModel->getAllEmployees($offset, $limit);
            $totalCount = $this->employeeModel->getTotalEmployeeCount();
            $totalPages = ceil($totalCount / $limit);

            require __DIR__ . '/../../Views/hr/employees/list.php';
        } catch (\Throwable $e) {
            error_log('HrController::employees error: ' . $e->getMessage());
            $_SESSION['errorMessage'] = 'Error loading employees: ' . $e->getMessage();
            $this->redirect('/hr/dashboard');
        }
    }

    /**
     * View employee details with assets and uniforms
     */
    public function employeeDetail($employeeId) {
        $this->requireHR();

        try {
            $employeeId = (int) $employeeId;
            $employee = $this->employeeModel->getEmployeeDetail($employeeId);
            
            if (!$employee) {
                $_SESSION['errorMessage'] = 'Employee not found.';
                $this->redirect('/hr/employees');
            }

            $assets = $this->employeeModel->getEmployeeAssets($employeeId);
            $uniforms = $this->employeeModel->getEmployeeCurrentUniforms($employeeId);
            $uniformHistory = $this->employeeModel->getEmployeeUniforms($employeeId);
            $notifications = $this->notificationModel->getLatest($_SESSION['account_id'], 10);

            // Log view action
            $this->hrModel->logAction('VIEWED_EMPLOYEE', $employeeId, null, $_SESSION['account_id'], 
                "Viewed employee: {$employee['firstname']} {$employee['lastname']}");

            require __DIR__ . '/../../Views/hr/employees/detail.php';
        } catch (\Throwable $e) {
            error_log('HrController::employeeDetail error: ' . $e->getMessage());
            $_SESSION['errorMessage'] = 'Error loading employee details: ' . $e->getMessage();
            $this->redirect('/hr/employees');
        }
    }

    /**
     * Search employees
     */
    public function searchEmployees() {
        $this->requireHR();

        try {
            $searchTerm = trim($_GET['q'] ?? '');
            
            if (empty($searchTerm)) {
                $this->redirect('/hr/employees');
            }

            $employees = $this->employeeModel->searchEmployees($searchTerm);

            require __DIR__ . '/../../Views/hr/employees/search.php';
        } catch (\Throwable $e) {
            error_log('HrController::searchEmployees error: ' . $e->getMessage());
            $_SESSION['errorMessage'] = 'Error searching employees: ' . $e->getMessage();
            $this->redirect('/hr/employees');
        }
    }

    /**
     * Download accountability form as PDF
     */
    public function downloadAccountabilityForm($employeeId) {
        $this->requireHR();

        try {
            $employeeId = (int) $employeeId;
            $employee = $this->employeeModel->getEmployeeDetail($employeeId);
            
            if (!$employee) {
                $_SESSION['errorMessage'] = 'Employee not found.';
                $this->redirect('/hr/employees');
            }

            $assets = $this->employeeModel->getEmployeeAssets($employeeId);
            $uniforms = $this->employeeModel->getEmployeeCurrentUniforms($employeeId);

            // Log download action
            $this->hrModel->logAction('DOWNLOADED_FORM', $employeeId, null, $_SESSION['account_id'], 
                "Downloaded accountability form for: {$employee['firstname']} {$employee['lastname']}");

            // Generate PDF
            $this->generateAccountabilityFormPDF($employee, $assets, $uniforms);

        } catch (\Throwable $e) {
            error_log('HrController::downloadAccountabilityForm error: ' . $e->getMessage());
            $_SESSION['errorMessage'] = 'Error generating form: ' . $e->getMessage();
            $this->redirect('/hr/employees');
        }
    }

    /**
     * Generate PDF accountability form
     */
    protected function generateAccountabilityFormPDF($employee, $assets, $uniforms) {
        require __DIR__ . '/../../Services/PdfGeneratorService.php';
        
        try {
            $pdfService = new PdfGeneratorService();
            $pdfService->generateAccountabilityForm($employee, $assets, $uniforms);
        } catch (\Throwable $e) {
            error_log('PDF Generation error: ' . $e->getMessage());
            $_SESSION['errorMessage'] = 'Error generating PDF: ' . $e->getMessage();
            $this->redirect('/hr/employees');
        }
    }
}
