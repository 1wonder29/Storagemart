<?php
$base = rtrim(BASE_URL, '/');
require_once __DIR__ . '/../../Helpers/TicketSla.php';
$adminName = htmlspecialchars($loggedFirstname ?? 'Admin');
$todayLabel = date('l, F j, Y');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>Storage Mart | Admin Dashboard</title>
    <link href="<?= htmlspecialchars($base) ?>/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/storagemart.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/admin-dashboard.css" rel="stylesheet">
</head>

<body id="page-top">

    <div id="wrapper">
        <?php
        $activePage = 'dashboard';
        require_once __DIR__ . '/../partials/admin/sidebar_topbar.php';
        ?>

        <div class="container-fluid admin-dashboard-page">

            <div class="page-hero">
                <h1><i class="fas fa-tachometer-alt mr-2"></i>Admin Dashboard</h1>
                <p>Welcome back, <?= $adminName ?> — monitor users, tickets, assets, and system activity at a glance.</p>
                <div class="hero-date"><i class="far fa-calendar-alt mr-1"></i><?= $todayLabel ?></div>
                <div class="quick-nav mt-3">
                    <a href="<?= htmlspecialchars($base) ?>/admin/tickets" class="btn btn-sm btn-outline-light">
                        <i class="fas fa-ticket-alt mr-1"></i> View Tickets
                    </a>
                    <a href="<?= htmlspecialchars($base) ?>/admin/tickets?status=<?= rawurlencode('Pending') ?>" class="btn btn-sm btn-outline-light">
                        <i class="fas fa-clock mr-1"></i> Pending
                    </a>
                    <a href="<?= htmlspecialchars($base) ?>/admin/audit-trail" class="btn btn-sm btn-outline-light">
                        <i class="fas fa-history mr-1"></i> Audit Trail
                    </a>
                </div>
            </div>

            <div class="workspace-minimal dashboard-section">
                <p class="workspace-eyebrow">Overview</p>
                <div class="admin-stat-grid">
                    <a href="<?= htmlspecialchars($base) ?>/admin/account" class="admin-stat-card tone-users">
                        <span class="stat-number"><?= (int) $userCount ?></span>
                        <span class="stat-title"><i class="fas fa-users" aria-hidden="true"></i> Users</span>
                        <span class="stat-hint">Manage accounts</span>
                    </a>
                    <a href="<?= htmlspecialchars($base) ?>/admin/tickets" class="admin-stat-card tone-tickets">
                        <span class="stat-number"><?= (int) $ticketCount ?></span>
                        <span class="stat-title"><i class="fas fa-ticket-alt" aria-hidden="true"></i> Tickets</span>
                        <span class="stat-hint">All filed tickets</span>
                    </a>
                    <a href="<?= htmlspecialchars($base) ?>/admin/tickets?status=<?= rawurlencode('Open') ?>" class="admin-stat-card tone-open">
                        <span class="stat-number"><?= (int) $ticketOpen ?></span>
                        <span class="stat-title"><i class="fas fa-folder-open" aria-hidden="true"></i> Open ticket</span>
                        <span class="stat-hint">Awaiting assignment</span>
                    </a>
                    <a href="<?= htmlspecialchars($base) ?>/admin/tickets?status=<?= rawurlencode('In Progress') ?>" class="admin-stat-card tone-progress">
                        <span class="stat-number"><?= (int) $ticketInProgress ?></span>
                        <span class="stat-title"><i class="fas fa-spinner" aria-hidden="true"></i> In progress</span>
                        <span class="stat-hint">Active assignments</span>
                    </a>
                    <a href="#admin-sla-chart" class="admin-stat-card tone-sla">
                        <span class="stat-number"><?= htmlspecialchars(number_format((float) ($slaCompliance ?? 0), 1)) ?>%</span>
                        <span class="stat-title"><i class="fas fa-check-circle" aria-hidden="true"></i> SLA Compliance</span>
                        <span class="stat-hint">View resolution chart</span>
                    </a>
                    <a href="<?= htmlspecialchars($base) ?>/admin/assets" class="admin-stat-card tone-assets">
                        <span class="stat-number"><?= (int) $assetCount ?></span>
                        <span class="stat-title"><i class="fas fa-archive" aria-hidden="true"></i> Assets</span>
                        <span class="stat-hint">Inventory directory</span>
                    </a>
                </div>
            </div>

            <div class="row dashboard-section dashboard-charts-row">
                <div class="col-xl-6 col-lg-6 mb-4">
                    <div class="card dash-card shadow h-100">
                        <div class="card-header">
                            <h6><i class="fas fa-chart-pie"></i>System Overview</h6>
                        </div>
                        <div class="card-body">
                            <div class="chart-wrap">
                                <canvas id="adminOverviewChart"></canvas>
                            </div>
                            <p class="chart-caption mb-0">Distribution based on current totals across users, tickets, assets, and in-progress work.</p>
                        </div>
                    </div>
                </div>
                <div class="col-xl-6 col-lg-6 mb-4">
                    <div class="card dash-card shadow h-100">
                        <div class="card-header">
                            <h6><i class="fas fa-chart-pie"></i>Tickets by Status</h6>
                        </div>
                        <div class="card-body">
                            <div class="chart-wrap">
                                <canvas id="adminTicketStatusChart"></canvas>
                            </div>
                            <p class="chart-caption mb-0">Current breakdown of all ticket statuses.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row dashboard-section dashboard-charts-row">
                <div class="col-xl-6 col-lg-6 mb-4">
                    <div class="card dash-card shadow h-100">
                        <div class="card-header">
                            <h6><i class="fas fa-chart-pie"></i>Tickets by Category</h6>
                        </div>
                        <div class="card-body">
                            <div class="chart-wrap">
                                <canvas id="adminTicketCategoryChart"></canvas>
                            </div>
                            <p class="chart-caption mb-0">Current breakdown of filed tickets grouped by category.</p>
                        </div>
                    </div>
                </div>
                <div class="col-xl-6 col-lg-6 mb-4">
                    <div class="card dash-card shadow h-100">
                        <div class="card-header">
                            <h6><i class="fas fa-chart-bar"></i>Tickets by Branch</h6>
                        </div>
                        <div class="card-body">
                            <div class="chart-wrap">
                                <canvas id="adminTicketBranchChart"></canvas>
                            </div>
                            <p class="chart-caption mb-0">Vertical bar chart of filed tickets grouped by branch.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row dashboard-section dashboard-charts-row">
                <div class="col-xl-6 col-lg-6 mb-4">
                    <div class="card dash-card shadow h-100">
                        <div class="card-header">
                            <h6><i class="fas fa-list-ol"></i>Top 5 Reported Issues</h6>
                        </div>
                        <div class="card-body">
                            <?php $topIssues = $topReportedIssues ?? []; ?>
                            <div class="reported-issues-panel">
                                <h3 class="reported-issues-title">Top 5 Reported Issues</h3>
                                <?php if (empty($topIssues)): ?>
                                    <div class="reported-issues-empty">
                                        <i class="fas fa-inbox" aria-hidden="true"></i>
                                        <p class="mb-0">No reported issues yet.</p>
                                    </div>
                                <?php else: ?>
                                    <div class="table-responsive reported-issues-table-wrap">
                                        <table class="table reported-issues-table mb-0">
                                            <thead>
                                                <tr>
                                                    <th scope="col" class="col-rank">#</th>
                                                    <th scope="col" class="col-issue">Issue</th>
                                                    <th scope="col" class="col-total">Total Tickets</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php $rank = 1; foreach ($topIssues as $issue => $count): ?>
                                                    <tr>
                                                        <td class="col-rank"><?= $rank ?></td>
                                                        <td class="col-issue"><?= htmlspecialchars((string) $issue) ?></td>
                                                        <td class="col-total"><?= (int) $count ?></td>
                                                    </tr>
                                                <?php $rank++; endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-6 col-lg-6 mb-4" id="admin-sla-chart">
                    <div class="card dash-card shadow h-100">
                        <div class="card-header">
                            <h6><i class="fas fa-stopwatch"></i>Ticket Resolution Time (SLA)</h6>
                        </div>
                        <div class="card-body">
                            <div class="chart-wrap">
                                <canvas id="myAreaChart"></canvas>
                            </div>
                            <p class="chart-caption mb-0">Resolution hours for recently resolved tickets.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row dashboard-section dashboard-charts-row">
                <div class="col-xl-6 col-lg-6 mb-4">
                    <div class="card dash-card shadow h-100">
                        <div class="card-header">
                            <h6><i class="fas fa-users-cog"></i>IT Personnel Workload</h6>
                        </div>
                        <div class="card-body">
                            <h3 class="reported-issues-title workload-chart-title">IT Personnel Workload</h3>
                            <?php if (empty($itPersonnelWorkload ?? [])): ?>
                                <div class="reported-issues-empty">
                                    <i class="fas fa-user-clock" aria-hidden="true"></i>
                                    <p class="mb-0">No IT personnel workload data yet.</p>
                                </div>
                            <?php else: ?>
                                <div class="chart-wrap chart-wrap-workload">
                                    <canvas id="adminItWorkloadChart"></canvas>
                                </div>
                            <?php endif; ?>
                            <p class="chart-caption mb-0">Stacked ticket workload per IT personnel — assigned, resolved, pending, and overdue.</p>
                        </div>
                    </div>
                </div>
                <div class="col-xl-6 col-lg-6 mb-4">
                    <div class="card dash-card shadow h-100">
                        <div class="card-header">
                            <h6><i class="fas fa-chart-pie"></i>Tickets by Priority</h6>
                        </div>
                        <div class="card-body">
                            <?php
                            $priorityCounts = $ticketPriorityCounts ?? [];
                            $priorityMeta = [
                                'High (P2)' => '#fd7e14',
                                'Medium (P3)' => '#f6c23e',
                                'Low (P4)' => '#1cc88a',
                            ];
                            $priorityTotal = array_sum($priorityCounts);
                            ?>
                            <div class="priority-chart-panel">
                                <h3 class="reported-issues-title">Tickets by Priority</h3>
                                <?php if ($priorityTotal <= 0): ?>
                                    <div class="reported-issues-empty">
                                        <i class="fas fa-inbox" aria-hidden="true"></i>
                                        <p class="mb-0">No ticket priority data yet.</p>
                                    </div>
                                <?php else: ?>
                                    <div class="priority-chart-layout">
                                        <div class="priority-chart-canvas">
                                            <canvas id="adminTicketPriorityChart"></canvas>
                                        </div>
                                        <ul class="priority-legend-list mb-0">
                                            <?php foreach ($priorityMeta as $label => $color):
                                                $count = (int) ($priorityCounts[$label] ?? 0);
                                                $pct = $priorityTotal > 0 ? number_format(($count / $priorityTotal) * 100, 2) : '0.00';
                                            ?>
                                                <li>
                                                    <span class="priority-swatch" style="background-color: <?= htmlspecialchars($color) ?>;"></span>
                                                    <span class="priority-label"><?= htmlspecialchars($label) ?></span>
                                                    <span class="priority-stats"><?= $count ?> (<?= $pct ?>%)</span>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

            </div>
        </div>
    </div>

    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <script>
        window.adminOverviewData = {
            users: <?= (int)($userCount ?? 0) ?>,
            tickets: <?= (int)($ticketCount ?? 0) ?>,
            assets: <?= (int)($assetCount ?? 0) ?>,
            inProgress: <?= (int)($ticketInProgress ?? 0) ?>
        };

        window.ticketResolution = {
            labels: <?= json_encode($resolutionLabels ?? []) ?>,
            data: <?= json_encode($resolutionData ?? []) ?>
        };

        window.ticketCategoryCounts = <?= json_encode($ticketCategoryCounts ?? []) ?>;
        window.ticketBranchCounts = <?= json_encode($ticketBranchCounts ?? []) ?>;
        window.itPersonnelWorkload = <?= json_encode($itPersonnelWorkload ?? []) ?>;
        window.ticketPriorityCounts = <?= json_encode($ticketPriorityCounts ?? []) ?>;
        window.ticketStatusCounts = <?= json_encode($ticketStatusCounts ?? []) ?>;
        window.slaResolutionHours = <?= (int) TicketSla::RESOLUTION_SLA_HOURS ?>;
    </script>
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/jquery/jquery.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/js/sb-admin-2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/js/demo/admin_dashboard_overview.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/js/demo/admin_dashboard_category_bar.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/js/demo/admin_dashboard_branch_bar.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/js/demo/admin_dashboard_status_chart.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/js/demo/admin_dashboard_it_workload.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/js/demo/admin_dashboard_priority_chart.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/js/demo/dashboard_areachart.js"></script>
</body>

</html>
