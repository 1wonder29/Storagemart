<?php
$base = rtrim(BASE_URL, '/');
$loggedFirstname = $ctx['loggedFirstname'] ?? 'AOM';
$loggedLastname = $ctx['loggedLastname'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Storage Mart | Create Ticket</title>

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
    $activePage = 'tickets';
    require_once __DIR__ . '/../partials/aom/sidebar_topbar.php';?>
        <!-- Page Content -->
        <div class="container-fluid">
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">Create New Ticket</h1>
            </div>

            <!-- Flash Messages -->
            <?php if (isset($_SESSION['flash_error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($_SESSION['flash_error']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['flash_error']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['flash_success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($_SESSION['flash_success']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['flash_success']); ?>
            <?php endif; ?>

            <div class="row">
                <div class="col-lg-8 offset-lg-2">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 bg-primary">
                            <h6 class="m-0 font-weight-bold text-white"><i class="fas fa-clipboard"></i> Ticket Information</h6>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="<?= htmlspecialchars($base) ?>/aom/tickets/create">
                                <!-- Branch Selection (REQUIRED) -->
                                <div class="mb-3">
                                    <label for="branch_id" class="form-label text-xs font-weight-bold text-gray-600 text-uppercase">
                                        <i class="fas fa-building"></i> Assign to Branch <span class="text-danger">*</span>
                                    </label>
                                    <select id="branch_id" name="branch_id" class="form-control form-control-lg border-left-primary" required>
                                        <option value="">-- Select a Branch --</option>
                                        <?php foreach ($branches as $branch): ?>
                                            <option value="<?php echo $branch['branch_id']; ?>">
                                                <?php echo htmlspecialchars($branch['branchName'] . ' (' . $branch['branchCode'] . ')'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <small class="form-text text-muted d-block mt-1">
                                        Only your assigned branches are available
                                    </small>
                                </div>

                                <!-- Employee Selection (Optional) -->
                                <div class="mb-3">
                                    <label for="employee_id" class="form-label text-xs font-weight-bold text-gray-600 text-uppercase">
                                        <i class="fas fa-user"></i> Assigned Employee (Optional)
                                    </label>
                                    <select id="employee_id" name="employee_id" class="form-control form-control-lg">
                                        <option value="">-- Not Assigned to Specific Employee --</option>
                                        <!-- Will be populated dynamically -->
                                    </select>
                                </div>

                                <!-- Department -->
                                <div class="mb-3">
                                    <label for="department" class="form-label text-xs font-weight-bold text-gray-600 text-uppercase">
                                        <i class="fas fa-sitemap"></i> Department
                                    </label>
                                    <input type="text" id="department" name="department" class="form-control form-control-lg" placeholder="e.g., Operations">
                                </div>

                                <!-- Category -->
                                <div class="mb-3">
                                    <label for="category" class="form-label text-xs font-weight-bold text-gray-600 text-uppercase">
                                        <i class="fas fa-tag"></i> Category
                                    </label>
                                    <select id="category" name="category" class="form-control form-control-lg">
                                        <option value="">-- Select Category --</option>
                                        <option value="Network">Network</option>
                                        <option value="Hardware">Hardware</option>
                                        <option value="Software">Software</option>
                                        <option value="Facility">Facility</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>

                                <!-- Priority -->
                                <div class="mb-3">
                                    <label for="priority" class="form-label text-xs font-weight-bold text-gray-600 text-uppercase">
                                        <i class="fas fa-exclamation-triangle"></i> Priority
                                    </label>
                                    <select id="priority" name="priority" class="form-control form-control-lg">
                                        <option value="Low">Low</option>
                                        <option value="Medium" selected>Medium</option>
                                        <option value="High">High</option>
                                    </select>
                                </div>

                                <!-- Description -->
                                <div class="mb-3">
                                    <label for="concern_details" class="form-label text-xs font-weight-bold text-gray-600 text-uppercase">
                                        <i class="fas fa-align-left"></i> Ticket Description <span class="text-danger">*</span>
                                    </label>
                                    <textarea id="concern_details" name="concern_details" class="form-control form-control-lg" 
                                              rows="6" placeholder="Describe the issue or request..." required></textarea>
                                    <small class="form-text text-muted d-block mt-1">
                                        Provide detailed information about the ticket
                                    </small>
                                </div>

                                <!-- Buttons -->
                                <div class="d-flex gap-2 mt-4">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-check"></i> Create Ticket
                                    </button>
                                    <a href="<?= htmlspecialchars($base) ?>/aom/tickets" class="btn btn-secondary">
                                        <i class="fas fa-times"></i> Cancel
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- End of Page Content -->
    </div>
    <!-- End of Main Content -->

    </div>
    <!-- End of Content Wrapper -->

    <!-- Scripts -->
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/jquery/jquery.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/js/sb-admin-2.min.js"></script>

    <script>
        $(document).ready(function() {
            // Load employees when branch is selected
            $('#branch_id').on('change', function() {
                const branchId = $(this).val();
                const employeeSelect = $('#employee_id');
                
                if (!branchId) {
                    employeeSelect.html('<option value="">-- Not Assigned to Specific Employee --</option>');
                    return;
                }

                // AJAX call to get employees for branch
                $.ajax({
                    url: '<?= htmlspecialchars($base) ?>/aom/api/employees-by-branch',
                    method: 'GET',
                    data: { branch_id: branchId },
                    dataType: 'json',
                    success: function(response) {
                        let options = '<option value="">-- Not Assigned to Specific Employee --</option>';
                        if (response.data && response.data.length > 0) {
                            response.data.forEach(function(employee) {
                                options += `<option value="${employee.employee_id}">
                                    ${employee.firstname} ${employee.lastname} (${employee.position})
                                </option>`;
                            });
                        }
                        employeeSelect.html(options);
                    },
                    error: function() {
                        employeeSelect.html('<option value="">-- Error loading employees --</option>');
                    }
                });
            });
        });
    </script>
</body>
</html>
</html>
