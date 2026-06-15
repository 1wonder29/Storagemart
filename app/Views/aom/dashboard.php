<?php
$base = rtrim(BASE_URL, '/');
$loggedFirstname = $ctx['loggedFirstname'] ?? 'AOM';
$loggedLastname = $ctx['loggedLastname'] ?? '';
$displayName = trim($loggedFirstname . ' ' . $loggedLastname) ?: 'AOM';
$chartTicketStats = [
    'Pending'     => (int)($ticketStats['Pending'] ?? 0),
    'In Progress' => (int)($ticketStats['In Progress'] ?? 0),
    'Cancelled'   => (int)($ticketStats['Cancelled'] ?? 0),
    'Resolved'    => (int)($ticketStats['Resolved'] ?? 0),
];
$hasChartData = array_sum($chartTicketStats) > 0;
$ticketStatusClasses = [
    'Pending'     => 'status-pending',
    'In Progress' => 'status-in-progress',
    'Resolved'    => 'status-resolved',
    'Cancelled'   => 'status-cancelled',
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Storage Mart | AOM Dashboard</title>

    <link href="<?= htmlspecialchars($base) ?>/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900" rel="stylesheet">
    <link rel="icon" href="<?= htmlspecialchars($base) ?>/assets/img/sm_favicon.png" type="image/x-icon">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/storagemart.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/aom-dashboard.css" rel="stylesheet">
</head>
<body id="page-top">

<div id="wrapper">
    <?php
    $activePage = 'dashboard';
    require_once __DIR__ . '/../partials/aom/sidebar_topbar.php';
    ?>

    <div class="container-fluid aom-dashboard-page">

        <!-- Hero -->
        <div class="page-hero">
            <div class="row align-items-center">
                <div class="col-lg-5">
                    <h1><i class="fas fa-tachometer-alt mr-2"></i>Dashboard</h1>
                    <p>Welcome back, <?= htmlspecialchars($displayName) ?> — here's an overview of your operations.</p>
                </div>
                <div class="col-lg-7 mt-3 mt-lg-0">
                    <div class="row">
                        <div class="col-6 col-md-3 mb-2 mb-md-0">
                            <div class="hero-stat">
                                <div class="stat-value"><?= (int)($stats['total_branches'] ?? 0) ?></div>
                                <div class="stat-label">Branches</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3 mb-2 mb-md-0">
                            <div class="hero-stat">
                                <div class="stat-value"><?= (int)($stats['total_employees'] ?? 0) ?></div>
                                <div class="stat-label">Employees</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="hero-stat">
                                <div class="stat-value"><?= (int)($stats['pending_tickets'] ?? 0) ?></div>
                                <div class="stat-label">Pending</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="hero-stat">
                                <div class="stat-value"><?= (int)($stats['resolved_this_month'] ?? 0) ?></div>
                                <div class="stat-label">Resolved</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="quick-actions">
            <a href="<?= htmlspecialchars($base) ?>/aom/tickets/create" class="quick-action-btn qa-primary">
                <i class="fas fa-plus"></i> Create Ticket
            </a>
            <a href="<?= htmlspecialchars($base) ?>/aom/employees" class="quick-action-btn qa-success">
                <i class="fas fa-users"></i> View Employees
            </a>
            <a href="<?= htmlspecialchars($base) ?>/aom/tickets" class="quick-action-btn qa-info">
                <i class="fas fa-ticket-alt"></i> All Tickets
            </a>
        </div>

        <!-- Branches & Chart -->
        <div class="row mb-4">
            <div class="col-xl-8 mb-4 mb-xl-0">
                <div class="card dash-card shadow">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h6><i class="fas fa-store"></i>Assigned Branches</h6>
                        <span class="badge badge-primary" style="border-radius:2rem;">
                            <?= count($branches ?? []) ?> branch<?= count($branches ?? []) === 1 ? '' : 'es' ?>
                        </span>
                    </div>
                    <div class="card-body" style="max-height:340px;overflow-y:auto;">
                        <?php if (!empty($branches)): ?>
                            <?php foreach ($branches as $branch): ?>
                                <a href="<?= htmlspecialchars($base) ?>/aom/branches/detail?id=<?= (int)$branch['branch_id'] ?>"
                                   class="branch-item d-flex px-2">
                                    <div class="branch-icon">
                                        <i class="fas fa-building"></i>
                                    </div>
                                    <div>
                                        <div class="branch-name"><?= htmlspecialchars($branch['branchName']) ?></div>
                                        <?php if (!empty($branch['branchAddress'])): ?>
                                            <div class="branch-meta"><?= htmlspecialchars($branch['branchAddress']) ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <span class="branch-count">
                                        <?= (int)($branch['employee_count'] ?? 0) ?> staff
                                    </span>
                                </a>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-store-slash"></i>
                                <p class="mb-0">No branches assigned yet.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-xl-4">
                <div class="card dash-card shadow">
                    <div class="card-header">
                        <h6><i class="fas fa-chart-pie"></i>Ticket Statistics</h6>
                    </div>
                    <div class="card-body">
                        <?php if ($hasChartData): ?>
                            <div class="chart-wrap">
                                <canvas id="ticketStatsChart"></canvas>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-chart-pie"></i>
                                <p class="mb-0">No ticket data available.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Tickets -->
        <div class="card dash-card shadow mb-4">
            <div class="card-header d-flex align-items-center justify-content-between flex-wrap">
                <h6><i class="fas fa-clipboard-list"></i>Recent Tickets</h6>
                <a href="<?= htmlspecialchars($base) ?>/aom/tickets" class="btn btn-sm btn-outline-primary" style="border-radius:2rem;font-size:0.75rem;">
                    View All
                </a>
            </div>
            <div class="card-body p-0">
                <?php if (!empty($tickets)): ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Ticket #</th>
                                <th>Employee</th>
                                <th>Status</th>
                                <th>Date Filed</th>
                                <th class="text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (array_slice($tickets, 0, 5) as $ticket):
                                $first = $ticket['firstname'] ?? ($ticket['employee_firstname'] ?? '');
                                $last  = $ticket['lastname'] ?? ($ticket['employee_lastname'] ?? '');
                                $empName = trim($first . ' ' . $last);
                                $status = (string)($ticket['status'] ?? '');
                            ?>
                                <tr>
                                    <td>
                                        <span class="ticket-id"><?= htmlspecialchars($ticket['ticket_number'] ?? (string)$ticket['ticket_id']) ?></span>
                                    </td>
                                    <td><?= htmlspecialchars($empName !== '' ? $empName : '—') ?></td>
                                    <td>
                                        <span class="ticket-status <?= $ticketStatusClasses[$status] ?? 'status-default' ?>">
                                            <?= htmlspecialchars($status) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?= !empty($ticket['date_filed']) ? date('M d, Y', strtotime($ticket['date_filed'])) : '—' ?>
                                    </td>
                                    <td class="text-right">
                                        <a href="<?= htmlspecialchars($base) ?>/aom/tickets/view?id=<?= (int)$ticket['ticket_id'] ?>"
                                           class="btn btn-sm btn-outline-primary btn-view-sm">
                                            <i class="fas fa-eye mr-1"></i> View
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <p class="mb-0">No tickets found.</p>
                    </div>
                <?php endif; ?>
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

    var labels = <?= json_encode(array_keys($chartTicketStats)) ?>;
    var data = <?= json_encode(array_values($chartTicketStats)) ?>;

    new Chart(ctx.getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: data,
                backgroundColor: ['#f6c23e', '#1cc88a', '#858796', '#36b9cc', '#e74a3b'],
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
                        padding: 14,
                        usePointStyle: true,
                        font: { size: 11 }
                    }
                }
            },
            cutout: '62%'
        }
    });
})();
</script>
<?php endif; ?>
</body>
</html>
