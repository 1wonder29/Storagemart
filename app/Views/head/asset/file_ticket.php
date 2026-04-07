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

    <title>storagemart | File Ticket Employee</title>

    <!-- Custom fonts for this template -->
    <link href="<?= htmlspecialchars($base) ?>/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="<?= htmlspecialchars($base) ?>/assets/css/storagemart.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/input.css" rel="stylesheet">
    <!-- Custom styles for this page -->

</head>

<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper">
        <?php 
        $activePage = 'assets';
        require_once __DIR__ . '/../../partials/head/sidebar_topbar.php';?>
                <!-- End of Topbar -->

                <!-- Begin Page Content -->
                <div class="container-fluid">

                    <!-- Page Heading -->
                    <h1 class="h3 mb-2 text-gray-800"></h1>
                    <p class="mb-4">"Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum."</p>

                    <!-- DataTales Example -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Add Ticket</h6>
                        </div>
                        <div class="card-body">
                            <div class="container mt-4">
                                <form action="<?= htmlspecialchars($base) ?>/head/assets/file_ticket" method="POST">
                                    <input type="hidden" name="branch_id" value="<?= htmlspecialchars($inventory['branch_id'] ?? '') ?>">
                                    <input type="hidden" name="inventory_id" value="<?= htmlspecialchars($inventory['inventory_id'] ?? '') ?>">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                            <h1>Employee Details</h1>
                                <div class="row mb-5">
                                    <div class="col-md-6">
                                        <label for="employee_id" class="form-label">Employee ID</label>
                                        <input type="text" class="form-control" id="employee_id" name="employee_id" placeholder="Employee ID" value="<?= htmlspecialchars($inventory['employee_id'] ?? '') ?>" readonly>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="fullname" class="form-label">Fullname</label>
                                        <input type="text" class="form-control" id="fullname" name="fullname" placeholder="Full Name" value="<?= htmlspecialchars($inventory['fullname'] ?? '')  ?>" readonly>
                                    </div>
                                    </div>
                                    <div class ="row mb-5">
                                        <div class="col-md-6">
                                            <label for="department" class="form-label">Department</label>
                                            <input type="text" class="form-control" id="department" name="department" placeholder="Department" value="<?= htmlspecialchars($inventory['department'] ?? '') ?>" readonly>
                                        </div>
                                    <div class="col-md-6">
                                        <label for="branchName" class="form-label">Branch</label>
                                        <input type="text" class="form-control" id="branchName" name="branchName" placeholder="Branch" value="<?= htmlspecialchars($inventory['branchName'] ?? '') ?>" readonly>
                                    </div>
                                    </div>
                                <hr></hr>
                                <h1>Asset Details</h1>
                                <div class="row mb-5">
                                    <div class="col-md-6">
                                        <label for="assetNumber" class="form-label">Asset Number</label>
                                        <input type="text" class="form-control" id="assetNumber" name="assetNumber" placeholder="Asset Number" value="<?= htmlspecialchars($inventory['assetNumber'] ?? '') ?>" readonly>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="groupName" class="form-label">Model Name</label>
                                        <input type="text" class="form-control" id="groupName" name="groupName" placeholder="Model" value="<?= htmlspecialchars($inventory['groupName'] ?? '') ?>"  readonly>
                                    </div>
                                    </div>
                                <h1>Ticket Concerns</h1>
                                    <div class ="row mb-5">
                                            <div class ="col-md-6">
                                                <label for="category" class="form-label">Technical Category</label>
                                                <select id="category" name="category" class="form-control" required>
                                                <option value="">-- Select Category --</option>
                                                <option value="Hardware">Hardware</option>
                                                <option value="Software">Software</option>
                                                <option value="Network">Network</option>
                                                </select>
                                            </div>
                                            <div class ="col-md-6">
                                                <label for ="priority" class ="form-label">Priority</label>
                                                    <select id="priority" name="priority" class="form-control" required>
                                                    <option value="">-- Select Priority level --</option>
                                                    <option value="low">Low</option>
                                                    <option value="medium">Medium</option>
                                                    <option value="high">High</option>
                                                    </select>
                                            </div>
                                    </div>

                                    <div class="row mb-5">
                                            <div class="col-md-6">
                                                <label for = "concern_details" class ="form-label">Concern Summary</label>
                                                <textarea id ="concern_details" name="concern_details" class="form-control" rows="6" maxlength="1000" required></textarea>
                                                <small class="form-text text-muted">Maximum 1000 characters.</small>
                                            </div>
                                    </div>

                                    <button type="submit" class="btn btn-primary" name="btnSubmit">Submit</button>
                                    <a href="<?= htmlspecialchars($base) ?>/head/assets" class="btn btn-danger">Cancel</a>
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
    <!-- Bootstrap core JavaScript-->
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/jquery/jquery.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Core plugin JavaScript-->
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/jquery-easing/jquery.easing.min.js"></script>

    <!-- Custom scripts for all pages-->
    <script src="<?= htmlspecialchars($base) ?>/assets/js/sb-admin-2.min.js"></script>
<script>
document.getElementById("employee_id").addEventListener("change", function() {
    var selectedOption = this.options[this.selectedIndex];
    if (selectedOption.value !== "") {
        document.getElementById("lastname").value = selectedOption.getAttribute("data-lastname");
        document.getElementById("firstname").value = selectedOption.getAttribute("data-firstname");
        document.getElementById("middlename").value = selectedOption.getAttribute("data-middlename");
        document.getElementById("branch").value = selectedOption.getAttribute("data-branch");
        document.getElementById("department").value = selectedOption.getAttribute("data-department");
    } else {
        document.getElementById("lastname").value = "";
        document.getElementById("firstname").value = "";
        document.getElementById("middlename").value = "";
        document.getElementById("branch").value = "";
        document.getElementById("department").value = "";
    }
});
</script>

</body>

</html>