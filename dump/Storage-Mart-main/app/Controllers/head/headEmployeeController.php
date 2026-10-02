<?php
require_once __DIR__ . '/../AuthController.php';
require_once __DIR__ . '/../../Models/employee/Employee.php';
require_once __DIR__ . '/../../Models/employee/Ticket.php';
require_once __DIR__ . '/../../Helpers/Session.php';

class HeadEmployeeController extends AuthController
{
    public function tickets()
    {
        $employeeId = (int)($_GET['employee_id'] ?? 0);

        if ($employeeId <= 0) {
            echo json_encode([]);
            return;
        }

        $ticketModel = new EmployeeTicket();
        echo json_encode(
            $ticketModel->fetchAllTicketsByEmployee($employeeId)
        );
    }

    public function assets()
    {
        $employeeId = (int)($_GET['employee_id'] ?? 0);
        $employeeModel = new Employee();

        echo json_encode(
            $employeeModel->fetchAssetsByEmployeeId($employeeId)
        );
    }

        public function assetTickets()
    {
        $inventoryId = (int)($_GET['inventory_id'] ?? 0);

        if ($inventoryId <= 0) {
            echo json_encode([]);
            return;
        }

        $employeeModel = new Employee();
        echo json_encode(
            $employeeModel->fetchTicketsByAsset($inventoryId)
        );
    }

}
