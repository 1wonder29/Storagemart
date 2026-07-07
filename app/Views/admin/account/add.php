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

    <title>Storage Mart | Admin Accounts</title>

    <link href="<?= htmlspecialchars($base) ?>/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">

    <link href="<?= htmlspecialchars($base) ?>/assets/css/storagemart.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/admin-users.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/role-list-page.css" rel="stylesheet">
    <link rel="icon" href="<?= htmlspecialchars($base) ?>/assets/img/favicon.ico" type="image/x-icon">

</head>

<body id="page-top">

    <div id="wrapper">
            <?php 
            $activePage = 'users';
            $userSubPage = 'accounts';
            require_once __DIR__ . '/../../partials/admin/sidebar_topbar.php';?>

                <div class="container-fluid admin-users-page role-form-page">

                    <div class="page-hero hero-accounts">
                        <div class="row align-items-center">
                            <div class="col-12">
                                <h1><i class="fas fa-user-plus mr-2"></i>Add Account</h1>
                                <p>Create a new system account with login credentials and employee profile details.</p>
                            </div>
                        </div>
                    </div>

                    <div class="card form-card shadow mb-4">
                        <div class="card-header d-flex align-items-center justify-content-between flex-wrap">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="fas fa-id-card-alt mr-1"></i> Account Information
                            </h6>
                        </div>
                        <div class="card-body">
                            <form action="<?= rtrim($base, '/') ?>/admin/account/add" method="POST">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

                                <h5 class="form-section-title">Account Details</h5>
                                <div class="row form-row-gap">
                                    <div class="col-md-6">
                                        <label for="username" class="form-label">Username</label>
                                        <input type="text" class="form-control" id="username" name="username" placeholder="Username" required>
                                    </div>
                                    <div class="col-md-6 position-relative">
                                        <label for="password" class="form-label">Password</label>
                                        <div class="input-group">
                                            <input type="password" class="form-control" id="password" name="password" placeholder="Enter password" required>
                                            <span class="input-group-text" id="showPassword" style="cursor: pointer;">
                                                <i class="fas fa-eye"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div class="row form-row-gap">
                                    <div class="col-md-6">
                                        <label for="usertype" class="form-label">User Type</label>
                                        <select id="usertype" name="usertype" class="form-control" required>
                                            <option value="">-- Select User Type --</option>
                                            <option value="ADMIN">Admin</option>
                                            <option value="HEAD">Head</option>
                                            <option value="HR">HR</option>
                                            <option value="IT">Information Technology</option>
                                            <option value="AOM">Area Operation Manager</option>
                                            <option value="HOM">Head Of Operation</option>
                                            <option value="EMPLOYEE">Employee</option>
                                        </select>
                                    </div>
                                </div>

                                <h5 class="form-section-title">Employee Details</h5>
                                <div class="row form-row-gap">
                                    <div class="col-md-6">
                                        <label for="employee_id" class="form-label">Employee ID</label>
                                        <input type="text" class="form-control" id="employee_id" name="employee_id" placeholder="Employee ID" required>
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
                                        <label for="lastname" class="form-label">Last Name</label>
                                        <input type="text" class="form-control" id="lastname" name="lastname" placeholder="Last name" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="firstname" class="form-label">First Name</label>
                                        <input type="text" class="form-control" id="firstname" name="firstname" placeholder="First name" required>
                                    </div>
                                </div>

                                <div class="row form-row-gap">
                                    <div class="col-md-6">
                                        <label for="middlename" class="form-label">Middle Name</label>
                                        <input type="text" class="form-control" id="middlename" name="middlename" placeholder="Middle name">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="department" class="form-label">Department</label>
                                        <select id="department" name="department" class="form-control" required>
                                            <option value="">-- Select Department --</option>
                                            <option value="IT">Information Technology</option>
                                            <option value="Sales">Sales</option>
                                            <option value="Purchasing">Purchasing</option>
                                            <option value="Accounting">Accounting</option>
                                            <option value="HRMD">Human Resource Management and Development</option>
                                            <option value="Marketing">Marketing</option>
                                            <option value="Compliance">Corporate Compliance</option>
                                            <option value="Operations">Operations</option>
                                            <option value="Digital Marketing">Digital Marketing</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row form-row-gap">
                                    <div class="col-md-6">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="text" class="form-control" id="email" name="email" placeholder="Email" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="position" class="form-label">Position</label>
                                        <input type="text" class="form-control" id="position" name="position" placeholder="Position" required>
                                    </div>
                                </div>

                                <div class="form-actions">
                                    <button type="submit" class="btn btn-primary" name="btnSubmit">
                                        <i class="fas fa-save mr-1"></i> Submit
                                    </button>
                                    <a href="<?= htmlspecialchars($base)?>/admin/account" class="btn btn-outline-danger">Cancel</a>
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

    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/jquery/jquery.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/js/sb-admin-2.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/js/admin-edit.js"></script>
</body>

</html>
