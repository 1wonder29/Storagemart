<?php
/**
 * Signed-in user card at the top of every sidebar: name, email, position, logout.
 * Also restores the saved collapsed state before the sidebar is painted.
 */
require_once __DIR__ . '/../../Helpers/AppShell.php';

$base = rtrim(BASE_URL, '/');
$sidebarUser = AppShell::currentUser();
$sidebarProfileUrl = AppShell::profileUrl();
?>
<li class="sidebar-user">
    <script>
    (function () {
        try {
            if (localStorage.getItem('app-sidebar-collapsed') === '1') {
                var sidebar = document.currentScript.closest('.sidebar');
                if (sidebar) sidebar.classList.add('toggled');
                document.body.classList.add('sidebar-toggled');
            }
        } catch (e) {}
    })();
    </script>
    <div class="sidebar-user-card">
        <?php if ($sidebarProfileUrl !== ''): ?>
        <a class="sidebar-user-profile" href="<?= htmlspecialchars($sidebarProfileUrl) ?>" title="View my profile">
            <img class="sidebar-user-avatar" src="<?= htmlspecialchars($base) ?>/assets/img/undraw_profile.svg" alt="My profile">
        </a>
        <?php else: ?>
        <img class="sidebar-user-avatar" src="<?= htmlspecialchars($base) ?>/assets/img/undraw_profile.svg" alt="">
        <?php endif; ?>
        <div class="sidebar-user-info">
            <?php if ($sidebarProfileUrl !== ''): ?>
            <a class="sidebar-user-name sidebar-user-profile" href="<?= htmlspecialchars($sidebarProfileUrl) ?>" title="View my profile"><?= htmlspecialchars($sidebarUser['fullname']) ?></a>
            <?php else: ?>
            <div class="sidebar-user-name"><?= htmlspecialchars($sidebarUser['fullname']) ?></div>
            <?php endif; ?>
            <?php if ($sidebarUser['email'] !== ''): ?>
                <div class="sidebar-user-email" title="<?= htmlspecialchars($sidebarUser['email']) ?>"><?= htmlspecialchars($sidebarUser['email']) ?></div>
            <?php endif; ?>
            <?php if ($sidebarUser['position'] !== ''): ?>
                <div class="sidebar-user-position"><?= htmlspecialchars($sidebarUser['position']) ?></div>
            <?php endif; ?>
            <a class="sidebar-user-link" href="<?= htmlspecialchars($base) ?>/change-password">
                <i class="fas fa-key" aria-hidden="true"></i> Change password
            </a>
        </div>
        <button type="button" class="sidebar-logout" data-toggle="modal" data-target="#logoutModal" title="Logout">
            <i class="fas fa-sign-out-alt" aria-hidden="true"></i>
            <span>Logout</span>
        </button>
    </div>
</li>
<hr class="sidebar-divider my-0">
