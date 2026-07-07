<?php
$base = rtrim(BASE_URL, '/');
$assetStatus = strtoupper(trim((string) ($inventory['status'] ?? '')));
$isAssignMode = in_array($assetStatus, ['UNASSIGNED', 'RETURNED'], true);
$pageTitle = $isAssignMode ? 'Assign Asset' : 'Transfer Asset';
$pageDescription = $isAssignMode
    ? 'Assign this asset item to an employee.'
    : 'Reassign this asset item to another employee and record transfer details.';
$submitLabel = $isAssignMode ? 'Confirm' : 'Complete Transfer';
$detailsLabel = $isAssignMode ? 'Assign Details' : 'Transfer Details';
$heroIcon = $isAssignMode ? 'fa-user-plus' : 'fa-exchange-alt';
$groupId = (int) ($inventory['group_id'] ?? 0);
$backUrl = $groupId > 0
    ? $base . '/admin/assets/item?group_id=' . $groupId
    : $base . '/admin/assets';
?>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>storagemart | Admin Transfer Details</title>

    <!-- Custom fonts for this template -->
    <link href="<?= htmlspecialchars($base) ?>/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="<?= htmlspecialchars($base) ?>/assets/css/storagemart.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/admin-assets.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/input.css" rel="stylesheet">

</head>

<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper">
            <?php 
            $activePage = 'assets';
            $assetSubPage = 'directory';
            require_once __DIR__ . '/../../partials/admin/sidebar_topbar.php';?>

                <div class="container-fluid admin-assets-page">

                    <div class="page-hero hero-form">
                        <h1><i class="fas <?= htmlspecialchars($heroIcon) ?> mr-2"></i><?= htmlspecialchars($pageTitle) ?></h1>
                        <p><?= htmlspecialchars($pageDescription) ?></p>
                        <div class="quick-nav mt-3">
                            <a href="<?= htmlspecialchars($backUrl) ?>" class="btn btn-sm btn-outline-light">
                                <i class="fas fa-arrow-left mr-1"></i> Back to Inventory
                            </a>
                        </div>
                    </div>

                    <div class="card form-card shadow mb-4">
                        <?php if (!$isAssignMode): ?>
                        <div class="card-header">
                            <h6 class="m-0 font-weight-bold text-primary">Transfer Details</h6>
                        </div>
                        <?php endif; ?>
                        <div class="card-body">
                            <form action="<?= htmlspecialchars($base) ?>/admin/assets/transfer?inventory_id=<?= (int)($inventory['inventory_id'] ?? 0) ?>" method="POST" id="transferForm">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                                <input type="hidden" name="item_id" value="<?= (int)($inventory['inventory_id'] ?? 0) ?>">
                                <input type="hidden" name="group_id" value="<?= (int)($inventory['group_id'] ?? 0) ?>">
                                    <div class="row mb-5">
                                        <div class="col-md-6">
                                            <label for="employee_search" class="form-label">Search Employee</label>
                                            <div class="employee-search-field"
                                                 data-employee-search
                                                 data-suggest-url="<?= htmlspecialchars($base) ?>/admin/assets/search-employees"
                                                 data-search-url="<?= htmlspecialchars($base) ?>/admin/assets/search-employee">
                                                <div class="input-group">
                                                    <input type="text"
                                                           id="employee_search"
                                                           class="form-control"
                                                           placeholder="Type employee name or ID"
                                                           autocomplete="off"
                                                           data-employee-search-input>
                                                    <button type="button" class="btn btn-primary" id="btnSearchEmployee" data-employee-search-button>Search</button>
                                                </div>
                                                <div id="employee_search_suggestions"
                                                     class="employee-search-suggestions"
                                                     data-employee-suggestions
                                                     hidden></div>
                                                <input type="hidden" id="employee_id" name="employee_id" data-employee-id-input>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="branchName" class="form-label">Employee Branch</label>
                                            <input type="text" class="form-control" id="branchName" name="branchName" placeholder="Employee Branch" readonly data-employee-branch-input>
                                        </div>
                                    </div>

                                    <?php if ($isAssignMode): ?>
                                    <input type="hidden" name="transferDetails" value="Asset assigned">
                                    <?php else: ?>
                                    <div class="row mb-5">
                                        <div class="col-md-6">
                                            <label for="transferDetails" class="form-label"><?= htmlspecialchars($detailsLabel) ?></label>
                                            <textarea id="transferDetails"
                                                      name="transferDetails"
                                                      class="form-control"
                                                      rows="6"
                                                      maxlength="1000"
                                                      required></textarea>
                                            <small class="form-text text-muted">Maximum 1000 characters.</small>
                                        </div>
                                    </div>
                                    <?php endif; ?>

                                    <div class="form-actions">
                                        <button type="submit" class="btn btn-primary" name="btnSubmit">
                                            <i class="fas fa-check mr-1"></i> <?= htmlspecialchars($submitLabel) ?>
                                        </button>
                                        <a class="btn btn-outline-secondary" href="<?= htmlspecialchars($backUrl) ?>">Cancel</a>
                                    </div>
                                    </form>
                        </div>
                    </div>
                    
                </div>
                <!-- /.container-fluid -->

            </div>
            <!-- End of Main Content -->

            <!-- Footer -->
            <!-- End of Footer -->

        </div>
        <!-- End of Content Wrapper -->

    </div>
    <!-- End of Page Wrapper -->

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <!-- Logout Modal-->

    <!-- Bootstrap core JavaScript-->
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/jquery/jquery.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Core plugin JavaScript-->
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/jquery-easing/jquery.easing.min.js"></script>

    <!-- Custom scripts for all pages-->
    <script src="<?= htmlspecialchars($base) ?>/assets/js/sb-admin-2.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/js/employee-search-autocomplete.js"></script>

</body>

</html>