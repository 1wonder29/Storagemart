<?php

require_once __DIR__ . '/AppSettings.php';
require_once __DIR__ . '/ManualPresentation.php';

/**
 * IT troubleshooting manuals (tblit_manuals), managed by Admin and IT.
 * After a ticket is filed, the best-matching manual is posted in the ticket chat;
 * IT also sees the matches on the ticket page. Everyone signed in can open the
 * library (/manuals); guides marked "IT only" are shown to IT and Admin only.
 */
class TicketManuals
{
    public const DEFAULT_URL = 'https://canva.link/3u9kajhn0ytr3ge';

    /** Library groups, in the order of the IT Department's manual folders. */
    public const GROUPS = [
        'Printer Issues',
        'PC and Laptop Issues',
        'Internet and Network Issues',
        'Email and Software Issues',
        'CCTV Issues',
        'Company Phone Issues',
        'PTI System Issues',
        'Basic Device Terms and Definitions',
        'IT Admin and Setup Guides',
        'PMS and Maintenance Guides',
        'General',
    ];

    /** Steps listed in the chat message only for short guides; longer ones get the slides link. */
    private const CHAT_MAX_STEPS = 6;

    /** @return array<int, array> all manuals, default first, then by group and title */
    public static function all(PDO $pdo): array
    {
        try {
            $rows = $pdo->query('SELECT * FROM tblit_manuals ORDER BY is_default DESC, title')->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Throwable $e) {
            return [];
        }
        $order = array_flip(self::GROUPS);
        usort($rows, static function ($a, $b) use ($order) {
            if ((int) $a['is_default'] !== (int) $b['is_default']) {
                return (int) $b['is_default'] <=> (int) $a['is_default'];
            }
            $ga = $order[self::groupOf($a)] ?? 98;
            $gb = $order[self::groupOf($b)] ?? 98;
            return $ga === $gb ? strcasecmp((string) $a['title'], (string) $b['title']) : $ga <=> $gb;
        });
        return $rows;
    }

    public static function groupOf(array $manual): string
    {
        $group = trim((string) ($manual['manual_group'] ?? ''));
        return $group !== '' ? $group : 'General';
    }

    /** IT-only guides (setup and maintenance) are for IT and Admin accounts. */
    public static function canView(array $manual): bool
    {
        if (empty($manual['it_only'])) {
            return true;
        }
        $role = strtoupper((string) ($_SESSION['superuser_real_usertype'] ?? $_SESSION['usertype'] ?? ''));
        return in_array($role, ['IT', 'ADMIN'], true);
    }

    /**
     * Manuals for a ticket, best match first. Each keyword found in the subject or
     * description counts 3, the ticket category counts 2 (categories such as Hardware
     * are broad, so specific words win). IT-only guides are left out (the result is sent
     * to the requester). Always ends with the default manual.
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
            if (!empty($manual['it_only'])) {
                continue;
            }
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
                'solution' => trim((string) ($manual['solution'] ?? '')),
                'intro' => trim((string) ($manual['intro'] ?? '')),
                'steps' => self::steps((string) ($manual['steps'] ?? '')),
                'has_sections' => (bool) preg_match('/^\s*#(?!#)/m', (string) ($manual['steps'] ?? '')),
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
        // Best score first; on a tie the guide added first (the main guide of its group) wins.
        usort($scored, static function ($a, $b) {
            return $b['score'] <=> $a['score'] ?: $a['manual_id'] <=> $b['manual_id'];
        });
        $scored = array_slice($scored, 0, $limit);

        if ($default === null) {
            $default = [
                'manual_id' => 0,
                'title' => 'IT Basic Troubleshooting Manual',
                'url' => self::defaultUrl($pdo),
                'description' => 'Basic steps to try before IT arrives.',
                'solution' => '',
                'intro' => '',
                'steps' => [],
                'has_sections' => false,
                'viewer' => self::libraryUrl(),
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
     * Chat lines for a matched guide: short guides list their steps; long ones (or guides
     * with sections) give the solution and the slides link.
     * @return string[]
     */
    public static function chatLines(array $match): array
    {
        $lines = [];
        $steps = $match['steps'];
        if ($steps && count($steps) <= self::CHAT_MAX_STEPS && empty($match['has_sections'])) {
            $lines[] = 'While waiting, please try these steps (' . $match['title'] . '):';
            foreach ($steps as $i => $step) {
                $lines[] = ($i + 1) . '. ' . $step;
            }
        } else {
            $lines[] = 'While waiting, please check this guide: ' . $match['title'] . '.';
            $summary = $match['solution'] !== '' ? $match['solution'] : ($match['intro'] !== '' ? $match['intro'] : $match['description']);
            if ($summary !== '') {
                $lines[] = ManualPresentation::plain(mb_strlen($summary) > 400 ? mb_substr($summary, 0, 397) . '...' : $summary);
            }
            if ($steps) {
                $lines[] = 'The slides show all ' . count($steps) . ' steps.';
            }
        }
        if ($match['manual_id'] > 0) {
            $lines[] = 'Slides: ' . $match['viewer'];
        }
        return $lines;
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

            $name = trim((string) ($ticket['firstname'] ?? ''));
            $lines = ['Hi' . ($name !== '' ? ' ' . $name : '') . '! Ticket ' . $ticket['ticket_number']
                . ' was received and IT has been notified.', ''];
            if ($manual['manual_id'] > 0) {
                $lines = array_merge($lines, self::chatLines($manual), ['']);
            }
            // All guides, hosted by the TMS (the Canva folder asks for a Canva login).
            $lines[] = 'All IT manuals: ' . self::libraryUrl();
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

    /** Full address of the TMS (works in the chat on any device). */
    private static function absoluteBase(): string
    {
        $base = defined('BASE_URL') ? rtrim(BASE_URL, '/') : '';
        if (!preg_match('#^https?://#i', $base) && !empty($_SERVER['HTTP_HOST'])) {
            $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $base = $scheme . '://' . $_SERVER['HTTP_HOST'] . $base;
        }
        return $base;
    }

    /** Full link to a guide's slides in the TMS. */
    public static function viewerUrl(int $manualId): string
    {
        return self::absoluteBase() . '/manuals/view?id=' . $manualId;
    }

    /** Full link to the manuals library. */
    public static function libraryUrl(): string
    {
        return self::absoluteBase() . '/manuals';
    }

    /** Slides of the default guide, or the library when there are no guides yet. */
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
        return self::libraryUrl();
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

    /**
     * Store the photo of one step (JPG or PNG, shown on the STEP slide), in app/uploads/manuals/steps.
     * Returns the stored file name.
     */
    public static function storeStepPhoto(array $file, int $manualId, int $stepIndex): string
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || !is_uploaded_file((string) $file['tmp_name'])) {
            throw new RuntimeException('Step ' . $stepIndex . ': the photo could not be uploaded.');
        }
        if ((int) $file['size'] > 8 * 1024 * 1024) {
            throw new RuntimeException('Step ' . $stepIndex . ': the photo is too large (max 8 MB).');
        }
        $info = @getimagesize((string) $file['tmp_name']);
        if (!$info || !in_array($info[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG], true)) {
            throw new RuntimeException('Step ' . $stepIndex . ': use a JPG or PNG photo.');
        }
        $dir = dirname(__DIR__) . '/uploads/manuals/steps';
        if (!is_dir($dir) && !@mkdir($dir, 0755, true) && !is_dir($dir)) {
            throw new RuntimeException('The upload folder could not be created.');
        }
        $stored = $manualId . '_' . $stepIndex . '_' . bin2hex(random_bytes(8)) . ($info[2] === IMAGETYPE_PNG ? '.png' : '.jpg');
        if (!move_uploaded_file((string) $file['tmp_name'], $dir . '/' . $stored)) {
            throw new RuntimeException('Step ' . $stepIndex . ': the photo could not be saved.');
        }
        return $stored;
    }

    public static function stepPhotoPath(string $stored): ?string
    {
        if (!preg_match('#^\d+_\d+_[a-f0-9]{16}\.(jpg|png)$#', $stored)) {
            return null;
        }
        $path = dirname(__DIR__) . '/uploads/manuals/steps/' . $stored;
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

    /** @return string[] the STEP lines (section titles and information pages left out) */
    public static function steps(string $value): array
    {
        return ManualPresentation::stepTexts($value);
    }

    /** @return string[] lower-case, trimmed, comma/newline separated */
    private static function splitList(string $value): array
    {
        $parts = preg_split('/[,\n;]+/', strtolower($value)) ?: [];
        return array_values(array_filter(array_map('trim', $parts), 'strlen'));
    }
}
