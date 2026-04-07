<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Storage Mart | IT Dashboard</title>

    <!-- Custom fonts for this template -->
    <link href="<?= htmlspecialchars($base)?>/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="<?= htmlspecialchars($base)?>/assets/css/StorageMart.css" rel="stylesheet">
</head>

<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper">
    <?php 
    $activePage = 'dashboard';
    require_once __DIR__ . '/../../partials/it/sidebar_topbar.php';?>
                <!-- Page Content -->
                <div class="container-fluid">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">IT Dashboard</h1>
                    </div>

                    <div class="row">
                        <!-- Assets Card -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-primary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        Tickets
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        <?php echo $assignedCount; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tickets Card -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-success shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                        On Going Tickets
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        <?php echo $pendingTickets; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Ongoing Tickets -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-warning shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                        Resolve
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        <?php echo $resolveTickets; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!--User Dashboard-->
                <div class="container-fluid">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">My Dashboard</h1>
                    </div>

                    <div class="row">
                        <!-- Assets Card -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-primary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        My Assets
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        <?php echo $myAssets; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tickets Card -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-success shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                        My Tickets
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        <?php echo $myTickets; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Ongoing Tickets -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-warning shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                        My On-going Tickets
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                       <?php echo $myOngoingTickets; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <div class="container-fluid px-3">
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
            </div>    
                <!-- End Page Content -->
            </div>
        </div>
    </div>

    <!-- Scroll to Top -->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <!-- Logout Modal -->
    <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
         aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Ready to Leave?</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">Select "Logout" below if you are ready to end your current session.</div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                    <a class="btn btn-primary" href="<?=htmlspecialchars($base) ?>/logout">Logout</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        window.ticketData = [
            <?= (int)$assignedCount ?>,
            <?= (int)$pendingTickets; ?>,
            <?= (int)$resolveTickets ?>
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
    <script src="<?= htmlspecialchars($base) ?>/assets/js/demo/admindashboard_chart.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/js/demo/dashboard_areachart.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/author/ouaaa.js"></script>
</body>
</html>
