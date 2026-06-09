<?php
$cancelBase = rtrim($base ?? BASE_URL ?? '', '/');
?>
<div class="modal fade" id="cancelTicketModal" tabindex="-1" role="dialog" aria-labelledby="cancelTicketModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="cancelTicketModalLabel">
                    <i class="fas fa-ban"></i> Cancel Ticket
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="cancelTicketForm">
                <div class="modal-body">
                    <div id="cancelTicketAlert" class="alert d-none" role="alert"></div>
                    <p class="mb-3">
                        Are you sure you want to cancel
                        <strong id="cancelTicketNumberLabel">this ticket</strong>?
                        This action cannot be undone.
                    </p>
                    <input type="hidden" name="ticket_id" id="cancel_ticket_id" value="">
                    <div class="form-group mb-0">
                        <label for="cancel_reason" class="font-weight-bold">Reason for cancellation <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="cancel_reason" name="cancel_reason" rows="3" maxlength="500"
                                  placeholder="Please explain why this ticket is being cancelled..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-danger" id="cancelTicketSubmitBtn">
                        <i class="fas fa-ban"></i> Confirm Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
window.TICKET_CANCEL_BASE_URL = "<?= htmlspecialchars($cancelBase) ?>";
</script>
<script src="<?= htmlspecialchars($cancelBase) ?>/assets/js/ticket/ticket_cancel.js"></script>
