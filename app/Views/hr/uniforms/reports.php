<?php
$base = rtrim(BASE_URL, '/');
$reports = $reports ?? [];
$statusFilter = $statusFilter ?? 'PENDING';
$pendingItemReports = (int) ($pendingItemReports ?? 0);
$statusLabels = [
    'PENDING'   => ['Pending', 'warning'],
    'CONFIRMED' => ['Confirmed', 'danger'],
    'ITEM_OK'   => ['Item OK / Active', 'success'],
];
$filters = ['PENDING' => 'Pending', 'CONFIRMED' => 'Confirmed', 'ITEM_OK' => 'Item OK', 'ALL' => 'All'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Storage Mart | Lost / Damaged Reports</title>
    <link href="<?= htmlspecialchars($base) ?>/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900" rel="stylesheet">
    <link rel="icon" href="<?= htmlspecialchars($base) ?>/assets/img/sm_favicon.png" type="image/x-icon">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/storagemart.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/hr-dashboard.css?v=20261007" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/hr-uniforms.css" rel="stylesheet">
</head>
<body id="page-top">
    <div id="wrapper">
    <?php
    $activePage = 'item-reports';
    require_once dirname(dirname(__DIR__)) . '/partials/uniform_sidebar_topbar.php';
    ?>
        <div class="container-fluid hr-dashboard-page hr-uniform-page">
            <div class="page-hero">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <h1><i class="fas fa-exclamation-triangle mr-2"></i>Lost / Damaged Reports</h1>
                        <p>Items employees reported as lost or damaged. Confirm the report to update inventory, or mark the item OK to keep it active with the employee. The employee is notified either way.</p>
                    </div>
                    <div class="col-lg-4 mt-3 mt-lg-0">
                        <div class="hero-stat">
                            <div class="stat-value"><?= $pendingItemReports ?></div>
                            <div class="stat-label">Waiting for Review</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="quick-actions">
                <a href="<?= htmlspecialchars($base) ?>/hr/uniforms" class="quick-action-btn qa-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Inventory
                </a>
                <?php foreach ($filters as $key => $label): ?>
                    <a href="<?= htmlspecialchars($base) ?>/hr/uniforms/reports?status=<?= $key ?>"
                       class="quick-action-btn <?= $statusFilter === $key ? 'qa-primary' : 'qa-info' ?>">
                        <?= htmlspecialchars($label) ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <?php if (!empty($_SESSION['successMessage'])): ?>
                <div class="alert alert-success alert-dismissible fade show alert-modern" role="alert">
                    <i class="fas fa-check-circle mr-1"></i><?= htmlspecialchars($_SESSION['successMessage']) ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <?php unset($_SESSION['successMessage']); ?>
            <?php endif; ?>
            <?php if (!empty($_SESSION['errorMessage'])): ?>
                <div class="alert alert-danger alert-dismissible fade show alert-modern" role="alert">
                    <i class="fas fa-exclamation-circle mr-1"></i><?= htmlspecialchars($_SESSION['errorMessage']) ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <?php unset($_SESSION['errorMessage']); ?>
            <?php endif; ?>

            <div class="card shadow uniform-card data-card">
                <div class="card-header py-3">
                    <h6><i class="fas fa-list"></i> Reports (<?= count($reports) ?>)</h6>
                </div>
                <div class="card-body">
                    <?php if (empty($reports)): ?>
                        <div class="empty-state">
                            <i class="fas fa-clipboard-check"></i>
                            <p class="mb-0">No reports in this list.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0 uniforms-table">
                                <thead>
                                    <tr>
                                        <th>Reported</th>
                                        <th>Employee</th>
                                        <th>Item</th>
                                        <th>Report</th>
                                        <th>Details</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($reports as $r):
                                        [$label, $color] = $statusLabels[$r['status']] ?? [$r['status'], 'secondary'];
                                        $item = $r['uniform_type'] . ' (' . $r['size'] . ')';
                                    ?>
                                        <tr>
                                            <td><?= htmlspecialchars(date('M j, Y g:i A', strtotime((string) $r['created_at']))) ?></td>
                                            <td>
                                                <div class="font-weight-bold"><?= htmlspecialchars((string) ($r['employee_name'] ?? '')) ?></div>
                                                <small class="text-muted"><?= htmlspecialchars((string) ($r['department'] ?? '')) ?></small>
                                            </td>
                                            <td><?= htmlspecialchars($item) ?></td>
                                            <td>
                                                <span class="badge badge-<?= $r['report_type'] === 'LOST' ? 'dark' : 'danger' ?>">
                                                    <?= (int) $r['quantity'] ?> × <?= htmlspecialchars(ucfirst(strtolower((string) $r['report_type']))) ?>
                                                </span>
                                            </td>
                                            <td style="max-width:260px;">
                                                <?= htmlspecialchars((string) ($r['description'] ?? '')) ?>
                                                <?php if (!empty($r['hr_remarks'])): ?>
                                                    <div class="small text-muted mt-1"><strong>HR:</strong> <?= htmlspecialchars((string) $r['hr_remarks']) ?></div>
                                                <?php endif; ?>
                                            </td>
                                            <td><span class="badge badge-<?= $color ?>"><?= htmlspecialchars($label) ?></span></td>
                                            <td class="text-nowrap">
                                                <?php require dirname(__DIR__, 2) . '/partials/hr/item_report_actions.php'; ?>
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

    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/jquery/jquery.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/js/storagemart.min.js"></script>
    <?php require dirname(__DIR__, 2) . '/partials/hr/item_report_review_modal.php'; ?>
</body>
</html>
