<?php
$base = rtrim(BASE_URL, '/');
$returnEmployeeId = (int) ($return_employee_id ?? 0);
$backUrl = $returnEmployeeId > 0
    ? $base . '/hr/employees/detail/' . $returnEmployeeId
    : $base . '/hr/employees';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Storage Mart | Transfer History</title>
    <link href="<?= htmlspecialchars($base) ?>/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/storagemart.css" rel="stylesheet">
    <link rel="icon" href="<?= htmlspecialchars($base) ?>/assets/img/sm_favicon.png" type="image/x-icon">
    <link href="<?= htmlspecialchars($base) ?>/assets/vendor/datatables/datatables.min.css" rel="stylesheet">
</head>
<body id="page-top">
    <div id="wrapper">
        <?php
        $activePage = 'employees';
        require_once dirname(dirname(__DIR__)) . '/partials/hr/sidebar_topbar.php';
        ?>
        <div class="container-fluid">
            <div class="row mb-4">
                <div class="col-12">
                    <a href="<?= htmlspecialchars($backUrl) ?>" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                </div>
            </div>

            <h1 class="h3 mb-4 text-gray-800">Transfer History</h1>

            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <?= htmlspecialchars($inventory['itemInfo'] ?? 'Asset') ?>
                        (<?= htmlspecialchars($inventory['assetNumber'] ?? '') ?>)
                    </h6>
                </div>
                <div class="card-body">
                    <?php if (empty($assignments)): ?>
                        <p class="text-muted mb-0">No transfer history found for this asset.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-bordered" id="asset-history" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>Employee ID</th>
                                        <th>Name</th>
                                        <th>Transfer Details</th>
                                        <th>Date Issued</th>
                                        <th>Date Returned</th>
                                        <th>Created By</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($assignments as $row): ?>
                                        <tr>
                                            <td><?= htmlspecialchars((string) ($row['employee_id'] ?? '')) ?></td>
                                            <td><?= htmlspecialchars((string) ($row['assignedTo'] ?? '')) ?></td>
                                            <td><?= htmlspecialchars((string) ($row['transferDetails'] ?? '')) ?></td>
                                            <td><?= htmlspecialchars((string) ($row['dateIssued'] ?? '')) ?></td>
                                            <td><?= htmlspecialchars($row['dateReturned'] ?? '') ?></td>
                                            <td><?= htmlspecialchars((string) ($row['createdByName'] ?? $row['createdby'] ?? '')) ?></td>
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

    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/jquery/jquery.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/js/storagemart.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/datatables/datatables.min.js"></script>
    <script>
    $(document).ready(function () {
        if ($('#asset-history').length) {
            $('#asset-history').DataTable();
        }
    });
    </script>
</body>
</html>
