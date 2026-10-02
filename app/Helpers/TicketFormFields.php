<?php

/**
 * Shared rules for the "New Support Ticket" form: priorities, categories per
 * branch type, and the subject stored alongside a ticket.
 */
class TicketFormFields
{
    public const PRIORITIES = ['Low', 'Medium', 'High', 'Critical'];

    public const HEAD_OFFICE_CATEGORIES = [
        'Access & Permissions',
        'Hardware',
        'Email & Communication',
        'Network',
        'Software',
        'Others',
    ];

    public const BRANCH_CATEGORIES = [
        'Branch Falcon / Storguard System Issues',
        'Desktop and Laptop Usability Issues',
        'Branch CCTV Status Issues',
        'CCTV Review & Access Request',
        'Network Performance (Latency and Bandwidth)',
        'Printer Issues',
        'POS / Payment Terminal Issues',
        'Access Control / Biometric Issues',
        'Telephone Issues',
    ];

    public static function isHeadOffice(?string $branchCode, ?string $branchName): bool
    {
        if (strtoupper(trim((string) $branchCode)) === 'HO') {
            return true;
        }
        return stripos((string) $branchName, 'head office') !== false;
    }

    /** @return string[] */
    public static function categoriesFor(bool $headOffice): array
    {
        return $headOffice ? self::HEAD_OFFICE_CATEGORIES : self::BRANCH_CATEGORIES;
    }

    public static function isHeadOfficeBranchId(int $branchId): bool
    {
        if ($branchId <= 0) {
            return false;
        }
        global $pdo;
        try {
            $stmt = $pdo->prepare('SELECT branchCode, branchName FROM tblbranch WHERE branch_id = ? LIMIT 1');
            $stmt->execute([$branchId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ? self::isHeadOffice($row['branchCode'] ?? '', $row['branchName'] ?? '') : false;
        } catch (Throwable $e) {
            error_log('TicketFormFields::isHeadOfficeBranchId failed: ' . $e->getMessage());
            return false;
        }
    }

    public static function subjectFor(int $ticketId): string
    {
        if ($ticketId <= 0) {
            return '';
        }
        global $pdo;
        try {
            $stmt = $pdo->prepare('SELECT subject FROM tbltickets WHERE ticket_id = ? LIMIT 1');
            $stmt->execute([$ticketId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ? (string) ($row['subject'] ?? '') : '';
        } catch (Throwable $e) {
            error_log('TicketFormFields::subjectFor failed for ticket ' . $ticketId . ': ' . $e->getMessage());
            return '';
        }
    }

    /**
     * Extract the subject from a POST payload.
     * A missing subject falls back to the start of the description so a ticket
     * never ends up without a headline.
     */
    public static function subjectFromPost(array $post): string
    {
        $subject = trim((string) ($post['subject'] ?? ''));
        if ($subject === '') {
            $description = trim(preg_replace('/\s+/', ' ', (string) ($post['concern_details'] ?? '')));
            $subject = function_exists('mb_substr') ? mb_substr($description, 0, 80) : substr($description, 0, 80);
        }
        $subject = function_exists('mb_substr') ? mb_substr($subject, 0, 255) : substr($subject, 0, 255);

        return $subject;
    }

    /**
     * Call right after a ticket is created: saves the subject, then e-mails the
     * admin mailbox (unless an admin filed it themselves). Failures are logged but never
     * block ticket creation (e.g. an environment that has not run the migration yet).
     */
    public static function ticketCreated(int $ticketId, array $post): void
    {
        if ($ticketId <= 0) {
            return;
        }
        global $pdo;
        $subject = self::subjectFromPost($post);
        try {
            $stmt = $pdo->prepare('UPDATE tbltickets SET subject = ? WHERE ticket_id = ?');
            $stmt->execute([$subject !== '' ? $subject : null, $ticketId]);
        } catch (Throwable $e) {
            error_log('TicketFormFields::ticketCreated failed for ticket ' . $ticketId . ': ' . $e->getMessage());
        }

        if (strtoupper((string) ($_SESSION['usertype'] ?? '')) !== 'ADMIN') {
            require_once __DIR__ . '/../Services/TicketMailer.php';
            TicketMailer::newTicket($ticketId);
        }
    }
}
