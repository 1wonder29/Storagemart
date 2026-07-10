<?php
// app/Models/employee/Ticket.php

require_once __DIR__ . '/../admin/BaseModel.php';
require_once __DIR__ . '/../../Helpers/TicketStatus.php';

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
            LEFT JOIN {$this->tblbranch} b ON b.branch_id = COALESCE(NULLIF(t.branch_id, 0), e.branch_id)
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
                ON e.employee_id = th.performed_by
                OR e.account_id = th.performed_by
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

    /**
     * Fetch a single ticket by ID with all details
     * 
     * @param int $ticketId
     * @return array|false Ticket data or false if not found
     */
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
                e.firstname AS employee_firstname,
                e.lastname AS employee_lastname,
                e.firstname AS emp_firstname,
                e.lastname AS emp_lastname,
                b.branchName
            FROM {$this->tbltickets} t
            JOIN {$this->tblemployee} e ON t.employee_id = e.employee_id
            LEFT JOIN {$this->tblbranch} b ON b.branch_id = COALESCE(NULLIF(t.branch_id, 0), e.branch_id)
            WHERE t.ticket_id = :ticket_id
            LIMIT 1
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':ticket_id' => (int)$ticketId]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: false;
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
                ON b.branch_id = COALESCE(NULLIF(t.branch_id, 0), e.branch_id)
            WHERE e.department = ?
            ORDER BY t.date_filed DESC
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$department]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function fetchAllBranches(): array
    {
        $sql = "
            SELECT branch_id, branchName, branchCode
            FROM {$this->tblbranch}
            ORDER BY branchName ASC
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function fetchAllTickets(): array
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
            LEFT JOIN {$this->tblbranch} b ON b.branch_id = COALESCE(NULLIF(t.branch_id, 0), e.branch_id)
            ORDER BY t.date_filed DESC
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function getTicketsByCreatedBy(int $accountId): array
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
                t.concern_details,
                COALESCE(NULLIF(t.branch_id, 0), e.branch_id) AS branch_id
            FROM {$this->tbltickets} t
            JOIN {$this->tblemployee} e ON t.employee_id = e.employee_id
            LEFT JOIN {$this->tblbranch} b ON b.branch_id = COALESCE(NULLIF(t.branch_id, 0), e.branch_id)
            WHERE t.created_by = :created_by
            ORDER BY t.date_filed DESC
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':created_by' => $accountId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function updateTechnicalReportPath(int $ticketId, string $filepath): bool
    {
        $sql = "UPDATE {$this->tbltickets} SET technical_report_path = :filepath WHERE ticket_id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':filepath' => $filepath, ':id' => $ticketId]);
    }

    /**
     * Check whether a ticket belongs to the Operations department.
     */
    public function isOperationsTicket(int $ticketId): bool
    {
        $stmt = $this->pdo->prepare("
            SELECT e.department
            FROM {$this->tbltickets} t
            JOIN {$this->tblemployee} e ON t.employee_id = e.employee_id
            WHERE t.ticket_id = :ticket_id
            LIMIT 1
        ");
        $stmt->execute([':ticket_id' => $ticketId]);
        $department = $stmt->fetchColumn();

        return $department !== false
            && strcasecmp((string) $department, 'Operations') === 0;
    }

    /**
     * Transfer an Operations ticket to another employee.
     * Returns [bool $ok, string $message]
     */
    public function transferTicketToEmployee(
        int $ticketId,
        int $newEmployeeId,
        int $performedByEmployeeId,
        string $performedRole,
        ?string $remarks = null
    ): array {
        if ($ticketId <= 0) {
            return [false, 'Invalid ticket.'];
        }
        if ($newEmployeeId <= 0) {
            return [false, 'Please select a valid employee.'];
        }

        $stmt = $this->pdo->prepare("
            SELECT t.ticket_id, t.employee_id, t.branch_id, t.status, t.ticket_number,
                   e.firstname AS old_firstname, e.lastname AS old_lastname
            FROM {$this->tbltickets} t
            JOIN {$this->tblemployee} e ON t.employee_id = e.employee_id
            WHERE t.ticket_id = :ticket_id
            LIMIT 1
        ");
        $stmt->execute([':ticket_id' => $ticketId]);
        $ticket = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$ticket) {
            return [false, 'Ticket not found.'];
        }

        $currentStatus = (string) ($ticket['status'] ?? '');
        if (in_array(strtolower($currentStatus), ['resolved', 'cancelled', 'closed'], true)) {
            return [false, 'This ticket cannot be transferred because it is ' . $currentStatus . '.'];
        }

        $currentEmployeeId = (int) ($ticket['employee_id'] ?? 0);
        if ($currentEmployeeId === $newEmployeeId) {
            return [false, 'Ticket is already assigned to the selected employee.'];
        }

        $stmt = $this->pdo->prepare("
            SELECT employee_id, firstname, lastname, branch_id, department
            FROM {$this->tblemployee}
            WHERE employee_id = :employee_id
            LIMIT 1
        ");
        $stmt->execute([':employee_id' => $newEmployeeId]);
        $newEmployee = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$newEmployee) {
            return [false, 'Selected employee does not exist.'];
        }

        if (strcasecmp((string) ($newEmployee['department'] ?? ''), 'Operations') !== 0) {
            return [false, 'Tickets can only be transferred to Operations employees.'];
        }

        $oldName = trim(($ticket['old_firstname'] ?? '') . ' ' . ($ticket['old_lastname'] ?? ''));
        $newName = trim(($newEmployee['firstname'] ?? '') . ' ' . ($newEmployee['lastname'] ?? ''));
        $newBranchId = (int) ($newEmployee['branch_id'] ?? 0);

        try {
            $this->pdo->beginTransaction();

            $sql = "
                UPDATE {$this->tbltickets}
                SET employee_id = :employee_id,
                    branch_id = :branch_id,
                    last_updated = NOW()
            ";
            $params = [
                ':employee_id' => $newEmployeeId,
                ':branch_id' => $newBranchId > 0 ? $newBranchId : (int) ($ticket['branch_id'] ?? 0),
                ':ticket_id' => $ticketId,
            ];

            if ($remarks !== null && $remarks !== '') {
                $sql .= ", remarks = :remarks";
                $params[':remarks'] = $remarks;
            }

            $sql .= " WHERE ticket_id = :ticket_id";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);

            $actionDetails = "Transferred from {$oldName} to {$newName}";
            if ($remarks !== null && $remarks !== '') {
                $actionDetails .= " — {$remarks}";
            }

            $stmt = $this->pdo->prepare("
                INSERT INTO {$this->tblticket_history}
                    (ticket_id, action_type, action_details, old_status, new_status, performed_by, performed_role, date_logged)
                VALUES
                    (:ticket_id, 'Transferred', :action_details, :old_status, :new_status, :performed_by, :performed_role, NOW())
            ");
            $stmt->execute([
                ':ticket_id' => $ticketId,
                ':action_details' => $actionDetails,
                ':old_status' => $currentStatus,
                ':new_status' => $currentStatus,
                ':performed_by' => $performedByEmployeeId,
                ':performed_role' => $performedRole,
            ]);

            $this->pdo->commit();

            return [true, "Ticket transferred to {$newName} successfully."];
        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log('transferTicketToEmployee error: ' . $e->getMessage());
            return [false, 'Failed to transfer ticket. Please try again.'];
        }
    }

    /**
     * Transfer multiple Operations tickets to another employee in one transaction.
     * Returns [bool $ok, string $message, int $transferredCount]
     */
    public function transferAllTicketsToEmployee(
        array $ticketIds,
        int $newEmployeeId,
        int $performedByEmployeeId,
        string $performedRole,
        ?string $remarks = null
    ): array {
        $ticketIds = array_values(array_unique(array_filter(array_map('intval', $ticketIds))));
        if (empty($ticketIds)) {
            return [false, 'No transferable tickets found.', 0];
        }
        if ($newEmployeeId <= 0) {
            return [false, 'Please select a valid employee.', 0];
        }

        $placeholders = implode(',', array_fill(0, count($ticketIds), '?'));
        $stmt = $this->pdo->prepare("
            SELECT t.ticket_id, t.employee_id, t.branch_id, t.status, t.ticket_number,
                   e.firstname AS old_firstname, e.lastname AS old_lastname
            FROM {$this->tbltickets} t
            JOIN {$this->tblemployee} e ON t.employee_id = e.employee_id
            WHERE t.ticket_id IN ({$placeholders})
        ");
        $stmt->execute($ticketIds);
        $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        if (count($tickets) !== count($ticketIds)) {
            return [false, 'One or more tickets could not be found.', 0];
        }

        $fromEmployeeId = (int) ($tickets[0]['employee_id'] ?? 0);
        foreach ($tickets as $ticket) {
            if ((int) ($ticket['employee_id'] ?? 0) !== $fromEmployeeId) {
                return [false, 'Tickets must belong to the same employee.', 0];
            }
        }

        if ($fromEmployeeId === $newEmployeeId) {
            return [false, 'Tickets are already assigned to the selected employee.', 0];
        }

        $stmt = $this->pdo->prepare("
            SELECT employee_id, firstname, lastname, branch_id, department
            FROM {$this->tblemployee}
            WHERE employee_id = :employee_id
            LIMIT 1
        ");
        $stmt->execute([':employee_id' => $newEmployeeId]);
        $newEmployee = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$newEmployee) {
            return [false, 'Selected employee does not exist.', 0];
        }

        if (strcasecmp((string) ($newEmployee['department'] ?? ''), 'Operations') !== 0) {
            return [false, 'Tickets can only be transferred to Operations employees.', 0];
        }

        $oldName = trim(($tickets[0]['old_firstname'] ?? '') . ' ' . ($tickets[0]['old_lastname'] ?? ''));
        $newName = trim(($newEmployee['firstname'] ?? '') . ' ' . ($newEmployee['lastname'] ?? ''));
        $newBranchId = (int) ($newEmployee['branch_id'] ?? 0);

        try {
            $this->pdo->beginTransaction();

            $updateSql = "
                UPDATE {$this->tbltickets}
                SET employee_id = :employee_id,
                    branch_id = :branch_id,
                    last_updated = NOW()
            ";
            if ($remarks !== null && $remarks !== '') {
                $updateSql .= ", remarks = :remarks";
            }
            $updateSql .= " WHERE ticket_id = :ticket_id";

            $updateStmt = $this->pdo->prepare($updateSql);
            $historyStmt = $this->pdo->prepare("
                INSERT INTO {$this->tblticket_history}
                    (ticket_id, action_type, action_details, old_status, new_status, performed_by, performed_role, date_logged)
                VALUES
                    (:ticket_id, 'Transferred', :action_details, :old_status, :new_status, :performed_by, :performed_role, NOW())
            ");

            $transferredCount = 0;
            foreach ($tickets as $ticket) {
                $ticketId = (int) ($ticket['ticket_id'] ?? 0);
                $currentStatus = (string) ($ticket['status'] ?? '');

                $params = [
                    ':employee_id' => $newEmployeeId,
                    ':branch_id' => $newBranchId > 0 ? $newBranchId : (int) ($ticket['branch_id'] ?? 0),
                    ':ticket_id' => $ticketId,
                ];
                if ($remarks !== null && $remarks !== '') {
                    $params[':remarks'] = $remarks;
                }
                $updateStmt->execute($params);

                $actionDetails = "Transferred from {$oldName} to {$newName}";
                if ($remarks !== null && $remarks !== '') {
                    $actionDetails .= " — {$remarks}";
                }

                $historyStmt->execute([
                    ':ticket_id' => $ticketId,
                    ':action_details' => $actionDetails,
                    ':old_status' => $currentStatus,
                    ':new_status' => $currentStatus,
                    ':performed_by' => $performedByEmployeeId,
                    ':performed_role' => $performedRole,
                ]);

                $transferredCount++;
            }

            $this->pdo->commit();

            $message = $transferredCount === 1
                ? "1 ticket transferred to {$newName} successfully."
                : "{$transferredCount} tickets transferred to {$newName} successfully.";

            return [true, $message, $transferredCount];
        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log('transferAllTicketsToEmployee error: ' . $e->getMessage());
            return [false, 'Failed to transfer tickets. Please try again.', 0];
        }
    }

    /**
     * Get Operations tickets assigned to an employee (all statuses).
     *
     * @return array<int, array<string, mixed>>
     */
    public function getOperationsTicketsForEmployee(int $employeeId, ?int $branchId = null): array
    {
        if ($employeeId <= 0) {
            return [];
        }

        $sql = "
            SELECT t.ticket_id, t.ticket_number, t.status, t.branch_id
            FROM {$this->tbltickets} t
            LEFT JOIN {$this->tblemployee} e ON t.employee_id = e.employee_id
            WHERE t.employee_id = :employee_id
              AND COALESCE(e.department, t.department) = 'Operations'
        ";
        $params = ['employee_id' => $employeeId];

        if ($branchId !== null && $branchId > 0) {
            $sql .= ' AND t.branch_id = :branch_id';
            $params['branch_id'] = $branchId;
        }

        $sql .= ' ORDER BY t.date_filed DESC';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Get Operations employees who have at least one ticket in a branch.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getOperationsEmployeesWithTicketsInBranch(int $branchId): array
    {
        if ($branchId <= 0) {
            return [];
        }

        $sql = "
            SELECT
                e.employee_id,
                e.firstname,
                e.lastname,
                e.middlename,
                e.email,
                e.position,
                COALESCE(e.department, t.department) AS department,
                COALESCE(e.branch_id, t.branch_id) AS branch_id,
                b.branchName,
                COUNT(t.ticket_id) AS ticket_count
            FROM {$this->tbltickets} t
            LEFT JOIN {$this->tblemployee} e ON t.employee_id = e.employee_id
            INNER JOIN {$this->tblbranch} b ON t.branch_id = b.branch_id
            WHERE t.branch_id = :branch_id
              AND t.employee_id IS NOT NULL
              AND t.employee_id > 0
              AND COALESCE(e.department, t.department) = 'Operations'
            GROUP BY
                e.employee_id,
                e.firstname,
                e.lastname,
                e.middlename,
                e.email,
                e.position,
                e.department,
                t.department,
                e.branch_id,
                t.branch_id,
                b.branchName
            ORDER BY e.lastname ASC, e.firstname ASC
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['branch_id' => $branchId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

}
