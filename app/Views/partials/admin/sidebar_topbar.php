<?php
$base = rtrim(BASE_URL, '/');
$ticketSubPage = $ticketSubPage ?? '';
$userSubPage = $userSubPage ?? '';
$assetSubPage = $assetSubPage ?? '';
?>
<link href="<?= htmlspecialchars($base) ?>/assets/css/admin-sidebar.css" rel="stylesheet">
        <!-- Sidebar -->
        <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

            <!-- Sidebar - Brand -->
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="<?= htmlspecialchars($base) ?>/admin">
                <img src="<?= htmlspecialchars($base) ?>/assets/img/storagemart-logo.png" alt="storagemart Logo" style="width:100px; height:auto;">
            </a>
            <!-- Divider -->
            <hr class="sidebar-divider my-0">

            <!-- Nav Item - Dashboard -->
            <li class="nav-item <?= ($activePage === 'dashboard') ? 'active' : '' ?>">
                <a class="nav-link" href="<?= htmlspecialchars($base) ?>/admin">  
                    <i class="fas fa-fw fa-tachometer-alt"></i>
                    <span>Dashboard</span></a>
            </li>

            <!-- Divider -->
            <hr class="sidebar-divider">

            <!-- Heading -->
            <div class="sidebar-heading">
                Interface
            </div>

            <!-- Nav Item - Users -->
            <li class="nav-item <?= ($activePage === 'users') ? 'active' : '' ?>">
                <a class="nav-link <?= ($activePage === 'users') ? '' : 'collapsed' ?>" href="#" data-toggle="collapse" data-target="#collapseUsers"
                    aria-expanded="<?= ($activePage === 'users') ? 'true' : 'false' ?>" aria-controls="collapseUsers">
                    <i class="fas fa-fw fa-users"></i>
                    <span>Users</span>
                </a>
                <div id="collapseUsers" class="collapse <?= ($activePage === 'users') ? 'show' : '' ?>" aria-labelledby="headingUsers" data-parent="#accordionSidebar">
                    <div class="sidebar-submenu">
                        <a class="sidebar-submenu-item <?= ($userSubPage === 'accounts') ? 'active' : '' ?>"
                           href="<?= htmlspecialchars($base) ?>/admin/account">
                            <i class="fas fa-id-card"></i>
                            <span>Accounts</span>
                        </a>
                        <a class="sidebar-submenu-item <?= ($userSubPage === 'employee') ? 'active' : '' ?>"
                           href="<?= htmlspecialchars($base) ?>/admin/employee">
                            <i class="fas fa-user-tie"></i>
                            <span>Employee</span>
                        </a>
                    </div>
                </div>
            </li>
			
            <li class="nav-item <?= ($activePage === 'tickets') ? 'active' : '' ?>">
                <a class="nav-link <?= ($activePage === 'tickets') ? '' : 'collapsed' ?>" href="#" data-toggle="collapse" data-target="#collapseTickets"
                    aria-expanded="<?= ($activePage === 'tickets') ? 'true' : 'false' ?>" aria-controls="collapseTickets">
                    <i class="fas fa-ticket-alt"></i>
                    <span>Ticket</span>
                </a>
                <div id="collapseTickets" class="collapse <?= ($activePage === 'tickets') ? 'show' : '' ?>" aria-labelledby="headingTickets" data-parent="#accordionSidebar">
                    <div class="sidebar-submenu">
                        <a class="sidebar-submenu-item <?= ($ticketSubPage === 'all') ? 'active' : '' ?>"
                           href="<?= htmlspecialchars($base) ?>/admin/tickets">
                            <i class="fas fa-list-ul"></i>
                            <span>All Tickets</span>
                        </a>
                        <a class="sidebar-submenu-item <?= ($ticketSubPage === 'cancelled') ? 'active' : '' ?>"
                           href="<?= htmlspecialchars($base) ?>/admin/tickets/cancelled">
                            <i class="fas fa-ban"></i>
                            <span>Cancel History</span>
                        </a>
                    </div>
                </div>
            </li>
            <li class="nav-item <?= in_array($activePage ?? '', ['assets', 'asset', 'branch', 'category']) ? 'active' : '' ?>">
                <a class="nav-link <?= in_array($activePage ?? '', ['assets', 'asset', 'branch', 'category']) ? '' : 'collapsed' ?>" href="#" data-toggle="collapse" data-target="#collapseAssets"
                    aria-expanded="<?= in_array($activePage ?? '', ['assets', 'asset', 'branch', 'category']) ? 'true' : 'false' ?>" aria-controls="collapseAssets">
                    <i class="fas fa-archive"></i>
                    <span>Assets Directory</span>
                </a>
                <div id="collapseAssets" class="collapse <?= in_array($activePage ?? '', ['assets', 'asset', 'branch', 'category']) ? 'show' : '' ?>" aria-labelledby="headingAssets" data-parent="#accordionSidebar">
                    <div class="sidebar-submenu">
                        <a class="sidebar-submenu-item <?= ($assetSubPage === 'directory') ? 'active' : '' ?>"
                           href="<?= htmlspecialchars($base) ?>/admin/assets">
                            <i class="fas fa-th-list"></i>
                            <span>Assets Directory</span>
                        </a>
                        <a class="sidebar-submenu-item <?= ($assetSubPage === 'add-item') ? 'active' : '' ?>"
                           href="<?= htmlspecialchars($base) ?>/admin/assets/add">
                            <i class="fas fa-plus-circle"></i>
                            <span>Add Item</span>
                        </a>
                        <a class="sidebar-submenu-item <?= ($assetSubPage === 'add-branch') ? 'active' : '' ?>"
                           href="<?= htmlspecialchars($base) ?>/admin/assets/branch/add">
                            <i class="fas fa-map-marker-alt"></i>
                            <span>Add Branch</span>
                        </a>
                        <a class="sidebar-submenu-item <?= ($assetSubPage === 'add-category') ? 'active' : '' ?>"
                           href="<?= htmlspecialchars($base) ?>/admin/assets/category/add">
                            <i class="fas fa-tags"></i>
                            <span>Add Category</span>
                        </a>
                        <a class="sidebar-submenu-item <?= ($assetSubPage === 'add-group') ? 'active' : '' ?>"
                           href="<?= htmlspecialchars($base) ?>/admin/assets/group/add">
                            <i class="fas fa-layer-group"></i>
                            <span>Add Group</span>
                        </a>
                    </div>
                </div>
            </li>
            <!-- Divider -->
            <hr class="sidebar-divider">

            <!-- Heading -->
            <div class="sidebar-heading">
                Addons
            </div>
			
            <!-- Nav Item - Tables -->
            <li class="nav-item <?= ($activePage === 'pendings') ? 'active' : '' ?>">
                <a class="nav-link" href="<?= htmlspecialchars($base) ?>/admin/pendings">
                    <i class="fas fa-fw fa-table"></i>
                    <span>Pendings</span></a>
            </li>

            <!-- Nav Item - Audit Trail -->
            <li class="nav-item <?= ($activePage === 'audit_trail') ? 'active' : '' ?>">
                <a class="nav-link" href="<?= htmlspecialchars($base) ?>/admin/audit-trail">
                    <i class="fas fa-fw fa-history"></i>
                    <span>Audit Trail</span></a>
            </li>

            <!-- Divider -->
            <hr class="sidebar-divider">

            <!-- Heading -->
            <div class="sidebar-heading">
                Reports
            </div>

            <!-- Nav Item - Ratings -->
            <li class="nav-item <?= ($activePage === 'ratings') ? 'active' : '' ?>">
                <a class="nav-link" href="<?= htmlspecialchars($base) ?>/admin/ratings">
                    <i class="fas fa-fw fa-star"></i>
                    <span>Ratings Report</span></a>
            </li>

            <!-- Nav Item - Monthly Ticket Report -->
            <li class="nav-item <?= ($activePage === 'monthly_report') ? 'active' : '' ?>">
                <a class="nav-link" href="<?= htmlspecialchars($base) ?>/admin/reports/monthly-tickets">
                    <i class="fas fa-fw fa-file-excel"></i>
                    <span>Monthly Ticket Report</span></a>
            </li>

            <!-- Divider -->
            <hr class="sidebar-divider d-none d-md-block">

            <!-- Sidebar Toggler (Sidebar) -->
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
                    <!-- Topbar Navbar -->
                    <ul class="navbar-nav ml-auto">

                        <div class="topbar-divider d-none d-sm-block"></div>
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

                                    <h6 class="dropdown-header">Alerts Center</h6>

                                    <?php if (empty($notifications)): ?>
                                        <div class="dropdown-item text-center small text-gray-500">
                                            No new alerts
                                        </div>
                                    <?php else: ?>
                                    <div class="notification-scroll">
                                        <?php foreach ($notifications as $n): ?>
                                            <a class="dropdown-item d-flex align-items-center notification-item <?= $n['is_read'] ? 'notification-read' : 'notification-unread' ?>"
                                                href="<?= htmlspecialchars($n['action_url'] ?? '#') ?>"
                                                data-id="<?= (int)$n['id'] ?>"
                                                data-related="<?= (int)($n['related_id'] ?? 0) ?>">

                                                <div class="mr-3">
                                                    <div class="icon-circle bg-<?= htmlspecialchars($n['bg_color']) ?>">
                                                        <i class="fas <?= htmlspecialchars($n['icon']) ?> text-white"></i>
                                                    </div>
                                                </div>

                                                <div>
                                                    <div class="small text-gray-500">
                                                        <?= date('F d, Y', strtotime($n['created_at'])) ?>
                                                    </div>
                                                    <?= htmlspecialchars($n['message']) ?>
                                                </div>
                                            </a>
                                        <?php endforeach; ?>
                                    </div>


                                    <?php endif; ?>

                                    <a class="dropdown-item text-center small text-gray-500" href="#">
                                        Show All Alerts
                                    </a>
                                </div>
                            </li>

                        <!-- Nav Item - User Information -->
                        <li class="nav-item dropdown no-arrow">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="mr-2 d-none d-lg-inline text-gray-600 small"><?= htmlspecialchars($loggedFirstname) . " (" . htmlspecialchars($loggedPosition) . ")" ?></span>
                                <img class="img-profile rounded-circle"
                                    src="<?= htmlspecialchars($base) ?>/assets/img/undraw_profile.svg">
                            </a>
                            <!-- Dropdown - User Information -->
                            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in"
                                aria-labelledby="userDropdown">
                                <a class="dropdown-item" href="<?= htmlspecialchars($base) ?>/admin/profile">
                                    <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Profile
                                </a>
                                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal">
                                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Logout
                                </a>
                            </div>
                        </li>

                    </ul>

                </nav>

<?php require_once __DIR__ . '/../realtime_scripts.php'; ?>
<!-- Logout Modal -->
<div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" 
     aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ready to Leave?</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Select "Logout" below to end your current session.
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>

                <!-- Redirects to logout -->
                <a class="btn btn-primary" href="<?= htmlspecialchars($base) ?>/logout">Logout</a>
            </div>
        </div>
    </div>
</div>