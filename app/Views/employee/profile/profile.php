<?php
$base = rtrim(BASE_URL, '/');
$fullName = trim(($profile['lastname'] ?? '') . ', ' . ($profile['firstname'] ?? '') . ' ' . ($profile['middlename'] ?? ''));
?>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Storage Mart | Employee Profile</title>

    <link href="<?= htmlspecialchars($base) ?>/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/storagemart.css" rel="stylesheet">
</head>
<body id="page-top">
<div id="wrapper">
    <?php
    $activePage = 'profile';
    require_once __DIR__ . '/../../partials/employee/sidebar_topbar.php';
    ?>

    <div class="container-fluid">
        <h1 class="h3 mb-3 text-gray-800">My Profile</h1>

        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Account Information</h6>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6"><strong>Full Name:</strong> <?= htmlspecialchars($fullName !== ',' ? trim($fullName) : '-') ?></div>
                    <div class="col-md-6"><strong>Username:</strong> <?= htmlspecialchars($profile['username'] ?? '-') ?></div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6"><strong>Employee ID:</strong> <?= htmlspecialchars((string)($profile['employee_id'] ?? '-')) ?></div>
                    <div class="col-md-6"><strong>User Type:</strong> <?= htmlspecialchars($profile['usertype'] ?? '-') ?></div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6"><strong>Department:</strong> <?= htmlspecialchars($profile['department'] ?? '-') ?></div>
                    <div class="col-md-6"><strong>Position:</strong> <?= htmlspecialchars($profile['position'] ?? '-') ?></div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6"><strong>Email:</strong> <?= htmlspecialchars($profile['email'] ?? '-') ?></div>
                    <div class="col-md-6"><strong>Branch:</strong> <?= htmlspecialchars($profile['branchName'] ?? '-') ?></div>
                </div>
                <div class="row">
                    <div class="col-md-6"><strong>Status:</strong> <?= htmlspecialchars($profile['status'] ?? '-') ?></div>
                    <div class="col-md-6"><strong>Date Created:</strong> <?= htmlspecialchars($profile['account_datecreated'] ?? '-') ?></div>
                </div>
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
