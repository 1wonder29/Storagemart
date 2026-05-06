<?php
$base = rtrim(BASE_URL, '/');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Storage Mart | Search Uniforms</title>
    <link href="<?= htmlspecialchars($base) ?>/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900" rel="stylesheet">
    <link rel="icon" href="<?= htmlspecialchars($base) ?>/assets/img/sm_favicon.png" type="image/x-icon">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/storagemart.css" rel="stylesheet">
</head>
<body id="page-top">
    <div id="wrapper">
    <?php 
    $activePage = 'uniforms';
    require_once dirname(dirname(__DIR__)) . '/partials/hr/sidebar_topbar.php';?>
        <div class="container-fluid">
            <h1 class="h3 mb-4 text-gray-800">Uniform Search Results</h1>

            <div class="card shadow">
                <div class="card-body">
                    <form method="GET" action="<?= htmlspecialchars($base) ?>/hr/uniforms/search" class="form-inline mb-3">
                        <input type="text" name="q" class="form-control mr-2" value="<?= htmlspecialchars($searchTerm ?? '') ?>" placeholder="Search by type or size..." required>
                        <button type="submit" class="btn btn-primary">Search</button>
                        <a href="<?= htmlspecialchars($base) ?>/hr/uniforms" class="btn btn-secondary ml-2">View All</a>
                    </form>

                    <?php if (empty($uniforms)): ?>
                        <p class="text-muted">No uniforms found matching your search.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Uniform Type</th>
                                        <th>Size</th>
                                        <th>Quantity</th>
                                        <th>Level</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($uniforms as $uniform): 
                                        $levelPercentage = ($uniform['quantity_in_stock'] / $uniform['reorder_level']) * 100;
                                        $levelColor = ($levelPercentage > 100) ? 'success' : (($levelPercentage > 50) ? 'warning' : 'danger');
                                    ?>
                                        <tr>
                                            <td><strong><?= htmlspecialchars($uniform['uniform_type']) ?></strong></td>
                                            <td><?= htmlspecialchars($uniform['size']) ?></td>
                                            <td><?= (int)$uniform['quantity_in_stock'] ?></td>
                                            <td>
                                                <div class="progress" style="height: 20px;">
                                                    <div class="progress-bar bg-<?= $levelColor ?>" role="progressbar" 
                                                         style="width: <?= min(100, $levelPercentage) ?>%"
                                                         aria-valuenow="<?= $levelPercentage ?>" aria-valuemin="0" aria-valuemax="100">
                                                        <?= (int)$levelPercentage ?>%
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <a href="<?= htmlspecialchars($base) ?>/hr/uniforms/edit/<?= $uniform['uniform_id'] ?>" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-edit"></i>
                                                </a>
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

    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/jquery/jquery.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/js/storagemart.min.js"></script>
</body>
</html>
