<?php

require_once __DIR__ . '/admin/BaseModel.php';
require_once __DIR__ . '/employee/Employee.php';
require_once __DIR__ . '/aom/AOMTicketModel.php';

class TicketCancelModel extends BaseModel
{
    protected $tbltickets = 'tbltickets';
    protected $tblhistory = 'tblticket_history';
    protected $tbllogs = 'tbllogs';

    private const CANCELLABLE_STATUSES = [
        'Pending',
        'In Progress',
        'On Hold',
        'Reopened',
    ];

    public function isCancellableStatus(string $status): bool
    {
        return in_array($status, self::CANCELLABLE_STATUSES, true);
    }

    public function getTicketRow(int $ticketId): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT ticket_id, ticket_number, employee_id, department, branch_id,
                   status, created_by, assigned_to
            FROM {$this->tbltickets}
            WHERE ticket_id = :id
            LIMIT 1
        ");
        $stmt->execute([':id' => $ticketId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function canUserCancelTicket(int $ticketId, int $accountId, string $usertype): bool
    {
        if ($accountId <= 0) {
            return false;
        }

        $ticket = $this->getTicketRow($ticketId);
        if (!$ticket) {
            return false;
        }

        if (!$this->isCancellableStatus((string) ($ticket['status'] ?? ''))) {
            return false;
        }

        $role = strtoupper(trim($usertype));

        switch ($role) {
            case 'ADMIN':
                return true;

            case 'IT':
                return true;

            case 'EMPLOYEE':
                $employeeModel = new Employee();
                $employeeId = $employeeModel->getEmployeeIdByAccountId($accountId);
                return $employeeId && (int) $ticket['employee_id'] === (int) $employeeId;

            case 'HEAD':
            case 'HR':
                $employeeModel = new Employee();
                $user = $employeeModel->fetchUserDetails($accountId);
                if (!$user) {
                    return false;
                }
                $hrEmployee = $employeeModel->getEmployeeById((int) ($user['employee_id'] ?? 0));
                $department = $hrEmployee['department'] ?? null;
                return $department && strcasecmp((string) $ticket['department'], (string) $department) === 0;

            case 'AOM':
                $employeeModel = new Employee();
                $employeeId = $employeeModel->getEmployeeIdByAccountId($accountId);
                if (!$employeeId) {
                    return false;
                }
                $aomModel = new AOMTicketModel();
                return (bool) $aomModel->getTicketByIdForAOM($ticketId, (int) $employeeId);

            case 'HOM':
            case 'OM':
                return (int) ($ticket['created_by'] ?? 0) === $accountId;

            default:
                return false;
        }
    }

    public function cancelTicket(
        int $ticketId,
        string $reason,
        int $performedByAccountId,
        string $performedRole
    ): bool {
        $ticket = $this->getTicketRow($ticketId);
        if (!$ticket) {
            return false;
        }

        $oldStatus = (string) ($ticket['status'] ?? '');
        if (!$this->isCancellableStatus($oldStatus)) {
            return false;
        }

        $newStatus = 'Cancelled';
        $reason = trim($reason);

        try {
            $this->pdo->beginTransaction();

            $sql = "UPDATE {$this->tbltickets}
                    SET status = :status,
                        remarks = :remarks,
                        last_updated = NOW()
                    WHERE ticket_id = :ticket_id
                    AND status = :old_status";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':status'     => $newStatus,
                ':remarks'    => $reason !== '' ? $reason : 'Ticket cancelled',
                ':ticket_id'  => $ticketId,
                ':old_status' => $oldStatus,
            ]);

            if ($stmt->rowCount() === 0) {
                $this->pdo->rollBack();
                return false;
            }

            $details = 'Ticket cancelled';
            if ($reason !== '') {
                $details .= ': ' . $reason;
            }

            $sqlHist = "INSERT INTO {$this->tblhistory}
                        (ticket_id, action_type, action_details, old_status, new_status, performed_by, performed_role, date_logged)
                        VALUES (:ticket_id, 'Cancelled', :details, :old_status, :new_status, :performed_by, :performed_role, NOW())";
            $stmtHist = $this->pdo->prepare($sqlHist);
            $stmtHist->execute([
                ':ticket_id'      => $ticketId,
                ':details'        => $details,
                ':old_status'     => $oldStatus,
                ':new_status'     => $newStatus,
                ':performed_by'   => $performedByAccountId,
                ':performed_role' => $performedRole,
            ]);

            $sqlLog = "INSERT INTO {$this->tbllogs} (datelog, timelog, action, module, ID, performedby)
                       VALUES (:datelog, :timelog, 'Cancel', 'Ticket Management', :ID, :performedby)";
            $stmtLog = $this->pdo->prepare($sqlLog);
            $stmtLog->execute([
                ':datelog'     => date('Y-m-d'),
                ':timelog'     => date('H:i:s'),
                ':ID'          => $ticketId,
                ':performedby' => $_SESSION['username'] ?? $performedByAccountId,
            ]);

            $this->pdo->commit();
            return true;
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log('cancelTicket error: ' . $e->getMessage());
            return false;
        }
    }

    public function getEmployeeAccountIdByTicketId(int $ticketId): ?int
    {
        $stmt = $this->pdo->prepare("
            SELECT e.account_id
            FROM {$this->tbltickets} t
            JOIN tblemployee e ON e.employee_id = t.employee_id
            WHERE t.ticket_id = :id
            LIMIT 1
        ");
        $stmt->execute([':id' => $ticketId]);
        $val = $stmt->fetchColumn();
        return $val !== false ? (int) $val : null;
    }

    public static function mapPerformedRole(string $usertype): string
    {
        $map = [
            'ADMIN'    => 'Admin',
            'EMPLOYEE' => 'Employee',
            'HEAD'     => 'Head',
            'HR'       => 'HR',
            'IT'       => 'IT Staff',
            'AOM'      => 'AOM',
            'HOM'      => 'HOM',
            'OM'       => 'OM',
        ];
        $role = strtoupper(trim($usertype));
        return $map[$role] ?? $role;
    }

    public function canItViewCancelledTicket(int $ticketId, int $employeeId, int $accountId): bool
    {
        $ticket = $this->getTicketRow($ticketId);
        if (!$ticket || strcasecmp((string) ($ticket['status'] ?? ''), 'Cancelled') !== 0) {
            return false;
        }

        if ($employeeId > 0) {
            if ((int) ($ticket['employee_id'] ?? 0) === $employeeId) {
                return true;
            }
            if ((int) ($ticket['assigned_to'] ?? 0) === $employeeId) {
                return true;
            }
        }

        if ($accountId <= 0) {
            return false;
        }

        $stmt = $this->pdo->prepare("
            SELECT 1
            FROM {$this->tblhistory}
            WHERE ticket_id = :ticket_id
              AND new_status = 'Cancelled'
              AND performed_by = :account_id
            LIMIT 1
        ");
        $stmt->execute([
            ':ticket_id'  => $ticketId,
            ':account_id' => $accountId,
        ]);

        return (bool) $stmt->fetchColumn();
    }

    /**
     * Cancelled tickets visible to the current IT user (filed by, assigned to, or cancelled by them).
     */
    public function getCancelledTicketsForIt(int $employeeId, int $accountId): array
    {
        if ($employeeId <= 0 && $accountId <= 0) {
            return [];
        }

        $sql = $this->buildCancelledTicketsSql() . "
            AND (
                t.employee_id = :employee_id
                OR t.assigned_to = :assigned_to
                OR th.performed_by = :account_id
            )
            ORDER BY th.date_logged DESC
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':employee_id' => $employeeId,
            ':assigned_to' => $employeeId,
            ':account_id'  => $accountId,
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * All cancelled tickets (admin view).
     */
    public function getAllCancelledTickets(): array
    {
        $sql = $this->buildCancelledTicketsSql() . "
            ORDER BY th.date_logged DESC
        ";

        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    private function buildCancelledTicketsSql(): string
    {
        return "
            SELECT
                t.ticket_id,
                t.ticket_number,
                CONCAT(e.lastname, ', ', e.firstname) AS employee_name,
                t.category,
                t.priority,
                t.concern_details,
                t.remarks AS cancel_reason,
                t.date_filed,
                b.branchName,
                th.old_status,
                th.action_details,
                th.performed_role,
                th.date_logged AS date_cancelled,
                COALESCE(
                    NULLIF(TRIM(CONCAT(cancel_emp.firstname, ' ', cancel_emp.lastname)), ''),
                    acc.username,
                    'Unknown'
                ) AS cancelled_by_name
            FROM {$this->tbltickets} t
            JOIN tblemployee e ON t.employee_id = e.employee_id
            LEFT JOIN tblbranch b ON b.branch_id = COALESCE(NULLIF(t.branch_id, 0), e.branch_id)
            INNER JOIN {$this->tblhistory} th
                ON th.ticket_id = t.ticket_id
                AND th.new_status = 'Cancelled'
                AND th.history_id = (
                    SELECT h2.history_id
                    FROM {$this->tblhistory} h2
                    WHERE h2.ticket_id = t.ticket_id
                    AND h2.new_status = 'Cancelled'
                    ORDER BY h2.date_logged DESC, h2.history_id DESC
                    LIMIT 1
                )
            LEFT JOIN tblaccounts acc ON th.performed_by = acc.account_id
            LEFT JOIN tblemployee cancel_emp ON cancel_emp.account_id = acc.account_id
            WHERE t.status = 'Cancelled'
        ";
    }
}
