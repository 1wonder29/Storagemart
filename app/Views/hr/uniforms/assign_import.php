<?php
$base = rtrim(BASE_URL, '/');
$import = $import ?? null;
$validRows = $import['rows'] ?? [];
$rowErrors = $import['errors'] ?? [];
$people = count(array_unique(array_column($validRows, 'employee_id')));
$pieces = array_sum(array_column($validRows, 'quantity'));
$h = static fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Storage Mart | Assign Items from Excel</title>
    <link href="<?= $h($base) ?>/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900" rel="stylesheet">
    <link rel="icon" href="<?= $h($base) ?>/assets/img/sm-icon-32.png?v=20261016" type="image/x-icon">
    <link href="<?= $h($base) ?>/assets/css/storagemart.css" rel="stylesheet">
    <link href="<?= $h($base) ?>/assets/css/hr-dashboard.css?v=20261008" rel="stylesheet">
    <link href="<?= $h($base) ?>/assets/css/hr-uniforms.css" rel="stylesheet">
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
                    <h1><i class="fas fa-users mr-2"></i>Assign Items from Excel</h1>
                    <p>Issue items to many employees at once. Upload the list, check the preview, then send it for approval.
                        Nothing is issued and the stock does not change until another HR account or the Admin approves it.</p>
                </div>
            </div>
        </div>

        <div class="quick-actions">
            <a href="<?= $h($base) ?>/hr/uniforms" class="quick-action-btn qa-secondary"><i class="fas fa-arrow-left"></i> Back to Inventory</a>
            <a href="<?= $h($base) ?>/hr/uniforms/assign-import/template" class="quick-action-btn qa-success"><i class="fas fa-download"></i> Download Template</a>
            <a href="<?= $h($base) ?>/hr/uniforms/approvals" class="quick-action-btn qa-primary"><i class="fas fa-clipboard-check"></i> Approvals</a>
        </div>

        <?php foreach (['successMessage' => 'success', 'errorMessage' => 'danger'] as $key => $type): ?>
            <?php if (!empty($_SESSION[$key])): ?>
                <div class="alert alert-<?= $type ?> alert-modern"><?= $h($_SESSION[$key]) ?></div>
                <?php unset($_SESSION[$key]); ?>
            <?php endif; ?>
        <?php endforeach; ?>

        <?php if (!$import): ?>
        <div class="row">
            <div class="col-lg-6 mb-4">
                <div class="card shadow uniform-card data-card">
                    <div class="card-header py-3"><h6><i class="fas fa-upload"></i> Upload</h6></div>
                    <div class="card-body">
                        <ol class="small pl-3">
                            <li>Download the template and fill in one row per employee and item.</li>
                            <li>Upload it here. The TMS checks every row and shows you a preview.</li>
                            <li>Send it for approval. When it is approved, the items are issued, the stock goes down, and each employee is asked to confirm they received the item.</li>
                        </ol>
                        <form method="POST" action="<?= $h($base) ?>/hr/uniforms/assign-import/preview" enctype="multipart/form-data">
                            <input type="hidden" name="csrf_token" value="<?= $h($csrf_token ?? '') ?>">
                            <div class="form-group">
                                <label for="import_file" class="font-weight-bold">Spreadsheet (.xlsx or .csv, max 2 MB)</label>
                                <input type="file" class="form-control-file" id="import_file" name="import_file" accept=".xlsx,.csv" required>
                            </div>
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
                            <li><strong>Employee Email</strong> (the TMS username), or <strong>Employee ID</strong>, or <strong>Employee Name</strong> — the email is the safest</li>
                            <li><strong>Item Type</strong> and <strong>Size</strong> (required) — as written in the inventory</li>
                            <li><strong>Color</strong> — needed when the same item and size come in several colors</li>
                            <li><strong>Quantity</strong> (required) — 1 or more</li>
                            <li>Condition (Good, Fair or Used; Good when empty) and Remarks (optional)</li>
                        </ul>
                        <p class="mb-0">Rows are skipped when the employee is not found or is inactive, the item is not found or is
                            discontinued, or there is not enough stock for all the rows of that item.</p>
                    </div>
                </div>
            </div>
        </div>
        <?php else: ?>
        <div class="card shadow uniform-card data-card mb-4">
            <div class="card-header py-3 d-flex flex-wrap justify-content-between align-items-center">
                <h6 class="mb-0"><i class="fas fa-eye"></i> Preview: <?= $h($import['filename']) ?></h6>
                <span class="small text-muted"><?= count($validRows) ?> row(s) ready · <?= (int) $pieces ?> item(s) for <?= (int) $people ?> employee(s) ·
                    <?= count($rowErrors) ?> with errors</span>
            </div>
            <div class="card-body">
                <?php if ($rowErrors): ?>
                    <div class="alert alert-warning">
                        <strong><?= count($rowErrors) ?> row(s) will be skipped:</strong>
                        <ul class="mb-0 mt-1">
                            <?php foreach ($rowErrors as $line => $message): ?>
                                <li>Row <?= (int) $line ?>: <?= $h($message) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?php if ($validRows): ?>
                <div class="table-responsive">
                    <table class="table table-sm table-hover uniforms-table">
                        <thead>
                            <tr><th>Row</th><th>Employee</th><th>Item</th><th>Qty</th><th>Condition</th><th>Remarks</th><th>Stock now</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($validRows as $r): ?>
                                <tr>
                                    <td><?= (int) $r['excel_row'] ?></td>
                                    <td><strong><?= $h($r['employee_name']) ?></strong><div class="small text-muted"><?= $h($r['employee_info']) ?></div></td>
                                    <td><?= $h($r['item_label']) ?></td>
                                    <td><?= (int) $r['quantity'] ?></td>
                                    <td><?= $h(ucfirst(strtolower($r['condition']))) ?></td>
                                    <td><?= $h($r['remarks'] ?: '—') ?></td>
                                    <td><?= (int) $r['stock_now'] ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>

                <div class="d-flex flex-wrap mt-3">
                    <?php if ($validRows): ?>
                    <form method="POST" action="<?= $h($base) ?>/hr/uniforms/assign-import/submit" class="mr-2 mb-2">
                        <input type="hidden" name="csrf_token" value="<?= $h($csrf_token ?? '') ?>">
                        <input type="hidden" name="import_token" value="<?= $h($import['token']) ?>">
                        <button type="submit" class="btn btn-success"><i class="fas fa-paper-plane mr-1"></i>
                            Send for approval (<?= count($validRows) ?> row<?= count($validRows) === 1 ? '' : 's' ?>)</button>
                    </form>
                    <?php endif; ?>
                    <form method="POST" action="<?= $h($base) ?>/hr/uniforms/assign-import/cancel" class="mb-2">
                        <input type="hidden" name="csrf_token" value="<?= $h($csrf_token ?? '') ?>">
                        <button type="submit" class="btn btn-outline-secondary">Cancel / upload another file</button>
                    </form>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
</div>
<script src="<?= $h($base) ?>/assets/vendor/jquery/jquery.min.js"></script>
<script src="<?= $h($base) ?>/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="<?= $h($base) ?>/assets/js/storagemart.min.js"></script>
</body>
</html>
