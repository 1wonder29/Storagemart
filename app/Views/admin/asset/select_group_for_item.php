<?php
$base = rtrim(BASE_URL, '/');
$groupCount = count($groups ?? []);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Storage Mart | Add Asset Item</title>

    <link href="<?= htmlspecialchars($base) ?>/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/storagemart.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/admin-assets.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/role-list-page.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/input.css" rel="stylesheet">
    <link rel="icon" href="<?= htmlspecialchars($base) ?>/assets/img/favicon.ico" type="image/x-icon">
</head>

<body id="page-top">

<div id="wrapper">
    <?php 
    $activePage = 'assets';
    require_once __DIR__ . '/../../partials/admin/sidebar_topbar.php';
    ?>

    <div class="container-fluid admin-assets-page role-form-page">

        <div class="page-hero hero-form">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h1><i class="fas fa-plus-circle mr-2"></i>Add Asset Item</h1>
                    <p>Select an asset group to begin adding a new inventory item.</p>
                </div>
                <div class="col-lg-4 mt-3 mt-lg-0 text-lg-right">
                    <div class="hero-stat d-inline-block text-center px-4">
                        <div class="stat-value"><?= (int) $groupCount ?></div>
                        <div class="stat-label">Asset Groups</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card form-card shadow mb-4">
            <div class="card-header">
                <h6><i class="fas fa-layer-group mr-1"></i>Asset Details</h6>
            </div>
            <div class="card-body">
                <form method="GET" action="<?= htmlspecialchars($base) ?>/admin/assets/add">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label for="group_id" class="form-label">Select Asset Group <span class="text-danger">*</span></label>
                            <select id="group_id" name="group_id" class="form-control" required onchange="this.form.submit()">
                                <option value="">-- Choose a Group --</option>
                                <?php foreach($groups as $group): ?>
                                <option value="<?= (int)$group['group_id']; ?>">
                                    <?= htmlspecialchars($group['groupName']); ?> 
                                    (<?= htmlspecialchars($group['categoryName']); ?>) 
                                    - <?= (int)$group['totalItems']; ?> items
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
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

<?php require __DIR__ . '/../../partials/flash_modal.php'; ?>   
</body>

</html>
