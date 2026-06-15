<?php
$base = rtrim(BASE_URL, '/');

$totalItems = count($items);
$branches = [];
$statuses = [];
$assignedCount = 0;

foreach ($items as $row) {
    $bn = trim((string) ($row['branchName'] ?? ''));
    if ($bn !== '') {
        $branches[$bn] = true;
    }
    $st = trim((string) ($row['status'] ?? ''));
    if ($st !== '') {
        $statuses[$st] = true;
    }
    if (!empty($row['employeeName'])) {
        $assignedCount++;
    }
}
ksort($branches);
ksort($statuses);

function admin_asset_status_class(string $status): string
{
    $s = strtolower(trim($status));
    if (strpos($s, 'assign') !== false) {
        return 'status-assigned';
    }
    if (strpos($s, 'unassign') !== false || strpos($s, 'available') !== false) {
        return 'status-unassigned';
    }
    if (strpos($s, 'defect') !== false) {
        return 'status-defective';
    }
    return 'status-default';
}
?>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Storage Mart | Asset Inventory</title>
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

            <div class="page-hero hero-inventory">
                <div class="row align-items-center">
                    <div class="col-lg-7">
                        <h1><i class="fas fa-box-open mr-2"></i>Asset Inventory</h1>
                        <p>Individual items in this group — serial numbers, assignments, transfers, and status.</p>
                        <div class="quick-nav mt-3">
                            <a href="<?= htmlspecialchars($base) ?>/admin/assets" class="btn btn-sm btn-outline-light">
                                <i class="fas fa-arrow-left mr-1"></i> Back to Directory
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-5">
                        <div class="row mt-3 mt-lg-0">
                            <div class="col-4">
                                <div class="hero-stat">
                                    <div class="stat-value"><?= (int) $totalItems ?></div>
                                    <div class="stat-label">Items</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="hero-stat">
                                    <div class="stat-value"><?= (int) $assignedCount ?></div>
                                    <div class="stat-label">Assigned</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="hero-stat">
                                    <div class="stat-value"><?= count($branches) ?></div>
                                    <div class="stat-label">Branches</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="filter-toolbar">
                <div class="row align-items-end">
                    <div class="col-md-4 col-sm-6 mb-2 mb-md-0">
                        <label for="invBranchFilter">Branch</label>
                        <select id="invBranchFilter" class="form-control form-control-sm">
                            <option value="">All Branches</option>
                            <?php foreach (array_keys($branches) as $branch): ?>
                                <option value="<?= htmlspecialchars($branch) ?>"><?= htmlspecialchars($branch) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4 col-sm-6 mb-2 mb-md-0">
                        <label for="invStatusFilter">Status</label>
                        <select id="invStatusFilter" class="form-control form-control-sm">
                            <option value="">All Statuses</option>
                            <?php foreach (array_keys($statuses) as $status): ?>
                                <option value="<?= htmlspecialchars($status) ?>"><?= htmlspecialchars($status) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4 col-sm-6 text-md-right">
                        <button type="button" id="invClearFilters" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-undo mr-1"></i> Clear Filters
                        </button>
                    </div>
                </div>
            </div>

            <div class="card asset-list-card shadow mb-4">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-list-ul mr-1"></i> Item List
                    </h6>
                    <span class="badge badge-info"><?= (int) $totalItems ?> item<?= $totalItems === 1 ? '' : 's' ?></span>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($items)): ?>
                        <div class="empty-state">
                            <i class="fas fa-inbox d-block"></i>
                            No items in this group.
                        </div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="asset_inventory" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Item Info</th>
                                    <th>Asset #</th>
                                    <th>Serial</th>
                                    <th>Branch</th>
                                    <th>Status</th>
                                    <th>Employee</th>
                                    <th>Transfer</th>
                                    <th class="text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($items as $row):
                                    $status = (string) ($row['status'] ?? '');
                                    $branch = (string) ($row['branchName'] ?? '');
                                ?>
                                    <tr data-branch="<?= htmlspecialchars(strtolower(trim($branch))) ?>"
                                        data-status="<?= htmlspecialchars(strtolower(trim($status))) ?>">
                                        <td>
                                            <div class="desc-text" style="max-width:none;"><?= htmlspecialchars((string) ($row['itemInfo'] ?? '')) ?></div>
                                        </td>
                                        <td><span class="asset-number"><?= htmlspecialchars((string) ($row['assetNumber'] ?? '')) ?></span></td>
                                        <td><span class="desc-text" style="max-width:none;"><?= htmlspecialchars((string) ($row['serialNumber'] ?? '')) ?></span></td>
                                        <td>
                                            <?php if ($branch !== ''): ?>
                                                <span class="branch-pill">
                                                    <i class="fas fa-map-marker-alt"></i>
                                                    <?= htmlspecialchars($branch) ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted">—</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($status !== ''): ?>
                                                <span class="status-badge <?= admin_asset_status_class($status) ?>">
                                                    <?= htmlspecialchars($status) ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted">—</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars((string) ($row['employeeName'] ?? '—')) ?></td>
                                        <td>
                                            <div class="desc-text" style="max-width:180px;"><?= htmlspecialchars((string) ($row['transferDetails'] ?? '—')) ?></div>
                                        </td>
                                        <td class="text-right">
                                            <div class="action-btn-group">
                                                <a href="<?= htmlspecialchars($base) ?>/admin/assets/item/edit?inventory_id=<?= (int) ($row['inventory_id'] ?? 0) ?>"
                                                   class="btn btn-sm btn-outline-secondary" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="<?= htmlspecialchars($base) ?>/admin/assets/transfer?inventory_id=<?= (int) ($row['inventory_id'] ?? 0) ?>"
                                                   class="btn btn-sm btn-outline-primary" title="Transfer">
                                                    <i class="fas fa-exchange-alt"></i>
                                                </a>
                                                <a href="<?= htmlspecialchars($base) ?>/admin/assets/transfer-history?inventory_id=<?= (int) ($row['inventory_id'] ?? 0) ?>"
                                                   class="btn btn-sm btn-outline-info" title="History">
                                                    <i class="fas fa-history"></i>
                                                </a>
                                                <a href="<?= htmlspecialchars($base) ?>/admin/assets/item/delete?inventory_id=<?= (int) ($row['inventory_id'] ?? 0) ?>"
                                                   class="btn btn-sm btn-outline-danger" title="Delete"
                                                   onclick="return confirm('Are you sure you want to delete this item?');">
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
    <script src="<?= htmlspecialchars($base) ?>/assets/js/admin-asset-inventory.js"></script>
    <?php require __DIR__ . '/../../partials/flash_modal.php'; ?>
</body>

</html>
