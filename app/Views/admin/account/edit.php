<?php
$base = rtrim(BASE_URL, '/');
require_once __DIR__ . '/../../../Helpers/RoleLabel.php';
require_once __DIR__ . '/../../../Helpers/PasswordPolicy.php';
?>
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
    <link href="<?= htmlspecialchars($base) ?>/assets/css/storagemart.css" rel="stylesheet">
    <link rel="icon" href="<?= htmlspecialchars($base) ?>/assets/img/favicon.ico" type="image/x-icon">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/admin-users.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/vendor/datatables/datatables.min.css" rel="stylesheet">
</head>

<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper">
            <?php 
            $activePage = 'users';
            $userSubPage = 'accounts';
            require_once __DIR__ . '/../../partials/admin/sidebar_topbar.php';?>

                <!-- Begin Page Content -->
                <div class="container-fluid admin-users-page account-edit-page">
                    <div class="page-hero hero-accounts">
                        <div class="row align-items-center">
                            <div class="col-12">
                                <h1><i class="fas fa-user-edit mr-2"></i>Update Account</h1>
                                <p>Update login credentials, role assignments, and employee profile details.</p>
                            </div>
                        </div>
                    </div>

                    <div class="card data-list-card shadow mb-4">
                        <div class="card-header d-flex align-items-center justify-content-between flex-wrap">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="fas fa-id-card-alt mr-1"></i> Account Information
                            </h6>
                        </div>
                        <div class="card-body">
                            <?php if (isset($_GET['reset'])): ?>
                                <div class="alert alert-warning">
                                    <i class="fas fa-key mr-1"></i>
                                    <strong>Password reset request.</strong> Confirm it's really this person (call them or see them in person),
                                    then set a temporary password below and tell it to them directly. They can change it from
                                    <em>Change password</em> in the sidebar after logging in. Saving a new password also unlocks the account.
                                </div>
                            <?php endif; ?>
                            <form class="account-edit-form" action="<?= htmlspecialchars($base) ?>/admin/account/edit" method="POST">
                                    <input type="hidden" name="account_id" value="<?= htmlspecialchars($account['account_id'] ?? '') ?>">
                                    <input type="hidden" name="employee_id" value="<?= htmlspecialchars($account ['employee_id'] ?? '') ?>">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                                    <h5 class="form-section-title">Account Details</h5>
                                    <div class ="row form-row-gap">
                                        <div class = "col-md-6">
                                            <label for="username" class="form-label">Username</label>
                                            <input type="text" class ="form-control" id ="username" name="username" placeholder="Username" value="<?= htmlspecialchars($account['username'] ?? '') ?>" required>
                                        </div>
                                    <div class="col-md-6 position-relative">
                                        <label for="password" class="form-label">Password</label>
                                        <div class="input-group">
                                        <input type="password" class="form-control" id="password" name="password"
                                            placeholder="Leave blank to keep current password" autocomplete="new-password">
                                            <span class="input-group-text" id="showPassword" style="cursor: pointer;">
                                                <i class="fas fa-eye"></i>
                                            </span>
                                        </div>
                                        <small class="form-text text-muted"><?= htmlspecialchars(PasswordPolicy::HINT) ?></small>
                                    </div>


                                    </div>

                                    <div class="row form-row-gap">
                                    <div class="col-md-6">
                                        <label for="usertype" class="form-label">User Type</label>
                                        <select id="usertype" name="usertype" class="form-control" required>
                                            <option value="">-- Select User Type --</option>
                                            <?php
                                            $currentRole = strtoupper(trim((string) ($account['usertype'] ?? '')));
                                            $roleChoices = RoleLabel::assignable();
                                            if ($currentRole !== '' && !isset($roleChoices[$currentRole])) {
                                                // Keep an older role selectable so saving doesn't change it by accident.
                                                $roleChoices[$currentRole] = $currentRole;
                                            }
                                            foreach ($roleChoices as $roleCode => $roleName): ?>
                                                <option value="<?= htmlspecialchars($roleCode) ?>" <?= $currentRole === $roleCode ? 'selected' : '' ?>><?= htmlspecialchars($roleName) ?></option>
                                            <?php endforeach; ?>
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

                                    <h5 class="form-section-title">Employee Details</h5>
                                    <div class ="row form-row-gap">
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
                                    <div class="row form-row-gap">
                                    <div class="col-md-6">
                                        <label for="last-name" class="form-label">Last Name</label>
                                        <input type="text" class="form-control" id="last-name" name="last-name" placeholder="Last name" value="<?= htmlspecialchars($employee['lastname'] ?? '') ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="first-name" class="form-label">First Name</label>
                                        <input type="text" class="form-control" id="first-name" name="first-name" placeholder="First name" value="<?= htmlspecialchars($employee['firstname'] ?? '') ?>" required>
                                    </div>
                                    </div>

                                    <div class="row form-row-gap">
                                    <div class="col-md-6">
                                        <label for="middle-name" class="form-label">Middle Name</label>
                                        <input type="text" class="form-control" id="middle-name" name="middle-name" placeholder="Middle name" value="<?= htmlspecialchars($employee['middlename'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="department" class="form-label">Department</label>
                                        <select id="department" name="department" class="form-control" required>
                                        <option value="">-- Select Department --</option>
                                        <?php
                                        $currentDept = (string) ($employee['department'] ?? '');
                                        $deptCodes = array_map(static fn($d) => (string) $d['code'], $departments ?? []);
                                        ?>
                                        <?php if ($currentDept !== '' && !in_array($currentDept, $deptCodes, true)): ?>
                                            <option value="<?= htmlspecialchars($currentDept) ?>" selected><?= htmlspecialchars($currentDept) ?> (not in department list)</option>
                                        <?php endif; ?>
                                        <?php foreach (($departments ?? []) as $dept): ?>
                                            <option value="<?= htmlspecialchars((string) $dept['code']) ?>"<?= $currentDept === (string) $dept['code'] ? ' selected' : '' ?>><?= htmlspecialchars((string) $dept['label']) ?></option>
                                        <?php endforeach; ?>
                                        </select>
                                    </div>
                                    </div>
                                    <div class="row form-row-gap">
                                        <div class="col-md-6">
                                            <label for="email" class="form-label">Email</label>
                                            <input type="text" class="form-control" id="email" name="email" placeholder="Email" value="<?= htmlspecialchars($employee['email'] ?? '') ?>" required>
                                        </div>
                                    </div>
                                    <div class="form-actions d-flex justify-content-end w-100">
                                        <button type="submit" class="btn btn-primary" name="btnSubmit">
                                            <i class="fas fa-save mr-1"></i> Save Changes
                                        </button>
                                        <a href="<?= htmlspecialchars($base) ?>/admin/account" class="btn btn-outline-danger ml-2">
                                            <i class="fas fa-times mr-1"></i> Cancel
                                        </a>
                                    </div>
                            </form>

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
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/datatables/datatables.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/js/demo/datatables-demo.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/js/admin-edit.js"></script>
    <?php require __DIR__ . '/../../partials/flash_modal.php'; ?>
</body>

</html>
