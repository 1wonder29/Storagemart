<?php

require_once __DIR__ . '/../admin/BaseModel.php';
require_once __DIR__ . '/../../Helpers/TicketStatus.php';

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

    public function getInProgressTickets(int $assignedToEmployeeId = 0): array
    {
        $this->autoCloseResolvedTickets();

        $sql = "
            SELECT t.*, 
                   CONCAT(e.firstname,' ',e.lastname) AS employee_name,
                   b.branchName,
                   CONCAT(IFNULL(i.assetNumber, 'N/A'),' - ', IFNULL(g.groupName, 'General')) AS asset_info,
                   CONCAT(a2.firstname,' ',a2.lastname) AS assigned_to_name
            FROM tbltickets t
            JOIN tblemployee e ON t.employee_id = e.employee_id
            JOIN tblbranch b ON b.branch_id = COALESCE(NULLIF(t.branch_id, 0), e.branch_id)
            LEFT JOIN tblassets_inventory i ON t.inventory_id = i.inventory_id
            LEFT JOIN tblassets_group g ON i.group_id = g.group_id
            LEFT JOIN tblemployee a2 ON t.assigned_to = a2.employee_id
            WHERE t.status = 'In Progress'
        ";
        
        // If employee ID provided, filter to tickets assigned to that employee
        if ($assignedToEmployeeId > 0) {
            $sql .= " AND t.assigned_to = :assigned_to";
        }
        
        $sql .= " ORDER BY t.date_filed ASC";

        $stmt = $this->pdo->prepare($sql);
        $params = [];
        if ($assignedToEmployeeId > 0) {
            $params[':assigned_to'] = $assignedToEmployeeId;
        }
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function getOpenTickets(int $assignedToEmployeeId = 0): array
    {
        $this->autoCloseResolvedTickets();

        $sql = "
            SELECT t.*,
                   CONCAT(e.firstname,' ',e.lastname) AS employee_name,
                   b.branchName,
                   CONCAT(IFNULL(i.assetNumber, 'N/A'),' - ', IFNULL(g.groupName, 'General')) AS asset_info,
                   CONCAT(a2.firstname,' ',a2.lastname) AS assigned_to_name
            FROM tbltickets t
            JOIN tblemployee e ON t.employee_id = e.employee_id
            JOIN tblbranch b ON b.branch_id = COALESCE(NULLIF(t.branch_id, 0), e.branch_id)
            LEFT JOIN tblassets_inventory i ON t.inventory_id = i.inventory_id
            LEFT JOIN tblassets_group g ON i.group_id = g.group_id
            LEFT JOIN tblemployee a2 ON t.assigned_to = a2.employee_id
            WHERE t.status = :status
        ";

        if ($assignedToEmployeeId > 0) {
            $sql .= " AND t.assigned_to = :assigned_to";
        }

        $sql .= " ORDER BY t.date_filed ASC";

        $stmt = $this->pdo->prepare($sql);
        $params = [':status' => TicketStatus::OPEN];
        if ($assignedToEmployeeId > 0) {
            $params[':assigned_to'] = $assignedToEmployeeId;
        }
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function getClosedTickets(int $assignedToEmployeeId = 0): array
    {
        $this->autoCloseResolvedTickets();

        $sql = "
            SELECT t.*,
                   CONCAT(e.firstname,' ',e.lastname) AS employee_name,
                   b.branchName,
                   CONCAT(IFNULL(i.assetNumber, 'N/A'),' - ', IFNULL(g.groupName, 'General')) AS asset_info,
                   CONCAT(a2.firstname,' ',a2.lastname) AS assigned_to_name
            FROM tbltickets t
            JOIN tblemployee e ON t.employee_id = e.employee_id
            JOIN tblbranch b ON b.branch_id = COALESCE(NULLIF(t.branch_id, 0), e.branch_id)
            LEFT JOIN tblassets_inventory i ON t.inventory_id = i.inventory_id
            LEFT JOIN tblassets_group g ON i.group_id = g.group_id
            LEFT JOIN tblemployee a2 ON t.assigned_to = a2.employee_id
            WHERE t.status = :status
        ";

        if ($assignedToEmployeeId > 0) {
            $sql .= " AND t.assigned_to = :assigned_to";
        }

        $sql .= " ORDER BY t.last_updated DESC, t.date_filed DESC";

        $stmt = $this->pdo->prepare($sql);
        $params = [':status' => TicketStatus::CLOSED];
        if ($assignedToEmployeeId > 0) {
            $params[':assigned_to'] = $assignedToEmployeeId;
        }
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function getPendingTickets(int $assignedToEmployeeId = 0): array
    {
        $this->autoCloseResolvedTickets();

        $sql = "
            SELECT t.*, 
                   CONCAT(e.firstname,' ',e.lastname) AS employee_name,
                   b.branchName,
                   CONCAT(IFNULL(i.assetNumber, 'N/A'),' - ', IFNULL(g.groupName, 'General')) AS asset_info,
                   CONCAT(a2.firstname,' ',a2.lastname) AS assigned_to_name
            FROM tbltickets t
            JOIN tblemployee e ON t.employee_id = e.employee_id
            JOIN tblbranch b ON b.branch_id = COALESCE(NULLIF(t.branch_id, 0), e.branch_id)
            LEFT JOIN tblassets_inventory i ON t.inventory_id = i.inventory_id
            LEFT JOIN tblassets_group g ON i.group_id = g.group_id
            LEFT JOIN tblemployee a2 ON t.assigned_to = a2.employee_id
            WHERE t.status = :open_status
        ";

        if ($assignedToEmployeeId > 0) {
            $sql .= " AND t.assigned_to = :assigned_to";
        }

        $sql .= " ORDER BY t.date_filed ASC";

        $stmt = $this->pdo->prepare($sql);
        $params = [':open_status' => TicketStatus::PENDING];
        if ($assignedToEmployeeId > 0) {
            $params[':assigned_to'] = $assignedToEmployeeId;
        }
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
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

    /**
     * @return array<string, int>
     */
    public function getTicketStatusCounts(): array
    {
        $this->autoCloseResolvedTickets();

        $counts = [];
        foreach (TicketStatus::all() as $status) {
            $counts[$status] = 0;
        }

        $stmt = $this->pdo->query("
            SELECT status, COUNT(*) AS total
            FROM {$this->tbltickets}
            GROUP BY status
        ");

        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) ?: [] as $row) {
            $status = (string) ($row['status'] ?? '');
            if ($status !== '') {
                $counts[$status] = (int) ($row['total'] ?? 0);
            }
        }

        $summary = [];
        foreach (TicketStatus::all() as $status) {
            $summary[$status] = (int) ($counts[$status] ?? 0);
        }

        return $summary;
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
        $this->autoCloseResolvedTickets();

        $sql = "
            SELECT 
                t.ticket_id,
                t.ticket_number,
                CONCAT(e.lastname, ', ', e.firstname, ' ', LEFT(IFNULL(e.middlename, ''), 1), '.') AS employee_name,
                CONCAT(IFNULL(g.groupName, 'N/A'), ' - ', IFNULL(i.itemInfo, 'N/A')) AS asset,
                b.branchName,
                tt.technical_purpose,
                tt.action_taken,
                tt.result,
                tt.remarks,
                tt.date_performed
            FROM {$this->tbltickets} t
            LEFT JOIN {$this->tbltechnical} tt ON t.ticket_id = tt.ticket_id
            JOIN {$this->tblemployee} e ON t.employee_id = e.employee_id
            LEFT JOIN {$this->tblassets} i ON t.inventory_id = i.inventory_id
            LEFT JOIN {$this->tblgroup} g ON i.group_id = g.group_id
            JOIN {$this->tblbranch} b ON b.branch_id = COALESCE(NULLIF(t.branch_id, 0), e.branch_id)
            WHERE t.status = 'Resolved'
            ORDER BY COALESCE(tt.date_performed, t.last_updated) DESC
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
            ':status'          => TicketStatus::initial(),
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
            VALUES (:id, 'Created', 'Ticket filed by employee', NULL, :new_status, :pid, 'Employee', NOW())
        ")->execute([
            ':id'  => $ticketId,
            ':new_status' => TicketStatus::initial(),
            ':pid' => $data['employee_id']
        ]);

        return $ticketId;
    }

    private function generateTicketNumber(int $id): string
    {
        return 'STM-' . date('Ymd') . '-' . str_pad($id, 4, '0', STR_PAD_LEFT);
    }

    /**
     * All tickets filed in the system (IT oversight view).
     */
    public function fetchAllFiledTickets(): array
    {
        $this->autoCloseResolvedTickets();

        $sql = "
            SELECT
                t.ticket_id,
                t.ticket_number,
                CONCAT(e.lastname, ', ', e.firstname) AS employee_name,
                t.category,
                t.priority,
                t.status,
                t.date_filed,
                t.concern_details,
                b.branchName,
                t.assigned_to AS assigned_to_id,
                CONCAT(a2.firstname, ' ', a2.lastname) AS assigned_to_name
            FROM {$this->tbltickets} t
            JOIN {$this->tblemployee} e ON t.employee_id = e.employee_id
            LEFT JOIN {$this->tblbranch} b ON b.branch_id = COALESCE(NULLIF(t.branch_id, 0), e.branch_id)
            LEFT JOIN {$this->tblemployee} a2 ON t.assigned_to = a2.employee_id
            ORDER BY t.date_filed DESC
        ";

        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function fetchAllTicketsByEmployee(int $employeeId): array
    {
        $this->autoCloseResolvedTickets();

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
            LEFT JOIN {$this->tblbranch}   b ON b.branch_id = COALESCE(NULLIF(t.branch_id, 0), e.branch_id)
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

    /**
     * Insert PDF generation record for a resolved ticket
     * 
     * @param int $ticketId Ticket ID
     * @param string $filename PDF filename
     * @param string $path Relative path to PDF file
     * @param int $generatedBy User ID who triggered generation
     * @param string $role User role
     * @param int $fileSize File size in bytes
     * @return bool Success or failure
     */
    public function insertTicketPdf($ticketId, $filename, $path, $generatedBy, $role = 'IT', $fileSize = null)
    {
        try {
            $sql = "INSERT INTO tblticket_pdfs (ticket_id, pdf_filename, pdf_path, generated_by, role, file_size, is_active, date_generated)
                    VALUES (:ticket_id, :filename, :path, :generated_by, :role, :file_size, 1, NOW())";
            
            $stmt = $this->pdo->prepare($sql);
            $result = $stmt->execute([
                ':ticket_id'    => $ticketId,
                ':filename'     => $filename,
                ':path'         => $path,
                ':generated_by' => $generatedBy,
                ':role'         => $role,
                ':file_size'    => $fileSize
            ]);
            
            return $result && $stmt->rowCount() > 0;
        } catch (\Exception $e) {
            error_log("Error inserting PDF record: " . $e->getMessage());
            return false;
        }
    }

    public function autoCloseResolvedTickets(): int
    {
        $stmt = $this->pdo->prepare(
            "UPDATE {$this->tbltickets}
             SET status = 'Closed', last_updated = NOW()
             WHERE status = 'Resolved'
               AND last_updated <= DATE_SUB(NOW(), INTERVAL 7 DAY)"
        );
        $stmt->execute();
        return (int) $stmt->rowCount();
    }


}
