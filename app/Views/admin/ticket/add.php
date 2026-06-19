<?php
$base = rtrim(BASE_URL, '/');
?>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Storage Mart - Add Ticket</title>

    <link href="<?= htmlspecialchars($base) ?>/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/storagemart.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/admin-ticket-list.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/role-list-page.css" rel="stylesheet">
    <link rel="icon" href="<?= htmlspecialchars($base) ?>/assets/img/favicon.ico" type="image/x-icon">
    <link href="<?= htmlspecialchars($base) ?>/assets/vendor/datatables/datatables.min.css" rel="stylesheet">
</head>

<body id="page-top">

    <div id="wrapper">
            <?php 
            $activePage = 'tickets';
            require_once __DIR__ . '/../../partials/admin/sidebar_topbar.php';?>

                <div class="container-fluid admin-ticket-page role-form-page">

                    <div class="page-hero">
                        <div class="row align-items-center">
                            <div class="col-12">
                                <h1><i class="fas fa-ticket-alt mr-2"></i>Add Ticket</h1>
                                <p>Search for an employee and select assets to file a new support ticket.</p>
                            </div>
                        </div>
                    </div>

                    <div class="card form-card shadow mb-4">
                        <div class="card-header">
                            <h6><i class="fas fa-user mr-1"></i>Employee Details</h6>
                        </div>
                        <div class="card-body">
                            <form action="<?= htmlspecialchars($base) ?>/admin/tickets/add" method="POST">
                                <div class="row form-row-gap">
                                    <div class="col-md-6">
                                        <label for="employee_search" class="form-label">Search Employee</label>
                                        <div class="input-group mb-3">
                                            <input type="text" id="employee_search" class="form-control" placeholder="Type employee name or ID">
                                            <button type="button" class="btn btn-primary" id="btnSearchEmployee">Search</button>
                                        </div>
                                        <input type="hidden" id="employee_id" name="employee_id">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="fullname" class="form-label">Fullname</label>
                                        <input type="text" class="form-control" id="fullname" name="fullname" placeholder="Full Name" required>
                                    </div>
                                </div>
                                <div class="row form-row-gap">
                                    <div class="col-md-6">
                                        <label for="department" class="form-label">Department</label>
                                        <input type="text" class="form-control" id="department" name="department" placeholder="Department" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="branch" class="form-label">Branch</label>
                                        <input type="text" class="form-control" id="branch" name="branch" placeholder="Branch" required>
                                    </div>
                                </div>

                                <h5 class="form-section-title mt-4">Employee Assets</h5>
                                <div class="table-responsive">
                                    <table class="table table-bordered" id="asset-ticket" width="100%" cellspacing="0">
                                        <thead>
                                            <tr>
                                                <th>Asset Number</th>
                                                <th>Name</th>
                                                <th>IC CODE</th>
                                                <th>Description</th>
                                                <th>Serial Number</th>
                                                <th>Year Purchased</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tfoot>
                                            <tr>
                                                <th>Asset Number</th>
                                                <th>Name</th>
                                                <th>IC CODE</th>
                                                <th>Description</th>
                                                <th>Serial Number</th>
                                                <th>Year Purchased</th>
                                                <th>Action</th>
                                            </tr>
                                        </tfoot>
                                        <tbody id="assetsTable">
                                        </tbody>
                                    </table>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                </div>

            </div>

        </div>

    </div>

    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

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

    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/jquery/jquery.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/js/sb-admin-2.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/datatables/datatables.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/js/demo/datatables-demo.js"></script>
    <script>
        window.BASE_URL = "<?= htmlspecialchars($base) ?>";
    </script>
    <script src="<?= htmlspecialchars($base) ?>/assets/js/search_employee.js"></script>
</body>
</html>
