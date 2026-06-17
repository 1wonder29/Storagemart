<?php

require_once __DIR__ . '/admin/BaseModel.php';

class RealtimeModel extends BaseModel
{
    protected $tblTickets = 'tbltickets';
    protected $tblEmployee = 'tblemployee';

    /**
     * Tickets updated since $since for the current user's scope.
     */
    public function getTicketUpdates(int $accountId, string $usertype, string $since): array
    {
        $this->autoCloseResolvedTickets();

        $since = trim($since);
        if ($since === '') {
            $since = date('Y-m-d H:i:s', time() - 60);
        }

        $role = strtoupper(trim($usertype));
        $params = [':since' => $since];
        $scopeSql = '1=1';

        if ($role === 'EMPLOYEE') {
            $employeeId = $this->getEmployeeIdByAccountId($accountId);
            if ($employeeId === null) {
                return [];
            }
            $scopeSql = '(t.employee_id = :employee_id OR t.created_by = :account_id)';
            $params[':employee_id'] = $employeeId;
            $params[':account_id'] = $accountId;
        } elseif (in_array($role, ['HEAD', 'HR'], true)) {
            $department = $this->getDepartmentByAccountId($accountId);
            if ($department === null || $department === '') {
                return [];
            }
            $scopeSql = 't.department = :department';
            $params[':department'] = $department;
        } elseif (in_array($role, ['OM', 'HOM'], true)) {
            $scopeSql = 't.created_by = :account_id';
            $params[':account_id'] = $accountId;
        } elseif ($role === 'AOM') {
            $aomEmployeeId = $this->getEmployeeIdByAccountId($accountId);
            if ($aomEmployeeId === null) {
                return [];
            }
            $scopeSql = '(
                t.branch_id IN (
                    SELECT branch_id FROM tblbranch_assignments
                    WHERE aom_employee_id = :aom_employee_id AND is_active = 1
                )
                OR t.employee_id IN (
                    SELECT employee_id FROM tblhom_employee_assignments
                    WHERE aom_id = :aom_employee_id_2 AND is_active = 1
                )
            )';
            $params[':aom_employee_id'] = $aomEmployeeId;
            $params[':aom_employee_id_2'] = $aomEmployeeId;
        }
        // ADMIN, IT: no extra scope

        $sql = "
            SELECT
                t.ticket_id,
                t.ticket_number,
                t.status,
                t.priority,
                t.last_updated,
                TRIM(CONCAT(COALESCE(ae.firstname, ''), ' ', COALESCE(ae.lastname, ''))) AS assigned_to_name
            FROM {$this->tblTickets} t
            LEFT JOIN {$this->tblEmployee} ae ON ae.employee_id = t.assigned_to
            WHERE t.last_updated > :since
              AND {$scopeSql}
            ORDER BY t.last_updated DESC
            LIMIT 100
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function getTicketSnapshot(int $ticketId, int $accountId, string $usertype): ?array
    {
        $this->autoCloseResolvedTickets();

        $updates = $this->getTicketUpdates($accountId, $usertype, '1970-01-01 00:00:00');
        foreach ($updates as $row) {
            if ((int) ($row['ticket_id'] ?? 0) === $ticketId) {
                return $row;
            }
        }

        $stmt = $this->pdo->prepare("
            SELECT
                t.ticket_id,
                t.ticket_number,
                t.status,
                t.priority,
                t.last_updated,
                TRIM(CONCAT(COALESCE(ae.firstname, ''), ' ', COALESCE(ae.lastname, ''))) AS assigned_to_name
            FROM {$this->tblTickets} t
            LEFT JOIN {$this->tblEmployee} ae ON ae.employee_id = t.assigned_to
            WHERE t.ticket_id = :ticket_id
            LIMIT 1
        ");
        $stmt->execute([':ticket_id' => $ticketId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    private function getEmployeeIdByAccountId(int $accountId): ?int
    {
        $stmt = $this->pdo->prepare(
            "SELECT employee_id FROM {$this->tblEmployee} WHERE account_id = :account_id LIMIT 1"
        );
        $stmt->execute([':account_id' => $accountId]);
        $val = $stmt->fetchColumn();

        return $val !== false ? (int) $val : null;
    }

    private function getDepartmentByAccountId(int $accountId): ?string
    {
        $stmt = $this->pdo->prepare(
            "SELECT department FROM {$this->tblEmployee} WHERE account_id = :account_id LIMIT 1"
        );
        $stmt->execute([':account_id' => $accountId]);
        $val = $stmt->fetchColumn();

        return $val !== false ? (string) $val : null;
    }

    private function autoCloseResolvedTickets(): void
    {
        $stmt = $this->pdo->prepare(
            "UPDATE {$this->tblTickets}
             SET status = 'Closed', last_updated = NOW()
             WHERE status = 'Resolved'
               AND last_updated <= DATE_SUB(NOW(), INTERVAL 7 DAY)"
        );
        $stmt->execute();
    }

}
