<?php
$base = rtrim(BASE_URL, '/');
$routePrefix = $routePrefix ?? (($user_role ?? '') === 'HOM' ? 'hom' : 'om');
$employees = $employees ?? [];
$branches = $branches ?? [];
$totalEmployees = count($employees);
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
    <link href="<?= htmlspecialchars($base) ?>/assets/css/om-dashboard.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/hom-employees.css" rel="stylesheet">
</head>
<body id="page-top">

<div id="wrapper">
    <?php
    $activePage = 'employees';
    require_once __DIR__ . '/../partials/om/sidebar_topbar.php';
    ?>

    <div class="container-fluid om-dashboard-page hom-employees-page">
        <div class="page-hero">
            <div class="row align-items-center">
                <div class="col-lg-7">
                    <h1><i class="fas fa-users mr-2"></i>Operations Employees</h1>
                    <p>View all Operations staff and transfer employees between branches.</p>
                </div>
                <div class="col-lg-5 mt-3 mt-lg-0">
                    <div class="row">
                        <div class="col-6">
                            <div class="hero-stat">
                                <div class="stat-value"><?= (int) $totalEmployees ?></div>
                                <div class="stat-label">Total Staff</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="hero-stat">
                                <div class="stat-value"><?= count($branches) ?></div>
                                <div class="stat-label">Branches</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success alert-dismissible fade show alert-modern" role="alert">
                <i class="fas fa-check-circle mr-1"></i><?= htmlspecialchars($_SESSION['success_message']) ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger alert-dismissible fade show alert-modern" role="alert">
                <i class="fas fa-exclamation-circle mr-1"></i><?= htmlspecialchars($_SESSION['error_message']) ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>

        <div class="filter-toolbar card shadow-sm mb-4">
            <div class="card-body py-3">
                <div class="row align-items-end">
                    <div class="col-md-4 col-sm-6 mb-2 mb-md-0">
                        <label for="homBranchFilter">Current Branch</label>
                        <select id="homBranchFilter" class="form-control form-control-sm">
                            <option value="">All Branches</option>
                            <?php foreach ($branches as $branch): ?>
                                <option value="<?= (int) ($branch['branch_id'] ?? 0) ?>">
                                    <?= htmlspecialchars($branch['branchName'] ?? '') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4 col-sm-6 mb-2 mb-md-0">
                        <label for="homSearchFilter">Search</label>
                        <input type="text" id="homSearchFilter" class="form-control form-control-sm" placeholder="Name, position, or email">
                    </div>
                    <div class="col-md-4 col-sm-6 text-md-right">
                        <button type="button" id="homClearFilters" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-undo mr-1"></i> Clear Filters
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow mb-4 data-list-card">
            <div class="card-header d-flex align-items-center justify-content-between flex-wrap">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-address-book mr-1"></i> Operations &amp; HOM Staff
                </h6>
                <span class="badge badge-primary employee-count-badge">
                    <?= (int) $totalEmployees ?> employee<?= $totalEmployees === 1 ? '' : 's' ?>
                </span>
            </div>
            <div class="card-body p-0">
                <?php if (empty($employees)): ?>
                    <div class="empty-state">
                        <i class="fas fa-users-slash d-block"></i>
                        No Operations employees found.
                    </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="homEmployeesTable">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Position</th>
                                <th>Current Branch</th>
                                <th>Assigned AOM</th>
                                <th>Role</th>
                                <th class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($employees as $employee):
                                $employeeId = (int) ($employee['employee_id'] ?? 0);
                                $fullName = trim(($employee['firstname'] ?? '') . ' ' . ($employee['lastname'] ?? ''));
                                $branchId = (int) ($employee['branch_id'] ?? 0);
                                $branchLabel = trim(($employee['branchName'] ?? '') . (($employee['branchCode'] ?? '') ? ' (' . $employee['branchCode'] . ')' : ''));
                                if ($branchLabel === '') {
                                    $branchLabel = 'Unassigned';
                                }
                                $usertype = strtoupper((string) ($employee['usertype'] ?? 'EMPLOYEE'));
                            ?>
                            <tr data-branch-id="<?= $employeeId > 0 ? $branchId : 0 ?>"
                                data-search="<?= htmlspecialchars(strtolower($fullName . ' ' . ($employee['position'] ?? '') . ' ' . ($employee['email'] ?? ''))) ?>">
                                <td>
                                    <div class="employee-name"><?= htmlspecialchars($fullName) ?></div>
                                    <div class="employee-meta">ID: <?= $employeeId ?> &middot; <?= htmlspecialchars($employee['email'] ?? '') ?></div>
                                </td>
                                <td><?= htmlspecialchars($employee['position'] ?? 'N/A') ?></td>
                                <td>
                                    <span class="branch-pill">
                                        <i class="fas fa-map-marker-alt mr-1"></i><?= htmlspecialchars($branchLabel) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if (!empty($employee['aom_firstname'])): ?>
                                        <?= htmlspecialchars(trim(($employee['aom_firstname'] ?? '') . ' ' . ($employee['aom_lastname'] ?? ''))) ?>
                                    <?php else: ?>
                                        <span class="text-muted">Unassigned</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="role-pill role-<?= strtolower($usertype) ?>">
                                        <?= htmlspecialchars($usertype) ?>
                                    </span>
                                </td>
                                <td class="text-right">
                                    <button type="button"
                                            class="btn btn-sm btn-primary transfer-branch-btn"
                                            data-employee-id="<?= $employeeId ?>"
                                            data-employee-name="<?= htmlspecialchars($fullName) ?>"
                                            data-current-branch-id="<?= $branchId ?>"
                                            data-current-branch-name="<?= htmlspecialchars($branchLabel) ?>">
                                        <i class="fas fa-exchange-alt"></i> Transfer
                                    </button>
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

<div class="modal fade" id="transferBranchModal" tabindex="-1" role="dialog" aria-labelledby="transferBranchModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="POST" action="<?= htmlspecialchars($base) ?>/<?= htmlspecialchars($routePrefix) ?>/transfer-employee">
                <div class="modal-header">
                    <h5 class="modal-title" id="transferBranchModalLabel">
                        <i class="fas fa-exchange-alt mr-1"></i> Transfer Employee
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="employee_id" id="transferEmployeeId" value="">
                    <div class="transfer-summary mb-3">
                        <div><strong>Employee:</strong> <span id="transferEmployeeName"></span></div>
                        <div><strong>Current Branch:</strong> <span id="transferCurrentBranch"></span></div>
                    </div>
                    <div class="form-group mb-0">
                        <label for="transferBranchId"><strong>New Branch *</strong></label>
                        <select name="branch_id" id="transferBranchId" class="form-control" required>
                            <option value="">-- Select destination branch --</option>
                            <?php foreach ($branches as $branch): ?>
                                <option value="<?= (int) ($branch['branch_id'] ?? 0) ?>">
                                    <?= htmlspecialchars(($branch['branchName'] ?? '') . (($branch['branchCode'] ?? '') ? ' (' . $branch['branchCode'] . ')' : '')) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="form-text text-muted">The employee will be moved from their current branch to the selected branch.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-check mr-1"></i> Confirm Transfer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<a class="scroll-to-top rounded" href="#page-top">
    <i class="fas fa-angle-up"></i>
</a>

<script src="<?= htmlspecialchars($base) ?>/assets/vendor/jquery/jquery.min.js"></script>
<script src="<?= htmlspecialchars($base) ?>/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="<?= htmlspecialchars($base) ?>/assets/vendor/jquery-easing/jquery.easing.min.js"></script>
<script src="<?= htmlspecialchars($base) ?>/assets/js/sb-admin-2.min.js"></script>
<script src="<?= htmlspecialchars($base) ?>/assets/js/hom-employees.js"></script>
</body>
</html>
