<?php
$count = $count ?? 0;
$notifications = $notifications ?? [];
$loggedFirstname = $loggedFirstname ?? ($ctx['loggedFirstname'] ?? 'AOM');
$loggedLastname = $loggedLastname ?? ($ctx['loggedLastname'] ?? '');
$loggedDisplayName = trim($loggedFirstname . ' ' . $loggedLastname) ?: 'AOM';

$base = rtrim(BASE_URL, '/');
?>

<!-- Sidebar -->
<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

    <!-- Sidebar - Brand -->
    <a class="sidebar-brand d-flex align-items-center justify-content-center" 
       href="<?= htmlspecialchars($base) ?>/aom/dashboard">
        <div class="sidebar-brand-icon">
            <img src="<?= htmlspecialchars($base) ?>/assets/img/storagemart-logo.png" 
                 alt="Logo" style="width:100px; height:auto;">
        </div>
    </a>

    <hr class="sidebar-divider my-0">

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

    <!-- Tickets -->
    <li class="nav-item <?= ($activePage === 'tickets') ? 'active' : '' ?>">
        <a class="nav-link" href="<?= htmlspecialchars($base) ?>/aom/tickets">
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
                <!-- Notifications -->
                <li class="nav-item dropdown no-arrow mx-1">
                    <a class="nav-link dropdown-toggle" href="#" id="alertsDropdown"
                    role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-bell fa-fw"></i>

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
                                <?php foreach ($notifications as $n): ?>
                                    <a class="dropdown-item d-flex align-items-center notification-item <?= ($n['is_read'] ?? 0) ? 'notification-read' : 'notification-unread' ?>"
                                       href="<?= htmlspecialchars($n['action_url'] ?? '#') ?>"
                                       data-id="<?= (int)($n['id'] ?? 0) ?>">
                                        <div class="mr-3">
                                            <div class="icon-circle bg-<?= htmlspecialchars($n['bg_color'] ?? 'primary') ?>">
                                                <i class="fas <?= htmlspecialchars($n['icon'] ?? 'fa-bell') ?> text-white"></i>
                                            </div>
                                        </div>
                                        <div>
                                            <?php
                                            $createdAt = $n['created_at'] ?? null;
                                            $createdLabel = $createdAt ? date('M d, Y H:i', strtotime($createdAt)) : '';
                                            ?>
                                            <div class="small text-gray-500"><?= htmlspecialchars($createdLabel) ?></div>
                                            <span class="font-weight-bold"><?= htmlspecialchars($n['message'] ?? '') ?></span>
                                        </div>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <a class="dropdown-item text-center small text-gray-500" href="<?= htmlspecialchars($base) ?>/notifications">Show All Alerts</a>
                    </div>
                </li>

                <div class="topbar-divider d-none d-sm-block"></div>

                <!-- User Profile Dropdown -->
                <li class="nav-item dropdown no-arrow">
                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <span class="mr-2 d-none d-lg-inline text-gray-600 small"><?= htmlspecialchars($loggedDisplayName) ?></span>
                        <img class="img-profile rounded-circle" height="30" width="30"
                            src="<?= htmlspecialchars($base) ?>/assets/img/undraw_profile.svg">
                    </a>
                    <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in"
                        aria-labelledby="userDropdown">
                        <a class="dropdown-item" href="<?= htmlspecialchars($base) ?>/aom/profile">
                            <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                            Profile
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

<?php require_once __DIR__ . '/../realtime_scripts.php'; ?>
