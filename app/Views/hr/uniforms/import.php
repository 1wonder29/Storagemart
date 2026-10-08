<?php
$base = rtrim(BASE_URL, '/');
$import = $import ?? null;
$validRows = $import['rows'] ?? [];
$rowErrors = $import['errors'] ?? [];
$newCount = count(array_filter($validRows, static fn($r) => $r['existing_id'] === null));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Storage Mart | Import Items</title>
    <link href="<?= htmlspecialchars($base) ?>/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900" rel="stylesheet">
    <link rel="icon" href="<?= htmlspecialchars($base) ?>/assets/img/sm_favicon.png" type="image/x-icon">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/storagemart.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/hr-dashboard.css?v=20261008" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/hr-uniforms.css" rel="stylesheet">
</head>
<body id="page-top">
<div id="wrapper">
    <?php
    $activePage = 'uniforms';
    require_once dirname(dirname(__DIR__)) . '/partials/uniform_sidebar_topbar.php';
    ?>
    <div class="container-fluid hr-dashboard-page hr-uniform-page">
        <div class="page-hero">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h1><i class="fas fa-file-import mr-2"></i>Import Items from Excel</h1>
                    <p>Add many items or stock counts at once. Upload a spreadsheet, check the preview, then confirm. Nothing changes until you confirm.</p>
                </div>
            </div>
        </div>

        <div class="quick-actions">
            <a href="<?= htmlspecialchars($base) ?>/hr/uniforms" class="quick-action-btn qa-secondary"><i class="fas fa-arrow-left"></i> Back to Inventory</a>
            <a href="<?= htmlspecialchars($base) ?>/hr/uniforms/import/template" class="quick-action-btn qa-success"><i class="fas fa-download"></i> Download Template</a>
        </div>

        <?php foreach (['successMessage' => 'success', 'errorMessage' => 'danger'] as $key => $type): ?>
            <?php if (!empty($_SESSION[$key])): ?>
                <div class="alert alert-<?= $type ?> alert-modern"><?= htmlspecialchars($_SESSION[$key]) ?></div>
                <?php unset($_SESSION[$key]); ?>
            <?php endif; ?>
        <?php endforeach; ?>

        <?php if (!$import): ?>
        <div class="row">
            <div class="col-lg-6 mb-4">
                <div class="card shadow uniform-card data-card">
                    <div class="card-header py-3"><h6><i class="fas fa-upload"></i> Upload</h6></div>
                    <div class="card-body">
                        <form method="POST" action="<?= htmlspecialchars($base) ?>/hr/uniforms/import/preview" enctype="multipart/form-data">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                            <div class="form-group">
                                <label for="import_file" class="font-weight-bold">Spreadsheet (.xlsx or .csv, max 2 MB)</label>
                                <input type="file" class="form-control-file" id="import_file" name="import_file" accept=".xlsx,.csv" required>
                            </div>
                            <div class="form-group">
                                <label class="font-weight-bold d-block">Quantity column means</label>
                                <div class="custom-control custom-radio">
                                    <input type="radio" id="modeAdd" name="mode" value="add" class="custom-control-input" checked>
                                    <label class="custom-control-label" for="modeAdd">New stock received — <strong>add</strong> to current stock</label>
                                </div>
                                <div class="custom-control custom-radio">
                                    <input type="radio" id="modeSet" name="mode" value="set" class="custom-control-input">
                                    <label class="custom-control-label" for="modeSet">Physical count — <strong>replace</strong> current stock</label>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary"><i class="fas fa-search mr-1"></i> Preview Import</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 mb-4">
                <div class="card shadow uniform-card data-card">
                    <div class="card-header py-3"><h6><i class="fas fa-info-circle"></i> File format</h6></div>
                    <div class="card-body small">
                        <p>The first row must be a header row. Columns can be in any order:</p>
                        <ul class="mb-2">
                            <li><strong>Item Type</strong> (required) — e.g. New Polo Shirt</li>
                            <li><strong>Size</strong> (required) — e.g. M, One Size</li>
                            <li><strong>Quantity</strong> (required) — whole number</li>
                            <li>Color, Reorder Level, Cost per Unit, Supplier (optional)</li>
                        </ul>
                        <p class="mb-0">A row whose Item Type, Size and Color match an existing item updates that item; otherwise a new item is created. Start from the template to avoid header mistakes.</p>
                    </div>
                </div>
            </div>
        </div>
        <?php else: ?>
        <div class="card shadow uniform-card data-card mb-4">
            <div class="card-header py-3 d-flex flex-wrap justify-content-between align-items-center">
                <h6 class="mb-0"><i class="fas fa-eye"></i> Preview: <?= htmlspecialchars($import['filename']) ?></h6>
                <span class="small text-muted">
                    <?= count($validRows) ?> ready (<?= $newCount ?> new, <?= count($validRows) - $newCount ?> existing) ·
                    <?= count($rowErrors) ?> with errors ·
                    mode: <strong><?= $import['mode'] === 'set' ? 'replace stock' : 'add to stock' ?></strong>
                </span>
            </div>
            <div class="card-body">
                <?php if ($rowErrors): ?>
                    <div class="alert alert-warning">
                        <strong><?= count($rowErrors) ?> row(s) will be skipped:</strong>
                        <ul class="mb-0 mt-1">
                            <?php foreach ($rowErrors as $line => $message): ?>
                                <li>Row <?= (int) $line ?>: <?= htmlspecialchars($message) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?php if ($validRows): ?>
                <div class="table-responsive">
                    <table class="table table-sm table-hover uniforms-table">
                        <thead>
                            <tr>
                                <th>Row</th><th>Action</th><th>Item</th><th>Size</th><th>Color</th>
                                <th>Stock now</th><th>Quantity</th><th>Stock after</th><th>Reorder</th><th>Cost</th><th>Supplier</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($validRows as $r):
                                $isNew = $r['existing_id'] === null;
                                $after = $isNew ? $r['quantity'] : ($import['mode'] === 'set' ? $r['quantity'] : $r['existing_stock'] + $r['quantity']);
                            ?>
                                <tr>
                                    <td><?= (int) $r['excel_row'] ?></td>
                                    <td>
                                        <?php if ($isNew): ?>
                                            <span class="badge badge-success">New item</span>
                                        <?php else: ?>
                                            <span class="badge badge-info">Update</span>
                                            <?php if (strtoupper((string) $r['existing_status']) !== 'ACTIVE' && $r['quantity'] > 0): ?>
                                                <span class="badge badge-warning">Reactivate</span>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($r['type']) ?></td>
                                    <td><?= htmlspecialchars($r['size']) ?></td>
                                    <td><?= htmlspecialchars($r['color'] ?: '—') ?></td>
                                    <td><?= $isNew ? '—' : (int) $r['existing_stock'] ?></td>
                                    <td><?= $import['mode'] === 'set' || $isNew ? '' : '+' ?><?= (int) $r['quantity'] ?></td>
                                    <td class="font-weight-bold"><?= (int) $after ?></td>
                                    <td><?= $r['reorder'] === null ? '—' : (int) $r['reorder'] ?></td>
                                    <td><?= $r['cost'] === null ? '—' : number_format((float) $r['cost'], 2) ?></td>
                                    <td><?= htmlspecialchars($r['supplier'] ?? '—') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>

                <div class="d-flex flex-wrap mt-3">
                    <?php if ($validRows): ?>
                    <form method="POST" action="<?= htmlspecialchars($base) ?>/hr/uniforms/import/apply" class="mr-2 mb-2">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                        <input type="hidden" name="import_token" value="<?= htmlspecialchars($import['token']) ?>">
                        <button type="submit" class="btn btn-success"><i class="fas fa-check mr-1"></i> Import <?= count($validRows) ?> row(s)</button>
                    </form>
                    <?php endif; ?>
                    <form method="POST" action="<?= htmlspecialchars($base) ?>/hr/uniforms/import/cancel" class="mb-2">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                        <button type="submit" class="btn btn-outline-secondary">Cancel / upload another file</button>
                    </form>
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
