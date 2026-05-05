<?php
$base = rtrim(BASE_URL, '/');
?>

<!-- Sidebar -->
<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

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
                <li class="nav-item dropdown no-arrow mx-1">
                    <a class="nav-link dropdown-toggle" href="#" id="alertsDropdown"
                    role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-bell fa-fw"></i>
                        <?php $count = count($notifications ?? []); ?>
                        <?php if ($count > 0): ?>
                            <span class="badge badge-danger badge-counter">
                                <?= $count > 9 ? '9+' : $count ?>
                            </span>
                        <?php endif; ?>
                    </a>

                    <div class="dropdown-list dropdown-menu dropdown-menu-right shadow animated--grow-in"
                        aria-labelledby="alertsDropdown">
                        <h6 class="dropdown-header">Notifications</h6>
                        <?php if (empty($notifications)): ?>
                            <div class="dropdown-item text-center small text-gray-500">
                                No new notifications
                            </div>
                        <?php else: ?>
                            <div class="notification-scroll">
                                <?php foreach (array_slice($notifications, 0, 5) as $n): ?>
                                    <a href="<?= htmlspecialchars($n['action_url'] ?? '#') ?>" 
                                       class="dropdown-item d-flex align-items-center notification-item <?= ($n['is_read'] ?? 0) ? 'notification-read' : 'notification-unread' ?>">
                                        <div>
                                            <span class="font-weight-bold"><?= htmlspecialchars(substr($n['message'] ?? 'Notification', 0, 30)) ?></span>
                                            <div class="small text-gray-500"><?= htmlspecialchars(substr($n['message'] ?? '', 0, 50)) ?></div>
                                            <span class="small text-muted"><?= date('M d, H:i', strtotime($n['created_at'] ?? 'now')) ?></span>
                                        </div>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </li>

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
                        <a class="dropdown-item" href="<?= htmlspecialchars($base) ?>/logout">
                            <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                            Logout
                        </a>
                    </div>
                </li>

            </ul>

        </nav>
        <!-- End of Topbar -->
