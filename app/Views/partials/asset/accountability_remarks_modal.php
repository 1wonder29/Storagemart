<?php
$remarksFormAction = $remarksFormAction ?? '';
$returnUrl = $returnUrl ?? '';
$csrf_token = $csrf_token ?? '';
?>
<div class="modal fade" id="editRemarksModal" tabindex="-1" role="dialog" aria-labelledby="editRemarksModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="POST" action="<?= htmlspecialchars($remarksFormAction) ?>">
                <div class="modal-header">
                    <h5 class="modal-title" id="editRemarksModalLabel">Edit Accountability Remarks</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                    <input type="hidden" name="return_url" value="<?= htmlspecialchars($returnUrl) ?>">
                    <input type="hidden" name="assignment_id" id="editAssignmentId" value="">
                    <div class="form-group mb-0">
                        <label for="editRemarksText">Remarks</label>
                        <textarea class="form-control" id="editRemarksText" name="remarks" rows="4" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Remarks</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
(function () {
    if (typeof window.jQuery === 'undefined') {
        return;
    }
    jQuery(document).on('click', '.btn-edit-accountability-remarks', function () {
        jQuery('#editAssignmentId').val(jQuery(this).data('assignment-id') || '');
        jQuery('#editRemarksText').val(jQuery(this).data('remarks') || '');
        jQuery('#editRemarksModal').modal('show');
    });
})();
</script>
