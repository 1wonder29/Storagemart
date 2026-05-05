<?php
$base = rtrim(BASE_URL, '/');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Storage Mart | HR Dashboard</title>
    <link href="<?= htmlspecialchars($base) ?>/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="icon" href="<?= htmlspecialchars($base) ?>/assets/img/sm_favicon.png" type="image/x-icon">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/storagemart.css" rel="stylesheet">
</head>

<body id="page-top">
    <div id="wrapper">
    <?php 
    $activePage = 'dashboard';
    require_once dirname(__DIR__) . '/partials/hr/sidebar_topbar.php';?>
        <div class="container-fluid">
            <h1 class="h3 mb-4 text-gray-800">HR Dashboard</h1>

            <!-- Alerts -->
            <?php if (!empty($_SESSION['successMessage'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($_SESSION['successMessage']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['successMessage']); ?>
            <?php endif; ?>

            <?php if (!empty($_SESSION['errorMessage'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($_SESSION['errorMessage']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['errorMessage']); ?>
            <?php endif; ?>

            <div class="row">
                <!-- Total Employees Card -->
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Employees</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $totalEmployees ?? 0 ?></div>
                        </div>
                    </div>
                </div>

                <!-- Uniform Types Card -->
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-success shadow h-100 py-2">
                        <div class="card-body">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Uniform Types</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $uniformStats['total_uniform_types'] ?? 0 ?></div>
                        </div>
                    </div>
                </div>

                <!-- Total Stock Card -->
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-info shadow h-100 py-2">
                        <div class="card-body">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total Stock</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $uniformStats['total_stock'] ?? 0 ?></div>
                        </div>
                    </div>
                </div>

                <!-- Reorder Alerts Card -->
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-warning shadow h-100 py-2">
                        <div class="card-body">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Needs Reorder</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $uniformsNeedingReorder ?? 0 ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Quick Actions -->
                <div class="col-lg-6 mb-4">
                    <div class="card shadow">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                        </div>
                        <div class="card-body">
                            <div class="list-group">
                                <a href="<?= htmlspecialchars($base) ?>/hr/employees" class="list-group-item list-group-item-action">
                                    <i class="fas fa-users"></i> View All Employees
                                </a>
                                <a href="<?= htmlspecialchars($base) ?>/hr/uniforms" class="list-group-item list-group-item-action">
                                    <i class="fas fa-tshirt"></i> Manage Uniforms
                                </a>
                                <a href="<?= htmlspecialchars($base) ?>/hr/uniforms/add" class="list-group-item list-group-item-action">
                                    <i class="fas fa-plus"></i> Add New Uniform
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="col-lg-6 mb-4">
                    <div class="card shadow">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Recent Activity (Last 7 Days)</h6>
                        </div>
                        <div class="card-body">
                            <?php if (empty($recentLogs)): ?>
                                <p class="text-muted">No recent activity</p>
                            <?php else: ?>
                                <div style="max-height: 300px; overflow-y: auto;">
                                    <?php foreach (array_slice($recentLogs, 0, 10) as $log): ?>
                                        <div class="mb-2">
                                            <small class="text-muted"><?= date('M d, H:i', strtotime($log['date_logged'])) ?></small>
                                            <p class="mb-0"><strong><?= htmlspecialchars($log['action']) ?></strong></p>
                                            <?php if (!empty($log['details'])): ?>
                                                <small><?= htmlspecialchars($log['details']) ?></small>
                                            <?php endif; ?>
                                        </div>
                                        <hr class="my-1">
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>

    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/jquery/jquery.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/js/storagemart.min.js"></script>
</body>
</html>
