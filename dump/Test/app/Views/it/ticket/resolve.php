<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Storage Mart | IT Resolve Tickets - Tables</title>

    <!-- Custom fonts for this template -->
    <link href="<?= htmlspecialchars($base)?>/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="<?= htmlspecialchars($base)?>/assets/css/StorageMart.css" rel="stylesheet">
        <link rel="icon" href="<?= htmlspecialchars($base)?>/assets/img/favicon.ico" type="image/x-icon">

    <!-- Custom styles for this page -->
    <link href="<?= htmlspecialchars($base)?>/assets/vendor/datatables/dataTables.min.css" rel="stylesheet">

</head>

<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper">
            <?php 
            $activePage = 'tickets';
            require_once __DIR__ . '/../../partials/it/sidebar_topbar.php';?>
                        <!-- Begin Page Content -->
                <div class="container-fluid">

                    <!-- DataTales Example -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">List of Resolve Tickets</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="ticketTables" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>Ticket #</th>
                                            <th>Employee Name</th>
                                            <th>Asset</th>
                                            <th>Branch</th>
											                      <th>Technical Purpose</th>
                                            <th>Action Taken</th>
                                            <th>Result</th>
											                      <th>Remarks</th>
                                            <th>Date Performed</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tfoot>
                                        <tr>
                                            <th>Ticket #</th>
                                            <th>Employee Name</th>
                                            <th>Asset</th>
                                            <th>Branch</th>
											                      <th>Technical Purpose</th>
                                            <th>Action Taken</th>
                                            <th>Result</th>
											                      <th>Remarks</th>
                                            <th>Date Performed</th>
                                            <th>Action</th>
                                        </tr>
                                    </tfoot>
                                    <tbody>
                                        <?php foreach($tickets as $row) { ?>
                                            <tr>  
                                                <td><?= htmlspecialchars($row['ticket_number']) ?></td>
                                                <td><?= htmlspecialchars($row['employee_name']) ?></td>
                                                <td><?= htmlspecialchars($row['asset']) ?></td>
                                                <td><?= htmlspecialchars($row['branchName']) ?></td>
                                                <td><?= htmlspecialchars($row['technical_purpose']) ?></td>
                                                <td><?= htmlspecialchars($row['action_taken']) ?></td>
                                                <td><?= htmlspecialchars($row['result']) ?></td>
                                                <td><?= htmlspecialchars($row['remarks']) ?></td>
                                                <td><?= htmlspecialchars($row['date_performed']) ?></td>
                                                <td>
                                                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                                                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <span class="mr-2 d-none d-lg-inline text-gray-600 ">
                                                        Action</span>
                                                    </a>
                                                    <!-- Dropdown - User Information -->
                                                    <div class="dropdown-menu dropdown-menu-right shadow" aria-labelledby="userDropdown">
                                                    <a href="<?= htmlspecialchars($base) ?>/assets/generatePDF/generate_technical.php?ticket_id=<?= $row['ticket_id'] ?>" class="dropdown-item">
                                                        <i class="fas fa-file-word a-fw mr-2 text-black-400"></i> Generate Technical Report
                                                    </a>
                                                    </div>

                                                </td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>
                <!-- /.container-fluid -->

            </div>
            <!-- End of Main Content -->

            <!-- Footer -->
            <!-- End of Footer -->

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
        <script src="<?= htmlspecialchars($base)?>/assets/vendor/jquery/jquery.min.js"></script>
        <script src="<?= htmlspecialchars($base)?>/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

        <!-- Core plugin JavaScript-->
        <script src="<?= htmlspecialchars($base)?>/assets/vendor/jquery-easing/jquery.easing.min.js"></script>

        <!-- Custom scripts for all pages-->
        <script src="<?= htmlspecialchars($base)?>/assets/js/sb-admin-2.min.js"></script>

        <!-- Page level plugins -->
        <script src="<?= htmlspecialchars($base)?>/assets/vendor/datatables/jquery.dataTables.min.js"></script>
        <script src="<?= htmlspecialchars($base)?>/assets/vendor/datatables/dataTables.min.js"></script>

        <!-- Page level custom scripts -->
        <script src="<?= htmlspecialchars($base)?>/assets/js/demo/datatables-demo.js"></script>
</body>

</html>