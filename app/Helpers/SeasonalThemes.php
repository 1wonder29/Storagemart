<?php

/**
 * Seasonal looks for the whole system (Halloween, Christmas, ...). Admin previews a
 * theme in their own browser, then schedules it with a release date / time and an end.
 * The look itself is drawn by public/assets/js/global-widgets.js.
 */
class SeasonalThemes
{
    /**
     * key => name, effect (falling items), items (emoji), colors [main, accent],
     * default greeting, and suggested dates (month-day, used for "this year").
     */
    public const CATALOG = [
        'halloween' => [
            'name' => 'Halloween', 'icon' => 'fa-ghost', 'effect' => 'float',
            'items' => ['🎃', '🦇', '👻', '🕸️'], 'colors' => ['#ff7518', '#3b1f4a'],
            'message' => 'Happy Halloween from StorageMart! 🎃', 'from' => '10-24', 'to' => '11-01',
        ],
        'christmas' => [
            'name' => 'Christmas', 'icon' => 'fa-holly-berry', 'effect' => 'snow',
            'items' => ['❄', '❅', '❆', '🎄'], 'colors' => ['#c0392b', '#1e8449'],
            'message' => 'Merry Christmas from StorageMart! 🎄', 'from' => '12-01', 'to' => '12-31',
        ],
        'new_year' => [
            'name' => 'New Year', 'icon' => 'fa-glass-cheers', 'effect' => 'confetti',
            'items' => ['🎉', '✨', '🎊'], 'colors' => ['#d4af37', '#1f2a44'],
            'message' => 'Happy New Year! Wishing you a great year ahead. 🎉', 'from' => '12-31', 'to' => '01-07',
        ],
        'valentines' => [
            'name' => "Valentine's Day", 'icon' => 'fa-heart', 'effect' => 'float',
            'items' => ['❤️', '💕', '💖'], 'colors' => ['#e84393', '#fd79a8'],
            'message' => "Happy Valentine's Day! 💕", 'from' => '02-10', 'to' => '02-15',
        ],
        'tropical' => [
            'name' => 'Tropical Summer', 'icon' => 'fa-umbrella-beach', 'effect' => 'float',
            'items' => ['🌴', '🌺', '☀️', '🍹', '🥥'], 'colors' => ['#00b894', '#fdcb6e'],
            'message' => 'Summer is here! Stay cool and hydrated. 🌴', 'from' => '04-01', 'to' => '05-31',
        ],
        'independence' => [
            'name' => 'Independence Day (PH)', 'icon' => 'fa-flag', 'effect' => 'confetti',
            'items' => ['🇵🇭', '⭐', '☀️'], 'colors' => ['#0038a8', '#ce1126'],
            'message' => 'Happy Independence Day, Philippines! 🇵🇭', 'from' => '06-10', 'to' => '06-13',
        ],
        'anniversary' => [
            'name' => 'Company Celebration', 'icon' => 'fa-star', 'effect' => 'confetti',
            'items' => ['🎈', '🎉', '⭐'], 'colors' => ['#f26522', '#1f3a93'],
            'message' => 'Thank you for being part of StorageMart! 🎈', 'from' => '', 'to' => '',
        ],
    ];

    public static function exists(string $key): bool
    {
        return isset(self::CATALOG[$key]);
    }

    /** Theme data for the browser (no PHP-only fields). */
    public static function forBrowser(string $key, ?string $message = null): ?array
    {
        if (!self::exists($key)) {
            return null;
        }
        $theme = self::CATALOG[$key];
        return [
            'key' => $key,
            'name' => $theme['name'],
            'effect' => $theme['effect'],
            'items' => $theme['items'],
            'colors' => $theme['colors'],
            'message' => ($message !== null && trim($message) !== '') ? trim($message) : $theme['message'],
        ];
    }

    /** The scheduled theme that is live right now, if any. */
    public static function current(PDO $pdo): ?array
    {
        try {
            $row = $pdo->query('SELECT schedule_id, theme_key, message, ends_at FROM tblthemes_schedule
                    WHERE is_active = 1 AND starts_at <= NOW() AND ends_at > NOW()
                    ORDER BY starts_at DESC LIMIT 1')->fetch(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            return null;
        }
        if (!$row) {
            return null;
        }
        $theme = self::forBrowser((string) $row['theme_key'], $row['message']);
        if ($theme) {
            $theme['schedule_id'] = (int) $row['schedule_id'];
        }
        return $theme;
    }

    /** Suggested next date range for a theme (this year, or next year when already past). */
    public static function suggestedRange(string $key): ?array
    {
        $theme = self::CATALOG[$key] ?? null;
        if (!$theme || $theme['from'] === '') {
            return null;
        }
        $year = (int) date('Y');
        $from = $year . '-' . $theme['from'];
        $toYear = $theme['to'] < $theme['from'] ? $year + 1 : $year;
        $to = $toYear . '-' . $theme['to'];
        if ($to . ' 23:59:59' < date('Y-m-d H:i:s')) {
            $from = ($year + 1) . '-' . $theme['from'];
            $to = ($toYear + 1) . '-' . $theme['to'];
        }
        return ['from' => $from . 'T00:00', 'to' => $to . 'T23:59'];
    }
}
