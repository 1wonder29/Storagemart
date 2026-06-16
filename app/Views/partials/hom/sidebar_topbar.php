<?php
$count = $count ?? 0;
$notifications = $notifications ?? [];
$activePage = $activePage ?? '';
$user = $user ?? [];

$base = rtrim(BASE_URL, '/');
$routePrefix = 'hom';
?>

<!-- Sidebar -->
<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

    <!-- Sidebar - Brand -->
    <a class="sidebar-brand d-flex align-items-center justify-content-center" 
       href="<?= htmlspecialchars($base) ?>/<?= htmlspecialchars($routePrefix) ?>/dashboard">
        <div class="sidebar-brand-icon">
            <img src="<?= htmlspecialchars($base) ?>/assets/img/storagemart-logo.png" 
                 alt="Logo" style="width:100px; height:auto;">
        </div>
    </a>

    <hr class="sidebar-divider my-0">

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

    <!-- Assignments -->
    <li class="nav-item <?= ($activePage === 'assignments') ? 'active' : '' ?>">
        <a class="nav-link" href="<?= htmlspecialchars($base) ?>/<?= htmlspecialchars($routePrefix) ?>/assignments">
            <i class="fas fa-link"></i>
            <span>Assignments</span>
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
    <li class="nav-item <?= ($activePage === 'create-ticket') ? 'active' : '' ?>">
        <a class="nav-link" href="<?= htmlspecialchars($base) ?>/<?= htmlspecialchars($routePrefix) ?>/tickets/create">
            <i class="fas fa-plus-circle"></i>
            <span>New Ticket</span>
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
                        <?php if (!empty($count)): ?>
                            <span class="badge badge-danger badge-counter"><?= (int)$count > 9 ? '9+' : (int)$count ?></span>
                        <?php endif; ?>
                    </a>
                    <div class="dropdown-list dropdown-menu dropdown-menu-right shadow animated--grow-in"
                        aria-labelledby="alertsDropdown">
                        <h6 class="dropdown-header bg-light font-weight-bold">Notification Center</h6>
                        <?php if (!empty($notifications)): ?>
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
                                            <div class="font-weight-bold"><?= htmlspecialchars($n['message'] ?? '') ?></div>
                                        </div>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="dropdown-item text-center small text-gray-500 py-2">No notifications</div>
                        <?php endif; ?>
                        <a class="dropdown-item text-center small text-gray-500" href="<?= htmlspecialchars($base) ?>/notifications">Show All Alerts</a>
                    </div>
                </li>

                <div class="topbar-divider d-none d-sm-block"></div>

                <!-- Nav Item - User Information -->
                <li class="nav-item dropdown no-arrow">
                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <span class="mr-2 d-none d-lg-inline text-gray-600 small">
                            <?= htmlspecialchars(($user['firstname'] ?? '') . ' ' . ($user['lastname'] ?? '')) ?>
                        </span>
                        <img class="img-profile rounded-circle" src="<?= htmlspecialchars($base) ?>/assets/img/undraw_profile.svg">
                    </a>
                    <!-- Dropdown - User Information -->
                    <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in"
                        aria-labelledby="userDropdown">
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
