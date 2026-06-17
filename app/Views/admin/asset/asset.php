<?php
$base = rtrim(BASE_URL, '/');

$totalGroups = count($assets);
$totalItems = 0;
$totalAssigned = 0;
$categories = [];

foreach ($assets as $row) {
    $totalItems += (int) ($row['totalItems'] ?? 0);
    $totalAssigned += (int) ($row['assigned'] ?? 0);
    $cat = trim((string) ($row['categoryName'] ?? ''));
    if ($cat !== '') {
        $categories[$cat] = true;
    }
}
ksort($categories);
?>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Storage Mart | Assets Directory</title>
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
        $assetSubPage = 'directory';
        require_once __DIR__ . '/../../partials/admin/sidebar_topbar.php';
        ?>

        <div class="container-fluid admin-assets-page">

            <div class="page-hero hero-directory">
                <div class="row align-items-center">
                    <div class="col-lg-7">
                        <h1><i class="fas fa-archive mr-2"></i>Assets Directory</h1>
                        <p>Manage asset groups, track inventory quantities, and organize items by category.</p>
                        <div class="quick-nav mt-3">
                            <a href="<?= htmlspecialchars($base) ?>/admin/assets/add" class="btn btn-sm btn-outline-light mr-1">
                                <i class="fas fa-plus mr-1"></i> Add Item
                            </a>
                            <a href="<?= htmlspecialchars($base) ?>/admin/assets/group/add" class="btn btn-sm btn-outline-light mr-1">
                                <i class="fas fa-layer-group mr-1"></i> Add Group
                            </a>
                            <a href="<?= htmlspecialchars($base) ?>/admin/assets/defective" class="btn btn-sm btn-outline-light">
                                <i class="fas fa-exclamation-triangle mr-1"></i> Defective Items
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-5">
                        <div class="row mt-3 mt-lg-0">
                            <div class="col-3">
                                <div class="hero-stat">
                                    <div class="stat-value"><?= (int) $totalGroups ?></div>
                                    <div class="stat-label">Groups</div>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="hero-stat">
                                    <div class="stat-value"><?= (int) $totalItems ?></div>
                                    <div class="stat-label">Items</div>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="hero-stat">
                                    <div class="stat-value"><?= (int) $totalAssigned ?></div>
                                    <div class="stat-label">Assigned</div>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="hero-stat">
                                    <div class="stat-value"><?= (int) ($defectiveCount ?? 0) ?></div>
                                    <div class="stat-label">Defective</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="filter-toolbar">
                <div class="row align-items-end">
                    <div class="col-md-4 col-sm-6 mb-2 mb-md-0">
                        <label for="assetCategoryFilter">Category</label>
                        <select id="assetCategoryFilter" class="form-control form-control-sm">
                            <option value="">All Categories</option>
                            <?php foreach (array_keys($categories) as $cat): ?>
                                <option value="<?= htmlspecialchars($cat) ?>"><?= htmlspecialchars($cat) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-8 col-sm-6 text-md-right">
                        <button type="button" id="assetClearFilters" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-undo mr-1"></i> Clear Filters
                        </button>
                    </div>
                </div>
            </div>

            <div class="card asset-list-card shadow mb-4">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-boxes mr-1"></i> Asset Groups
                    </h6>
                    <div class="card-header-actions">
                        <span class="badge badge-info"><?= (int) $totalGroups ?> group<?= $totalGroups === 1 ? '' : 's' ?></span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($assets)): ?>
                        <div class="empty-state">
                            <i class="fas fa-box-open d-block"></i>
                            No asset groups found.
                        </div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="asset" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Model</th>
                                    <th>Description</th>
                                    <th>Category</th>
                                    <th>Total</th>
                                    <th>Assigned</th>
                                    <th>Unassigned</th>
                                    <th>Defective</th>
                                    <th class="text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($assets as $row):
                                    $categoryName = (string) ($row['categoryName'] ?? '');
                                ?>
                                    <tr data-category="<?= htmlspecialchars(strtolower(trim($categoryName))) ?>">
                                        <td>
                                            <div class="model-name"><?= htmlspecialchars((string) ($row['groupName'] ?? '')) ?></div>
                                        </td>
                                        <td>
                                            <div class="desc-text"><?= htmlspecialchars((string) ($row['description'] ?? '')) ?></div>
                                        </td>
                                        <td>
                                            <?php if ($categoryName !== ''): ?>
                                                <span class="category-pill">
                                                    <i class="fas fa-tag"></i>
                                                    <?= htmlspecialchars($categoryName) ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted">—</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><span class="qty-badge"><?= (int) ($row['totalItems'] ?? 0) ?></span></td>
                                        <td><span class="qty-badge assigned"><?= (int) ($row['assigned'] ?? 0) ?></span></td>
                                        <td><span class="qty-badge unassigned"><?= (int) ($row['unassigned'] ?? 0) ?></span></td>
                                        <td><span class="qty-badge defective"><?= (int) ($row['defective'] ?? 0) ?></span></td>
                                        <td class="text-right">
                                            <div class="action-btn-group">
                                                <a href="<?= htmlspecialchars($base) ?>/admin/assets/item?group_id=<?= (int) ($row['group_id'] ?? 0) ?>"
                                                   class="btn btn-sm btn-outline-primary" title="View items">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="<?= htmlspecialchars($base) ?>/admin/assets/group/update?group_id=<?= (int) ($row['group_id'] ?? 0) ?>"
                                                   class="btn btn-sm btn-outline-secondary" title="Edit group">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="<?= htmlspecialchars($base) ?>/admin/assets/group/delete?group_id=<?= (int) ($row['group_id'] ?? 0) ?>"
                                                   class="btn btn-sm btn-outline-danger" title="Delete group"
                                                   onclick="return confirm('Are you sure you want to delete this asset group?');">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
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
    <script src="<?= htmlspecialchars($base) ?>/assets/js/admin-assets-directory.js"></script>
    <?php require __DIR__ . '/../../partials/flash_modal.php'; ?>
</body>

</html>
