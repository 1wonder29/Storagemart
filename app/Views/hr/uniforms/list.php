<?php
$base = rtrim(BASE_URL, '/');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Storage Mart | Uniforms</title>
    <link href="<?= htmlspecialchars($base) ?>/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900" rel="stylesheet">
    <link rel="icon" href="<?= htmlspecialchars($base) ?>/assets/img/sm_favicon.png" type="image/x-icon">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/storagemart.css" rel="stylesheet">
</head>
<body id="page-top">
    <div id="wrapper">
    <?php 
    $activePage = 'uniforms';
    require_once dirname(dirname(__DIR__)) . '/partials/hr/sidebar_topbar.php';?>
        <div class="container-fluid">
            <div class="row mb-4">
                <div class="col-12">
                    <a href="<?= htmlspecialchars($base) ?>/hr/uniforms/add" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Uniform
                    </a>
                    <a href="<?= htmlspecialchars($base) ?>/hr/uniforms/assign" class="btn btn-success">
                        <i class="fas fa-hand-holding"></i> Assign Uniform to Employee
                    </a>
                </div>
            </div>
            <h1 class="h3 mb-4 text-gray-800">Uniform Inventory</h1>

            <!-- Reorder Alert -->
            <?php if ($uniformsNeedingReorder > 0): ?>
                <div class="alert alert-warning" role="alert">
                    <i class="fas fa-exclamation-triangle"></i> 
                    <strong><?= $uniformsNeedingReorder ?> uniform(s) need reorder!</strong>
                </div>
            <?php endif; ?>

            <!-- Uniforms Table -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">All Uniforms (Page <?= $page ?? 1 ?>/<?= $totalPages ?? 1 ?>)</h6>
                </div>
                <div class="card-body">
                    <?php if (empty($uniforms)): ?>
                        <p class="text-muted">No uniforms found.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Type</th>
                                        <th>Size</th>
                                        <th>In Stock</th>
                                        <th>Reorder Level</th>
                                        <th>Stock Status</th>
                                        <th>Pending Return</th>
                                        <th>Damaged</th>
                                        <th>Lost</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($uniforms as $uniform): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($uniform['uniform_type']) ?></td>
                                            <td><?= htmlspecialchars($uniform['size']) ?></td>
                                            <td><?= (int)$uniform['quantity_in_stock'] ?></td>
                                            <td><?= (int)$uniform['reorder_level'] ?></td>
                                            <td><span class="badge bg-<?= ($uniform['stock_status'] === 'NEEDS_REORDER') ? 'warning' : 'info' ?>"><?= htmlspecialchars($uniform['stock_status']) ?></span></td>
                                            <td>
                                                <?php $pendingReturn = (int) ($uniform['quantity_returned'] ?? 0); ?>
                                                <a href="<?= htmlspecialchars($base) ?>/hr/uniforms/assignments/<?= (int) $uniform['uniform_id'] ?>"
                                                   class="btn btn-sm btn-warning"
                                                   title="View pending returns">
                                                    <?= $pendingReturn ?>
                                                </a>
                                            </td>
                                            <td>
                                                <span class="btn btn-sm btn-danger" title="Damaged uniforms">
                                                    <?= (int) ($uniform['quantity_damaged'] ?? 0) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="btn btn-sm btn-dark" title="Lost uniforms">
                                                    <?= (int) ($uniform['quantity_lost'] ?? 0) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="<?= htmlspecialchars($base) ?>/hr/uniforms/edit/<?= $uniform['uniform_id'] ?>" 
                                                   class="btn btn-sm btn-primary">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="<?= htmlspecialchars($base) ?>/hr/uniforms/delete/<?= $uniform['uniform_id'] ?>" 
                                                   class="btn btn-sm btn-danger">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <nav>
                            <ul class="pagination">
                                <?php if ($page > 1): ?>
                                    <li class="page-item"><a class="page-link" href="?page=1">First</a></li>
                                    <li class="page-item"><a class="page-link" href="?page=<?= $page - 1 ?>">Previous</a></li>
                                <?php endif; ?>

                                <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                                    <li class="page-item <?= ($i === $page) ? 'active' : '' ?>">
                                        <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>

                                <?php if ($page < $totalPages): ?>
                                    <li class="page-item"><a class="page-link" href="?page=<?= $page + 1 ?>">Next</a></li>
                                    <li class="page-item"><a class="page-link" href="?page=<?= $totalPages ?>">Last</a></li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    </div>

    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/jquery/jquery.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/js/storagemart.min.js"></script>
</body>
</html>
