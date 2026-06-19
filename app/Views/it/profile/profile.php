<?php
$base = rtrim(BASE_URL, '/');
$fullName = trim(($profile['lastname'] ?? '') . ', ' . ($profile['firstname'] ?? '') . ' ' . ($profile['middlename'] ?? ''));
$displayName = $fullName !== ',' ? trim($fullName) : '-';
$dateCreated = $profile['account_datecreated'] ?? null;
$dateCreatedLabel = $dateCreated ? date('M d, Y', strtotime($dateCreated)) : '-';
?>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Storage Mart | IT Profile</title>

    <link href="<?= htmlspecialchars($base) ?>/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/storagemart.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/profile-page.css" rel="stylesheet">
    <?php require_once __DIR__ . '/../../partials/it/theme_head.php'; ?>
</head>
<body id="page-top">
<div id="wrapper">
    <?php
    $activePage = 'profile';
    require_once __DIR__ . '/../../partials/it/sidebar_topbar.php';
    ?>

    <div class="container-fluid profile-page theme-it">

        <div class="profile-hero">
            <div class="d-flex align-items-center flex-wrap">
                <div class="profile-avatar mr-3 mb-3 mb-md-0">
                    <i class="fas fa-laptop-code"></i>
                </div>
                <div>
                    <h1><?= htmlspecialchars($displayName) ?></h1>
                    <div class="profile-role"><?= htmlspecialchars($profile['position'] ?? '-') ?></div>
                    <?php if (!empty($profile['status'])): ?>
                        <span class="profile-badge"><?= htmlspecialchars($profile['status']) ?></span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="card profile-card shadow mb-4">
            <div class="card-header">
                <h6><i class="fas fa-id-card"></i>Account Information</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="profile-field">
                            <div class="profile-field-label">Full Name</div>
                            <div class="profile-field-value"><?= htmlspecialchars($displayName) ?></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="profile-field">
                            <div class="profile-field-label">Username</div>
                            <div class="profile-field-value"><?= htmlspecialchars($profile['username'] ?? '-') ?></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="profile-field">
                            <div class="profile-field-label">Employee ID</div>
                            <div class="profile-field-value"><?= htmlspecialchars((string)($profile['employee_id'] ?? '-')) ?></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="profile-field">
                            <div class="profile-field-label">User Type</div>
                            <div class="profile-field-value"><?= htmlspecialchars($profile['usertype'] ?? '-') ?></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="profile-field">
                            <div class="profile-field-label">Department</div>
                            <div class="profile-field-value"><?= htmlspecialchars($profile['department'] ?? '-') ?></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="profile-field">
                            <div class="profile-field-label">Position</div>
                            <div class="profile-field-value"><?= htmlspecialchars($profile['position'] ?? '-') ?></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="profile-field">
                            <div class="profile-field-label">Email</div>
                            <div class="profile-field-value"><?= htmlspecialchars($profile['email'] ?? '-') ?></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="profile-field">
                            <div class="profile-field-label">Branch</div>
                            <div class="profile-field-value"><?= htmlspecialchars($profile['branchName'] ?? '-') ?></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="profile-field">
                            <div class="profile-field-label">Status</div>
                            <div class="profile-field-value"><?= htmlspecialchars($profile['status'] ?? '-') ?></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="profile-field">
                            <div class="profile-field-label">Date Created</div>
                            <div class="profile-field-value"><?= htmlspecialchars($dateCreatedLabel) ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    </div>
    <!-- End of Main Content -->

</div>
<!-- End of Content Wrapper -->

</div>
<!-- End of Page Wrapper -->

<div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="logoutModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="logoutModalLabel">Ready to Leave?</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">x</span>
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
</body>
</html>
