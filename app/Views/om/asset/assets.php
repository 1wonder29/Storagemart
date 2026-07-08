<?php
$base = rtrim(BASE_URL, '/');
$routePrefix = $routePrefix ?? (($user_role ?? '') === 'HOM' ? 'hom' : 'om');
$myAssets = $myAssets ?? [];
$teamAssets = $teamAssets ?? [];
$branches = $branches ?? [];
$teamEmptyMessage = $teamEmptyMessage ?? 'No assets found for Operations employees.';

$myAssetCount = count($myAssets);
$teamAssetCount = count($teamAssets);
$teamEmployeeIds = [];
$teamBranchIds = [];
foreach ($teamAssets as $row) {
    $eid = (int) ($row['employee_id'] ?? 0);
    $bid = (int) ($row['branch_id'] ?? 0);
    if ($eid > 0) {
        $teamEmployeeIds[$eid] = true;
    }
    if ($bid > 0) {
        $teamBranchIds[$bid] = true;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Storage Mart | Operations Assets</title>

    <link href="<?= htmlspecialchars($base) ?>/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900" rel="stylesheet">
    <link rel="icon" href="<?= htmlspecialchars($base) ?>/assets/img/sm_favicon.png" type="image/x-icon">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/storagemart.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/om-dashboard.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/admin-assets.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/vendor/datatables/datatables.min.css" rel="stylesheet">
</head>
<body id="page-top">

<div id="wrapper">
    <?php
    $activePage = 'assets';
    require_once __DIR__ . '/../../partials/om/sidebar_topbar.php';
    ?>

    <div class="container-fluid om-dashboard-page admin-assets-page">

        <div class="page-hero hero-inventory">
            <div class="row align-items-center">
                <div class="col-lg-7">
                    <h1><i class="fas fa-archive mr-2"></i>Operations Assets</h1>
                    <p>View your assigned assets and assets held by Operations staff.</p>
                </div>
                <div class="col-lg-5 mt-3 mt-lg-0">
                    <div class="row">
                        <div class="col-4">
                            <div class="hero-stat">
                                <div class="stat-value"><?= (int) $myAssetCount ?></div>
                                <div class="stat-label">My Assets</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="hero-stat">
                                <div class="stat-value"><?= (int) $teamAssetCount ?></div>
                                <div class="stat-label">Employee Assets</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="hero-stat">
                                <div class="stat-value"><?= count($teamEmployeeIds) ?></div>
                                <div class="stat-label">Employees</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php require __DIR__ . '/../../partials/asset/operations_asset_lists.php'; ?>

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
<script src="<?= htmlspecialchars($base) ?>/assets/js/operations-assets.js"></script>

<?php require __DIR__ . '/../../partials/flash_modal.php'; ?>
</body>
</html>
