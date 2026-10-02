<?php
$base = rtrim(BASE_URL, '/');
$departments = $departments ?? [];
$totalDepartments = count($departments);
?>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Storage Mart | Manage Departments</title>
    <link href="<?= htmlspecialchars($base) ?>/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/storagemart.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/admin-users.css" rel="stylesheet">
    <link rel="icon" href="<?= htmlspecialchars($base) ?>/assets/img/favicon.ico" type="image/x-icon">
</head>

<body id="page-top">

    <div id="wrapper">
        <?php
        $activePage = 'users';
        $userSubPage = 'department';
        require_once __DIR__ . '/../../partials/admin/sidebar_topbar.php';
        ?>

        <div class="container-fluid admin-users-page">

            <div class="page-hero hero-accounts">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <h1><i class="fas fa-building mr-2"></i>Manage Departments</h1>
                        <p>Add, rename, or remove department options used on Add/Edit Account.</p>
                    </div>
                    <div class="col-lg-4">
                        <div class="hero-stat mt-3 mt-lg-0">
                            <div class="stat-value"><?= (int) $totalDepartments ?></div>
                            <div class="stat-label">Departments</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card form-card shadow mb-4">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">Add Department</h6>
                </div>
                <div class="card-body">
                    <form action="<?= htmlspecialchars($base) ?>/admin/department" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                        <div class="row form-row-gap">
                            <div class="col-md-5">
                                <label for="code" class="form-label">Code <span class="text-danger">*</span></label>
                                <input type="text" name="code" class="form-control" id="code" placeholder="e.g. Construction" required>
                                <small class="form-text text-muted">Stored value. Avoid changing this later for a department already in use without checking existing employees first.</small>
                            </div>
                            <div class="col-md-5">
                                <label for="label" class="form-label">Display Label <span class="text-danger">*</span></label>
                                <input type="text" name="label" class="form-control" id="label" placeholder="e.g. Construction" required>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100" name="btnSubmit">
                                    <i class="fas fa-save mr-1"></i> Add
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card data-list-card shadow mb-4">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-list-ul mr-1"></i> Department List
                    </h6>
                    <span class="badge badge-info"><?= (int) $totalDepartments ?> department<?= $totalDepartments === 1 ? '' : 's' ?></span>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($departments)): ?>
                        <div class="empty-state">
                            <i class="fas fa-building d-block"></i>
                            No departments found. Add your first one above.
                        </div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>Display Label</th>
                                    <th class="text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($departments as $dept): ?>
                                    <tr>
                                        <td><span class="asset-number"><?= htmlspecialchars((string) ($dept['code'] ?? '')) ?></span></td>
                                        <td><?= htmlspecialchars((string) ($dept['label'] ?? '')) ?></td>
                                        <td class="text-right">
                                            <div class="action-btn-group">
                                                <a href="<?= htmlspecialchars($base) ?>/admin/department/update?department_id=<?= (int) ($dept['department_id'] ?? 0) ?>"
                                                   class="btn btn-sm btn-outline-primary" title="Edit department">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="<?= htmlspecialchars($base) ?>/admin/department/delete?department_id=<?= (int) ($dept['department_id'] ?? 0) ?>"
                                                   class="btn btn-sm btn-outline-danger" title="Delete department"
                                                   onclick="return confirm('Delete this department? This only works if no employee is currently assigned to it.');">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
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

    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/jquery/jquery.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/js/sb-admin-2.min.js"></script>
    <?php require __DIR__ . '/../../partials/flash_modal.php'; ?>
</body>

</html>
