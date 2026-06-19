<?php
$count = $count ?? 0;
$notifications = $notifications ?? [];

$base = rtrim(BASE_URL, '/');

require_once dirname(__DIR__, 3) . '/Helpers/HrDepartmentAccess.php';
$showUniformsNav = HrDepartmentAccess::isHrDepartmentHead();
?>


<?php require_once __DIR__ . '/../sidebar_styles.php'; ?>

<!-- Sidebar -->
<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion sidebar-modern" id="accordionSidebar">

    <!-- Sidebar - Brand -->
    <a class="sidebar-brand d-flex align-items-center justify-content-center" 
       href="<?= htmlspecialchars($base) ?>/head/dashboard">
        <div class="sidebar-brand-icon">
            <img src="<?= htmlspecialchars($base) ?>/assets/img/logo.png" 
                 alt="Logo" style="width:100px; height:auto;">
        </div>
    </a>

    <hr class="sidebar-divider my-0">

    <!-- Dashboard -->
    <li class="nav-item <?= ($activePage === 'dashboard') ? 'active' : '' ?>">
        <a class="nav-link" href="<?= htmlspecialchars($base) ?>/head/dashboard">
            <i class="fas fa-fw fa-tachometer-alt"></i>
            <span>Dashboard</span>
        </a>
    </li>

    <hr class="sidebar-divider">

    <div class="sidebar-heading">Interface</div>

    <li class="nav-item <?= ($activePage === 'tickets') ? 'active' : '' ?>">
        <a class="nav-link" href="<?= htmlspecialchars($base) ?>/head/tickets">
            <i class="fas fa-ticket-alt"></i>
            <span>Ticket</span>
        </a>
    </li>

    <li class="nav-item <?= ($activePage === 'assets') ? 'active' : '' ?>">
        <a class="nav-link" href="<?= htmlspecialchars($base) ?>/head/assets">
            <i class="fas fa-archive"></i>
            <span>Assets</span>
        </a>
    </li>

    <hr class="sidebar-divider d-none d-md-block">

    <div class="sidebar-heading">My Department</div>

    <li class="nav-item <?= ($activePage === 'employee') ? 'active' : '' ?>">
        <a class="nav-link" href="<?= htmlspecialchars($base) ?>/head/employee">
            <i class="fas fa-user-friends"></i>
            <span>Employees</span>
        </a>
    </li>

    <?php if ($showUniformsNav): ?>
    <li class="nav-item <?= ($activePage === 'uniforms') ? 'active' : '' ?>">
        <a class="nav-link" href="<?= htmlspecialchars($base) ?>/hr/uniforms">
            <i class="fas fa-tshirt"></i>
            <span>Uniforms</span>
        </a>
    </li>
    <?php endif; ?>

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
                <?php require_once __DIR__ . '/../notification_dropdown.php'; ?>


                <!-- User Info -->
                <li class="nav-item dropdown no-arrow">
                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                       data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <span class="mr-2 d-none d-lg-inline text-gray-600 small">
                            <?= htmlspecialchars((string) ($loggedFirstname ?? '')) ?>
                            (<?= htmlspecialchars((string) ($loggedPosition ?? '')) ?>)
                        </span>
                        <img class="img-profile rounded-circle"
                             src="<?= htmlspecialchars($base) ?>/assets/img/undraw_profile.svg">
                    </a>

                    <!-- Dropdown -->
                    <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in"
                        aria-labelledby="userDropdown">

                        <a class="dropdown-item" href="<?= htmlspecialchars($base) ?>/head/profile">
                            <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                            Profile
                        </a>

                        <!-- FIXED: Modal trigger -->
                        <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal">
                            <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                            Logout
                        </a>

                    </div>
                </li>
            </ul>

        </nav>
        <!-- End of Topbar -->

        <!-- Logout Modal -->
        <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" 
             aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Ready to Leave?</h5>
                        <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">�</span>
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
<div class="modal fade" id="rateTicketModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-warning text-white">
        <h5 class="modal-title"><i class="fas fa-star"></i> Rate IT Support</h5>
        <button type="button" class="close text-white" data-dismiss="modal">
          <span>&times;</span>
        </button>
      </div>
      <div class="modal-body" id="rateTicketModalBody">
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../realtime_scripts.php'; ?>