<?php

class TicketSla
{
    public const RESOLUTION_SLA_HOURS = 24;

    public static function overdueCondition(string $alias = 't'): string
    {
        return "
            {$alias}.status IN ('Open', 'In Progress', 'Pending')
            AND {$alias}.date_filed < DATE_SUB(
                NOW(),
                INTERVAL CASE UPPER(TRIM({$alias}.priority))
                    WHEN 'HIGH' THEN 2
                    WHEN 'MEDIUM' THEN 5
                    ELSE 7
                END DAY
            )
        ";
    }

    /**
     * Tickets that missed the resolution SLA (resolved late or still open past the limit).
     */
    public static function resolutionBreachCondition(string $alias = 't', int $slaHours = self::RESOLUTION_SLA_HOURS): string
    {
        $slaHours = max(1, (int) $slaHours);

        return "
            (
                {$alias}.status IN ('Resolved', 'Closed')
                AND {$alias}.last_updated IS NOT NULL
                AND {$alias}.date_filed IS NOT NULL
                AND TIMESTAMPDIFF(MINUTE, {$alias}.date_filed, {$alias}.last_updated) / 60 > {$slaHours}
            )
            OR (
                {$alias}.status IN ('Open', 'In Progress', 'Pending')
                AND {$alias}.date_filed < DATE_SUB(NOW(), INTERVAL {$slaHours} HOUR)
            )
        ";
    }
}
