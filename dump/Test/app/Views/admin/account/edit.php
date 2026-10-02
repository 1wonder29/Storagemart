<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Storage Mart | Accounts Update</title>

    <!-- Custom fonts for this template -->
    <link href="<?= htmlspecialchars($base) ?>/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
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

                <!-- Begin Page Content -->
                <div class="container-fluid">

                    <!-- Page Heading -->
                    <h1 class="h3 mb-2 text-gray-800"></h1>
                    <p class="mb-4">"Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum."</p>

                    <!-- DataTales Example -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Update Account</h6>
                        </div>
                        <div class="card-body">
                            <div class="container mt-4">
                                <form action="<?= htmlspecialchars($base) ?>/admin/account/edit" method="POST">
                                    <input type="hidden" name="account_id" value="<?= htmlspecialchars($account['account_id'] ?? '') ?>">
                                    <input type="hidden" name="employee_id" value="<?= htmlspecialchars($account ['employee_id'] ?? '') ?>">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                                    <h1>Account Details</h1>
                                    <div class ="row mb-5">
                                        <div class = "col-md-6">
                                            <label for="username" class="form-label">Username</label>
                                            <input type="text" class ="form-control" id ="username" name="username" placeholder="Username" value="<?= htmlspecialchars($account['username'] ?? '') ?>" required>
                                        </div>
                                    <div class="col-md-6 position-relative">
                                        <label for="password" class="form-label">Password</label>
                                        <div class="input-group">
                                        <input type="password" class="form-control" id="password" name="password"
                                            placeholder="Leave blank to keep current password">
                                            <span class="input-group-text" id="showPassword" style="cursor: pointer;">
                                                <i class="fas fa-eye"></i>
                                            </span>
                                        </div>
                                    </div>


                                    </div>

                                    <div class="row mb-5">
                                    <div class="col-md-6">
                                        <label for="usertype" class="form-label">User Type</label>
                                        <select id="usertype" name="usertype" class="form-control" required>
                                            <option value="">-- Select User Type --</option>
                                            <option value="ADMIN" <?= (($account['usertype'] ?? '') === 'ADMIN') ? 'selected' : '' ?>>Admin</option>
                                            <option value="IT" <?= (($account['usertype'] ?? '') === 'IT') ? 'selected' : '' ?>>Information Technology</option>
                                            <option value="HEAD" <?= (($account['usertype'] ?? '') === 'HEAD') ? 'selected' : '' ?>>Head</option>
                                            <option value="EMPLOYEE" <?= (($account['usertype'] ?? '') === 'EMPLOYEE') ? 'selected' : '' ?>>Employee</option>
                                        </select>
                                    </div>
                                        <div class="col-md-6">
                                            <label for="status" class="form-label">Status</label>
                                            <select id="status" name="status" class="form-control" required>
                                            <option value="">-- Select Status --</option>
                                            <option value="ACTIVE" <?= (($account['status'] ?? '') === 'ACTIVE') ? 'selected' : '' ?>>Active</option>
                                            <option value="INACTIVE" <?= (($account['status'] ?? '') === 'INACTIVE') ? 'selected' : '' ?>>Inactive</option>
                                            </select>
                                        </div>
                                    </div>


                                    <h1>Employee Details </h1>
                                    <div class ="row mb-5">
                                            <div class= "col-md-6">
                                                <label for="employee_id" class="form-label">Employee ID</label>
                                                <input type="text" class="form-control" id="employee_id" name="employee_id" placeholder="Employee ID" value="<?= htmlspecialchars($employee['employee_id'] ?? '') ?>" readonly> 
                                            </div>
                                                <div class="col-md-6">
                                                <label for="branch_id" class="form-label">Branch</label>
                                                    <?php $currentBranch = $employee['branch_id'] ?? ''; ?>
                                                    <select id="branch_id" name="branch_id" class="form-control" required>
                                                        <option value="">-- Select Branch --</option>
                                                        <?php foreach ($branches as $b):
                                                            $bId = $b['branch_id'];
                                                            $bName = $b['branchName'];
                                                            $sel = ($bId == $currentBranch) ? ' selected' : '';
                                                        ?>
                                                            <option value="<?= htmlspecialchars($bId) ?>"<?= $sel ?>><?= htmlspecialchars($bName) ?></option>
                                                        <?php endforeach; ?>
                                                    </select>

                                                </div>
                                            </div>
                                    <div class="row mb-5">
                                    <div class="col-md-6">
                                        <label for="last-name" class="form-label">Last Name</label>
                                        <input type="text" class="form-control" id="last-name" name="last-name" placeholder="Last name" value="<?= htmlspecialchars($employee['lastname'] ?? '') ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="first-name" class="form-label">First Name</label>
                                        <input type="text" class="form-control" id="first-name" name="first-name" placeholder="First name" value="<?= htmlspecialchars($employee['firstname'] ?? '') ?>" required>
                                    </div>
                                    </div>

                                    <div class="row mb-5">
                                    <div class="col-md-6">
                                        <label for="middle-name" class="form-label">Middle Name</label>
                                        <input type="text" class="form-control" id="middle-name" name="middle-name" placeholder="Middle name" value="<?= htmlspecialchars($employee['middlename'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="department" class="form-label">Department</label>
                                        <select id="department" name="department" class="form-control" required>
                                        <option value="">-- Select Department --</option>
                                        <option value="IT"<?= (($employee['department'] ?? '') === 'IT') ? 'selected' : '' ?>>Information Technology</option>
                                        <option value="Sales" <?= (($employee['department'] ?? '') === 'Sales') ? 'selected' : '' ?>>Sales</option>
                                        <option value="Purchasing"<?= (($employee['department'] ?? '') === 'Purchasing') ? 'selected' : '' ?>>Purchasing</option>
                                        <option value="Accounting"<?= (($employee['department'] ?? '') === 'Accounting') ? 'selected' : '' ?>>Accounting</option>
                                        <option value="HRMD"<?= (($employee['department'] ?? '') === 'HRMD') ? 'selected' : '' ?>>Human Resource Management and Development</option>
                                        <option value="Marketing"<?= (($employee['department'] ?? '') === 'Marketing') ? 'selected' : '' ?>>Marketing</option>
                                        <option value="Compliance"<?= (($employee['department'] ?? '') === 'Compliance') ? 'selected' : '' ?>>Corporate Compliance</option>
                                        <option value="Operations"<?= (($employee['department'] ?? '') === 'Operations') ? 'selected' : '' ?>>Operations</option>
                                        <option value="Digital Marketing"<?= (($employee['department'] ?? '') === 'Digital Marketing') ? 'selected' : '' ?>>Digital Marketing</option>
                                        </select>
                                    </div>
                                    </div>
                                    <div class="row mb-5">
                                        <div class="col-md-6">
                                            <label for="email" class="form-label">Email</label>
                                            <input type="text" class="form-control" id="email" name="email" placeholder="Email" value="<?= htmlspecialchars($employee['email'] ?? '') ?>" required>
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-primary" name="btnSubmit">Submit</button>
                                    <a href="<?= htmlspecialchars($base) ?>/admin/account" class="btn btn-danger">Cancel</a>
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
    <script>
    (function(){
        var btn = document.getElementById('closeModalBtn');
        var modal = document.getElementById('updateModal');
        if (!modal) return;
        // ensure modal visible (it already is styled inline as visible)
        modal.style.display = 'flex';
        // focus OK button for keyboard users
        if (btn) btn.focus();
        btn.addEventListener('click', function () {
            modal.style.display = 'none';
        });
        modal.addEventListener('click', function(e){
            if (e.target === this) this.style.display='none';
        });
    })();
    </script>



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
    <script src="<?= htmlspecialchars($base) ?>/assets/js/demo/datatables-demo.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/js/admin-edit.js"></script>
</body>

</html>