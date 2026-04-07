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

    <title>Storage Mart | File Ticket Admin</title>

    <!-- Custom fonts for this template -->
    <link href="<?= htmlspecialchars($base)?>/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="<?= htmlspecialchars($base)?>/assets/css/storagemart.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($base)?>/assets/css/input.css" rel="stylesheet">

</head>

<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper">
            <?php 
            $activePage = 'tickets';
            require_once __DIR__ . '/../../partials/admin/sidebar_topbar.php';?>
                <!-- End of Topbar -->

                <!-- Begin Page Content -->
                <div class="container-fluid">

                    <!-- Page Heading -->
                    <h1 class="h3 mb-2 text-gray-800"></h1>
                    <p class="mb-4">"Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum."</p>

                    <!-- DataTales Example -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Add Ticket</h6>
                        </div>
                        <div class="card-body">
                            <div class="container mt-4">
                            <form action="<?= htmlspecialchars($base) ?>/admin/tickets/file" method="POST">   
                                <input type="hidden" name="inventory_id" value="<?= htmlspecialchars($inventory['inventory_id'] ?? '') ?>">
                                <input type="hidden" name="branch_id" value="<?= htmlspecialchars($inventory['branch_id'] ?? '') ?>">                                 
                            <h1>Employee Details</h1>
                                <div class="row mb-5">
                                    <div class="col-md-6">
                                        <label for="employee_id" class="form-label">Employee ID</label>
                                        <input type="text" class="form-control" id="employee_id" name="employee_id" value="<?= htmlspecialchars($inventory['employee_id'] ?? '') ?>" readonly>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="fullname" class="form-label">Fullname</label>
                                        <input type="text" class="form-control" id="fullname" name="fullname" placeholder="Full Name" value="<?= htmlspecialchars($inventory['fullname'] ?? '') ?>" readonly>
                                    </div>
                                    </div>
                                    <div class ="row mb-5">
                                        <div class="col-md-6">
                                            <label for="department" class="form-label">Department</label>
                                            <input type="text" class="form-control" id="department" name="department" placeholder="Department" value="<?= htmlspecialchars($inventory['department'] ?? '') ?>" readonly>
                                        </div>
                                    <div class="col-md-6">
                                        <label for="branchName" class="form-label">Branch</label>
                                        <input type="text" class="form-control" id="branchName" name="branchName" placeholder="Branch" value="<?= htmlspecialchars($inventory['branchName'] ?? '') ?>" readonly>
                                    </div>
                                    </div>
                                <hr></hr>
                                <h1>Asset Details</h1>
                                <div class="row mb-5">
                                    <div class="col-md-6">
                                        <label for="assetNumber" class="form-label">Asset Number</label>
                                        <input type="text" class="form-control" id="assetNumber" name="assetNumber" placeholder="Asset Number" value="<?= htmlspecialchars($inventory['assetNumber'] ?? '') ?>" readonly>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="groupName" class="form-label">Model Name</label>
                                        <input type="text" class="form-control" id="groupName" name="groupName" placeholder="Model" value="<?= htmlspecialchars($inventory['groupName'] ?? '') ?>" readonly>
                                    </div>
                                    </div>
                                <h1>Ticket Details</h1>
                                    <div class="row mb-5">
                                        <div class="col-md-6">
                                            <label for="department" class="form-label">Assign to</label>
                                                <select id="ticket_assign" name="ticket_assign" class="form-control">
                                                    <option value="">-- Select Assignee --</option>
                                                    <?php foreach ($itStaff as $it): ?>
                                                        <option value="<?= (int)$it['employee_id'] ?>">
                                                            <?= htmlspecialchars($it['firstname'] . ' ' . $it['lastname']) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                        </div>
                                    </div>
                                    <div class ="row mb-5">
                                            <div class ="col-md-6">
                                                <label for="technical_purpose" class="form-label">Technical Purpose</label>
                                                <select id="technical_purpose" name="technical_purpose" class="form-control" required>
                                                <option value="">-- Select Purpose --</option>
                                                <option value="CCTV & MAINTAINANCE">CCTV & MAINTAINANCE</option>
                                                <option value=""></option>
                                                <option value=""></option>
                                                </select>
                                            </div>

                                            <div class="col-md-6">
                                                <label for = "concern-details" class ="form-label">Concern Details</label>
                                                <textarea id ="concern_details" name="concern_details"class="form-control" rows="6" maxlength="1000" required></textarea>
                                                <small class="form-text text-muted">Maximum 1000 characters.</small>
                                            </div>
                                    </div>

                                    <div class ="row mb-5">
                                            <div class="col-md-6">
                                                <label for = "action" class ="form-label">Action Taken</label>
                                                <textarea id ="action" name="action"class="form-control" rows="6" maxlength="1000" required></textarea>
                                                <small class="form-text text-muted">Maximum 1000 characters.</small>
                                            </div>

                                            <div class="col-md-6">
                                                <label for = "result" class ="form-label">Result Details</label>
                                                <textarea id ="result" name="result" class="form-control" rows="6" maxlength="1000" required></textarea>
                                                <small class="form-text text-muted">Maximum 1000 characters.</small>
                                            </div>
                                    </div>

                                    <div class ="row mb-5">
                                        <div class ="col-md-6">
                                            <label for ="priority" class ="form-label">Priority</label>
                                                <select id="priority" name="priority" class="form-control" required>
                                                <option value="">-- Select Priority level --</option>
                                                <option value="low">Low</option>
                                                <option value="medium">Medium</option>
                                                <option value="high">High</option>
                                                </select>
                                        </div>

                                        <div class ="col-md-6">
                                            <label for ="category" class ="form-label">Category</label>
                                                <select id="category" name="category" class="form-control" required>
                                                <option value="">-- Select Category --</option>
                                                <option value="Software,Hardware">Software & Hardware</option>
                                                <option value=""></option>
                                                <option value=""></option>
                                                </select>
                                        </div>

                                    </div>

                                    <div class="row mb-5">
                                            <div class="col-md-6">
                                                <label for = "remarks" class ="form-label">Remarks</label>
                                                <textarea id ="remarks" name="remarks" class="form-control" rows="6" maxlength="1000" required></textarea>
                                                <small class="form-text text-muted">Maximum 1000 characters.</small>
                                            </div>
                                    </div>

                                    <button type="submit" class="btn btn-primary" name="btnSubmit">Submit</button>
                                    <a href="<?= htmlspecialchars($base) ?>/admin/tickets" class="btn btn-danger">Cancel</a>
                                    </form>
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
<?php
$flashSuccess = $_SESSION['flash_success'] ?? '';
$flashError   = $_SESSION['flash_error'] ?? '';
unset($_SESSION['flash_success'], $_SESSION['flash_error']);
?>

<!-- Notification Modal -->
<div class="modal fade" id="notificationModal" tabindex="-1" role="dialog" aria-labelledby="notificationTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="notificationTitle">Notification</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" id="notificationBody">
        <!-- message will go here -->
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">OK</button>
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
    <script src="<?= htmlspecialchars($base) ?>/assets/js/ticket/file_ticket.js"></script>
</body>

</html>