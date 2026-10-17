<?php

/**
 * Small key/value store (tblsettings) for admin-adjustable options such as the
 * ticket reassign delay. Reads fall back to the default when the table is missing.
 */
class AppSettings
{
    /** @var array<string, string|null> */
    private static $cache = [];

    public static function get(string $key, ?string $default = null): ?string
    {
        if (array_key_exists($key, self::$cache)) {
            return self::$cache[$key] ?? $default;
        }
        global $pdo;
        try {
            $stmt = $pdo->prepare('SELECT setting_value FROM tblsettings WHERE setting_key = ? LIMIT 1');
            $stmt->execute([$key]);
            $value = $stmt->fetchColumn();
            self::$cache[$key] = $value === false ? null : (string) $value;
        } catch (Throwable $e) {
            self::$cache[$key] = null;
        }
        return self::$cache[$key] ?? $default;
    }

    public static function getInt(string $key, int $default): int
    {
        $value = self::get($key);
        return ($value !== null && is_numeric($value)) ? (int) $value : $default;
    }

    public static function set(string $key, ?string $value): bool
    {
        global $pdo;
        try {
            $stmt = $pdo->prepare('INSERT INTO tblsettings (setting_key, setting_value, updated_at)
                    VALUES (:k, :v, NOW())
                    ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = NOW()');
            $ok = $stmt->execute([':k' => $key, ':v' => $value]);
            self::$cache[$key] = $value;
            return $ok;
        } catch (Throwable $e) {
            error_log('AppSettings::set failed for ' . $key . ': ' . $e->getMessage());
            return false;
        }
    }
}
