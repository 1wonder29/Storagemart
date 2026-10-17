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

    <title>Storage Mart | Admin Accounts</title>

    <link href="<?= htmlspecialchars($base) ?>/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">

    <link href="<?= htmlspecialchars($base) ?>/assets/css/storagemart.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/admin-users.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/role-list-page.css" rel="stylesheet">
    <link rel="icon" href="<?= htmlspecialchars($base) ?>/assets/img/sm-icon-32.png?v=20261016" type="image/png">

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
                            <form action="<?= rtrim($base, '/') ?>/admin/account/add" method="POST" autocomplete="off">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                <p class="text-muted small mb-3">
                                    Order: <b>1</b> the employee, <b>2</b> make their company Gmail and tick every step, <b>3</b> their TMS login.
                                    The account is created only after all Company Gmail steps are ticked.
                                </p>

                                <h5 class="form-section-title"><span class="badge badge-pill badge-primary mr-1">1</span> Employee</h5>
                                <div class="row form-row-gap">
                                    <div class="col-md-4">
                                        <label for="lastname" class="form-label">Last Name</label>
                                        <input type="text" class="form-control" id="lastname" name="lastname" placeholder="Last name" value="<?= htmlspecialchars((string) ($old['lastname'] ?? '')) ?>" required autocomplete="off">
                                    </div>
                                    <div class="col-md-4">
                                        <label for="firstname" class="form-label">First Name</label>
                                        <input type="text" class="form-control" id="firstname" name="firstname" placeholder="First name" value="<?= htmlspecialchars((string) ($old['firstname'] ?? '')) ?>" required autocomplete="off">
                                    </div>
                                    <div class="col-md-4">
                                        <label for="middlename" class="form-label">Middle Name</label>
                                        <input type="text" class="form-control" id="middlename" name="middlename" placeholder="Middle name (N/A if none)" value="<?= htmlspecialchars((string) ($old['middlename'] ?? '')) ?>" autocomplete="off">
                                    </div>
                                </div>
                                <div class="row form-row-gap">
                                    <div class="col-md-4">
                                        <label for="employee_id" class="form-label">Employee ID</label>
                                        <input type="text" class="form-control" id="employee_id" name="employee_id" placeholder="Numbers only, e.g. 2600192" value="<?= htmlspecialchars((string) ($old['employee_id'] ?? '')) ?>" required inputmode="numeric" pattern="[0-9]{1,10}" maxlength="10" title="Numbers only" data-numbers-only autocomplete="off">
                                        <small class="form-text text-muted">Numbers only, from the masterlist.</small>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="usertype" class="form-label">User Type</label>
                                        <select id="usertype" name="usertype" class="form-control" required data-role-department='<?= htmlspecialchars(json_encode($roleDepartments ?? []), ENT_QUOTES) ?>'>
                                            <option value="">-- Select User Type --</option>
                                            <?php foreach (RoleLabel::assignable() as $roleCode => $roleName): ?>
                                                <option value="<?= htmlspecialchars($roleCode) ?>" <?= (($old['usertype'] ?? '') === $roleCode) ? 'selected' : '' ?>><?= htmlspecialchars($roleName) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="department" class="form-label">Department</label>
                                        <select id="department" name="department" class="form-control" required>
                                            <option value="">-- Select Department --</option>
                                            <?php foreach (($departments ?? []) as $dept): ?>
                                                <option value="<?= htmlspecialchars((string) $dept['code']) ?>"<?= (($old['department'] ?? '') === (string) $dept['code']) ? ' selected' : '' ?>><?= htmlspecialchars((string) $dept['label']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="row form-row-gap">
                                    <div class="col-md-6">
                                        <label for="branch_id" class="form-label">Branch</label>
                                        <?php $currentBranch = $old['branch_id'] ?? ($employee['branch_id'] ?? ''); ?>
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
                                    <div class="col-md-6">
                                        <label for="position" class="form-label">Position</label>
                                        <input type="text" class="form-control" id="position" name="position" placeholder="Position" value="<?= htmlspecialchars((string) ($old['position'] ?? '')) ?>" required autocomplete="off">
                                    </div>
                                </div>

                                <?php require __DIR__ . '/_personal_fields.php'; ?>

                                <h5 class="form-section-title mt-4"><span class="badge badge-pill badge-primary mr-1">2</span> Company Gmail <span class="text-muted small font-weight-normal">(make it first)</span></h5>
                                <div class="row form-row-gap">
                                    <div class="col-md-6">
                                        <label for="email" class="form-label">Gmail address</label>
                                        <input type="email" class="form-control" id="email" name="email" placeholder="storagemart.firstnamelastname@gmail.com" value="<?= htmlspecialchars((string) ($old['email'] ?? '')) ?>" required pattern=".+@.+\..+" title="A full email address with @" data-company-email data-first="#firstname" data-last="#lastname" autocomplete="off">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="gmail_password" class="form-label">Gmail password</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control" id="gmail_password" name="gmail_password" minlength="8" maxlength="100" autocomplete="off" spellcheck="false" placeholder="Middle name + Employee ID" data-gmail-formula value="<?= htmlspecialchars((string) ($old['gmail_password'] ?? '')) ?>" required>
                                            <div class="input-group-append">
                                                <button type="button" class="btn btn-outline-secondary" data-gmail-formula-btn="#gmail_password" title="Middle name + Employee ID"><i class="fas fa-calculator"></i> Formula</button>
                                            </div>
                                        </div>
                                        <small class="form-text text-muted">Middle name + Employee ID (e.g. C.2600192), as in the corporate email list. Admin and HR can see it later; it is not the TMS password.</small>
                                    </div>
                                </div>
                                <?php require __DIR__ . '/_gmail_setup.php'; ?>

                                <h5 class="form-section-title"><span class="badge badge-pill badge-primary mr-1">3</span> TMS login</h5>
                                <div class="row form-row-gap">
                                    <div class="col-md-6">
                                        <label for="username" class="form-label">Username</label>
                                        <input type="text" class="form-control" id="username" name="username" placeholder="Same as the Gmail address" value="<?= htmlspecialchars((string) ($old['username'] ?? '')) ?>" required autocomplete="off" data-follow-email="#email">
                                        <small class="form-text text-muted">Fills in with the Gmail address.</small>
                                    </div>
                                    <div class="col-md-6 position-relative">
                                        <label for="password" class="form-label">TMS password</label>
                                        <div class="input-group">
                                            <input type="password" class="form-control" id="password" name="password" placeholder="Enter password" required autocomplete="new-password">
                                            <span class="input-group-text" id="showPassword" style="cursor: pointer;">
                                                <i class="fas fa-eye"></i>
                                            </span>
                                        </div>
                                        <small class="form-text text-muted"><?= htmlspecialchars(PasswordPolicy::ADMIN_HINT) ?></small>
                                    </div>
                                </div>

                                <div class="form-actions">
                                    <button type="submit" class="btn btn-primary" name="btnSubmit">
                                        <i class="fas fa-user-check mr-1"></i> Create account
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcode-generator/1.4.4/qrcode.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/js/user-form.js?v=20261017"></script>
    <?php require __DIR__ . '/../../partials/flash_modal.php'; ?>
</body>

</html>
