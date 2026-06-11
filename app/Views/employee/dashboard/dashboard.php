<?php
$base = rtrim(BASE_URL, '/');
$displayName = trim($loggedFirstname ?? '') ?: 'Employee';
$position = trim($loggedPosition ?? '') ?: 'Employee';

$pendingTickets = (int)($pendingTickets ?? 0);
$inProgressTickets = (int)($inProgressTickets ?? 0);
$resolvedTickets = (int)($resolvedTickets ?? 0);
$totalTickets = (int)($totalTickets ?? 0);
$assetsCount = (int)($assetsCount ?? 0);

$hasTicketChart = ($pendingTickets + $inProgressTickets + $resolvedTickets) > 0;
$hasResolutionChart = !empty($resolutionLabels) && !empty($resolutionData);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Storage Mart | Employee Dashboard</title>

    <link href="<?= htmlspecialchars($base) ?>/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900" rel="stylesheet">
    <link rel="icon" href="<?= htmlspecialchars($base) ?>/assets/img/sm_favicon.png" type="image/x-icon">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/storagemart.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/employee-dashboard.css" rel="stylesheet">
</head>
<body id="page-top">

<div id="wrapper">
    <?php
    $activePage = 'dashboard';
    require_once __DIR__ . '/../../partials/employee/sidebar_topbar.php';
    ?>

    <div class="container-fluid employee-dashboard-page">

        <!-- Hero -->
        <div class="page-hero">
            <div class="row align-items-center">
                <div class="col-lg-5">
                    <h1><i class="fas fa-tachometer-alt mr-2"></i>Dashboard</h1>
                    <p>Welcome back, <?= htmlspecialchars($displayName) ?> — track your assets and support tickets.</p>
                    <?php if ($position !== ''): ?>
                        <span class="hero-role"><?= htmlspecialchars($position) ?></span>
                    <?php endif; ?>
                </div>
                <div class="col-lg-7 mt-3 mt-lg-0">
                    <div class="row">
                        <div class="col-6 col-md-3 mb-2 mb-md-0">
                            <div class="hero-stat">
                                <div class="stat-value"><?= $assetsCount ?></div>
                                <div class="stat-label">Your Assets</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3 mb-2 mb-md-0">
                            <div class="hero-stat">
                                <div class="stat-value"><?= $totalTickets ?></div>
                                <div class="stat-label">Total Tickets</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="hero-stat">
                                <div class="stat-value"><?= $pendingTickets ?></div>
                                <div class="stat-label">Pending</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="hero-stat">
                                <div class="stat-value"><?= $resolvedTickets ?></div>
                                <div class="stat-label">Resolved</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="quick-actions">
            <a href="<?= htmlspecialchars($base) ?>/employee/assets" class="quick-action-btn qa-primary">
                <i class="fas fa-archive"></i> My Assets
            </a>
            <a href="<?= htmlspecialchars($base) ?>/employee/tickets" class="quick-action-btn qa-success">
                <i class="fas fa-ticket-alt"></i> My Tickets
            </a>
            <a href="<?= htmlspecialchars($base) ?>/employee/tickets/create" class="quick-action-btn qa-info">
                <i class="fas fa-plus"></i> Create Ticket
            </a>
            <a href="<?= htmlspecialchars($base) ?>/employee/profile" class="quick-action-btn qa-secondary">
                <i class="fas fa-user"></i> My Profile
            </a>
        </div>

        <!-- Charts -->
        <div class="row mb-4">
            <div class="col-xl-4 col-lg-5 mb-4 mb-xl-0">
                <div class="card dash-card shadow">
                    <div class="card-header">
                        <h6><i class="fas fa-chart-pie"></i>Ticket Status Overview</h6>
                    </div>
                    <div class="card-body">
                        <?php if ($hasTicketChart): ?>
                            <div class="chart-wrap">
                                <canvas id="ticketChart"></canvas>
                            </div>
                            <p class="chart-caption mb-0">Distribution of your tickets by status</p>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-chart-pie"></i>
                                <p class="mb-0">No tickets yet.
                                    <a href="<?= htmlspecialchars($base) ?>/employee/tickets/create">Create one</a>
                                </p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-xl-8 col-lg-7">
                <div class="card dash-card shadow">
                    <div class="card-header">
                        <h6><i class="fas fa-chart-line"></i>Ticket Resolution Overview</h6>
                    </div>
                    <div class="card-body">
                        <?php if ($hasResolutionChart): ?>
                            <div class="chart-wrap">
                                <canvas id="myAreaChart"></canvas>
                            </div>
                            <p class="chart-caption mb-0">Resolution time per ticket (hours converted to days)</p>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-chart-line"></i>
                                <p class="mb-0">No resolution data available yet.</p>
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

<script>
    window.ticketData = [
        <?= $pendingTickets ?>,
        <?= $inProgressTickets ?>,
        <?= $resolvedTickets ?>
    ];
    window.ticketResolution = {
        labels: <?= json_encode($resolutionLabels ?? []) ?>,
        data: <?= json_encode($resolutionData ?? []) ?>
    };
</script>

<script src="<?= htmlspecialchars($base) ?>/assets/vendor/jquery/jquery.min.js"></script>
<script src="<?= htmlspecialchars($base) ?>/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="<?= htmlspecialchars($base) ?>/assets/vendor/jquery-easing/jquery.easing.min.js"></script>
<script src="<?= htmlspecialchars($base) ?>/assets/js/sb-admin-2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<?php if ($hasTicketChart): ?>
<script>
(function () {
    var ctx = document.getElementById('ticketChart');
    if (!ctx || !window.ticketData) return;

    var total = window.ticketData.reduce(function (a, b) { return a + b; }, 0);

    var centerText = {
        id: 'centerText',
        afterDraw: function (chart) {
            var meta = chart.getDatasetMeta(0);
            if (!meta || !meta.data || !meta.data.length) return;
            var c = chart.ctx;
            var pt = meta.data[0];
            c.save();
            c.font = 'bold 22px Nunito';
            c.fillStyle = '#5a5c69';
            c.textAlign = 'center';
            c.textBaseline = 'middle';
            c.fillText(total, pt.x, pt.y);
            c.restore();
        }
    };

    new Chart(ctx.getContext('2d'), {
        type: 'doughnut',
        plugins: [centerText],
        data: {
            labels: ['Pending', 'In Progress', 'Resolved'],
            datasets: [{
                data: window.ticketData,
                backgroundColor: ['#f6c23e', '#36b9cc', '#1cc88a'],
                borderColor: '#fff',
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '68%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { padding: 14, usePointStyle: true, font: { size: 11 } }
                }
            }
        }
    });
})();
</script>
<?php endif; ?>

<?php if ($hasResolutionChart): ?>
<script src="<?= htmlspecialchars($base) ?>/assets/js/demo/dashboard_areachart.js"></script>
<?php endif; ?>

<script src="<?= htmlspecialchars($base) ?>/assets/author/ouaaa.js"></script>
<?php require __DIR__ . '/../../partials/flash_modal.php'; ?>
</body>
</html>
