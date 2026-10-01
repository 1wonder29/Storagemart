<?php
/**
 * Shared ticket detail layout: info card + comments (left), actions + history (right).
 *
 * Expected: $ticket
 * Optional: $history, $ticketHistory, $routePrefix, $base, $canPostComments,
 *           plus flags passed through to ticket_detail_actions.php
 */
require_once __DIR__ . '/../it/ticket_view_helpers.php';
require_once __DIR__ . '/../../../Helpers/TicketFormFields.php';
$status = (string) ($ticket['status'] ?? 'Open');
$historyEntries = $history ?? $ticketHistory ?? [];
$ticketId = (int) ($ticket['ticket_id'] ?? 0);
$ticketSubject = trim(TicketFormFields::subjectFor($ticketId));
$employeeName = trim(
    (string) (($ticket['emp_firstname'] ?? $ticket['employee_firstname'] ?? '') . ' ' . ($ticket['emp_lastname'] ?? $ticket['employee_lastname'] ?? ''))
) ?: 'Unassigned';

$actionTaken = trim((string) ($ticket['action_taken'] ?? ''));
$resolutionDetails = trim((string) ($ticket['resolution_details'] ?? $ticket['result'] ?? ''));

$technical = null;
if ($ticketId > 0) {
    require_once __DIR__ . '/../../../Models/TicketTechnicalModel.php';
    $technical = (new TicketTechnicalModel())->getLatestByTicketId($ticketId);
    if ($technical && $actionTaken === '' && $resolutionDetails === '') {
        $actionTaken = trim((string) ($technical['action_taken'] ?? ''));
        $resolutionDetails = trim((string) ($technical['result'] ?? ''));
    }
}

// Only the IT staff this ticket is assigned to may edit Action Taken / Resolution,
// and only while the ticket hasn't already been finalized.
$viewerEmployeeId = isset($employeeId) ? (int) $employeeId : 0;
$canEditResolution = ($routePrefix ?? '') === 'it'
    && strtoupper((string) ($_SESSION['usertype'] ?? '')) === 'IT'
    && $viewerEmployeeId > 0
    && (int) ($ticket['assigned_to'] ?? 0) === $viewerEmployeeId
    && ticket_assignment_can_update($status);

$displayValue = static function (string $value): string {
    return $value !== '' ? $value : '-';
};
$showUpdateAssignmentInHeader = (bool) ($showUpdateAssignmentInHeader ?? false);
$showDownloadTechnicalRecord = (bool) ($showDownloadTechnicalRecord ?? false);
$detailBase = rtrim($base ?? BASE_URL ?? '', '/');
$detailRoutePrefix = $routePrefix ?? 'employee';
?>
<div class="row ticket-detail-layout" data-realtime-ticket-detail data-ticket-id="<?= $ticketId ?>">
    <div class="col-lg-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3 ticket-detail-card-header">
                <div class="d-flex align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-ticket-alt"></i>
                        <?= htmlspecialchars((string) ($ticket['ticket_number'] ?? ('#' . $ticketId))) ?>
                    </h6>
                    <?php if ($showUpdateAssignmentInHeader && !empty($showUpdateAssignment) && ticket_assignment_can_update($status)): ?>
                        <button type="button" class="btn btn-primary btn-sm openUpdateAssignBtn"
                            data-ticket-id="<?= $ticketId ?>"
                            data-assignedid="<?= (int) ($ticket['assigned_to'] ?? 0) ?>"
                            data-status="<?= htmlspecialchars($status) ?>">
                            <i class="fas fa-edit"></i> Update Assignment
                        </button>
                    <?php endif; ?>
                    <?php if ($showDownloadTechnicalRecord && strcasecmp($status, 'resolved') === 0): ?>
                        <a href="<?= htmlspecialchars($detailBase) ?>/<?= htmlspecialchars($detailRoutePrefix) ?>/tickets/download-record?id=<?= $ticketId ?>"
                           class="btn btn-success btn-sm" title="Generate technical report">
                            <i class="fas fa-file-word"></i> Generate Report
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card-body">
                <?php if ($ticketSubject !== ''): ?>
                <div class="mb-3">
                    <div class="small text-gray-500 text-uppercase font-weight-bold">Subject</div>
                    <div class="h5 mb-0 font-weight-bold"><?= htmlspecialchars($ticketSubject) ?></div>
                </div>
                <?php endif; ?>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="small text-gray-500 text-uppercase font-weight-bold">Ticket ID</div>
                        <div class="h6 mb-0"><?= $ticketId ?></div>
                    </div>
                    <div class="col-md-4">
                        <div class="small text-gray-500 text-uppercase font-weight-bold">Employee</div>
                        <div class="h6 mb-0"><?= htmlspecialchars($employeeName) ?></div>
                    </div>
                    <div class="col-md-4">
                        <div class="small text-gray-500 text-uppercase font-weight-bold">Branch</div>
                        <div class="h6 mb-0"><?= htmlspecialchars((string) ($ticket['branchName'] ?? '-')) ?></div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="small text-gray-500 text-uppercase font-weight-bold">Status</div>
                        <div class="h6 mb-0" data-ticket-status><?= htmlspecialchars($status) ?></div>
                    </div>
                    <div class="col-md-4">
                        <div class="small text-gray-500 text-uppercase font-weight-bold">Priority</div>
                        <div class="h6 mb-0" data-ticket-priority><?= htmlspecialchars((string) ($ticket['priority'] ?? '-')) ?></div>
                    </div>
                    <div class="col-md-4">
                        <div class="small text-gray-500 text-uppercase font-weight-bold">Filed</div>
                        <div class="h6 mb-0">
                            <?= !empty($ticket['date_filed']) ? date('M d, Y', strtotime((string) $ticket['date_filed'])) : '-' ?>
                        </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="small text-gray-500 text-uppercase font-weight-bold">Department</div>
                        <div class="h6 mb-0"><?= htmlspecialchars((string) ($ticket['department'] ?? '-')) ?></div>
                    </div>
                    <div class="col-md-6">
                        <div class="small text-gray-500 text-uppercase font-weight-bold">Category</div>
                        <div class="h6 mb-0"><?= htmlspecialchars((string) ($ticket['category'] ?? '-')) ?></div>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="small text-gray-500 text-uppercase font-weight-bold">Concern</div>
                    <div class="p-3 bg-light rounded border">
                        <?= $displayValue((string) ($ticket['concern_details'] ?? '')) !== '-'
                            ? nl2br(htmlspecialchars((string) $ticket['concern_details']))
                            : '-' ?>
                    </div>
                </div>

                <?php if ($canEditResolution): ?>
                    <form method="POST" action="<?= htmlspecialchars($detailBase) ?>/it/tickets/update" class="resolution-edit-form">
                        <input type="hidden" name="ticket_id" value="<?= $ticketId ?>">
                        <input type="hidden" name="action" value="<?= htmlspecialchars($status) ?>">
                        <input type="hidden" name="technical_purpose" value="<?= htmlspecialchars((string) ($technical['technical_purpose'] ?? '')) ?>">
                        <input type="hidden" name="remarks" value="<?= htmlspecialchars((string) ($technical['remarks'] ?? '')) ?>">
                        <input type="hidden" name="return_to" value="/it/tickets/view?id=<?= $ticketId ?>">

                        <div class="mb-3">
                            <label class="small text-gray-500 text-uppercase font-weight-bold" for="priority_field">Priority</label>
                            <select class="form-control" id="priority_field" name="priority">
                                <?php foreach (TicketFormFields::PRIORITIES as $priorityOption): ?>
                                    <option value="<?= $priorityOption ?>"<?= strcasecmp((string) ($ticket['priority'] ?? ''), $priorityOption) === 0 ? ' selected' : '' ?>><?= $priorityOption ?></option>
                                <?php endforeach; ?>
                            </select>
                            <small class="form-text text-muted">Only IT personnel can change a ticket's priority.</small>
                        </div>

                        <div class="mb-3">
                            <label class="small text-gray-500 text-uppercase font-weight-bold" for="action_taken_field">Action Taken</label>
                            <textarea class="form-control" id="action_taken_field" name="action_taken" rows="3" placeholder="Describe what was done to address the issue"><?= htmlspecialchars($actionTaken) ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="small text-gray-500 text-uppercase font-weight-bold" for="result_field">Resolution Details</label>
                            <textarea class="form-control" id="result_field" name="result" rows="3" placeholder="Outcome or follow-up notes for the requester"><?= htmlspecialchars($resolutionDetails) ?></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="fas fa-save"></i> Save Changes
                        </button>
                    </form>
                <?php else: ?>
                    <div class="mb-3">
                        <div class="small text-gray-500 text-uppercase font-weight-bold">Action Taken</div>
                        <div class="p-3 bg-light rounded border">
                            <?= $actionTaken !== '' ? nl2br(htmlspecialchars($actionTaken)) : '-' ?>
                        </div>
                    </div>

                    <div class="mb-0">
                        <div class="small text-gray-500 text-uppercase font-weight-bold">Resolution Details</div>
                        <div class="p-3 bg-light rounded border">
                            <?= $resolutionDetails !== '' ? nl2br(htmlspecialchars($resolutionDetails)) : '-' ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <?php
        $canPostComments = (bool) ($canPostComments ?? true);
        require __DIR__ . '/comments_section.php';
        ?>
    </div>

    <div class="col-lg-4">
        <?php require __DIR__ . '/ticket_detail_actions.php'; ?>

        <div class="card shadow mb-4">
            <div class="card-header py-2 ticket-detail-section-header ticket-history-header">
                <h6 class="m-0 font-weight-bold"><i class="fas fa-history"></i> Ticket History</h6>
            </div>
            <div class="card-body p-0" style="max-height: 320px; overflow-y: auto;">
                <?php if (empty($historyEntries)): ?>
                    <p class="text-muted small mb-0 p-3">No history found.</p>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($historyEntries as $entry): ?>
                            <div class="list-group-item py-2 px-3">
                                <p class="mb-1 small font-weight-bold text-gray-800">
                                    <?= htmlspecialchars((string) ($entry['action_details'] ?? ($entry['action_type'] ?? 'Updated'))) ?>
                                </p>
                                <small class="text-muted">
                                    <?= htmlspecialchars((string) ($entry['performed_by'] ?? $entry['assigned_to'] ?? 'System')) ?> &bull;
                                    <?= !empty($entry['date_logged']) ? date('M d, Y H:i', strtotime((string) $entry['date_logged'])) : '' ?>
                                </small>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
