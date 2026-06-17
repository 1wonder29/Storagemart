<?php
/**
 * Shared helpers for IT ticket list views.
 */
if (!function_exists('it_ticket_status_class')) {
    function it_ticket_status_class(string $status): string
    {
        $map = [
            'Pending'     => 'status-pending',
            'In Progress' => 'status-in-progress',
            'On Hold'     => 'status-on-hold',
            'Resolved'    => 'status-resolved',
            'Unresolved'  => 'status-unresolved',
            'Closed'      => 'status-closed',
            'Reopened'    => 'status-reopened',
            'Cancelled'   => 'status-cancelled',
        ];
        return $map[$status] ?? 'status-default';
    }
}

if (!function_exists('it_ticket_priority_class')) {
    function it_ticket_priority_class(string $priority): string
    {
        $p = strtolower(trim($priority));
        if ($p === 'high') return 'high';
        if ($p === 'medium') return 'medium';
        if ($p === 'low') return 'low';
        return 'default';
    }
}

if (!function_exists('it_ticket_priority_options')) {
    function it_ticket_priority_options(array $found = []): array
    {
        $options = ['High', 'Medium', 'Low'];
        foreach ($found as $priority) {
            $priority = trim((string) $priority);
            if ($priority !== '' && !in_array($priority, $options, true)) {
                $options[] = $priority;
            }
        }
        return $options;
    }
}

if (!function_exists('it_ticket_truncate')) {
    function it_ticket_truncate(string $text, int $len = 70): string
    {
        $text = trim($text);
        if ($text === '') return '';
        if (function_exists('mb_strlen') && mb_strlen($text) > $len) {
            return mb_substr($text, 0, $len) . '…';
        }
        if (strlen($text) > $len) {
            return substr($text, 0, $len) . '…';
        }
        return $text;
    }
}

if (!function_exists('it_ticket_format_date')) {
    function it_ticket_format_date(string $date): array
    {
        $ts = strtotime($date);
        if (!$ts) {
            return ['main' => '—', 'time' => '', 'order' => 0];
        }
        return [
            'main'  => date('M j, Y', $ts),
            'time'  => date('g:i A', $ts),
            'order' => $ts,
        ];
    }
}
