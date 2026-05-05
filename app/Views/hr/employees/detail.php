<?php
$base = rtrim(BASE_URL, '/');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Storage Mart | Employee Detail</title>
    <link href="<?= htmlspecialchars($base) ?>/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900" rel="stylesheet">
    <link rel="icon" href="<?= htmlspecialchars($base) ?>/assets/img/sm_favicon.png" type="image/x-icon">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/storagemart.css" rel="stylesheet">
</head>
<body id="page-top">
    <div id="wrapper">
    <?php 
    $activePage = 'employees';
    require_once dirname(dirname(__DIR__)) . '/partials/hr/sidebar_topbar.php';?>
        <div class="container-fluid">
            <div class="row mb-4">
                <div class="col-12">
                    <a href="<?= htmlspecialchars($base) ?>/hr/employees" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                </div>
            </div>

            <h1 class="h3 mb-4 text-gray-800">
                <?= htmlspecialchars($employee['firstname'] . ' ' . $employee['lastname']) ?> 
                <small class="text-muted">(<?= htmlspecialchars($employee['employee_id']) ?>)</small>
            </h1>

            <!-- Employee Info -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Employee Information</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Name:</strong> <?= htmlspecialchars($employee['firstname'] . ' ' . $employee['lastname']) ?></p>
                            <p><strong>Position:</strong> <?= htmlspecialchars($employee['position']) ?></p>
                            <p><strong>Department:</strong> <?= htmlspecialchars($employee['department']) ?></p>
                            <p><strong>Branch:</strong> <?= htmlspecialchars($employee['branchName'] ?? 'N/A') ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Email:</strong> <?= htmlspecialchars($employee['email']) ?></p>
                            <p><strong>User Type:</strong> <span class="badge bg-primary"><?= htmlspecialchars($employee['usertype']) ?></span></p>
                            <p><strong>Status:</strong> <span class="badge bg-<?= ($employee['status'] === 'ACTIVE') ? 'success' : 'danger' ?>"><?= htmlspecialchars($employee['status']) ?></span></p>
                            <p><strong>Date Created:</strong> <?= date('M d, Y', strtotime($employee['datecreated'])) ?></p>
                        </div>
                    </div>
                    <hr>
                    <a href="<?= htmlspecialchars($base) ?>/hr/employees/accountability/<?= $employee['employee_id'] ?>" 
                       class="btn btn-success">
                        <i class="fas fa-download"></i> Download Accountability Form
                    </a>
                </div>
            </div>

            <!-- IT Assets -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Assigned IT Assets</h6>
                </div>
                <div class="card-body">
                    <?php if (empty($assets)): ?>
                        <p class="text-muted">No assets assigned.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead>
                                    <tr>
                                        <th>Asset Code</th>
                                        <th>Item Info</th>
                                        <th>Serial #</th>
                                        <th>Category</th>
                                        <th>Issued Date</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($assets as $asset): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($asset['assetNumber']) ?></td>
                                            <td><?= htmlspecialchars($asset['itemInfo']) ?></td>
                                            <td><?= htmlspecialchars($asset['serialNumber']) ?></td>
                                            <td><?= htmlspecialchars($asset['categoryName'] ?? 'N/A') ?></td>
                                            <td><?= $asset['dateIssued'] ? date('M d, Y', strtotime($asset['dateIssued'])) : 'N/A' ?></td>
                                            <td><span class="badge bg-info"><?= htmlspecialchars($asset['asset_status']) ?></span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Current Uniforms -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Current Uniforms</h6>
                </div>
                <div class="card-body">
                    <?php if (empty($uniforms)): ?>
                        <p class="text-muted">No uniforms assigned.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead>
                                    <tr>
                                        <th>Type</th>
                                        <th>Size</th>
                                        <th>Color</th>
                                        <th>Quantity</th>
                                        <th>Issued</th>
                                        <th>Condition</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($uniforms as $unif): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($unif['uniform_type']) ?></td>
                                            <td><?= htmlspecialchars($unif['size']) ?></td>
                                            <td><?= htmlspecialchars($unif['color']) ?></td>
                                            <td><?= $unif['quantity_issued'] ?></td>
                                            <td><?= date('M d, Y', strtotime($unif['date_issued'])) ?></td>
                                            <td><?= htmlspecialchars($unif['condition_upon_issue'] ?? '-') ?></td>
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
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Uniform History (All)</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
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
                                        <td><?= htmlspecialchars($hist['uniform_type']) ?></td>
                                        <td><?= htmlspecialchars($hist['size'] . ' / ' . $hist['color']) ?></td>
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
