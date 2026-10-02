<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>StorageMart | Admin Transfer Details</title>

    <!-- Custom fonts for this template -->
    <link href="<?= htmlspecialchars($base) ?>/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="<?= htmlspecialchars($base) ?>/assets/css/StorageMart.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/input.css" rel="stylesheet">

</head>

<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper">
            <?php 
            $activePage = 'assets';
            require_once __DIR__ . '/../../partials/admin/sidebar_topbar.php';?>

                <!-- Begin Page Content -->
                <div class="container-fluid">

                    <!-- Page Heading -->
                    <h1 class="h3 mb-2 text-gray-800"></h1>
                    <p class="mb-4">"Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum."</p>

                    <!-- DataTales Example -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Transfer Asset</h6>
                        </div>
                        <div class="card-body">
                            <div class="container mt-4">
                            <form action="<?= htmlspecialchars($base) ?>/admin/assets/transfer?inventory_id=<?= (int)($inventory['inventory_id'] ?? 0) ?>" method="POST" id="transferForm">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                                <input type="hidden" name="item_id" value="<?= (int)($inventory['inventory_id'] ?? 0) ?>">
                                <input type="hidden" name="group_id" value="<?= (int)($inventory['group_id'] ?? 0) ?>">
                                    <h1>Transfer Details</h1>
                                    <div class="row mb-5">
                                        <div class="col-md-6">
                                            <label for="employee_search" class="form-label">Search Employee</label>
                                            <div class="input-group">
                                                <input type="text" id="employee_search" class="form-control" placeholder="Type employee name or ID">
                                                <button type="button" class="btn btn-primary" id="btnSearchEmployee">Search</button>
                                            </div>
                                            <input type="hidden" id="employee_id" name="employee_id">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="branchName" class="form-label">Employee Branch</label>
                                            <input type="text" class="form-control" id="branchName" name="branchName" placeholder="Employee Branch" readonly>
                                        </div>
                                    </div>

                                    <div class ="row mb-5">
                                            <div class="col-md-6">
                                                <label for = "transferDetails" class ="form-label">Transfer Details</label>
                                                <textarea id ="transferDetails" name="transferDetails" class="form-control" rows="6" maxlength="1000" required></textarea>
                                                <small class="form-text text-muted">Maximum 1000 characters.</small>
                                            </div>
                                    </div>

                                    <button type="submit" class="btn btn-primary" name="btnSubmit">Submit</button>
                                    <a class="btn btn-secondary" href="<?= htmlspecialchars($base) ?>/admin/assets/item?group_id=<?= (int)($inventory['group_id'] ?? 0) ?>">Cancel</a>

                                    </form>
                            </div>

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
    <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
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
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/jquery/jquery.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Core plugin JavaScript-->
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/jquery-easing/jquery.easing.min.js"></script>

    <!-- Custom scripts for all pages-->
    <script src="<?= htmlspecialchars($base) ?>/assets/js/sb-admin-2.min.js"></script>
<script>
document.getElementById('btnSearchEmployee').addEventListener('click', function () {
    var q = document.getElementById('employee_search').value.trim();
    if (!q) { alert('Enter employee id or name'); return; }

    // $base rendered by PHP here
    var url = "<?= htmlspecialchars($base, ENT_QUOTES) ?>/admin/assets/search-employee?q=" + encodeURIComponent(q);

    fetch(url, { credentials: 'same-origin' })
        .then(r => {
            if (!r.ok) return r.text().then(t => { throw new Error('HTTP '+r.status+': '+t); });
            return r.json();
        })
        .then(data => {
            if (data.success) {
                document.getElementById("employee_id").value = data.employee_id;
                document.getElementById("employee_search").value = data.full_name || data.fullName || '';
                document.getElementById("branchName").value = data.branchName || '';
            } else {
                alert(data.message || 'Employee not found');
            }
        })
        .catch(err => {
            console.error(err);
            alert("Error contacting server: " + err.message);
        });
});
</script>

</body>

</html>