<?php
$base = rtrim(BASE_URL, '/');
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
                    <a href="<?= htmlspecialchars($base) ?>/admin/pendings" class="btn btn-sm btn-outline-light">
                        <i class="fas fa-clock mr-1"></i> Pendings
                    </a>
                    <a href="<?= htmlspecialchars($base) ?>/admin/audit-trail" class="btn btn-sm btn-outline-light">
                        <i class="fas fa-history mr-1"></i> Audit Trail
                    </a>
                </div>
            </div>

            <div class="row dashboard-section">
                <div class="col-xl-3 col-md-6 mb-4">
                    <a href="<?= htmlspecialchars($base) ?>/admin/account" class="stat-card stat-card-users">
                        <div class="stat-card-icon"><i class="fas fa-users"></i></div>
                        <div>
                            <span class="stat-card-label">Users</span>
                            <span class="stat-card-value"><?= (int) $userCount ?></span>
                            <span class="stat-card-hint">Manage accounts</span>
                        </div>
                    </a>
                </div>
                <div class="col-xl-3 col-md-6 mb-4">
                    <a href="<?= htmlspecialchars($base) ?>/admin/tickets" class="stat-card stat-card-tickets">
                        <div class="stat-card-icon"><i class="fas fa-ticket-alt"></i></div>
                        <div>
                            <span class="stat-card-label">Tickets</span>
                            <span class="stat-card-value"><?= (int) $ticketCount ?></span>
                            <span class="stat-card-hint">All filed tickets</span>
                        </div>
                    </a>
                </div>
                <div class="col-xl-3 col-md-6 mb-4">
                    <a href="<?= htmlspecialchars($base) ?>/admin/assets" class="stat-card stat-card-assets">
                        <div class="stat-card-icon"><i class="fas fa-archive"></i></div>
                        <div>
                            <span class="stat-card-label">Assets</span>
                            <span class="stat-card-value"><?= (int) $assetCount ?></span>
                            <span class="stat-card-hint">Inventory directory</span>
                        </div>
                    </a>
                </div>
                <div class="col-xl-3 col-md-6 mb-4">
                    <a href="<?= htmlspecialchars($base) ?>/admin/pendings" class="stat-card stat-card-ongoing">
                        <div class="stat-card-icon"><i class="fas fa-spinner"></i></div>
                        <div>
                            <span class="stat-card-label">On-going</span>
                            <span class="stat-card-value"><?= (int) $ticketOngoing ?></span>
                            <span class="stat-card-hint">Pending resolution</span>
                        </div>
                    </a>
                </div>
            </div>

            <div class="row dashboard-section">
                <div class="col-xl-7 col-lg-7 mb-4">
                    <div class="card dash-card shadow">
                        <div class="card-header d-flex align-items-center">
                            <span class="header-icon"><i class="fas fa-chart-pie"></i></span>
                            <h6>System Overview</h6>
                        </div>
                        <div class="card-body">
                            <div class="chart-wrap">
                                <canvas id="adminOverviewChart"></canvas>
                            </div>
                            <p class="chart-caption mb-0">Distribution based on current totals across users, tickets, assets, and on-going work.</p>
                        </div>
                    </div>
                </div>
                <div class="col-xl-5 col-lg-5 mb-4">
                    <div class="card dash-card shadow">
                        <div class="card-header d-flex align-items-center">
                            <span class="header-icon"><i class="fas fa-bolt"></i></span>
                            <h6>Quick Actions</h6>
                        </div>
                        <div class="card-body">
                            <a class="quick-action" href="<?= htmlspecialchars($base) ?>/admin/account">
                                <span class="quick-action-icon"><i class="fas fa-user"></i></span>
                                <span class="quick-action-text">Manage Accounts</span>
                                <i class="fas fa-chevron-right quick-action-arrow"></i>
                            </a>
                            <a class="quick-action" href="<?= htmlspecialchars($base) ?>/admin/tickets">
                                <span class="quick-action-icon"><i class="fas fa-ticket-alt"></i></span>
                                <span class="quick-action-text">View Tickets</span>
                                <i class="fas fa-chevron-right quick-action-arrow"></i>
                            </a>
                            <a class="quick-action" href="<?= htmlspecialchars($base) ?>/admin/pendings">
                                <span class="quick-action-icon"><i class="fas fa-table"></i></span>
                                <span class="quick-action-text">On-going Tickets</span>
                                <i class="fas fa-chevron-right quick-action-arrow"></i>
                            </a>
                            <a class="quick-action" href="<?= htmlspecialchars($base) ?>/admin/assets">
                                <span class="quick-action-icon"><i class="fas fa-archive"></i></span>
                                <span class="quick-action-text">Assets Directory</span>
                                <i class="fas fa-chevron-right quick-action-arrow"></i>
                            </a>
                            <a class="quick-action" href="<?= htmlspecialchars($base) ?>/admin/audit-trail">
                                <span class="quick-action-icon"><i class="fas fa-history"></i></span>
                                <span class="quick-action-text">Audit Trail</span>
                                <i class="fas fa-chevron-right quick-action-arrow"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row dashboard-section">
                <div class="col-xl-6 col-lg-6 mb-4">
                    <div class="card dash-card shadow">
                        <div class="card-header d-flex align-items-center">
                            <span class="header-icon"><i class="fas fa-layer-group"></i></span>
                            <h6>Filed Tickets by Category</h6>
                        </div>
                        <div class="card-body">
                            <div class="chart-wrap">
                                <canvas id="adminTicketCategoryChart"></canvas>
                            </div>
                            <p class="chart-caption mb-0">Network, Software, and Hardware tickets filed in the system.</p>
                        </div>
                    </div>
                </div>
                <div class="col-xl-6 col-lg-6 mb-4">
                    <div class="card dash-card shadow">
                        <div class="card-header d-flex align-items-center">
                            <span class="header-icon"><i class="fas fa-chart-pie"></i></span>
                            <h6>Tickets by Status</h6>
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

            <div class="row dashboard-section">
                <div class="col-xl-12 mb-4">
                    <div class="card dash-card shadow">
                        <div class="card-header d-flex align-items-center">
                            <span class="header-icon"><i class="fas fa-stopwatch"></i></span>
                            <h6>Ticket Resolution Time (SLA)</h6>
                        </div>
                        <div class="card-body">
                            <div class="chart-wrap chart-wrap-lg">
                                <canvas id="myAreaChart"></canvas>
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
            ongoing: <?= (int)($ticketOngoing ?? 0) ?>
        };

        window.ticketResolution = {
            labels: <?= json_encode($resolutionLabels ?? []) ?>,
            data: <?= json_encode($resolutionData ?? []) ?>
        };

        window.ticketCategoryCounts = <?= json_encode($ticketCategoryCounts ?? ['network' => 0, 'software' => 0, 'hardware' => 0]) ?>;
        window.ticketStatusCounts = <?= json_encode($ticketStatusCounts ?? []) ?>;
    </script>
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/jquery/jquery.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/js/sb-admin-2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/js/demo/admin_dashboard_overview.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/js/demo/admin_dashboard_category_bar.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/js/demo/admin_dashboard_status_chart.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/js/demo/dashboard_areachart.js"></script>
</body>

</html>
