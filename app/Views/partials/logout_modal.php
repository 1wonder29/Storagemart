<?php
$base = rtrim($base ?? BASE_URL ?? '', '/');
?>
<div class="modal fade logout-modal" id="logoutModal" tabindex="-1" role="dialog"
     aria-labelledby="logoutModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <button type="button" class="logout-modal-close" data-dismiss="modal" aria-label="Close">
                <i class="fas fa-times" aria-hidden="true"></i>
            </button>
            <div class="modal-body text-center">
                <div class="logout-modal-icon" aria-hidden="true">
                    <i class="fas fa-sign-out-alt"></i>
                </div>
                <h5 class="logout-modal-title" id="logoutModalLabel">Ready to Leave?</h5>
                <p class="logout-modal-text">You'll be signed out of your session. Sign in again anytime to continue.</p>
            </div>
            <div class="modal-footer logout-modal-footer">
                <button type="button" class="btn logout-modal-cancel" data-dismiss="modal">Stay signed in</button>
                <a class="btn logout-modal-confirm" href="<?= htmlspecialchars($base) ?>/logout">Sign out</a>
            </div>
        </div>
    </div>
</div>
