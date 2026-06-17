<?php
$base = rtrim(BASE_URL, '/');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Storage Mart | IT — Transfer History</title>
    <link href="<?= htmlspecialchars($base) ?>/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/storagemart.css" rel="stylesheet">
    <?php require_once __DIR__ . '/../../partials/it/theme_head.php'; ?>
    <link rel="icon" href="<?= htmlspecialchars($base) ?>/assets/img/favicon.png" type="image/png">
    <link href="<?= htmlspecialchars($base) ?>/assets/vendor/datatables/datatables.min.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/it-assets.css" rel="stylesheet">
</head>
<body id="page-top">
    <div id="wrapper">
        <?php
        $activePage = 'assets';
        require_once __DIR__ . '/../../partials/it/sidebar_topbar.php';
        ?>
        <div class="container-fluid it-assets-page">
            <div class="page-hero hero-assets">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <h1><i class="fas fa-history mr-2"></i>Transfer History</h1>
                        <p>Review assignment and return records for accountability tracking.</p>
                    </div>
                    <div class="col-lg-4 mt-3 mt-lg-0 text-lg-right">
                        <a href="<?= htmlspecialchars($base) ?>/it/assets" class="btn btn-light btn-sm">
                            <i class="fas fa-arrow-left"></i> Back to My Assets
                        </a>
                    </div>
                </div>
            </div>

            <?php if (!empty($_SESSION['flash_success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($_SESSION['flash_success']) ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <?php unset($_SESSION['flash_success']); ?>
            <?php endif; ?>

            <?php if (!empty($_SESSION['flash_error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($_SESSION['flash_error']) ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <?php unset($_SESSION['flash_error']); ?>
            <?php endif; ?>

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
                                        <th>Remarks</th>
                                        <th>Date Issued</th>
                                        <th>Date Returned</th>
                                        <th>Created By</th>
                                        <th class="text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($assignments as $row): ?>
                                        <?php
                                        $hasReturn = !empty($row['dateReturned']);
                                        $assignmentId = (int) ($row['assignment_id'] ?? 0);
                                        ?>
                                        <tr>
                                            <td><?= htmlspecialchars((string) ($row['employee_id'] ?? '')) ?></td>
                                            <td><?= htmlspecialchars((string) ($row['assignedTo'] ?? '')) ?></td>
                                            <td><?= htmlspecialchars((string) ($row['transferDetails'] ?? '')) ?></td>
                                            <td><?= htmlspecialchars((string) ($row['dateIssued'] ?? '')) ?></td>
                                            <td><?= htmlspecialchars((string) ($row['dateReturned'] ?? '')) ?></td>
                                            <td><?= htmlspecialchars((string) ($row['createdByName'] ?? $row['createdby'] ?? '')) ?></td>
                                            <td class="text-right">
                                                <?php if ($hasReturn && $assignmentId > 0): ?>
                                                    <button type="button"
                                                            class="btn btn-sm btn-outline-primary btn-edit-accountability-remarks"
                                                            data-assignment-id="<?= $assignmentId ?>"
                                                            data-remarks="<?= htmlspecialchars((string) ($row['transferDetails'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                                        <i class="fas fa-edit"></i> Edit Remarks
                                                    </button>
                                                <?php else: ?>
                                                    <span class="text-muted">—</span>
                                                <?php endif; ?>
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
    <?php require __DIR__ . '/../../partials/asset/accountability_remarks_modal.php'; ?>
</body>
</html>
