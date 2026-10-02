<?php
/**
 * Bulk transfer tickets modal — used by AOM and HOM ticket list pages.
 *
 * Expected: $base, $branches, $csrf_token, $routePrefix
 * Optional: $bulkTransferAction — form POST URL (defaults to /{routePrefix}/tickets/bulk-transfer)
 */
if (empty($branches)) {
    return;
}

$bulkTransferAction = $bulkTransferAction ?? (rtrim($base, '/') . '/' . $routePrefix . '/tickets/bulk-transfer');
?>
<div class="modal fade" id="bulkTransferModal" tabindex="-1" role="dialog" aria-labelledby="bulkTransferModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable bulk-transfer-modal" role="document">
        <form method="POST" action="<?= htmlspecialchars($bulkTransferAction) ?>" id="bulkTransferForm">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '', ENT_QUOTES, 'UTF-8') ?>">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title" id="bulkTransferModalLabel">
                        <i class="fas fa-exchange-alt"></i> Bulk Transfer Tickets
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="small text-muted bulk-transfer-intro">
                        Transfer all tickets from an employee in the selected branch to any Operations employee.
                        All ticket statuses are included.
                    </p>

                    <div class="form-group bulk-transfer-field">
                        <label for="bulk_transfer_branch_id" class="font-weight-bold d-block">Branch (Transfer From) <span class="text-danger">*</span></label>
                        <select class="form-control" name="branch_id" id="bulk_transfer_branch_id" required>
                            <option value="">-- Select Branch --</option>
                            <?php foreach ($branches as $branch): ?>
                                <option value="<?= (int) ($branch['branch_id'] ?? 0) ?>">
                                    <?= htmlspecialchars(trim((string) ($branch['branchName'] ?? ''))) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="form-text text-muted">Type to search and select a branch.</small>
                    </div>

                    <div class="form-group bulk-transfer-field">
                        <label for="bulk_transfer_source_employee_id" class="font-weight-bold d-block">Transfer From <span class="text-danger">*</span></label>
                        <select class="form-control" name="source_employee_id" id="bulk_transfer_source_employee_id" required disabled>
                            <option value="">-- Select branch first --</option>
                        </select>
                        <small class="form-text text-muted">Only employees with tickets in the selected branch are shown.</small>
                    </div>

                    <div class="form-group bulk-transfer-field">
                        <label for="bulk_transfer_employee_id" class="font-weight-bold d-block">Transfer To <span class="text-danger">*</span></label>
                        <select class="form-control" name="employee_id" id="bulk_transfer_employee_id" required>
                            <option value="">-- Select Employee --</option>
                        </select>
                        <small class="form-text text-muted">Type to search all Operations department employees.</small>
                    </div>

                    <div id="bulkTransferCountWrap" class="alert alert-info small d-none bulk-transfer-count">
                        <span id="bulkTransferCountText"></span>
                    </div>

                    <div class="form-group bulk-transfer-field mb-0">
                        <label for="bulk_transfer_remarks" class="font-weight-bold d-block">Remarks (optional)</label>
                        <textarea class="form-control" name="remarks" id="bulk_transfer_remarks" rows="3"
                                  maxlength="500" placeholder="Reason for transfer (optional)"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning" id="bulkTransferSubmitBtn" disabled>
                        <i class="fas fa-check"></i> Confirm Bulk Transfer
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
