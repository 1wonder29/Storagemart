<?php
// Defensive defaults (PREVENT NOTICES)
$totalAssets              = $totalAssets              ?? 0;
$totalTickets             = $totalTickets             ?? 0;
$pendingTickets           = $pendingTickets           ?? 0;
$resolvedTickets          = $resolvedTickets          ?? 0;

$departmentAssets         = $departmentAssets         ?? 0;
$totalDepartmentTickets   = $totalDepartmentTickets   ?? 0;
$pendingDepartmentTickets = $pendingDepartmentTickets ?? 0;
$resolvedDepartmentTickets= $resolvedDepartmentTickets?? 0;

// If you don’t track this yet
$inProgressTickets = max(0, $totalTickets - ($pendingTickets + $resolvedTickets));

$base = rtrim(BASE_URL, '/');

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Storage Mart | Admin Dashboard</title>

    <!-- Custom fonts for this template -->
    <link href="<?= htmlspecialchars($base) ?>/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Custom styles for this template -->
    <link href="<?= htmlspecialchars($base) ?>/assets/css/storagemart.css" rel="stylesheet">
</head>

<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper">
    <?php 
    $activePage = 'dashboard';
    require_once __DIR__ . '/../../partials/head/sidebar_topbar.php';?>
                <!-- Page Content -->
            <!-- Page Content -->
            <div class="container-fluid">
                <h1 class="h3 mb-4 text-gray-800">My Dashboard</h1>

                <div class="row">
                    <!-- Assets Card -->
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-primary shadow h-100 py-2">
                            <div class="card-body">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Your Assets</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $totalAssets ?></div>
                            </div>
                        </div>
                    </div>

                    <!-- Total Tickets -->
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-success shadow h-100 py-2">
                            <div class="card-body">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Tickets</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $totalTickets ?></div>
                            </div>
                        </div>
                    </div>

                    <!-- Pending Tickets -->
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-warning shadow h-100 py-2">
                            <div class="card-body">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Pending Tickets</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $pendingTickets ?></div>
                            </div>
                        </div>
                    </div>

                    <!-- Resolved Tickets -->
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-info shadow h-100 py-2">
                            <div class="card-body">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Resolved</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $resolvedTickets ?></div>
                            </div>
                        </div>
                    </div>
                </div>


                <h1 class="h3 mb-4 text-gray-800">My Department</h1>

                <div class="row">
                    <!-- Assets Card -->
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-primary shadow h-100 py-2">
                            <div class="card-body">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Department Assets</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $departmentAssets?></div>
                            </div>
                        </div>
                    </div>

                    <!-- Total Tickets -->
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-success shadow h-100 py-2">
                            <div class="card-body">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Tickets</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $totalDepartmentTickets ?></div>
                            </div>
                        </div>
                    </div>

                    <!-- Pending Tickets -->
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-warning shadow h-100 py-2">
                            <div class="card-body">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Pending Tickets</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $pendingDepartmentTickets ?></div>
                            </div>
                        </div>
                    </div>

                    <!-- Resolved Tickets -->
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-info shadow h-100 py-2">
                            <div class="card-body">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Resolved</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $resolvedDepartmentTickets ?></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">

                    <!-- Ticket Status Pie Chart -->
                    <div class="col-xl-4 col-lg-5">
                        <div class="card shadow mb-4">

                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">
                                    Ticket Status Overview
                                </h6>
                            </div>

                            <div class="card-body text-center">
                                <div style="height:300px;">
                                    <canvas id="ticketChart"></canvas>
                                </div>

                                <hr>

                                <span class="small text-muted">
                                    Ticket distribution by status
                                </span>
                            </div>

                        </div>
                    </div>

                    <!-- Ticket Resolution Time Chart -->
                    <div class="col-xl-8 col-lg-7">
                        <div class="card shadow mb-4">

                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">
                                    Ticket Resolution Overview
                                </h6>
                            </div>

                            <div class="card-body text-center">
                                <div style="height:300px;">
                                    <canvas id="myAreaChart"></canvas>
                                </div>

                                <hr>

                                <span class="small text-muted">
                                    Ticket resolution time (hours)
                                </span>
                            </div>

                        </div>
                    </div>

                </div>

                <!-- End Page Content -->
            </div>
        </div>
    </div>
<!--This is flash card -->

    <!-- Scroll to Top -->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>
    <script>
        window.ticketData = [
            <?= (int)$pendingTickets ?>,
            <?= (int)$inProgressTickets ?>,
            <?= (int)$resolvedTickets ?>
        ];
    </script>
        <script>
    window.ticketResolution = {
        labels: <?= json_encode($resolutionLabels) ?>,
        data: <?= json_encode($resolutionData) ?>
    };
    </script>

    <script src="<?=htmlspecialchars ($base)?>/assets/vendor/jquery/jquery.min.js"></script>
    <script src="<?=htmlspecialchars ($base)?>/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="<?=htmlspecialchars ($base)?>/assets/vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="<?=htmlspecialchars ($base)?>/assets/js/sb-admin-2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Dashboard chart -->
    <script src="<?= htmlspecialchars($base) ?>/assets/js/demo/dashboard_chart.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/js/demo/dashboard_areachart.js"></script>
    
</body>
</html>
