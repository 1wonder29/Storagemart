<?php 
    require_once 'BaseModel.php';
    require_once __DIR__ . '/../../Helpers/TicketStatus.php';

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

    //fetch all tickets (optional filter: sla-breach)
    public function fetchTicket(?string $filter = null): array
    {
        $sql = "SELECT t.ticket_id, t.ticket_number, CONCAT(e.lastname, ', ', e.firstname) AS employee_name, t.category, t.priority, t.status, t.date_filed, b.branchName, t.assigned_to AS assigned_to_id, CONCAT(a2.firstname, ' ', a2.lastname) AS assigned_to_name
            FROM {$this->table} t
            JOIN {$this->tblemployee} e ON t.employee_id = e.employee_id
            LEFT JOIN {$this->tblbranch} b ON b.branch_id = COALESCE(NULLIF(t.branch_id, 0), e.branch_id)
            LEFT JOIN {$this->tblemployee} a2 ON t.assigned_to = a2.employee_id";

        if ($filter === 'sla-breach') {
            require_once __DIR__ . '/../../Helpers/TicketSla.php';
            $sql .= ' WHERE (' . TicketSla::resolutionBreachCondition('t') . ')';
        }

        $sql .= ' ORDER BY t.date_filed DESC';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function fetchTicketById(int $ticketId)
    {
        $sql = "
            SELECT
                t.ticket_id,
                t.employee_id,
                t.ticket_number,
                t.department,
                t.category,
                t.concern_details,
                t.priority,
                t.date_filed,
                t.status,
                t.remarks,
                t.assigned_to,
                e.firstname AS emp_firstname,
                e.lastname AS emp_lastname,
                e.firstname AS employee_firstname,
                e.lastname AS employee_lastname,
                b.branchName,
                CONCAT(a2.firstname, ' ', a2.lastname) AS assigned_to_name
            FROM {$this->tbltickets} t
            JOIN {$this->tblemployee} e ON t.employee_id = e.employee_id
            LEFT JOIN {$this->tblbranch} b ON b.branch_id = COALESCE(NULLIF(t.branch_id, 0), e.branch_id)
            LEFT JOIN {$this->tblemployee} a2 ON t.assigned_to = a2.employee_id
            WHERE t.ticket_id = :ticket_id
            LIMIT 1
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':ticket_id' => $ticketId]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: false;
    }

    public function fetchTicketHistory(int $ticketId): array
    {
        $sql = "
            SELECT 
                th.action_details,
                CONCAT(e.firstname, ' ', e.lastname) AS assigned_to, 
                th.old_status,
                th.new_status,
                th.date_logged,
                th.action_type
            FROM tblticket_history th
            LEFT JOIN tblemployee e
                ON e.employee_id = th.performed_by
                OR e.account_id = th.performed_by
            WHERE th.ticket_id = :ticket_id
            ORDER BY th.date_logged DESC
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':ticket_id' => $ticketId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function fetchEmployeesByDepartment(string $department): array
    {
        // BUG-16 fix: join with tblaccounts to filter by active IT accounts only
        $sql = "SELECT e.employee_id, e.firstname, e.lastname
                FROM tblemployee e
                JOIN tblaccounts a ON a.account_id = e.account_id
                WHERE e.department = :dept
                  AND UPPER(a.usertype) = UPPER(:dept_type)
                  AND UPPER(a.status) = 'ACTIVE'
                ORDER BY e.firstname, e.lastname";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':dept' => $department, ':dept_type' => $department]);

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
        $statusLower     = strtolower(trim((string) $currentStatus));

        if (in_array($statusLower, ['resolved', 'closed'], true)) {
            return [false, 'This ticket is already ' . $currentStatus . ' and cannot be reassigned.'];
        }

        if ($statusLower === 'cancelled') {
            return [false, 'This ticket is cancelled and cannot be reassigned.'];
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

            // 4) Update tbltickets — also set status to In Progress when assigning
            $newStatus = TicketStatus::assigned();
            $sql = "UPDATE tbltickets 
                    SET assigned_to = :new_assigned, status = :new_status, last_updated = NOW()";
            $params = [
                ':new_assigned' => $newAssignedTo,
                ':new_status'   => $newStatus,
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
                ':new_status'     => $newStatus,
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
                OR CONCAT(e.lastname, ', ', e.firstname, ' ', IFNULL(e.middlename, '')) LIKE :full_with_comma
                OR CONCAT(e.firstname, ' ', IFNULL(e.middlename, ''), ' ', e.lastname) LIKE :full_plain
            LIMIT 1
        ";

        $stmt = $this->pdo->prepare($sql);
        $like = "%{$q}%";

        $stmt->bindValue(':first', $like, PDO::PARAM_STR);
        $stmt->bindValue(':last',  $like, PDO::PARAM_STR);
        $stmt->bindValue(':empid', $like, PDO::PARAM_STR);
        $stmt->bindValue(':full_with_comma', $like, PDO::PARAM_STR);
        $stmt->bindValue(':full_plain', $like, PDO::PARAM_STR);

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
            'status'          => TicketStatus::initial(),
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
            CONCAT(IFNULL(i.assetNumber, 'N/A'), ' - ', IFNULL(g.groupName, 'N/A')) AS asset_info,
            t.category,
            t.priority,
            t.concern_details,
            t.date_filed,
            t.status
        FROM {$this->tbltickets} t
        JOIN {$this->tblemployee} e ON t.employee_id = e.employee_id
        LEFT JOIN {$this->tblbranch} b ON b.branch_id = COALESCE(NULLIF(t.branch_id, 0), e.branch_id)
        LEFT JOIN {$this->tblassets} i ON t.inventory_id = i.inventory_id
        LEFT JOIN {$this->tblgroup} g ON i.group_id = g.group_id
        WHERE t.status = :pending_status
        ORDER BY t.date_filed ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':pending_status' => TicketStatus::PENDING]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function fetchITStaff(): array
    {
        $sql = "SELECT employee_id, firstname, lastname FROM {$this->tblemployee} WHERE department = 'IT' ORDER BY lastname ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Fetch all tickets filed within a date range (start inclusive, end exclusive).
     */
    public function fetchTicketsByDateRange(string $start, string $end): array
    {
        $sql = "SELECT
            t.ticket_id,
            t.ticket_number,
            CONCAT(e.lastname, ', ', e.firstname, ' ', IFNULL(e.middlename, '')) AS employee_name,
            e.department AS employee_department,
            b.branchName,
            t.department,
            t.category,
            CONCAT(IFNULL(i.assetNumber, 'N/A'), ' - ', IFNULL(g.groupName, 'N/A')) AS asset_info,
            t.priority,
            t.status,
            t.concern_details,
            t.remarks,
            CONCAT(IFNULL(a2.firstname, ''), ' ', IFNULL(a2.lastname, '')) AS assigned_to_name,
            t.date_filed,
            t.last_updated,
            t.date_approved,
            t.decline_reason
        FROM {$this->tbltickets} t
        JOIN {$this->tblemployee} e ON t.employee_id = e.employee_id
        LEFT JOIN {$this->tblbranch} b ON b.branch_id = COALESCE(NULLIF(t.branch_id, 0), e.branch_id)
        LEFT JOIN {$this->tblassets} i ON t.inventory_id = i.inventory_id
        LEFT JOIN {$this->tblgroup} g ON i.group_id = g.group_id
        LEFT JOIN {$this->tblemployee} a2 ON t.assigned_to = a2.employee_id
        WHERE t.date_filed >= :start_date AND t.date_filed < :end_date
        ORDER BY t.date_filed ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':start_date' => $start,
            ':end_date'   => $end,
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Fetch all tickets filed within a given calendar month.
     */
    public function fetchTicketsByMonth(int $year, int $month): array
    {
        $start = sprintf('%04d-%02d-01 00:00:00', $year, $month);
        $end = date('Y-m-d H:i:s', strtotime($start . ' +1 month'));

        return $this->fetchTicketsByDateRange($start, $end);
    }

    /**
     * Fetch all tickets filed within a given ISO week.
     */
    public function fetchTicketsByWeek(int $isoYear, int $isoWeek): array
    {
        $startDate = new \DateTimeImmutable();
        $startDate = $startDate->setISODate($isoYear, $isoWeek, 1);
        $start = $startDate->format('Y-m-d 00:00:00');
        $end = $startDate->modify('+7 days')->format('Y-m-d H:i:s');

        return $this->fetchTicketsByDateRange($start, $end);
    }

    /**
     * Ticket counts filed per calendar month for a given year (Jan–Dec).
     *
     * @return array{labels: string[], data: int[], year: int}
     */
    public function fetchMonthlyTicketTrend(int $year): array
    {
        $year = max(2000, min(2100, $year));
        $labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $counts = array_fill(0, 12, 0);

        $sql = "
            SELECT MONTH(date_filed) AS month_num, COUNT(*) AS ticket_count
            FROM {$this->tbltickets}
            WHERE date_filed IS NOT NULL
              AND YEAR(date_filed) = :year
            GROUP BY MONTH(date_filed)
            ORDER BY month_num ASC
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':year' => $year]);

        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $monthNum = (int) ($row['month_num'] ?? 0);
            if ($monthNum >= 1 && $monthNum <= 12) {
                $counts[$monthNum - 1] = (int) ($row['ticket_count'] ?? 0);
            }
        }

        return [
            'labels' => $labels,
            'data' => $counts,
            'year' => $year,
        ];
    }

    public function countTicketsByMonth(int $year, int $month): int
    {
        $start = sprintf('%04d-%02d-01 00:00:00', $year, $month);
        $end = date('Y-m-d H:i:s', strtotime($start . ' +1 month'));

        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*) FROM {$this->tbltickets}
             WHERE date_filed >= :start_date AND date_filed < :end_date"
        );
        $stmt->execute([
            ':start_date' => $start,
            ':end_date'   => $end,
        ]);

        return (int) $stmt->fetchColumn();
    }

    public function approveAndAssign(int $ticketId, int $assignedToEmployeeId, int $approvedByAccountId, string $remarks = ''): bool
    {
        try {
            $performedByEmployeeId = $this->getEmployeeIdByAccountId($approvedByAccountId) ?? 0;

            $this->pdo->beginTransaction();

            // update ticket
            $sql = "UPDATE {$this->tbltickets}
                    SET status = :in_progress_status, assigned_to = :assigned_to, approved_by = :approved_by, remarks = :remarks, date_approved = NOW(), last_updated = NOW()
                    WHERE ticket_id = :ticket_id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':in_progress_status' => TicketStatus::IN_PROGRESS,
                ':assigned_to' => $assignedToEmployeeId,
                ':approved_by' => $approvedByAccountId,
                ':remarks'     => $remarks,
                ':ticket_id'   => $ticketId
            ]);

            // insert ticket history
            $sqlHist = "INSERT INTO {$this->tblhistory} (ticket_id, action_type, action_details, old_status, new_status, performed_by, performed_role, date_logged)
                        VALUES (:ticket_id, 'Approved', :details, :old_status, :new_status, :performed_by, 'Admin', NOW())";
            $details = "Approved & assigned to employee {$assignedToEmployeeId}";
            $stmt = $this->pdo->prepare($sqlHist);
            $stmt->execute([
                ':ticket_id'    => $ticketId,
                ':details'      => $details,
                ':old_status'   => TicketStatus::PENDING,
                ':new_status'   => TicketStatus::IN_PROGRESS,
                ':performed_by' => $performedByEmployeeId,
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
            $performedByEmployeeId = $this->getEmployeeIdByAccountId($declinedByAccountId) ?? 0;

            $this->pdo->beginTransaction();

            $sql = "UPDATE {$this->tbltickets}
                    SET status = :closed_status, decline_reason = :decline_reason, remarks = :remarks, declined_by = :declined_by, date_declined = NOW(), last_updated = NOW()
                    WHERE ticket_id = :ticket_id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':closed_status'  => TicketStatus::CLOSED,
                ':decline_reason' => $declineReason,
                ':remarks'        => $remarks,
                ':declined_by'    => $declinedByAccountId,
                ':ticket_id'      => $ticketId
            ]);

            // only log if update affected a row
            if ($stmt->rowCount() > 0) {
                $sqlHist = "INSERT INTO {$this->tblhistory} (ticket_id, action_type, action_details, old_status, new_status, performed_by, performed_role, date_logged)
                            VALUES (:ticket_id, 'Closed', 'Ticket Declined by Admin', :old_status, :new_status, :performed_by, 'Admin', NOW())";
                $stmt2 = $this->pdo->prepare($sqlHist);
                $stmt2->execute([
                    ':ticket_id'   => $ticketId,
                    ':old_status'  => TicketStatus::PENDING,
                    ':new_status'  => TicketStatus::CLOSED,
                    ':performed_by'=> $performedByEmployeeId
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

    
}