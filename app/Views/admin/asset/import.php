<?php
$base = rtrim(BASE_URL, '/');
$h = static function ($value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};
$rows = $import['rows'] ?? [];
$rowErrors = $import['errors'] ?? [];
$counts = ['new' => 0, 'update' => 0, 'same' => 0];
foreach ($rows as $r) {
    $counts[$r['action']]++;
}
$labels = ['itemInfo' => 'Item info', 'serialNumber' => 'Serial', 'year_purchased' => 'Year', 'branch_id' => 'Branch'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Storage Mart | Import Assets</title>
    <link href="<?= $h($base) ?>/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900" rel="stylesheet">
    <link href="<?= $h($base) ?>/assets/css/storagemart.css" rel="stylesheet">
    <link href="<?= $h($base) ?>/assets/css/admin-users.css" rel="stylesheet">
    <link rel="icon" href="<?= $h($base) ?>/assets/img/sm_favicon.png" type="image/x-icon">
</head>

<body id="page-top">

    <div id="wrapper">
        <?php
        $activePage = 'assets';
        require_once __DIR__ . '/../../partials/admin/sidebar_topbar.php';
        ?>

        <div class="container-fluid admin-users-page">

            <div class="page-hero hero-accounts">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <h1><i class="fas fa-file-import mr-2"></i>Import Assets</h1>
                        <p>Upload the updated IT inventory sheet. Rows that match an existing asset (by Asset Number, then Serial Number)
                            update it; other rows are added as new unassigned items. Nothing is saved until you confirm the preview.</p>
                    </div>
                    <div class="col-lg-4 text-lg-right">
                        <a href="<?= $h($base) ?>/admin/assets" class="btn btn-light btn-sm mt-2"><i class="fas fa-arrow-left mr-1"></i>Assets</a>
                        <a href="<?= $h($base) ?>/admin/assets/import?template=1" class="btn btn-light btn-sm mt-2"><i class="fas fa-download mr-1"></i>Template</a>
                    </div>
                </div>
            </div>

            <?php if (!$import): ?>
                <div class="row">
                    <div class="col-lg-6">
                        <div class="card form-card shadow mb-4">
                            <div class="card-header"><h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-upload mr-1"></i>Upload</h6></div>
                            <div class="card-body">
                                <form method="POST" action="<?= $h($base) ?>/admin/assets/import" enctype="multipart/form-data">
                                    <input type="hidden" name="csrf_token" value="<?= $h($csrf_token ?? '') ?>">
                                    <input type="hidden" name="step" value="preview">
                                    <div class="form-group">
                                        <label for="import_file" class="font-weight-bold">Spreadsheet (.xlsx or .csv, max 5 MB)</label>
                                        <input type="file" class="form-control-file" id="import_file" name="import_file" accept=".xlsx,.csv" required>
                                        <small class="form-text text-muted">From Google Sheets: File &gt; Download &gt; Microsoft Excel (.xlsx).</small>
                                    </div>
                                    <button type="submit" class="btn btn-primary"><i class="fas fa-search mr-1"></i>Read file &amp; preview</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card form-card shadow mb-4">
                            <div class="card-header"><h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-info-circle mr-1"></i>Columns</h6></div>
                            <div class="card-body small">
                                <ul class="mb-2">
                                    <li><strong>Model</strong> and <strong>Item Info</strong> (required for new items), e.g. Laptop / Lenovo ThinkPad E14, i5, 16GB</li>
                                    <li><strong>Category</strong>: needed only when the Model does not exist yet (it is then created)</li>
                                    <li><strong>Serial Number</strong>, <strong>Year Purchased</strong> (e.g. 2019), <strong>Branch</strong> (code or name)</li>
                                    <li><strong>Asset Number</strong>: fill in to update that exact asset; leave empty for new items</li>
                                </ul>
                                <p class="mb-0 text-muted">Assignment and status are not imported. Use Assign, Transfer and Return for those, so the history stays correct.</p>
                            </div>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="card data-list-card shadow mb-4">
                    <div class="card-header d-flex flex-wrap justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-eye mr-1"></i>Preview: <?= $h($import['filename']) ?></h6>
                        <span class="small text-muted">
                            <?= $counts['new'] ?> new · <?= $counts['update'] ?> update · <?= $counts['same'] ?> unchanged · <?= count($rowErrors) ?> with errors
                        </span>
                    </div>
                    <div class="card-body">
                        <?php if ($rowErrors): ?>
                            <div class="alert alert-warning">
                                <strong><?= count($rowErrors) ?> row(s) will be skipped:</strong>
                                <ul class="mb-0 mt-1 small">
                                    <?php foreach (array_slice($rowErrors, 0, 50, true) as $line => $message): ?>
                                        <li>Row <?= (int) $line ?>: <?= $h($message) ?></li>
                                    <?php endforeach; ?>
                                    <?php if (count($rowErrors) > 50): ?><li>… and <?= count($rowErrors) - 50 ?> more</li><?php endif; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                        <?php if ($rows): ?>
                            <div class="table-responsive">
                                <table class="table table-sm table-hover">
                                    <thead><tr><th>Row</th><th>Action</th><th>Asset #</th><th>Model</th><th>Item Info</th><th>Serial</th><th>Year</th><th>Branch</th><th>Changes</th></tr></thead>
                                    <tbody>
                                        <?php foreach ($rows as $r): ?>
                                            <tr>
                                                <td><?= (int) $r['excel_row'] ?></td>
                                                <td>
                                                    <?php if ($r['action'] === 'new'): ?>
                                                        <span class="badge badge-success">New</span>
                                                        <?php if (!empty($r['create_group'])): ?><span class="badge badge-info">New model</span><?php endif; ?>
                                                    <?php elseif ($r['action'] === 'update'): ?>
                                                        <span class="badge badge-primary">Update</span>
                                                    <?php else: ?>
                                                        <span class="badge badge-light">No change</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= $h($r['asset_number'] ?: '(new number)') ?></td>
                                                <td><?= $h($r['model']) ?></td>
                                                <td><?= $h($r['info']) ?></td>
                                                <td><?= $h($r['serial'] ?: '—') ?></td>
                                                <td><?= $h($r['year'] ?: '—') ?></td>
                                                <td><?= $h($r['branch'] ?: '—') ?></td>
                                                <td class="small"><?= $h(implode(', ', array_map(static fn($c) => $labels[$c] ?? $c, array_keys($r['changes'])))) ?: '—' ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                        <div class="d-flex flex-wrap mt-3" style="gap:8px">
                            <?php if ($counts['new'] + $counts['update'] > 0): ?>
                                <form method="POST" action="<?= $h($base) ?>/admin/assets/import">
                                    <input type="hidden" name="csrf_token" value="<?= $h($csrf_token ?? '') ?>">
                                    <input type="hidden" name="step" value="apply">
                                    <input type="hidden" name="import_token" value="<?= $h($import['token']) ?>">
                                    <button type="submit" class="btn btn-success"><i class="fas fa-check mr-1"></i>
                                        Import (<?= $counts['new'] ?> new, <?= $counts['update'] ?> update)</button>
                                </form>
                            <?php endif; ?>
                            <form method="POST" action="<?= $h($base) ?>/admin/assets/import">
                                <input type="hidden" name="csrf_token" value="<?= $h($csrf_token ?? '') ?>">
                                <input type="hidden" name="step" value="cancel">
                                <button type="submit" class="btn btn-outline-secondary">Cancel / upload another file</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

            </div>
        </div>
    </div>

    <script src="<?= $h($base) ?>/assets/vendor/jquery/jquery.min.js"></script>
    <script src="<?= $h($base) ?>/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="<?= $h($base) ?>/assets/vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="<?= $h($base) ?>/assets/js/sb-admin-2.min.js"></script>
    <?php require __DIR__ . '/../../partials/flash_modal.php'; ?>
</body>

</html>
