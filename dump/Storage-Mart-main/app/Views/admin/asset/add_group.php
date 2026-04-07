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

    <title>Storage Mart | Add Group</title>

    <!-- Custom fonts for this template -->
    <link href="<?= htmlspecialchars($base) ?>/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="<?= htmlspecialchars($base) ?>/assets/css/storagemart.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/input.css" rel="stylesheet">

</head>

<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper">
            <?php 
            $activePage = 'assets';
            require_once __DIR__ . '/../../partials/admin/sidebar_topbar.php';?>
                <!-- End of Topbar -->

                <!-- Begin Page Content -->
                <div class="container-fluid">
                    <h1 class="h3 mb-2 text-gray-800">Add Group Asset</h1>
                    <p class="mb-4">Create a new asset group by filling out the information below.</p>

                    <!-- Form to Add Group -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Group Asset Details</h6>
                        </div>
                        <div class="card-body">
                                <form action="<?= rtrim($base, '/') ?>/admin/assets/group/add" method="POST">
                                <div class="row mb-5">
                                    <div class="col-md-6">
                                        <label for="categoryName" class="form-label">Item Category</label>
                                        <?php $ic_code = $employee['ic_code'] ?? ''; ?>
                                            <select name="category_id" id="category_id" class="form-control" required>
                                                <option value="">-- Select Category --</option>
                                                <?php foreach ($categories as $category): ?>
                                                    <option 
                                                        value="<?= $category['category_id'] ?>" 
                                                        data-ic_code="<?= htmlspecialchars($category['ic_code']) ?>"
                                                    >
                                                        <?= htmlspecialchars($category['categoryName']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>                                            
                                    </div>
                                    <div class="col-md-6">
                                        <label for="ic_code" class="form-label">IC Code</label>
                                        <input type="text" name="ic_code" class="form-control" id="ic_code" placeholder="IC Code" readonly>
                                    </div>
                                </div>

                                <div class="row mb-5">
                                    <div class="col-md-6">
                                        <label for="groupName" class="form-label">Group Asset Name</label>
                                        <input type="text" name="groupName" class="form-control" id="groupName" placeholder="Group Asset Name" required>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="description" class="form-label">Description</label>
                                        <textarea id="description" name="description" class="form-control" rows="6" maxlength="1000" required></textarea>
                                        <small class="form-text text-muted">Maximum 1000 characters.</small>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary" name="btnSubmit">Submit</button>
                                <a href="<?= htmlspecialchars($base) ?>/admin/assets" class="btn btn-danger">Cancel</a>
                            </form>
                        </div>
                    </div>
                </div>
                <!-- /.container-fluid -->

            </div>
            <!-- End of Main Content -->

            <!-- Footer -->
            <footer class="sticky-footer bg-white">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                        <span>© 2025 Storage Mart. All Rights Reserved.</span>
                    </div>
                </div>
            </footer>
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
    <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Ready to Leave?</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">Select "Logout" below if you are ready to end your current session.</div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                    <a class="btn btn-primary" href="<?= htmlspecialchars($base) ?>/logout">Logout</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap core JavaScript-->

    <script src="<?= htmlspecialchars($base) ?>/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Core plugin JavaScript-->
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/jquery-easing/jquery.easing.min.js"></script>

    <!-- Custom scripts for all pages-->
    <script src="<?= htmlspecialchars($base) ?>/assets/js/sb-admin-2.min.js"></script>
</body>

</html>
