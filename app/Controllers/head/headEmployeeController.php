<?php
require_once __DIR__ . '/../AuthController.php';
require_once __DIR__ . '/../../Models/employee/Employee.php';
require_once __DIR__ . '/../../Models/employee/Ticket.php';
require_once __DIR__ . '/../../Helpers/Session.php';

class HeadEmployeeController extends AuthController
{
    public function tickets()
    {
        header('Content-Type: application/json');

        try {
            $employeeId = (int)($_GET['employee_id'] ?? 0);
            if ($employeeId <= 0) {
                echo json_encode(['data' => []]);
                return;
            }
            if (!$this->canViewEmployee($employeeId)) {
                $this->deny();
                return;
            }

            $ticketModel = new EmployeeTicket();
            echo json_encode(['data' => $ticketModel->fetchAllTicketsByEmployee($employeeId)]);
        } catch (Throwable $e) {
            error_log('HeadEmployeeController::tickets error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['data' => []]);
        }
    }

    public function assets()
    {
        header('Content-Type: application/json');

        $employeeId = (int)($_GET['employee_id'] ?? 0);
        if (!$this->canViewEmployee($employeeId)) {
            $this->deny();
            return;
        }

        $employeeModel = new Employee();
        echo json_encode(['data' => $employeeModel->fetchAssetsByEmployeeId($employeeId)]);
    }

    public function assetTickets()
    {
        header('Content-Type: application/json');

        $inventoryId = (int)($_GET['inventory_id'] ?? 0);
        if ($inventoryId <= 0) {
            echo json_encode(['data' => []]);
            return;
        }

        $employeeModel = new Employee();
        $stmt = $employeeModel->getPDO()->prepare('SELECT employee_id FROM tblassets_inventory WHERE inventory_id = ? LIMIT 1');
        $stmt->execute([$inventoryId]);
        if (!$this->canViewEmployee((int) $stmt->fetchColumn())) {
            $this->deny();
            return;
        }

        echo json_encode(['data' => $employeeModel->fetchTicketsByAsset($inventoryId)]);
    }

    /**
     * Department heads may look up staff in their own department only; the
     * General Manager (full-access admin acting as Head) may look up anyone.
     */
    private function canViewEmployee(int $employeeId): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if ($employeeId <= 0 || empty($_SESSION['account_id'])
            || strtoupper((string) ($_SESSION['usertype'] ?? '')) !== 'HEAD') {
            return false;
        }

        require_once __DIR__ . '/../../Helpers/SuperUser.php';
        if (SuperUser::isActingInRoleArea()) {
            return true;
        }

        $employeeModel = new Employee();
        $user = $employeeModel->fetchUserDetails((int) $_SESSION['account_id']);
        $head = $user ? $employeeModel->getEmployeeById((int) $user['employee_id']) : null;
        $employee = $employeeModel->getEmployeeById($employeeId);
        $headDepartment = (string) ($head['department'] ?? '');

        return $employee !== null && $headDepartment !== ''
            && strcasecmp((string) ($employee['department'] ?? ''), $headDepartment) === 0;
    }

    private function deny(): void
    {
        http_response_code(403);
        echo json_encode(['data' => []]);
    }
}
