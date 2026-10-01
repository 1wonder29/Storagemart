<?php
require_once __DIR__ . '/MailService.php';

/**
 * Ticket e-mails:
 *  - a new ticket is filed          -> the admin mailbox
 *  - IT staff is assigned to ticket -> that staff member's own e-mail address
 *
 * Optional .env setting: ADMIN_NOTIFY_EMAIL (comma-separated list; defaults to storagemart.it@gmail.com).
 * The first address is also the e-mail shown for the Admin account in the sidebar.
 */
class TicketMailer
{
    private const DEFAULT_ADMIN_EMAIL = 'storagemart.it@gmail.com';

    /** @return string[] */
    public static function adminRecipients(): array
    {
        $configured = trim((string) getenv('ADMIN_NOTIFY_EMAIL'));
        $list = $configured !== '' ? explode(',', $configured) : [self::DEFAULT_ADMIN_EMAIL];

        $valid = [];
        foreach ($list as $address) {
            $address = trim($address);
            if (filter_var($address, FILTER_VALIDATE_EMAIL)) {
                $valid[] = $address;
            }
        }
        return $valid;
    }

    public static function newTicket(int $ticketId): void
    {
        try {
            $ticket = self::loadTicket($ticketId);
            if (!$ticket) {
                return;
            }

            $subject = '[TMS] New ticket ' . $ticket['ticket_number'] . ' — ' . $ticket['headline'];
            $body = self::render(
                'New ticket filed',
                'A new support ticket has been filed and is waiting to be assigned.',
                $ticket,
                self::siteUrl() . '/admin/tickets/view?id=' . $ticketId,
                'Review ticket'
            );

            foreach (self::adminRecipients() as $address) {
                MailService::send($address, $subject, $body);
            }
        } catch (Throwable $e) {
            error_log('TicketMailer::newTicket failed for ticket ' . $ticketId . ': ' . $e->getMessage());
        }
    }

    public static function ticketAssigned(int $ticketId, int $assignedEmployeeId, string $assignedBy = ''): void
    {
        try {
            if ($assignedEmployeeId <= 0) {
                return;
            }
            $ticket = self::loadTicket($ticketId);
            $staff = self::loadEmployee($assignedEmployeeId);
            if (!$ticket || !$staff || $staff['email'] === '') {
                return;
            }

            $intro = 'Hi ' . ($staff['firstname'] !== '' ? $staff['firstname'] : 'there')
                . ', a ticket has been assigned to you' . ($assignedBy !== '' ? ' by ' . $assignedBy : '') . '.';

            $subject = '[TMS] Ticket ' . $ticket['ticket_number'] . ' assigned to you — ' . $ticket['headline'];
            $body = self::render(
                'Ticket assigned to you',
                $intro,
                $ticket,
                self::siteUrl() . '/it/tickets/view?id=' . $ticketId,
                'Open ticket'
            );

            MailService::send($staff['email'], $subject, $body, self::adminRecipients()[0] ?? null);
        } catch (Throwable $e) {
            error_log('TicketMailer::ticketAssigned failed for ticket ' . $ticketId . ': ' . $e->getMessage());
        }
    }

    /** @return array<string, mixed>|null */
    private static function loadTicket(int $ticketId): ?array
    {
        if ($ticketId <= 0) {
            return null;
        }
        global $pdo;
        $stmt = $pdo->prepare(
            "SELECT t.ticket_number, t.subject, t.category, t.priority, t.concern_details,
                    t.date_filed, t.status,
                    CONCAT(e.firstname, ' ', e.lastname) AS employee_name,
                    e.department AS employee_department,
                    b.branchName
             FROM tbltickets t
             LEFT JOIN tblemployee e ON t.employee_id = e.employee_id
             LEFT JOIN tblbranch b ON b.branch_id = COALESCE(NULLIF(t.branch_id, 0), e.branch_id)
             WHERE t.ticket_id = ?
             LIMIT 1"
        );
        $stmt->execute([$ticketId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return null;
        }

        $row['ticket_number'] = (string) ($row['ticket_number'] ?: ('#' . $ticketId));
        $description = trim((string) ($row['concern_details'] ?? ''));
        $subjectText = trim((string) ($row['subject'] ?? ''));
        $row['headline'] = $subjectText !== '' ? $subjectText : self::shorten($description, 80);
        return $row;
    }

    /** @return array{firstname: string, email: string}|null */
    private static function loadEmployee(int $employeeId): ?array
    {
        global $pdo;
        $stmt = $pdo->prepare('SELECT firstname, email FROM tblemployee WHERE employee_id = ? LIMIT 1');
        $stmt->execute([$employeeId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? ['firstname' => trim((string) $row['firstname']), 'email' => trim((string) $row['email'])] : null;
    }

    private static function siteUrl(): string
    {
        $configured = rtrim((string) (defined('BASE_URL') ? BASE_URL : ''), '/');
        if ($configured !== '') {
            return $configured;
        }
        $https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
        return ($https ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
    }

    private static function shorten(string $text, int $max): string
    {
        $text = trim(preg_replace('/\s+/', ' ', $text));
        return mb_strlen($text) > $max ? rtrim(mb_substr($text, 0, $max)) . '…' : $text;
    }

    private static function render(string $heading, string $intro, array $ticket, string $link, string $linkLabel): string
    {
        $e = static fn ($value) => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');

        $rows = [
            'Ticket' => $ticket['ticket_number'],
            'Subject' => $ticket['headline'],
            'Filed by' => trim($ticket['employee_name'] . ($ticket['employee_department'] ? ' (' . $ticket['employee_department'] . ')' : '')),
            'Branch' => $ticket['branchName'] ?? '',
            'Category' => $ticket['category'] ?? '',
            'Priority' => $ticket['priority'] ?? '',
            'Filed on' => !empty($ticket['date_filed']) ? date('M j, Y g:i A', strtotime((string) $ticket['date_filed'])) : '',
        ];

        $tableRows = '';
        foreach ($rows as $label => $value) {
            if (trim((string) $value) === '') {
                continue;
            }
            $tableRows .= '<tr><td style="padding:6px 12px 6px 0;color:#6b7280;white-space:nowrap;vertical-align:top">'
                . $e($label) . '</td><td style="padding:6px 0;color:#111827;font-weight:600">' . $e($value) . '</td></tr>';
        }

        $description = self::shorten((string) ($ticket['concern_details'] ?? ''), 600);

        return '<div style="font-family:Segoe UI,Arial,sans-serif;max-width:560px;margin:0 auto;color:#111827">'
            . '<h2 style="margin:0 0 8px;color:#1a237e">' . $e($heading) . '</h2>'
            . '<p style="margin:0 0 16px;color:#374151">' . $e($intro) . '</p>'
            . '<table style="border-collapse:collapse;font-size:14px">' . $tableRows . '</table>'
            . ($description !== '' ? '<p style="margin:16px 0 4px;color:#6b7280;font-size:13px">Description</p>'
                . '<div style="padding:12px;background:#f3f4f6;border-radius:6px;font-size:14px">' . nl2br($e($description)) . '</div>' : '')
            . '<p style="margin:20px 0"><a href="' . $e($link) . '" style="background:#3949ab;color:#ffffff;text-decoration:none;'
            . 'padding:10px 18px;border-radius:6px;font-weight:600;display:inline-block">' . $e($linkLabel) . '</a></p>'
            . '<p style="margin:24px 0 0;color:#9ca3af;font-size:12px">Automated message from Storage Mart TMS.</p>'
            . '</div>';
    }
}
