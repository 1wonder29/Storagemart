<?php

/**
 * Screen effects made by the Admin (Themes > Custom effects): one of the styles below, played with
 * uploaded pictures (for example the IT team's faces) and / or emoji. Stored in tblcustom_effects;
 * pictures in app/uploads/effects, shown to signed-in users through /effects/image.
 * A theme uses one with the key "c" + effect_id (e.g. "c3") next to the built-in effects.
 */
class CustomEffects
{
    public const STYLES = [
        'fireworks' => ['Fireworks', 'Rockets go up and burst into your pictures'],
        'fall'      => ['Falling', 'Your pictures fall from the top and sway'],
        'rise'      => ['Floating up', 'Your pictures float up like balloons'],
        'pop'       => ['Popping', 'Your pictures pop up around the screen'],
        'fly'       => ['Flying across', 'Your pictures fly across the screen'],
    ];
    public const MAX_IMAGES = 6;
    public const MAX_BYTES = 3145728; // 3 MB per picture

    private static $rows = null;

    public static function ready(PDO $pdo): bool
    {
        try {
            $pdo->query('SELECT 1 FROM tblcustom_effects LIMIT 1');
            return true;
        } catch (Throwable $e) {
            return false;
        }
    }

    public static function isKey(string $key): bool
    {
        return (bool) preg_match('/^c\d{1,9}$/', $key);
    }

    /** @return array<string, array> "c3" => effect */
    public static function all(PDO $pdo): array
    {
        if (self::$rows !== null) {
            return self::$rows;
        }
        $out = [];
        try {
            foreach ($pdo->query('SELECT * FROM tblcustom_effects ORDER BY name')->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $out['c' . (int) $row['effect_id']] = [
                    'id' => (int) $row['effect_id'],
                    'key' => 'c' . (int) $row['effect_id'],
                    'name' => (string) $row['name'],
                    'style' => isset(self::STYLES[$row['style']]) ? (string) $row['style'] : 'fall',
                    'images' => array_values(array_filter((array) json_decode((string) $row['images'], true), [self::class, 'validName'])),
                    'emoji' => array_values(array_filter(preg_split('/\s+/u', trim((string) $row['emoji'])) ?: [], 'strlen')),
                    'round' => (int) $row['round_images'] === 1,
                    'size' => max(24, min(160, (int) $row['size'])),
                    'amount' => max(4, min(60, (int) $row['amount'])),
                ];
            }
        } catch (Throwable $e) {
            $out = []; // table not created yet
        }
        return self::$rows = $out;
    }

    public static function forget(): void
    {
        self::$rows = null;
    }

    /** What the browser needs to play the effects (picture links instead of file names). */
    public static function forBrowser(PDO $pdo, ?array $keys = null): array
    {
        $base = rtrim(defined('BASE_URL') ? BASE_URL : '', '/');
        $out = [];
        foreach (self::all($pdo) as $key => $effect) {
            if ($keys !== null && !in_array($key, $keys, true)) {
                continue;
            }
            $out[$key] = [
                'name' => $effect['name'],
                'style' => $effect['style'],
                'images' => array_map(static fn($f) => $base . '/effects/image?f=' . rawurlencode($f), $effect['images']),
                'emoji' => $effect['emoji'],
                'round' => $effect['round'],
                'size' => $effect['size'],
                'amount' => $effect['amount'],
            ];
        }
        return $out;
    }

    /** Saves an uploaded picture; returns the stored file name. */
    public static function storeImage(array $file): string
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || !is_uploaded_file((string) $file['tmp_name'])) {
            throw new RuntimeException('A picture could not be uploaded.');
        }
        if ((int) $file['size'] > self::MAX_BYTES) {
            throw new RuntimeException('A picture is too large (max 3 MB each).');
        }
        $info = @getimagesize((string) $file['tmp_name']);
        $types = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_GIF => 'gif', IMAGETYPE_WEBP => 'webp'];
        if (!$info || !isset($types[$info[2]])) {
            throw new RuntimeException('Use JPG, PNG, GIF or WebP pictures.');
        }
        $dir = self::dir();
        if (!is_dir($dir) && !@mkdir($dir, 0755, true) && !is_dir($dir)) {
            throw new RuntimeException('The upload folder could not be created.');
        }
        $stored = bin2hex(random_bytes(12)) . '.' . $types[$info[2]];
        if (!move_uploaded_file((string) $file['tmp_name'], $dir . '/' . $stored)) {
            throw new RuntimeException('A picture could not be saved.');
        }
        return $stored;
    }

    public static function imagePath(string $stored): ?string
    {
        if (!self::validName($stored)) {
            return null;
        }
        $path = self::dir() . '/' . $stored;
        return is_file($path) ? $path : null;
    }

    public static function deleteImages(array $names): void
    {
        foreach ($names as $name) {
            $path = self::imagePath((string) $name);
            if ($path !== null) {
                @unlink($path);
            }
        }
    }

    public static function validName($name): bool
    {
        return is_string($name) && (bool) preg_match('/^[a-f0-9]{24}\.(jpg|png|gif|webp)$/', $name);
    }

    private static function dir(): string
    {
        return dirname(__DIR__) . '/uploads/effects';
    }
}
