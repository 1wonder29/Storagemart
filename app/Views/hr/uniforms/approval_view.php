<?php
$base = rtrim(BASE_URL, '/');
$h = static fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
$rows = $batch['rows'] ?? [];
$isAssign = $batch['batch_type'] === 'ASSIGN';
$statusBadge = [
    'PENDING'   => ['Waiting for approval', 'warning'],
    'APPLYING'  => ['Being applied', 'info'],
    'APPROVED'  => ['Approved', 'success'],
    'REJECTED'  => ['Rejected', 'danger'],
    'CANCELLED' => ['Cancelled', 'secondary'],
    'FAILED'    => ['Could not apply', 'danger'],
];
[$statusLabel, $statusColor] = $statusBadge[$batch['status']] ?? [$batch['status'], 'secondary'];
// Stock still enough for every row of an Assign upload?
$short = [];
if ($isAssign && $batch['status'] === 'PENDING') {
    $need = [];
    foreach ($rows as $r) {
        $need[(int) $r['uniform_id']] = ($need[(int) $r['uniform_id']] ?? 0) + (int) $r['quantity'];
    }
    foreach ($need as $id => $qty) {
        $now = $stockNow[$id] ?? null;
        if (!$now || $now['stock'] < $qty || strtoupper($now['status']) !== 'ACTIVE') {
            $short[$id] = $qty;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Storage Mart | Approval #<?= (int) $batch['batch_id'] ?></title>
    <link href="<?= $h($base) ?>/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900" rel="stylesheet">
    <link rel="icon" href="<?= $h($base) ?>/assets/img/sm_favicon.png" type="image/x-icon">
    <link href="<?= $h($base) ?>/assets/css/storagemart.css" rel="stylesheet">
    <link href="<?= $h($base) ?>/assets/css/hr-dashboard.css?v=20261008" rel="stylesheet">
    <link href="<?= $h($base) ?>/assets/css/hr-uniforms.css" rel="stylesheet">
</head>
<body id="page-top">
<div id="wrapper">
    <?php
    $activePage = 'uniforms';
    require_once dirname(dirname(__DIR__)) . '/partials/uniform_sidebar_topbar.php';
    ?>
    <div class="container-fluid hr-dashboard-page hr-uniform-page">
        <div class="page-hero">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h1><i class="fas <?= $isAssign ? 'fa-users' : 'fa-boxes' ?> mr-2"></i><?= $isAssign ? 'Assign items to employees' : 'Inventory update' ?> #<?= (int) $batch['batch_id'] ?></h1>
                    <p><?= $h($batch['summary']) ?> · <?= $h($batch['filename']) ?><br>
                        Sent by <?= $h($batch['submitted_name']) ?> on <?= $h(date('M j, Y g:i A', strtotime((string) $batch['submitted_at']))) ?></p>
                </div>
                <div class="col-lg-4">
                    <div class="hero-stat mt-3 mt-lg-0">
                        <div class="stat-value" style="font-size:1.1rem"><span class="badge badge-<?= $statusColor ?>"><?= $h($statusLabel) ?></span></div>
                        <div class="stat-label"><?= count($rows) ?> row(s)</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="quick-actions">
            <a href="<?= $h($base) ?>/hr/uniforms/approvals" class="quick-action-btn qa-secondary"><i class="fas fa-arrow-left"></i> All approvals</a>
        </div>

        <?php foreach (['successMessage' => 'success', 'errorMessage' => 'danger'] as $key => $type): ?>
            <?php if (!empty($_SESSION[$key])): ?>
                <div class="alert alert-<?= $type ?> alert-modern"><?= $h($_SESSION[$key]) ?></div>
                <?php unset($_SESSION[$key]); ?>
            <?php endif; ?>
        <?php endforeach; ?>

        <?php if ($batch['status'] !== 'PENDING'): ?>
            <div class="alert alert-<?= $statusColor === 'warning' ? 'info' : $statusColor ?>">
                <strong><?= $h($statusLabel) ?></strong>
                <?= $batch['decided_name'] !== '' ? ' by ' . $h($batch['decided_name']) : '' ?>
                <?= !empty($batch['decided_at']) ? ' on ' . $h(date('M j, Y g:i A', strtotime((string) $batch['decided_at']))) : '' ?>.
                <?php if (!empty($batch['decision_note'])): ?><div>Note: <?= $h($batch['decision_note']) ?></div><?php endif; ?>
                <?php if (!empty($batch['result_note'])): ?><div>Result: <?= $h($batch['result_note']) ?></div><?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if (!$isAssign && $batch['import_mode'] === 'replace'): ?>
            <div class="alert alert-warning">
                <strong>Overwrite the list:</strong> the stock of each item becomes the number in the file.
                <?php if (!empty($toDiscontinue)): ?>
                    <?= count($toDiscontinue) ?> active item(s) are not in the file and will be <strong>discontinued</strong>:
                    <?= $h(implode(', ', array_map(static fn($i) => $i['uniform_type'] . ' (' . $i['size'] . ')', array_slice($toDiscontinue, 0, 20)))) ?><?= count($toDiscontinue) > 20 ? '…' : '' ?>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        <?php if ($short): ?>
            <div class="alert alert-danger"><i class="fas fa-exclamation-triangle mr-1"></i>
                There is no longer enough stock for some items (marked in red). Approving would fail; reject it so the sender can fix the file.</div>
        <?php endif; ?>

        <div class="card shadow uniform-card data-card mb-4">
            <div class="card-header py-3"><h6 class="mb-0"><i class="fas fa-list"></i> What will change</h6></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0 uniforms-table">
                        <?php if ($isAssign): ?>
                            <thead><tr><th>Row</th><th>Employee</th><th>Item</th><th>Qty</th><th>Condition</th><th>Remarks</th><th>Stock now</th></tr></thead>
                            <tbody>
                            <?php foreach ($rows as $r): $now = $stockNow[(int) $r['uniform_id']] ?? null; $bad = isset($short[(int) $r['uniform_id']]); ?>
                                <tr class="<?= $bad ? 'table-danger' : '' ?>">
                                    <td><?= (int) $r['excel_row'] ?></td>
                                    <td><strong><?= $h($r['employee_name']) ?></strong><div class="small text-muted"><?= $h($r['employee_info']) ?></div></td>
                                    <td><?= $h($r['item_label']) ?></td>
                                    <td><?= (int) $r['quantity'] ?></td>
                                    <td><?= $h(ucfirst(strtolower((string) $r['condition']))) ?></td>
                                    <td><?= $h($r['remarks'] ?: '—') ?></td>
                                    <td><?= $now ? (int) $now['stock'] : '—' ?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        <?php else: ?>
                            <thead><tr><th>Row</th><th>Action</th><th>Item</th><th>Size</th><th>Color</th><th>Stock now</th><th>Quantity</th><th>Stock after</th></tr></thead>
                            <tbody>
                            <?php foreach ($rows as $r):
                                $isNew = $r['existing_id'] === null;
                                $now = $isNew ? null : ($stockNow[(int) $r['existing_id']]['stock'] ?? (int) $r['existing_stock']);
                                $replace = $batch['import_mode'] === 'replace';
                                $after = $isNew || $replace ? (int) $r['quantity'] : (int) $now + (int) $r['quantity'];
                            ?>
                                <tr>
                                    <td><?= (int) $r['excel_row'] ?></td>
                                    <td><?= $isNew ? '<span class="badge badge-success">New item</span>' : '<span class="badge badge-info">Update</span>' ?></td>
                                    <td><?= $h($r['type']) ?></td>
                                    <td><?= $h($r['size']) ?></td>
                                    <td><?= $h($r['color'] ?: '—') ?></td>
                                    <td><?= $isNew ? '—' : (int) $now ?></td>
                                    <td><?= $replace || $isNew ? '' : '+' ?><?= (int) $r['quantity'] ?></td>
                                    <td class="font-weight-bold"><?= $batch['status'] === 'PENDING' ? (int) $after : '—' ?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        <?php endif; ?>
                    </table>
                </div>
            </div>
        </div>

        <?php if ($batch['status'] === 'PENDING'): ?>
        <div class="card shadow uniform-card data-card mb-4">
            <div class="card-header py-3"><h6 class="mb-0"><i class="fas fa-gavel"></i> Decision</h6></div>
            <div class="card-body">
                <?php if ($canDecide): ?>
                    <form method="POST" action="<?= $h($base) ?>/hr/uniforms/approvals/decide">
                        <input type="hidden" name="csrf_token" value="<?= $h($csrf_token ?? '') ?>">
                        <input type="hidden" name="batch_id" value="<?= (int) $batch['batch_id'] ?>">
                        <div class="form-group">
                            <label for="note" class="font-weight-bold">Note <small class="text-muted">(required when you reject)</small></label>
                            <input type="text" class="form-control" id="note" name="note" maxlength="255" placeholder="e.g. Checked against the delivery receipt">
                        </div>
                        <button type="submit" name="decision" value="approve" class="btn btn-success mr-2" <?= $short ? 'disabled' : '' ?>
                                onclick="return confirm('<?= $isAssign ? 'Issue these items to the employees now?' : 'Apply this inventory update now?' ?>');">
                            <i class="fas fa-check mr-1"></i> Approve<?= $isAssign ? ' and issue' : ' and update stock' ?></button>
                        <button type="submit" name="decision" value="reject" class="btn btn-outline-danger">
                            <i class="fas fa-times mr-1"></i> Reject</button>
                    </form>
                <?php elseif ($isOwner): ?>
                    <p class="mb-2">You sent this upload, so another HR account or the Admin must approve it. They were notified.</p>
                    <form method="POST" action="<?= $h($base) ?>/hr/uniforms/approvals/decide" onsubmit="return confirm('Cancel this upload? Nothing will be changed.');">
                        <input type="hidden" name="csrf_token" value="<?= $h($csrf_token ?? '') ?>">
                        <input type="hidden" name="batch_id" value="<?= (int) $batch['batch_id'] ?>">
                        <button type="submit" name="decision" value="cancel" class="btn btn-outline-secondary"><i class="fas fa-ban mr-1"></i> Cancel my upload</button>
                    </form>
                <?php else: ?>
                    <p class="mb-0 text-muted">Waiting for approval.</p>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
</div>
<script src="<?= $h($base) ?>/assets/vendor/jquery/jquery.min.js"></script>
<script src="<?= $h($base) ?>/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="<?= $h($base) ?>/assets/js/storagemart.min.js"></script>
</body>
</html>
