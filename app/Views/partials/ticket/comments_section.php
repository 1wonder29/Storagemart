<?php
/**
 * Ticket comments section partial.
 *
 * Expected variables:
 * - $ticketId (int)
 * - $canPostComments (bool, optional, default true)
 * - $base (string, optional — falls back to BASE_URL)
 */
$ticketId = (int) ($ticketId ?? 0);
$canPostComments = (bool) ($canPostComments ?? true);
$commentsBase = rtrim($base ?? BASE_URL ?? '', '/');
$sectionId = 'ticketComments_' . $ticketId;
?>
<div class="ticket-comments-section"
     id="<?= htmlspecialchars($sectionId) ?>"
     data-ticket-id="<?= $ticketId ?>"
     data-can-post="<?= $canPostComments ? '1' : '0' ?>"
     data-base-url="<?= htmlspecialchars($commentsBase) ?>">
    <div class="ticket-comments-header">
        <h6><i class="fas fa-comments mr-1"></i> Comments</h6>
        <small>Shared communication for all roles on this ticket</small>
    </div>
    <div class="ticket-comments-list">
        <div class="text-center text-muted py-3 ticket-comments-loading">
            <i class="fas fa-spinner fa-spin"></i> Loading comments...
        </div>
    </div>

    <div class="ticket-comments-form-wrap">
        <div class="ticket-comments-alert alert d-none mb-2 py-2" role="alert"></div>
        <form class="ticket-comments-form">
            <div class="form-group mb-2">
                <label class="small font-weight-bold text-uppercase mb-1" style="color:#64748b;letter-spacing:0.05em;">Add a comment</label>
                <textarea class="form-control ticket-comment-input" rows="3" maxlength="2000"
                          placeholder="Type your message here (visible to everyone on this ticket)..." required></textarea>
            </div>
            <button type="submit" class="btn btn-sm ticket-comment-submit">
                <i class="fas fa-paper-plane mr-1"></i> Post Comment
            </button>
        </form>
    </div>
</div>
