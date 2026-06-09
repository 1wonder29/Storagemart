<?php
$base = rtrim(BASE_URL, '/');
require_once __DIR__ . '/../../partials/it/asset_view_helpers.php';

$totalAssets = count($assets);
$deviceTypes = [];
$laptopCount = 0;
$desktopCount = 0;

foreach ($assets as $asset) {
    $group = trim((string) ($asset['groupName'] ?? ''));
    if ($group !== '') {
        $deviceTypes[$group] = true;
    }
    $meta = it_asset_device_meta($group);
    if ($meta[0] === 'laptop') {
        $laptopCount++;
    }
    if ($meta[0] === 'desktop') {
        $desktopCount++;
    }
}

ksort($deviceTypes);
?>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Storage Mart | My Assets</title>

    <link href="<?= htmlspecialchars($base) ?>/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/storagemart.css" rel="stylesheet">
    <?php require_once __DIR__ . '/../../partials/it/theme_head.php'; ?>
    <link rel="icon" href="<?= htmlspecialchars($base) ?>/assets/img/favicon.png" type="image/png">
    <link href="<?= htmlspecialchars($base) ?>/assets/vendor/datatables/datatables.min.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/it-assets.css" rel="stylesheet">
</head>

<body id="page-top">

    <div id="wrapper">
        <?php
        $activePage = 'assets';
        require_once __DIR__ . '/../../partials/it/sidebar_topbar.php';
        ?>

        <div class="container-fluid it-assets-page">

            <div class="page-hero hero-assets">
                <div class="row align-items-center">
                    <div class="col-lg-7">
                        <h1><i class="fas fa-laptop mr-2"></i>My Assets</h1>
                        <p>Equipment assigned to you — view details and file a support ticket when something needs attention.</p>
                        <div class="quick-nav mt-3">
                            <a href="<?= htmlspecialchars($base) ?>/it/tickets/in_progress" class="btn btn-sm btn-outline-light mr-1">
                                <i class="fas fa-spinner mr-1"></i> In Progress
                            </a>
                            <a href="<?= htmlspecialchars($base) ?>/it/tickets" class="btn btn-sm btn-outline-light mr-1">
                                <i class="fas fa-ticket-alt mr-1"></i> My Tickets
                            </a>
                            <a href="<?= htmlspecialchars($base) ?>/it/uploads" class="btn btn-sm btn-outline-light">
                                <i class="fas fa-file-alt mr-1"></i> Reports
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-5">
                        <div class="row mt-3 mt-lg-0">
                            <div class="col-4">
                                <div class="hero-stat">
                                    <div class="stat-value"><?= (int) $totalAssets ?></div>
                                    <div class="stat-label">Assigned</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="hero-stat">
                                    <div class="stat-value"><?= (int) $laptopCount ?></div>
                                    <div class="stat-label">Laptops</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="hero-stat">
                                    <div class="stat-value"><?= (int) $desktopCount ?></div>
                                    <div class="stat-label">Desktops</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php if (!empty($assets)): ?>
                <div class="filter-toolbar">
                    <div class="row align-items-end">
                        <div class="col-md-4 col-sm-6 mb-2 mb-md-0">
                            <label for="assetTypeFilter">Device Type</label>
                            <select id="assetTypeFilter" class="form-control form-control-sm">
                                <option value="">All Device Types</option>
                                <?php foreach (array_keys($deviceTypes) as $type): ?>
                                    <option value="<?= htmlspecialchars($type) ?>"><?= htmlspecialchars($type) ?></option>
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
            <?php endif; ?>

            <div class="card asset-list-card shadow mb-4">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-archive mr-1"></i> Assigned Assets
                    </h6>
                    <span class="badge badge-primary"><?= (int) $totalAssets ?> asset<?= $totalAssets === 1 ? '' : 's' ?></span>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($assets)): ?>
                        <div class="empty-state">
                            <i class="fas fa-box-open d-block"></i>
                            <h5 class="font-weight-bold text-gray-700">No assets assigned</h5>
                            <p class="mb-0">Equipment assigned to you will appear here.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0" id="assetUser" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>Asset ID</th>
                                        <th>Device</th>
                                        <th>Specifications</th>
                                        <th>Status</th>
                                        <th class="text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($assets as $row):
                                        $inventoryId = (int) ($row['inventory_id'] ?? 0);
                                        $assetNumber = (string) ($row['assetNumber'] ?? '');
                                        $groupName = (string) ($row['groupName'] ?? '');
                                        $description = (string) ($row['description'] ?? '');
                                        $itemInfo = (string) ($row['itemInfo'] ?? '');
                                        $serialNumber = (string) ($row['serialNumber'] ?? '');
                                        [$deviceClass, $deviceIcon] = it_asset_device_meta($groupName);
                                    ?>
                                        <tr>
                                            <td>
                                                <div class="asset-number"><?= htmlspecialchars($assetNumber) ?></div>
                                                <?php if ($serialNumber !== ''): ?>
                                                    <div class="serial-hint" title="<?= htmlspecialchars($serialNumber) ?>">
                                                        <i class="fas fa-barcode"></i>S/N: <?= htmlspecialchars($serialNumber) ?>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="device-type-wrap">
                                                    <div class="device-icon <?= $deviceClass ?>">
                                                        <i class="fas <?= $deviceIcon ?>"></i>
                                                    </div>
                                                    <div>
                                                        <div class="device-name"><?= htmlspecialchars($groupName ?: 'Unknown Device') ?></div>
                                                        <?php if ($description !== ''): ?>
                                                            <div class="device-desc"><?= htmlspecialchars($description) ?></div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="item-info-text">
                                                    <?= htmlspecialchars($itemInfo ?: '—') ?>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="assigned-badge">
                                                    <i class="fas fa-check-circle"></i> Assigned
                                                </span>
                                            </td>
                                            <td class="text-right">
                                                <div class="action-btn-group">
                                                    <a href="<?= htmlspecialchars($base) ?>/it/tickets/create?inventory_id=<?= $inventoryId ?>"
                                                       class="btn btn-sm btn-primary" title="File a support ticket for this asset">
                                                        <i class="fas fa-ticket-alt mr-1"></i> File Ticket
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

    <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Ready to Leave?</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
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

    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/jquery/jquery.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/js/sb-admin-2.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/datatables/datatables.min.js"></script>
    <?php if (!empty($assets)): ?>
    <script src="<?= htmlspecialchars($base) ?>/assets/js/it-my-assets.js"></script>
    <?php endif; ?>
    <?php require __DIR__ . '/../../partials/flash_modal.php'; ?>
</body>
</html>
