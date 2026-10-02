<?php
$base = rtrim(BASE_URL, '/');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Storage Mart | AOM — Create Employee Ticket</title>

    <link href="<?= htmlspecialchars($base) ?>/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900" rel="stylesheet">
    <link rel="icon" href="<?= htmlspecialchars($base) ?>/assets/img/sm_favicon.png" type="image/x-icon">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/storagemart.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/ticket-create.css" rel="stylesheet">
</head>
<body id="page-top">

<div id="wrapper">
    <?php
    $activePage = 'create-employee-ticket';
    require_once __DIR__ . '/../partials/aom/sidebar_topbar.php';
    ?>
    <div class="container-fluid ticket-create-page">
        <div class="page-hero">
            <h1><i class="fas fa-users mr-2"></i>Create Employee Ticket</h1>
            <p>File a ticket for an employee in your assigned branches. Use <strong>My Ticket</strong> to file for yourself.</p>
        </div>

        <?php require __DIR__ . '/../partials/ticket/flash_messages.php'; ?>

        <div class="row">
            <div class="col-lg-9 col-xl-8">
                <div class="card shadow mb-4 ticket-form-card">
                    <div class="card-header ticket-header-employee text-white">
                        <h6 class="font-weight-bold text-white"><i class="fas fa-users mr-1"></i> Employee Ticket</h6>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="<?= htmlspecialchars($base) ?>/aom/tickets/create/employee">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '', ENT_QUOTES, 'UTF-8') ?>">

                            <div class="form-section">
                                <div class="form-section-title">
                                    <i class="fas fa-user-friends"></i> Assignment
                                </div>

                                <div class="mb-3">
                                    <label for="branch_id" class="form-label">
                                        <i class="fas fa-building"></i> Branch <span class="text-danger">*</span>
                                    </label>
                                    <select id="branch_id" name="branch_id" class="form-control form-control-lg" required>
                                        <option value="">-- Select a Branch --</option>
                                        <?php foreach ($branches as $branch): ?>
                                            <option value="<?= (int) $branch['branch_id'] ?>">
                                                <?= htmlspecialchars($branch['branchName'] . ' (' . $branch['branchCode'] . ')') ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <small class="form-text text-muted">Only your assigned branches are available.</small>
                                </div>

                                <div class="mb-0">
                                    <label for="employee_id" class="form-label">
                                        <i class="fas fa-user"></i> Employee <span class="text-danger">*</span>
                                    </label>
                                    <select id="employee_id" name="employee_id" class="form-control form-control-lg" required>
                                        <option value="">-- Select Employee --</option>
                                    </select>
                                    <small class="form-text text-muted">Employees load after you select a branch.</small>
                                </div>
                            </div>

                            <?php
                            $submitLabel = 'Create Ticket';
                            $cancelUrl = htmlspecialchars($base) . '/aom/tickets';
                            $extendedCategories = true;
                            require __DIR__ . '/../partials/ticket/form_fields_ticket_details.php';
                            ?>
                        </form>
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
        var branchId = $(this).val();
        var employeeSelect = $('#employee_id');

        if (!branchId) {
            employeeSelect.html('<option value="">-- Select Employee --</option>');
            return;
        }

        $.ajax({
            url: '<?= htmlspecialchars($base) ?>/aom/api/employees-by-branch',
            method: 'GET',
            data: { branch_id: branchId },
            dataType: 'json',
            success: function (response) {
                var options = '<option value="">-- Select Employee --</option>';
                if (response.data && response.data.length > 0) {
                    response.data.forEach(function (employee) {
                        options += '<option value="' + employee.employee_id + '">' +
                            employee.firstname + ' ' + employee.lastname +
                            (employee.position ? ' (' + employee.position + ')' : '') +
                            '</option>';
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
