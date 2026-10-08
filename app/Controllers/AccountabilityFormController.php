<?php
require_once __DIR__ . '/AuthController.php';
require_once __DIR__ . '/../Models/hr/EmployeeModel.php';
require_once __DIR__ . '/../Models/hr/HRModel.php';
require_once __DIR__ . '/../Services/AccountabilityFormService.php';
require_once __DIR__ . '/../Helpers/HrDepartmentAccess.php';

/**
 * Accountability form builder (options + live preview + PDF/Word download), shared by HR
 * (/hr/employees/accountability/{id}) and Admin (/admin/employees/accountability/{id}).
 */
class AccountabilityFormController extends AuthController
{
    private string $area;

    public function __construct(string $area = 'hr')
    {
        parent::__construct();
        $this->area = $area === 'admin' ? 'admin' : 'hr';
    }

    private function requireAccess(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION['account_id'])) {
            $_SESSION['loginMessage'] = 'Please log in to continue.';
            $this->redirect('/login');
        }
        $allowed = $this->area === 'admin'
            ? strtoupper((string) ($_SESSION['usertype'] ?? '')) === 'ADMIN'
            : HrDepartmentAccess::canAccessHr();
        if (!$allowed) {
            http_response_code(403);
            exit('Unauthorized');
        }
    }

    private function formBase(int $employeeId): string
    {
        return rtrim(BASE_URL, '/') . ($this->area === 'admin' ? '/admin' : '/hr') . '/employees/accountability/' . $employeeId;
    }

    /** @return array{0: array, 1: array, 2: AccountabilityFormService, 3: array} */
    private function load(int $employeeId): array
    {
        $this->requireAccess();
        $options = AccountabilityFormService::options($_GET);
        $employees = new EmployeeModel();
        $employee = $employees->getEmployeeDetail($employeeId);
        if (!$employee) {
            $_SESSION['errorMessage'] = 'Employee not found.';
            $this->redirect($this->area === 'admin' ? '/admin/account' : '/hr/employees');
        }
        $service = new AccountabilityFormService();
        $rows = $service->rows(
            $employees->getAccountabilityAssetItems($employeeId),
            $employees->getAccountabilityUniformItems($employeeId),
            $options
        );
        return [$employee, $rows, $service, $options];
    }

    /** Options page with live preview. */
    public function builder(int $employeeId)
    {
        [$employee, $rows, , $options] = $this->load($employeeId);

        $formBase = $this->formBase($employeeId);
        $sidebarPartial = $this->area;
        $backUrl = rtrim(BASE_URL, '/') . ($this->area === 'admin'
            ? '/admin/assets/view?employee_id=' . $employeeId
            : '/hr/employees/detail/' . $employeeId);
        $ctx = $this->getLoggedUserContext();
        $loggedFirstname = $ctx['loggedFirstname'];
        $loggedPosition = $ctx['loggedPosition'];
        $notificationData = $this->loadNotifications();
        $count = $notificationData['count'];
        $notifications = $notificationData['notifications'];

        require __DIR__ . '/../Views/hr/employees/accountability.php';
    }

    /** The form exactly as it will print (loaded in the preview iframe). */
    public function preview(int $employeeId)
    {
        [$employee, $rows, $service, $options] = $this->load($employeeId);
        header('Content-Type: text/html; charset=UTF-8');
        header('X-Frame-Options: SAMEORIGIN');
        echo $service->html($employee, $rows, $options);
    }

    public function download(int $employeeId)
    {
        [$employee, $rows, $service, $options] = $this->load($employeeId);
        $format = ($_GET['format'] ?? 'pdf') === 'docx' ? 'docx' : 'pdf';

        (new HRModel())->logAction('DOWNLOADED_FORM', $employeeId, null, $_SESSION['account_id'],
            "Downloaded accountability form ({$format}, {$options['preset']}) for: {$employee['firstname']} {$employee['lastname']}");

        $baseName = 'accountability_form_' . $employeeId . '_' . date('YmdHis');
        try {
            if ($format === 'pdf') {
                $pdf = $service->pdf($employee, $rows, $options);
                header('Content-Type: application/pdf');
                header('Content-Disposition: attachment; filename="' . $baseName . '.pdf"');
                header('Content-Length: ' . strlen($pdf));
                header('Cache-Control: no-store');
                echo $pdf;
                exit;
            }

            // Built in the temp folder and deleted after sending (never left in a public web folder).
            $file = $service->docx($employee, $rows, $options, sys_get_temp_dir());
            header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
            header('Content-Disposition: attachment; filename="' . $baseName . '.docx"');
            header('Content-Length: ' . filesize($file));
            header('Cache-Control: no-store');
            readfile($file);
            @unlink($file);
            exit;
        } catch (\Throwable $e) {
            error_log('AccountabilityFormController::download error: ' . $e->getMessage());
            $_SESSION['errorMessage'] = 'Error generating the form: ' . $e->getMessage();
            $this->redirect(($this->area === 'admin' ? '/admin' : '/hr') . '/employees/accountability/' . $employeeId
                . '?' . AccountabilityFormService::query($options));
        }
    }
}
