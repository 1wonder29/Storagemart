<?php
$base = rtrim(BASE_URL, '/');
require_once __DIR__ . '/../partials/admin/account_view_helpers.php';

$employees = $employees ?? [];
$branches = $branches ?? [];
$totalEmployees = count($employees);
$activeCount = 0;

foreach ($employees as $row) {
    if (strtoupper((string) ($row['status'] ?? '')) === 'ACTIVE') {
        $activeCount++;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Storage Mart | Operations Employees</title>

    <link href="<?= htmlspecialchars($base) ?>/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900" rel="stylesheet">
    <link rel="icon" href="<?= htmlspecialchars($base) ?>/assets/img/sm_favicon.png" type="image/x-icon">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/storagemart.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/admin-users.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/aom-employees.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/vendor/datatables/datatables.min.css" rel="stylesheet">
</head>
<body id="page-top">

<div id="wrapper">
    <?php
    $activePage = 'employees';
    require_once __DIR__ . '/../partials/aom/sidebar_topbar.php';
    ?>

    <div class="container-fluid admin-users-page aom-employees-page">

        <!-- Page Hero -->
        <div class="page-hero">
            <div class="row align-items-center">
                <div class="col-lg-7">
                    <h1><i class="fas fa-users mr-2"></i>Operations Employees</h1>
                    <p>View Operations staff across your assigned branches.</p>
                </div>
                <div class="col-lg-5">
                    <div class="row mt-3 mt-lg-0">
                        <div class="col-4">
                            <div class="hero-stat">
                                <div class="stat-value"><?= (int) $totalEmployees ?></div>
                                <div class="stat-label">Total</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="hero-stat">
                                <div class="stat-value"><?= count($branches) ?></div>
                                <div class="stat-label">Branches</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="hero-stat">
                                <div class="stat-value"><?= (int) $activeCount ?></div>
                                <div class="stat-label">Active</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="filter-toolbar">
            <div class="row align-items-end">
                <div class="col-md-6 col-sm-6 mb-2 mb-md-0">
                    <label for="aomBranchFilter">Branch</label>
                    <select id="aomBranchFilter" class="form-control form-control-sm">
                        <option value="">All Branches</option>
                        <?php foreach ($branches as $branch): ?>
                            <option value="<?= (int) ($branch['branch_id'] ?? 0) ?>">
                                <?= htmlspecialchars($branch['branchName'] ?? '') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6 col-sm-6 text-md-right">
                    <button type="button" id="aomClearFilters" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-undo mr-1"></i> Clear Filters
                    </button>
                </div>
            </div>
        </div>

        <!-- Employee Table -->
        <div class="card data-list-card shadow mb-4">
            <div class="card-header d-flex align-items-center justify-content-between flex-wrap">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-address-book mr-1"></i> Operations Staff
                </h6>
                <span class="badge badge-primary" style="border-radius:2rem;padding:0.4rem 0.75rem;">
                    <?= (int) $totalEmployees ?> employee<?= $totalEmployees === 1 ? '' : 's' ?>
                </span>
            </div>
            <div class="card-body p-0">
                <?php if (empty($employees)): ?>
                    <div class="empty-state">
                        <i class="fas fa-users-slash d-block"></i>
                        No Operations employees found in your assigned branches.
                    </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="aom-employee-table" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Department / Position</th>
                                <th>Branch</th>
                                <th>Email</th>
                                <th>Status</th>
                                <th class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($employees as $emp):
                                $employeeId = (int) ($emp['employee_id'] ?? 0);
                                $fullName = admin_employee_full_name($emp);
                                $department = (string) ($emp['department'] ?? '');
                                $position = (string) ($emp['position'] ?? '');
                                $isActive = strtoupper((string) ($emp['status'] ?? '')) === 'ACTIVE';
                            ?>
                                <tr data-branch-id="<?= (int) ($emp['branch_id'] ?? 0) ?>"
                                    data-department="<?= htmlspecialchars(strtolower(trim($department))) ?>">
                                    <td>
                                        <div class="employee-name"><?= htmlspecialchars($fullName) ?></div>
                                        <div class="employee-meta">
                                            <span class="employee-id">#<?= $employeeId ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if ($department !== ''): ?>
                                            <span class="dept-badge <?= admin_employee_department_class($department) ?>">
                                                <i class="fas fa-building"></i>
                                                <?= htmlspecialchars($department) ?>
                                            </span>
                                        <?php endif; ?>
                                        <?php if ($position !== ''): ?>
                                            <div class="position-text"><?= htmlspecialchars($position) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($emp['branchName'])): ?>
                                            <span class="branch-pill">
                                                <i class="fas fa-map-marker-alt"></i>
                                                <?= htmlspecialchars((string) $emp['branchName']) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($emp['email'])): ?>
                                            <div class="email-text">
                                                <i class="fas fa-envelope mr-1 text-muted"></i>
                                                <?= htmlspecialchars((string) $emp['email']) ?>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="status-pill <?= $isActive ? 'status-active' : 'status-inactive' ?>">
                                            <?= $isActive ? 'Active' : 'Inactive' ?>
                                        </span>
                                    </td>
                                    <td class="text-right">
                                        <a href="<?= htmlspecialchars($base) ?>/aom/employees/detail?id=<?= $employeeId ?>"
                                           class="btn btn-sm btn-outline-primary btn-view">
                                            <i class="fas fa-eye mr-1"></i> View
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
    <!-- End of Page Content -->

    </div>
    <!-- End of Main Content -->

</div>
<!-- End of Content Wrapper -->

</div>
<!-- End of Page Wrapper -->

<script src="<?= htmlspecialchars($base) ?>/assets/vendor/jquery/jquery.min.js"></script>
<script src="<?= htmlspecialchars($base) ?>/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="<?= htmlspecialchars($base) ?>/assets/vendor/jquery-easing/jquery.easing.min.js"></script>
<script src="<?= htmlspecialchars($base) ?>/assets/js/sb-admin-2.min.js"></script>
<script src="<?= htmlspecialchars($base) ?>/assets/vendor/datatables/jquery.dataTables.min.js"></script>
<script src="<?= htmlspecialchars($base) ?>/assets/vendor/datatables/datatables.min.js"></script>
<script src="<?= htmlspecialchars($base) ?>/assets/js/aom-employees.js"></script>
</body>
</html>
