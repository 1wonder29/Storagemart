<?php

/**
 * Seasonal looks for the whole system (Halloween, Christmas, ...). Admin adds, edits and
 * deletes themes (tblthemes), previews one in their own browser, then schedules it with a
 * release date / time and an end. Each theme plays its own list of screen effects.
 * The look and the effects are drawn by public/assets/js/global-widgets.js and theme-effects.js.
 */
class SeasonalThemes
{
    /** Screen effects a theme can use (key => label, short description). */
    public const EFFECTS = [
        'rain'      => ['Rain', 'Rain drops and a lightning flash'],
        'zombies'   => ['Zombies', 'Zombies walk across the bottom of the screen'],
        'bats'      => ['Bats', 'Bats fly across the screen'],
        'ghosts'    => ['Ghosts', 'Ghosts float up and fade'],
        'snow'      => ['Snow', 'Snowflakes fall gently'],
        'sleigh'    => ['Santa\'s sleigh', 'Santa and his reindeer fly across the top'],
        'lights'    => ['Christmas lights', 'Blinking lights along the top of the screen'],
        'fireworks' => ['Fireworks', 'Fireworks burst over the page'],
        'confetti'  => ['Confetti', 'Confetti in the theme colors'],
        'sparkles'  => ['Sparkles', 'Twinkling sparkles'],
        'hearts'    => ['Floating hearts', 'Hearts rise from the bottom'],
        'petals'    => ['Flower petals', 'Petals fall and sway'],
        'sun'       => ['Sunshine', 'A glowing sun in the corner'],
        'bubbles'   => ['Bubbles', 'Bubbles rise and wobble'],
        'leaves'    => ['Tropical leaves', 'Leaves and flowers fall'],
        'waves'     => ['Ocean waves', 'Waves along the bottom of the screen'],
        'flags'     => ['Philippine flags', 'Small Philippine flags fall and wave'],
        'stars'     => ['Stars', 'Stars fall and twinkle'],
        'balloons'  => ['Balloons', 'Balloons float up'],
        'items'     => ['Theme icons', 'The theme\'s own icons fall from the top'],
    ];

    /** Icons the Admin can pick for a theme. */
    public const ICONS = [
        'fa-ghost', 'fa-holly-berry', 'fa-glass-cheers', 'fa-heart', 'fa-umbrella-beach', 'fa-flag', 'fa-star',
        'fa-gift', 'fa-birthday-cake', 'fa-tree', 'fa-sun', 'fa-snowflake', 'fa-leaf', 'fa-moon', 'fa-cross',
        'fa-church', 'fa-graduation-cap', 'fa-trophy', 'fa-medal', 'fa-music', 'fa-paw', 'fa-palette', 'fa-calendar-day',
    ];

    /**
     * Built-in themes (also written to tblthemes by the update SQL; used when that table is missing,
     * and by "Restore built-in themes").
     */
    public const CATALOG = [
        'halloween' => [
            'name' => 'Halloween', 'icon' => 'fa-ghost', 'effects' => ['rain', 'zombies', 'bats', 'ghosts'],
            'items' => ['🎃', '🦇', '👻', '🕸️'], 'colors' => ['#ff7518', '#3b1f4a'],
            'message' => 'Happy Halloween from StorageMart! 🎃', 'from' => '10-24', 'to' => '11-01',
        ],
        'christmas' => [
            'name' => 'Christmas', 'icon' => 'fa-holly-berry', 'effects' => ['snow', 'sleigh', 'lights'],
            'items' => ['❄', '❅', '❆', '🎄'], 'colors' => ['#c0392b', '#1e8449'],
            'message' => 'Merry Christmas from StorageMart! 🎄', 'from' => '12-01', 'to' => '12-31',
        ],
        'new_year' => [
            'name' => 'New Year', 'icon' => 'fa-glass-cheers', 'effects' => ['fireworks', 'confetti', 'sparkles'],
            'items' => ['🎉', '✨', '🎊'], 'colors' => ['#d4af37', '#1f2a44'],
            'message' => 'Happy New Year! Wishing you a great year ahead. 🎉', 'from' => '12-31', 'to' => '01-07',
        ],
        'valentines' => [
            'name' => "Valentine's Day", 'icon' => 'fa-heart', 'effects' => ['hearts', 'petals'],
            'items' => ['❤️', '💕', '💖'], 'colors' => ['#e84393', '#fd79a8'],
            'message' => "Happy Valentine's Day! 💕", 'from' => '02-10', 'to' => '02-15',
        ],
        'tropical' => [
            'name' => 'Tropical Summer', 'icon' => 'fa-umbrella-beach', 'effects' => ['sun', 'bubbles', 'leaves', 'waves'],
            'items' => ['🌴', '🌺', '☀️', '🍹', '🥥'], 'colors' => ['#00b894', '#fdcb6e'],
            'message' => 'Summer is here! Stay cool and hydrated. 🌴', 'from' => '04-01', 'to' => '05-31',
        ],
        'independence' => [
            'name' => 'Independence Day (PH)', 'icon' => 'fa-flag', 'effects' => ['flags', 'confetti', 'stars'],
            'items' => ['⭐', '☀️'], 'colors' => ['#0038a8', '#ce1126'],
            'message' => 'Happy Independence Day, Philippines!', 'from' => '06-10', 'to' => '06-13',
        ],
        'anniversary' => [
            'name' => 'Company Celebration', 'icon' => 'fa-star', 'effects' => ['balloons', 'confetti', 'fireworks'],
            'items' => ['🎈', '🎉', '⭐'], 'colors' => ['#f26522', '#1f3a93'],
            'message' => 'Thank you for being part of StorageMart! 🎈', 'from' => '', 'to' => '',
        ],
    ];

    /** @var array<string, array>|null cached for the request */
    private static $themes = null;

    /** @return array<string, array> every theme (key => name, icon, items, colors, message, from, to, effects, builtin) */
    public static function all(PDO $pdo): array
    {
        if (self::$themes !== null) {
            return self::$themes;
        }
        try {
            $rows = $pdo->query('SELECT * FROM tblthemes ORDER BY sort_order, name')->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            $rows = null; // table not created yet: built-in list
        }
        $themes = [];
        if ($rows === null) {
            foreach (self::CATALOG as $key => $theme) {
                $themes[$key] = $theme + ['builtin' => true];
            }
        } else {
            foreach ($rows as $row) {
                $themes[(string) $row['theme_key']] = self::fromRow($row);
            }
        }
        return self::$themes = $themes;
    }

    public static function forget(): void
    {
        self::$themes = null;
    }

    private static function fromRow(array $row): array
    {
        $items = preg_split('/[\s,]+/u', trim((string) $row['items'])) ?: [];
        $effects = array_values(array_filter(array_map('trim', explode(',', (string) $row['effects'])), static function ($key) {
            return isset(self::EFFECTS[$key]);
        }));
        return [
            'name' => (string) $row['name'],
            'icon' => (string) ($row['icon'] ?: 'fa-palette'),
            'effects' => $effects,
            'items' => array_values(array_filter($items, 'strlen')),
            'colors' => [(string) $row['color_main'], (string) $row['color_accent']],
            'message' => (string) $row['message'],
            'from' => (string) $row['date_from'],
            'to' => (string) $row['date_to'],
            'builtin' => (int) $row['is_builtin'] === 1,
        ];
    }

    public static function get(PDO $pdo, string $key): ?array
    {
        return self::all($pdo)[$key] ?? null;
    }

    public static function exists(PDO $pdo, string $key): bool
    {
        return self::get($pdo, $key) !== null;
    }

    /** Theme data for the browser (no PHP-only fields). */
    public static function forBrowser(PDO $pdo, string $key, ?string $message = null): ?array
    {
        $theme = self::get($pdo, $key);
        if (!$theme) {
            return null;
        }
        return [
            'key' => $key,
            'name' => $theme['name'],
            'effects' => $theme['effects'] ?: ['items'],
            'items' => $theme['items'] ?: ['✨'],
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
        $theme = self::forBrowser($pdo, (string) $row['theme_key'], $row['message']);
        if ($theme) {
            $theme['schedule_id'] = (int) $row['schedule_id'];
        }
        return $theme;
    }

    /** Suggested next date range for a theme (this year, or next year when already past). */
    public static function suggestedRange(array $theme): ?array
    {
        if (empty($theme['from']) || empty($theme['to'])) {
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

    /** A new theme key from its name (letters, numbers, underscores; unique). */
    public static function newKey(PDO $pdo, string $name): string
    {
        $slug = trim(preg_replace('/[^a-z0-9]+/', '_', strtolower($name)), '_') ?: 'theme';
        $slug = substr($slug, 0, 24);
        $key = $slug;
        $n = 2;
        while (self::exists($pdo, $key)) {
            $key = substr($slug, 0, 24) . '_' . $n++;
        }
        return $key;
    }
}
