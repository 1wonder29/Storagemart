<?php
$count = $count ?? 0;
$notifications = $notifications ?? [];
$activePage = $activePage ?? '';
$user = $user ?? [];

$base = rtrim(BASE_URL, '/');
$routePrefix = $routePrefix ?? (($user_role ?? '') === 'HOM' ? 'hom' : 'om');
?>

<?php require_once __DIR__ . '/../sidebar_styles.php'; ?>

<!-- Sidebar -->
<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion sidebar-modern" id="accordionSidebar">

<?php require __DIR__ . '/../sidebar_user.php'; ?>

    <!-- Dashboard -->
    <li class="nav-item <?= ($activePage === 'dashboard') ? 'active' : '' ?>">
        <a class="nav-link" href="<?= htmlspecialchars($base) ?>/<?= htmlspecialchars($routePrefix) ?>/dashboard">
            <i class="fas fa-fw fa-tachometer-alt"></i>
            <span>Dashboard</span>
        </a>
    </li>

    <hr class="sidebar-divider">

    <div class="sidebar-heading">Assignment Management</div>

    <!-- Employees -->
    <li class="nav-item <?= ($activePage === 'employees') ? 'active' : '' ?>">
        <a class="nav-link" href="<?= htmlspecialchars($base) ?>/<?= htmlspecialchars($routePrefix) ?>/employees">
            <i class="fas fa-users"></i>
            <span>Employees</span>
        </a>
    </li>

    <!-- Assets -->
    <li class="nav-item <?= ($activePage === 'assets') ? 'active' : '' ?>">
        <a class="nav-link" href="<?= htmlspecialchars($base) ?>/<?= htmlspecialchars($routePrefix) ?>/assets">
            <i class="fas fa-archive"></i>
            <span>Assets</span>
        </a>
    </li>

    <!-- AOM Branch Assignments -->
    <li class="nav-item <?= ($activePage === 'aom-branches') ? 'active' : '' ?>">
        <a class="nav-link" href="<?= htmlspecialchars($base) ?>/<?= htmlspecialchars($routePrefix) ?>/aom-branches">
            <i class="fas fa-building"></i>
            <span>AOM Branches</span>
        </a>
    </li>

    <hr class="sidebar-divider">

    <div class="sidebar-heading">Ticket Management</div>

    <!-- Tickets -->
    <li class="nav-item <?= ($activePage === 'tickets') ? 'active' : '' ?>">
        <a class="nav-link" href="<?= htmlspecialchars($base) ?>/<?= htmlspecialchars($routePrefix) ?>/tickets">
            <i class="fas fa-ticket-alt"></i>
            <span>Tickets</span>
        </a>
    </li>

    <!-- Create Ticket -->
    <li class="nav-item <?= ($activePage === 'create-my-ticket') ? 'active' : '' ?>">
        <a class="nav-link" href="<?= htmlspecialchars($base) ?>/<?= htmlspecialchars($routePrefix) ?>/tickets/create/my">
            <i class="fas fa-user"></i>
            <span>My Ticket</span>
        </a>
    </li>

    <li class="nav-item <?= ($activePage === 'create-employee-ticket') ? 'active' : '' ?>">
        <a class="nav-link" href="<?= htmlspecialchars($base) ?>/<?= htmlspecialchars($routePrefix) ?>/tickets/create/employee">
            <i class="fas fa-plus-circle"></i>
            <span>Employee Ticket</span>
        </a>
    </li>

</ul>
<!-- End of Sidebar -->

<!-- Content Wrapper -->
<div id="content-wrapper" class="d-flex flex-column">

    <!-- Main Content -->
    <div id="content">

<?php
$shellHomeUrl = $base . '/' . $routePrefix . '/dashboard';
$shellProfileUrl = $base . '/' . $routePrefix . '/profile';
require __DIR__ . '/../app_topbar.php';
?>
        <!-- End of Topbar -->

<?php require_once __DIR__ . '/../realtime_scripts.php'; ?>
<?php require_once __DIR__ . '/../logout_modal.php'; ?>
