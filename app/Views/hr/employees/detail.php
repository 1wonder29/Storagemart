<?php
$base = rtrim(BASE_URL, '/');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Storage Mart | Employee Detail</title>
    <link href="<?= htmlspecialchars($base) ?>/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900" rel="stylesheet">
    <link rel="icon" href="<?= htmlspecialchars($base) ?>/assets/img/sm_favicon.png" type="image/x-icon">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/storagemart.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/hr-dashboard.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/hr-pages.css" rel="stylesheet">
</head>
<body id="page-top">
    <div id="wrapper">
    <?php 
    $activePage = 'employees';
    require_once dirname(dirname(__DIR__)) . '/partials/hr/sidebar_topbar.php';?>
        <div class="container-fluid hr-dashboard-page hr-employee-detail-page">
            <div class="page-hero">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <h1><i class="fas fa-id-badge mr-2"></i><?= htmlspecialchars(($employee['firstname'] ?? '') . ' ' . ($employee['lastname'] ?? '')) ?></h1>
                        <p>Employee profile, assets, and uniform accountability overview.</p>
                    </div>
                    <div class="col-lg-4 mt-3 mt-lg-0 text-lg-right">
                        <a href="<?= htmlspecialchars($base) ?>/hr/employees" class="btn btn-light btn-sm">
                            <i class="fas fa-arrow-left"></i> Back to Employees
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

            <!-- Employee Info -->
            <div class="card shadow mb-4 detail-card">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-user mr-1"></i> Employee Information</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="detail-grid-item">
                                <span class="detail-label">Name</span>
                                <div class="detail-value"><?= htmlspecialchars(($employee['firstname'] ?? '') . ' ' . ($employee['lastname'] ?? '')) ?></div>
                            </div>
                            <div class="detail-grid-item">
                                <span class="detail-label">Position</span>
                                <div class="detail-value"><?= htmlspecialchars($employee['position'] ?? '') ?></div>
                            </div>
                            <div class="detail-grid-item">
                                <span class="detail-label">Department</span>
                                <div class="detail-value"><?= htmlspecialchars($employee['department'] ?? '') ?></div>
                            </div>
                            <div class="detail-grid-item">
                                <span class="detail-label">Branch</span>
                                <div class="detail-value"><?= htmlspecialchars($employee['branchName'] ?? 'N/A') ?></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="detail-grid-item">
                                <span class="detail-label">Email</span>
                                <div class="detail-value"><?= htmlspecialchars($employee['email'] ?? '') ?></div>
                            </div>
                            <div class="detail-grid-item">
                                <span class="detail-label">User Type</span>
                                <div class="detail-value"><span class="badge bg-primary"><?= htmlspecialchars($employee['usertype'] ?? '') ?></span></div>
                            </div>
                            <div class="detail-grid-item">
                                <span class="detail-label">Status</span>
                                <div class="detail-value"><span class="badge bg-<?= ($employee['status'] === 'ACTIVE') ? 'success' : 'danger' ?>"><?= htmlspecialchars($employee['status'] ?? '') ?></span></div>
                            </div>
                            <div class="detail-grid-item">
                                <span class="detail-label">Date Created</span>
                                <div class="detail-value"><?= !empty($employee['datecreated']) ? date('M d, Y', strtotime($employee['datecreated'])) : '-' ?></div>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="actions-inline">
                    <a href="<?= htmlspecialchars($base) ?>/hr/employees/accountability/<?= $employee['employee_id'] ?>" class="btn btn-success">
                        <i class="fas fa-download"></i> Download Accountability Form
                    </a>
                    </div>
                </div>
            </div>

            <!-- IT Assets -->
            <div class="card shadow mb-4 data-card">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-laptop mr-1"></i> Assigned IT Assets</h6>
                </div>
                <div class="card-body">
                    <?php if (empty($assets)): ?>
                        <p class="text-muted">No assets assigned.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Asset Code</th>
                                        <th>Item Info</th>
                                        <th>Serial #</th>
                                        <th>Category</th>
                                        <th>Issued Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($assets as $asset): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($asset['assetNumber'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($asset['itemInfo'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($asset['serialNumber'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($asset['categoryName'] ?? 'N/A') ?></td>
                                            <td><?= $asset['dateIssued'] ? date('M d, Y', strtotime($asset['dateIssued'])) : 'N/A' ?></td>
                                            <td><span class="badge bg-info"><?= htmlspecialchars($asset['asset_status'] ?? '') ?></span></td>
                                            <td>
                                                <a href="<?= htmlspecialchars($base) ?>/hr/assets/transfer?inventory_id=<?= (int) $asset['inventory_id'] ?>&return_employee_id=<?= (int) $employee['employee_id'] ?>"
                                                   class="btn btn-sm btn-primary">
                                                    <i class="fas fa-exchange-alt"></i> Transfer
                                                </a>
                                                <a href="<?= htmlspecialchars($base) ?>/hr/assets/transfer-history?inventory_id=<?= (int) $asset['inventory_id'] ?>&return_employee_id=<?= (int) $employee['employee_id'] ?>"
                                                   class="btn btn-sm btn-secondary">
                                                    <i class="fas fa-history"></i> History
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Current Uniforms -->
            <div class="card shadow mb-4 data-card">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-tshirt mr-1"></i> Current Uniforms</h6>
                </div>
                <div class="card-body">
                    <?php if (empty($uniforms)): ?>
                        <p class="text-muted">No uniforms assigned.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Type</th>
                                        <th>Size</th>
                                        <th>Quantity</th>
                                        <th>Issued</th>
                                        <th>Condition</th>
                                        <th>Return</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($uniforms as $unif): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($unif['uniform_type'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($unif['size'] ?? '') ?></td>
                                            <td><?= $unif['quantity_issued'] ?></td>
                                            <td><?= date('M d, Y', strtotime($unif['date_issued'])) ?></td>
                                            <td><?= htmlspecialchars($unif['condition_upon_issue'] ?? '-') ?></td>
                                            <td>
                                                <a href="<?= htmlspecialchars($base) ?>/hr/uniforms/return_confirm/<?= $unif['assignment_id'] ?>"
                                                   class="btn btn-sm btn-primary">Return</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Uniform History -->
            <?php if (!empty($uniformHistory)): ?>
            <div class="card shadow mb-4 data-card">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-history mr-1"></i> Uniform History (All)</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Size/Color</th>
                                    <th>Issued</th>
                                    <th>Returned</th>
                                    <th>Condition Issue</th>
                                    <th>Condition Return</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($uniformHistory as $hist): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($hist['uniform_type'] ?? '') ?></td>
                                        <td><?= htmlspecialchars(($hist['size'] ?? '') . ' / ' . ($hist['color'] ?? '')) ?></td>
                                        <td><?= date('M d, Y', strtotime($hist['date_issued'])) ?></td>
                                        <td><?= $hist['date_returned'] ? date('M d, Y', strtotime($hist['date_returned'])) : '-' ?></td>
                                        <td><?= htmlspecialchars($hist['condition_upon_issue'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($hist['condition_upon_return'] ?? '-') ?></td>
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

    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/jquery/jquery.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/js/storagemart.min.js"></script>
</body>
</html>
