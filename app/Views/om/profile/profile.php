<?php
$base = rtrim(BASE_URL, '/');
$routePrefix = $routePrefix ?? (($user_role ?? '') === 'HOM' ? 'hom' : 'om');
$fullName = trim(
    ($profile['firstname'] ?? '') . ' ' .
    ($profile['middlename'] ?? '') . ' ' .
    ($profile['lastname'] ?? '')
);
$fullName = preg_replace('/\s+/', ' ', $fullName);
$displayName = $fullName !== '' ? $fullName : trim(($user['firstname'] ?? '') . ' ' . ($user['lastname'] ?? ''));
$roleLabel = strtoupper($profile['usertype'] ?? $user_role ?? 'Operations');
$dateCreated = $profile['account_datecreated'] ?? $profile['employee_datecreated'] ?? null;
$dateCreatedLabel = $dateCreated ? date('M d, Y', strtotime($dateCreated)) : '-';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Storage Mart | Profile</title>

    <link href="<?= htmlspecialchars($base) ?>/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900" rel="stylesheet">
    <link rel="icon" href="<?= htmlspecialchars($base) ?>/assets/img/sm_favicon.png" type="image/x-icon">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/storagemart.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/profile-page.css" rel="stylesheet">
</head>
<body id="page-top">

<div id="wrapper">
    <?php
    $activePage = 'profile';
    require_once __DIR__ . '/../../partials/om/sidebar_topbar.php';
    ?>

    <div class="container-fluid profile-page theme-om">

        <div class="profile-hero">
            <div class="d-flex align-items-center flex-wrap">
                <div class="profile-avatar mr-3 mb-3 mb-md-0">
                    <i class="fas fa-user-tie"></i>
                </div>
                <div>
                    <h1><?= htmlspecialchars($displayName !== '' ? $displayName : 'User Profile') ?></h1>
                    <div class="profile-role"><?= htmlspecialchars($profile['position'] ?? '-') ?></div>
                    <?php if (!empty($profile['status'])): ?>
                        <span class="profile-badge"><?= htmlspecialchars(ucfirst((string) $profile['status'])) ?></span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="card profile-card shadow mb-4">
            <div class="card-header">
                <h6><i class="fas fa-id-card"></i> Account Information</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="profile-field">
                            <div class="profile-field-label">Full Name</div>
                            <div class="profile-field-value"><?= htmlspecialchars($displayName !== '' ? $displayName : '-') ?></div>
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
                            <div class="profile-field-value"><?= htmlspecialchars((string) ($profile['employee_id'] ?? '-')) ?></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="profile-field">
                            <div class="profile-field-label">User Type</div>
                            <div class="profile-field-value"><?= htmlspecialchars($roleLabel) ?></div>
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
                            <div class="profile-field-value"><?= htmlspecialchars(ucfirst((string) ($profile['status'] ?? '-'))) ?></div>
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

<a class="scroll-to-top rounded" href="#page-top">
    <i class="fas fa-angle-up"></i>
</a>

<script src="<?= htmlspecialchars($base) ?>/assets/vendor/jquery/jquery.min.js"></script>
<script src="<?= htmlspecialchars($base) ?>/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="<?= htmlspecialchars($base) ?>/assets/vendor/jquery-easing/jquery.easing.min.js"></script>
<script src="<?= htmlspecialchars($base) ?>/assets/js/sb-admin-2.min.js"></script>
</body>
</html>
