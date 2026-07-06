<?php
require_once __DIR__ . '/topbar_helpers.php';

$base = rtrim((string) ($base ?? (defined('BASE_URL') ? BASE_URL : '')), '/');
$profileUrl = (string) ($topbarProfileUrl ?? ($base . '/admin/profile'));
$userLabels = tms_topbar_user_labels(
    (string) ($loggedFirstname ?? ''),
    (string) ($loggedPosition ?? ''),
    (string) ($_SESSION['usertype'] ?? '')
);
$displayName = $userLabels['name'];
$displayRole = $userLabels['role'];
?>
<nav class="navbar navbar-expand topbar topbar-modern mb-4 static-top">
    <button id="sidebarToggleTop" class="topbar-mobile-toggle btn btn-link d-md-none mr-2" type="button" aria-label="Toggle sidebar">
        <i class="fa fa-bars"></i>
    </button>

    <ul class="navbar-nav topbar-actions ml-auto">
        <?php require_once __DIR__ . '/notification_dropdown.php'; ?>

        <li class="nav-item dropdown no-arrow">
            <a class="nav-link topbar-user-chip dropdown-toggle" href="#" id="userDropdown" role="button"
                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <span class="topbar-user-meta d-none d-lg-flex<?= $displayRole === '' ? ' topbar-user-meta--single' : '' ?>">
                    <span class="topbar-user-name"><?= htmlspecialchars($displayName) ?></span>
                    <?php if ($displayRole !== ''): ?>
                        <span class="topbar-user-role"><?= htmlspecialchars($displayRole) ?></span>
                    <?php endif; ?>
                </span>
                <span class="topbar-avatar">
                    <img src="<?= htmlspecialchars($base) ?>/assets/img/undraw_profile.svg" alt="">
                </span>
            </a>
            <div class="dropdown-menu dropdown-menu-right topbar-user-dropdown shadow animated--grow-in"
                aria-labelledby="userDropdown">
                <a class="dropdown-item" href="<?= htmlspecialchars($profileUrl) ?>">
                    <i class="fas fa-user fa-sm fa-fw mr-2"></i>
                    Profile
                </a>
                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal">
                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2"></i>
                    Logout
                </a>
            </div>
        </li>
    </ul>
</nav>
