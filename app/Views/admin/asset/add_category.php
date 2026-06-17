<?php
$base = rtrim(BASE_URL, '/');
$categories = $categories ?? [];
$totalCategories = count($categories);
?>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Storage Mart | Add Category</title>
    <link href="<?= htmlspecialchars($base) ?>/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/storagemart.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/admin-assets.css" rel="stylesheet">
    <link rel="icon" href="<?= htmlspecialchars($base) ?>/assets/img/favicon.ico" type="image/x-icon">
    <link href="<?= htmlspecialchars($base) ?>/assets/vendor/datatables/datatables.min.css" rel="stylesheet">
</head>

<body id="page-top">

    <div id="wrapper">
        <?php
        $activePage = 'assets';
        $assetSubPage = 'add-category';
        require_once __DIR__ . '/../../partials/admin/sidebar_topbar.php';
        ?>

        <div class="container-fluid admin-assets-page">

            <div class="page-hero hero-form">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <h1><i class="fas fa-tags mr-2"></i>Add Category</h1>
                        <p>Create a new asset category with its IC code for inventory classification.</p>
                        <div class="quick-nav mt-3">
                            <a href="<?= htmlspecialchars($base) ?>/admin/assets" class="btn btn-sm btn-outline-light">
                                <i class="fas fa-arrow-left mr-1"></i> Back to Directory
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="hero-stat mt-3 mt-lg-0">
                            <div class="stat-value"><?= (int) $totalCategories ?></div>
                            <div class="stat-label">Categories</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card form-card shadow mb-4">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">Category Details</h6>
                </div>
                <div class="card-body">
                    <form action="<?= htmlspecialchars($base) ?>/admin/assets/category/add" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                        <div class="form-section-title">Category Information</div>

                        <div class="row form-row-gap">
                            <div class="col-md-6">
                                <label for="categoryName" class="form-label">Category Name <span class="text-danger">*</span></label>
                                <input type="text" name="categoryName" class="form-control" id="categoryName" placeholder="e.g. Laptop" required>
                            </div>
                            <div class="col-md-6">
                                <label for="ic_code" class="form-label">IC Code <span class="text-danger">*</span></label>
                                <input type="text" name="ic_code" class="form-control" id="ic_code" placeholder="e.g. CM" required>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary" name="btnSubmit">
                                <i class="fas fa-save mr-1"></i> Save Category
                            </button>
                            <a href="<?= htmlspecialchars($base) ?>/admin/assets" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card asset-list-card shadow mb-4">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-list-ul mr-1"></i> Category List
                    </h6>
                    <span class="badge badge-info"><?= (int) $totalCategories ?> categor<?= $totalCategories === 1 ? 'y' : 'ies' ?></span>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($categories)): ?>
                        <div class="empty-state">
                            <i class="fas fa-tags d-block"></i>
                            No categories found. Add your first category above.
                        </div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="categoryList" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>IC Code</th>
                                    <th>Category Name</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($categories as $category): ?>
                                    <tr>
                                        <td><span class="asset-number"><?= htmlspecialchars((string) ($category['ic_code'] ?? '')) ?></span></td>
                                        <td>
                                            <span class="category-pill">
                                                <i class="fas fa-tag"></i>
                                                <?= htmlspecialchars((string) ($category['categoryName'] ?? '')) ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
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
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/datatables/datatables.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/js/admin-assets-form-lists.js"></script>
    <?php require __DIR__ . '/../../partials/flash_modal.php'; ?>
</body>

</html>
