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
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            if (empty($_SESSION['account_id'])) {
                http_response_code(401);
                echo json_encode(['data' => []]);
                return;
            }

            $employeeId = (int)($_GET['employee_id'] ?? 0);
            if ($employeeId <= 0) {
                echo json_encode(['data' => []]);
                return;
            }

            // ✅ THIS IS THE KEY LINE
            $ticketModel = new EmployeeTicket();

            $rows = $ticketModel->fetchAllTicketsByEmployee($employeeId);

            echo json_encode(['data' => $rows]);

        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode([
                'data'  => [],
                'error' => $e->getMessage()
            ]);
        }
    }

    public function assets()
    {
        header('Content-Type: application/json');

        $employeeId = (int)($_GET['employee_id'] ?? 0);
        $employeeModel = new Employee();

        echo json_encode([
            'data' => $employeeModel->fetchAssetsByEmployeeId($employeeId)
        ]);
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

        echo json_encode([
            'data' => $employeeModel->fetchTicketsByAsset($inventoryId)
        ]);
    }
}

