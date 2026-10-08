<?php
$base = rtrim(BASE_URL, '/');
$assetCount = is_array($assets ?? null) ? count($assets) : 0;
?>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Storage Mart | Assets</title>

    <link href="<?= htmlspecialchars($base) ?>/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/storagemart.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/admin-users.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/admin-assets.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/role-list-page.css" rel="stylesheet">
    <link rel="icon" href="<?= htmlspecialchars($base) ?>/assets/img/favicon.ico" type="image/x-icon">
    <link href="<?= htmlspecialchars($base) ?>/assets/vendor/datatables/datatables.min.css" rel="stylesheet">
</head>

<body id="page-top">

<div id="wrapper">
    <?php 
    $activePage = 'users';
    $userSubPage = 'accounts';
    require_once __DIR__ . '/../../partials/admin/sidebar_topbar.php';
    ?>

    <div class="container-fluid admin-users-page admin-assets-page role-list-page">

        <div class="page-hero hero-inventory">
            <div class="row align-items-center">
                <div class="col-lg-7">
                    <h1><i class="fas fa-archive mr-2"></i>Employee Assets</h1>
                    <p>View assigned assets and generate accountability forms for this employee.</p>
                </div>
                <div class="col-lg-5 mt-3 mt-lg-0 text-lg-right">
                    <div class="hero-stat d-inline-block text-center px-4 mb-3">
                        <div class="stat-value"><?= (int) $assetCount ?></div>
                        <div class="stat-label">Assigned Assets</div>
                    </div>
                    <br>
                    <a href="<?= htmlspecialchars($base) ?>/admin/employees/accountability/<?= (int) $employee_id ?>"
                       class="btn btn-light btn-sm shadow-sm">
                        <i class="fas fa-file-word"></i> Generate Accountability Form
                    </a>
                </div>
            </div>
        </div>

        <div class="card data-card shadow mb-4">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h6><i class="fas fa-list mr-1"></i>Assigned Assets</h6>
                <span class="ticket-count-badge"><?= (int) $assetCount ?> item<?= $assetCount === 1 ? '' : 's' ?></span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="assetUser" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Asset Number</th>
                                <th>Model</th>
                                <th>Description</th>
                                <th>Item Info</th>
                                <th>Serial Number</th>
                                <th class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (!empty($assets) && is_array($assets)): ?>
                            <?php foreach ($assets as $row): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['assetNumber']); ?></td>
                                <td><?= htmlspecialchars($row['groupName']); ?></td>
                                <td><?= htmlspecialchars($row['description']); ?></td>
                                <td><?= htmlspecialchars($row['itemInfo']); ?></td>
                                <td><?= htmlspecialchars($row['serialNumber']); ?></td>
                                <td class="text-right">
                                    <button type="button"
                                            class="btn btn-sm btn-warning btn-return-asset"
                                            title="Return Asset"
                                            data-inventory-id="<?= (int) ($row['inventory_id'] ?? 0) ?>"
                                            data-asset-number="<?= htmlspecialchars((string) ($row['assetNumber'] ?? '')) ?>">
                                        <i class="fas fa-undo mr-1"></i> Return
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card data-card shadow mb-4">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h6><i class="fas fa-tshirt mr-1"></i>Issued Items (from HR)</h6>
                <span class="ticket-count-badge"><?= count($issuedItems ?? []) ?> item<?= count($issuedItems ?? []) === 1 ? '' : 's' ?></span>
            </div>
            <div class="card-body p-0">
                <?php if (empty($issuedItems)): ?>
                    <p class="text-muted p-3 mb-0">No uniforms or other items are currently issued to this employee.</p>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Size</th>
                                <th>Qty</th>
                                <th>Date Issued</th>
                                <th>Report</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($issuedItems as $item): ?>
                            <tr>
                                <td><?= htmlspecialchars((string) $item['uniform_type']) ?></td>
                                <td><?= htmlspecialchars((string) $item['size']) ?></td>
                                <td><?= (int) $item['quantity_issued'] ?></td>
                                <td><?= $item['date_issued'] ? htmlspecialchars(date('M d, Y', strtotime((string) $item['date_issued']))) : '—' ?></td>
                                <td>
                                    <?php if (!empty($item['pending_report_type'])): ?>
                                        <span class="badge badge-warning">Reported <?= htmlspecialchars(strtolower((string) $item['pending_report_type'])) ?> — pending HR</span>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if (!empty($itemReports)):
            $reportStatusLabels = ['PENDING' => ['Pending', 'warning'], 'CONFIRMED' => ['Confirmed', 'danger'], 'ITEM_OK' => ['Item OK / Active', 'success']];
        ?>
        <div class="card data-card shadow mb-4">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h6><i class="fas fa-exclamation-triangle mr-1"></i>Lost / Damaged Reports</h6>
                <?php if (!empty($canReviewItemReports)): ?>
                    <a href="<?= htmlspecialchars($base) ?>/hr/uniforms/reports" class="btn btn-sm btn-outline-primary">Review in HR</a>
                <?php endif; ?>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Reported</th>
                                <th>Item</th>
                                <th>Report</th>
                                <th>Details</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($itemReports as $r):
                                [$reportLabel, $reportColor] = ($r['source'] ?? '') === 'HR_RETURN'
                                    ? ['Recorded at return', 'secondary']
                                    : ($reportStatusLabels[$r['status']] ?? [$r['status'], 'secondary']);
                            ?>
                            <tr>
                                <td><?= htmlspecialchars(date('M d, Y', strtotime((string) $r['created_at']))) ?></td>
                                <td><?= htmlspecialchars($r['uniform_type'] . ' (' . $r['size'] . ')') ?></td>
                                <td><?= (int) $r['quantity'] ?> x <?= htmlspecialchars(ucfirst(strtolower((string) $r['report_type']))) ?></td>
                                <td>
                                    <?= htmlspecialchars((string) ($r['description'] ?? '')) ?>
                                    <?php if (!empty($r['hr_remarks'])): ?>
                                        <div class="small text-muted"><strong>HR:</strong> <?= htmlspecialchars((string) $r['hr_remarks']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td><span class="badge badge-<?= $reportColor ?>"><?= htmlspecialchars($reportLabel) ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

            </div>
        </div>
</div>

<div class="modal fade" id="returnAssetModal" tabindex="-1" role="dialog" aria-labelledby="returnAssetModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="POST" action="<?= htmlspecialchars($base) ?>/admin/assets/return">
                <div class="modal-header bg-warning text-white">
                    <h5 class="modal-title" id="returnAssetModalLabel">
                        <i class="fas fa-undo mr-1"></i> Return Asset
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                    <input type="hidden" name="employee_id" value="<?= (int) ($employee_id ?? 0) ?>">
                    <input type="hidden" name="inventory_id" id="returnInventoryId" value="">
                    <p class="mb-3">You are returning asset <strong id="returnAssetNumber"></strong> from this employee. The item will be marked as <strong>Returned</strong> and the accountability form will update automatically.</p>
                    <div class="form-group mb-0">
                        <label for="returnReason">Additional Remarks <span class="text-muted">(optional)</span></label>
                        <textarea class="form-control" id="returnReason" name="reason" rows="4"
                                  placeholder="Optional notes. System remarks are generated automatically."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-undo mr-1"></i> Return Asset
                    </button>
                </div>
            </form>
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
<script src="<?= htmlspecialchars($base) ?>/assets/js/demo/datatables-demo.js"></script>
<script>
(function ($) {
    $(document).on('click', '.btn-return-asset', function () {
        $('#returnInventoryId').val($(this).data('inventory-id'));
        $('#returnAssetNumber').text($(this).data('asset-number') || '');
        $('#returnReason').val('');
        $('#returnAssetModal').modal('show');
    });
})(jQuery);
</script>
<?php require __DIR__ . '/../../partials/flash_modal.php'; ?>
</body>
</html>
