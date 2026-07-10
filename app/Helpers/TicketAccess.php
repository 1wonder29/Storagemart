<?php

/**
 * Shared ticket visibility rules for history, ratings, and downloads.
 */
class TicketAccess
{
    /**
     * @return array<string, mixed>|null
     */
    public static function fetchTicketRow(PDO $pdo, int $ticketId): ?array
    {
        if ($ticketId <= 0) {
            return null;
        }

        $stmt = $pdo->prepare(
            'SELECT ticket_id, employee_id, department, created_by, branch_id, assigned_to
             FROM tbltickets
             WHERE ticket_id = :ticket_id
             LIMIT 1'
        );
        $stmt->execute([':ticket_id' => $ticketId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    /**
     * @param array<string, mixed> $ticket
     */
    public static function canViewTicket(PDO $pdo, array $ticket, int $accountId, string $userRole): bool
    {
        $userRole = strtoupper(trim($userRole));

        if (in_array($userRole, ['ADMIN', 'IT'], true)) {
            return true;
        }

        if ($userRole === 'EMPLOYEE') {
            $employeeId = self::getEmployeeIdByAccountId($pdo, $accountId);
            if ($employeeId === null) {
                return false;
            }

            return (int) ($ticket['employee_id'] ?? 0) === $employeeId
                || (int) ($ticket['created_by'] ?? 0) === $accountId;
        }

        if (in_array($userRole, ['HEAD', 'HR'], true)) {
            $department = self::getDepartmentByAccountId($pdo, $accountId);
            if ($department === null || $department === '') {
                return false;
            }

            return strcasecmp((string) ($ticket['department'] ?? ''), $department) === 0;
        }

        if (in_array($userRole, ['OM', 'HOM'], true)) {
            return (int) ($ticket['created_by'] ?? 0) === $accountId;
        }

        if ($userRole === 'AOM') {
            return self::aomCanViewTicket($pdo, $accountId, $ticket);
        }

        return false;
    }

    public static function canViewTicketId(PDO $pdo, int $ticketId, int $accountId, string $userRole): bool
    {
        $ticket = self::fetchTicketRow($pdo, $ticketId);

        return $ticket !== null && self::canViewTicket($pdo, $ticket, $accountId, $userRole);
    }

    private static function getEmployeeIdByAccountId(PDO $pdo, int $accountId): ?int
    {
        $stmt = $pdo->prepare('SELECT employee_id FROM tblemployee WHERE account_id = :account_id LIMIT 1');
        $stmt->execute([':account_id' => $accountId]);
        $val = $stmt->fetchColumn();

        return $val !== false ? (int) $val : null;
    }

    private static function getDepartmentByAccountId(PDO $pdo, int $accountId): ?string
    {
        $stmt = $pdo->prepare('SELECT department FROM tblemployee WHERE account_id = :account_id LIMIT 1');
        $stmt->execute([':account_id' => $accountId]);
        $val = $stmt->fetchColumn();

        return $val !== false ? (string) $val : null;
    }

    /**
     * @param array<string, mixed> $ticket
     */
    private static function aomCanViewTicket(PDO $pdo, int $accountId, array $ticket): bool
    {
        $aomEmployeeId = self::getEmployeeIdByAccountId($pdo, $accountId);
        if ($aomEmployeeId === null) {
            return false;
        }

        $ticketId = (int) ($ticket['ticket_id'] ?? 0);
        if ($ticketId <= 0) {
            return false;
        }

        $sql = 'SELECT 1
                FROM tbltickets t
                WHERE t.ticket_id = :ticket_id
                  AND (
                    t.branch_id IN (
                        SELECT branch_id FROM tblbranch_assignments
                        WHERE aom_employee_id = :aom_employee_id AND is_active = 1
                    )
                    OR t.employee_id IN (
                        SELECT employee_id FROM tblhom_employee_assignments
                        WHERE aom_id = :aom_employee_id_2 AND is_active = 1
                    )
                  )
                LIMIT 1';

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':ticket_id' => $ticketId,
            ':aom_employee_id' => $aomEmployeeId,
            ':aom_employee_id_2' => $aomEmployeeId,
        ]);

        return (bool) $stmt->fetchColumn();
    }
}
