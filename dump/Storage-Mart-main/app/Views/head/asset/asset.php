<?php
// Defensive defaults
$assets = $assets ?? [];
$employee_id = $employee_id ?? null;
$base = rtrim(BASE_URL, '/');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title>Storage Mart | Assets</title>

    <link href="<?= htmlspecialchars($base) ?>/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/storagemart.css" rel="stylesheet">
    <link rel="icon" href="<?= htmlspecialchars($base) ?>/assets/img/favicon.ico">
    <link href="<?= htmlspecialchars($base) ?>/assets/vendor/datatables/datatables.min.css" rel="stylesheet">
</head>

<body id="page-top">

<div id="wrapper">
<?php
$activePage = 'assets';
require_once __DIR__ . '/../../partials/head/sidebar_topbar.php';
?>

<div id="content-wrapper" class="d-flex flex-column">
<div id="content">
<div class="container-fluid">

    <h1 class="h3 mb-4 text-gray-800">My Assets</h1>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Assigned Assets</h6>

            <?php if ($employee_id): ?>
            <a href="<?= htmlspecialchars($base) ?>/assets/generatePDF/generate_accountability.php?employee_id=<?= (int)$employee_id ?>"
               class="btn btn-primary">
                <i class="fas fa-file-word"></i> Generate Accountability Form
            </a>
            <?php endif; ?>
        </div>

        <div class="card-body">
            <?php if (empty($assets)): ?>
                <div class="alert alert-info text-center">
                    No assets assigned to you.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered" id="assetUser" width="100%" cellspacing="0">
                        <thead>
                        <tr>
                            <th>Asset Number</th>
                            <th>Model</th>
                            <th>Description</th>
                            <th>Item Info</th>
                            <th>Serial Number</th>
                            <th>Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($assets as $row): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['assetNumber']) ?></td>
                                <td><?= htmlspecialchars($row['groupName']) ?></td>
                                <td><?= htmlspecialchars($row['description']) ?></td>
                                <td><?= htmlspecialchars($row['itemInfo']) ?></td>
                                <td><?= htmlspecialchars($row['serialNumber']) ?></td>
                                <td>
                                    <div class="dropdown">
                                        <a class="btn btn-sm btn-secondary dropdown-toggle"
                                           href="#"
                                           role="button"
                                           data-toggle="dropdown">
                                            Action
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-right">
                                            <a class="dropdown-item"
                                               href="<?= htmlspecialchars($base) ?>/head/tickets/create?inventory_id=<?= (int)$row['inventory_id'] ?>">
                                                <i class="fas fa-edit mr-2"></i> File Ticket
                                            </a>
                                        </div>
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
<script src="<?= htmlspecialchars($base) ?>/assets/js/demo/datatables-demo.js"></script>

<?php require __DIR__ . '/../../partials/flash_modal.php'; ?>

</body>
</html>
