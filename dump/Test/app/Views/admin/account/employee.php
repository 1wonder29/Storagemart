<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Storage Mart Employee - Tables</title>
    <!-- Custom fonts for this template -->
    <link href="<?= htmlspecialchars($base)?>/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="<?= htmlspecialchars($base) ?>/assets/css/StorageMart.css" rel="stylesheet">
    <link rel="icon" href="<?= htmlspecialchars($base) ?>/assets/img/favicon.ico" type="image/x-icon">
    <link href="<?= htmlspecialchars($base) ?>/assets/vendor/datatables/dataTables.min.css" rel="stylesheet">
</head>

<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper">
            <?php 
            $activePage = 'users';
            require_once __DIR__ . '/../../partials/admin/sidebar_topbar.php';?>
                <!-- End of Topbar -->

                <!-- Begin Page Content -->
                <div class="container-fluid">

                    <!-- Page Heading -->
                    <h1 class="h3 mb-2 text-gray-800">Tables</h1>
                    <p class="mb-4">DataTables is a third party plugin that is used to generate the demo table below.
                        For more information about DataTables, please visit the <a target="_blank"
                            href="https://datatables.net">official DataTables documentation</a>.</p>

                    <!-- Main conctent -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">List of Employee</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="employee-table" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>Employee ID</th>
											<th>Account ID</th>
                                            <th>Last name</th>
                                            <th>First name</th>
                                            <th>Middle name</th>
											<th>Department</th>
                                            <th>Position</th>
											<th>Branch</th>
                                            <th>Email</th>
                                            <th>Created by</th>
                                            <th>Date created</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($employees) && is_array($employees)): ?>
                                            <?php foreach ($employees as $row): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($row['employee_id']) ?></td>
                                            <td><?= htmlspecialchars($row['account_id']) ?></td>
                                            <td><?= htmlspecialchars($row['lastname']) ?></td>
                                            <td><?= htmlspecialchars($row['firstname']) ?></td>
                                            <td><?= htmlspecialchars($row['middlename']) ?></td>
                                            <td><?= htmlspecialchars($row['department']) ?></td>
                                            <td><?= htmlspecialchars($row['position']) ?></td>
                                            <td><?= htmlspecialchars($row['branchName']) ?></td>
                                            <td><?= htmlspecialchars($row['email']) ?></td>
                                            <td><?= htmlspecialchars($row['createdby']) ?></td>
                                            <td><?= htmlspecialchars($row['datecreated']) ?></td>
                                                <td>
                                                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                                                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                        <span class="mr-2 d-none d-lg-inline text-gray-600 ">
                                                            Action</span>
                                                    </a>
                                                        <div class="dropdown-menu dropdown-menu-right shadow" aria-labelledby="userDropdown">
                                                            <a class="dropdown-item" href="<?= htmlspecialchars($base) ?>/admin/assets/view?employee_id=<?= $row['employee_id']; ?>">
                                                                <i class="fas fa-eye fa-sm fa-fw mr-2 text-black-400"></i>
                                                                View Asset
                                                            </a>
                                                        </div>
                                                </td>
                                        </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

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
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/jquery/jquery.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Core plugin JavaScript-->
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/jquery-easing/jquery.easing.min.js"></script>
    <!-- Custom scripts for all pages-->
    <script src="<?= htmlspecialchars($base) ?>/assets/js/sb-admin-2.min.js"></script>

    <!-- Page level plugins -->
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/datatables/dataTables.min.js"></script>

    <!-- Page level custom scripts -->
    <script src="<?= htmlspecialchars($base) ?>/assets/js/demo/datatables-demo.js"></script>
</body>

</html>