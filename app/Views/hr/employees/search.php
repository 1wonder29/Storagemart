<?php
$base = rtrim(BASE_URL, '/');
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
</head>
<body id="page-top">
    <div id="wrapper">
    <?php 
    $activePage = 'employees';
    require_once dirname(dirname(__DIR__)) . '/partials/hr/sidebar_topbar.php';?>
        <div class="container-fluid">
            <h1 class="h3 mb-4 text-gray-800">Employee Search Results</h1>

            <div class="card shadow">
                <div class="card-body">
                    <form method="GET" action="<?= htmlspecialchars($base) ?>/hr/employees/search" class="form-inline mb-3">
                        <input type="text" name="q" class="form-control mr-2" value="<?= htmlspecialchars($searchTerm ?? '') ?>" placeholder="Search by name or email..." required>
                        <button type="submit" class="btn btn-primary">Search</button>
                        <a href="<?= htmlspecialchars($base) ?>/hr/employees" class="btn btn-secondary ml-2">View All</a>
                    </form>

                    <?php if (empty($employees)): ?>
                        <p class="text-muted">No employees found matching your search.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Name</th>
                                        <th>Position</th>
                                        <th>Department</th>
                                        <th>Email</th>
                                        <th>Branch</th>
                                        <th>Actions</th>
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
                                            <td>
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

    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/jquery/jquery.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/js/storagemart.min.js"></script>
</body>
</html>
