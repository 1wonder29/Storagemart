<?php
$base = rtrim(BASE_URL, '/');
$isIt = ($role ?? '') === 'IT';
$items = $items ?? [];
$h = static function ($value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};
$byCategory = [];
foreach ($items as $item) {
    $byCategory[(string) $item['categoryName']] = ($byCategory[(string) $item['categoryName']] ?? 0) + 1;
}
arsort($byCategory);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Storage Mart | Assets for Replacement</title>
    <link href="<?= $h($base) ?>/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900" rel="stylesheet">
    <link href="<?= $h($base) ?>/assets/css/storagemart.css" rel="stylesheet">
    <link href="<?= $h($base) ?>/assets/css/admin-users.css" rel="stylesheet">
    <link rel="icon" href="<?= $h($base) ?>/assets/img/sm_favicon.png" type="image/x-icon">
    <?php if ($isIt) { require_once __DIR__ . '/../partials/it/theme_head.php'; } ?>
    <style>
        .cat-chip { display:inline-block; padding:3px 10px; margin:0 6px 6px 0; border-radius:999px; font-size:.78rem; font-weight:700;
                    background:#fff7ed; color:#c2410c; border:1px solid #fed7aa; cursor:pointer; }
        .cat-chip.active { background:#f26522; color:#fff; border-color:#f26522; }
        .age-pill { display:inline-block; padding:1px 8px; border-radius:999px; font-weight:800; font-size:.75rem; background:#fee2e2; color:#b91c1c; }
    </style>
</head>

<body id="page-top">

    <div id="wrapper">
        <?php
        if ($isIt) {
            $activePage = 'asset-replacement';
            require_once __DIR__ . '/../partials/it/sidebar_topbar.php';
        } else {
            $activePage = 'assets';
            require_once __DIR__ . '/../partials/admin/sidebar_topbar.php';
        }
        ?>

        <div class="container-fluid admin-users-page">

            <div class="page-hero hero-accounts">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <h1><i class="fas fa-history mr-2"></i>Assets for Replacement</h1>
                        <p>Every asset (TV, CCTV, PC set, laptop and all other types) bought <?= (int) $years ?> or more years ago,
                            based on Year Purchased. Oldest first. Disposed and lost items are not listed.</p>
                    </div>
                    <div class="col-lg-4">
                        <div class="hero-stat mt-3 mt-lg-0">
                            <div class="stat-value"><?= count($items) ?></div>
                            <div class="stat-label">Due for replacement</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-8 mb-3">
                    <?php if ($byCategory): ?>
                        <span class="cat-chip active" data-cat="">All (<?= count($items) ?>)</span>
                        <?php foreach ($byCategory as $cat => $count): ?>
                            <span class="cat-chip" data-cat="<?= $h(strtolower($cat)) ?>"><?= $h($cat) ?> (<?= (int) $count ?>)</span>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <?php if (!$isIt): ?>
                <div class="col-lg-4 mb-3">
                    <form method="POST" action="<?= $h($base . $home) ?>" class="form-inline justify-content-lg-end">
                        <input type="hidden" name="csrf_token" value="<?= $h($csrf_token ?? '') ?>">
                        <label for="rep_years" class="mr-2 small font-weight-bold">Tag items at</label>
                        <div class="input-group input-group-sm" style="width:150px">
                            <input type="number" min="1" max="30" class="form-control" id="rep_years" name="years" value="<?= (int) $years ?>">
                            <div class="input-group-append"><span class="input-group-text">years</span></div>
                        </div>
                        <button type="submit" class="btn btn-sm btn-primary ml-2">Save</button>
                    </form>
                </div>
                <?php endif; ?>
            </div>

            <div class="card data-list-card shadow mb-4">
                <div class="card-body p-0">
                    <?php if (!$items): ?>
                        <p class="text-muted p-4 mb-0"><i class="fas fa-check-circle text-success mr-1"></i>
                            No assets are <?= (int) $years ?> or more years old. Items without a Year Purchased are not checked.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0" id="replacementTable">
                                <thead>
                                    <tr><th>Age</th><th>Year</th><th>Category</th><th>Model</th><th>Item</th><th>Asset #</th><th>Branch</th><th>Status</th><th>Employee</th></tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($items as $item): $age = AssetAge::age($item['year_purchased']); ?>
                                        <tr data-cat="<?= $h(strtolower((string) $item['categoryName'])) ?>">
                                            <td><span class="age-pill"><?= (int) $age ?> yrs</span></td>
                                            <td><?= $h($item['year_purchased']) ?></td>
                                            <td><?= $h($item['categoryName']) ?></td>
                                            <td>
                                                <?php if (!$isIt): ?>
                                                    <a href="<?= $h($base) ?>/admin/assets/item?group_id=<?= (int) $item['group_id'] ?>"><?= $h($item['groupName']) ?></a>
                                                <?php else: ?>
                                                    <?= $h($item['groupName']) ?>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= $h($item['itemInfo']) ?></td>
                                            <td><?= $h($item['assetNumber']) ?></td>
                                            <td><?= $h($item['branchName'] ?: '—') ?></td>
                                            <td><?= $h($item['status']) ?></td>
                                            <td><?= $h(trim((string) $item['employeeName']) ?: '—') ?></td>
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

    <script src="<?= $h($base) ?>/assets/vendor/jquery/jquery.min.js"></script>
    <script src="<?= $h($base) ?>/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="<?= $h($base) ?>/assets/vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="<?= $h($base) ?>/assets/js/sb-admin-2.min.js"></script>
    <script>
        document.querySelectorAll('.cat-chip').forEach(function (chip) {
            chip.addEventListener('click', function () {
                var cat = chip.getAttribute('data-cat');
                document.querySelectorAll('.cat-chip').forEach(function (c) { c.classList.toggle('active', c === chip); });
                document.querySelectorAll('#replacementTable tbody tr').forEach(function (row) {
                    row.style.display = !cat || row.getAttribute('data-cat') === cat ? '' : 'none';
                });
            });
        });
    </script>
    <?php require __DIR__ . '/../partials/flash_modal.php'; ?>
</body>

</html>
