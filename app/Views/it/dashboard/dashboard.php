<?php
$base = rtrim(BASE_URL, '/');
$displayName = trim($loggedFirstname ?? '') ?: 'IT User';
$position = trim($loggedPosition ?? '') ?: 'IT Support';

$assignedCount = (int)($assignedCount ?? 0);
$pendingTickets = (int)($pendingTickets ?? 0);
$resolveTickets = (int)($resolveTickets ?? 0);
$myAssets = (int)($myAssets ?? 0);
$myTickets = (int)($myTickets ?? 0);
$myOngoingTickets = (int)($myOngoingTickets ?? 0);

$hasTicketChart = ($assignedCount + $pendingTickets + $resolveTickets) > 0;
$hasResolutionChart = !empty($resolutionLabels) && !empty($resolutionData);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Storage Mart | IT Dashboard</title>

    <link href="<?= htmlspecialchars($base) ?>/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900" rel="stylesheet">
    <link rel="icon" href="<?= htmlspecialchars($base) ?>/assets/img/sm_favicon.png" type="image/x-icon">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/storagemart.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/it-dashboard.css" rel="stylesheet">
    <?php require_once __DIR__ . '/../../partials/it/theme_head.php'; ?>
</head>
<body id="page-top">

<div id="wrapper">
    <?php
    $activePage = 'dashboard';
    require_once __DIR__ . '/../../partials/it/sidebar_topbar.php';
    ?>

    <div class="container-fluid it-dashboard-page">

        <!-- Hero -->
        <div class="page-hero">
            <div class="row align-items-center">
                <div class="col-lg-5">
                    <h1><i class="fas fa-tachometer-alt mr-2"></i>IT Dashboard</h1>
                    <p>Welcome back, <?= htmlspecialchars($displayName) ?> — manage assigned tickets and your workspace.</p>
                    <?php if ($position !== ''): ?>
                        <span class="hero-role"><?= htmlspecialchars($position) ?></span>
                    <?php endif; ?>
                </div>
                <div class="col-lg-7 mt-3 mt-lg-0">
                    <div class="row">
                        <div class="col-4">
                            <div class="hero-stat">
                                <div class="stat-value"><?= $assignedCount ?></div>
                                <div class="stat-label">Assigned</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="hero-stat">
                                <div class="stat-value"><?= $pendingTickets ?></div>
                                <div class="stat-label">In Progress</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="hero-stat">
                                <div class="stat-value"><?= $resolveTickets ?></div>
                                <div class="stat-label">Resolved</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Personal Stats -->
        <div class="section-label"><i class="fas fa-user-circle mr-1"></i> My Workspace</div>
        <div class="personal-stat-grid">
            <a href="<?= htmlspecialchars($base) ?>/it/assets" class="personal-stat-card">
                <div class="stat-icon icon-assets"><i class="fas fa-archive"></i></div>
                <div>
                    <div class="stat-number"><?= $myAssets ?></div>
                    <div class="stat-title">My Assets</div>
                </div>
            </a>
            <a href="<?= htmlspecialchars($base) ?>/it/tickets" class="personal-stat-card">
                <div class="stat-icon icon-tickets"><i class="fas fa-ticket-alt"></i></div>
                <div>
                    <div class="stat-number"><?= $myTickets ?></div>
                    <div class="stat-title">My Tickets</div>
                </div>
            </a>
            <a href="<?= htmlspecialchars($base) ?>/it/tickets/in_progress" class="personal-stat-card">
                <div class="stat-icon icon-ongoing"><i class="fas fa-spinner"></i></div>
                <div>
                    <div class="stat-number"><?= $myOngoingTickets ?></div>
                    <div class="stat-title">My Ongoing</div>
                </div>
            </a>
        </div>

        <!-- Quick Actions -->
        <div class="quick-actions">
            <a href="<?= htmlspecialchars($base) ?>/it/tickets/in_progress" class="quick-action-btn qa-primary">
                <i class="fas fa-spinner"></i> In Progress
            </a>
            <a href="<?= htmlspecialchars($base) ?>/it/tickets/resolve" class="quick-action-btn qa-success">
                <i class="fas fa-check-circle"></i> Resolved
            </a>
            <a href="<?= htmlspecialchars($base) ?>/it/tickets" class="quick-action-btn qa-info">
                <i class="fas fa-ticket-alt"></i> All Tickets
            </a>
            <a href="<?= htmlspecialchars($base) ?>/it/uploads" class="quick-action-btn qa-warning">
                <i class="fas fa-file-upload"></i> Uploads
            </a>
            <a href="<?= htmlspecialchars($base) ?>/it/ratings" class="quick-action-btn qa-secondary">
                <i class="fas fa-star"></i> My Ratings
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
                        <div class="chart-wrap">
                            <canvas id="ticketChart"></canvas>
                        </div>
                        <p class="chart-caption mb-0">Assigned ticket distribution by status</p>
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
                            <div class="chart-wrap chart-wrap-wide">
                                <canvas id="myAreaChart"></canvas>
                            </div>
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

</div>

<script>
    window.ticketData = [
        <?= $assignedCount ?>,
        <?= $pendingTickets ?>,
        <?= $resolveTickets ?>
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
<script src="<?= htmlspecialchars($base) ?>/assets/js/demo/admindashboard_chart.js"></script>
<?php if ($hasResolutionChart): ?>
<script src="<?= htmlspecialchars($base) ?>/assets/js/demo/dashboard_areachart.js"></script>
<?php endif; ?>
</body>
</html>
