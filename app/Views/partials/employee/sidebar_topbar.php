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
        <a class="nav-link" href="<?= htmlspecialchars($base) ?>/employee/dashboard">
            <i class="fas fa-fw fa-tachometer-alt"></i>
            <span>Dashboard</span>
        </a>
    </li>

    <hr class="sidebar-divider">

    <div class="sidebar-heading">Interface</div>

    <li class="nav-item <?= ($activePage === 'tickets') ? 'active' : '' ?>">
        <a class="nav-link" href="<?= htmlspecialchars($base) ?>/employee/tickets">
            <i class="fas fa-ticket-alt"></i>
            <span>My Tickets</span>
        </a>
    </li>

    <li class="nav-item <?= ($activePage === 'create-ticket') ? 'active' : '' ?>">
        <a class="nav-link" href="<?= htmlspecialchars($base) ?>/employee/tickets/create">
            <i class="fas fa-plus-circle"></i>
            <span>Create Ticket</span>
        </a>
    </li>

    <li class="nav-item <?= ($activePage === 'assets') ? 'active' : '' ?>">
        <a class="nav-link" href="<?= htmlspecialchars($base) ?>/employee/assets">
            <i class="fas fa-archive"></i>
            <span>Assets</span>
        </a>
    </li>

    <li class="nav-item <?= ($activePage === 'items') ? 'active' : '' ?>">
        <a class="nav-link" href="<?= htmlspecialchars($base) ?>/employee/items">
            <i class="fas fa-tshirt"></i>
            <span>My Issued Items</span>
        </a>
    </li>

</ul>
<!-- End of Sidebar -->

<!-- Content Wrapper -->
<div id="content-wrapper" class="d-flex flex-column">

    <!-- Main Content -->
    <div id="content">

<?php
$shellHomeUrl = $base . '/employee/dashboard';
$shellProfileUrl = $base . '/employee/profile';
require __DIR__ . '/../app_topbar.php';
?>
        <!-- End of Topbar -->

<?php require_once __DIR__ . '/../realtime_scripts.php'; ?>

        <!-- Logout Modal -->
        <?php require_once __DIR__ . '/../logout_modal.php'; ?>
<div class="modal fade" id="rateTicketModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-warning text-white">
        <h5 class="modal-title"><i class="fas fa-star"></i> Rate This Ticket</h5>
        <button type="button" class="close text-white" data-dismiss="modal">
          <span>&times;</span>
        </button>
      </div>
      <div class="modal-body" id="rateTicketModalBody">
      </div>
    </div>
  </div>
</div>