<?php
$base = rtrim(BASE_URL, '/');
require_once dirname(__DIR__, 2) . '/partials/it/asset_view_helpers.php';

$totalAssets = count($assets ?? []);
$laptopCount = 0;

foreach ($assets ?? [] as $asset) {
    $meta = it_asset_device_meta((string) ($asset['groupName'] ?? ''));
    if ($meta[0] === 'laptop') {
        $laptopCount++;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Storage Mart | My Assets</title>

    <link href="<?= htmlspecialchars($base) ?>/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900" rel="stylesheet">
    <link rel="icon" href="<?= htmlspecialchars($base) ?>/assets/img/sm_favicon.png" type="image/x-icon">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/storagemart.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/vendor/datatables/datatables.min.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/employee-assets.css" rel="stylesheet">
</head>
<body id="page-top">

<div id="wrapper">
    <?php
    $activePage = 'assets';
    require_once dirname(__DIR__, 2) . '/partials/employee/sidebar_topbar.php';
    ?>

    <div class="container-fluid employee-assets-page">

        <div class="page-hero">
            <div class="row align-items-center">
                <div class="col-lg-7">
                    <h1><i class="fas fa-archive mr-2"></i>My Assets</h1>
                    <p>Equipment assigned to you — view details and file a support ticket when something needs attention.</p>
                    <div class="quick-nav mt-3">
                        <a href="<?= htmlspecialchars($base) ?>/employee/dashboard" class="btn btn-sm btn-outline-light mr-1">
                            <i class="fas fa-tachometer-alt mr-1"></i> Dashboard
                        </a>
                        <a href="<?= htmlspecialchars($base) ?>/employee/tickets" class="btn btn-sm btn-outline-light">
                            <i class="fas fa-ticket-alt mr-1"></i> My Tickets
                        </a>
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="row mt-3 mt-lg-0">
                        <div class="col-6">
                            <div class="hero-stat">
                                <div class="stat-value"><?= (int) $totalAssets ?></div>
                                <div class="stat-label">Assigned</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="hero-stat">
                                <div class="stat-value"><?= (int) $laptopCount ?></div>
                                <div class="stat-label">Laptops</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card asset-list-card shadow mb-4">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h6><i class="fas fa-laptop"></i>Assigned Assets</h6>
                <span class="asset-count-badge"><?= (int) $totalAssets ?> asset<?= $totalAssets === 1 ? '' : 's' ?></span>
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
                                    <th>Asset Number</th>
                                    <th>Device</th>
                                    <th>Item Info</th>
                                    <th>Serial Number</th>
                                    <th class="text-right">Action</th>
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
                                        </td>
                                        <td>
                                            <div class="device-type-wrap">
                                                <div class="device-icon <?= htmlspecialchars($deviceClass) ?>">
                                                    <i class="fas <?= htmlspecialchars($deviceIcon) ?>"></i>
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
                                            <div class="item-info-text"><?= htmlspecialchars($itemInfo ?: '—') ?></div>
                                        </td>
                                        <td>
                                            <?php if ($serialNumber !== ''): ?>
                                                <div class="serial-hint">
                                                    <i class="fas fa-barcode"></i><?= htmlspecialchars($serialNumber) ?>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-muted">—</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-right">
                                            <div class="action-btn-group">
                                                <a href="<?= htmlspecialchars($base) ?>/employee/tickets/create?inventory_id=<?= $inventoryId ?>"
                                                   class="btn btn-sm btn-file-ticket"
                                                   title="File a support ticket for this asset">
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
<script src="<?= htmlspecialchars($base) ?>/assets/js/employee-my-assets.js"></script>
<?php endif; ?>
<?php require dirname(__DIR__, 2) . '/partials/flash_modal.php'; ?>
</body>
</html>
