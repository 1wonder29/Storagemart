<?php
/**
 * Shared helpers for IT ticket list views.
 */
require_once dirname(__DIR__, 3) . '/Helpers/TicketStatus.php';

if (!function_exists('it_ticket_status_class')) {
    function it_ticket_status_class(string $status): string
    {
        $map = [
            'Open'        => 'status-open',
            'Pending'     => 'status-pending',
            'In Progress' => 'status-in-progress',
            'On Hold'     => 'status-pending',
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

if (!function_exists('it_ticket_all_statuses')) {
    function it_ticket_all_statuses(): array
    {
        return TicketStatus::all();
    }
}

if (!function_exists('it_ticket_status_filter_options')) {
    /**
     * @param string[] $foundStatuses
     */
    function it_ticket_status_filter_options(array $foundStatuses = []): array
    {
        $options = it_ticket_all_statuses();
        foreach ($foundStatuses as $status) {
            $status = trim((string) $status);
            if ($status !== '' && !in_array($status, $options, true) && in_array($status, TicketStatus::all(), true)) {
                $options[] = $status;
            }
        }
        return $options;
    }
}

if (!function_exists('it_ticket_stat_tone')) {
    function it_ticket_stat_tone(string $status): string
    {
        if ($status === 'Open') {
            return 'warning';
        }
        if ($status === 'In Progress') {
            return 'info';
        }
        if ($status === 'Pending') {
            return 'secondary';
        }
        if ($status === 'Resolved') {
            return 'success';
        }
        if ($status === 'Cancelled') {
            return 'danger';
        }
        return 'secondary';
    }
}

if (!function_exists('it_ticket_summary_links')) {
  /**
   * @return array<string, string>
   */
    function it_ticket_summary_links(string $base): array
    {
        $base = rtrim($base, '/');

        return [
            TicketStatus::OPEN         => $base . '/it/tickets/open',
            TicketStatus::IN_PROGRESS  => $base . '/it/tickets/in_progress',
            TicketStatus::PENDING      => $base . '/it/tickets/pending',
            TicketStatus::RESOLVED     => $base . '/it/tickets/resolve',
            TicketStatus::CLOSED       => $base . '/it/tickets/closed',
            TicketStatus::CANCELLED    => $base . '/it/tickets/cancelled',
        ];
    }
}

if (!function_exists('ticket_assignment_can_update')) {
    function ticket_assignment_can_update(string $status): bool
    {
        $status = strtolower(trim($status));
        return !in_array($status, ['resolved', 'closed', 'cancelled'], true);
    }
}
