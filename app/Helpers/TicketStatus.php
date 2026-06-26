<?php

class TicketStatus
{
    public const OPEN = 'Open';
    public const IN_PROGRESS = 'In Progress';
    public const PENDING = 'Pending';
    public const RESOLVED = 'Resolved';
    public const CLOSED = 'Closed';
    public const CANCELLED = 'Cancelled';

    /** Status for a newly filed ticket (not yet assigned to IT). */
    public static function initial(): string
    {
        return self::OPEN;
    }

    /** Status after the ticket is assigned to an IT team member. */
    public static function assigned(): string
    {
        return self::IN_PROGRESS;
    }

    /**
     * @return string[]
     */
    public static function all(): array
    {
        return [
            self::OPEN,
            self::IN_PROGRESS,
            self::PENDING,
            self::RESOLVED,
            self::CLOSED,
            self::CANCELLED,
        ];
    }
}
