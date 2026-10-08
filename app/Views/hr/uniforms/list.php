<?php
$base = rtrim(BASE_URL, '/');
$totalCount = (int) ($totalCount ?? 0);
$uniformsNeedingReorder = (int) ($uniformsNeedingReorder ?? 0);
$page = (int) ($page ?? 1);
$totalPages = (int) ($totalPages ?? 1);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Storage Mart | Inventory</title>
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
    $activePage = 'uniforms';
    require_once dirname(dirname(__DIR__)) . '/partials/uniform_sidebar_topbar.php';
    ?>
        <div class="container-fluid hr-dashboard-page hr-uniform-page">
            <div class="page-hero">
                <div class="row align-items-center">
                    <div class="col-lg-7">
                        <h1><i class="fas fa-archive mr-2"></i>Item Inventory</h1>
                        <p>Track stock levels, reorder thresholds, and item status across all types and sizes.</p>
                    </div>
                    <div class="col-lg-5 mt-3 mt-lg-0">
                        <div class="row">
                            <div class="col-6">
                                <div class="hero-stat">
                                    <div class="stat-value"><?= $totalCount ?></div>
                                    <div class="stat-label">Total Items</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="hero-stat">
                                    <div class="stat-value"><?= $uniformsNeedingReorder ?></div>
                                    <div class="stat-label">Needs Reorder</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="quick-actions">
                <a href="<?= htmlspecialchars($base) ?>/hr/uniforms/add" class="quick-action-btn qa-info">
                    <i class="fas fa-plus"></i> Add New Item
                </a>
                <a href="<?= htmlspecialchars($base) ?>/hr/uniforms/assign" class="quick-action-btn qa-warning">
                    <i class="fas fa-user-tag"></i> Assign Item to Employee
                </a>
                <a href="<?= htmlspecialchars($base) ?>/hr/uniforms/export" class="quick-action-btn qa-success">
                    <i class="fas fa-file-excel"></i> Download Summary
                </a>
                <a href="<?= htmlspecialchars($base) ?>/hr/uniforms/import" class="quick-action-btn qa-primary">
                    <i class="fas fa-file-import"></i> Import from Excel
                </a>
                <button type="button" class="quick-action-btn qa-secondary border-0" data-toggle="modal" data-target="#resetStockModal">
                    <i class="fas fa-redo"></i> Reset All Stock
                </button>
                <a href="<?= htmlspecialchars($base) ?>/hr/uniforms/reports" class="quick-action-btn qa-danger">
                    <i class="fas fa-exclamation-triangle"></i> Lost / Damaged Reports
                    <?php if (!empty($pendingItemReports)): ?>
                        <span class="badge badge-light ml-1"><?= (int) $pendingItemReports ?></span>
                    <?php endif; ?>
                </a>
            </div>

            <?php if (!empty($_SESSION['successMessage'])): ?>
                <div class="alert alert-success alert-dismissible fade show alert-modern" role="alert">
                    <i class="fas fa-check-circle mr-1"></i><?= htmlspecialchars($_SESSION['successMessage']) ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <?php unset($_SESSION['successMessage']); ?>
            <?php endif; ?>

            <?php if (!empty($_SESSION['errorMessage'])): ?>
                <div class="alert alert-danger alert-dismissible fade show alert-modern" role="alert">
                    <i class="fas fa-exclamation-circle mr-1"></i><?= htmlspecialchars($_SESSION['errorMessage']) ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <?php unset($_SESSION['errorMessage']); ?>
            <?php endif; ?>

            <?php if ($uniformsNeedingReorder > 0): ?>
                <div class="alert alert-warning alert-modern" role="alert">
                    <i class="fas fa-exclamation-triangle mr-1"></i>
                    <strong><?= $uniformsNeedingReorder ?> item<?= $uniformsNeedingReorder === 1 ? '' : 's' ?> need<?= $uniformsNeedingReorder === 1 ? 's' : '' ?> reorder.</strong>
                </div>
            <?php endif; ?>

            <div class="card shadow uniform-card data-card">
                <div class="card-header py-3">
                    <h6><i class="fas fa-list"></i> All Items (<?= $totalCount ?> item<?= $totalCount === 1 ? '' : 's' ?>, Page <?= $page ?>/<?= $totalPages ?>)</h6>
                </div>
                <div class="card-body">
                    <?php if (empty($uniforms)): ?>
                        <div class="empty-state">
                            <i class="fas fa-archive"></i>
                            <p class="mb-0">No items found.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0 uniforms-table">
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th title="Units on hand, ready to issue">In Stock</th>
                                        <th title="Restock when In Stock falls to this number">Reorder At</th>
                                        <th>Stock Status</th>
                                        <th title="Issued to employees and not yet returned">Issued (Not Returned)</th>
                                        <th>Damaged</th>
                                        <th>Lost</th>
                                        <th title="Whether this uniform is still in use or discontinued">Item Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($uniforms as $uniform): ?>
                                        <?php
                                        $itemStatus = strtoupper($uniform['status'] ?? 'ACTIVE');
                                        $inStock = (int) ($uniform['quantity_in_stock'] ?? 0);
                                        if ($inStock <= 0) {
                                            $stockBadgeClass = 'danger';
                                            $stockLabel = 'Out of Stock';
                                        } elseif (($uniform['stock_status'] ?? 'OK') === 'NEEDS_REORDER') {
                                            $stockBadgeClass = 'warning';
                                            $stockLabel = 'Low Stock';
                                        } else {
                                            $stockBadgeClass = 'success';
                                            $stockLabel = 'In Stock';
                                        }
                                        $pendingReturnCount = (int) ($uniform['quantity_pending_return'] ?? 0);
                                        $damagedCount = (int) ($uniform['quantity_damaged'] ?? 0);
                                        $lostCount = (int) ($uniform['quantity_lost'] ?? 0);
                                        $uniformDetail = trim(($uniform['size'] ?? '') . (!empty($uniform['color']) ? ' · ' . $uniform['color'] : ''));
                                        ?>
                                        <tr>
                                            <td>
                                                <strong><?= htmlspecialchars($uniform['uniform_type'] ?? '') ?></strong>
                                                <?php if ($uniformDetail !== ''): ?>
                                                    <div class="small text-muted"><?= htmlspecialchars($uniformDetail) ?></div>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= $inStock ?></td>
                                            <td><?= (int) $uniform['reorder_level'] ?></td>
                                            <td>
                                                <span class="badge badge-<?= $stockBadgeClass ?> status-pill">
                                                    <?= htmlspecialchars($stockLabel) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="<?= htmlspecialchars($base) ?>/hr/uniforms/assignments/<?= (int) $uniform['uniform_id'] ?>?condition=PENDING"
                                                   class="count-badge badge badge-<?= $pendingReturnCount > 0 ? 'info' : 'light' ?>"
                                                   title="View uniforms issued and not yet returned">
                                                    <?= $pendingReturnCount ?>
                                                </a>
                                            </td>
                                            <td>
                                                <a href="<?= htmlspecialchars($base) ?>/hr/uniforms/assignments/<?= (int) $uniform['uniform_id'] ?>?condition=DAMAGED"
                                                   class="count-badge badge badge-<?= $damagedCount > 0 ? 'danger' : 'light' ?>"
                                                   title="View damaged items">
                                                    <?= $damagedCount ?>
                                                </a>
                                            </td>
                                            <td>
                                                <a href="<?= htmlspecialchars($base) ?>/hr/uniforms/assignments/<?= (int) $uniform['uniform_id'] ?>?condition=LOST"
                                                   class="count-badge badge badge-<?= $lostCount > 0 ? 'dark' : 'light' ?>"
                                                   title="View lost items">
                                                    <?= $lostCount ?>
                                                </a>
                                            </td>
                                            <td>
                                                <span class="badge badge-<?= $itemStatus === 'ACTIVE' ? 'success' : 'secondary' ?> status-pill">
                                                    <?= htmlspecialchars($itemStatus) ?>
                                                </span>
                                            </td>
                                            <td class="actions-cell">
                                                <div class="action-btn-group">
                                                    <a href="<?= htmlspecialchars($base) ?>/hr/uniforms/edit/<?= (int) $uniform['uniform_id'] ?>"
                                                       class="btn btn-sm btn-primary" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <?php if ($itemStatus === 'DISCONTINUED'): ?>
                                                        <form method="post"
                                                              action="<?= htmlspecialchars($base) ?>/hr/uniforms/reactivate/<?= (int) $uniform['uniform_id'] ?>"
                                                              class="d-inline"
                                                              onsubmit="return confirm('Reactivate this item?');">
                                                            <button type="submit" class="btn btn-sm btn-success" title="Reactivate">
                                                                <i class="fas fa-undo"></i>
                                                            </button>
                                                        </form>
                                                    <?php else: ?>
                                                        <a href="<?= htmlspecialchars($base) ?>/hr/uniforms/delete/<?= (int) $uniform['uniform_id'] ?>"
                                                           class="btn btn-sm btn-danger" title="Delete">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <nav>
                            <ul class="pagination mb-0">
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
        </div>
    <div class="modal fade" id="resetStockModal" tabindex="-1" role="dialog" aria-labelledby="resetStockTitle" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form method="POST" action="<?= htmlspecialchars($base) ?>/hr/uniforms/reset-stock">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title" id="resetStockTitle"><i class="fas fa-redo mr-1"></i> Reset All Stock</h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                        <p>This sets <strong>In Stock to 0 for every item</strong>, for example before a physical count.
                            Items already issued to employees are not changed. After counting, use <em>Import from Excel</em>
                            with "Physical count — replace current stock" to load the new numbers.</p>
                        <p class="mb-2"><a href="<?= htmlspecialchars($base) ?>/hr/uniforms/export"><i class="fas fa-file-excel"></i> Download the summary first</a> to keep a copy of the current counts.</p>
                        <div class="custom-control custom-checkbox mb-3">
                            <input type="checkbox" class="custom-control-input" id="includeDamagedLost" name="include_damaged_lost" value="1">
                            <label class="custom-control-label" for="includeDamagedLost">Also reset the Damaged and Lost counts</label>
                        </div>
                        <label for="resetConfirmText" class="font-weight-bold">Type <code>RESET</code> to confirm</label>
                        <input type="text" class="form-control" id="resetConfirmText" name="confirm_text" autocomplete="off" required pattern="[Rr][Ee][Ss][Ee][Tt]">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Reset Stock</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/jquery/jquery.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/js/storagemart.min.js"></script>
</body>
</html>
