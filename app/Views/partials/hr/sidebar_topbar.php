<?php
$base = rtrim(BASE_URL, '/');
?>

<?php require_once __DIR__ . '/../sidebar_styles.php'; ?>

<!-- Sidebar -->
<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion sidebar-modern" id="accordionSidebar">

    <!-- Sidebar - Brand -->
    <a class="sidebar-brand d-flex align-items-center justify-content-center" 
       href="<?= htmlspecialchars($base) ?>/hr/dashboard">
        <div class="sidebar-brand-icon">
            <img src="<?= htmlspecialchars($base) ?>/assets/img/storagemart-logo.png" 
                 alt="Logo" style="width:100px; height:auto;">
        </div>
    </a>

    <hr class="sidebar-divider my-0">

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
            <i class="fas fa-tshirt"></i>
            <span>Uniforms</span>
        </a>
    </li>

    <li class="nav-item <?= ($activePage === 'tickets') ? 'active' : '' ?>">
        <a class="nav-link" href="<?= htmlspecialchars($base) ?>/hr/tickets">
            <i class="fas fa-ticket-alt"></i>
            <span>Tickets</span>
        </a>
    </li>

    <hr class="sidebar-divider d-none d-md-block">

    <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>

</ul>
<!-- End of Sidebar -->

<!-- Content Wrapper -->
<div id="content-wrapper" class="d-flex flex-column">

    <!-- Main Content -->
    <div id="content">

        <!-- Topbar -->
        <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">

            <!-- Sidebar Toggle (Topbar) -->
            <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                <i class="fa fa-bars"></i>
            </button>

            <ul class="navbar-nav ml-auto">

                <div class="topbar-divider d-none d-sm-block"></div>
                <?php $count = (int) ($count ?? 0); require_once __DIR__ . '/../notification_dropdown.php'; ?>

                <div class="topbar-divider d-none d-sm-block"></div>

                <!-- User Profile Dropdown -->
                <li class="nav-item dropdown no-arrow">
                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <span class="mr-2 d-none d-lg-inline text-gray-600 small">
                            <?= htmlspecialchars($_SESSION['username'] ?? 'HR User') ?>
                        </span>
                        <img class="img-profile rounded-circle" src="<?= htmlspecialchars($base) ?>/assets/img/undraw_profile.svg">
                    </a>

                    <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">
                        <a class="dropdown-item" href="<?= htmlspecialchars($base) ?>/hr/dashboard">
                            <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                            Dashboard
                        </a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal">
                            <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                            Logout
                        </a>
                    </div>
                </li>

            </ul>

        </nav>
        <!-- End of Topbar -->

<?php require_once __DIR__ . '/../realtime_scripts.php'; ?>
<?php require_once __DIR__ . '/../logout_modal.php'; ?>
