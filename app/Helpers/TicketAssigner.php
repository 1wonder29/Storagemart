<?php

require_once __DIR__ . '/AppSettings.php';
require_once __DIR__ . '/TicketStatus.php';

/**
 * Automatic IT assignment, replacing the admin approval step:
 *  - a ticket from a branch goes to the IT person of that branch's zone (tblbranch.zone,
 *    tblit_zones), unless that person is marked away;
 *  - Head Office and branches without a zone go to a random available IT person
 *    (the least busy ones first);
 *  - a ticket the assignee has not opened within the set minutes, or whose assignee
 *    went away, moves to the next available IT person (each person at most once);
 *  - on Saturdays, every new ticket goes to the IT person on Saturday duty (a monthly rotation,
 *    with optional changes for one Saturday), and unopened tickets move to them as well.
 *    When that person is marked away, the normal rules above are used.
 * When nobody is available the ticket stays Open for the admin to assign by hand.
 */
class TicketAssigner
{
    /** Assign a newly filed ticket. Returns the IT employee_id, or null when left Open. */
    public static function autoAssign(PDO $pdo, int $ticketId): ?int
    {
        try {
            $ticket = self::loadTicket($pdo, $ticketId);
            if (!$ticket || !empty($ticket['assigned_to']) || $ticket['status'] !== TicketStatus::OPEN) {
                return null;
            }
            if (AppSettings::get('auto_assign_enabled', '1') !== '1') {
                return null;
            }

            $filerEmployeeId = (int) ($ticket['filer_employee_id'] ?? 0);
            $candidates = self::availableStaff($pdo, [$filerEmployeeId, (int) $ticket['employee_id']]);
            if (!$candidates) {
                return null;
            }

            $zone = trim((string) ($ticket['zone'] ?? ''));
            $chosen = null;
            $reason = '';
            $duty = self::saturdayDutyFor(date('Y-m-d'));
            if ($duty !== null && isset($candidates[$duty])) {
                $chosen = $duty;
                $reason = 'Saturday duty';
            } elseif ($zone !== '') {
                $zoneStaff = self::zoneStaff($pdo, $zone);
                if ($zoneStaff && isset($candidates[$zoneStaff])) {
                    $chosen = $zoneStaff;
                    $reason = 'Zone ' . $zone;
                } else {
                    $reason = 'Zone ' . $zone . ' IT not available';
                }
            } else {
                $reason = ($ticket['branchName'] ?? '') !== '' ? $ticket['branchName'] : 'No branch zone';
            }
            if ($chosen === null) {
                $chosen = self::pickBalanced($candidates);
                $reason .= ($reason !== '' ? ', ' : '') . 'random';
            }

            $stmt = $pdo->prepare("UPDATE tbltickets
                    SET assigned_to = :assigned_to, status = :status, date_approved = NOW(),
                        auto_assigned_at = NOW(), assignee_seen_at = NULL, last_updated = NOW()
                    WHERE ticket_id = :ticket_id AND assigned_to IS NULL AND status = :open");
            $stmt->execute([
                ':assigned_to' => $chosen,
                ':status' => TicketStatus::assigned(),
                ':ticket_id' => $ticketId,
                ':open' => TicketStatus::OPEN,
            ]);
            if ($stmt->rowCount() < 1) {
                return null;
            }

            $name = $candidates[$chosen]['name'];
            self::history($pdo, $ticketId, 'Automatically assigned to ' . $name . ' (' . $reason . ')',
                TicketStatus::OPEN, TicketStatus::assigned());
            self::notifyAssigned($pdo, $ticket, $chosen, $candidates[$chosen], 'A new ticket ' . $ticket['ticket_number'] . ' was assigned to you.');
            self::notifyOwner($pdo, $ticket, 'Your ticket ' . $ticket['ticket_number'] . ' was assigned to ' . $name . ' (IT).');

            require_once __DIR__ . '/../Services/TicketMailer.php';
            TicketMailer::ticketAssigned($ticketId, $chosen, 'the system');

            return $chosen;
        } catch (Throwable $e) {
            error_log('TicketAssigner::autoAssign failed for ticket ' . $ticketId . ': ' . $e->getMessage());
            return null;
        }
    }

    /** Move auto-assigned tickets that were not opened in time (runs at most once a minute). */
    public static function reassignStale(PDO $pdo): int
    {
        try {
            $lastRun = (int) AppSettings::get('reassign_last_run', '0');
            if (time() - $lastRun < 60) {
                return 0;
            }
            AppSettings::set('reassign_last_run', (string) time());

            $minutes = max(5, AppSettings::getInt('reassign_after_minutes', 30));
            $duty = self::saturdayDutyFor(date('Y-m-d'));
            $stmt = $pdo->prepare("SELECT t.ticket_id, t.ticket_number, t.assigned_to, t.employee_id, t.reassign_count,
                        t.created_by, t.auto_assigned_at, COALESCE(av.is_away, 0) AS assignee_away
                    FROM tbltickets t
                    LEFT JOIN tblit_availability av ON av.employee_id = t.assigned_to
                    WHERE t.status = :status
                      AND t.auto_assigned_at IS NOT NULL
                      AND t.assignee_seen_at IS NULL
                      AND (t.auto_assigned_at < (NOW() - INTERVAL {$minutes} MINUTE) OR av.is_away = 1)");
            $stmt->execute([':status' => TicketStatus::IN_PROGRESS]);
            $moved = 0;
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $ticket = self::loadTicket($pdo, (int) $row['ticket_id']);
                if (!$ticket) {
                    continue;
                }
                $current = (int) $row['assigned_to'];
                $candidates = self::availableStaff($pdo, [$current, (int) ($ticket['filer_employee_id'] ?? 0), (int) $ticket['employee_id']]);
                $next = null;
                if ($duty !== null) {
                    // Saturday: the person on duty keeps their tickets, and unopened ones move to them.
                    if ($current === $duty && (int) $row['assignee_away'] !== 1) {
                        continue;
                    }
                    if (isset($candidates[$duty])) {
                        $next = $duty;
                    }
                }
                if ($next === null) {
                    // Each other IT person gets one turn; after that the ticket stays put.
                    if (!$candidates || (int) $row['reassign_count'] >= count($candidates)) {
                        continue;
                    }
                    $next = self::pickBalanced($candidates);
                }
                $upd = $pdo->prepare("UPDATE tbltickets
                        SET assigned_to = :next, auto_assigned_at = NOW(), assignee_seen_at = NULL,
                            reassign_count = reassign_count + 1, last_updated = NOW()
                        WHERE ticket_id = :ticket_id AND assigned_to = :current AND assignee_seen_at IS NULL");
                $upd->execute([':next' => $next, ':ticket_id' => (int) $row['ticket_id'], ':current' => $current]);
                if ($upd->rowCount() < 1) {
                    continue;
                }
                $why = (int) $row['assignee_away'] === 1 ? 'previous IT is away' : 'not opened within ' . $minutes . ' minutes';
                if ($next === $duty) {
                    $why .= ', Saturday duty';
                }
                $previousName = self::staffName($pdo, $current);
                self::history($pdo, (int) $row['ticket_id'],
                    'Reassigned from ' . $previousName . ' to ' . $candidates[$next]['name'] . ' (' . $why . ')',
                    TicketStatus::IN_PROGRESS, TicketStatus::IN_PROGRESS);
                self::notifyAssigned($pdo, $ticket, $next, $candidates[$next],
                    'Ticket ' . $ticket['ticket_number'] . ' was reassigned to you (' . $why . ').');
                $previousAccount = self::accountIdOfEmployee($pdo, $current);
                if ($previousAccount) {
                    self::notify($pdo, $previousAccount, 'Ticket ' . $ticket['ticket_number'] . ' was moved to '
                        . $candidates[$next]['name'] . ' (' . $why . ').', 'fa-random', 'secondary',
                        '/it/tickets/view?id=' . (int) $row['ticket_id'], (int) $row['ticket_id']);
                }
                $moved++;
            }
            return $moved;
        } catch (Throwable $e) {
            error_log('TicketAssigner::reassignStale failed: ' . $e->getMessage());
            return 0;
        }
    }

    /** @return array<string, int> month ("2026-10") => IT employee_id on Saturday duty that month */
    public static function saturdayDutyMonths(): array
    {
        $raw = json_decode((string) AppSettings::get('saturday_duty', ''), true);
        $out = [];
        foreach (is_array($raw) ? $raw : [] as $month => $id) {
            if (preg_match('/^\d{4}-\d{2}$/', (string) $month) && (int) $id > 0) {
                $out[(string) $month] = (int) $id;
            }
        }
        ksort($out);
        return $out;
    }

    /** @return array<string, int> Saturday ("2026-10-17") => IT employee_id, or 0 = nobody (normal rules) that day */
    public static function saturdayDutyDates(): array
    {
        $raw = json_decode((string) AppSettings::get('saturday_duty_dates', ''), true);
        $out = [];
        foreach (is_array($raw) ? $raw : [] as $date => $id) {
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $date) && (int) $id >= 0) {
                $out[(string) $date] = (int) $id;
            }
        }
        ksort($out);
        return $out;
    }

    /** IT employee_id on duty on that date (a Saturday), or null (not a Saturday / nobody set). */
    public static function saturdayDutyFor(string $date): ?int
    {
        $time = strtotime($date);
        if ($time === false || date('N', $time) !== '6') {
            return null;
        }
        $dates = self::saturdayDutyDates();
        if (array_key_exists(date('Y-m-d', $time), $dates)) {
            return $dates[date('Y-m-d', $time)] ?: null;
        }
        return self::saturdayDutyMonths()[date('Y-m', $time)] ?? null;
    }

    /** The assignee opened the ticket: it is theirs now and will not be moved. */
    public static function markSeen(PDO $pdo, int $ticketId, int $employeeId): void
    {
        if ($ticketId <= 0 || $employeeId <= 0) {
            return;
        }
        try {
            $stmt = $pdo->prepare('UPDATE tbltickets SET assignee_seen_at = NOW()
                    WHERE ticket_id = ? AND assigned_to = ? AND assignee_seen_at IS NULL');
            $stmt->execute([$ticketId, $employeeId]);
        } catch (Throwable $e) {
            // Column not migrated yet.
        }
    }

    /** @return array<int, array{name:string, account_id:int, load:int}> IT staff who can take tickets, keyed by employee_id */
    public static function availableStaff(PDO $pdo, array $excludeEmployeeIds = []): array
    {
        $stmt = $pdo->query("SELECT e.employee_id, e.account_id, CONCAT(e.firstname, ' ', e.lastname) AS name,
                    COALESCE(av.is_away, 0) AS is_away,
                    (SELECT COUNT(*) FROM tbltickets t
                      WHERE t.assigned_to = e.employee_id AND t.status IN ('In Progress', 'Pending', 'On Hold')) AS active_load
                FROM tblaccounts a
                JOIN tblemployee e ON e.account_id = a.account_id
                LEFT JOIN tblit_availability av ON av.employee_id = e.employee_id
                WHERE a.usertype = 'IT' AND UPPER(a.status) = 'ACTIVE'");
        $staff = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $id = (int) $row['employee_id'];
            if ((int) $row['is_away'] === 1 || in_array($id, $excludeEmployeeIds, true)) {
                continue;
            }
            $staff[$id] = ['name' => trim((string) $row['name']), 'account_id' => (int) $row['account_id'], 'load' => (int) $row['active_load']];
        }
        return $staff;
    }

    /** Random pick among the least busy. */
    private static function pickBalanced(array $candidates): int
    {
        $minLoad = min(array_column($candidates, 'load'));
        $least = array_keys(array_filter($candidates, static function ($c) use ($minLoad) {
            return $c['load'] === $minLoad;
        }));
        return (int) $least[array_rand($least)];
    }

    private static function zoneStaff(PDO $pdo, string $zone): ?int
    {
        $stmt = $pdo->prepare('SELECT it_employee_id FROM tblit_zones WHERE zone = ? LIMIT 1');
        $stmt->execute([$zone]);
        $id = (int) $stmt->fetchColumn();
        return $id > 0 ? $id : null;
    }

    private static function loadTicket(PDO $pdo, int $ticketId): ?array
    {
        $stmt = $pdo->prepare("SELECT t.ticket_id, t.ticket_number, t.employee_id, t.assigned_to, t.status,
                    t.created_by, b.zone, b.branchName, owner.account_id AS owner_account_id,
                    owner_acc.usertype AS owner_usertype, filer.employee_id AS filer_employee_id
                FROM tbltickets t
                LEFT JOIN tblbranch b ON b.branch_id = t.branch_id
                LEFT JOIN tblemployee owner ON owner.employee_id = t.employee_id
                LEFT JOIN tblaccounts owner_acc ON owner_acc.account_id = owner.account_id
                LEFT JOIN tblemployee filer ON filer.account_id = t.created_by
                WHERE t.ticket_id = ? LIMIT 1");
        $stmt->execute([$ticketId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    private static function history(PDO $pdo, int $ticketId, string $details, string $old, string $new): void
    {
        $stmt = $pdo->prepare("INSERT INTO tblticket_history
                (ticket_id, action_type, action_details, old_status, new_status, performed_by, performed_role, date_logged)
                VALUES (?, 'Assigned', ?, ?, ?, 0, 'System', NOW())");
        $stmt->execute([$ticketId, $details, $old, $new]);
    }

    private static function notifyAssigned(PDO $pdo, array $ticket, int $employeeId, array $staff, string $message): void
    {
        $accountId = (int) ($staff['account_id'] ?? 0) ?: self::accountIdOfEmployee($pdo, $employeeId);
        if ($accountId) {
            self::notify($pdo, $accountId, $message, 'fa-user-check', 'info',
                '/it/tickets/view?id=' . (int) $ticket['ticket_id'], (int) $ticket['ticket_id']);
        }
    }

    private static function notifyOwner(PDO $pdo, array $ticket, string $message): void
    {
        $accountId = (int) ($ticket['owner_account_id'] ?? 0);
        if ($accountId <= 0) {
            return;
        }
        require_once __DIR__ . '/../Models/NotificationModel.php';
        $url = (new NotificationModel())->getTicketViewUrlForRole((string) ($ticket['owner_usertype'] ?? 'EMPLOYEE'), (int) $ticket['ticket_id']);
        self::notify($pdo, $accountId, $message, 'fa-user-check', 'success', $url, (int) $ticket['ticket_id']);
    }

    private static function notify(PDO $pdo, int $accountId, string $message, string $icon, string $color, string $url, int $ticketId): void
    {
        require_once __DIR__ . '/../Models/NotificationModel.php';
        (new NotificationModel())->create($accountId, $message, $icon, $color, $url, $ticketId);
    }

    private static function accountIdOfEmployee(PDO $pdo, int $employeeId): ?int
    {
        $stmt = $pdo->prepare('SELECT account_id FROM tblemployee WHERE employee_id = ? LIMIT 1');
        $stmt->execute([$employeeId]);
        $id = (int) $stmt->fetchColumn();
        return $id > 0 ? $id : null;
    }

    private static function staffName(PDO $pdo, int $employeeId): string
    {
        $stmt = $pdo->prepare("SELECT CONCAT(firstname, ' ', lastname) FROM tblemployee WHERE employee_id = ? LIMIT 1");
        $stmt->execute([$employeeId]);
        return trim((string) $stmt->fetchColumn()) ?: 'IT';
    }
}
