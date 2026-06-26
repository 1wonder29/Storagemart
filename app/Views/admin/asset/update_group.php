<?php
$base = rtrim(BASE_URL, '/');
?>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>storagemart | Update Group</title>

    <!-- Custom fonts for this template -->
    <link href="<?= htmlspecialchars($base) ?>/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="<?= htmlspecialchars($base) ?>/assets/css/storagemart.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/admin-assets.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/input.css" rel="stylesheet">

</head>

<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper">
            <?php 
            $activePage = 'assets';
            $assetSubPage = 'directory';
            require_once __DIR__ . '/../../partials/admin/sidebar_topbar.php';?>

                <div class="container-fluid admin-assets-page">

                    <div class="page-hero hero-form">
                        <h1><i class="fas fa-edit mr-2"></i>Update Group</h1>
                        <p>Edit the asset group model name, category, and description.</p>
                    </div>

                    <div class="card form-card shadow mb-4">
                        <div class="card-header">
                            <h6 class="m-0 font-weight-bold text-primary">Update Group Asset</h6>
                        </div>
                        <div class="card-body">
                                <form action="<?= rtrim($base, '/') ?>/admin/assets/group/update" method="POST">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                                    <input type="hidden" name="group_id" value="<?= htmlspecialchars($assets['group_id'] ?? '') ?>">
                                    <div class="form-section-title">Group Details</div>
                                    <div class ="row mb-5">
                                        <div class = "col-md-6">
                                            <label for="ic_code" class="form-label">IC CODE</label>
                                            <input type="text" class ="form-control" id ="ic_code" name="ic_code" placeholder="IC CODE" value="<?= htmlspecialchars($category['ic_code'] ?? '') ?>" readonly>
                                        </div>
                                        <div class = "col-md-6">
                                            <label for="categoryName" class="form-label">Category Name</label>
                                            <input type="text" class ="form-control" id ="categoryName" name="categoryName" placeholder="Category Name" value="<?= htmlspecialchars($category['categoryName'] ?? '') ?>" readonly>
                                        </div>
                                    </div>


                                    <div class ="row mb-5">
                                            <div class = "col-md-6">
                                                <label for="groupName" class="groupName">Group Asset Name</label>
                                                <input type="text" class ="form-control" id ="groupName" name="groupName" placeholder="Group Asset Name" value="<?= htmlspecialchars($assets['groupName'] ?? '') ?>" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label for = "description" class ="form-label">Description</label>
                                                <textarea id ="description" name="description" class="form-control" rows="6" maxlength="1000" required><?= htmlspecialchars($assets['description'] ?? '') ?></textarea>
                                                <small class="form-text text-muted">Maximum 1000 characters.</small>
                                            </div>
                                    </div>
                                    <div class="form-actions">
                                        <button type="submit" class="btn btn-primary" name="btnSubmit">
                                            <i class="fas fa-save mr-1"></i> Save Changes
                                        </button>
                                        <a href="<?= rtrim($base, '/') ?>/admin/assets" class="btn btn-outline-secondary">Cancel</a>
                                    </div>
                                    </form>
                        </div>
                    </div>
                    
                </div>
                <!-- /.container-fluid -->

            </div>
            <!-- End of Main Content -->

            <!-- Footer -->
            <!-- End of Footer -->

        </div>
        <!-- End of Content Wrapper -->

    </div>
    <!-- End of Page Wrapper -->

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <!-- Logout Modal-->
</div>

    <!-- Bootstrap core JavaScript-->
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/jquery/jquery.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Core plugin JavaScript-->
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/jquery-easing/jquery.easing.min.js"></script>

    <!-- Custom scripts for all pages-->
    <script src="<?= htmlspecialchars($base) ?>/assets/js/sb-admin-2.min.js"></script>
</body>

</html>