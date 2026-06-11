<?php
$base = rtrim(BASE_URL, '/');
?><html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Storage Mart | Edit Branch</title>

    <link href="<?= htmlspecialchars($base) ?>/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">

    <link href="<?= htmlspecialchars($base) ?>/assets/css/storagemart.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/admin-assets.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/input.css" rel="stylesheet">

</head>

<body id="page-top">

    <div id="wrapper">
            <?php
            $activePage = 'assets';
            $assetSubPage = 'add-branch';
            require_once __DIR__ . '/../../partials/admin/sidebar_topbar.php';?>

                <div class="container-fluid admin-assets-page">

                    <div class="page-hero hero-form">
                        <h1><i class="fas fa-edit mr-2"></i>Edit Branch</h1>
                        <p>Update branch name, code, and address.</p>
                    </div>

                    <div class="card form-card shadow mb-4">
                        <div class="card-header">
                            <h6 class="m-0 font-weight-bold text-primary">Branch Details</h6>
                        </div>
                        <div class="card-body">
                        <form action="<?= rtrim($base, '/') ?>/admin/assets/branch/update" method="POST">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                            <input type="hidden" name="branch_id" value="<?= (int) ($branch['branch_id'] ?? 0) ?>">
                                <div class="form-section-title">Branch Information</div>
                                    <div class ="row mb-5">
                                            <div class="col-md-6">
                                            <label for="branchName" class="form-label">Branch Name</label>
                                                <input type="text" name="branchName" class="form-control" id="branchName" placeholder="Branch Name" value="<?= htmlspecialchars((string) ($branch['branchName'] ?? '')) ?>" required>
                                            </div>
                                            <div class="col-md-6">
                                            <label for="branchCode" class="form-label">Branch Code</label>
                                                <input type="text" name="branchCode" class="form-control" id="branchCode" placeholder="Branch Code" value="<?= htmlspecialchars((string) ($branch['branchCode'] ?? '')) ?>" required>
                                            </div>
                                    </div>

                                    <div class ="row mb-5">
                                            <div class="col-md-6">
                                                <label for="branchAddress" class="form-label">Branch Address</label>
                                                <textarea id="branchAddress" name="branchAddress" class="form-control" rows="6" maxlength="1000" required><?= htmlspecialchars((string) ($branch['branchAddress'] ?? '')) ?></textarea>
                                                <small class="form-text text-muted">Maximum 1000 characters.</small>
                                            </div>
                                    </div>
                                    <div class="form-actions">
                                        <button type="submit" class="btn btn-primary" name="btnSubmit">
                                            <i class="fas fa-save mr-1"></i> Save Changes
                                        </button>
                                        <a href="<?= htmlspecialchars($base) ?>/admin/assets/branch/add" class="btn btn-outline-secondary">Cancel</a>
                                    </div>
                                    </form>
                        </div>
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
    <?php require __DIR__ . '/../../partials/flash_modal.php'; ?>
</body>

</html>
