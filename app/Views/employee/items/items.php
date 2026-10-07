<?php
$base = rtrim(BASE_URL, '/');
$routePrefix = $routePrefix ?? 'employee';
$itemsUrl = $base . '/' . $routePrefix . '/items';
// Each role keeps its own sidebar; Admin has no personal assets page, so it links to its dashboard.
$sidebarPartial = [
    'employee' => 'employee', 'head' => 'head', 'aom' => 'aom', 'hom' => 'om', 'om' => 'om', 'admin' => 'admin',
][$routePrefix] ?? 'employee';
[$backUrl, $backLabel, $backIcon] = $routePrefix === 'admin'
    ? [$base . '/admin', 'Dashboard', 'fa-tachometer-alt']
    : [$base . '/' . $routePrefix . '/assets', 'My Assets', 'fa-archive'];
// Distinct names: the full-access admin sidebar uses $items in its own loop.
$issuedItems = $issuedItems ?? [];
$itemReports = $itemReports ?? [];
$pendingCount = 0;
foreach ($itemReports as $r) {
    if (($r['status'] ?? '') === 'PENDING') {
        $pendingCount++;
    }
}
$statusLabels = [
    'PENDING'   => ['Waiting for HR', 'warning'],
    'CONFIRMED' => ['Confirmed by HR', 'danger'],
    'ITEM_OK'   => ['Item OK / Active', 'success'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Storage Mart | My Issued Items</title>

    <link href="<?= htmlspecialchars($base) ?>/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900" rel="stylesheet">
    <link rel="icon" href="<?= htmlspecialchars($base) ?>/assets/img/sm_favicon.png" type="image/x-icon">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/storagemart.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/employee-assets.css" rel="stylesheet">
</head>
<body id="page-top">

<div id="wrapper">
    <?php
    $activePage = 'items';
    require_once dirname(__DIR__, 2) . '/partials/' . $sidebarPartial . '/sidebar_topbar.php';
    ?>

    <div class="container-fluid employee-assets-page">

        <div class="page-hero">
            <div class="row align-items-center">
                <div class="col-lg-7">
                    <h1><i class="fas fa-tshirt mr-2"></i>My Issued Items</h1>
                    <p>Uniforms and other items issued to you by HR. Report an item here if it was lost or damaged.</p>
                    <div class="quick-nav mt-3">
                        <a href="<?= htmlspecialchars($backUrl) ?>" class="btn btn-sm btn-outline-light">
                            <i class="fas <?= $backIcon ?> mr-1"></i> <?= htmlspecialchars($backLabel) ?>
                        </a>
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="row mt-3 mt-lg-0">
                        <div class="col-6">
                            <div class="hero-stat">
                                <div class="stat-value"><?= count($issuedItems) ?></div>
                                <div class="stat-label">Issued</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="hero-stat">
                                <div class="stat-value"><?= (int) $pendingCount ?></div>
                                <div class="stat-label">Pending Reports</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card asset-list-card shadow mb-4">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h6><i class="fas fa-tshirt"></i>Issued Items</h6>
                <span class="asset-count-badge"><?= count($issuedItems) ?> item<?= count($issuedItems) === 1 ? '' : 's' ?></span>
            </div>
            <div class="card-body p-0">
                <?php if (empty($issuedItems)): ?>
                    <div class="empty-state">
                        <i class="fas fa-box-open d-block"></i>
                        <h5 class="font-weight-bold text-gray-700">No items issued</h5>
                        <p class="mb-0">Items HR issues to you will appear here.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Size</th>
                                    <th>Qty</th>
                                    <th>Date Issued</th>
                                    <th class="text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($issuedItems as $row): ?>
                                    <tr>
                                        <td class="font-weight-bold"><?= htmlspecialchars((string) $row['uniform_type']) ?></td>
                                        <td><?= htmlspecialchars((string) $row['size']) ?></td>
                                        <td><?= (int) $row['quantity_issued'] ?></td>
                                        <td><?= htmlspecialchars($row['date_issued'] ? date('M j, Y', strtotime((string) $row['date_issued'])) : '—') ?></td>
                                        <td class="text-right">
                                            <?php if (!empty($row['pending_report_type'])): ?>
                                                <span class="badge badge-warning">
                                                    Reported <?= htmlspecialchars(strtolower((string) $row['pending_report_type'])) ?> — waiting for HR
                                                </span>
                                            <?php else: ?>
                                                <button type="button" class="btn btn-sm btn-outline-danger btn-report-item"
                                                        data-assignment-id="<?= (int) $row['assignment_id'] ?>"
                                                        data-item="<?= htmlspecialchars($row['uniform_type'] . ' (' . $row['size'] . ')') ?>"
                                                        data-max="<?= (int) $row['quantity_issued'] ?>">
                                                    <i class="fas fa-exclamation-triangle mr-1"></i> Report Lost / Damaged
                                                </button>
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

        <?php if (!empty($itemReports)): ?>
        <div class="card asset-list-card shadow mb-4">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h6><i class="fas fa-history"></i>My Reports</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Item</th>
                                <th>Report</th>
                                <th>Details</th>
                                <th>Status</th>
                                <th>HR Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($itemReports as $r):
                                [$label, $color] = $statusLabels[$r['status']] ?? [$r['status'], 'secondary'];
                            ?>
                                <tr>
                                    <td><?= htmlspecialchars(date('M j, Y', strtotime((string) $r['created_at']))) ?></td>
                                    <td><?= htmlspecialchars($r['uniform_type'] . ' (' . $r['size'] . ')') ?></td>
                                    <td><?= (int) $r['quantity'] ?> × <?= htmlspecialchars(ucfirst(strtolower((string) $r['report_type']))) ?></td>
                                    <td><?= htmlspecialchars((string) ($r['description'] ?? '')) ?></td>
                                    <td><span class="badge badge-<?= $color ?>"><?= htmlspecialchars($label) ?></span></td>
                                    <td><?= htmlspecialchars((string) ($r['hr_remarks'] ?? '') ?: '—') ?></td>
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

<div class="modal fade" id="reportItemModal" tabindex="-1" role="dialog" aria-labelledby="reportItemModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="POST" action="<?= htmlspecialchars($itemsUrl) ?>/report">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title" id="reportItemModalLabel"><i class="fas fa-exclamation-triangle mr-1"></i> Report Item</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                    <input type="hidden" name="assignment_id" id="reportAssignmentId" value="">
                    <p class="mb-3">Item: <strong id="reportItemName"></strong></p>
                    <div class="form-group">
                        <label class="font-weight-bold d-block">What happened? <span class="text-danger">*</span></label>
                        <div class="custom-control custom-radio custom-control-inline">
                            <input type="radio" id="reportLost" name="report_type" value="LOST" class="custom-control-input" required>
                            <label class="custom-control-label" for="reportLost">Lost</label>
                        </div>
                        <div class="custom-control custom-radio custom-control-inline">
                            <input type="radio" id="reportDamaged" name="report_type" value="DAMAGED" class="custom-control-input" required>
                            <label class="custom-control-label" for="reportDamaged">Damaged</label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="reportQuantity" class="font-weight-bold">Quantity <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="reportQuantity" name="quantity" min="1" value="1" required>
                    </div>
                    <div class="form-group mb-0">
                        <label for="reportDescription" class="font-weight-bold">Details <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="reportDescription" name="description" rows="3" maxlength="1000" required
                                  placeholder="When and how was it lost or damaged?"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning"><i class="fas fa-paper-plane mr-1"></i> Send to HR</button>
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
<script>
    $(document).on('click', '.btn-report-item', function () {
        var $btn = $(this);
        $('#reportAssignmentId').val($btn.data('assignment-id'));
        $('#reportItemName').text($btn.data('item'));
        $('#reportQuantity').attr('max', $btn.data('max')).val(1);
        $('#reportDescription').val('');
        $('input[name="report_type"]').prop('checked', false);
        $('#reportItemModal').modal('show');
    });
</script>
<?php require dirname(__DIR__, 2) . '/partials/flash_modal.php'; ?>
</body>
</html>
