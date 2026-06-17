<?php
/**
 * Sidebar action buttons for ticket detail (replaces the old Summary card).
 *
 * Expected: $ticket, $status, $base, $routePrefix
 * Optional: $ticketsListUrl, $showTechnicalUpload, $showRateDownload,
 *           $showUpdateAssignment
 */
$ticketId = (int) ($ticket['ticket_id'] ?? 0);
$ticketStatus = (string) ($ticket['status'] ?? '');
$ticketNumber = (string) ($ticket['ticket_number'] ?? '');
$status = (string) ($status ?? $ticketStatus);
$routePrefix = $routePrefix ?? 'employee';
$showTechnicalUpload = (bool) ($showTechnicalUpload ?? ($status === 'Resolved'));
$showRateDownload = (bool) ($showRateDownload ?? ($status === 'Resolved'));
$showUpdateAssignment = (bool) ($showUpdateAssignment ?? false);
$showUpdateAssignmentInHeader = (bool) ($showUpdateAssignmentInHeader ?? false);
$btnBlock = true;

$canShowTransfer = !empty($showTransferTicket)
    && !empty($canTransferTicket)
    && !empty($transferEmployees);

$showUpdateInSidebar = $showUpdateAssignment
    && !$showUpdateAssignmentInHeader
    && strcasecmp($status, 'resolved') !== 0;

$hasSidebarActions = ($showRateDownload && $status === 'Resolved')
    || $showUpdateInSidebar
    || $canShowTransfer;

$hasActions = ($showTechnicalUpload && $status === 'Resolved') || $hasSidebarActions;

if (!$hasActions) {
    return;
}
?>
<?php if ($showTechnicalUpload && $status === 'Resolved'): ?>
<div class="card shadow mb-4 ticket-detail-actions">
    <div class="card-header py-3 ticket-detail-section-header">
        <h6 class="m-0 font-weight-bold"><i class="fas fa-file-upload"></i> Technical Report</h6>
    </div>
    <div class="card-body">
        <div id="uploadMsg" class="alert d-none" role="alert"></div>
        <form id="uploadForm" enctype="multipart/form-data">
            <input type="hidden" name="ticket_id" value="<?= $ticketId ?>">
            <div class="form-group">
                <label class="small text-gray-600 font-weight-bold">Select File (PDF, DOCX, DOC, JPG, PNG - Max 10MB)</label>
                <input type="file" class="form-control-file" name="report_file" required accept=".pdf,.docx,.doc,.jpg,.jpeg,.png">
            </div>
            <button type="submit" class="btn btn-primary btn-block" id="uploadBtn">
                <i class="fas fa-upload"></i> Upload Report
            </button>
        </form>
    </div>
</div>
<?php endif; ?>

<?php if ($hasSidebarActions): ?>
<div class="card shadow mb-4 ticket-detail-actions">
    <div class="card-body">
        <?php if ($showRateDownload && $status === 'Resolved'): ?>
        <div class="mb-3">
            <a href="<?= htmlspecialchars(rtrim($base ?? BASE_URL ?? '', '/')) ?>/<?= htmlspecialchars($routePrefix) ?>/tickets/download-record?id=<?= $ticketId ?>" class="btn btn-outline-secondary btn-block btn-sm">
                <i class="fas fa-download"></i> Download Technical Record
            </a>
        </div>
        <div class="mb-3">
            <button type="button" class="btn btn-outline-secondary btn-block btn-sm rateBtn" data-ticketid="<?= $ticketId ?>">
                <i class="fas fa-star"></i> Rate Ticket
            </button>
        </div>
        <?php endif; ?>

        <?php if ($showUpdateInSidebar): ?>
        <div class="mb-3">
            <button type="button" class="btn btn-primary btn-block btn-sm openUpdateAssignBtn"
                data-ticket-id="<?= $ticketId ?>"
                data-assignedid="<?= (int) ($ticket['assigned_to'] ?? 0) ?>"
                data-status="<?= htmlspecialchars($status) ?>">
                <i class="fas fa-edit"></i> Update Assignment
            </button>
        </div>
        <?php endif; ?>

        <?php if ($canShowTransfer): ?>
            <?php require __DIR__ . '/transfer_ticket_modal.php'; ?>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>
