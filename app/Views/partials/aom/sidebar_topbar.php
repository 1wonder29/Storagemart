<?php
$count = $count ?? 0;
$notifications = $notifications ?? [];
$loggedFirstname = $loggedFirstname ?? ($ctx['loggedFirstname'] ?? 'AOM');
$loggedLastname = $loggedLastname ?? ($ctx['loggedLastname'] ?? '');
$loggedDisplayName = trim($loggedFirstname . ' ' . $loggedLastname) ?: 'AOM';

$base = rtrim(BASE_URL, '/');
?>

<?php require_once __DIR__ . '/../sidebar_styles.php'; ?>

<!-- Sidebar -->
<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion sidebar-modern" id="accordionSidebar">

<?php require __DIR__ . '/../sidebar_user.php'; ?>

    <!-- Dashboard -->
    <li class="nav-item <?= ($activePage === 'dashboard') ? 'active' : '' ?>">
        <a class="nav-link" href="<?= htmlspecialchars($base) ?>/aom/dashboard">
            <i class="fas fa-fw fa-tachometer-alt"></i>
            <span>Dashboard</span>
        </a>
    </li>

    <hr class="sidebar-divider">

    <div class="sidebar-heading">Operations</div>

    <!-- Employees -->
    <li class="nav-item <?= ($activePage === 'employees') ? 'active' : '' ?>">
        <a class="nav-link" href="<?= htmlspecialchars($base) ?>/aom/employees">
            <i class="fas fa-users"></i>
            <span>Employees</span>
        </a>
    </li>

    <!-- Assets -->
    <li class="nav-item <?= ($activePage === 'assets') ? 'active' : '' ?>">
        <a class="nav-link" href="<?= htmlspecialchars($base) ?>/aom/assets">
            <i class="fas fa-archive"></i>
            <span>Assets</span>
        </a>
    </li>

    <li class="nav-item <?= ($activePage === 'items') ? 'active' : '' ?>">
        <a class="nav-link" href="<?= htmlspecialchars($base) ?>/aom/items">
            <i class="fas fa-tshirt"></i>
            <span>My Issued Items</span>
        </a>
    </li>

    <!-- Tickets -->
    <li class="nav-item <?= ($activePage === 'tickets') ? 'active' : '' ?>">
        <a class="nav-link" href="<?= htmlspecialchars($base) ?>/aom/tickets">
            <i class="fas fa-ticket-alt"></i>
            <span>Tickets</span>
        </a>
    </li>

    <!-- My Ticket -->
    <li class="nav-item <?= ($activePage === 'create-my-ticket') ? 'active' : '' ?>">
        <a class="nav-link" href="<?= htmlspecialchars($base) ?>/aom/tickets/create/my">
            <i class="fas fa-user"></i>
            <span>My Ticket</span>
        </a>
    </li>

    <!-- Employee Ticket -->
    <li class="nav-item <?= ($activePage === 'create-employee-ticket') ? 'active' : '' ?>">
        <a class="nav-link" href="<?= htmlspecialchars($base) ?>/aom/tickets/create/employee">
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
$shellHomeUrl = $base . '/aom/dashboard';
$shellProfileUrl = $base . '/aom/profile';
require __DIR__ . '/../app_topbar.php';
?>
        <!-- End of Topbar -->

<?php require_once __DIR__ . '/../realtime_scripts.php'; ?>
<?php require_once __DIR__ . '/../logout_modal.php'; ?>
