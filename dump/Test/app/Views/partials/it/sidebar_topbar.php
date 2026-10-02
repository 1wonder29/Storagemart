
        <!-- Sidebar -->
        <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">
            
            <!-- Brand -->
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="<?= htmlspecialchars($base)?>/it">
                <div class="sidebar-brand-icon rotate-n-15"></div>
                <img src="<?= htmlspecialchars($base)?>/assets/img/logo.png" alt="Logo" style="width:100px; height:auto;">
            </a>

            <hr class="sidebar-divider my-0">

            <!-- Dashboard -->
            <li class="nav-item <?= ($activePage === 'dashboard') ? 'active' : '' ?>">
                <a class="nav-link" href="<?= htmlspecialchars($base)?>/it">
                    <i class="fas fa-fw fa-tachometer-alt"></i>
                    <span>Dashboard</span></a>
            </li>

            <hr class="sidebar-divider">

            <div class="sidebar-heading">Interface</div>

            <li class="nav-item <?= ($activePage === 'tickets') ? 'active' : '' ?>">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseTwo"
                    aria-expanded="true" aria-controls="collapseTwo">
                    <i class="fas fa-ticket-alt"></i>
                    <span>Ticket</span>	
                </a>
                <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">
                        <h6 class="collapse-header">Ticket:</h6>
                        <a class="collapse-item" href="<?= htmlspecialchars($base)?>/it/tickets/in_progress">In Progress</a>
                        <a class="collapse-item" href="<?= htmlspecialchars($base)?>/it/tickets/resolve">Resolve</a>
                        <a class="collapse-item" href="<?= htmlspecialchars($base)?>/it/tickets">My Ticket</a>
                    </div>
                </div>
            </li>
            <li class="nav-item <?= ($activePage === 'assets') ? 'active' : '' ?>">
                <a class="nav-link" href="<?= htmlspecialchars($base)?>/it/assets">
                    <i class="fas fa-archive"></i>
                    <span>My Assets</span>
                </a>
            </li>

            <hr class="sidebar-divider">

            <div class="sidebar-heading">Addons</div>

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
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="fa fa-bars"></i>
                    </button>

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
                        <li class="nav-item dropdown no-arrow">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                               data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="mr-2 d-none d-lg-inline text-gray-600 small">
                                    <?= htmlspecialchars($loggedFirstname) . " (" . htmlspecialchars($loggedPosition) . ")" ?>
                                </span>
                                <img class="img-profile rounded-circle" src="<?= htmlspecialchars($base)?>/assets/img/undraw_profile.svg">
                            </a>
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

        const url = this.href;
        const notifId = this.dataset.id;

        // Mark as read in database
        fetch('<?= htmlspecialchars($base) ?>/notifications/read', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: 'id=' + encodeURIComponent(notifId)
        }).then(() => {
            // Update UI immediately (do NOT remove)
            this.classList.remove('notification-unread');
            this.classList.add('notification-read');

            // Redirect IT to target page
            window.location.href = url;
        });
    });
});
</script>

