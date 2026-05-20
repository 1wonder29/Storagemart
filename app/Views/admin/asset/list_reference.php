<?php
$base = rtrim(BASE_URL, '/');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Storage Mart | Branch, Category &amp; Group Lists</title>
    <link href="<?= htmlspecialchars($base) ?>/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/storagemart.css" rel="stylesheet">
</head>
<body id="page-top">
<div id="wrapper">
    <?php
    $activePage = 'assets';
    require_once __DIR__ . '/../../partials/admin/sidebar_topbar.php';
    ?>
    <div class="container-fluid">
        <h1 class="h3 mb-3 text-gray-800">Branch, Category &amp; Group Lists</h1>

        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Branches</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm mb-0" width="100%" cellspacing="0">
                        <thead>
                        <tr>
                            <th>Branch ID</th>
                            <th>Branch Code</th>
                            <th>Branch Name</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach (($branches ?? []) as $row): ?>
                            <tr>
                                <td><?= htmlspecialchars((string)($row['branch_id'] ?? '')); ?></td>
                                <td><?= htmlspecialchars($row['branchCode'] ?? ''); ?></td>
                                <td><?= htmlspecialchars($row['branchName'] ?? ''); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($branches)): ?>
                            <tr><td colspan="3" class="text-center text-muted">No branches found.</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Categories</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm mb-0" width="100%" cellspacing="0">
                        <thead>
                        <tr>
                            <th>Category ID</th>
                            <th>IC Code</th>
                            <th>Category Name</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach (($categories ?? []) as $row): ?>
                            <tr>
                                <td><?= htmlspecialchars((string)($row['category_id'] ?? '')); ?></td>
                                <td><?= htmlspecialchars($row['ic_code'] ?? ''); ?></td>
                                <td><?= htmlspecialchars($row['categoryName'] ?? ''); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($categories)): ?>
                            <tr><td colspan="3" class="text-center text-muted">No categories found.</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Groups</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm mb-0" width="100%" cellspacing="0">
                        <thead>
                        <tr>
                            <th>Group ID</th>
                            <th>Group Name</th>
                            <th>Description</th>
                            <th>Category</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach (($groups ?? []) as $row): ?>
                            <tr>
                                <td><?= htmlspecialchars((string)($row['group_id'] ?? '')); ?></td>
                                <td><?= htmlspecialchars($row['groupName'] ?? ''); ?></td>
                                <td><?= htmlspecialchars($row['description'] ?? ''); ?></td>
                                <td><?= htmlspecialchars($row['categoryName'] ?? ''); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($groups)): ?>
                            <tr><td colspan="4" class="text-center text-muted">No groups found.</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <!-- /.container-fluid -->

    </div>
    <!-- End of Main Content -->

    </div>
    <!-- End of Content Wrapper -->

</div>
<!-- End of Page Wrapper -->

<a class="scroll-to-top rounded" href="#page-top">
    <i class="fas fa-angle-up"></i>
</a>

<div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Ready to Leave?</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">Select "Logout" below if you are ready to end your current session.</div>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                <a class="btn btn-primary" href="<?= htmlspecialchars($base) ?>/logout">Logout</a>
            </div>
        </div>
    </div>
</div>

<script src="<?= htmlspecialchars($base) ?>/assets/vendor/jquery/jquery.min.js"></script>
<script src="<?= htmlspecialchars($base) ?>/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="<?= htmlspecialchars($base) ?>/assets/vendor/jquery-easing/jquery.easing.min.js"></script>
<script src="<?= htmlspecialchars($base) ?>/assets/js/sb-admin-2.min.js"></script>
<?php require __DIR__ . '/../../partials/flash_modal.php'; ?>
</body>
</html>
