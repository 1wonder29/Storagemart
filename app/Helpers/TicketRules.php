<?php

/**
 * Checks run before any role files a ticket:
 *  - the filer's previous resolved ticket must be rated first,
 *  - no second open ticket for the same problem (same person, or same branch),
 *  - the concern must be specific (device brand/model and where it is).
 *
 * Every store() calls TicketRules::rejectionFor() and shows the returned message.
 */
class TicketRules
{
    private const ACTIVE_STATUSES = ['Open', 'Pending', 'In Progress', 'On Hold'];

    /** Categories about a physical device, where brand/model and location matter. */
    private const DEVICE_CATEGORIES = [
        'Hardware',
        'Network',
        'Desktop and Laptop Usability Issues',
        'Branch CCTV Status Issues',
        'Network Performance (Latency and Bandwidth)',
        'Printer Issues',
        'POS / Payment Terminal Issues',
        'Access Control / Biometric Issues',
        'Telephone Issues',
    ];

    /** Roles that rate tickets from their ticket page (IT and Admin do not). */
    private const RATING_ROLES = [
        'EMPLOYEE' => 'employee',
        'HEAD'     => 'head',
        'HR'       => 'hr',
        'HOM'      => 'hom',
        'OM'       => 'om',
        'AOM'      => 'aom',
    ];

    /**
     * @param array{employee_id:int, category:string, subject?:string, concern_details:string,
     *              inventory_id?:?int, branch_id?:?int, self_filed?:bool} $ticket
     * @return string|null message to show, or null when the ticket may be filed
     */
    public static function rejectionFor(PDO $pdo, array $ticket, string $filerUsertype): ?string
    {
        $role = strtoupper(trim($filerUsertype));
        if ($role === 'ADMIN') {
            return null;
        }

        $employeeId = (int) ($ticket['employee_id'] ?? 0);
        $category = trim((string) ($ticket['category'] ?? ''));
        $subject = trim((string) ($ticket['subject'] ?? ''));
        $details = trim((string) ($ticket['concern_details'] ?? ''));
        $inventoryId = (int) ($ticket['inventory_id'] ?? 0);
        $branchId = (int) ($ticket['branch_id'] ?? 0);
        $selfFiled = (bool) ($ticket['self_filed'] ?? true);

        try {
            if ($selfFiled && isset(self::RATING_ROLES[$role]) && $employeeId > 0) {
                $unrated = self::latestUnratedTicket($pdo, $employeeId);
                if ($unrated) {
                    return 'Please rate your previous ticket ' . $unrated['ticket_number']
                        . ' first (open it from My Tickets and click Rate), then file your new ticket.';
                }
            }

            $vague = self::vaguenessError($category, $subject, $details, $inventoryId > 0);
            if ($vague !== null) {
                return $vague;
            }

            $duplicate = self::duplicateOf($pdo, $employeeId, $branchId, $category, $inventoryId, $subject . ' ' . $details);
            if ($duplicate !== null) {
                return $duplicate;
            }
        } catch (Throwable $e) {
            // A missing table/column must never stop people from filing tickets.
            error_log('TicketRules::rejectionFor failed: ' . $e->getMessage());
        }

        return null;
    }

    /** The filer's most recent resolved/closed ticket, when they have not rated it. */
    public static function latestUnratedTicket(PDO $pdo, int $employeeId): ?array
    {
        $stmt = $pdo->prepare("SELECT t.ticket_id, t.ticket_number
                FROM tbltickets t
                WHERE t.employee_id = :employee_id
                  AND t.status IN ('Resolved', 'Closed')
                  AND t.assigned_to IS NOT NULL
                ORDER BY COALESCE(t.last_updated, t.date_filed) DESC, t.ticket_id DESC
                LIMIT 1");
        $stmt->execute([':employee_id' => $employeeId]);
        $latest = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$latest) {
            return null;
        }

        $rated = $pdo->prepare('SELECT COUNT(*) FROM ticket_ratings WHERE ticket_id = :ticket_id');
        $rated->execute([':ticket_id' => (int) $latest['ticket_id']]);
        return (int) $rated->fetchColumn() > 0 ? null : $latest;
    }

    /**
     * Generic one-liners such as "Printer is not working" are refused; device problems
     * need the brand/model (or a selected asset) and where the device is.
     */
    public static function vaguenessError(string $category, string $subject, string $details, bool $hasAsset): ?string
    {
        $text = trim($subject . ' ' . $details);
        $normalized = strtolower(trim(preg_replace('/[^\p{L}\p{N}\s-]+/u', ' ', $details)));
        $normalized = preg_replace('/\s+/', ' ', $normalized);
        $wordCount = preg_match_all('/[\p{L}\p{N}][\p{L}\p{N}-]*/u', $details);

        $devices = 'printer|computer|pc|laptop|desktop|internet|wifi|wi-fi|network|cctv|camera|email|outlook|'
            . 'phone|telephone|pos|terminal|biometric|system|monitor|mouse|keyboard|ups|router|scanner';
        if (preg_match('/^(the |my |our |yung |ang )?(' . $devices . ')( is| are)? ?(not working|not work|broken|down|sira|error|problem|issue|ayaw|hindi gumagana|no connection|offline|slow)$/', $normalized)) {
            return 'Please be specific. Instead of "' . trim($details) . '", say which device or system (brand/model), '
                . 'where it is, and what exactly happens - e.g. "Brother DCP-L2540DW printer at the front desk shows '
                . 'Paper Jam and will not print since this morning."';
        }

        if ($wordCount < 6) {
            return 'Please describe the problem in more detail (at least one full sentence): which device or system, '
                . 'where it is, and what exactly happens or what error appears.';
        }

        if (!in_array($category, self::DEVICE_CATEGORIES, true) || $hasAsset) {
            return null;
        }

        $brands = 'epson|brother|canon|hp|lenovo|asus|acer|dell|msi|samsung|xiaomi|huawei|tp-?link|cisco|mikrotik|'
            . 'ubiquiti|hikvision|dahua|kedacom|ezviz|zkteco|panasonic|logitech|a4tech|apc|secure|realme|oppo|vivo|'
            . 'iphone|apple|toshiba|sharp|ricoh|xerox|kyocera|pldt|globe|converge|smart|chuwi|dere|nvision|viewsonic|'
            . 'aoc|philips|grandstream|yealink|ingenico|verifone|sunmi|fortinet|tenda|d-?link|ruijie';
        $hasModel = preg_match('/\b(' . $brands . ')\b/i', $text)
            // A model-like token mixes letters and digits (L2540DW, M2020, 650VA), but not "2nd".
            || preg_match('/\b(?!\d+(?:st|nd|rd|th)\b)(?=[a-z0-9-]*\d)(?=[a-z0-9-]*[a-z])[a-z0-9-]{3,}\b/i', $text)
            // ...or a numbered unit: "camera 3", "channel 12", "terminal 1", "PC #2".
            || preg_match('/\b(camera|cam|channel|ch|unit|terminal|pc|computer|station|printer|phone|line|port|biometric|no)\.?\s*#?\s*\d+\b/i', $text);
        $hasPlace = preg_match('/\b(front ?desk|counter|reception|lobby|office|room|floor|area|desk|station|cashier|'
            . 'admin|warehouse|unit|aisle|entrance|gate|door|stock ?room|pantry|conference|meeting|branch|bodega|'
            . 'taas|baba|ground|upstairs|downstairs|hallway|guard|server|rack|cabinet|table)\b/i', $text);

        if (!$hasModel || !$hasPlace) {
            $missing = [];
            if (!$hasModel) {
                $missing[] = 'the brand/model (e.g. Brother DCP-L2540DW, Lenovo IdeaPad 3)';
            }
            if (!$hasPlace) {
                $missing[] = 'where it is (e.g. front desk, 2nd floor admin office)';
            }
            return 'Please be specific so IT can act faster - add ' . implode(' and ', $missing)
                . '. You can also pick the asset from My Assets.';
        }

        return null;
    }

    /** An open ticket for the same problem by the same person, or reported at the same branch today. */
    public static function duplicateOf(PDO $pdo, int $employeeId, int $branchId, string $category, int $inventoryId, string $text): ?string
    {
        if ($category === '') {
            return null;
        }
        $placeholders = implode(',', array_fill(0, count(self::ACTIVE_STATUSES), '?'));

        // Same person, same category, still open (a different selected asset is a different problem).
        if ($employeeId > 0) {
            $stmt = $pdo->prepare("SELECT ticket_number, inventory_id FROM tbltickets
                    WHERE employee_id = ? AND category = ? AND status IN ($placeholders)
                    ORDER BY date_filed DESC");
            $stmt->execute(array_merge([$employeeId, $category], self::ACTIVE_STATUSES));
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $otherAsset = (int) ($row['inventory_id'] ?? 0);
                if ($inventoryId > 0 && $otherAsset > 0 && $otherAsset !== $inventoryId) {
                    continue;
                }
                return 'You already have an open ticket (' . $row['ticket_number'] . ') for "' . $category
                    . '". Please add the new details in that ticket\'s chat instead of filing another one.';
            }
        }

        // Someone else at the branch reported the same thing in the last 24 hours.
        if ($branchId > 0) {
            $stmt = $pdo->prepare("SELECT t.ticket_number, t.subject, t.concern_details, t.inventory_id
                    FROM tbltickets t
                    WHERE t.branch_id = ? AND t.category = ? AND t.employee_id <> ?
                      AND t.status IN ($placeholders)
                      AND t.date_filed >= (NOW() - INTERVAL 1 DAY)");
            $stmt->execute(array_merge([$branchId, $category, $employeeId], self::ACTIVE_STATUSES));
            $mine = self::normalize($text);
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $sameAsset = $inventoryId > 0 && (int) ($row['inventory_id'] ?? 0) === $inventoryId;
                $theirs = self::normalize(($row['subject'] ?? '') . ' ' . ($row['concern_details'] ?? ''));
                similar_text($mine, $theirs, $percent);
                if ($sameAsset || $percent >= 60) {
                    return 'This looks already reported at your branch today (' . $row['ticket_number']
                        . '). IT is working on it; please follow up in that ticket instead of filing a new one.';
                }
            }
        }

        return null;
    }

    /**
     * Turn a rejection into a flash message, keep what was typed (the shared ticket form
     * refills it once) and return the app path of the form the user came from.
     */
    public static function rejectAndReturnPath(string $message, array $post, string $fallbackPath): string
    {
        $_SESSION['flash_error'] = $message;
        unset($post['csrf_token']);
        $_SESSION['ticket_old_input'] = $post;

        $referer = (string) ($_SERVER['HTTP_REFERER'] ?? '');
        $refererHost = parse_url($referer, PHP_URL_HOST);
        if ($referer === '' || ($refererHost && $refererHost !== ($_SERVER['HTTP_HOST'] ?? $refererHost)
            && $refererHost !== parse_url('http://' . ($_SERVER['HTTP_HOST'] ?? ''), PHP_URL_HOST))) {
            return $fallbackPath;
        }
        $path = (string) parse_url($referer, PHP_URL_PATH);
        $query = (string) parse_url($referer, PHP_URL_QUERY);
        $basePath = rtrim((string) (parse_url(defined('BASE_URL') ? BASE_URL : '', PHP_URL_PATH) ?? ''), '/');
        if ($basePath !== '' && strpos($path, $basePath) === 0) {
            $path = substr($path, strlen($basePath));
        }
        if ($path === '' || $path[0] !== '/') {
            return $fallbackPath;
        }
        return $path . ($query !== '' ? '?' . $query : '');
    }

    /** Link to the filer's own ticket page, for messages and redirects. */
    public static function ticketsPathFor(string $usertype): string
    {
        $prefix = self::RATING_ROLES[strtoupper(trim($usertype))] ?? null;
        return $prefix ? '/' . $prefix . '/tickets' : '/login';
    }

    private static function normalize(string $text): string
    {
        $text = strtolower(preg_replace('/[^\p{L}\p{N}\s]+/u', ' ', $text));
        return trim(preg_replace('/\s+/', ' ', $text));
    }
}
