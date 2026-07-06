<?php
$base = rtrim(BASE_URL, '/');
$ticketSubPage = $ticketSubPage ?? '';
$userSubPage = $userSubPage ?? '';
$assetSubPage = $assetSubPage ?? '';
?>
<?php require_once __DIR__ . '/../sidebar_styles.php'; ?>
        <!-- Sidebar -->
        <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion sidebar-modern" id="accordionSidebar">

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
                <a class="nav-link" href="<?= htmlspecialchars($base) ?>/admin/tickets">
                    <i class="fas fa-fw fa-ticket-alt"></i>
                    <span>Ticket</span>
                </a>
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
                        <a class="sidebar-submenu-item <?= ($assetSubPage === 'defective') ? 'active' : '' ?>"
                           href="<?= htmlspecialchars($base) ?>/admin/assets/defective">
                            <i class="fas fa-exclamation-triangle"></i>
                            <span>Defective Items</span>
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

            <!-- Nav Item - Ticket Report -->
            <li class="nav-item <?= ($activePage === 'ticket_report') ? 'active' : '' ?>">
                <a class="nav-link" href="<?= htmlspecialchars($base) ?>/admin/reports/tickets">
                    <i class="fas fa-fw fa-clipboard-list"></i>
                    <span>Ticket Report</span></a>
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
                <?php
                $topbarProfileUrl = $base . '/admin/profile';
                require_once __DIR__ . '/../topbar_user_nav.php';
                ?>

<?php require_once __DIR__ . '/../realtime_scripts.php'; ?>
<?php require_once __DIR__ . '/../logout_modal.php'; ?>