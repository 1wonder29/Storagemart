<?php
/**
 * Cancel ticket button partial.
 *
 * Expected variables:
 * - $ticketId (int)
 * - $ticketStatus (string, optional)
 * - $ticketNumber (string, optional)
 * - $btnClass (string, optional — default btn-danger btn-sm)
 * - $btnBlock (bool, optional)
 */
$ticketId = (int) ($ticketId ?? 0);
$ticketStatus = (string) ($ticketStatus ?? '');
$ticketNumber = (string) ($ticketNumber ?? '');
$btnClass = (string) ($btnClass ?? 'btn-danger btn-sm');
$btnBlock = (bool) ($btnBlock ?? false);
$isDropdownItem = strpos($btnClass, 'dropdown-item') !== false;
$buttonClasses = trim(
    ($isDropdownItem ? '' : 'btn ')
    . $btnClass
    . ($btnBlock ? ' btn-block' : '')
    . ' cancelTicketBtn'
);

$cancellableStatuses = ['Pending', 'In Progress', 'On Hold', 'Reopened'];
$showCancel = $ticketId > 0 && in_array($ticketStatus, $cancellableStatuses, true);

if (!$showCancel) {
    return;
}
?>
<button type="button"
        class="<?= htmlspecialchars($buttonClasses) ?>"
        data-ticket-id="<?= $ticketId ?>"
        data-ticket-num="<?= htmlspecialchars($ticketNumber) ?>"
        data-ticket-status="<?= htmlspecialchars($ticketStatus) ?>">
    <i class="fas fa-ban"></i> Cancel Ticket
</button>
