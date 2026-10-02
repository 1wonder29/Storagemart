<?php
$base = rtrim(BASE_URL, '/');
$branches = $branches ?? [];
$itStaff = $itStaff ?? [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Storage Mart | Add Ticket</title>
    <link href="<?= htmlspecialchars($base) ?>/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900" rel="stylesheet">
    <link rel="icon" href="<?= htmlspecialchars($base) ?>/assets/img/favicon.ico" type="image/x-icon">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/storagemart.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/ticket-create.css" rel="stylesheet">
</head>
<body id="page-top">

<div id="wrapper">
    <?php
    $activePage = 'tickets';
    require_once __DIR__ . '/../../partials/admin/sidebar_topbar.php';
    ?>
    <div class="container-fluid ticket-create-page">
        <div class="page-hero d-flex align-items-start justify-content-between flex-wrap">
            <div class="mr-3">
                <h1><i class="fas fa-ticket-alt mr-2"></i>Add Ticket</h1>
                <p>File a ticket for any employee. Pick their branch and name, describe the issue, and optionally assign IT staff right away.</p>
            </div>
            <a href="<?= htmlspecialchars($base) ?>/admin/tickets" class="btn btn-light btn-sm mt-2">
                <i class="fas fa-arrow-left mr-1"></i> Back to Tickets
            </a>
        </div>

        <?php require __DIR__ . '/../../partials/ticket/flash_messages.php'; ?>

        <div class="row">
            <div class="col-lg-9 col-xl-8">
                <div class="card shadow mb-4 ticket-form-card">
                    <div class="card-header ticket-header-employee text-white">
                        <h6 class="font-weight-bold text-white"><i class="fas fa-clipboard-list mr-1"></i> Ticket Information</h6>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="<?= htmlspecialchars($base) ?>/admin/tickets/add" id="adminAddTicketForm">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '', ENT_QUOTES, 'UTF-8') ?>">

                            <div class="form-section">
                                <div class="form-section-title">
                                    <i class="fas fa-user"></i> Filed For
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="branch_id" class="form-label">
                                            <i class="fas fa-building"></i> Branch <span class="text-danger">*</span>
                                        </label>
                                        <select id="branch_id" name="branch_id" class="form-control form-control-lg" required>
                                            <option value="">-- Select a Branch --</option>
                                            <?php foreach ($branches as $branch): ?>
                                                <option value="<?= (int) $branch['branch_id'] ?>"><?= htmlspecialchars((string) $branch['branchName']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="employee_id" class="form-label">
                                            <i class="fas fa-user"></i> Employee <span class="text-danger">*</span>
                                        </label>
                                        <select id="employee_id" name="employee_id" class="form-control form-control-lg" required disabled>
                                            <option value="">-- Select a branch first --</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="department" class="form-label">
                                            <i class="fas fa-sitemap"></i> Department <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" id="department" name="department" class="form-control form-control-lg" required
                                               placeholder="Filled in from the employee">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="inventory_id" class="form-label">
                                            <i class="fas fa-archive"></i> Asset (optional)
                                        </label>
                                        <select id="inventory_id" name="inventory_id" class="form-control form-control-lg" disabled>
                                            <option value="">-- No specific asset --</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="mb-0">
                                    <label for="ticket_assign" class="form-label">
                                        <i class="fas fa-user-cog"></i> Assign to IT Staff (optional)
                                    </label>
                                    <select id="ticket_assign" name="ticket_assign" class="form-control form-control-lg">
                                        <option value="">-- Assign later --</option>
                                        <?php foreach ($itStaff as $staff): ?>
                                            <option value="<?= (int) $staff['employee_id'] ?>">
                                                <?= htmlspecialchars(trim($staff['firstname'] . ' ' . $staff['lastname'])) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <small class="form-text text-muted">Leave blank to review and assign it from the ticket list.</small>
                                </div>
                            </div>

                            <?php
                            $submitLabel = 'Create Ticket';
                            $cancelUrl = $base . '/admin/tickets';
                            $extendedCategories = true;
                            require __DIR__ . '/../../partials/ticket/form_fields_ticket_details.php';
                            ?>
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
$(function () {
    var base = <?= json_encode($base) ?>;
    var $branch = $('#branch_id');
    var $employee = $('#employee_id');
    var $department = $('#department');
    var $asset = $('#inventory_id');
    var employees = {};

    function resetAssets(message) {
        $asset.html('<option value="">' + message + '</option>').prop('disabled', true);
    }

    $branch.on('change', function () {
        var branchId = $branch.val();
        employees = {};
        $department.val('');
        resetAssets('-- No specific asset --');
        if (!branchId) {
            $employee.html('<option value="">-- Select a branch first --</option>').prop('disabled', true);
            return;
        }
        $employee.html('<option value="">Loading employees…</option>').prop('disabled', true);
        $.getJSON(base + '/admin/tickets/employee-list', { branch_id: branchId })
            .done(function (res) {
                var list = (res && res.employees) || [];
                var html = '<option value="">-- Select an employee --</option>';
                list.forEach(function (emp) {
                    employees[emp.employee_id] = emp;
                    var label = $.trim(emp.full_name) + (emp.position ? ' — ' + emp.position : '');
                    html += '<option value="' + emp.employee_id + '">' + $('<div>').text(label).html() + '</option>';
                });
                if (!list.length) html = '<option value="">No employees in this branch</option>';
                $employee.html(html).prop('disabled', !list.length);
            })
            .fail(function () {
                $employee.html('<option value="">Could not load employees</option>').prop('disabled', true);
            });
    });

    $employee.on('change', function () {
        var emp = employees[$employee.val()];
        $department.val(emp ? (emp.department || '') : '');
        if (!emp) { resetAssets('-- No specific asset --'); return; }
        resetAssets('Loading assets…');
        $.getJSON(base + '/admin/tickets/get-assets', { employee_id: emp.employee_id })
            .done(function (res) {
                var assets = (res && res.data) || [];
                var html = '<option value="">-- No specific asset --</option>';
                assets.forEach(function (a) {
                    var label = (a.assetNumber || '') + (a.groupName ? ' — ' + a.groupName : '');
                    html += '<option value="' + a.inventory_id + '">' + $('<div>').text(label).html() + '</option>';
                });
                $asset.html(html).prop('disabled', !assets.length);
            })
            .fail(function () { resetAssets('-- No specific asset --'); });
    });
});
</script>
</body>
</html>
