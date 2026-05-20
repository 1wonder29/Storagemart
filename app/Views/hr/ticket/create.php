<?php
$base = rtrim(BASE_URL, '/');
$loggedFirstname = $ctx['loggedFirstname'] ?? 'HR';
$loggedLastname = $ctx['loggedLastname'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Storage Mart | HR — Create Ticket</title>

    <link href="<?= htmlspecialchars($base) ?>/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900" rel="stylesheet">
    <link rel="icon" href="<?= htmlspecialchars($base) ?>/assets/img/sm_favicon.png" type="image/x-icon">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/storagemart.css" rel="stylesheet">
</head>

<body id="page-top">

<div id="wrapper">
    <?php
    $activePage = 'tickets';
    require_once __DIR__ . '/../../partials/hr/sidebar_topbar.php';
    ?>
    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Create New Ticket</h1>
        </div>

        <?php if (!empty($_SESSION['flash_error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars((string) $_SESSION['flash_error']); ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <?php unset($_SESSION['flash_error']); ?>
        <?php endif; ?>

        <div class="row">
            <div class="col-lg-8 offset-lg-2">
                <div class="card shadow mb-4">
                    <div class="card-header py-3 bg-primary">
                        <h6 class="m-0 font-weight-bold text-white"><i class="fas fa-clipboard"></i> Ticket Information</h6>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="<?= htmlspecialchars($base) ?>/hr/tickets/create">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '', ENT_QUOTES, 'UTF-8') ?>">

                            <div class="mb-3">
                                <label for="branch_id" class="form-label text-xs font-weight-bold text-gray-600 text-uppercase">
                                    <i class="fas fa-building"></i> Branch <span class="text-danger">*</span>
                                </label>
                                <select id="branch_id" name="branch_id" class="form-control form-control-lg border-left-primary" required>
                                    <option value="">-- Select a Branch --</option>
                                    <?php foreach ($branches as $branch): ?>
                                        <option value="<?php echo (int) ($branch['branch_id'] ?? 0); ?>">
                                            <?php echo htmlspecialchars(($branch['branchName'] ?? '') . ' (' . ($branch['branchCode'] ?? '') . ')'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="form-text text-muted d-block mt-1">Choose the branch this ticket relates to.</small>
                            </div>

                            <div class="mb-3">
                                <label for="employee_id" class="form-label text-xs font-weight-bold text-gray-600 text-uppercase">
                                    <i class="fas fa-user"></i> Employee (optional)
                                </label>
                                <select id="employee_id" name="employee_id" class="form-control form-control-lg">
                                    <option value="">-- Default: yourself (HR account) --</option>
                                </select>
                                <small class="form-text text-muted d-block mt-1">Leave blank to file on behalf of your HR login, or pick a staff member in the selected branch.</small>
                            </div>

                            <div class="mb-3">
                                <label for="department" class="form-label text-xs font-weight-bold text-gray-600 text-uppercase">
                                    <i class="fas fa-sitemap"></i> Department <span class="text-danger">*</span>
                                </label>
                                <input type="text" id="department" name="department" class="form-control form-control-lg" placeholder="e.g., IT, Operations" required>
                            </div>

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

                            <div class="mb-3">
                                <label for="concern_details" class="form-label text-xs font-weight-bold text-gray-600 text-uppercase">
                                    <i class="fas fa-align-left"></i> Ticket Description <span class="text-danger">*</span>
                                </label>
                                <textarea id="concern_details" name="concern_details" class="form-control form-control-lg" rows="6"
                                          placeholder="Describe the issue or request..." required></textarea>
                            </div>

                            <div class="d-flex gap-2 mt-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-check"></i> Create Ticket
                                </button>
                                <a href="<?= htmlspecialchars($base) ?>/hr/tickets" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
</div>

<script src="<?= htmlspecialchars($base) ?>/assets/vendor/jquery/jquery.min.js"></script>
<script src="<?= htmlspecialchars($base) ?>/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="<?= htmlspecialchars($base) ?>/assets/js/sb-admin-2.min.js"></script>
<script>
    $(document).ready(function () {
        $('#branch_id').on('change', function () {
            const branchId = $(this).val();
            const employeeSelect = $('#employee_id');

            if (!branchId) {
                employeeSelect.html('<option value="">-- Default: yourself (HR account) --</option>');
                return;
            }

            $.ajax({
                url: '<?= htmlspecialchars($base) ?>/hr/tickets/employees-by-branch',
                method: 'GET',
                data: {branch_id: branchId},
                dataType: 'json',
                success: function (response) {
                    let options = '<option value="">-- Default: yourself (HR account) --</option>';
                    if (response.data && response.data.length > 0) {
                        response.data.forEach(function (employee) {
                            const pos = employee.position ? ' (' + employee.position + ')' : '';
                            options += '<option value="' + employee.employee_id + '">' +
                                employee.firstname + ' ' + employee.lastname + pos + '</option>';
                        });
                    }
                    employeeSelect.html(options);
                },
                error: function () {
                    employeeSelect.html('<option value="">-- Error loading employees --</option>');
                }
            });
        });
    });
</script>
</body>
</html>
