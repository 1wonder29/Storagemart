<?php
$base = rtrim(BASE_URL, '/');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Storage Mart | Pending Uniform Returns</title>
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
            <h1 class="h3 mb-4 text-gray-800">Pending Uniform Returns - Approval</h1>

            <?php if (empty($pendingReturns)): ?>
                <div class="alert alert-info">
                    <h5>No Pending Returns</h5>
                    <p>All uniform returns have been processed.</p>
                </div>
            <?php else: ?>
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-list"></i> Review & Approve Returns (<?= count($pendingReturns) ?> pending)
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Date Returned</th>
                                        <th>Employee</th>
                                        <th>Uniform Type</th>
                                        <th>Size</th>
                                        <th>Qty</th>
                                        <th>Condition</th>
                                        <th>Remarks</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pendingReturns as $return): ?>
                                        <tr>
                                            <td><?= htmlspecialchars(date('M d, Y', strtotime($return['date_returned']))) ?></td>
                                            <td><?= htmlspecialchars($return['employee_name'] ?? 'N/A') ?></td>
                                            <td><?= htmlspecialchars($return['uniform_type'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($return['size'] ?? '') ?></td>
                                            <td><?= max(0, (int) ($return['quantity_returned'] ?? 0)) ?></td>
                                            <td>
                                                <?php
                                                $condition = strtoupper($return['condition_upon_return']);
                                                $badgeColor = match($condition) {
                                                    'GOOD' => 'success',
                                                    'FAIR' => 'info',
                                                    'USED' => 'secondary',
                                                    'DAMAGED' => 'danger',
                                                    'LOST' => 'dark',
                                                    default => 'warning'
                                                };
                                                ?>
                                                <span class="badge bg-<?= $badgeColor ?>">
                                                    <?= htmlspecialchars($condition) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <small><?= htmlspecialchars($return['remarks'] ?? '-') ?></small>
                                            </td>
                                            <td>
                                                <form method="post" action="<?= htmlspecialchars($base) ?>/hr/uniforms/approve-return" style="display: inline;">
                                                    <input type="hidden" name="return_id" value="<?= (int)$return['return_id'] ?>">
                                                    <input type="hidden" name="action" value="approve">
                                                    <button type="submit" class="btn btn-sm btn-success" title="Approve Return">
                                                        <i class="fas fa-check"></i> Approve
                                                    </button>
                                                </form>
                                                <form method="post" action="<?= htmlspecialchars($base) ?>/hr/uniforms/approve-return" style="display: inline;">
                                                    <input type="hidden" name="return_id" value="<?= (int)$return['return_id'] ?>">
                                                    <input type="hidden" name="action" value="reject">
                                                    <button type="submit" class="btn btn-sm btn-danger" title="Reject Return" onclick="return confirm('Are you sure you want to reject this return?');">
                                                        <i class="fas fa-times"></i> Reject
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="alert alert-info">
                    <h6><i class="fas fa-info-circle"></i> How to Process Returns:</h6>
                    <ul class="mb-0">
                        <li><strong>GOOD/FAIR/USED:</strong> Click <strong>Approve</strong> to return to stock</li>
                        <li><strong>DAMAGED:</strong> Click <strong>Approve</strong> to move to damaged inventory</li>
                        <li><strong>LOST:</strong> Click <strong>Approve</strong> to move to lost inventory</li>
                        <li><strong>Reject:</strong> Use if the return should not be processed</li>
                    </ul>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/jquery/jquery.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/js/sb-admin-2.min.js"></script>
</body>
</html>
