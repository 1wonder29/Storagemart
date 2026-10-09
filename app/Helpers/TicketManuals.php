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
     * Manuals for a ticket, best match first. Each keyword found in the subject or
     * description counts 3, the ticket category counts 2 (categories such as Hardware
     * are broad, so specific words win). Always ends with the default manual.
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
                if ($category !== '' && $cat === $category) {
                    $score += 2;
                    break;
                }
            }
            foreach (self::splitList((string) ($manual['keywords'] ?? '')) as $keyword) {
                if (preg_match('/\b' . preg_quote($keyword, '/') . '(s|es)?\b/i', $text)) {
                    $score += 3;
                }
            }
            $row = [
                'manual_id' => (int) $manual['manual_id'],
                'title' => (string) $manual['title'],
                'url' => (string) $manual['url'],
                'description' => (string) ($manual['description'] ?? ''),
                'steps' => self::steps((string) ($manual['steps'] ?? '')),
                'viewer' => self::viewerUrl((int) $manual['manual_id']),
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
                'url' => self::defaultUrl($pdo),
                'description' => 'Basic steps to try before IT arrives.',
                'steps' => [],
                'viewer' => self::defaultUrl($pdo),
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
            // Slides hosted by the TMS (the Canva link asks for a Canva login).
            $fullManualUrl = self::defaultViewerUrl($pdo);

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
            if ($manual['manual_id'] > 0 && $manual['viewer'] !== $fullManualUrl) {
                $lines[] = 'Slides for this problem: ' . $manual['viewer'];
            }
            $lines[] = 'IT troubleshooting manual (slides): ' . $fullManualUrl;
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

    /** Full link to a guide's slides in the TMS (works in the chat on any device). */
    public static function viewerUrl(int $manualId): string
    {
        $base = defined('BASE_URL') ? rtrim(BASE_URL, '/') : '';
        if (!preg_match('#^https?://#i', $base) && !empty($_SERVER['HTTP_HOST'])) {
            $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $base = $scheme . '://' . $_SERVER['HTTP_HOST'] . $base;
        }
        return $base . '/manuals/view?id=' . $manualId;
    }

    /** Slides of the default guide, or the old setting link when there are no guides yet. */
    public static function defaultViewerUrl(PDO $pdo): string
    {
        try {
            $id = (int) $pdo->query('SELECT manual_id FROM tblit_manuals WHERE is_default = 1 ORDER BY manual_id LIMIT 1')->fetchColumn();
            if ($id > 0) {
                return self::viewerUrl($id);
            }
        } catch (Throwable $e) {
            // table not created yet
        }
        return self::defaultUrl($pdo);
    }

    public const FILE_TYPES = [
        'pptx' => ['application/vnd.openxmlformats-officedocument.presentationml.presentation', 'application/zip', 'application/octet-stream'],
        'ppt'  => ['application/vnd.ms-powerpoint', 'application/octet-stream', 'application/CDFV2', 'application/x-ole-storage'],
        'pdf'  => ['application/pdf'],
    ];

    /**
     * Store an uploaded PowerPoint / PDF for a guide (app/uploads/manuals).
     * @return array{path: string, name: string, mime: string, size: int}
     */
    public static function storeFile(array $file, int $manualId): array
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || !is_uploaded_file((string) $file['tmp_name'])) {
            throw new RuntimeException('The file could not be uploaded (max ' . ini_get('upload_max_filesize') . ').');
        }
        if ((int) $file['size'] > 25 * 1024 * 1024) {
            throw new RuntimeException('The file is too large (max 25 MB).');
        }
        $name = basename((string) $file['name']);
        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        if (!isset(self::FILE_TYPES[$ext])) {
            throw new RuntimeException('Upload a PowerPoint (.pptx / .ppt) or PDF file.');
        }
        $mime = function_exists('finfo_open') ? (string) finfo_file(finfo_open(FILEINFO_MIME_TYPE), (string) $file['tmp_name']) : '';
        if ($mime !== '' && !in_array($mime, self::FILE_TYPES[$ext], true)) {
            throw new RuntimeException('The file does not look like a .' . $ext . ' file.');
        }
        $dir = dirname(__DIR__) . '/uploads/manuals';
        if (!is_dir($dir) && !@mkdir($dir, 0755, true) && !is_dir($dir)) {
            throw new RuntimeException('The upload folder could not be created.');
        }
        $stored = $manualId . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
        if (!move_uploaded_file((string) $file['tmp_name'], $dir . '/' . $stored)) {
            throw new RuntimeException('The file could not be saved.');
        }
        $mimes = ['pptx' => self::FILE_TYPES['pptx'][0], 'ppt' => self::FILE_TYPES['ppt'][0], 'pdf' => 'application/pdf'];
        return ['path' => $stored, 'name' => mb_substr($name, 0, 255), 'mime' => $mimes[$ext], 'size' => (int) $file['size']];
    }

    public static function filePath(string $stored): ?string
    {
        if (!preg_match('#^\d+_[a-f0-9]{16}\.(pptx|ppt|pdf)$#', $stored)) {
            return null;
        }
        $path = dirname(__DIR__) . '/uploads/manuals/' . $stored;
        return is_file($path) ? $path : null;
    }

    /** Link of the default manual (falls back to the it_manual_url setting). */
    public static function defaultUrl(PDO $pdo): string
    {
        try {
            $url = (string) $pdo->query("SELECT url FROM tblit_manuals WHERE is_default = 1 AND url <> '' ORDER BY manual_id LIMIT 1")->fetchColumn();
            if ($url !== '') {
                return $url;
            }
        } catch (Throwable $e) {
            // table not created yet
        }
        return AppSettings::get('it_manual_url', self::DEFAULT_URL) ?: self::DEFAULT_URL;
    }

    /** @return string[] one step per non-empty line */
    public static function steps(string $value): array
    {
        $lines = preg_split('/\r\n|\r|\n/', $value) ?: [];
        return array_values(array_filter(array_map('trim', $lines), 'strlen'));
    }

    /** @return string[] lower-case, trimmed, comma/newline separated */
    private static function splitList(string $value): array
    {
        $parts = preg_split('/[,\n;]+/', strtolower($value)) ?: [];
        return array_values(array_filter(array_map('trim', $parts), 'strlen'));
    }
}
