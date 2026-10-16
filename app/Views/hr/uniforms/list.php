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
    <link rel="icon" href="<?= htmlspecialchars($base) ?>/assets/img/sm-icon-32.png?v=20261016" type="image/x-icon">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/storagemart.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/hr-dashboard.css?v=20261007" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/hr-uniforms.css" rel="stylesheet">
    <style>
        .inv-guide { background: #fff; border: 1px solid rgba(25, 57, 138, .08); border-radius: 1rem; box-shadow: 0 6px 22px -12px rgba(15, 37, 99, .25); padding: 14px 16px; margin-bottom: 1.25rem; }
        .inv-guide-head { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 6px; margin-bottom: 10px; color: #19398a; }
        .inv-guide-head strong { font-size: 1rem; }
        .inv-steps { margin: 0 0 12px; padding: 10px 14px 10px 32px; background: #f5f8fe; border-radius: .75rem; font-size: .88rem; color: #334155; }
        .inv-steps li { margin-bottom: 4px; }
        .inv-groups { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px; }
        .inv-group { border: 1px solid #e6ecf7; border-radius: .85rem; padding: 12px; background: linear-gradient(180deg, #fbfcff, #fff); display: flex; flex-direction: column; }
        .inv-group-title { font-weight: 800; color: #19398a; font-size: .9rem; display: flex; align-items: center; gap: 8px; }
        .inv-num { width: 24px; height: 24px; border-radius: 50%; background: #f85f00; color: #fff; font-size: .75rem; display: inline-flex; align-items: center; justify-content: center; flex: 0 0 auto; }
        .inv-group p { font-size: .8rem; color: #64748b; margin: 6px 0 10px; flex: 1; }
        .inv-group-actions { display: flex; flex-wrap: wrap; gap: 6px; }
        @media (max-width: 1199.98px) { .inv-groups { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (max-width: 575.98px) { .inv-groups { grid-template-columns: 1fr; } }
        html.it-dark .inv-guide, html.it-dark .inv-group { background: #232838; border-color: #343a4f; }
        html.it-dark .inv-steps { background: #1c2130; color: #cbd5e1; }
        html.it-dark .inv-group p { color: #9ca3af; }
    </style>
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

            <?php
            require_once dirname(__DIR__, 3) . '/Models/hr/HrBatchModel.php';
            $pendingApprovals = (new HrBatchModel())->pendingCount();
            ?>
            <?php /* The buttons grouped by what HR wants to do, each with a one-line explanation. */ ?>
            <div class="inv-guide">
                <div class="inv-guide-head">
                    <strong><i class="fas fa-compass mr-1"></i> What do you want to do?</strong>
                    <button type="button" class="btn btn-sm btn-link p-0 inv-guide-toggle" data-toggle="collapse" data-target="#invHowItWorks" aria-expanded="false" aria-controls="invHowItWorks">
                        <i class="fas fa-question-circle"></i> How it works (4 steps)
                    </button>
                </div>
                <div class="collapse" id="invHowItWorks">
                    <ol class="inv-steps">
                        <li><b>Add each item once</b> with <i>Add New Item</i>: type, size, color, how many are in stock, and the reorder level. For many items at once use <i>Import from Excel</i>.</li>
                        <li><b>Give items to employees.</b> One person: <i>Assign Item to Employee</i>. Many people: <i>Assign from Excel</i>. The stock goes down automatically.</li>
                        <li><b>Excel files need approval.</b> Another HR account or the Admin approves them in <i>Approvals</i>. Nothing changes until then.</li>
                        <li><b>The employee confirms</b> with "Confirm received" in My Issued Items. If an item is lost or damaged, they report it and you answer in <i>Lost / Damaged Reports</i>.</li>
                    </ol>
                </div>
                <div class="inv-groups">
                    <div class="inv-group">
                        <div class="inv-group-title"><span class="inv-num">1</span> Items in stock</div>
                        <p>Add the items HR keeps (shirts, IDs...) or update their stock.</p>
                        <div class="inv-group-actions">
                            <a href="<?= htmlspecialchars($base) ?>/hr/uniforms/add" class="btn btn-sm btn-warning"><i class="fas fa-plus"></i> Add New Item</a>
                            <a href="<?= htmlspecialchars($base) ?>/hr/uniforms/import" class="btn btn-sm btn-outline-primary" title="Add many items or stock counts from an Excel file (needs approval)"><i class="fas fa-file-import"></i> Import from Excel</a>
                            <a href="<?= htmlspecialchars($base) ?>/hr/uniforms/export" class="btn btn-sm btn-outline-success" title="Download the stock list as Excel"><i class="fas fa-file-excel"></i> Download Summary</a>
                            <button type="button" class="btn btn-sm btn-outline-secondary" data-toggle="modal" data-target="#resetStockModal" title="Set every item's stock to 0 (for a new count)"><i class="fas fa-redo"></i> Reset All Stock</button>
                        </div>
                    </div>
                    <div class="inv-group">
                        <div class="inv-group-title"><span class="inv-num">2</span> Give items to employees</div>
                        <p>Issue items. The stock goes down and the employee confirms receipt.</p>
                        <div class="inv-group-actions">
                            <a href="<?= htmlspecialchars($base) ?>/hr/uniforms/assign" class="btn btn-sm btn-primary" title="Give an item to one employee"><i class="fas fa-user-tag"></i> Assign Item to Employee</a>
                            <a href="<?= htmlspecialchars($base) ?>/hr/uniforms/assign-import" class="btn btn-sm btn-outline-primary" title="Give items to many employees from an Excel file (needs approval)"><i class="fas fa-users"></i> Assign from Excel</a>
                        </div>
                    </div>
                    <div class="inv-group">
                        <div class="inv-group-title"><span class="inv-num">3</span> Approve Excel uploads</div>
                        <p>Excel files wait here. Another HR account or the Admin approves.</p>
                        <div class="inv-group-actions">
                            <a href="<?= htmlspecialchars($base) ?>/hr/uniforms/approvals" class="btn btn-sm btn-primary"><i class="fas fa-clipboard-check"></i> Approvals
                                <?php if ($pendingApprovals > 0): ?><span class="badge badge-light"><?= (int) $pendingApprovals ?></span><?php endif; ?>
                            </a>
                        </div>
                    </div>
                    <div class="inv-group">
                        <div class="inv-group-title"><span class="inv-num">4</span> Lost or damaged items</div>
                        <p>Employees report problems with issued items here.</p>
                        <div class="inv-group-actions">
                            <a href="<?= htmlspecialchars($base) ?>/hr/uniforms/reports" class="btn btn-sm btn-danger"><i class="fas fa-exclamation-triangle"></i> Lost / Damaged Reports
                                <?php if (!empty($pendingItemReports)): ?><span class="badge badge-light"><?= (int) $pendingItemReports ?></span><?php endif; ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <?php if ($pendingApprovals > 0): ?>
                <div class="alert alert-warning alert-modern">
                    <i class="fas fa-clipboard-check mr-1"></i>
                    <?= (int) $pendingApprovals ?> Excel upload<?= $pendingApprovals === 1 ? ' is' : 's are' ?> waiting for approval.
                    <a href="<?= htmlspecialchars($base) ?>/hr/uniforms/approvals" class="font-weight-bold">Open Approvals</a>
                </div>
            <?php endif; ?>

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
                                                       class="btn btn-sm btn-primary" title="Edit item">
                                                        <i class="fas fa-edit"></i> Edit
                                                    </a>
                                                    <?php if ($itemStatus === 'DISCONTINUED'): ?>
                                                        <form method="post"
                                                              action="<?= htmlspecialchars($base) ?>/hr/uniforms/reactivate/<?= (int) $uniform['uniform_id'] ?>"
                                                              class="d-inline"
                                                              data-confirm="Reactivate this item?">
                                                            <button type="submit" class="btn btn-sm btn-success" title="Make the item active again">
                                                                <i class="fas fa-undo"></i> Reactivate
                                                            </button>
                                                        </form>
                                                        <form method="post"
                                                              action="<?= htmlspecialchars($base) ?>/hr/uniforms/delete-permanent/<?= (int) $uniform['uniform_id'] ?>"
                                                              class="d-inline"
                                                              data-confirm="Delete this item permanently? Its past issuance records are removed too. This cannot be undone.">
                                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                                                            <button type="submit" class="btn btn-sm btn-danger" title="Remove the item for good">
                                                                <i class="fas fa-trash-alt"></i> Delete permanently
                                                            </button>
                                                        </form>
                                                    <?php else: ?>
                                                        <a href="<?= htmlspecialchars($base) ?>/hr/uniforms/delete/<?= (int) $uniform['uniform_id'] ?>"
                                                           class="btn btn-sm btn-outline-danger" title="Stop using this item (it can be reactivated)">
                                                            <i class="fas fa-ban"></i> Discontinue
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
