<?php
$base = rtrim(BASE_URL, '/');
$batches = $batches ?? [];
$h = static fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
$statusBadge = [
    'PENDING'   => ['Waiting for approval', 'warning'],
    'APPLYING'  => ['Being applied', 'info'],
    'APPROVED'  => ['Approved', 'success'],
    'REJECTED'  => ['Rejected', 'danger'],
    'CANCELLED' => ['Cancelled', 'secondary'],
    'FAILED'    => ['Could not apply', 'danger'],
];
$pending = array_values(array_filter($batches, static fn($b) => $b['status'] === 'PENDING'));
$done = array_values(array_filter($batches, static fn($b) => $b['status'] !== 'PENDING'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Storage Mart | Approvals</title>
    <link href="<?= $h($base) ?>/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900" rel="stylesheet">
    <link rel="icon" href="<?= $h($base) ?>/assets/img/sm-icon-32.png?v=20261016" type="image/x-icon">
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
                    <h1><i class="fas fa-clipboard-check mr-2"></i>Approvals</h1>
                    <p>Excel uploads wait here until they are approved: inventory imports and "Assign from Excel".
                        Another HR account or the Admin approves them; you cannot approve your own upload.</p>
                </div>
                <div class="col-lg-4">
                    <div class="hero-stat mt-3 mt-lg-0">
                        <div class="stat-value"><?= count($pending) ?></div>
                        <div class="stat-label">Waiting for approval</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="quick-actions">
            <a href="<?= $h($base) ?>/hr/uniforms" class="quick-action-btn qa-secondary"><i class="fas fa-arrow-left"></i> Back to Inventory</a>
            <a href="<?= $h($base) ?>/hr/uniforms/import" class="quick-action-btn qa-primary"><i class="fas fa-file-import"></i> Import from Excel</a>
            <a href="<?= $h($base) ?>/hr/uniforms/assign-import" class="quick-action-btn qa-warning"><i class="fas fa-users"></i> Assign from Excel</a>
        </div>

        <?php foreach (['successMessage' => 'success', 'errorMessage' => 'danger'] as $key => $type): ?>
            <?php if (!empty($_SESSION[$key])): ?>
                <div class="alert alert-<?= $type ?> alert-modern"><?= $h($_SESSION[$key]) ?></div>
                <?php unset($_SESSION[$key]); ?>
            <?php endif; ?>
        <?php endforeach; ?>

        <?php if (!empty($setupMissing)): ?>
            <div class="alert alert-warning"><i class="fas fa-database mr-1"></i> Import the database update (update M) first.</div>
        <?php endif; ?>

        <?php foreach ([['Waiting for approval', $pending, 'fa-hourglass-half'], ['Decided', $done, 'fa-history']] as [$title, $list, $icon]): ?>
        <div class="card shadow uniform-card data-card mb-4">
            <div class="card-header py-3"><h6 class="mb-0"><i class="fas <?= $icon ?>"></i> <?= $h($title) ?> (<?= count($list) ?>)</h6></div>
            <div class="card-body p-0">
                <?php if (!$list): ?>
                    <p class="text-muted p-3 mb-0"><?= $title === 'Waiting for approval' ? 'Nothing is waiting for approval.' : 'No decided uploads yet.' ?></p>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr><th>#</th><th>Type</th><th>What</th><th>File</th><th>Sent by</th><th>Status</th><th class="text-right">Open</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($list as $b): [$label, $color] = $statusBadge[$b['status']] ?? [$b['status'], 'secondary']; ?>
                                <tr>
                                    <td><?= (int) $b['batch_id'] ?></td>
                                    <td><?= $b['batch_type'] === 'ASSIGN' ? '<i class="fas fa-users mr-1"></i>Assign to employees' : '<i class="fas fa-boxes mr-1"></i>Inventory' ?></td>
                                    <td><?= $h($b['summary']) ?><?= (int) $b['skipped_count'] ? '<div class="small text-muted">' . (int) $b['skipped_count'] . ' row(s) with errors left out</div>' : '' ?></td>
                                    <td class="small"><?= $h($b['filename']) ?></td>
                                    <td><?= $h($b['submitted_name']) ?><div class="small text-muted"><?= $h(date('M j, Y g:i A', strtotime((string) $b['submitted_at']))) ?></div></td>
                                    <td><span class="badge badge-<?= $color ?>"><?= $h($label) ?></span>
                                        <?php if ($b['decided_name'] !== '' && $b['status'] !== 'PENDING'): ?>
                                            <div class="small text-muted">by <?= $h($b['decided_name']) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-right">
                                        <a class="btn btn-sm <?= $b['status'] === 'PENDING' && (int) $b['submitted_by'] !== (int) $me ? 'btn-primary' : 'btn-outline-primary' ?>"
                                           href="<?= $h($base) ?>/hr/uniforms/approvals/<?= (int) $b['batch_id'] ?>">
                                            <?= $b['status'] === 'PENDING' && (int) $b['submitted_by'] !== (int) $me ? 'Review' : 'View' ?></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
</div>
<script src="<?= $h($base) ?>/assets/vendor/jquery/jquery.min.js"></script>
<script src="<?= $h($base) ?>/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="<?= $h($base) ?>/assets/js/storagemart.min.js"></script>
</body>
</html>
