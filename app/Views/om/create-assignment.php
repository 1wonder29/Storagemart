<?php
$base = rtrim(BASE_URL, '/');
$routePrefix = (($user_role ?? '') === 'HOM') ? 'hom' : 'om';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Storage Mart | Create Assignment</title>

    <!-- Custom fonts for this template -->
    <link href="<?= htmlspecialchars($base) ?>/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900" rel="stylesheet">
    <link rel="icon" href="<?= htmlspecialchars($base) ?>/assets/img/sm_favicon.png" type="image/x-icon">

    <!-- Custom styles for this template -->
    <link href="<?= htmlspecialchars($base) ?>/assets/css/storagemart.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/searchable-select.css" rel="stylesheet">
</head>

<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper">
    <?php 
    $activePage = 'assignments';
    require_once __DIR__ . '/../partials/om/sidebar_topbar.php';?>
        <!-- Page Content -->
        <div class="container-fluid">
            <!-- Page Heading -->
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">Create Assignment</h1>
            </div>

            <!-- Messages -->
            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($_SESSION['error_message']) ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <?php unset($_SESSION['error_message']); ?>
            <?php endif; ?>

            <!-- Form Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-primary">
                    <h6 class="m-0 font-weight-bold text-white">Assign Employee to AOM</h6>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="form-group">
                            <label for="employee_id"><strong>Select Employee *</strong></label>
                            <select name="employee_id" id="employee_id" class="form-control" required>
                                <option value="">-- Choose an Employee --</option>
                                <?php if (!empty($unassigned_employees)): ?>
                                    <?php foreach ($unassigned_employees as $emp): ?>
                                    <option value="<?= $emp['employee_id'] ?>">
                                        <?= htmlspecialchars($emp['firstname'] . ' ' . $emp['lastname'] . ' (ID: ' . $emp['employee_id'] . ')') ?>
                                    </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="branch_id"><strong>Select Branch *</strong></label>
                            <select name="branch_id" id="branch_id" class="form-control" required>
                                <option value="">-- Choose a Branch --</option>
                                <?php if (!empty($branches)): ?>
                                    <?php foreach ($branches as $branch): ?>
                                    <option value="<?= $branch['branch_id'] ?>">
                                        <?= htmlspecialchars($branch['branchName'] . ' (' . $branch['branchCode'] . ')') ?>
                                    </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="aom_id"><strong>Select AOM *</strong></label>
                            <select name="aom_id" id="aom_id" class="form-control" required>
                                <option value="">-- Choose an AOM --</option>
                                <?php if (!empty($active_aoms)): ?>
                                    <?php foreach ($active_aoms as $aom): ?>
                                    <option value="<?= $aom['employee_id'] ?>">
                                        <?= htmlspecialchars($aom['firstname'] . ' ' . $aom['lastname']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="notes"><strong>Notes</strong></label>
                            <textarea name="notes" id="notes" class="form-control" rows="4" placeholder="Enter any assignment notes..."></textarea>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-check"></i> Create Assignment
                            </button>
                            <a href="<?= htmlspecialchars($base) ?>/<?= htmlspecialchars($routePrefix) ?>/assignments" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <!-- /.container-fluid -->

    </div>
    <!-- End of Main Content -->

</div>
<!-- End of Content Wrapper -->

</div>
<!-- End of Page Wrapper -->

<!-- Scroll to Top Button-->
<a class="scroll-to-top rounded" href="#page-top">
    <i class="fas fa-angle-up"></i>
</a>

<!-- Bootstrap core JavaScript-->
<script src="<?= htmlspecialchars($base) ?>/assets/vendor/jquery/jquery.min.js"></script>
<script src="<?= htmlspecialchars($base) ?>/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

<!-- Core plugin JavaScript-->
<script src="<?= htmlspecialchars($base) ?>/assets/vendor/jquery-easing/jquery.easing.min.js"></script>

<!-- Custom scripts for all pages-->
<script src="<?= htmlspecialchars($base) ?>/assets/js/sb-admin-2.min.js"></script>
<script src="<?= htmlspecialchars($base) ?>/assets/js/searchable-select.js"></script>
<script>
(function () {
    var employeeSelect = document.getElementById('employee_id');
    if (!employeeSelect) return;

    initSearchableSelect(employeeSelect, {
        placeholder: '-- Type to search employee --',
        noResultsText: 'No employees found'
    });
})();
</script>

</body>
</html>
