<?php
$base = rtrim(BASE_URL, '/');
$returnedCount = count($assignments ?? []);
$conditionFilter = strtoupper(trim((string) ($conditionFilter ?? '')));
$listTitle = 'Uniform Assignments';
if ($conditionFilter === 'DAMAGED') {
    $listTitle = 'Damaged Uniform Returns';
} elseif ($conditionFilter === 'LOST') {
    $listTitle = 'Lost Uniform Returns';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Storage Mart | Uniform Assignments</title>
    <link href="<?= htmlspecialchars($base) ?>/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900" rel="stylesheet">
    <link rel="icon" href="<?= htmlspecialchars($base) ?>/assets/img/sm_favicon.png" type="image/x-icon">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/storagemart.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/hr-dashboard.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/hr-uniforms.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/role-list-page.css" rel="stylesheet">
</head>
<body id="page-top">
    <div id="wrapper">
        <?php
        $activePage = 'uniforms';
        require_once __DIR__ . '/../../partials/uniform_sidebar_topbar.php';
        ?>
        <div class="container-fluid hr-dashboard-page hr-uniform-page role-list-page">

            <div class="page-hero">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <h1><i class="fas fa-user-tag mr-2"></i><?= htmlspecialchars($listTitle) ?></h1>
                        <?php if (!empty($uniform)): ?>
                            <p>
                                <?= htmlspecialchars($uniform['uniform_type'] ?? '') ?>
                                <span class="text-white-50">(<?= htmlspecialchars($uniform['size'] ?? '') ?>)</span>
                            </p>
                        <?php else: ?>
                            <p>Review assignment and return history for this uniform.</p>
                        <?php endif; ?>
                    </div>
                    <div class="col-lg-4 mt-3 mt-lg-0 text-lg-right">
                        <div class="hero-stat d-inline-block text-center px-4 mb-3">
                            <div class="stat-value"><?= (int) $returnedCount ?></div>
                            <div class="stat-label">Records</div>
                        </div>
                        <br>
                        <a href="<?= htmlspecialchars($base) ?>/hr/uniforms" class="btn btn-light btn-sm">
                            <i class="fas fa-arrow-left"></i> Back to Uniforms
                        </a>
                    </div>
                </div>
            </div>

            <?php if (!empty($_SESSION['successMessage'])): ?>
                <div class="alert alert-success alert-dismissible fade show alert-modern" role="alert">
                    <?= htmlspecialchars($_SESSION['successMessage']) ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <?php unset($_SESSION['successMessage']); ?>
            <?php endif; ?>

            <?php if (!empty($_SESSION['errorMessage'])): ?>
                <div class="alert alert-danger alert-dismissible fade show alert-modern" role="alert">
                    <?= htmlspecialchars($_SESSION['errorMessage']) ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <?php unset($_SESSION['errorMessage']); ?>
            <?php endif; ?>

            <?php if (empty($assignments)): ?>
                <div class="alert alert-info alert-modern">
                    <h5 class="mb-1"><i class="fas fa-info-circle"></i> No Assignments</h5>
                    <p class="mb-0">No assignment history found for this uniform.</p>
                </div>
            <?php else: ?>
                <div class="card uniform-card data-card shadow mb-4">
                    <div class="card-header">
                        <h6>
                            <i class="fas fa-list mr-1"></i>
                            <?php if ($conditionFilter === 'DAMAGED'): ?>
                                Damaged Return History
                            <?php elseif ($conditionFilter === 'LOST'): ?>
                                Lost Return History
                            <?php else: ?>
                                Assignment History
                            <?php endif; ?>
                        </h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Employee</th>
                                        <th>Date Issued</th>
                                        <th>Quantity</th>
                                        <th>Date Returned</th>
                                        <th>Status</th>
                                        <th class="text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($assignments as $a): ?>
                                        <?php $isActive = empty($a['date_returned']); ?>
                                        <tr>
                                            <td><?= htmlspecialchars($a['employee_name'] ?? ($a['employee_id'] ?? '')) ?></td>
                                            <td>
                                                <?= !empty($a['date_issued'])
                                                    ? htmlspecialchars(date('M d, Y', strtotime($a['date_issued'])))
                                                    : '-' ?>
                                            </td>
                                            <td><?= (int) ($a['quantity_issued'] ?? 0) ?></td>
                                            <td>
                                                <?= !empty($a['date_returned'])
                                                    ? htmlspecialchars(date('M d, Y', strtotime($a['date_returned'])))
                                                    : '-' ?>
                                            </td>
                                            <td>
                                                <?php if ($isActive): ?>
                                                    <span class="badge bg-success">Active</span>
                                                <?php else: ?>
                                                    <?php
                                                    $returnCondition = strtoupper(trim((string) ($a['condition_upon_return'] ?? '')));
                                                    $badgeClass = 'bg-secondary';
                                                    if ($returnCondition === 'DAMAGED') {
                                                        $badgeClass = 'bg-danger';
                                                    } elseif ($returnCondition === 'LOST') {
                                                        $badgeClass = 'bg-dark';
                                                    }
                                                    ?>
                                                    <span class="badge <?= $badgeClass ?>">
                                                        <?= htmlspecialchars($returnCondition !== '' ? $returnCondition : 'Returned') ?>
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-right">
                                                <?php if ($isActive): ?>
                                                    <a href="<?= htmlspecialchars($base) ?>/hr/uniforms/return_confirm/<?= (int) $a['assignment_id'] ?>"
                                                       class="btn btn-sm btn-warning"
                                                       title="Process return">
                                                        <i class="fas fa-undo"></i> Return
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-muted small">—</span>
                                                <?php endif; ?>
                                            </td>
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
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/jquery/jquery.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/js/storagemart.min.js"></script>
</body>
</html>
