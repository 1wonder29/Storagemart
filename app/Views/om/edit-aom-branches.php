<?php
$base = rtrim(BASE_URL, '/');
$routePrefix = $routePrefix ?? 'hom';
$assignedIds = array_column($assigned_branches ?? [], 'branch_id');
$aomName = trim(($aom['firstname'] ?? '') . ' ' . ($aom['lastname'] ?? ''));
$branchCount = count($branches ?? []);
$assignedCount = count($assignedIds);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Storage Mart | Edit AOM Branches</title>

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
        <div class="container-fluid om-dashboard-page om-branches-page role-form-page">

            <div class="page-hero">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <h1><i class="fas fa-edit mr-2"></i>Assign Branches for AOM</h1>
                        <p>Select the branches <?= htmlspecialchars($aomName) ?> is responsible for.</p>
                    </div>
                    <div class="col-lg-4 mt-3 mt-lg-0 text-lg-right">
                        <div class="row mb-3 mb-lg-0">
                            <div class="col-6">
                                <div class="hero-stat">
                                    <div class="stat-value"><?= (int) $assignedCount ?></div>
                                    <div class="stat-label">Assigned</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="hero-stat">
                                    <div class="stat-value"><?= (int) $branchCount ?></div>
                                    <div class="stat-label">Available</div>
                                </div>
                            </div>
                        </div>
                        <a href="<?= htmlspecialchars($base) ?>/<?= htmlspecialchars($routePrefix) ?>/aom-branches" class="btn btn-light btn-sm shadow-sm">
                            <i class="fas fa-arrow-left fa-sm"></i> Back to List
                        </a>
                    </div>
                </div>
            </div>

            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger alert-dismissible fade show alert-modern" role="alert">
                    <?= htmlspecialchars($_SESSION['error_message']) ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <?php unset($_SESSION['error_message']); ?>
            <?php endif; ?>

            <div class="card form-card shadow mb-4">
                <div class="card-header">
                    <h6><i class="fas fa-user-tie mr-1"></i><?= htmlspecialchars($aomName) ?></h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?= htmlspecialchars($base) ?>/<?= htmlspecialchars($routePrefix) ?>/edit-aom-branches?id=<?= (int)$aom['employee_id'] ?>">
                        <input type="hidden" name="aom_employee_id" value="<?= (int)$aom['employee_id'] ?>">

                        <label class="form-label font-weight-bold">Assign Branches</label>
                        <div class="form-control mb-3" style="height: auto; border: 1px solid #ddd; padding: 10px; max-height: 300px; overflow-y: auto;">
                            <?php foreach ($branches as $branch): ?>
                                <?php
                                $branchId = $branch['branch_id'];
                                $branchName = $branch['branchName'];
                                $isChecked = in_array($branchId, $assignedIds) ? ' checked' : '';
                                ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="aom_branch_ids[]"
                                           value="<?= htmlspecialchars($branchId) ?>"<?= $isChecked ?>
                                           id="branch_<?= htmlspecialchars($branchId) ?>">
                                    <label class="form-check-label" for="branch_<?= htmlspecialchars($branchId) ?>">
                                        <?= htmlspecialchars($branchName) ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <small class="form-text text-muted d-block mb-4">Select the branches this AOM is responsible for.</small>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">Save Assignments</button>
                            <a href="<?= htmlspecialchars($base) ?>/<?= htmlspecialchars($routePrefix) ?>/aom-branches" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>

            <?php
            $history_title = 'Branch Assignment Transfer History';
            require __DIR__ . '/../partials/aom/branch_assignment_history.php';
            ?>
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
