<?php
$base = rtrim(BASE_URL, '/');
require_once dirname(__DIR__) . '/partials/admin/account_view_helpers.php';

$displayName = $_SESSION['username'] ?? 'HR User';
$totalEmployees = (int)($totalEmployees ?? 0);
$totalEmployeesWithUniforms = (int)($totalEmployeesWithUniforms ?? 0);
$uniformsNeedingReorder = (int)($uniformsNeedingReorder ?? 0);
$employeesWithoutUniforms = max(0, $totalEmployees - $totalEmployeesWithUniforms);

$chartUniformStats = [
    'With Uniforms'    => $totalEmployeesWithUniforms,
    'Without Uniforms' => $employeesWithoutUniforms,
];
$hasChartData = ($totalEmployeesWithUniforms + $employeesWithoutUniforms) > 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Storage Mart | HR Dashboard</title>

    <link href="<?= htmlspecialchars($base) ?>/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900" rel="stylesheet">
    <link rel="icon" href="<?= htmlspecialchars($base) ?>/assets/img/sm_favicon.png" type="image/x-icon">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/storagemart.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/admin-users.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/hr-dashboard.css" rel="stylesheet">
</head>
<body id="page-top">

<div id="wrapper">
    <?php
    $activePage = 'dashboard';
    require_once dirname(__DIR__) . '/partials/hr/sidebar_topbar.php';
    ?>

    <div class="container-fluid hr-dashboard-page">

        <!-- Hero -->
        <div class="page-hero">
            <div class="row align-items-center">
                <div class="col-lg-4">
                    <h1><i class="fas fa-tachometer-alt mr-2"></i>HR Dashboard</h1>
                    <p>Welcome back, <?= htmlspecialchars($displayName) ?> — manage employees, uniforms, and accountability.</p>
                </div>
                <div class="col-lg-8 mt-3 mt-lg-0">
                    <div class="row">
                        <div class="col-6 col-md mb-2 mb-md-0">
                            <div class="hero-stat">
                                <div class="stat-value"><?= $totalEmployees ?></div>
                                <div class="stat-label">Employees</div>
                            </div>
                        </div>
                        <div class="col-6 col-md mb-2 mb-md-0">
                            <div class="hero-stat">
                                <div class="stat-value"><?= (int)($uniformStats['total_uniform_types'] ?? 0) ?></div>
                                <div class="stat-label">Uniform Types</div>
                            </div>
                        </div>
                        <div class="col-6 col-md mb-2 mb-md-0">
                            <div class="hero-stat">
                                <div class="stat-value"><?= (int)($uniformStats['total_stock'] ?? 0) ?></div>
                                <div class="stat-label">Total Stock</div>
                            </div>
                        </div>
                        <div class="col-6 col-md mb-2 mb-md-0">
                            <div class="hero-stat">
                                <div class="stat-value"><?= $uniformsNeedingReorder ?></div>
                                <div class="stat-label">Needs Reorder</div>
                            </div>
                        </div>
                        <div class="col-6 col-md">
                            <div class="hero-stat">
                                <div class="stat-value"><?= $totalEmployeesWithUniforms ?></div>
                                <div class="stat-label">With Uniforms</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alerts -->
        <?php if (!empty($_SESSION['successMessage'])): ?>
            <div class="alert alert-success alert-dismissible fade show alert-modern" role="alert">
                <i class="fas fa-check-circle mr-1"></i><?= htmlspecialchars($_SESSION['successMessage']) ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <?php unset($_SESSION['successMessage']); ?>
        <?php endif; ?>

        <?php if (!empty($_SESSION['errorMessage'])): ?>
            <div class="alert alert-danger alert-dismissible fade show alert-modern" role="alert">
                <i class="fas fa-exclamation-circle mr-1"></i><?= htmlspecialchars($_SESSION['errorMessage']) ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <?php unset($_SESSION['errorMessage']); ?>
        <?php endif; ?>

        <!-- Quick Actions -->
        <div class="quick-actions">
            <a href="<?= htmlspecialchars($base) ?>/hr/employees" class="quick-action-btn qa-primary">
                <i class="fas fa-users"></i> View Employees
            </a>
            <a href="<?= htmlspecialchars($base) ?>/hr/uniforms" class="quick-action-btn qa-success">
                <i class="fas fa-tshirt"></i> Manage Uniforms
            </a>
            <a href="<?= htmlspecialchars($base) ?>/hr/uniforms/add" class="quick-action-btn qa-info">
                <i class="fas fa-plus"></i> Add Uniform
            </a>
            <a href="<?= htmlspecialchars($base) ?>/hr/uniforms/assign" class="quick-action-btn qa-warning">
                <i class="fas fa-user-tag"></i> Assign Uniform
            </a>
        </div>

        <!-- Chart & Recent Activity -->
        <div class="row mb-4">
            <div class="col-xl-4 mb-4 mb-xl-0">
                <div class="card dash-card shadow">
                    <div class="card-header">
                        <h6><i class="fas fa-chart-pie"></i>Uniform Coverage</h6>
                    </div>
                    <div class="card-body">
                        <?php if ($hasChartData): ?>
                            <div class="chart-wrap">
                                <canvas id="uniformCoverageChart"></canvas>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-chart-pie"></i>
                                <p class="mb-0">No employee data yet.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-xl-8">
                <div class="card dash-card shadow">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h6><i class="fas fa-history"></i>Recent Activity</h6>
                        <span class="activity-period">Last 7 days</span>
                    </div>
                    <div class="card-body">
                        <?php if (empty($recentLogs)): ?>
                            <div class="empty-state">
                                <i class="fas fa-clipboard-list"></i>
                                <p class="mb-0">No recent activity.</p>
                            </div>
                        <?php else: ?>
                            <div class="activity-feed">
                                <?php foreach (array_slice($recentLogs, 0, 10) as $log): ?>
                                    <div class="activity-item">
                                        <div class="activity-icon">
                                            <i class="fas fa-clipboard-check"></i>
                                        </div>
                                        <div>
                                            <div class="activity-action"><?= htmlspecialchars($log['action'] ?? 'Activity') ?></div>
                                            <?php if (!empty($log['details'])): ?>
                                                <div class="activity-details"><?= htmlspecialchars($log['details']) ?></div>
                                            <?php endif; ?>
                                            <div class="activity-time">
                                                <i class="far fa-clock mr-1"></i>
                                                <?= date('M d, Y H:i', strtotime($log['date_logged'] ?? 'now')) ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Employees with Assigned Uniforms -->
        <div class="card dash-card shadow mb-4">
            <div class="card-header d-flex align-items-center justify-content-between flex-wrap">
                <h6><i class="fas fa-user-check"></i>Employees with Assigned Uniforms</h6>
                <div class="d-flex align-items-center" style="gap:0.5rem;">
                    <?php if ($uniformsNeedingReorder > 0): ?>
                        <span class="reorder-alert">
                            <i class="fas fa-exclamation-triangle"></i>
                            <?= $uniformsNeedingReorder ?> need reorder
                        </span>
                    <?php endif; ?>
                    <span class="total-badge">
                        <?= $totalEmployeesWithUniforms ?> total
                    </span>
                </div>
            </div>
            <div class="card-body p-0">
                <?php if (empty($employeesWithUniforms)): ?>
                    <div class="empty-state">
                        <i class="fas fa-tshirt"></i>
                        <p class="mb-0">No employees with assigned uniforms.
                            <a href="<?= htmlspecialchars($base) ?>/hr/uniforms/assign">Assign one</a>
                        </p>
                    </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Department / Position</th>
                                <th>Uniforms</th>
                                <th>Count</th>
                                <th class="text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($employeesWithUniforms as $employee):
                                $fullName = admin_employee_full_name($employee);
                                $department = (string)($employee['department'] ?? '');
                                $position = (string)($employee['position'] ?? '');
                                $employeeId = (int)($employee['employee_id'] ?? 0);
                            ?>
                                <tr>
                                    <td>
                                        <div class="employee-name"><?= htmlspecialchars($fullName) ?></div>
                                        <div class="employee-meta">#<?= $employeeId ?></div>
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
                                        <div class="uniform-list"><?= htmlspecialchars($employee['uniforms_assigned'] ?? '—') ?></div>
                                    </td>
                                    <td>
                                        <span class="uniform-count">
                                            <i class="fas fa-tshirt mr-1"></i>
                                            <?= (int)($employee['uniform_count'] ?? 0) ?>
                                        </span>
                                    </td>
                                    <td class="text-right">
                                        <a href="<?= htmlspecialchars($base) ?>/hr/employees/detail/<?= $employeeId ?>"
                                           class="btn btn-sm btn-view-sm">
                                            <i class="fas fa-eye mr-1"></i> View
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php if ($totalEmployeesWithUniforms > 10): ?>
                    <div class="text-center py-3 border-top">
                        <a href="<?= htmlspecialchars($base) ?>/hr/uniforms/assign"
                           class="btn btn-sm btn-view-all">
                            View All Assignments
                        </a>
                    </div>
                <?php endif; ?>
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
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<?php if ($hasChartData): ?>
<script>
(function () {
    var ctx = document.getElementById('uniformCoverageChart');
    if (!ctx) return;

    new Chart(ctx.getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: <?= json_encode(array_keys($chartUniformStats)) ?>,
            datasets: [{
                data: <?= json_encode(array_values($chartUniformStats)) ?>,
                backgroundColor: ['#ec407a', '#cbd5e1'],
                hoverBackgroundColor: ['#ad1457', '#94a3b8'],
                borderColor: '#fff',
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 16,
                        usePointStyle: true,
                        font: { family: 'Nunito, sans-serif', size: 12 }
                    }
                }
            },
            cutout: '62%'
        }
    });
})();
</script>
<?php endif; ?>
</body>
</html>
