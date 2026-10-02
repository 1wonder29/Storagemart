<?php
$base = rtrim(BASE_URL, '/');
$department = $department ?? [];
?>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Storage Mart | Edit Department</title>
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
                    <div class="col-12">
                        <h1><i class="fas fa-building mr-2"></i>Edit Department</h1>
                        <p>Changing the code moves every employee currently using it to the new code automatically.</p>
                    </div>
                </div>
            </div>

            <div class="card form-card shadow mb-4">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">Department Details</h6>
                </div>
                <div class="card-body">
                    <form action="<?= htmlspecialchars($base) ?>/admin/department/update" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                        <input type="hidden" name="department_id" value="<?= htmlspecialchars((string) ($department['department_id'] ?? '')) ?>">
                        <div class="row form-row-gap">
                            <div class="col-md-6">
                                <label for="code" class="form-label">Code <span class="text-danger">*</span></label>
                                <input type="text" name="code" class="form-control" id="code" value="<?= htmlspecialchars((string) ($department['code'] ?? '')) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="label" class="form-label">Display Label <span class="text-danger">*</span></label>
                                <input type="text" name="label" class="form-control" id="label" value="<?= htmlspecialchars((string) ($department['label'] ?? '')) ?>" required>
                            </div>
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary" name="btnSubmit">
                                <i class="fas fa-save mr-1"></i> Save Changes
                            </button>
                            <a href="<?= htmlspecialchars($base) ?>/admin/department" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
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
