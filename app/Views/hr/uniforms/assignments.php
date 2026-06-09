<?php
$base = rtrim(BASE_URL, '/');
$activeCount = 0;
foreach ($assignments ?? [] as $a) {
    if (empty($a['date_returned'])) {
        $activeCount++;
    }
}
$returnedCount = count($assignments ?? []) - $activeCount;
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
</head>
<body id="page-top">
    <div id="wrapper">
        <?php
        $activePage = 'uniforms';
        require_once __DIR__ . '/../../partials/hr/sidebar_topbar.php';
        ?>
        <div class="container-fluid">
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0 text-gray-800">Uniform Assignments</h1>
                <a href="<?= htmlspecialchars($base) ?>/hr/uniforms" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> Back to Uniforms
                </a>
            </div>

            <?php if (!empty($_SESSION['successMessage'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($_SESSION['successMessage']) ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <?php unset($_SESSION['successMessage']); ?>
            <?php endif; ?>

            <?php if (!empty($_SESSION['errorMessage'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($_SESSION['errorMessage']) ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <?php unset($_SESSION['errorMessage']); ?>
            <?php endif; ?>

            <?php if (!empty($uniform)): ?>
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card border-left-primary shadow h-100 py-2">
                            <div class="card-body">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Uniform</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <?= htmlspecialchars($uniform['uniform_type'] ?? '') ?>
                                    <span class="text-muted">(<?= htmlspecialchars($uniform['size'] ?? '') ?>)</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-left-success shadow h-100 py-2">
                            <div class="card-body">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Active Assignments</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $activeCount ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-left-secondary shadow h-100 py-2">
                            <div class="card-body">
                                <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">Returned</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $returnedCount ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (empty($assignments)): ?>
                <div class="alert alert-info">
                    <h5><i class="fas fa-info-circle"></i> No Assignments</h5>
                    <p class="mb-0">No assignment history found for this uniform.</p>
                </div>
            <?php else: ?>
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-list"></i> Assignment History
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Employee</th>
                                        <th>Date Issued</th>
                                        <th>Quantity</th>
                                        <th>Date Returned</th>
                                        <th>Status</th>
                                        <th>Actions</th>
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
                                                    <span class="badge bg-secondary">Returned</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
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

    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/jquery/jquery.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/js/storagemart.min.js"></script>
</body>
</html>
