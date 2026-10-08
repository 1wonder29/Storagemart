<?php

require_once __DIR__ . '/AppSettings.php';

/**
 * IT troubleshooting manuals (tblit_manuals), managed by Admin and IT.
 * After a ticket is filed, the best-matching manual is posted in the ticket chat;
 * IT also sees the matches on the ticket page.
 */
class TicketManuals
{
    public const DEFAULT_URL = 'https://canva.link/3u9kajhn0ytr3ge';

    /** @return array<int, array> all manuals, default first */
    public static function all(PDO $pdo): array
    {
        try {
            return $pdo->query('SELECT * FROM tblit_manuals ORDER BY is_default DESC, title')->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Throwable $e) {
            return [];
        }
    }

    /**
     * Manuals for a ticket, best match first. Category match counts most, then
     * keywords found in the subject/description. Always ends with the default manual.
     *
     * @return array<int, array{manual_id:int, title:string, url:string, description:string, score:int}>
     */
    public static function matchesFor(PDO $pdo, string $category, string $text, int $limit = 3): array
    {
        $text = strtolower($text);
        $category = strtolower(trim($category));
        $scored = [];
        $default = null;
        foreach (self::all($pdo) as $manual) {
            $score = 0;
            foreach (self::splitList((string) ($manual['categories'] ?? '')) as $cat) {
                if ($cat !== '' && $category !== '' && (strpos($category, $cat) !== false || strpos($cat, $category) !== false)) {
                    $score += 5;
                }
            }
            foreach (self::splitList((string) ($manual['keywords'] ?? '')) as $keyword) {
                if ($keyword !== '' && preg_match('/\b' . preg_quote($keyword, '/') . '/i', $text)) {
                    $score += 2;
                }
            }
            $row = [
                'manual_id' => (int) $manual['manual_id'],
                'title' => (string) $manual['title'],
                'url' => (string) $manual['url'],
                'description' => (string) ($manual['description'] ?? ''),
                'steps' => self::steps((string) ($manual['steps'] ?? '')),
                'score' => $score,
            ];
            if ((int) ($manual['is_default'] ?? 0) === 1 && $default === null) {
                $default = $row;
            }
            if ($score > 0) {
                $scored[] = $row;
            }
        }
        usort($scored, static function ($a, $b) {
            return $b['score'] <=> $a['score'];
        });
        $scored = array_slice($scored, 0, $limit);

        if ($default === null) {
            $default = [
                'manual_id' => 0,
                'title' => 'IT Basic Troubleshooting Manual',
                'url' => AppSettings::get('it_manual_url', self::DEFAULT_URL) ?: self::DEFAULT_URL,
                'description' => 'Basic steps to try before IT arrives.',
                'steps' => [],
                'score' => 0,
            ];
        }
        $ids = array_column($scored, 'manual_id');
        if (!in_array($default['manual_id'], $ids, true) || $default['manual_id'] === 0) {
            $scored[] = $default;
        }
        return $scored;
    }

    /**
     * Post the troubleshooting message in a new ticket's chat (author "IT Helpdesk").
     * Returns true when a message was posted.
     */
    public static function postWelcome(PDO $pdo, int $ticketId): bool
    {
        try {
            $stmt = $pdo->prepare("SELECT t.ticket_number, t.category, t.subject, t.concern_details, e.firstname
                    FROM tbltickets t LEFT JOIN tblemployee e ON e.employee_id = t.employee_id
                    WHERE t.ticket_id = ? LIMIT 1");
            $stmt->execute([$ticketId]);
            $ticket = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$ticket) {
                return false;
            }
            $matches = self::matchesFor($pdo, (string) $ticket['category'],
                ($ticket['subject'] ?? '') . ' ' . ($ticket['concern_details'] ?? ''), 1);
            $manual = $matches[0];
            $fullManualUrl = AppSettings::get('it_manual_url', self::DEFAULT_URL) ?: self::DEFAULT_URL;

            $name = trim((string) ($ticket['firstname'] ?? ''));
            $lines = ['Hi' . ($name !== '' ? ' ' . $name : '') . '! Ticket ' . $ticket['ticket_number']
                . ' was received and IT has been notified.', ''];
            if ($manual['steps']) {
                $lines[] = 'While waiting, please try these steps (' . $manual['title'] . '):';
                foreach ($manual['steps'] as $i => $step) {
                    $lines[] = ($i + 1) . '. ' . $step;
                }
                $lines[] = '';
            }
            if ($manual['url'] !== '' && $manual['url'] !== $fullManualUrl) {
                $lines[] = $manual['title'] . ': ' . $manual['url'];
            }
            $lines[] = 'IT troubleshooting manual: ' . $fullManualUrl;
            $lines[] = '';
            $lines[] = 'If this does not fix the problem, tap "Need help from IT personnel" here in the chat.';
            $message = implode("\n", $lines);

            $pdo->prepare("INSERT INTO tblticket_comments (ticket_id, account_id, author_role, author_name, comment_text, created_at)
                    VALUES (?, 0, 'SYSTEM', 'IT Helpdesk', ?, NOW())")->execute([$ticketId, $message]);
            $pdo->prepare('UPDATE tbltickets SET manual_sent_at = NOW() WHERE ticket_id = ?')->execute([$ticketId]);
            return true;
        } catch (Throwable $e) {
            error_log('TicketManuals::postWelcome failed for ticket ' . $ticketId . ': ' . $e->getMessage());
            return false;
        }
    }

    /** @return string[] one step per non-empty line */
    public static function steps(string $value): array
    {
        $lines = preg_split('/
||
/', $value) ?: [];
        return array_values(array_filter(array_map('trim', $lines), 'strlen'));
    }

    /** @return string[] lower-case, trimmed, comma/newline separated */
    private static function splitList(string $value): array
    {
        $parts = preg_split('/[,\n;]+/', strtolower($value)) ?: [];
        return array_values(array_filter(array_map('trim', $parts), 'strlen'));
    }
}
