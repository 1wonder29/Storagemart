<?php
$base = rtrim(BASE_URL, '/');
$categories = $categories ?? [];
$totalCategories = count($categories);
$totalGroups = (int) ($totalGroups ?? 0);
?>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Storage Mart | Add Group</title>
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
        $assetSubPage = 'add-group';
        require_once __DIR__ . '/../../partials/admin/sidebar_topbar.php';
        ?>

        <div class="container-fluid admin-assets-page">

            <div class="page-hero hero-form">
                <div class="row align-items-center">
                    <div class="col-lg-7">
                        <h1><i class="fas fa-layer-group mr-2"></i>Add Group</h1>
                        <p>Create a new asset group by selecting a category and defining the model details.</p>
                        <div class="quick-nav mt-3">
                            <a href="<?= htmlspecialchars($base) ?>/admin/assets" class="btn btn-sm btn-outline-light">
                                <i class="fas fa-arrow-left mr-1"></i> Back to Directory
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-5">
                        <div class="hero-stat-row mt-3 mt-lg-0">
                            <div class="hero-stat hero-stat-inline">
                                <div class="stat-value"><?= (int) $totalGroups ?></div>
                                <div class="stat-label">Groups</div>
                            </div>
                            <div class="hero-stat hero-stat-inline">
                                <div class="stat-value"><?= (int) $totalCategories ?></div>
                                <div class="stat-label">Categories</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card form-card shadow mb-4">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">Group Asset Details</h6>
                </div>
                <div class="card-body">
                    <form action="<?= htmlspecialchars($base) ?>/admin/assets/group/add" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                        <div class="form-section-title">Group Information</div>

                        <div class="row form-row-gap">
                            <div class="col-md-6">
                                <label for="category_id" class="form-label">Item Category <span class="text-danger">*</span></label>
                                <select name="category_id" id="category_id" class="form-control" required>
                                    <option value="">-- Select Category --</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option
                                            value="<?= (int) ($category['category_id'] ?? 0) ?>"
                                            data-ic_code="<?= htmlspecialchars((string) ($category['ic_code'] ?? '')) ?>">
                                            <?= htmlspecialchars((string) ($category['categoryName'] ?? '')) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="ic_code" class="form-label">IC Code</label>
                                <input type="text" name="ic_code" class="form-control" id="ic_code" placeholder="IC Code" readonly>
                            </div>
                        </div>

                        <div class="row form-row-gap">
                            <div class="col-md-6">
                                <label for="groupName" class="form-label">Group Asset Name <span class="text-danger">*</span></label>
                                <input type="text" name="groupName" class="form-control" id="groupName" placeholder="e.g. Lenovo ThinkPad" required>
                            </div>
                            <div class="col-md-6">
                                <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
                                <textarea id="description" name="description" class="form-control" rows="5" maxlength="1000" required placeholder="Model details, specs, or notes"></textarea>
                                <small class="form-text text-muted">Maximum 1000 characters.</small>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary" name="btnSubmit">
                                <i class="fas fa-save mr-1"></i> Save Group
                            </button>
                            <a href="<?= htmlspecialchars($base) ?>/admin/assets" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
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
    <script src="<?= htmlspecialchars($base) ?>/assets/js/asset/add_group.js"></script>
    <?php require __DIR__ . '/../../partials/flash_modal.php'; ?>
</body>

</html>
