<?php
$base = rtrim(BASE_URL, '/');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Storage Mart | Uniforms - Search</title>
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
            <h1 class="h3 mb-4 text-gray-800">Search Results</h1>

            <div class="card shadow">
                <div class="card-body">
                    <?php if (empty($uniforms)): ?>
                        <p class="text-muted">No uniforms match your search.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Type</th>
                                        <th>Size</th>
                                        <th>In Stock</th>
                                        <th>Reorder Level</th>
                                        <th>Return</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($uniforms as $uniform):
                                        $itemStatus = strtoupper($uniform['status'] ?? 'ACTIVE');
                                        $statusBadgeColor = ($itemStatus === 'ACTIVE') ? 'success' : 'secondary';
                                        $levelPercentage = $uniform['reorder_level'] ? ($uniform['quantity_in_stock'] / max(1, $uniform['reorder_level'])) * 100 : 0;
                                        $levelColor = ($levelPercentage > 100) ? 'success' : (($levelPercentage > 50) ? 'warning' : 'danger');
                                    ?>
                                        <tr>
                                            <td><?= htmlspecialchars($uniform['uniform_type'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($uniform['size'] ?? '') ?></td>
                                            <td><?= (int)$uniform['quantity_in_stock'] ?></td>
                                            <td><?= (int)$uniform['reorder_level'] ?></td>
                                            <td>
                                                <span class="btn btn-sm btn-info" title="Active uniforms to return">
                                                    <?= (int)($uniform['return_count'] ?? 0) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="<?= htmlspecialchars($base) ?>/hr/uniforms/edit/<?= $uniform['uniform_id'] ?>" class="btn btn-sm btn-primary" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <?php if ($itemStatus === 'DISCONTINUED'): ?>
                                                    <form method="post"
                                                          action="<?= htmlspecialchars($base) ?>/hr/uniforms/reactivate/<?= (int) $uniform['uniform_id'] ?>"
                                                          class="d-inline"
                                                          onsubmit="return confirm('Reactivate this uniform?');">
                                                        <button type="submit" class="btn btn-sm btn-success" title="Reactivate">
                                                            <i class="fas fa-undo"></i>
                                                        </button>
                                                    </form>
                                                <?php else: ?>
                                                    <a href="<?= htmlspecialchars($base) ?>/hr/uniforms/delete/<?= $uniform['uniform_id'] ?>" class="btn btn-sm btn-danger" title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
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
</body>
</html>
