<?php

require_once __DIR__ . '/AppSettings.php';

/**
 * Asset age from the Year Purchased field. Items this many years old or older
 * (default 5; Admin can change it) are tagged "For replacement".
 */
class AssetAge
{
    public const DEFAULT_YEARS = 5;

    public static function threshold(): int
    {
        $years = AppSettings::getInt('asset_replacement_years', self::DEFAULT_YEARS);
        return $years >= 1 && $years <= 30 ? $years : self::DEFAULT_YEARS;
    }

    /** Whole years since purchase, or null when the year is blank / not a year. */
    public static function age($yearPurchased): ?int
    {
        if (!preg_match('/\b(19[89]\d|20\d\d)\b/', (string) $yearPurchased, $m)) {
            return null;
        }
        $age = (int) date('Y') - (int) $m[1];
        return $age >= 0 ? $age : null;
    }

    public static function isDue($yearPurchased): bool
    {
        $age = self::age($yearPurchased);
        return $age !== null && $age >= self::threshold();
    }

    /** SQL condition for "due for replacement" on an inventory alias (uses YEAR(CURDATE())). */
    public static function dueSql(string $alias = 'i'): string
    {
        $years = self::threshold();
        return "({$alias}.year_purchased REGEXP '^[[:space:]]*(19[89][0-9]|20[0-9][0-9])'
                 AND CAST(TRIM({$alias}.year_purchased) AS UNSIGNED) <= YEAR(CURDATE()) - {$years})";
    }
}
