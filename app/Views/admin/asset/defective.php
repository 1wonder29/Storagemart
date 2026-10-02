<?php
$base = rtrim(BASE_URL, '/');

$months = [
    1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
    5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
    9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December',
];
$yearOptions = range((int) date('Y'), (int) date('Y') - 5);
$totalItems = (int) ($totalItems ?? count($items ?? []));
?>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Storage Mart | Defective Items</title>
    <link href="<?= htmlspecialchars($base) ?>/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/storagemart.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/admin-assets.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/admin-monthly-report.css" rel="stylesheet">
    <link rel="icon" href="<?= htmlspecialchars($base) ?>/assets/img/favicon.ico" type="image/x-icon">
    <link href="<?= htmlspecialchars($base) ?>/assets/vendor/datatables/datatables.min.css" rel="stylesheet">
</head>

<body id="page-top">

    <div id="wrapper">
        <?php
        $activePage = 'assets';
        $assetSubPage = 'defective';
        require_once __DIR__ . '/../../partials/admin/sidebar_topbar.php';
        ?>

        <div class="container-fluid admin-assets-page admin-monthly-report-page">

            <div class="page-hero hero-defective">
                <h1><i class="fas fa-exclamation-triangle mr-2"></i>Defective Items</h1>
                <p>Review assets marked defective by month — filter by period, export reports, and manage item status.</p>
                <div class="quick-nav mt-3">
                    <a href="<?= htmlspecialchars($base) ?>/admin/assets" class="btn btn-sm btn-outline-light">
                        <i class="fas fa-arrow-left mr-1"></i> Back to Directory
                    </a>
                </div>
            </div>

            <div class="filter-toolbar">
                <div class="toolbar-title"><i class="fas fa-calendar-alt"></i>Select Report Period</div>
                <form method="GET" action="<?= htmlspecialchars($base) ?>/admin/assets/defective">
                    <div class="row align-items-end">
                        <div class="col-md-3 col-sm-6 mb-3 mb-md-0">
                            <label for="month">Month</label>
                            <select name="month" id="month" class="form-control">
                                <?php foreach ($months as $num => $name): ?>
                                    <option value="<?= $num ?>" <?= ($selectedMonth ?? (int) date('n')) === $num ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($name) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-3 mb-md-0">
                            <label for="year">Year</label>
                            <select name="year" id="year" class="form-control">
                                <?php foreach ($yearOptions as $y): ?>
                                    <option value="<?= $y ?>" <?= ($selectedYear ?? (int) date('Y')) === $y ? 'selected' : '' ?>>
                                        <?= $y ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 col-sm-12 text-md-right">
                            <button type="submit" class="btn btn-primary btn-view mr-2 mb-2 mb-md-0">
                                <i class="fas fa-search mr-1"></i> View Report
                            </button>
                            <a href="<?= htmlspecialchars($base) ?>/admin/assets/defective/export?month=<?= (int) ($selectedMonth ?? date('n')) ?>&year=<?= (int) ($selectedYear ?? date('Y')) ?>"
                               class="btn btn-export mb-2 mb-md-0">
                                <i class="fas fa-file-excel mr-1"></i> Download Excel
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            <div class="row summary-row">
                <div class="col-md-6 mb-3 mb-md-0">
                    <div class="summary-card summary-card-period">
                        <div class="summary-card-icon"><i class="fas fa-calendar-check"></i></div>
                        <div>
                            <span class="summary-card-label">Report Period</span>
                            <span class="summary-card-value"><?= htmlspecialchars($monthLabel ?? '') ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="summary-card summary-card-defective">
                        <div class="summary-card-icon"><i class="fas fa-exclamation-triangle"></i></div>
                        <div>
                            <span class="summary-card-label">Total Defective Items</span>
                            <span class="summary-card-value"><?= $totalItems ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card report-list-card shadow mb-4">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-tools mr-1"></i> Defective Item List
                    </h6>
                    <span class="ticket-count-badge"><?= $totalItems ?> item<?= $totalItems === 1 ? '' : 's' ?></span>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($items)): ?>
                        <div class="empty-state">
                            <i class="fas fa-check-circle d-block"></i>
                            No defective items found for this period.
                        </div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="asset_defective" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Group</th>
                                    <th>Category</th>
                                    <th>Asset #</th>
                                    <th>Serial</th>
                                    <th>Item Info</th>
                                    <th>Branch</th>
                                    <th>Reason</th>
                                    <th>Marked Defective</th>
                                    <th class="text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($items as $row):
                                    $branch = (string) ($row['branchName'] ?? '');
                                    $category = (string) ($row['categoryName'] ?? '');
                                ?>
                                    <tr>
                                        <td>
                                            <div class="model-name"><?= htmlspecialchars((string) ($row['groupName'] ?? '')) ?></div>
                                        </td>
                                        <td>
                                            <?php if ($category !== ''): ?>
                                                <span class="category-pill">
                                                    <i class="fas fa-tag"></i>
                                                    <?= htmlspecialchars($category) ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted">—</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><span class="asset-number"><?= htmlspecialchars((string) ($row['assetNumber'] ?? '')) ?></span></td>
                                        <td><span class="desc-text" style="max-width:none;"><?= htmlspecialchars((string) ($row['serialNumber'] ?? '')) ?></span></td>
                                        <td>
                                            <div class="desc-text" style="max-width:none;"><?= htmlspecialchars((string) ($row['itemInfo'] ?? '')) ?></div>
                                        </td>
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
                                            <div class="desc-text" style="max-width:180px;"><?= htmlspecialchars((string) ($row['transferDetails'] ?? '—')) ?></div>
                                        </td>
                                        <td><?= htmlspecialchars((string) ($row['markedDefectiveAt'] ?? '—')) ?></td>
                                        <td class="text-right">
                                            <div class="action-btn-group">
                                                <a href="<?= htmlspecialchars($base) ?>/admin/assets/item?group_id=<?= (int) ($row['group_id'] ?? 0) ?>"
                                                   class="btn btn-sm btn-outline-primary" title="View group">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="<?= htmlspecialchars($base) ?>/admin/assets/item/edit?inventory_id=<?= (int) ($row['inventory_id'] ?? 0) ?>"
                                                   class="btn btn-sm btn-outline-secondary" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="<?= htmlspecialchars($base) ?>/admin/assets/transfer-history?inventory_id=<?= (int) ($row['inventory_id'] ?? 0) ?>"
                                                   class="btn btn-sm btn-outline-info" title="History">
                                                    <i class="fas fa-history"></i>
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
    <script src="<?= htmlspecialchars($base) ?>/assets/js/admin-assets-defective.js"></script>
    <?php require __DIR__ . '/../../partials/flash_modal.php'; ?>
</body>

</html>
