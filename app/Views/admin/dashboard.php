<?php
$base = rtrim(BASE_URL, '/');
?>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Storage Mart | Admin Dashboard</title>

    <!-- Custom fonts for this template-->
    <link href="<?= htmlspecialchars($base) ?>/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="<?= htmlspecialchars($base) ?>/assets/css/storagemart.css" rel="stylesheet">

</head>

<body id="page-top">    

    <!-- Page Wrapper -->
    <div id="wrapper">
            <?php 
            $activePage = 'dashboard';
            require_once __DIR__ . '/../partials/admin/sidebar_topbar.php';?>
                <!-- End of Topbar -->

                <!-- Begin Page Content -->
                <div class="container-fluid">

                    <!-- Page Heading -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
                    </div>

                    <!-- Content Row -->
                    <div class="row">

                        <!-- Users Card -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <a href="/admin/account" style="text-decoration: none; color: inherit;">
                                <div class="card border-left-primary shadow h-100 py-2" style="cursor: pointer; transition: 0.3s;" onmouseover="this.style.transform='translateY(-5px)';" onmouseout="this.style.transform='translateY(0)';">
                                    <div class="card-body">
                                        <div class="row no-gutters align-items-center">
                                            <div class="col mr-2">
                                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                    USERS</div>
                                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $userCount; ?></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>

                        <!-- Ticket Card  -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <a href="/admin/tickets" style="text-decoration: none; color: inherit;">
                                <div class="card border-left-success shadow h-100 py-2" style="cursor: pointer; transition: 0.3s;" onmouseover="this.style.transform='translateY(-5px)';" onmouseout="this.style.transform='translateY(0)';">
                                    <div class="card-body">
                                        <div class="row no-gutters align-items-center">
                                            <div class="col mr-2">
                                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                                    Tickets</div>
                                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $ticketCount; ?></div>
                                            </div>
                                            
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <!-- Assets Card -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <a href="/admin/assets" style="text-decoration: none; color: inherit;">
                                <div class="card border-left-success shadow h-100 py-2" style="cursor: pointer; transition: 0.3s;" onmouseover="this.style.transform='translateY(-5px)';" onmouseout="this.style.transform='translateY(0)';">
                                    <div class="card-body">
                                        <div class="row no-gutters align-items-center">
                                            <div class="col mr-2">
                                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                                    Asset</div>
                                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $assetCount; ?></div>
                                            </div>
                                            
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                        
                        <!-- On-going Tickets Card -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <a href="/admin/pendings" style="text-decoration: none; color: inherit;">
                                <div class="card border-left-warning shadow h-100 py-2" style="cursor: pointer; transition: 0.3s;" onmouseover="this.style.transform='translateY(-5px)';" onmouseout="this.style.transform='translateY(0)';">
                                    <div class="card-body">
                                        <div class="row no-gutters align-items-center">
                                            <div class="col mr-2">
                                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">On-going Ticket
                                                </div>
                                                <div class="row no-gutters align-items-center">
                                                    <div class="col-auto">
                                                    <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800"><?php echo $ticketOngoing;?></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>

                    </div>
                    <!-- End Content Row -->

                    <!-- Dashboard Details Row -->
                    <div class="row">
                        <div class="col-xl-7 col-lg-7">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">Overview</h6>
                                </div>
                                <div class="card-body">
                                    <div style="height: 320px;">
                                        <canvas id="adminOverviewChart"></canvas>
                                    </div>
                                    <div class="small text-muted mt-2">
                                        Distribution based on current totals.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-5 col-lg-5">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                                </div>
                                <div class="card-body">
                                    <div class="list-group">
                                        <a class="list-group-item list-group-item-action d-flex align-items-center justify-content-between" href="<?= htmlspecialchars($base) ?>/admin/account">
                                            <span><i class="fas fa-fw fa-user mr-2 text-gray-400"></i>Manage Accounts</span>
                                            <i class="fas fa-chevron-right text-gray-400"></i>
                                        </a>
                                        <a class="list-group-item list-group-item-action d-flex align-items-center justify-content-between" href="<?= htmlspecialchars($base) ?>/admin/tickets">
                                            <span><i class="fas fa-ticket-alt mr-2 text-gray-400"></i>View Tickets</span>
                                            <i class="fas fa-chevron-right text-gray-400"></i>
                                        </a>
                                        <a class="list-group-item list-group-item-action d-flex align-items-center justify-content-between" href="<?= htmlspecialchars($base) ?>/admin/pendings">
                                            <span><i class="fas fa-fw fa-table mr-2 text-gray-400"></i>On-going Tickets</span>
                                            <i class="fas fa-chevron-right text-gray-400"></i>
                                        </a>
                                        <a class="list-group-item list-group-item-action d-flex align-items-center justify-content-between" href="<?= htmlspecialchars($base) ?>/admin/assets">
                                            <span><i class="fas fa-archive mr-2 text-gray-400"></i>Assets Directory</span>
                                            <i class="fas fa-chevron-right text-gray-400"></i>
                                        </a>
                                        <a class="list-group-item list-group-item-action d-flex align-items-center justify-content-between" href="<?= htmlspecialchars($base) ?>/admin/audit-trail">
                                            <span><i class="fas fa-fw fa-history mr-2 text-gray-400"></i>Audit Trail</span>
                                            <i class="fas fa-chevron-right text-gray-400"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- End Dashboard Details Row -->

                </div>
                <!-- /.container-fluid -->

            </div>
            <!-- End of Main Content -->

        </div>
        <!-- End of Content Wrapper -->

    </div>
    <!-- End of Page Wrapper -->

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <!-- Logout Modal-->
    <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Ready to Leave?</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">Select "Logout" below if you are ready to end your current session.</div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                    <a class="btn btn-primary" href="<?= htmlspecialchars($base) ?>/logout">Logout</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap core JavaScript-->
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/jquery/jquery.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Core plugin JavaScript-->
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/jquery-easing/jquery.easing.min.js"></script>
    <!-- Custom scripts for all pages-->
    <script src="<?= htmlspecialchars($base) ?>/assets/js/sb-admin-2.min.js"></script>

    <script>
        window.adminOverviewData = {
            users: <?= (int)($userCount ?? 0) ?>,
            tickets: <?= (int)($ticketCount ?? 0) ?>,
            assets: <?= (int)($assetCount ?? 0) ?>,
            ongoing: <?= (int)($ticketOngoing ?? 0) ?>
        };
    </script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/js/demo/admin_dashboard_overview.js"></script>
</body>

</html>