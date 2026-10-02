<?php
$base = rtrim(BASE_URL, '/');
$branches = $branches ?? [];
$totalBranches = count($branches);
?><html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Storage Mart | Add Branch</title>

    <!-- Custom fonts for this template -->
    <link href="<?= htmlspecialchars($base) ?>/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="<?= htmlspecialchars($base) ?>/assets/css/storagemart.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/admin-assets.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/input.css" rel="stylesheet">
    <!-- Custom styles for this page -->
    <link href="<?= htmlspecialchars($base) ?>/assets/vendor/datatables/datatables.min.css" rel="stylesheet">

</head>

<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper">
            <?php 
            $activePage = 'assets';
            $assetSubPage = 'add-branch';
            require_once __DIR__ . '/../../partials/admin/sidebar_topbar.php';?>

                <div class="container-fluid admin-assets-page">

                    <div class="page-hero hero-form">
                        <div class="row align-items-center">
                            <div class="col-lg-8">
                                <h1><i class="fas fa-map-marker-alt mr-2"></i>Add Branch</h1>
                                <p>Register a new branch location for asset tracking and employee assignment.</p>
                                <div class="quick-nav mt-3">
                                    <a href="<?= htmlspecialchars($base) ?>/admin/assets" class="btn btn-sm btn-outline-light">
                                        <i class="fas fa-arrow-left mr-1"></i> Back to Directory
                                    </a>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="hero-stat mt-3 mt-lg-0">
                                    <div class="stat-value"><?= (int) $totalBranches ?></div>
                                    <div class="stat-label">Branches</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card form-card shadow mb-4">
                        <div class="card-header">
                            <h6 class="m-0 font-weight-bold text-primary">Branch Details</h6>
                        </div>
                        <div class="card-body">
                        <form action="<?= rtrim($base, '/') ?>/admin/assets/branch/add" method="POST">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                                <div class="form-section-title">Branch Information</div>
                                    <div class="row form-row-gap">
                                            <div class="col-md-6">
                                            <label for = "branchName" class ="form-label">Branch Name</label>
                                                <input type="text" name="branchName" class="form-control" id="branchName" placeholder="Branch Name" required>
                                            </div>
                                            <div class="col-md-6">
                                            <label for = "branchCode" class ="form-label">Branch Code</label>
                                                <input type="text" name="branchCode" class="form-control" id="branchCode" placeholder="Branch Code" required>
                                            </div>
                                    </div>

                                    <div class="row form-row-gap">
                                            <div class="col-md-6">
                                                <label for = "branchAddress" class ="form-label">Branch Address</label>
                                                <textarea id ="branchAddress" name="branchAddress" class="form-control" rows="6" maxlength="1000" required></textarea>
                                                <small class="form-text text-muted">Maximum 1000 characters.</small>
                                            </div>
                                    </div>
                                    <div class="form-actions">
                                        <button type="submit" class="btn btn-primary" name="btnSubmit">
                                            <i class="fas fa-save mr-1"></i> Save Branch
                                        </button>
                                        <a href="<?= htmlspecialchars($base) ?>/admin/assets" class="btn btn-outline-secondary">Cancel</a>
                                    </div>
                                    </form>
                        </div>
                    </div>

                    <div class="card asset-list-card shadow mb-4">
                        <div class="card-header d-flex align-items-center justify-content-between flex-wrap">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="fas fa-list-ul mr-1"></i> Branch List
                            </h6>
                            <div class="card-header-actions">
                                <span class="badge badge-info"><?= (int) $totalBranches ?> branch<?= $totalBranches === 1 ? '' : 'es' ?></span>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <?php if (empty($branches)): ?>
                                <div class="empty-state">
                                    <i class="fas fa-map-marker-alt d-block"></i>
                                    No branches found. Add your first branch above.
                                </div>
                            <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0" id="branchList" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>Code</th>
                                            <th>Branch Name</th>
                                            <th>Address</th>
                                            <th>Date Created</th>
                                            <th class="text-right">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($branches as $branch): ?>
                                            <tr>
                                                <td>
                                                    <span class="asset-number"><?= htmlspecialchars((string) ($branch['branchCode'] ?? '')) ?></span>
                                                </td>
                                                <td>
                                                    <span class="branch-pill">
                                                        <i class="fas fa-map-marker-alt"></i>
                                                        <?= htmlspecialchars((string) ($branch['branchName'] ?? '')) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="desc-text" style="max-width:none;"><?= htmlspecialchars((string) ($branch['branchAddress'] ?? '')) ?></div>
                                                </td>
                                                <td><?= htmlspecialchars((string) ($branch['datecreated'] ?? '—')) ?></td>
                                                <td class="text-right">
                                                    <div class="action-btn-group">
                                                        <a href="<?= htmlspecialchars($base) ?>/admin/assets/branch/update?branch_id=<?= (int) ($branch['branch_id'] ?? 0) ?>"
                                                           class="btn btn-sm btn-outline-secondary" title="Edit branch">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <a href="<?= htmlspecialchars($base) ?>/admin/assets/branch/delete?branch_id=<?= (int) ($branch['branch_id'] ?? 0) ?>"
                                                           class="btn btn-sm btn-outline-danger" title="Delete branch"
                                                           onclick="return confirm('Are you sure you want to delete this branch?');">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                </div>
    </div>

    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/jquery/jquery.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/js/sb-admin-2.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/datatables/datatables.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/js/admin-assets-form-lists.js"></script>
    <?php require __DIR__ . '/../../partials/flash_modal.php'; ?>
</body>

</html>