<?php
/**
 * Transfer ticket modal — used by AOM and HOM on Operations ticket detail pages.
 *
 * Expected variables:
 *   $base, $routePrefix, $ticket, $transferEmployees, $csrf_token, $canTransferTicket
 * Optional:
 *   $transferAllTickets (bool) — when true, transfers all open tickets from current assignee
 *   $transferableTicketCount (int) — number of tickets that will be transferred
 */
if (empty($canTransferTicket) || empty($transferEmployees)) {
    return;
}

$transferAllTickets = !empty($transferAllTickets);
$ticketId = (int) ($ticket['ticket_id'] ?? 0);
$sourceEmployeeId = (int) ($ticket['employee_id'] ?? 0);
$transferableTicketCount = (int) ($transferableTicketCount ?? 0);
$currentEmployeeName = trim(
    ($ticket['employee_firstname'] ?? $ticket['emp_firstname'] ?? '')
    . ' '
    . ($ticket['employee_lastname'] ?? $ticket['emp_lastname'] ?? '')
);

if ($transferAllTickets && $transferableTicketCount <= 0) {
    return;
}
?>
<div class="mb-3">
    <button type="button" class="btn btn-warning btn-block btn-sm" data-toggle="modal" data-target="#transferTicketModal">
        <i class="fas fa-exchange-alt"></i>
        <?= $transferAllTickets ? 'Transfer All Tickets to Another Employee' : 'Transfer to Another Employee' ?>
    </button>
</div>

<div class="modal fade" id="transferTicketModal" tabindex="-1" role="dialog" aria-labelledby="transferTicketModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <form method="POST" action="<?= htmlspecialchars($base) ?>/<?= htmlspecialchars($routePrefix) ?>/tickets/transfer">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '', ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="ticket_id" value="<?= $ticketId ?>">
            <?php if ($transferAllTickets): ?>
                <input type="hidden" name="source_employee_id" value="<?= $sourceEmployeeId ?>">
            <?php endif; ?>
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title" id="transferTicketModalLabel">
                        <i class="fas fa-exchange-alt"></i>
                        <?= $transferAllTickets ? 'Transfer All Tickets' : 'Transfer Ticket' ?>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <?php if ($transferAllTickets): ?>
                        <p class="small text-muted mb-3">
                            Reassign all open tickets from
                            <strong><?= htmlspecialchars($currentEmployeeName ?: 'current employee') ?></strong>
                            to another Operations employee.
                            <?php if ($transferableTicketCount > 0): ?>
                                <span class="d-block mt-2">
                                    <strong><?= $transferableTicketCount ?></strong>
                                    ticket<?= $transferableTicketCount === 1 ? '' : 's' ?> will be transferred.
                                </span>
                            <?php endif; ?>
                        </p>
                    <?php else: ?>
                        <p class="small text-muted mb-3">
                            Reassign this ticket from
                            <strong><?= htmlspecialchars($currentEmployeeName ?: 'current employee') ?></strong>
                            to another Operations employee.
                        </p>
                    <?php endif; ?>
                    <div class="form-group">
                        <label for="transfer_employee_id" class="font-weight-bold">Transfer To <span class="text-danger">*</span></label>
                        <select class="form-control" name="employee_id" id="transfer_employee_id" required>
                            <option value="">-- Select Employee --</option>
                            <?php foreach ($transferEmployees as $emp): ?>
                                <option value="<?= (int) ($emp['employee_id'] ?? 0) ?>"
                                        data-branch-id="<?= (int) ($emp['branch_id'] ?? 0) ?>">
                                    <?= htmlspecialchars(trim(($emp['lastname'] ?? '') . ', ' . ($emp['firstname'] ?? ''))) ?>
                                    <?php if (!empty($emp['branchName'])): ?>
                                        (<?= htmlspecialchars($emp['branchName']) ?>)
                                    <?php endif; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group mb-0">
                        <label for="transfer_remarks" class="font-weight-bold">Remarks (optional)</label>
                        <textarea class="form-control" name="remarks" id="transfer_remarks" rows="3"
                                  maxlength="500" placeholder="Reason for transfer (optional)"></textarea>
                    </div>
                    <div class="alert alert-info small mt-3 mb-0">
                        <?php if ($transferAllTickets): ?>
                            All open tickets assigned to this employee will be transferred. Resolved, cancelled, and closed tickets are excluded.
                        <?php else: ?>
                            Transfer is not allowed for resolved, cancelled, or closed tickets.
                        <?php endif; ?>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-check"></i>
                        <?= $transferAllTickets ? 'Confirm Transfer All' : 'Confirm Transfer' ?>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
