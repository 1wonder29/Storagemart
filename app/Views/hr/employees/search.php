<?php
$base = rtrim(BASE_URL, '/');
$resultCount = count($employees ?? []);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Storage Mart | Search Employees</title>
    <link href="<?= htmlspecialchars($base) ?>/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900" rel="stylesheet">
    <link rel="icon" href="<?= htmlspecialchars($base) ?>/assets/img/sm_favicon.png" type="image/x-icon">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/storagemart.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/hr-dashboard.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/hr-pages.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/role-list-page.css" rel="stylesheet">
</head>
<body id="page-top">
    <div id="wrapper">
    <?php 
    $activePage = 'employees';
    require_once dirname(dirname(__DIR__)) . '/partials/hr/sidebar_topbar.php';?>
        <div class="container-fluid hr-dashboard-page role-form-page">

            <div class="page-hero">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <h1><i class="fas fa-search mr-2"></i>Employee Search Results</h1>
                        <p>Results for "<?= htmlspecialchars($searchTerm ?? '') ?>"</p>
                    </div>
                    <div class="col-lg-4 mt-3 mt-lg-0 text-lg-right">
                        <div class="hero-stat d-inline-block text-center px-4">
                            <div class="stat-value"><?= (int) $resultCount ?></div>
                            <div class="stat-label">Matches</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="filter-toolbar">
                <form method="GET" action="<?= htmlspecialchars($base) ?>/hr/employees/search" class="form-inline">
                    <input type="text" name="q" class="form-control mr-2 mb-2 mb-md-0" value="<?= htmlspecialchars($searchTerm ?? '') ?>" placeholder="Search by name or email..." required>
                    <button type="submit" class="btn btn-primary mr-2 mb-2 mb-md-0">Search</button>
                    <a href="<?= htmlspecialchars($base) ?>/hr/employees" class="btn btn-secondary mb-2 mb-md-0">View All</a>
                </form>
            </div>

            <div class="card data-card shadow">
                <div class="card-header">
                    <h6><i class="fas fa-users mr-1"></i>Search Results</h6>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($employees)): ?>
                        <div class="empty-state p-4">
                            <p class="text-muted mb-0">No employees found matching your search.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Position</th>
                                        <th>Department</th>
                                        <th>Email</th>
                                        <th>Branch</th>
                                        <th class="text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($employees as $emp): ?>
                                        <tr>
                                            <td><strong><?= htmlspecialchars($emp['firstname'] . ' ' . $emp['lastname']) ?></strong></td>
                                            <td><?= htmlspecialchars($emp['position']) ?></td>
                                            <td><?= htmlspecialchars($emp['department']) ?></td>
                                            <td><?= htmlspecialchars($emp['email']) ?></td>
                                            <td><?= htmlspecialchars($emp['branchName'] ?? 'N/A') ?></td>
                                            <td class="text-right">
                                                <a href="<?= htmlspecialchars($base) ?>/hr/employees/detail/<?= $emp['employee_id'] ?>" 
                                                   class="btn btn-sm btn-primary">
                                                    <i class="fas fa-eye"></i> View
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
    </div>
    </div>

            </div>
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/jquery/jquery.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/js/storagemart.min.js"></script>
</body>
</html>
