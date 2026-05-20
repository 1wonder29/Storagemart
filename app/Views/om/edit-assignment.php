<?php
$base = rtrim(BASE_URL, '/');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Storage Mart | Edit Assignment</title>

    <!-- Custom fonts for this template -->
    <link href="<?= htmlspecialchars($base) ?>/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900" rel="stylesheet">
    <link rel="icon" href="<?= htmlspecialchars($base) ?>/assets/img/sm_favicon.png" type="image/x-icon">

    <!-- Custom styles for this template -->
    <link href="<?= htmlspecialchars($base) ?>/assets/css/storagemart.css" rel="stylesheet">
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
                <h1 class="h3 mb-0 text-gray-800">Edit Assignment</h1>
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
                    <h6 class="m-0 font-weight-bold text-white">Update Assignment</h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($assignment)): ?>
                    <form method="POST">
                        <input type="hidden" name="assignment_id" value="<?= $assignment['assignment_id'] ?>">

                        <div class="form-group">
                            <label><strong>Employee</strong></label>
                            <p class="form-control-plaintext">
                                <?= htmlspecialchars($assignment['employee_firstname'] . ' ' . $assignment['employee_lastname']) ?>
                            </p>
                        </div>

                        <div class="form-group">
                            <label><strong>Current Branch</strong></label>
                            <p class="form-control-plaintext">
                                <?= htmlspecialchars($assignment['branch_name'] ?? 'N/A') ?>
                            </p>
                        </div>

                        <div class="form-group">
                            <label for="branch_id"><strong>Change Branch *</strong></label>
                            <select name="branch_id" id="branch_id" class="form-control" required>
                                <option value="">-- Choose a Branch --</option>
                                <?php if (!empty($branches)): ?>
                                    <?php foreach ($branches as $branch): ?>
                                    <option value="<?= $branch['branch_id'] ?>" 
                                            <?= ($assignment['branch_id'] == $branch['branch_id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($branch['branchName'] . ' (' . $branch['branchCode'] . ')') ?>
                                    </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label><strong>Current AOM</strong></label>
                            <p class="form-control-plaintext">
                                <?= htmlspecialchars($assignment['aom_firstname'] . ' ' . $assignment['aom_lastname']) ?>
                            </p>
                        </div>

                        <div class="form-group">
                            <label for="aom_id"><strong>Change AOM *</strong></label>
                            <select name="aom_id" id="aom_id" class="form-control" required>
                                <option value="">-- Choose an AOM --</option>
                                <?php if (!empty($active_aoms)): ?>
                                    <?php foreach ($active_aoms as $aom): ?>
                                    <option value="<?= $aom['employee_id'] ?>" 
                                            <?= ($assignment['aom_id'] == $aom['employee_id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($aom['firstname'] . ' ' . $aom['lastname']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="notes"><strong>Notes</strong></label>
                            <textarea name="notes" id="notes" class="form-control" rows="4"><?= htmlspecialchars($assignment['notes'] ?? '') ?></textarea>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Changes
                            </button>
                            <a href="<?= htmlspecialchars($base) ?>/om/assignments" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>

                    <?php if ($assignment['is_active']): ?>
                    <hr>
                    <div class="alert alert-warning">
                        <strong>Deactivate Assignment</strong>
                        <p>Click below to deactivate this assignment. The employee will no longer be assigned to this AOM.</p>
                        <form method="POST" action="<?= htmlspecialchars($base) ?>/om/deactivate-assignment" 
                              style="display:inline;" 
                              onsubmit="return confirm('Are you sure you want to deactivate this assignment?');">
                            <input type="hidden" name="assignment_id" value="<?= $assignment['assignment_id'] ?>">
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-ban"></i> Deactivate Assignment
                            </button>
                        </form>
                    </div>
                    <?php endif; ?>

                    <?php else: ?>
                    <div class="alert alert-danger">
                        Assignment not found. <a href="<?= htmlspecialchars($base) ?>/om/assignments">Back to Assignments</a>
                    </div>
                    <?php endif; ?>
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

</body>
</html>
