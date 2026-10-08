<?php
require_once __DIR__ . '/../../../Helpers/SuperUser.php';
if (SuperUser::isActingInRoleArea()) {
    require __DIR__ . '/../admin/sidebar_topbar.php';
    return;
}
?>
<?php
$base = rtrim(BASE_URL, '/');
?>
<?php require_once __DIR__ . '/../sidebar_styles.php'; ?>
        <!-- Sidebar -->
        <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion sidebar-modern" id="accordionSidebar">

            <?php require __DIR__ . '/../sidebar_user.php'; ?>

            <!-- Dashboard -->
            <li class="nav-item <?= ($activePage === 'dashboard') ? 'active' : '' ?>">
                <a class="nav-link" href="<?= htmlspecialchars($base)?>/it">
                    <i class="fas fa-fw fa-tachometer-alt"></i>
                    <span>Dashboard</span></a>
            </li>

            <hr class="sidebar-divider">

            <div class="sidebar-heading">Interface</div>

            <li class="nav-item <?= ($activePage === 'tickets') ? 'active' : '' ?>">
                <a class="nav-link" href="<?= htmlspecialchars($base)?>/it/tickets">
                    <i class="fas fa-ticket-alt"></i>
                    <span>Ticket</span>
                </a>
            </li>
            <li class="nav-item <?= ($activePage === 'assets') ? 'active' : '' ?>">
                <a class="nav-link" href="<?= htmlspecialchars($base)?>/it/assets">
                    <i class="fas fa-archive"></i>
                    <span>My Assets</span>
                </a>
            </li>
            <li class="nav-item <?= ($activePage === 'items') ? 'active' : '' ?>">
                <a class="nav-link" href="<?= htmlspecialchars($base)?>/it/items">
                    <i class="fas fa-tshirt"></i>
                    <span>My Issued Items</span>
                </a>
            </li>

            <li class="nav-item <?= ($activePage === 'uploads') ? 'active' : '' ?>">
                <a class="nav-link" href="<?= htmlspecialchars($base)?>/it/uploads">
                    <i class="fas fa-file-upload"></i>
                    <span>Employee Uploads</span>
                </a>
            </li>

            <li class="nav-item <?= ($activePage === 'ratings') ? 'active' : '' ?>">
                <a class="nav-link" href="<?= htmlspecialchars($base)?>/it/ratings">
                    <i class="fas fa-star"></i>
                    <span>My Ratings</span>
                </a>
            </li>
        </ul>
        <!-- End of Sidebar -->

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">

<?php
$shellHomeUrl = $base . '/it';
$shellProfileUrl = $base . '/it/profile';
require __DIR__ . '/../app_topbar.php';
?>
<?php require_once __DIR__ . '/../realtime_scripts.php'; ?>
<?php require_once __DIR__ . '/../logout_modal.php'; ?>
