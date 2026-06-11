<?php
$base = rtrim(BASE_URL, '/');
require_once __DIR__ . '/../../partials/admin/account_view_helpers.php';

$employees = $employees ?? [];
$totalEmployees = count($employees);
$departments = [];
$branches = [];
$thisMonth = 0;
$now = time();

foreach ($employees as $row) {
    $dept = trim((string) ($row['department'] ?? ''));
    if ($dept !== '') {
        $departments[$dept] = true;
    }
    $branch = trim((string) ($row['branchName'] ?? ''));
    if ($branch !== '') {
        $branches[$branch] = true;
    }
    $dc = strtotime((string) ($row['datecreated'] ?? ''));
    if ($dc && (int) date('Y', $dc) === (int) date('Y', $now) && (int) date('n', $dc) === (int) date('n', $now)) {
        $thisMonth++;
    }
}

ksort($departments);
ksort($branches);
?>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Storage Mart | Employees</title>
    <link href="<?= htmlspecialchars($base) ?>/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/storagemart.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/admin-users.css" rel="stylesheet">
    <link rel="icon" href="<?= htmlspecialchars($base) ?>/assets/img/favicon.ico" type="image/x-icon">
    <link href="<?= htmlspecialchars($base) ?>/assets/vendor/datatables/datatables.min.css" rel="stylesheet">
</head>

<body id="page-top">

    <div id="wrapper">
        <?php
        $activePage = 'users';
        $userSubPage = 'employee';
        require_once __DIR__ . '/../../partials/admin/sidebar_topbar.php';
        ?>

        <div class="container-fluid admin-users-page">

            <div class="page-hero hero-employees">
                <div class="row align-items-center">
                    <div class="col-lg-7">
                        <h1><i class="fas fa-user-tie mr-2"></i>Employees</h1>
                        <p>Browse the full employee directory — departments, positions, branches, and assigned assets.</p>
                        <div class="quick-nav mt-3">
                            <a href="<?= htmlspecialchars($base) ?>/admin/account" class="btn btn-sm btn-outline-light">
                                <i class="fas fa-id-card mr-1"></i> Accounts
                            </a>
                        </div>
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
                                    <div class="stat-value"><?= count($departments) ?></div>
                                    <div class="stat-label">Departments</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="hero-stat">
                                    <div class="stat-value"><?= (int) $thisMonth ?></div>
                                    <div class="stat-label">This Month</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="filter-toolbar">
                <div class="row align-items-end">
                    <div class="col-md-4 col-sm-6 mb-2 mb-md-0">
                        <label for="employeeDeptFilter">Department</label>
                        <select id="employeeDeptFilter" class="form-control form-control-sm">
                            <option value="">All Departments</option>
                            <?php foreach (array_keys($departments) as $dept): ?>
                                <option value="<?= htmlspecialchars($dept) ?>"><?= htmlspecialchars($dept) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4 col-sm-6 mb-2 mb-md-0">
                        <label for="employeeBranchFilter">Branch</label>
                        <select id="employeeBranchFilter" class="form-control form-control-sm">
                            <option value="">All Branches</option>
                            <?php foreach (array_keys($branches) as $branch): ?>
                                <option value="<?= htmlspecialchars($branch) ?>"><?= htmlspecialchars($branch) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4 col-sm-6 text-md-right">
                        <button type="button" id="employeeClearFilters" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-undo mr-1"></i> Clear Filters
                        </button>
                    </div>
                </div>
            </div>

            <div class="card data-list-card shadow mb-4">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-address-book mr-1"></i> Employee Directory
                    </h6>
                    <span class="badge badge-success"><?= (int) $totalEmployees ?> employee<?= $totalEmployees === 1 ? '' : 's' ?></span>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($employees)): ?>
                        <div class="empty-state">
                            <i class="fas fa-users-slash d-block"></i>
                            No employees found.
                        </div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="employee-table" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Employee</th>
                                    <th>Department / Position</th>
                                    <th>Branch</th>
                                    <th>Email</th>
                                    <th>Date Created</th>
                                    <th class="text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($employees as $row):
                                    $employeeId = (int) ($row['employee_id'] ?? 0);
                                    $fullName = admin_employee_full_name($row);
                                    $department = (string) ($row['department'] ?? '');
                                    $position = (string) ($row['position'] ?? '');
                                    $date = admin_account_format_date((string) ($row['datecreated'] ?? ''));
                                ?>
                                    <tr data-department="<?= htmlspecialchars(strtolower(trim($department))) ?>"
                                        data-branch="<?= htmlspecialchars(strtolower(trim((string) ($row['branchName'] ?? '')))) ?>">
                                        <td>
                                            <div class="employee-name"><?= htmlspecialchars($fullName) ?></div>
                                            <div class="employee-meta">
                                                <span class="employee-id">#<?= $employeeId ?></span>
                                                <?php if (!empty($row['account_id'])): ?>
                                                    <span class="ml-2">Acct #<?= htmlspecialchars((string) $row['account_id']) ?></span>
                                                <?php endif; ?>
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
                                            <?php if (!empty($row['branchName'])): ?>
                                                <span class="branch-pill">
                                                    <i class="fas fa-map-marker-alt"></i>
                                                    <?= htmlspecialchars((string) $row['branchName']) ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted">—</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($row['email'])): ?>
                                                <div class="email-text">
                                                    <i class="fas fa-envelope mr-1 text-muted"></i>
                                                    <?= htmlspecialchars((string) $row['email']) ?>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-muted">—</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="date-cell" data-order="<?= (int) $date['order'] ?>">
                                            <div class="date-main"><?= htmlspecialchars($date['main']) ?></div>
                                            <?php if ($date['time'] !== ''): ?>
                                                <div class="date-time"><?= htmlspecialchars($date['time']) ?></div>
                                            <?php endif; ?>
                                            <?php if (!empty($row['createdby'])): ?>
                                                <div class="meta-hint">by <?= htmlspecialchars((string) $row['createdby']) ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-right">
                                            <div class="action-btn-group">
                                                <a href="<?= htmlspecialchars($base) ?>/admin/assets/view?employee_id=<?= $employeeId ?>"
                                                   class="btn btn-sm btn-outline-primary" title="View assets">
                                                    <i class="fas fa-box-open"></i>
                                                </a>
                                                <form method="POST" action="<?= htmlspecialchars($base) ?>/admin/employee" class="d-inline">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="employee_id" value="<?= $employeeId ?>">
                                                    <button type="submit"
                                                        class="btn btn-sm btn-outline-danger"
                                                        title="Delete employee"
                                                        onclick="return confirm(<?= json_encode(
                                                            'Are you sure you want to delete employee "' . $fullName . '"?' . "\n\n" .
                                                            'This action cannot be undone and will permanently remove all associated data.'
                                                        ) ?>)">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
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
    </div>

    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/jquery/jquery.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/js/sb-admin-2.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/datatables/datatables.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/js/admin-employees.js"></script>
    <?php require __DIR__ . '/../../partials/flash_modal.php'; ?>
</body>

</html>
