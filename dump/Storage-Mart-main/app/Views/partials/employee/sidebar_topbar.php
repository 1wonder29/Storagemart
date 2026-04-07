<?php
$count = $count ?? 0;
$notifications = $notifications ?? [];

$base = rtrim(BASE_URL, '/');
?>


<!-- Sidebar -->
<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

    <!-- Sidebar - Brand -->
    <a class="sidebar-brand d-flex align-items-center justify-content-center" 
       href="<?= htmlspecialchars($base) ?>/employee/dashboard">
        <div class="sidebar-brand-icon">
            <img src="<?= htmlspecialchars($base) ?>/assets/img/storagemart-logo.png" 
                 alt="Logo" style="width:100px; height:auto;">
        </div>
    </a>

    <hr class="sidebar-divider my-0">

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
            <span>Ticket</span>
        </a>
    </li>

    <li class="nav-item <?= ($activePage === 'assets') ? 'active' : '' ?>">
        <a class="nav-link" href="<?= htmlspecialchars($base) ?>/employee/assets">
            <i class="fas fa-archive"></i>
            <span>Assets</span>
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


                <!-- User Info -->
                <li class="nav-item dropdown no-arrow">
                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                       data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <span class="mr-2 d-none d-lg-inline text-gray-600 small">
                            <?= htmlspecialchars($loggedFirstname) ?>
                            (<?= htmlspecialchars($loggedPosition) ?>)
                        </span>
                        <img class="img-profile rounded-circle"
                             src="<?= htmlspecialchars($base) ?>/assets/img/undraw_profile.svg">
                    </a>

                    <!-- Dropdown -->
                    <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in"
                        aria-labelledby="userDropdown">

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
                            <span aria-hidden="true">×</span>
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
      <div class="modal-header">
        <h5 class="modal-title">Rate IT Support</h5>
        <button type="button" class="close" data-dismiss="modal">
          <span>&times;</span>
        </button>
      </div>
      <div class="modal-body" id="rateTicketModalBody">
        <div class="text-center text-muted">Loading…</div>
      </div>
    </div>
  </div>
</div>


<script>
document.querySelectorAll('.notification-item').forEach(item => {
    item.addEventListener('click', function (e) {
        e.preventDefault();

        const url = this.href;
        const notifId = this.dataset.id;

        // Mark as read in DB
        fetch('<?= $base ?>/notifications/read', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'id=' + notifId
        });

        // 🔥 Update UI instantly
        this.classList.remove('notification-unread');
        this.classList.add('notification-read');

        // Rating → modal
        if (url.includes('/employee/tickets/rate')) {
            fetch(url)
              .then(res => res.text())
              .then(html => {
                  document.getElementById('rateTicketModalBody').innerHTML = html;
                  $('#rateTicketModal').modal('show');
              });
        } else {
            window.location.href = url;
        }
    });
});
</script>


