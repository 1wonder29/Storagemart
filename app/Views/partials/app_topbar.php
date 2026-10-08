<?php
/**
 * Shared header for every role.
 * Left: sidebar toggle + logo. Right: time, date, notifications, dark mode.
 * Profile and logout live in the sidebar user card.
 *
 * Expected (set by the role's sidebar partial): $shellHomeUrl, $shellProfileUrl
 */
require_once __DIR__ . '/../../Helpers/AppShell.php';

$base = rtrim(BASE_URL, '/');
$shellUser = AppShell::currentUser();
$shellHomeUrl = $shellHomeUrl ?? ($base . '/');
$shellProfileUrl = $shellProfileUrl ?? '';
$shellDisplayName = $shellUser['fullname'] !== '' ? $shellUser['fullname'] : (string) ($loggedFirstname ?? '');
?>
<!-- Topbar -->
<nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow app-topbar">
    <button type="button" id="appSidebarToggle" class="app-topbar-hamburger"
            aria-label="Toggle sidebar" aria-controls="accordionSidebar" aria-expanded="true" title="Toggle sidebar">
        <i class="fas fa-bars" aria-hidden="true"></i>
    </button>

    <a class="app-topbar-brand" href="<?= htmlspecialchars($shellHomeUrl) ?>" title="Storage Mart TMS">
        <img src="<?= htmlspecialchars($base) ?>/assets/img/storagemart-logo.png" alt="Storage Mart TMS">
    </a>

    <ul class="navbar-nav ml-auto align-items-center">
        <li class="nav-item app-topbar-clock d-none d-md-flex" title="Philippine time">
            <span class="app-clock-time" id="appClockTime"><?= htmlspecialchars(date('g:i A')) ?></span>
            <span class="app-clock-date" id="appClockDate"><?= htmlspecialchars(date('D, M j, Y')) ?></span>
        </li>

        <div class="topbar-divider d-none d-md-block"></div>

        <?php require_once __DIR__ . '/notification_dropdown.php'; ?>

        <li class="nav-item mx-1 d-flex align-items-center">
            <button type="button" id="itDarkModeToggle" class="btn btn-link nav-link py-2"
                    aria-label="Toggle dark mode" aria-pressed="false" title="Switch to dark mode">
                <i class="fas fa-moon" id="itDarkModeIcon"></i>
            </button>
        </li>

        <!-- Filled by global-widgets.js (e.g. the birthday greeting button) -->
        <li class="nav-item d-flex align-items-center app-topbar-extras" id="appTopbarExtras"></li>
    </ul>
</nav>
<script src="<?= htmlspecialchars($base) ?>/assets/js/app-shell.js?v=1" defer></script>
<script src="<?= htmlspecialchars($base) ?>/assets/js/it-dark-mode.js" defer></script>
