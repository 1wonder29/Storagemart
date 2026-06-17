<?php
/**
 * Shared ticket detail layout: info card + comments (left), actions + history (right).
 *
 * Expected: $ticket
 * Optional: $history, $ticketHistory, $routePrefix, $base, $canPostComments,
 *           plus flags passed through to ticket_detail_actions.php
 */
$status = (string) ($ticket['status'] ?? 'Pending');
$historyEntries = $history ?? $ticketHistory ?? [];
$ticketId = (int) ($ticket['ticket_id'] ?? 0);
$employeeName = trim(
    (string) (($ticket['emp_firstname'] ?? $ticket['employee_firstname'] ?? '') . ' ' . ($ticket['emp_lastname'] ?? $ticket['employee_lastname'] ?? ''))
) ?: 'Unassigned';

$actionTaken = trim((string) ($ticket['action_taken'] ?? ''));
$resolutionDetails = trim((string) ($ticket['resolution_details'] ?? $ticket['result'] ?? ''));

if ($actionTaken === '' && $resolutionDetails === '' && $ticketId > 0) {
    require_once __DIR__ . '/../../../Models/TicketTechnicalModel.php';
    $technical = (new TicketTechnicalModel())->getLatestByTicketId($ticketId);
    if ($technical) {
        $actionTaken = trim((string) ($technical['action_taken'] ?? ''));
        $resolutionDetails = trim((string) ($technical['result'] ?? ''));
    }
}

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
                    <?php if ($showUpdateAssignmentInHeader && !empty($showUpdateAssignment) && strcasecmp($status, 'resolved') !== 0): ?>
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
                    <div class="col-md-4">
                        <div class="small text-gray-500 text-uppercase font-weight-bold">Department</div>
                        <div class="h6 mb-0"><?= htmlspecialchars((string) ($ticket['department'] ?? '-')) ?></div>
                    </div>
                    <div class="col-md-4">
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
