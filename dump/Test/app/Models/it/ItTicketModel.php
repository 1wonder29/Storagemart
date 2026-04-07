<?php

require_once __DIR__ . '/../admin/BaseModel.php';

class ItTicketModel extends BaseModel
{
    protected $table = 'tblaccounts';
    protected $tblemployee = 'tblemployee';
    protected $tbltickets = 'tbltickets';
    protected $tblassets = 'tblassets_inventory';
    protected $tblbranch = 'tblbranch';
    protected $tblgroup = 'tblassets_group';
    protected $tbltechnical ='tblticket_technical';
    protected $tblticket_history = 'tblticket_history';

    public function getInProgressTickets(): array
    {
        $sql = "
            SELECT t.*, 
                   CONCAT(e.firstname,' ',e.lastname) AS employee_name,
                   b.branchName,
                   CONCAT(i.assetNumber,' - ', g.groupName) AS asset_info,
                   CONCAT(a2.firstname,' ',a2.lastname) AS assigned_to_name
            FROM tbltickets t
            JOIN tblemployee e ON t.employee_id = e.employee_id
            JOIN tblbranch b ON e.branch_id = b.branch_id
            JOIN tblassets_inventory i ON t.inventory_id = i.inventory_id
            LEFT JOIN tblassets_group g ON i.group_id = g.group_id
            LEFT JOIN tblemployee a2 ON t.assigned_to = a2.employee_id
            WHERE t.status = 'In Progress'
            ORDER BY t.date_filed ASC
        ";

        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAssignedTo(int $ticketId): ?int
    {
        $stmt = $this->pdo->prepare(
            "SELECT assigned_to FROM {$this->tbltickets} WHERE ticket_id = ?"
        );
        $stmt->execute([$ticketId]);
        $val = $stmt->fetchColumn();

        return $val !== null ? (int)$val : null;
    }

    public function updateTicket(int $ticketId, string $status, string $remarks): void
    {
        $stmt = $this->pdo->prepare(
            "UPDATE {$this->tbltickets}
             SET status = ?, remarks = ?, last_updated = NOW()
             WHERE ticket_id = ?"
        );
        $stmt->execute([$status, $remarks, $ticketId]);
    }

    public function getResolvedTechnicalTickets(): array
    {
        $sql = "
            SELECT 
                t.ticket_id,
                t.ticket_number,
                CONCAT(e.lastname, ', ', e.firstname, ' ', LEFT(e.middlename, 1), '.') AS employee_name,
                CONCAT(g.groupName, ' - ', i.itemInfo) AS asset,
                b.branchName,
                tt.technical_purpose,
                tt.action_taken,
                tt.result,
                tt.remarks,
                tt.date_performed
            FROM {$this->tbltickets} t
            JOIN {$this->tbltechnical} tt ON t.ticket_id = tt.ticket_id
            JOIN {$this->tblemployee} e ON t.employee_id = e.employee_id
            JOIN {$this->tblassets} i ON t.inventory_id = i.inventory_id
            LEFT JOIN {$this->tblgroup} g ON i.group_id = g.group_id
            JOIN {$this->tblbranch} b ON e.branch_id = b.branch_id
            WHERE t.status = 'Resolved'
            ORDER BY tt.date_performed DESC
        ";

        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function insertTechnical(array $data): void
    {
        $sql = "
            INSERT INTO {$this->tbltechnical}
            (ticket_id, performed_by, technical_purpose, action_taken, result, remarks, date_performed)
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $data['ticket_id'],
            $data['performed_by'],
            $data['technical_purpose'],
            $data['action_taken'],
            $data['result'],
            $data['remarks']
        ]);
    }

    public function insertHistory(array $data): void
    {
        $sql = "
            INSERT INTO tblticket_history
            (ticket_id, action_type, action_details, old_status, new_status, performed_by, performed_role)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $data['ticket_id'],
            $data['action_type'],
            $data['action_details'],
            $data['old_status'],
            $data['new_status'],
            $data['performed_by'],
            $data['performed_role']
        ]);
    }
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
    public function getEmployeeAccountIdByTicketId(int $ticketId): ?int
    {
        $stmt = $this->pdo->prepare("
            SELECT a.account_id
            FROM tbltickets t
            JOIN tblemployee e ON e.employee_id = t.employee_id
            JOIN tblaccounts a ON a.account_id = e.account_id
            WHERE t.ticket_id = ?
            LIMIT 1
        ");
        $stmt->execute([$ticketId]);

        $accountId = $stmt->fetchColumn();
        return $accountId ? (int)$accountId : null;
    }


}
