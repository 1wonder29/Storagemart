<?php
$base = rtrim(BASE_URL, '/');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Storage Mart | Delete Uniform</title>
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
        <div class="container-fluid hr-dashboard-page hr-uniform-page role-form-page">

            <div class="page-hero hero-danger">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <h1><i class="fas fa-trash-alt mr-2"></i>Delete Uniform</h1>
                        <p>Review the impact before removing this uniform from inventory.</p>
                    </div>
                    <div class="col-lg-4 mt-3 mt-lg-0 text-lg-right">
                        <a href="<?= htmlspecialchars($base) ?>/hr/uniforms" class="btn btn-light btn-sm">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                    </div>
                </div>
            </div>

            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6">
                    <div class="card uniform-card form-card shadow border-danger">
                        <div class="card-header bg-danger text-white">
                            <h6 class="m-0 text-white"><i class="fas fa-exclamation-triangle mr-1"></i>Confirm Deletion</h6>
                        </div>
                        <div class="card-body">
                            <p class="mb-3"><strong>Uniform:</strong> <?= htmlspecialchars($uniform['uniform_type'] . ' - ' . $uniform['size'] . ' - ' . $uniform['color']) ?></p>
                            
                            <?php if ($isInUse): ?>
                                <div class="alert alert-warning alert-modern">
                                    <i class="fas fa-exclamation-triangle"></i> This uniform has active assignments and cannot be deleted.
                                    It will be marked as DISCONTINUED instead.
                                </div>
                            <?php else: ?>
                                <div class="alert alert-danger alert-modern">
                                    <i class="fas fa-exclamation-triangle"></i> Are you sure you want to delete this uniform? This action cannot be undone.
                                </div>
                            <?php endif; ?>

                            <div class="form-actions mt-4">
                                <form method="POST" action="<?= htmlspecialchars($base) ?>/hr/uniforms/delete/<?= $uniform['uniform_id'] ?>" class="d-inline">
                                    <button type="submit" class="btn btn-danger">
                                        <i class="fas fa-trash"></i> Delete Uniform
                                    </button>
                                </form>
                                <a href="<?= htmlspecialchars($base) ?>/hr/uniforms" class="btn btn-secondary">Cancel</a>
                            </div>
                        </div>
                    </div>
                </div>
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
