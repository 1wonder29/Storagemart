<?php
$base = rtrim(BASE_URL, '/');
$groups = $groups ?? [];
$totalGroups = (int) ($totalGroups ?? count($groups));
$totalItems = (int) ($totalItems ?? 0);
?>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Storage Mart | Add Item</title>
    <link href="<?= htmlspecialchars($base) ?>/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/storagemart.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/admin-assets.css" rel="stylesheet">
    <link rel="icon" href="<?= htmlspecialchars($base) ?>/assets/img/favicon.ico" type="image/x-icon">
</head>

<body id="page-top">

    <div id="wrapper">
        <?php
        $activePage = 'assets';
        $assetSubPage = 'add-item';
        require_once __DIR__ . '/../../partials/admin/sidebar_topbar.php';
        ?>

        <div class="container-fluid admin-assets-page">

            <div class="page-hero hero-form">
                <div class="row align-items-center">
                    <div class="col-lg-7">
                        <h1><i class="fas fa-plus-circle mr-2"></i>Add Item</h1>
                        <p>Register a new physical asset item and assign it to an existing asset group.</p>
                        <div class="quick-nav mt-3">
                            <a href="<?= htmlspecialchars($base) ?>/admin/assets" class="btn btn-sm btn-outline-light">
                                <i class="fas fa-arrow-left mr-1"></i> Back to Directory
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-5">
                        <div class="hero-stat-row mt-3 mt-lg-0">
                            <div class="hero-stat hero-stat-inline">
                                <div class="stat-value"><?= $totalGroups ?></div>
                                <div class="stat-label">Groups</div>
                            </div>
                            <div class="hero-stat hero-stat-inline">
                                <div class="stat-value"><?= $totalItems ?></div>
                                <div class="stat-label">Items</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card form-card shadow mb-4">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">Asset Item Details</h6>
                </div>
                <div class="card-body">
                    <form action="<?= htmlspecialchars($base) ?>/admin/assets/add" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                        <div class="form-section-title">Item Information</div>

                        <div class="row form-row-gap">
                            <div class="col-md-6">
                                <label for="group_id" class="form-label">Asset Group <span class="text-danger">*</span></label>
                                <select id="group_id" name="group_id" class="form-control" required>
                                    <option value="">-- Select a Group --</option>
                                    <?php foreach ($groups as $group): ?>
                                        <option value="<?= (int) ($group['group_id'] ?? 0) ?>">
                                            <?= htmlspecialchars((string) ($group['groupName'] ?? '')) ?>
                                            (<?= htmlspecialchars((string) ($group['categoryName'] ?? '')) ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="row form-row-gap">
                            <div class="col-md-6">
                                <label for="itemInfo" class="form-label">Item General Info <span class="text-danger">*</span></label>
                                <textarea id="itemInfo" name="itemInfo" class="form-control" rows="5" maxlength="1000" required placeholder="Describe the item (e.g. Laptop, Monitor)"></textarea>
                                <small class="form-text text-muted">Maximum 1000 characters.</small>
                            </div>
                            <div class="col-md-6">
                                <label for="serialNumber" class="form-label">Serial Number <span class="text-danger">*</span></label>
                                <input type="text" name="serialNumber" class="form-control" id="serialNumber" placeholder="Serial Number" required>
                                <label for="year_purchased" class="form-label mt-3">Year Purchased <span class="text-danger">*</span></label>
                                <input type="text" name="year_purchased" class="form-control" id="year_purchased" placeholder="e.g. 2024" required>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary" name="btnSubmit">
                                <i class="fas fa-save mr-1"></i> Save Item
                            </button>
                            <a href="<?= htmlspecialchars($base) ?>/admin/assets" class="btn btn-outline-secondary">Cancel</a>
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
