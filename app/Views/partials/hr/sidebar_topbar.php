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
        <a class="nav-link" href="<?= htmlspecialchars($base) ?>/hr/dashboard">
            <i class="fas fa-fw fa-tachometer-alt"></i>
            <span>Dashboard</span>
        </a>
    </li>

    <hr class="sidebar-divider">

    <div class="sidebar-heading">HR Module</div>

    <li class="nav-item <?= ($activePage === 'employees') ? 'active' : '' ?>">
        <a class="nav-link" href="<?= htmlspecialchars($base) ?>/hr/employees">
            <i class="fas fa-users"></i>
            <span>Employees</span>
        </a>
    </li>

    <li class="nav-item <?= ($activePage === 'uniforms') ? 'active' : '' ?>">
        <a class="nav-link" href="<?= htmlspecialchars($base) ?>/hr/uniforms">
            <i class="fas fa-archive"></i>
            <span>Inventory</span>
        </a>
    </li>

    <li class="nav-item <?= ($activePage === 'tickets') ? 'active' : '' ?>">
        <a class="nav-link" href="<?= htmlspecialchars($base) ?>/hr/tickets">
            <i class="fas fa-ticket-alt"></i>
            <span>Tickets</span>
        </a>
    </li>

</ul>
<!-- End of Sidebar -->

<!-- Content Wrapper -->
<div id="content-wrapper" class="d-flex flex-column">

    <!-- Main Content -->
    <div id="content">

<?php
$shellHomeUrl = $base . '/hr/dashboard';
$shellProfileUrl = '';
require __DIR__ . '/../app_topbar.php';
?>
        <!-- End of Topbar -->

<?php require_once __DIR__ . '/../realtime_scripts.php'; ?>
<?php require_once __DIR__ . '/../logout_modal.php'; ?>
