<?php
if (!empty($_SESSION['flash_error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show alert-modern" role="alert">
        <i class="fas fa-exclamation-circle mr-1"></i> <?= htmlspecialchars((string) $_SESSION['flash_error']) ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    </div>
    <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>

<?php if (!empty($_SESSION['flash_success'])): ?>
    <div class="alert alert-success alert-dismissible fade show alert-modern" role="alert">
        <i class="fas fa-check-circle mr-1"></i> <?= htmlspecialchars((string) $_SESSION['flash_success']) ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    </div>
    <?php unset($_SESSION['flash_success']); ?>
<?php endif; ?>

<?php if (!empty($_SESSION['success_message'])): ?>
    <div class="alert alert-success alert-dismissible fade show alert-modern" role="alert">
        <i class="fas fa-check-circle mr-1"></i> <?= htmlspecialchars((string) $_SESSION['success_message']) ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    </div>
    <?php unset($_SESSION['success_message']); ?>
<?php endif; ?>

<?php if (!empty($_SESSION['error_message'])): ?>
    <div class="alert alert-danger alert-dismissible fade show alert-modern" role="alert">
        <i class="fas fa-exclamation-circle mr-1"></i> <?= htmlspecialchars((string) $_SESSION['error_message']) ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    </div>
    <?php unset($_SESSION['error_message']); ?>
<?php endif; ?>
