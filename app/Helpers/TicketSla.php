<?php

/**
 * Organisation-wide ticket SLA rules, expressed as SQL fragments so dashboards and
 * ticket lists can filter with them.
 *
 *  - Overdue: Open / In Progress / Pending tickets past their priority deadline
 *    (Critical 1 day, High 2 days, Medium 5 days, Low 7 days from the filing date).
 *  - Resolution SLA breach: resolved after 24 hours, or still open and filed more
 *    than 24 hours ago.
 */
class TicketSla
{
    public const RESOLUTION_SLA_HOURS = 24;

    public const PRIORITY_DEADLINE_HOURS = [
        'critical' => 24,
        'high'   => 48,
        'medium' => 120,
        'low'    => 168,
    ];

    public const ACTIVE_STATUSES = ['Open', 'In Progress', 'Pending'];
    public const FINISHED_STATUSES = ['Resolved', 'Closed'];

    public static function deadlineHours(string $priority): int
    {
        $key = strtolower(trim($priority));
        return self::PRIORITY_DEADLINE_HOURS[$key] ?? self::PRIORITY_DEADLINE_HOURS['medium'];
    }

    /** SQL: active ticket that has run past its priority deadline. */
    public static function overdueCondition(string $alias = 't'): string
    {
        $t = self::alias($alias);
        $critical = self::PRIORITY_DEADLINE_HOURS['critical'];
        $high = self::PRIORITY_DEADLINE_HOURS['high'];
        $medium = self::PRIORITY_DEADLINE_HOURS['medium'];
        $low = self::PRIORITY_DEADLINE_HOURS['low'];

        return "{$t}.status IN (" . self::quoted(self::ACTIVE_STATUSES) . ")
            AND {$t}.date_filed IS NOT NULL
            AND TIMESTAMPDIFF(MINUTE, {$t}.date_filed, NOW()) > (
                CASE LOWER(TRIM({$t}.priority))
                    WHEN 'critical' THEN {$critical}
                    WHEN 'high' THEN {$high}
                    WHEN 'low' THEN {$low}
                    ELSE {$medium}
                END
            ) * 60";
    }

    /** SQL: resolved after the 24h SLA, or still active and filed more than 24h ago. */
    public static function resolutionBreachCondition(string $alias = 't'): string
    {
        $t = self::alias($alias);
        $limitMinutes = self::RESOLUTION_SLA_HOURS * 60;

        return "{$t}.date_filed IS NOT NULL AND (
                ({$t}.status IN (" . self::quoted(self::FINISHED_STATUSES) . ")
                    AND {$t}.last_updated IS NOT NULL
                    AND TIMESTAMPDIFF(MINUTE, {$t}.date_filed, {$t}.last_updated) > {$limitMinutes})
                OR ({$t}.status IN (" . self::quoted(self::ACTIVE_STATUSES) . ")
                    AND TIMESTAMPDIFF(MINUTE, {$t}.date_filed, NOW()) > {$limitMinutes})
            )";
    }

    private static function alias(string $alias): string
    {
        $clean = preg_replace('/[^A-Za-z0-9_]/', '', $alias);
        return $clean !== '' ? $clean : 't';
    }

    /** @param string[] $values */
    private static function quoted(array $values): string
    {
        return "'" . implode("', '", $values) . "'";
    }
}
