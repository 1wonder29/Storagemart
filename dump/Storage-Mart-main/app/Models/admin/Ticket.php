<?php 
    require_once 'BaseModel.php';

class Ticket extends BaseModel {
    protected $table = 'tbltickets';
    protected $tblemployee = 'tblemployee';
    protected $tblbranch = 'tblbranch';
    protected $tbltickets = 'tbltickets';
    protected $tblhistory = 'tblticket_history';   
    protected $tblticket_history = 'tblticket_history'; 
    protected $tbltechnical = 'tblticket_technical';
    protected $tblassets = 'tblassets_inventory';
    protected $tblgroup = 'tblassets_group';
    protected $tbllogs = 'tbllogs'; 

    //fetch all tickets
    public function fetchTicket(): array{
        $stmt = $this->pdo->prepare("SELECT t.ticket_id, t.ticket_number, CONCAT(e.lastname, ', ', e.firstname) AS employee_name, t.category, t.priority, t.status, t.date_filed, b.branchName,  t.assigned_to AS assigned_to_id, CONCAT(a2.firstname, ' ', a2.lastname) AS assigned_to_name FROM {$this->table} t JOIN {$this->tblemployee} e ON t.employee_id = e.employee_id LEFT JOIN {$this->tblbranch} b ON e.branch_id = b.branch_id LEFT JOIN {$this->tblemployee} a2 ON t.assigned_to = a2.employee_id ORDER BY t.date_filed DESC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function fetchTicketHistory(int $ticketId): array
    {
        $sql = "
            SELECT 
                th.action_details,
                CONCAT(e2.lastname, ', ', e2.firstname) AS assigned_to, 
                th.old_status,
                th.new_status,
                th.date_logged
            FROM tblticket_history th
            LEFT JOIN tbltickets t ON th.ticket_id = t.ticket_id
            LEFT JOIN tblemployee e2 ON t.assigned_to = e2.employee_id
            WHERE th.ticket_id = :ticket_id
            ORDER BY th.date_logged DESC
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':ticket_id' => $ticketId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function fetchEmployeesByDepartment(string $department): array
    {
        $sql = "SELECT employee_id, firstname, lastname 
                FROM tblemployee 
                WHERE department = :dept 
                ORDER BY firstname, lastname";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':dept' => $department]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function getEmployeeIdByAccountId(int $accountId): ?int
    {
        $stmt = $this->pdo->prepare("
            SELECT employee_id 
            FROM tblemployee 
            WHERE account_id = :acc 
            LIMIT 1
        ");
        $stmt->execute([':acc' => $accountId]);
        $id = $stmt->fetchColumn();

        return $id ? (int)$id : null;
    }

    /**
     * Reassign ticket and log history + tbllogs
     * Returns [bool $ok, string $message]
     */
    public function reassignTicket(
        int $ticketId,
        int $newAssignedTo,
        ?string $remarks,
        int $accountId,
        string $performedByUsername
    ): array {
        if ($ticketId <= 0) {
            return [false, 'Invalid ticket selected.'];
        }
        if ($newAssignedTo <= 0) {
            return [false, 'Please select a valid assignee.'];
        }

        // Map account → employee
        $performedByEmployeeId = $this->getEmployeeIdByAccountId($accountId);

        // 1) Current ticket info
        $stmt = $this->pdo->prepare("
            SELECT status, assigned_to 
            FROM tbltickets 
            WHERE ticket_id = :id 
            LIMIT 1
        ");
        $stmt->execute([':id' => $ticketId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return [false, 'Ticket not found.'];
        }

        $currentStatus   = $row['status'];
        $currentAssigned = (int)($row['assigned_to'] ?? 0);

        // block reassignment if resolved
        if (strcasecmp($currentStatus, 'resolved') === 0) {
            return [false, 'This ticket is already resolved and cannot be reassigned.'];
        }

        // nothing changed
        if ($currentAssigned === $newAssignedTo) {
            return [true, 'No changes: ticket already assigned to selected user.'];
        }

        // 2) Check new assignee exists
        $stmt = $this->pdo->prepare("
            SELECT firstname, lastname 
            FROM tblemployee 
            WHERE employee_id = :id 
            LIMIT 1
        ");
        $stmt->execute([':id' => $newAssignedTo]);
        $assignee = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$assignee) {
            return [false, 'Selected assignee does not exist.'];
        }
        $assigneeName = trim($assignee['firstname'] . ' ' . $assignee['lastname']);

        // 3) Old assignee name (if any)
        $oldAssigneeName = 'Unassigned';
        if ($currentAssigned > 0) {
            $stmt = $this->pdo->prepare("
                SELECT firstname, lastname 
                FROM tblemployee 
                WHERE employee_id = :id 
                LIMIT 1
            ");
            $stmt->execute([':id' => $currentAssigned]);
            if ($old = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $oldAssigneeName = trim($old['firstname'] . ' ' . $old['lastname']);
            }
        }

        try {
            $this->pdo->beginTransaction();

            // 4) Update tbltickets
            $sql = "UPDATE tbltickets 
                    SET assigned_to = :new_assigned, last_updated = NOW()";
            $params = [
                ':new_assigned' => $newAssignedTo,
                ':ticket_id'    => $ticketId,
            ];

            if ($remarks !== null && $remarks !== '') {
                $sql .= ", remarks = :remarks";
                $params[':remarks'] = $remarks;
            }

            $sql .= " WHERE ticket_id = :ticket_id";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);

            // 5) Insert into tblticket_history
            $actionType    = 'Reassigned';
            $actionDetails = "Reassigned from {$oldAssigneeName} to {$assigneeName}";
            $performedRole = 'IT Staff';

            $stmt = $this->pdo->prepare("
                INSERT INTO tblticket_history 
                    (ticket_id, action_type, action_details, old_status, new_status, performed_by, performed_role, date_logged)
                VALUES 
                    (:ticket_id, :action_type, :action_details, :old_status, :new_status, :performed_by, :performed_role, NOW())
            ");
            $stmt->execute([
                ':ticket_id'      => $ticketId,
                ':action_type'    => $actionType,
                ':action_details' => $actionDetails,
                ':old_status'     => $currentStatus,
                ':new_status'     => $currentStatus,
                ':performed_by'   => $performedByEmployeeId ?? 0,
                ':performed_role' => $performedRole,
            ]);

            // 6) Insert into tbllogs
            $stmt = $this->pdo->prepare("
                INSERT INTO tbllogs 
                    (datelog, timelog, action, module, ID, performedby)
                VALUES
                    (CURDATE(), DATE_FORMAT(NOW(), '%h:%i:%s%p'), :action, :module, :id, :performedby)
            ");
            $stmt->execute([
                ':action'     => 'Reassigned Ticket',
                ':module'     => 'Ticket Management',
                ':id'         => $ticketId,
                ':performedby'=> $performedByUsername,
            ]);

            $this->pdo->commit();
            return [true, "Ticket reassigned to {$assigneeName} successfully."];

        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

public function searchEmployee(string $q): ?array
{
    $q = trim($q);
    if ($q === '') {
        return null;
    }

    $sql = "
        SELECT 
            e.employee_id,
            CONCAT(e.lastname, ', ', e.firstname, ' ', IFNULL(e.middlename, '')) AS full_name,
            b.branchName,
            e.department
        FROM {$this->tblemployee} e
        LEFT JOIN {$this->tblbranch} b ON e.branch_id = b.branch_id
        WHERE e.firstname   LIKE :first
            OR e.lastname   LIKE :last
            OR e.employee_id LIKE :empid
        LIMIT 1
    ";

    $stmt = $this->pdo->prepare($sql);
    $like = "%{$q}%";

    $stmt->bindValue(':first', $like, PDO::PARAM_STR);
    $stmt->bindValue(':last',  $like, PDO::PARAM_STR);
    $stmt->bindValue(':empid', $like, PDO::PARAM_STR);

    $stmt->execute();

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return $row ?: null;
}

    public function fetchAssetsByEmployee(int $employeeId): array
    {
        $sql = "
            SELECT 
                i.inventory_id,
                i.assetNumber,
                g.groupName,
                g.ic_code,
                i.itemInfo,
                i.serialNumber,
                i.year_purchased
            FROM tblassets_inventory i
            LEFT JOIN tblassets_group g ON i.group_id = g.group_id
            WHERE i.employee_id = :employee_id
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':employee_id' => $employeeId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
    
    public function getInventoryDetailsByInventoryId(int $inventoryId): ?array
    {
        $sql = "
            SELECT 
                e.employee_id,
                CONCAT(e.lastname, ', ', e.firstname, ' ', e.middlename) AS fullname,
                e.department,
                b.branch_id,
                b.branchName,
                i.inventory_id,
                i.assetNumber,
                g.group_id,
                g.groupName
            FROM tblemployee e
            JOIN tblbranch b ON e.branch_id = b.branch_id
            JOIN tblassets_inventory i ON e.employee_id = i.employee_id
            LEFT JOIN tblassets_group g ON g.group_id = i.group_id
            WHERE i.inventory_id = :inventory_id
            LIMIT 1
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':inventory_id' => $inventoryId]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }


    public function createTicket(array $data): int
    {
        $sql = "
            INSERT INTO tbltickets (
                employee_id,
                inventory_id,
                branch_id,
                department,
                category,
                concern_details,
                priority,
                status,
                remarks,
                assigned_to,
                created_by
            ) VALUES (
                :employee_id,
                :inventory_id,
                :branch_id,
                :department,
                :category,
                :concern_details,
                :priority,
                :status,
                :remarks,
                :assigned_to,
                :created_by
            )
        ";

        $defaults = [
            'branch_id'       => null,
            'department'      => null,
            'category'        => null,
            'concern_details' => null,
            'priority'        => 'Low',       // enum('Low','Medium','High')
            'status'          => 'Pending',   // enum in tbltickets + history
            'remarks'         => null,
            'assigned_to'     => null,
        ];

        $data = array_merge($defaults, $data);

        try {
            $this->pdo->beginTransaction();

            // 1) Insert ticket
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':employee_id'     => $data['employee_id'],
                ':inventory_id'    => $data['inventory_id'],
                ':branch_id'       => $data['branch_id'],
                ':department'      => $data['department'],
                ':category'        => $data['category'],
                ':concern_details' => $data['concern_details'],
                ':priority'        => $data['priority'],
                ':status'          => $data['status'],     // 'Pending'
                ':remarks'         => $data['remarks'],
                ':assigned_to'     => $data['assigned_to'],
                ':created_by'      => $data['created_by'],
            ]);

            $ticketId = (int)$this->pdo->lastInsertId();

            // 2) Generate ticket_number from ID
            $ticketNumber = $this->generateTicketNumber($ticketId);

            $upd = $this->pdo->prepare("
                UPDATE tbltickets 
                SET ticket_number = :ticket_number 
                WHERE ticket_id     = :ticket_id
            ");
            $upd->execute([
                ':ticket_number' => $ticketNumber,
                ':ticket_id'     => $ticketId,
            ]);

            // 3) Insert initial history row (Created)
            // who performed the creation? account → employee (or just employee_id)
            $performedBy   = $data['employee_id'];  // or map from created_by if you prefer
            $performedRole = 'Employee';            // matches your sample data

            $details = 'Ticket filed by employee';

            $h = $this->pdo->prepare("
                INSERT INTO tblticket_history (
                    ticket_id,
                    action_type,
                    action_details,
                    old_status,
                    new_status,
                    performed_by,
                    performed_role,
                    date_logged
                ) VALUES (
                    :ticket_id,
                    'Created',
                    :action_details,
                    NULL,
                    :new_status,
                    :performed_by,
                    :performed_role,
                    NOW()
                )
            ");

            $h->execute([
                ':ticket_id'      => $ticketId,
                ':action_details' => $details,
                ':new_status'     => $data['status'], // 'Pending'
                ':performed_by'   => $performedBy,
                ':performed_role' => $performedRole,
            ]);

            $this->pdo->commit();

            return $ticketId;

        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }



    public function addTechnicalDetails(array $data): int
    {
        $sql = "
            INSERT INTO tblticket_technical (
                ticket_id,
                performed_by,
                technical_purpose,
                action_taken,
                result,
                remarks
            ) VALUES (
                :ticket_id,
                :performed_by,
                :technical_purpose,
                :action_taken,
                :result,
                :remarks
            )
        ";

        $stmt = $this->pdo->prepare($sql);

        $defaults = [
            'technical_purpose' => null,
            'action_taken'      => null,
            'result'            => null,
            'remarks'           => null,
        ];

        $data = array_merge($defaults, $data);

        $stmt->execute([
            ':ticket_id'        => $data['ticket_id'],
            ':performed_by'     => $data['performed_by'],
            ':technical_purpose'=> $data['technical_purpose'],
            ':action_taken'     => $data['action_taken'],
            ':result'           => $data['result'],
            ':remarks'          => $data['remarks'],
        ]);

        return (int)$this->pdo->lastInsertId();
    }


    protected function generateTicketNumber(int $ticketId): string
    {
        return 'STM-' . date('Ymd') . '-' . str_pad((string)$ticketId, 5, '0', STR_PAD_LEFT);
    }

    public function fetchPendingTickets(): array
    {
        $sql = "SELECT 
            t.ticket_id,
            t.ticket_number,
            CONCAT(e.lastname, ', ', e.firstname, ' ', IFNULL(e.middlename,'')) AS fullname,
            b.branchName,
            e.department,
            CONCAT(i.assetNumber, ' - ', g.groupName) AS asset_info,
            t.category,
            t.priority,
            t.concern_details,
            t.date_filed,
            t.status
        FROM {$this->tbltickets} t
        JOIN {$this->tblemployee} e ON t.employee_id = e.employee_id
        JOIN {$this->tblbranch} b ON e.branch_id = b.branch_id
        JOIN {$this->tblassets} i ON t.inventory_id = i.inventory_id
        LEFT JOIN {$this->tblgroup} g ON i.group_id = g.group_id
        WHERE t.status = 'Pending'
        ORDER BY t.date_filed ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function fetchITStaff(): array
    {
        $sql = "SELECT employee_id, firstname, lastname FROM {$this->tblemployee} WHERE department = 'IT' ORDER BY lastname ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function approveAndAssign(int $ticketId, int $assignedToEmployeeId, int $approvedByAccountId, string $remarks = ''): bool
    {
        try {
            $this->pdo->beginTransaction();

            // update ticket
            $sql = "UPDATE {$this->tbltickets}
                    SET status = 'In Progress', assigned_to = :assigned_to, approved_by = :approved_by, remarks = :remarks, date_approved = NOW(), last_updated = NOW()
                    WHERE ticket_id = :ticket_id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':assigned_to' => $assignedToEmployeeId,
                ':approved_by' => $approvedByAccountId,
                ':remarks'     => $remarks,
                ':ticket_id'   => $ticketId
            ]);

            // insert ticket history
            $sqlHist = "INSERT INTO {$this->tblhistory} (ticket_id, action_type, action_details, old_status, new_status, performed_by, performed_role, date_logged)
                        VALUES (:ticket_id, 'Approved', :details, 'Pending', 'In Progress', :performed_by, 'Admin', NOW())";
            $details = "Approved & assigned to employee {$assignedToEmployeeId}";
            $stmt = $this->pdo->prepare($sqlHist);
            $stmt->execute([
                ':ticket_id'    => $ticketId,
                ':details'      => $details,
                ':performed_by' => $approvedByAccountId
            ]);

            // log to tbllogs (non-fatal)
            $sqlLog = "INSERT INTO {$this->tbllogs} (datelog, timelog, action, module, ID, performedby)
                    VALUES (:datelog, :timelog, :action, 'Ticket Management', :ID, :performedby)";
            $stmt = $this->pdo->prepare($sqlLog);
            $stmt->execute([
                ':datelog'     => date('Y-m-d'),
                ':timelog'     => date('H:i:s'),
                ':action'      => 'Approve & Assign',
                ':ID'          => $ticketId,
                ':performedby' => $_SESSION['username'] ?? $approvedByAccountId
            ]);

            $this->pdo->commit();
            return true;
        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) $this->pdo->rollBack();
            // helpful dev-time logging:
            error_log('approveAndAssign error: ' . $e->getMessage());
            return false;
        }
    }

    public function declineTicket(int $ticketId, string $declineReason, string $remarks, int $declinedByAccountId): bool
    {
        try {
            $this->pdo->beginTransaction();

            $sql = "UPDATE {$this->tbltickets}
                    SET status = 'Closed', decline_reason = :decline_reason, remarks = :remarks, declined_by = :declined_by, date_declined = NOW(), last_updated = NOW()
                    WHERE ticket_id = :ticket_id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':decline_reason' => $declineReason,
                ':remarks'        => $remarks,
                ':declined_by'    => $declinedByAccountId,
                ':ticket_id'      => $ticketId
            ]);

            // only log if update affected a row
            if ($stmt->rowCount() > 0) {
                $sqlHist = "INSERT INTO {$this->tblhistory} (ticket_id, action_type, action_details, old_status, new_status, performed_by, performed_role, date_logged)
                            VALUES (:ticket_id, 'Closed', 'Ticket Declined by Admin', 'Pending', 'Closed', :performed_by, 'Admin', NOW())";
                $stmt2 = $this->pdo->prepare($sqlHist);
                $stmt2->execute([
                    ':ticket_id'   => $ticketId,
                    ':performed_by'=> $declinedByAccountId
                ]);

                $sqlLog = "INSERT INTO {$this->tbllogs} (datelog, timelog, action, module, ID, performedby)
                        VALUES (:datelog, :timelog, 'Decline', 'Ticket Management', :ID, :performedby)";
                $stmt3 = $this->pdo->prepare($sqlLog);
                $stmt3->execute([
                    ':datelog'     => date('Y-m-d'),
                    ':timelog'     => date('H:i:s'),
                    ':ID'          => $ticketId,
                    ':performedby' => $_SESSION['username'] ?? $declinedByAccountId
                ]);
            }

            $this->pdo->commit();
            return true;
        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) $this->pdo->rollBack();
            error_log('declineTicket error: ' . $e->getMessage());
            return false;
        }
    }
    public function getApprovalNotificationTargets(int $ticketId): array
    {
        $sql = "
            SELECT 
                emp.account_id AS employee_account_id,
                head.account_id AS head_account_id,
                t.ticket_number
            FROM tbltickets t
            JOIN tblemployee emp ON emp.employee_id = t.employee_id
            LEFT JOIN tblemployee head_emp 
                ON head_emp.department = t.department
            AND head_emp.position = 'HEAD'
            LEFT JOIN tblaccounts head 
                ON head.account_id = head_emp.account_id
            WHERE t.ticket_id = :ticket_id
            LIMIT 1
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['ticket_id' => $ticketId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }


    
}