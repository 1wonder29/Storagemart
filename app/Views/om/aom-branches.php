<?php
$base = rtrim(BASE_URL, '/');
$routePrefix = $routePrefix ?? 'hom';
$aomCount = count($aoms ?? []);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Storage Mart | AOM Branch Assignments</title>

    <link href="<?= htmlspecialchars($base) ?>/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900" rel="stylesheet">
    <link rel="icon" href="<?= htmlspecialchars($base) ?>/assets/img/sm_favicon.png" type="image/x-icon">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/storagemart.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/om-dashboard.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/role-list-page.css" rel="stylesheet">
</head>

<body id="page-top">
    <div id="wrapper">
    <?php
    $activePage = 'aom-branches';
    require_once __DIR__ . '/../partials/om/sidebar_topbar.php';
    ?>
        <div class="container-fluid om-dashboard-page om-branches-page role-list-page">

            <div class="page-hero">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <h1><i class="fas fa-sitemap mr-2"></i>AOM Branch Assignments</h1>
                        <p>Manage which branches each Area Operations Manager is responsible for.</p>
                    </div>
                    <div class="col-lg-4 mt-3 mt-lg-0 text-lg-right">
                        <div class="hero-stat d-inline-block text-center px-4">
                            <div class="stat-value"><?= (int) $aomCount ?></div>
                            <div class="stat-label">Active AOMs</div>
                        </div>
                    </div>
                </div>
            </div>

            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success alert-dismissible fade show alert-modern" role="alert">
                    <?= htmlspecialchars($_SESSION['success_message']) ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <?php unset($_SESSION['success_message']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger alert-dismissible fade show alert-modern" role="alert">
                    <?= htmlspecialchars($_SESSION['error_message']) ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <?php unset($_SESSION['error_message']); ?>
            <?php endif; ?>

            <div class="card ticket-list-card shadow mb-4">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h6><i class="fas fa-users-cog mr-1"></i>Area Operation Managers</h6>
                    <span class="ticket-count-badge"><?= (int) $aomCount ?> AOM<?= $aomCount === 1 ? '' : 's' ?></span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>AOM</th>
                                    <th>Email</th>
                                    <th>Assigned Branches</th>
                                    <th class="text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($aoms)): ?>
                                    <?php foreach ($aoms as $aom): ?>
                                    <tr>
                                        <td><?= htmlspecialchars(trim(($aom['firstname'] ?? '') . ' ' . ($aom['lastname'] ?? ''))) ?></td>
                                        <td><?= htmlspecialchars($aom['email'] ?? '') ?></td>
                                        <td>
                                            <?php if (!empty($aom['branches'])): ?>
                                                <?php
                                                $branchNames = array_map(function ($b) {
                                                    return $b['branchName'] ?? '';
                                                }, $aom['branches']);
                                                echo htmlspecialchars(implode(', ', array_filter($branchNames)));
                                                ?>
                                            <?php else: ?>
                                                <span class="text-muted">No branches assigned</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-right">
                                            <a href="<?= htmlspecialchars($base) ?>/<?= htmlspecialchars($routePrefix) ?>/edit-aom-branches?id=<?= (int)$aom['employee_id'] ?>"
                                               class="btn btn-sm btn-primary">
                                                <i class="fas fa-edit"></i> Assign Branches
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">No active AOM accounts found</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

            </div>
<a class="scroll-to-top rounded" href="#page-top">
    <i class="fas fa-angle-up"></i>
</a>

<script src="<?= htmlspecialchars($base) ?>/assets/vendor/jquery/jquery.min.js"></script>
<script src="<?= htmlspecialchars($base) ?>/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="<?= htmlspecialchars($base) ?>/assets/vendor/jquery-easing/jquery.easing.min.js"></script>
<script src="<?= htmlspecialchars($base) ?>/assets/js/sb-admin-2.min.js"></script>
</body>
</html>
