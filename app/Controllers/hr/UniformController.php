<?php

require_once __DIR__ . '/../AuthController.php';
require_once __DIR__ . '/../../Models/hr/HRModel.php';
require_once __DIR__ . '/../../Models/hr/UniformModel.php';
require_once __DIR__ . '/../../Models/hr/EmployeeModel.php';
require_once __DIR__ . '/../../Models/NotificationModel.php';
require_once __DIR__ . '/../../Helpers/ActivityLogger.php';

/**
 * UniformController - Manages uniform inventory
 */
class UniformController extends AuthController {

    protected $uniformModel;
    protected $employeeModel;
    protected $hrModel;
    protected $notificationModel;

    public function __construct() {
        parent::__construct();
        $this->uniformModel = new UniformModel();
        $this->employeeModel = new EmployeeModel();
        $this->hrModel = new HRModel();
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
     * List all uniforms with pagination
     */
    public function list() {
        $this->requireHR();

        try {
            $page = max(1, (int) ($_GET['page'] ?? 1));
            $limit = 20;
            $offset = ($page - 1) * $limit;

            $uniforms = $this->uniformModel->getAllUniforms($offset, $limit);
            $totalCount = $this->uniformModel->getTotalUniformCount();
            $totalPages = ceil($totalCount / $limit);
            $uniformsNeedingReorder = count($this->uniformModel->getUniformsNeedingReorder());

            require __DIR__ . '/../../Views/hr/uniforms/list.php';
        } catch (\Throwable $e) {
            error_log('UniformController::list error: ' . $e->getMessage());
            $_SESSION['errorMessage'] = 'Error loading uniforms: ' . $e->getMessage();
            $this->redirect('/hr/uniforms');
        }
    }

    /**
     * Show add uniform form
     */
    public function addForm() {
        $this->requireHR();

        try {
            require __DIR__ . '/../../Views/hr/uniforms/form.php';
        } catch (\Throwable $e) {
            error_log('UniformController::addForm error: ' . $e->getMessage());
            $_SESSION['errorMessage'] = 'Error loading form: ' . $e->getMessage();
            $this->redirect('/hr/uniforms');
        }
    }

    /**
     * Process add uniform form
     */
    public function add() {
        $this->requireHR();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/hr/uniforms/add');
        }

        try {
            $data = [
                'uniform_type' => trim($_POST['uniform_type'] ?? ''),
                'size' => trim($_POST['size'] ?? ''),
                'quantity_in_stock' => (int) ($_POST['quantity_in_stock'] ?? 0),
                'reorder_level' => (int) ($_POST['reorder_level'] ?? 5),
                'createdby' => $_SESSION['username'] ?? 'system'
            ];

            // Validate required fields
            if (empty($data['uniform_type']) || empty($data['size'])) {
                $_SESSION['errorMessage'] = 'Please fill in all required fields.';
                $this->redirect('/hr/uniforms/add');
            }

            $uniformId = $this->uniformModel->addUniform($data);

            if ($uniformId) {
                // Log to audit trail via ActivityLogger
                ActivityLogger::create('HR - Uniforms', (string)$uniformId, 
                    "New uniform added: {$data['uniform_type']} - Size {$data['size']}", 
                    $_SESSION['username'] ?? 'system', [
                        'uniform_type' => $data['uniform_type'],
                        'size' => $data['size'],
                        'quantity_in_stock' => $data['quantity_in_stock'],
                        'reorder_level' => $data['reorder_level']
                    ]);
                
                $_SESSION['successMessage'] = 'Uniform added successfully!';
                $this->redirect('/hr/uniforms');
            } else {
                $_SESSION['errorMessage'] = 'Error adding uniform.';
                $this->redirect('/hr/uniforms/add');
            }

        } catch (\Throwable $e) {
            error_log('UniformController::add error: ' . $e->getMessage());
            $_SESSION['errorMessage'] = 'Error adding uniform: ' . $e->getMessage();
            $this->redirect('/hr/uniforms/add');
        }
    }

    /**
     * Show edit uniform form
     */
    public function editForm($uniformId) {
        $this->requireHR();

        try {
            $uniformId = (int) $uniformId;
            $uniform = $this->uniformModel->getUniformById($uniformId);

            if (!$uniform) {
                $_SESSION['errorMessage'] = 'Uniform not found.';
                $this->redirect('/hr/uniforms');
            }

            $isEditing = true;

            require __DIR__ . '/../../Views/hr/uniforms/form.php';
        } catch (\Throwable $e) {
            error_log('UniformController::editForm error: ' . $e->getMessage());
            $_SESSION['errorMessage'] = 'Error loading form: ' . $e->getMessage();
            $this->redirect('/hr/uniforms');
        }
    }

    /**
     * Process edit uniform form
     */
    public function edit($uniformId) {
        $this->requireHR();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/hr/uniforms/edit/' . $uniformId);
        }

        try {
            $uniformId = (int) $uniformId;
            $uniform = $this->uniformModel->getUniformById($uniformId);

            if (!$uniform) {
                $_SESSION['errorMessage'] = 'Uniform not found.';
                $this->redirect('/hr/uniforms');
            }

            $data = [
                'uniform_type' => trim($_POST['uniform_type'] ?? ''),
                'size' => trim($_POST['size'] ?? ''),
                'quantity_in_stock' => (int) ($_POST['quantity_in_stock'] ?? 0),
                'reorder_level' => (int) ($_POST['reorder_level'] ?? 5),
                'updated_by' => $_SESSION['username'] ?? 'system'
            ];

            // Validate required fields
            if (empty($data['uniform_type']) || empty($data['size'])) {
                $_SESSION['errorMessage'] = 'Please fill in all required fields.';
                $this->redirect('/hr/uniforms/edit/' . $uniformId);
            }

            if ($this->uniformModel->updateUniform($uniformId, $data)) {
                // Log to audit trail via ActivityLogger
                ActivityLogger::update('HR - Uniforms', (string)$uniformId, 
                    "Uniform updated: {$data['uniform_type']} - Size {$data['size']}", 
                    $_SESSION['username'] ?? 'system', [
                        'uniform_type' => $data['uniform_type'],
                        'size' => $data['size'],
                        'quantity_in_stock' => $data['quantity_in_stock'],
                        'reorder_level' => $data['reorder_level']
                    ]);
                
                $_SESSION['successMessage'] = 'Uniform updated successfully!';
                $this->redirect('/hr/uniforms');
            } else {
                $_SESSION['errorMessage'] = 'Error updating uniform.';
                $this->redirect('/hr/uniforms/edit/' . $uniformId);
            }

        } catch (\Throwable $e) {
            error_log('UniformController::edit error: ' . $e->getMessage());
            $_SESSION['errorMessage'] = 'Error updating uniform: ' . $e->getMessage();
            $this->redirect('/hr/uniforms/edit/' . $uniformId);
        }
    }

    /**
     * Delete uniform (with confirmation)
     */
    public function deleteConfirm($uniformId) {
        $this->requireHR();

        try {
            $uniformId = (int) $uniformId;
            $uniform = $this->uniformModel->getUniformById($uniformId);

            if (!$uniform) {
                $_SESSION['errorMessage'] = 'Uniform not found.';
                $this->redirect('/hr/uniforms');
            }

            $isInUse = $this->uniformModel->isUniformInUse($uniformId);

            require __DIR__ . '/../../Views/hr/uniforms/delete_confirm.php';
        } catch (\Throwable $e) {
            error_log('UniformController::deleteConfirm error: ' . $e->getMessage());
            $_SESSION['errorMessage'] = 'Error loading confirmation: ' . $e->getMessage();
            $this->redirect('/hr/uniforms');
        }
    }

    /**
     * Process delete uniform
     */
    public function delete($uniformId) {
        $this->requireHR();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/hr/uniforms');
        }

        try {
            $uniformId = (int) $uniformId;
            $uniform = $this->uniformModel->getUniformById($uniformId);

            if (!$uniform) {
                $_SESSION['errorMessage'] = 'Uniform not found.';
                $this->redirect('/hr/uniforms');
            }

            if ($this->uniformModel->deleteUniform($uniformId)) {
                // Log to audit trail via ActivityLogger
                ActivityLogger::delete('HR - Uniforms', (string)$uniformId, 
                    "Uniform deleted: {$uniform['uniform_type']} - Size {$uniform['size']} - Color {$uniform['color']}", 
                    $_SESSION['username'] ?? 'system', [
                        'uniform_id' => $uniformId,
                        'uniform_type' => $uniform['uniform_type'],
                        'size' => $uniform['size'],
                        'color' => $uniform['color'],
                        'quantity_in_stock' => $uniform['quantity_in_stock']
                    ]);
                
                $_SESSION['successMessage'] = 'Uniform deleted successfully!';
                $this->redirect('/hr/uniforms');
            } else {
                $_SESSION['errorMessage'] = 'Error deleting uniform.';
                $this->redirect('/hr/uniforms');
            }

        } catch (\Throwable $e) {
            error_log('UniformController::delete error: ' . $e->getMessage());
            $_SESSION['errorMessage'] = 'Error deleting uniform: ' . $e->getMessage();
            $this->redirect('/hr/uniforms');
        }
    }

    /**
     * Search uniforms
     */
    public function search() {
        $this->requireHR();

        try {
            $searchTerm = trim($_GET['q'] ?? '');
            
            if (empty($searchTerm)) {
                $this->redirect('/hr/uniforms');
            }

            $uniforms = $this->uniformModel->searchUniforms($searchTerm);

            require __DIR__ . '/../../Views/hr/uniforms/search.php';
        } catch (\Throwable $e) {
            error_log('UniformController::search error: ' . $e->getMessage());
            $_SESSION['errorMessage'] = 'Error searching uniforms: ' . $e->getMessage();
            $this->redirect('/hr/uniforms');
        }
    }

    /**
     * Get uniforms needing reorder (AJAX)
     */
    public function getReorderAlerts() {
        $this->requireHR();

        try {
            $uniforms = $this->uniformModel->getUniformsNeedingReorder();
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'data' => $uniforms]);
        } catch (\Throwable $e) {
            error_log('UniformController::getReorderAlerts error: ' . $e->getMessage());
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Show assign uniform to employee form
     */
    public function assignForm() {
        $this->requireHR();

        try {
            $accountId = (int) $_SESSION['account_id'];
            
            // Get list of all employees
            $employees = $this->employeeModel->getAllEmployees(0, 999);
            
            // Get list of uniform types
            $uniformTypes = $this->uniformModel->getUniformTypes();
            
            // Get user's notifications
            $notifications = $this->notificationModel->getLatest($accountId, 10);
            $activePage = 'uniforms';

            require __DIR__ . '/../../Views/hr/uniforms/assign_form.php';
        } catch (\Throwable $e) {
            error_log('UniformController::assignForm error: ' . $e->getMessage());
            $_SESSION['errorMessage'] = 'Error loading assignment form: ' . $e->getMessage();
            $this->redirect('/hr/uniforms');
        }
    }

    /**
     * Get uniforms by type (AJAX for dropdown)
     */
    public function getUniformsByType() {
        $this->requireHR();

        try {
            $uniformType = trim($_GET['type'] ?? '');
            
            if (empty($uniformType)) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Uniform type required']);
                return;
            }

            $uniforms = $this->uniformModel->getUniformsByType($uniformType);
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'data' => $uniforms]);
        } catch (\Throwable $e) {
            error_log('UniformController::getUniformsByType error: ' . $e->getMessage());
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Process uniform assignment to employee
     */
    public function assign() {
        $this->requireHR();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/hr/uniforms/assign');
        }

        try {
            $employeeId = (int) ($_POST['employee_id'] ?? 0);
            $uniformId = (int) ($_POST['uniform_id'] ?? 0);
            $quantityIssued = (int) ($_POST['quantity_issued'] ?? 1);
            $condition = trim($_POST['condition_upon_issue'] ?? 'GOOD');
            $remarks = trim($_POST['remarks'] ?? '');

            if ($employeeId <= 0 || $uniformId <= 0 || $quantityIssued <= 0) {
                $_SESSION['errorMessage'] = 'Invalid form data.';
                $this->redirect('/hr/uniforms/assign');
            }

            // Verify employee exists
            $employee = $this->employeeModel->getEmployeeDetail($employeeId);
            if (!$employee) {
                $_SESSION['errorMessage'] = 'Employee not found.';
                $this->redirect('/hr/uniforms/assign');
            }

            // Verify primary uniform exists and has sufficient stock
            $uniform = $this->uniformModel->getUniformById($uniformId);
            if (!$uniform) {
                $_SESSION['errorMessage'] = 'Uniform not found.';
                $this->redirect('/hr/uniforms/assign');
            }

            if ($uniform['quantity_in_stock'] < $quantityIssued) {
                $_SESSION['errorMessage'] = 'Insufficient uniform stock. Available: ' . $uniform['quantity_in_stock'];
                $this->redirect('/hr/uniforms/assign');
            }

            // Assign primary uniform
            $result = $this->uniformModel->assignUniform(
                $employeeId,
                $uniformId,
                $quantityIssued,
                $condition,
                $remarks,
                $_SESSION['account_id']
            );

            if ($result) {
                // Log action
                $this->hrModel->logAction(
                    'ASSIGNED_UNIFORM',
                    $employeeId,
                    $uniformId,
                    $_SESSION['account_id'],
                    "Assigned {$quantityIssued}x {$uniform['uniform_type']} to {$employee['firstname']} {$employee['lastname']}"
                );
            } else {
                $_SESSION['errorMessage'] = 'Failed to assign uniform.';
                $this->redirect('/hr/uniforms/assign');
            }

            // Process additional specific uniforms
            foreach ($_POST as $key => $value) {
                if (preg_match('/^specific_uniform_id_(\d+)$/', $key, $matches)) {
                    $specificUniformId = (int) $value;
                    $specificQuantity = (int) ($_POST['specific_quantity_' . $matches[1]] ?? 1);

                    if ($specificUniformId <= 0 || $specificQuantity <= 0) {
                        continue;
                    }

                    // Verify uniform exists and has sufficient stock
                    $specificUniform = $this->uniformModel->getUniformById($specificUniformId);
                    if (!$specificUniform) {
                        continue;
                    }

                    if ($specificUniform['quantity_in_stock'] < $specificQuantity) {
                        $_SESSION['warningMessage'] = 'Warning: Insufficient stock for ' . $specificUniform['uniform_type'];
                        continue;
                    }

                    // Assign additional uniform
                    $specificResult = $this->uniformModel->assignUniform(
                        $employeeId,
                        $specificUniformId,
                        $specificQuantity,
                        $condition,
                        $remarks,
                        $_SESSION['account_id']
                    );

                    if ($specificResult) {
                        // Log action
                        $this->hrModel->logAction(
                            'ASSIGNED_UNIFORM',
                            $employeeId,
                            $specificUniformId,
                            $_SESSION['account_id'],
                            "Assigned {$specificQuantity}x {$specificUniform['uniform_type']} to {$employee['firstname']} {$employee['lastname']}"
                        );
                    }
                }
            }

            $_SESSION['successMessage'] = 'Uniforms assigned successfully!';
            $this->redirect('/hr/uniforms/assign');
        } catch (\Throwable $e) {
            error_log('UniformController::assign error: ' . $e->getMessage());
            $_SESSION['errorMessage'] = 'Error assigning uniform: ' . $e->getMessage();
            $this->redirect('/hr/uniforms/assign');
        }
    }

    /**
     * Show return confirmation for an assignment
     */
    public function returnConfirm($assignmentId) {
        $this->requireHR();

        try {
            $assignmentId = (int) $assignmentId;
            $assignment = $this->uniformModel->getAssignmentById($assignmentId);

            if (!$assignment) {
                $_SESSION['errorMessage'] = 'Assignment not found.';
                $this->redirect('/hr/employees');
            }

            $notifications = $this->notificationModel->getLatest($_SESSION['account_id'] ?? 0, 10);
            require __DIR__ . '/../../Views/hr/uniforms/return_confirm.php';
        } catch (\Throwable $e) {
            error_log('UniformController::returnConfirm error: ' . $e->getMessage());
            $_SESSION['errorMessage'] = 'Error loading return confirmation: ' . $e->getMessage();
            $this->redirect('/hr/employees');
        }
    }

    /**
     * Process return of an assignment (POST)
     */
    public function processReturn($assignmentId) {
        $this->requireHR();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/hr/employees');
        }

        try {
            $assignmentId = (int) $assignmentId;
            $assignment = $this->uniformModel->getAssignmentById($assignmentId);

            if (!$assignment) {
                $_SESSION['errorMessage'] = 'Assignment not found.';
                $this->redirect('/hr/employees');
            }

            $result = $this->uniformModel->returnAssignment($assignmentId, (int) ($_SESSION['account_id'] ?? 0));

            if ($result) {
                $this->hrModel->logAction('RETURNED_UNIFORM', $assignment['employee_id'] ?? null, $assignment['uniform_id'] ?? null, $_SESSION['account_id'] ?? 0,
                    "Returned assignment: {$assignmentId}");
                $_SESSION['successMessage'] = 'Uniform returned successfully.';
            } else {
                $_SESSION['errorMessage'] = 'Failed to process return.';
            }

            // Redirect back to employee detail if possible
            $employeeId = (int) ($assignment['employee_id'] ?? 0);
            if ($employeeId > 0) {
                $this->redirect('/hr/employees/detail/' . $employeeId);
            } else {
                $this->redirect('/hr/uniforms');
            }
        } catch (\Throwable $e) {
            error_log('UniformController::return error: ' . $e->getMessage());
            $_SESSION['errorMessage'] = 'Error processing return: ' . $e->getMessage();
            $this->redirect('/hr/employees');
        }
    }
}
