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
                    <p>Add many items or stock counts at once. Upload a spreadsheet, check the preview, then send it for approval.
                        The stock changes only when another HR account or the Admin approves it.</p>
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
                            <p class="small text-muted">The file is read first and shown to you. Then you choose whether to
                                <strong>add</strong> its rows to the current list or <strong>overwrite</strong> the list with it.</p>
                            <button type="submit" class="btn btn-primary"><i class="fas fa-search mr-1"></i> Read file &amp; preview</button>
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
                            <li>Color, Reorder Level, Cost per Unit (optional)</li>
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
                    <?php if ($import['mode'] === null): ?>
                        <strong class="text-warning">choose how to import</strong>
                    <?php else: ?>
                        <strong><?= $import['mode'] === 'replace' ? 'Overwrite the list' : 'Add new rows' ?></strong>
                    <?php endif; ?>
                    · <a href="#" data-toggle="modal" data-target="#importModeModal">change</a>
                </span>
            </div>
            <div class="card-body">
                <?php if ($import['mode'] === 'replace' && !empty($toDiscontinue)): ?>
                    <div class="alert alert-warning">
                        <strong>Overwrite the list:</strong> <?= count($toDiscontinue) ?> active item(s) are not in the file and will be
                        <strong>discontinued</strong> (their history is kept; you can reactivate them later):
                        <?= htmlspecialchars(implode(', ', array_map(static fn($i) => $i['uniform_type'] . ' (' . $i['size'] . ')', array_slice($toDiscontinue, 0, 15)))) ?><?= count($toDiscontinue) > 15 ? '…' : '' ?>
                    </div>
                <?php endif; ?>
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
                                <th>Stock now</th><th>Quantity</th><th>Stock after</th><th>Reorder</th><th>Cost</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($validRows as $r):
                                $isNew = $r['existing_id'] === null;
                                $replace = $import['mode'] === 'replace';
                                $after = $isNew ? $r['quantity'] : ($replace ? $r['quantity'] : $r['existing_stock'] + $r['quantity']);
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
                                    <td><?= $replace || $isNew ? '' : '+' ?><?= (int) $r['quantity'] ?></td>
                                    <td class="font-weight-bold"><?= (int) $after ?></td>
                                    <td><?= $r['reorder'] === null ? '—' : (int) $r['reorder'] ?></td>
                                    <td><?= $r['cost'] === null ? '—' : number_format((float) $r['cost'], 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>

                <div class="d-flex flex-wrap mt-3">
                    <?php if ($validRows && $import['mode'] !== null): ?>
                    <form method="POST" action="<?= htmlspecialchars($base) ?>/hr/uniforms/import/apply" class="mr-2 mb-2">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                        <input type="hidden" name="import_token" value="<?= htmlspecialchars($import['token']) ?>">
                        <button type="submit" class="btn btn-success"><i class="fas fa-paper-plane mr-1"></i>
                            Send for approval (<?= $import['mode'] === 'replace' ? 'overwrite the list' : 'add ' . count($validRows) . ' row(s)' ?>)</button>
                    </form>
                    <?php elseif ($validRows): ?>
                    <button type="button" class="btn btn-primary mr-2 mb-2" data-toggle="modal" data-target="#importModeModal">
                        <i class="fas fa-question-circle mr-1"></i> Choose how to import</button>
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
<?php if ($import && !empty($import['rows'])): ?>
<!-- Asked after the file was read: add its rows, or overwrite the list with it. -->
<div class="modal fade" id="importModeModal" tabindex="-1" role="dialog" aria-labelledby="importModeTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importModeTitle"><i class="fas fa-file-import mr-1"></i> How should this file be imported?</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <p class="small text-muted mb-3"><?= htmlspecialchars($import['filename']) ?>: <?= count($import['rows']) ?> valid row(s).</p>
                <form method="POST" action="<?= htmlspecialchars($base) ?>/hr/uniforms/import/mode" class="mb-2">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                    <input type="hidden" name="mode" value="add">
                    <button type="submit" class="btn btn-outline-primary btn-block text-left p-3">
                        <strong><i class="fas fa-plus-circle mr-1"></i> Add new rows / stock</strong>
                        <div class="small">New items are added and quantities are <strong>added</strong> to current stock. Everything else stays as it is.</div>
                    </button>
                </form>
                <form method="POST" action="<?= htmlspecialchars($base) ?>/hr/uniforms/import/mode">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                    <input type="hidden" name="mode" value="replace">
                    <button type="submit" class="btn btn-outline-danger btn-block text-left p-3">
                        <strong><i class="fas fa-sync-alt mr-1"></i> Overwrite the list</strong>
                        <div class="small">The inventory becomes this file: stock is <strong>set</strong> to the file's quantity, and items not in the file are discontinued.</div>
                    </button>
                </form>
                <p class="small text-muted mt-3 mb-0">You will see the preview again. Then you send it for approval; nothing is saved before it is approved.</p>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
<script src="<?= htmlspecialchars($base) ?>/assets/vendor/jquery/jquery.min.js"></script>
<script src="<?= htmlspecialchars($base) ?>/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="<?= htmlspecialchars($base) ?>/assets/js/storagemart.min.js"></script>
<?php if ($import && !empty($import['rows']) && $import['mode'] === null): ?>
<script>$(function () { $('#importModeModal').modal('show'); });</script>
<?php endif; ?>
</body>
</html>
