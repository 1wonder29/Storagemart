<?php
$count = $count ?? 0;
$notifications = $notifications ?? [];

$base = rtrim(BASE_URL, '/');
?>


<?php require_once __DIR__ . '/../sidebar_styles.php'; ?>

<!-- Sidebar -->
<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion sidebar-modern" id="accordionSidebar">

<?php require __DIR__ . '/../sidebar_user.php'; ?>

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
            <span>My Tickets</span>
        </a>
    </li>

    <li class="nav-item <?= ($activePage === 'create-ticket') ? 'active' : '' ?>">
        <a class="nav-link" href="<?= htmlspecialchars($base) ?>/head/tickets/create">
            <i class="fas fa-plus-circle"></i>
            <span>Create Ticket</span>
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


</ul>
<!-- End of Sidebar -->

<!-- Content Wrapper -->
<div id="content-wrapper" class="d-flex flex-column">

    <!-- Main Content -->
    <div id="content">

<?php
$shellHomeUrl = $base . '/head/dashboard';
$shellProfileUrl = $base . '/head/profile';
require __DIR__ . '/../app_topbar.php';
?>
        <!-- End of Topbar -->

        <?php require_once __DIR__ . '/../logout_modal.php'; ?>
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
