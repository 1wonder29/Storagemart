<?php
$base = rtrim(BASE_URL, '/');
$branchCount = count($branches ?? []);
$categoryCount = count($categories ?? []);
$groupCount = count($groups ?? []);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Storage Mart | Branch, Category &amp; Group Lists</title>
    <link href="<?= htmlspecialchars($base) ?>/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/storagemart.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/admin-assets.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/role-list-page.css" rel="stylesheet">
    <link rel="icon" href="<?= htmlspecialchars($base) ?>/assets/img/favicon.ico" type="image/x-icon">
</head>
<body id="page-top">
<div id="wrapper">
    <?php
    $activePage = 'assets';
    require_once __DIR__ . '/../../partials/admin/sidebar_topbar.php';
    ?>
    <div class="container-fluid admin-assets-page role-form-page">

        <div class="page-hero hero-inventory">
            <div class="row align-items-center">
                <div class="col-lg-7">
                    <h1><i class="fas fa-book mr-2"></i>Branch, Category &amp; Group Lists</h1>
                    <p>Reference lists for branches, asset categories, and inventory groups.</p>
                </div>
                <div class="col-lg-5 mt-3 mt-lg-0">
                    <div class="row">
                        <div class="col-4">
                            <div class="hero-stat">
                                <div class="stat-value"><?= (int) $branchCount ?></div>
                                <div class="stat-label">Branches</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="hero-stat">
                                <div class="stat-value"><?= (int) $categoryCount ?></div>
                                <div class="stat-label">Categories</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="hero-stat">
                                <div class="stat-value"><?= (int) $groupCount ?></div>
                                <div class="stat-label">Groups</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card data-card shadow mb-4">
            <div class="card-header">
                <h6><i class="fas fa-building mr-1"></i>Branches</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-sm mb-0" width="100%" cellspacing="0">
                        <thead>
                        <tr>
                            <th>Branch ID</th>
                            <th>Branch Code</th>
                            <th>Branch Name</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach (($branches ?? []) as $row): ?>
                            <tr>
                                <td><?= htmlspecialchars((string)($row['branch_id'] ?? '')); ?></td>
                                <td><?= htmlspecialchars($row['branchCode'] ?? ''); ?></td>
                                <td><?= htmlspecialchars($row['branchName'] ?? ''); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($branches)): ?>
                            <tr><td colspan="3" class="text-center text-muted py-4">No branches found.</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card data-card shadow mb-4">
            <div class="card-header">
                <h6><i class="fas fa-tags mr-1"></i>Categories</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-sm mb-0" width="100%" cellspacing="0">
                        <thead>
                        <tr>
                            <th>Category ID</th>
                            <th>IC Code</th>
                            <th>Category Name</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach (($categories ?? []) as $row): ?>
                            <tr>
                                <td><?= htmlspecialchars((string)($row['category_id'] ?? '')); ?></td>
                                <td><?= htmlspecialchars($row['ic_code'] ?? ''); ?></td>
                                <td><?= htmlspecialchars($row['categoryName'] ?? ''); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($categories)): ?>
                            <tr><td colspan="3" class="text-center text-muted py-4">No categories found.</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card data-card shadow mb-4">
            <div class="card-header">
                <h6><i class="fas fa-layer-group mr-1"></i>Groups</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-sm mb-0" width="100%" cellspacing="0">
                        <thead>
                        <tr>
                            <th>Group ID</th>
                            <th>Group Name</th>
                            <th>Description</th>
                            <th>Category</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach (($groups ?? []) as $row): ?>
                            <tr>
                                <td><?= htmlspecialchars((string)($row['group_id'] ?? '')); ?></td>
                                <td><?= htmlspecialchars($row['groupName'] ?? ''); ?></td>
                                <td><?= htmlspecialchars($row['description'] ?? ''); ?></td>
                                <td><?= htmlspecialchars($row['categoryName'] ?? ''); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($groups)): ?>
                            <tr><td colspan="4" class="text-center text-muted py-4">No groups found.</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>

<a class="scroll-to-top rounded" href="#page-top">
    <i class="fas fa-angle-up"></i>
</a>

<div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Ready to Leave?</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
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
<?php require __DIR__ . '/../../partials/flash_modal.php'; ?>
</body>
</html>
