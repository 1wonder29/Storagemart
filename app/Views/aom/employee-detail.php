<?php
$base = rtrim(BASE_URL, '/');
$loggedFirstname = $ctx['loggedFirstname'] ?? 'AOM';
$loggedLastname = $ctx['loggedLastname'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Storage Mart | Employee Details</title>

    <link href="<?= htmlspecialchars($base) ?>/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900" rel="stylesheet">
    <link rel="icon" href="<?= htmlspecialchars($base) ?>/assets/img/sm_favicon.png" type="image/x-icon">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/storagemart.css" rel="stylesheet">
</head>

<body id="page-top">
<div id="wrapper">
<?php
$activePage = 'employees';
require_once __DIR__ . '/../partials/aom/sidebar_topbar.php';
?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Employee Details</h1>
        <a href="<?= htmlspecialchars($base) ?>/aom/employees" class="btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Back
        </a>
    </div>

    <?php if (empty($employee)): ?>
        <div class="alert alert-warning">Employee not found.</div>
    <?php else: ?>
        <div class="card shadow mb-4">
            <div class="card-header py-3 bg-primary">
                <h6 class="m-0 font-weight-bold text-white">
                    <i class="fas fa-user"></i>
                    <?= htmlspecialchars(($employee['firstname'] ?? '') . ' ' . ($employee['lastname'] ?? '')) ?>
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="small text-gray-500 text-uppercase font-weight-bold">Employee ID</div>
                        <div class="h6 mb-0"><?= htmlspecialchars($employee['employee_id'] ?? '') ?></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="small text-gray-500 text-uppercase font-weight-bold">Email</div>
                        <div class="h6 mb-0"><?= htmlspecialchars($employee['email'] ?? '') ?></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="small text-gray-500 text-uppercase font-weight-bold">Position</div>
                        <div class="h6 mb-0"><?= htmlspecialchars($employee['position'] ?? '-') ?></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="small text-gray-500 text-uppercase font-weight-bold">Department</div>
                        <div class="h6 mb-0"><?= htmlspecialchars($employee['department'] ?? '-') ?></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="small text-gray-500 text-uppercase font-weight-bold">Branch ID</div>
                        <div class="h6 mb-0"><?= htmlspecialchars($employee['branch_id'] ?? '-') ?></div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

</div>
</div>

<script src="<?= htmlspecialchars($base) ?>/assets/vendor/jquery/jquery.min.js"></script>
<script src="<?= htmlspecialchars($base) ?>/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="<?= htmlspecialchars($base) ?>/assets/js/sb-admin-2.min.js"></script>
</body>
</html>

