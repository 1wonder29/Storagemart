<?php
$base = rtrim(BASE_URL, '/');
?>
<?php require_once __DIR__ . '/../sidebar_styles.php'; ?>
        <!-- Sidebar -->
        <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion sidebar-modern" id="accordionSidebar">
            
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
                <a class="nav-link <?= ($activePage === 'tickets') ? '' : 'collapsed' ?>" href="<?= htmlspecialchars($base)?>/it/tickets"
                    aria-expanded="<?= ($activePage === 'tickets') ? 'true' : 'false' ?>" aria-controls="collapseTwo">
                    <i class="fas fa-ticket-alt"></i>
                    <span>Ticket</span>	
                </a>
                <div id="collapseTwo" class="collapse <?= ($activePage === 'tickets') ? 'show' : '' ?>" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">
                        <h6 class="collapse-header">Ticket:</h6>
                        <a class="collapse-item" href="<?= htmlspecialchars($base)?>/it/tickets/open">Open</a>
                        <a class="collapse-item" href="<?= htmlspecialchars($base)?>/it/tickets/in_progress">In Progress</a>
                        <a class="collapse-item" href="<?= htmlspecialchars($base)?>/it/tickets/pending">Pending</a>
                        <a class="collapse-item" href="<?= htmlspecialchars($base)?>/it/tickets/resolve">Resolve</a>
                        <a class="collapse-item" href="<?= htmlspecialchars($base)?>/it/tickets/closed">Closed</a>
                        <a class="collapse-item" href="<?= htmlspecialchars($base)?>/it/tickets/cancelled">Cancel History</a>
                    </div>
                </div>
            </li>
            <li class="nav-item <?= ($activePage === 'assets') ? 'active' : '' ?>">
                <a class="nav-link" href="<?= htmlspecialchars($base)?>/it/assets">
                    <i class="fas fa-archive"></i>
                    <span>My Assets</span>
                </a>
            </li>

            <li class="nav-item <?= ($activePage === 'uploads') ? 'active' : '' ?>">
                <a class="nav-link" href="<?= htmlspecialchars($base)?>/it/uploads">
                    <i class="fas fa-file-upload"></i>
                    <span>Employee Uploads</span>
                </a>
            </li>

            <li class="nav-item <?= ($activePage === 'ratings') ? 'active' : '' ?>">
                <a class="nav-link" href="<?= htmlspecialchars($base)?>/it/ratings">
                    <i class="fas fa-star"></i>
                    <span>My Ratings</span>
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
                        <li class="nav-item no-arrow mx-1 d-flex align-items-center">
                            <button type="button" id="itDarkModeToggle" class="btn btn-link nav-link py-2"
                                    aria-label="Toggle dark mode" aria-pressed="false" title="Switch to dark mode">
                                <i class="fas fa-moon" id="itDarkModeIcon"></i>
                            </button>
                        </li>
                        <div class="topbar-divider d-none d-sm-block"></div>
                <?php require_once __DIR__ . '/../notification_dropdown.php'; ?>
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
                                    <a class="dropdown-item" href="<?= htmlspecialchars($base)?>/it/profile">
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
<?php if (!isset($base)) { $base = rtrim(BASE_URL, '/'); } ?>
<script src="<?= htmlspecialchars($base) ?>/assets/js/it-dark-mode.js"></script>
<?php require_once __DIR__ . '/../logout_modal.php'; ?>

