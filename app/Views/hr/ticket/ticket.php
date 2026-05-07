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

    <title>Storage Mart Tickets - List of Tickets</title>

    <!-- Custom fonts for this template -->
    <link href="<?= htmlspecialchars($base)?>/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">

    <!-- Custom styles for this template -->
        <!-- Custom styles for this template -->
        <link href="<?= htmlspecialchars($base) ?>/assets/css/storagemart.css" rel="stylesheet">
        <link rel="icon" href="<?= htmlspecialchars($base) ?>/assets/img/favicon.ico" type="image/x-icon">
        <!-- Custom styles for this page -->
        <link href="<?= htmlspecialchars($base) ?>/assets/vendor/datatables/datatables.min.css" rel="stylesheet">

</head>


<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper">
        <?php 
        $activePage = 'tickets';
        require_once __DIR__ . '/../../partials/hr/sidebar_topbar.php';?>

            <!-- Begin Page Content -->
            <div class="container-fluid">

                <!-- Page Heading -->
                <h1 class="h3 mb-2 text-gray-800">List of Tickets</h1>


                <!-- Main content -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-primary">List of Tickets</h6>
                        <a href="<?= htmlspecialchars($base) ?>/hr/tickets/create" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Add Ticket
                        </a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="ticketsTable" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>Ticket #</th>
                                        <th>Concern Details</th>
                                        <th>Branch</th>
                                        <th>Category</th>
                                        <th>Priority</th>
                                        <th>Status</th>
                                        <th>Date Filed</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tfoot>
                                    <tr>

                                    </tr>
                                </tfoot>
                                <tbody>
                                    <?php foreach($tickets as $row): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($row['ticket_number']) ?></td>
                                            <td><?= htmlspecialchars($row['concern_details']) ?></td>
                                            <td><?= htmlspecialchars($row['branchName']) ?></td>
                                            <td><?= htmlspecialchars($row['category']) ?></td>
                                            <td><?= htmlspecialchars($row['priority']) ?></td>
                                            <td><?= htmlspecialchars($row['status']) ?></td>
                                            <td><?= htmlspecialchars($row['date_filed']) ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-primary viewBtn" 
                                                    data-ticketid="<?= $row['ticket_id'] ?>" 
                                                    data-ticketnum="<?= htmlspecialchars($row['ticket_number']) ?>"
                                                    data-employee="<?= htmlspecialchars($row['employee_name']) ?>"
                                                    data-branch="<?= htmlspecialchars($row['branchName']) ?>"
                                                    data-priority="<?= htmlspecialchars($row['priority']) ?>"
                                                    data-status="<?= htmlspecialchars($row['status']) ?>">
                                                    View
                                                </button>
                                       <?php if (strtolower($row['status']) === 'resolved'): ?>
                                                <button class="btn btn-sm btn-warning rateBtn"
                                                    data-ticketid="<?= $row['ticket_id'] ?>">
                                                    <i class="fas fa-star"></i> Rate
                                                </button>
                                                <button class="btn btn-sm btn-info uploadBtn"
                                                    data-ticketid="<?= $row['ticket_id'] ?>"
                                                    data-ticketnum="<?= htmlspecialchars($row['ticket_number']) ?>">
                                                    <i class="fas fa-upload"></i> Upload
                                                </button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /.container-fluid -->

    </div>
    <!-- End of Page Wrapper -->

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <!-- Bootstrap core JavaScript-->
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/jquery/jquery.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Core plugin JavaScript-->
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/jquery-easing/jquery.easing.min.js"></script>

    <!-- Custom scripts for all pages-->
    <script src="<?= htmlspecialchars($base) ?>/assets/js/sb-admin-2.min.js"></script>

    <!-- Page level plugins -->
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/datatables/datatables.min.js"></script>

    <!-- Page level custom scripts -->
    <script>
        // DataTables initialization
        $(document).ready(function() {
            $('#ticketsTable').DataTable({
                "order": [[6, 'desc']]
            });
        });
    </script>

</body>

</html>
