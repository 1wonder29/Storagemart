<?php
$base = rtrim(BASE_URL, '/');
?>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>StorageMart | Update Item</title>

    <!-- Fonts & Icons -->
    <link href="<?= htmlspecialchars($base) ?>/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900" rel="stylesheet">

    <!-- Main Styles -->
    <link href="<?= htmlspecialchars($base) ?>/assets/css/storagemart.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/input.css" rel="stylesheet">
</head>

<body id="page-top">

<div id="wrapper">

    <?php 
    $activePage = 'assets';
    require_once __DIR__ . '/../../partials/admin/sidebar_topbar.php';
    ?>

    <!-- Content Wrapper -->
    <div id="content-wrapper" class="d-flex flex-column">
        <div id="content">

            <!-- Page Content -->
            <div class="container-fluid">

                <!-- Page Heading -->
                <h1 class="h3 mb-4 text-gray-800">Update Item Asset</h1>

                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            Update Item Asset
                        </h6>
                    </div>

                    <div class="card-body">

                        <form action="<?= htmlspecialchars($base) ?>/admin/assets/item/update" method="POST">

                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                            <input type="hidden" name="inventory" value="<?= (int)($inventory['inventory_id'] ?? 0) ?>">
                            <input type="hidden" name="group_id" value="<?= (int)($inventory['group_id'] ?? ($_GET['group_id'] ?? 0)) ?>">

                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <label>Asset Number</label>
                                    <input type="text" class="form-control"
                                           value="<?= htmlspecialchars($inventory['assetNumber'] ?? '') ?>" readonly>
                                </div>

                                <div class="col-md-6">
                                    <label>Status</label>
                                    <select name="status" class="form-control" required>
                                        <option value="UNASSIGNED" <?= (($inventory['status'] ?? '') === 'UNASSIGNED') ? 'selected' : ''; ?>>Unassigned</option>
                                        <option value="ASSIGNED" <?= (($inventory['status'] ?? '') === 'ASSIGNED') ? 'selected' : ''; ?>>Assigned</option>
                                        <option value="DISPOSED" <?= (($inventory['status'] ?? '') === 'DISPOSED') ? 'selected' : ''; ?>>Disposed</option>
                                        <option value="LOST" <?= (($inventory['status'] ?? '') === 'LOST') ? 'selected' : ''; ?>>Lost</option>
                                        <option value="RETURNED" <?= (($inventory['status'] ?? '') === 'RETURNED') ? 'selected' : ''; ?>>Returned</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <label>Item General Info</label>
                                    <textarea name="itemInfo" class="form-control" rows="5" required><?= htmlspecialchars($inventory['itemInfo'] ?? '') ?></textarea>
                                </div>

                                <div class="col-md-6">
                                    <label>Serial Number</label>
                                    <input type="text" name="serialNumber" class="form-control"
                                           value="<?= htmlspecialchars($inventory['serialNumber'] ?? '') ?>" required>
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <label>Year Purchased</label>
                                    <input type="text" name="year_purchased" class="form-control"
                                           value="<?= htmlspecialchars($inventory['year_purchased'] ?? '') ?>" required>
                                </div>
                            </div>

                            <div class="row mb-4" id="reasonRow" style="display:none;">
                                <div class="col-md-12">
                                    <label>Reason</label>
                                    <textarea class="form-control" name="transferDetails" rows="4"
                                              placeholder="Enter reason for Disposed, Lost, or Returned"></textarea>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary">
                                Submit
                            </button>

                            <a href="<?= htmlspecialchars($base) ?>/admin/assets/item?group_id=<?= (int)($inventory['group_id'] ?? ($_GET['group_id'] ?? 0)); ?>"
                               class="btn btn-secondary">
                                Cancel
                            </a>

                        </form>

                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- Scroll to Top -->
<a class="scroll-to-top rounded" href="#page-top">
    <i class="fas fa-angle-up"></i>
</a>

<!-- Scripts -->
<script src="<?= htmlspecialchars($base) ?>/assets/vendor/jquery/jquery.min.js"></script>
<script src="<?= htmlspecialchars($base) ?>/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="<?= htmlspecialchars($base) ?>/assets/vendor/jquery-easing/jquery.easing.min.js"></script>
<script src="<?= htmlspecialchars($base) ?>/assets/js/sb-admin-2.min.js"></script>
<script src="<?= htmlspecialchars($base) ?>/assets/js/asset/update_item.js"></script>

</body>
</html>