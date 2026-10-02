<?php
// app/Models/employee/Ticket.php

require_once __DIR__ . '/../admin/BaseModel.php';

class EmployeeTicket extends BaseModel
{
    protected $tbltickets = 'tbltickets';
    protected $tblemployee = 'tblemployee';
    protected $tblassets = 'tblassets_inventory';
    protected $tblticket_history = 'tblticket_history';
    protected $tblbranch = 'tblbranch';
    protected $tblgroup = 'tblassets_group';

    public function getInventoryDetailsByInventoryId(int $inventoryId): ?array
    {
        $sql = "
            SELECT 
                e.employee_id,
                CONCAT(e.lastname, ', ', e.firstname, ' ', IFNULL(e.middlename, '')) AS fullname,
                e.department,
                b.branch_id,
                b.branchName,
                i.inventory_id,
                i.assetNumber,
                g.group_id,
                CONCAT(g.groupName, ' - ', g.description) AS groupName
            FROM {$this->tblemployee} e
            JOIN {$this->tblassets} i ON e.employee_id = i.employee_id
            JOIN {$this->tblbranch} b ON e.branch_id = b.branch_id
            LEFT JOIN {$this->tblgroup} g ON g.group_id = i.group_id
            WHERE i.inventory_id = :inventory_id
            LIMIT 1
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':inventory_id' => $inventoryId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Create Ticket (Employee-side)
     */
    public function createTicket(array $data): int
    {
        $sql = "
            INSERT INTO {$this->tbltickets} (
                employee_id, inventory_id, branch_id, department, category,
                concern_details, priority, status, created_by, date_filed
            ) VALUES (
                :employee_id, :inventory_id, :branch_id, :department, :category,
                :concern_details, :priority, :status, :created_by, NOW()
            )
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':employee_id'     => $data['employee_id'],
            ':inventory_id'    => $data['inventory_id'],
            ':branch_id'       => $data['branch_id'],
            ':department'      => $data['department'],
            ':category'        => $data['category'],
            ':concern_details' => $data['concern_details'],
            ':priority'        => $data['priority'],
            ':status'          => 'Pending',
            ':created_by'      => $data['created_by'],
        ]);

        $ticketId = (int)$this->pdo->lastInsertId();

        // Generate ticket number
        $ticketNo = $this->generateTicketNumber($ticketId);

        $this->pdo->prepare("
            UPDATE {$this->tbltickets} 
            SET ticket_number = :tn 
            WHERE ticket_id = :id
        ")->execute([
            ':tn' => $ticketNo,
            ':id' => $ticketId
        ]);

        // Insert ticket history
        $this->pdo->prepare("
            INSERT INTO {$this->tblticket_history} 
            (ticket_id, action_type, action_details, old_status, new_status, performed_by, performed_role, date_logged)
            VALUES (:id, 'Created', 'Ticket filed by employee', NULL, 'Pending', :pid, 'Employee', NOW())
        ")->execute([
            ':id'  => $ticketId,
            ':pid' => $data['employee_id']
        ]);

        return $ticketId;
    }

    private function generateTicketNumber(int $id): string
    {
        return 'STM-' . date('Ymd') . '-' . str_pad($id, 4, '0', STR_PAD_LEFT);
    }

    public function fetchAllTicketsByEmployee(int $employeeId): array
    {
        $sql = "
            SELECT 
                t.ticket_id, 
                t.ticket_number, 
                CONCAT(e.lastname, ', ', e.firstname) AS employee_name,
                t.category, 
                t.priority, 
                t.status, 
                t.date_filed, 
                b.branchName,
                t.concern_details
            FROM {$this->tbltickets} t
            JOIN {$this->tblemployee} e ON t.employee_id = e.employee_id
            LEFT JOIN {$this->tblbranch}   b ON e.branch_id = b.branch_id
            WHERE e.employee_id = :employee_id
            ORDER BY t.date_filed DESC
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':employee_id' => $employeeId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function getTicketNumberById(int $ticketId): ?string
    {
        $stmt = $this->pdo->prepare("SELECT ticket_number FROM {$this->tbltickets} WHERE ticket_id = :id LIMIT 1");
        $stmt->execute([':id' => $ticketId]);
        $val = $stmt->fetchColumn();
        return $val !== false ? (string)$val : null;
    }

    public function getTicketHistory(int $ticketId): array
    {
        $sql = "
            SELECT 
                th.action_details,
                CONCAT(e.lastname, ', ', e.firstname) AS performed_by,
                th.old_status,
                th.new_status,
                th.date_logged
            FROM {$this->tblticket_history} th
            LEFT JOIN {$this->tblemployee} e 
                ON th.performed_by = e.employee_id
            WHERE th.ticket_id = :ticket_id
            ORDER BY th.date_logged DESC
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['ticket_id' => $ticketId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
    public function getAssignedTo($ticketId)
    {
        $stmt = $this->pdo->prepare("
            SELECT assigned_to
            FROM {$this->tbltickets}
            WHERE ticket_id = ? LIMIT 1
        ");
        $stmt->execute([(int)$ticketId]);

        return $stmt->fetchColumn();
    }

    public function fetchTicketsByDepartment(string $department): array
    {
        $sql = "
            SELECT 
                t.ticket_id,
                t.ticket_number,
                t.concern_details,
                t.category,
                t.priority,
                t.status,
                t.date_filed,
                b.branchName,
                CONCAT(e.lastname, ', ', e.firstname) AS employee_name
            FROM {$this->tbltickets} t
            INNER JOIN {$this->tblemployee} e 
                ON t.employee_id = e.employee_id
            LEFT JOIN {$this->tblbranch} b
                ON e.branch_id = b.branch_id
            WHERE e.department = ?
            ORDER BY t.date_filed DESC
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$department]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }


}
