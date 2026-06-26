<?php
$base = rtrim(BASE_URL, '/');
$routePrefix = $routePrefix ?? (($user_role ?? '') === 'HOM' ? 'hom' : 'om');
$dashboardTitle = ($user_role ?? '') === 'HOM' ? 'Dashboard' : 'OM Dashboard';
$roleLabel = ($user_role ?? '') === 'HOM' ? 'Head of Operations' : 'Operations Manager';
$displayName = trim(($user['firstname'] ?? '') . ' ' . ($user['lastname'] ?? '')) ?: 'User';

$statusOrder = ['Open', 'In Progress', 'Cancelled', 'Resolved', 'Closed'];
$chartTicketStats = [];
foreach ($statusOrder as $status) {
    $chartTicketStats[$status] = (int) ($ticketStats[$status] ?? 0);
}
foreach ($ticketStats as $status => $count) {
    if ($status === 'total' || isset($chartTicketStats[$status])) {
        continue;
    }
    $chartTicketStats[$status] = (int) $count;
}
$hasChartData = array_sum($chartTicketStats) > 0;
$chartColors = ['#f59e0b', '#0891b2', '#dc2626', '#16a34a', '#64748b', '#7c3aed', '#ea580c'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Storage Mart | <?= htmlspecialchars($dashboardTitle) ?></title>

    <link href="<?= htmlspecialchars($base) ?>/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900" rel="stylesheet">
    <link rel="icon" href="<?= htmlspecialchars($base) ?>/assets/img/sm_favicon.png" type="image/x-icon">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/storagemart.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/om-dashboard.css" rel="stylesheet">
</head>
<body id="page-top">

<div id="wrapper">
    <?php
    $activePage = 'dashboard';
    require_once __DIR__ . '/../partials/om/sidebar_topbar.php';
    ?>

    <div class="container-fluid om-dashboard-page">

        <!-- Hero -->
        <div class="page-hero">
            <div class="row align-items-center">
                <div class="col-lg-5">
                    <h1><i class="fas fa-tachometer-alt mr-2"></i><?= htmlspecialchars($dashboardTitle) ?></h1>
                    <p>Welcome back, <?= htmlspecialchars($displayName) ?> — manage operations staff, branches, and tickets across your <?= htmlspecialchars(strtolower($roleLabel)) ?> area.</p>
                </div>
                <div class="col-lg-7 mt-3 mt-lg-0">
                    <div class="row">
                        <div class="col-6 col-md-3 mb-2 mb-md-0">
                            <div class="hero-stat">
                                <div class="stat-value"><?= (int)($employeeCount ?? 0) ?></div>
                                <div class="stat-label">Employees</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3 mb-2 mb-md-0">
                            <div class="hero-stat">
                                <div class="stat-value"><?= (int)($branchCount ?? 0) ?></div>
                                <div class="stat-label">Branches</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="hero-stat">
                                <div class="stat-value"><?= (int)($aomCount ?? 0) ?></div>
                                <div class="stat-label">AOMs</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="hero-stat">
                                <div class="stat-value"><?= (int)($ticketStats['total'] ?? 0) ?></div>
                                <div class="stat-label">Tickets</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Flash Messages -->
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success alert-dismissible fade show alert-modern" role="alert">
                <i class="fas fa-check-circle mr-1"></i><?= htmlspecialchars($_SESSION['success_message']) ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger alert-dismissible fade show alert-modern" role="alert">
                <i class="fas fa-exclamation-circle mr-1"></i><?= htmlspecialchars($_SESSION['error_message']) ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>

        <!-- Quick Actions -->
        <div class="quick-actions">
            <a href="<?= htmlspecialchars($base) ?>/<?= htmlspecialchars($routePrefix) ?>/employees" class="quick-action-btn qa-primary">
                <i class="fas fa-users"></i> Manage Employees
            </a>
            <a href="<?= htmlspecialchars($base) ?>/<?= htmlspecialchars($routePrefix) ?>/aom-branches" class="quick-action-btn qa-success">
                <i class="fas fa-building"></i> AOM Branches
            </a>
            <a href="<?= htmlspecialchars($base) ?>/<?= htmlspecialchars($routePrefix) ?>/tickets/create" class="quick-action-btn qa-warning">
                <i class="fas fa-ticket-alt"></i> Create Ticket
            </a>
            <a href="<?= htmlspecialchars($base) ?>/<?= htmlspecialchars($routePrefix) ?>/tickets" class="quick-action-btn qa-secondary">
                <i class="fas fa-clipboard-list"></i> All Tickets
            </a>
        </div>

        <!-- Tickets & Chart -->
        <div class="row mb-4">
            <div class="col-xl-4 mb-4 mb-xl-0">
                <div class="card dash-card shadow">
                    <div class="card-header">
                        <h6><i class="fas fa-chart-pie"></i>Ticket Breakdown</h6>
                    </div>
                    <div class="card-body">
                        <?php if ($hasChartData): ?>
                            <div class="chart-wrap">
                                <canvas id="ticketStatsChart"></canvas>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-chart-pie"></i>
                                <p class="mb-0">No ticket data yet.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-xl-8">
                <div class="card dash-card shadow">
                    <div class="card-header d-flex align-items-center justify-content-between flex-wrap">
                        <h6><i class="fas fa-clipboard-list"></i>Recent Tickets</h6>
                        <a href="<?= htmlspecialchars($base) ?>/<?= htmlspecialchars($routePrefix) ?>/tickets"
                           class="btn btn-sm btn-view-all">
                            View All
                        </a>
                    </div>
                    <div class="card-body p-0">
                        <?php if (!empty($recentTickets)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Ticket #</th>
                                        <th>Employee</th>
                                        <th class="text-right">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentTickets as $ticket):
                                        $ticketNumber = (string) ($ticket['ticket_number'] ?? $ticket['ticket_id'] ?? 'N/A');
                                        $employeeName = (string) ($ticket['employee_name'] ?? trim(($ticket['firstname'] ?? '') . ' ' . ($ticket['lastname'] ?? '')));
                                        if ($employeeName === '') {
                                            $employeeName = '—';
                                        }
                                        $status = (string) ($ticket['status'] ?? 'Open');
                                        $priority = (string) ($ticket['priority'] ?? 'Low');
                                        $branchName = (string) ($ticket['branchName'] ?? 'N/A');
                                        $dateFiled = !empty($ticket['date_filed'])
                                            ? date('M d, Y', strtotime((string) $ticket['date_filed']))
                                            : '—';
                                        $description = (string) ($ticket['concern_details'] ?? '');
                                    ?>
                                    <tr>
                                        <td><span class="ticket-id"><?= htmlspecialchars($ticketNumber) ?></span></td>
                                        <td><span class="employee-name"><?= htmlspecialchars($employeeName) ?></span></td>
                                        <td class="text-right">
                                            <button type="button"
                                                class="btn btn-sm btn-view-sm viewTicketBtn"
                                                data-ticket-number="<?= htmlspecialchars($ticketNumber, ENT_QUOTES) ?>"
                                                data-employee="<?= htmlspecialchars($employeeName, ENT_QUOTES) ?>"
                                                data-status="<?= htmlspecialchars($status, ENT_QUOTES) ?>"
                                                data-priority="<?= htmlspecialchars($priority, ENT_QUOTES) ?>"
                                                data-branch="<?= htmlspecialchars($branchName, ENT_QUOTES) ?>"
                                                data-date-filed="<?= htmlspecialchars($dateFiled, ENT_QUOTES) ?>"
                                                data-description="<?= htmlspecialchars($description, ENT_QUOTES) ?>">
                                                <i class="fas fa-eye mr-1"></i> Details
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-inbox"></i>
                                <p class="mb-0">No tickets created yet.
                                    <a href="<?= htmlspecialchars($base) ?>/<?= htmlspecialchars($routePrefix) ?>/tickets/create">Create one</a>
                                </p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <!-- End of Page Content -->

    </div>
    <!-- End of Main Content -->

</div>
<!-- End of Content Wrapper -->

</div>
<!-- End of Page Wrapper -->

<a class="scroll-to-top rounded" href="#page-top">
    <i class="fas fa-angle-up"></i>
</a>

<!-- Ticket Details Modal -->
<div class="modal fade" id="ticketDetailModal" tabindex="-1" aria-labelledby="ticketDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="border:none;border-radius:0.75rem;overflow:hidden;">
            <div class="modal-header text-white" style="background:linear-gradient(135deg,#1b5e20,#43a047);">
                <h5 class="modal-title" id="ticketDetailModalLabel"><i class="fas fa-ticket-alt mr-2"></i>Ticket Details</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="small text-muted text-uppercase mb-1">Ticket Number</label>
                        <p class="font-weight-bold mb-0" id="modalTicketNumber">—</p>
                    </div>
                    <div class="col-md-6">
                        <label class="small text-muted text-uppercase mb-1">Employee</label>
                        <p class="font-weight-bold mb-0" id="modalEmployee">—</p>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="small text-muted text-uppercase mb-1">Status</label>
                        <p class="mb-0"><span class="badge" id="modalStatusBadge">—</span></p>
                    </div>
                    <div class="col-md-4">
                        <label class="small text-muted text-uppercase mb-1">Priority</label>
                        <p class="mb-0"><span class="badge" id="modalPriorityBadge">—</span></p>
                    </div>
                    <div class="col-md-4">
                        <label class="small text-muted text-uppercase mb-1">Filed Date</label>
                        <p class="mb-0" id="modalDateFiled">—</p>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-12">
                        <label class="small text-muted text-uppercase mb-1">Branch</label>
                        <p class="mb-0" id="modalBranch">—</p>
                    </div>
                </div>
                <hr>
                <label class="small text-muted text-uppercase mb-1">Description</label>
                <p class="mb-0" id="modalDescription">—</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal" style="border-radius:2rem;">Close</button>
            </div>
        </div>
    </div>
</div>

<script src="<?= htmlspecialchars($base) ?>/assets/vendor/jquery/jquery.min.js"></script>
<script src="<?= htmlspecialchars($base) ?>/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="<?= htmlspecialchars($base) ?>/assets/vendor/jquery-easing/jquery.easing.min.js"></script>
<script src="<?= htmlspecialchars($base) ?>/assets/js/sb-admin-2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<?php if ($hasChartData): ?>
<script>
(function () {
    var ctx = document.getElementById('ticketStatsChart');
    if (!ctx) return;

    new Chart(ctx.getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: <?= json_encode(array_keys($chartTicketStats)) ?>,
            datasets: [{
                data: <?= json_encode(array_values($chartTicketStats)) ?>,
                backgroundColor: <?= json_encode(array_slice($chartColors, 0, count($chartTicketStats))) ?>,
                hoverBackgroundColor: <?= json_encode(array_slice(['#d97706', '#0e7490', '#b91c1c', '#15803d', '#475569', '#6d28d9', '#c2410c'], 0, count($chartTicketStats))) ?>,
                borderColor: '#fff',
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 16,
                        usePointStyle: true,
                        font: { family: 'Nunito, sans-serif', size: 12 }
                    }
                }
            },
            cutout: '62%'
        }
    });
})();
</script>
<?php endif; ?>

<script>
(function () {
    function statusBadgeClass(status) {
        if (status === 'Open') return 'badge-warning';
        if (status === 'In Progress') return 'badge-info';
        if (status === 'Resolved' || status === 'Completed') return 'badge-success';
        if (status === 'Closed') return 'badge-secondary';
        return 'badge-primary';
    }

    function priorityBadgeClass(priority) {
        if (priority === 'High') return 'badge-danger';
        if (priority === 'Medium') return 'badge-warning';
        return 'badge-success';
    }

    $(document).on('click', '.viewTicketBtn', function () {
        const $btn = $(this);

        $('#modalTicketNumber').text($btn.data('ticketNumber') || '—');
        $('#modalEmployee').text($btn.data('employee') || '—');
        $('#modalBranch').text($btn.data('branch') || '—');
        $('#modalDateFiled').text($btn.data('dateFiled') || '—');
        $('#modalDescription').text($btn.data('description') || 'No description provided.');

        const status = $btn.data('status') || '—';
        const priority = $btn.data('priority') || '—';

        $('#modalStatusBadge')
            .removeClass()
            .addClass('badge ' + statusBadgeClass(status))
            .text(status);

        $('#modalPriorityBadge')
            .removeClass()
            .addClass('badge ' + priorityBadgeClass(priority))
            .text(priority);

        $('#ticketDetailModal').modal('show');
    });
})();
</script>
</body>
</html>
