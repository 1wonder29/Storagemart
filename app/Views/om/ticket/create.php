<?php
$base = rtrim(BASE_URL, '/');
$routePrefix = $routePrefix ?? (($user_role ?? '') === 'HOM' ? 'hom' : 'om');
$roleLabel = ($user_role ?? '') === 'HOM' ? 'HOM' : 'OM';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Storage Mart | <?= htmlspecialchars($roleLabel) ?> — Create Ticket</title>

    <link href="<?= htmlspecialchars($base) ?>/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900" rel="stylesheet">
    <link rel="icon" href="<?= htmlspecialchars($base) ?>/assets/img/sm_favicon.png" type="image/x-icon">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/storagemart.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/ticket-create.css" rel="stylesheet">
</head>
<body id="page-top">

<div id="wrapper">
    <?php
    $activePage = 'tickets';
    require_once __DIR__ . '/../../partials/om/sidebar_topbar.php';
    ?>
    <div class="container-fluid ticket-create-page">
        <div class="page-hero">
            <h1><i class="fas fa-ticket-alt mr-2"></i>Create New Ticket</h1>
            <p>File a ticket on behalf of an employee. Branch is set automatically from the selected employee.</p>
        </div>

        <?php require __DIR__ . '/../../partials/ticket/flash_messages.php'; ?>

        <div class="row">
            <div class="col-lg-9 col-xl-8">
                <div class="card shadow mb-4 ticket-form-card">
                    <div class="card-header ticket-header-employee text-white">
                        <h6 class="font-weight-bold text-white"><i class="fas fa-clipboard-list mr-1"></i> Ticket Information</h6>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="<?= htmlspecialchars($base) ?>/<?= htmlspecialchars($routePrefix) ?>/tickets/create">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '', ENT_QUOTES, 'UTF-8') ?>">

                            <div class="form-section">
                                <div class="form-section-title">
                                    <i class="fas fa-user-friends"></i> Assignment
                                </div>

                                <div class="mb-3">
                                    <label for="employee_id" class="form-label">
                                        <i class="fas fa-user"></i> Employee <span class="text-danger">*</span>
                                    </label>
                                    <select id="employee_id" name="employee_id" class="form-control form-control-lg" required>
                                        <option value="">-- Select Employee --</option>
                                        <?php if (!empty($employees)): ?>
                                            <?php foreach ($employees as $emp): ?>
                                                <?php $isDefault = (isset($defaultEmployeeId) && (int)($emp['employee_id'] ?? 0) === $defaultEmployeeId) ? 'selected' : ''; ?>
                                                <option value="<?= (int) ($emp['employee_id'] ?? 0) ?>"
                                                        data-branch-id="<?= (int) ($emp['branch_id'] ?? 0) ?>"
                                                        <?= $isDefault ?>>
                                                    <?= htmlspecialchars(($emp['lastname'] ?? '') . ', ' . ($emp['firstname'] ?? '')) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                    <small class="form-text text-muted">Choose an employee to file this ticket for.</small>
                                </div>

                                <div class="mb-0">
                                    <label for="branch_id" class="form-label">
                                        <i class="fas fa-building"></i> Branch <span class="text-danger">*</span>
                                    </label>
                                    <select id="branch_id" class="form-control form-control-lg" required disabled>
                                        <option value="">-- Select Branch --</option>
                                        <?php if (!empty($branches)): ?>
                                            <?php foreach ($branches as $branch): ?>
                                                <option value="<?= (int) ($branch['branch_id'] ?? 0) ?>">
                                                    <?= htmlspecialchars(trim(($branch['branchName'] ?? '') . (!empty($branch['branchCode']) ? ' (' . $branch['branchCode'] . ')' : ''))) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                    <small class="form-text text-muted">Branch is set automatically from the selected employee.</small>
                                    <input type="hidden" id="branch_id_hidden" name="branch_id" value="">
                                </div>
                            </div>

                            <?php
                            $submitLabel = 'Create Ticket';
                            $cancelUrl = htmlspecialchars($base) . '/' . htmlspecialchars($routePrefix) . '/tickets';
                            $extendedCategories = false;
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

<script src="<?= htmlspecialchars($base) ?>/assets/vendor/jquery/jquery.min.js"></script>
<script src="<?= htmlspecialchars($base) ?>/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="<?= htmlspecialchars($base) ?>/assets/js/sb-admin-2.min.js"></script>
<script>
(function () {
    var employeeSelect = document.getElementById('employee_id');
    var branchSelect = document.getElementById('branch_id');
    if (!employeeSelect || !branchSelect) return;

    var branchHidden = document.getElementById('branch_id_hidden');

    function syncBranchFromEmployee() {
        var option = employeeSelect.options[employeeSelect.selectedIndex];
        var branchId = option ? option.getAttribute('data-branch-id') : '';
        var hasEmployee = !!employeeSelect.value;

        if (hasEmployee && branchId) {
            branchSelect.value = branchId;
            if (branchHidden) {
                branchHidden.value = branchId;
            }
        } else {
            branchSelect.value = '';
            if (branchHidden) {
                branchHidden.value = '';
            }
        }

        branchSelect.disabled = true;
    }

    employeeSelect.addEventListener('change', syncBranchFromEmployee);
    syncBranchFromEmployee();
})();
</script>
</body>
</html>
