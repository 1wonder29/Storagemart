<?php
$base = rtrim(BASE_URL, '/');
?>
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

            <!-- Nav Item - Pages Collapse Menu -->
            <li class="nav-item <?= ($activePage === 'users') ? 'active' : '' ?>">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseTwo"
                    aria-expanded="true" aria-controls="collapseTwo">
                    <i class="fas fa-fw fa-user"></i>
                    <span>Users</span>	
                </a>
                <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">
                        <h6 class="collapse-header">User:</h6>
                        <a class="collapse-item" href="<?= htmlspecialchars($base) ?>/admin/account">Accounts</a>
                        <a class="collapse-item" href="<?= htmlspecialchars($base) ?>/admin/employee">Employee</a>
                    </div>
                </div>
            </li>
			
            <li class="nav-item <?= ($activePage === 'tickets') ? 'active' : '' ?>">
                <a class="nav-link" href="<?= htmlspecialchars($base) ?>/admin/tickets">
                    <i class="fas fa-ticket-alt"></i>
                    <span>Ticket</span>
                </a>
            </li>
            <li class="nav-item <?= ($activePage === 'assets') ? 'active' : '' ?>">
                <a class="nav-link" href="<?= htmlspecialchars($base) ?>/admin/assets">
                    <i class="fas fa-archive"></i>
                    <span>Assets Directory </span>
                </a>
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
                                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal">
                                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Logout
                                </a>
                            </div>
                        </li>

                    </ul>

                </nav>

<script>
document.querySelectorAll('.notification-item').forEach(item => {
    item.addEventListener('click', function (e) {
        e.preventDefault();

        const notifId = this.dataset.id;
        const relatedId = this.dataset.related; // ticket_id
        const role = '<?= $_SESSION['usertype'] ?? '' ?>';

        // mark as read
        fetch('<?= $base ?>/notifications/read', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'id=' + notifId
        }).then(() => {
            this.classList.remove('notification-unread');
            this.classList.add('notification-read');

            // 🔀 ROLE-BASED REDIRECT
            if (relatedId) {
                if (role === 'IT') {
                    window.location.href = '<?= $base ?>/it/tickets';
                } else if (role === 'ADMIN') {
                    window.location.href = '<?= $base ?>/admin/tickets';
                } else {
                    window.location.href = '<?= $base ?>/employee/tickets';
                }
            }
        });
    });
});
</script>
