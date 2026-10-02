<?php
$base = rtrim(BASE_URL, '/');
$resultCount = count($uniforms ?? []);
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
    <link href="<?= htmlspecialchars($base) ?>/assets/css/hr-dashboard.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/hr-uniforms.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/role-list-page.css" rel="stylesheet">
</head>
<body id="page-top">
    <div id="wrapper">
    <?php 
    $activePage = 'uniforms';
    require_once dirname(dirname(__DIR__)) . '/partials/uniform_sidebar_topbar.php';?>
        <div class="container-fluid hr-dashboard-page hr-uniform-page role-list-page">

            <div class="page-hero">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <h1><i class="fas fa-search mr-2"></i>Uniform Search Results</h1>
                        <p>Matching uniform inventory items from your search.</p>
                    </div>
                    <div class="col-lg-4 mt-3 mt-lg-0 text-lg-right">
                        <div class="hero-stat d-inline-block text-center px-4">
                            <div class="stat-value"><?= (int) $resultCount ?></div>
                            <div class="stat-label">Results</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card uniform-card data-card shadow">
                <div class="card-header">
                    <h6><i class="fas fa-tshirt mr-1"></i>Search Results</h6>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($uniforms)): ?>
                        <div class="empty-state p-4">
                            <p class="text-muted mb-0">No uniforms match your search.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Type</th>
                                        <th>Size</th>
                                        <th>In Stock</th>
                                        <th>Reorder Level</th>
                                        <th>Return</th>
                                        <th class="text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($uniforms as $uniform):
                                        $itemStatus = strtoupper($uniform['status'] ?? 'ACTIVE');
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
                                            <td class="text-right">
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
