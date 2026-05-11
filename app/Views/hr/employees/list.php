<?php
$base = rtrim(BASE_URL, '/');
$filters = $filters ?? [];
$departments = $departments ?? [];
$branches = $branches ?? [];

$sort = $filters['sort'] ?? 'lastname_asc';
$department = $filters['department'] ?? '';
$branch = $filters['branch'] ?? '';
$status = $filters['status'] ?? '';
$startsWith = $filters['starts_with'] ?? '';
$limit = strtolower(trim((string) ($_GET['limit'] ?? '20')));
if (!in_array($limit, ['10', '20', '50', '100', 'all'], true)) {
    $limit = '20';
}

$queryParams = $_GET;
unset($queryParams['page']);
$paginationPrefix = '?';
if (!empty($queryParams)) {
    $paginationPrefix .= http_build_query($queryParams) . '&';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Storage Mart | Employees</title>
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
            <h1 class="h3 mb-4 text-gray-800">Employees</h1>

            <!-- Search Bar -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="<?= htmlspecialchars($base) ?>/hr/employees/search" class="form-inline mb-3">
                        <input type="text" name="q" class="form-control mr-2" placeholder="Search by name or email..." required>
                        <button type="submit" class="btn btn-primary">Search</button>
                    </form>

                    <form method="GET" action="<?= htmlspecialchars($base) ?>/hr/employees" class="row">
                        <div class="col-md-3 mb-2">
                            <label class="small text-muted mb-1">Sort By</label>
                            <select name="sort" class="form-control form-control-sm">
                                <option value="lastname_asc" <?= $sort === 'lastname_asc' ? 'selected' : '' ?>>Last Name A-Z</option>
                                <option value="lastname_desc" <?= $sort === 'lastname_desc' ? 'selected' : '' ?>>Last Name Z-A</option>
                                <option value="firstname_asc" <?= $sort === 'firstname_asc' ? 'selected' : '' ?>>First Name A-Z</option>
                                <option value="firstname_desc" <?= $sort === 'firstname_desc' ? 'selected' : '' ?>>First Name Z-A</option>
                                <option value="department_asc" <?= $sort === 'department_asc' ? 'selected' : '' ?>>Department A-Z</option>
                                <option value="position_asc" <?= $sort === 'position_asc' ? 'selected' : '' ?>>Position A-Z</option>
                            </select>
                        </div>

                        <div class="col-md-2 mb-2">
                            <label class="small text-muted mb-1">Starts With (First/Last Name)</label>
                            <select name="starts_with" class="form-control form-control-sm">
                                <option value="">All</option>
                                <?php foreach (range('A', 'Z') as $letter): ?>
                                    <option value="<?= $letter ?>" <?= $startsWith === $letter ? 'selected' : '' ?>><?= $letter ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-3 mb-2">
                            <label class="small text-muted mb-1">Department</label>
                            <select name="department" class="form-control form-control-sm">
                                <option value="">All Departments</option>
                                <?php foreach ($departments as $dept): ?>
                                    <option value="<?= htmlspecialchars($dept) ?>" <?= $department === $dept ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($dept) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-2 mb-2">
                            <label class="small text-muted mb-1">Branch</label>
                            <select name="branch" class="form-control form-control-sm">
                                <option value="">All Branches</option>
                                <?php foreach ($branches as $branchItem): ?>
                                    <option value="<?= htmlspecialchars($branchItem) ?>" <?= $branch === $branchItem ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($branchItem) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-1 mb-2">
                            <label class="small text-muted mb-1">Status</label>
                            <select name="status" class="form-control form-control-sm">
                                <option value="">All</option>
                                <option value="ACTIVE" <?= $status === 'ACTIVE' ? 'selected' : '' ?>>ACTIVE</option>
                                <option value="INACTIVE" <?= $status === 'INACTIVE' ? 'selected' : '' ?>>INACTIVE</option>
                            </select>
                        </div>

                        <div class="col-md-1 mb-2">
                            <label class="small text-muted mb-1">Per Page</label>
                            <select name="limit" class="form-control form-control-sm">
                                <option value="10" <?= $limit === '10' ? 'selected' : '' ?>>10</option>
                                <option value="20" <?= $limit === '20' ? 'selected' : '' ?>>20</option>
                                <option value="50" <?= $limit === '50' ? 'selected' : '' ?>>50</option>
                                <option value="100" <?= $limit === '100' ? 'selected' : '' ?>>100</option>
                                <option value="all" <?= $limit === 'all' ? 'selected' : '' ?>>All</option>
                            </select>
                        </div>

                        <div class="col-12 mt-2">
                            <button type="submit" class="btn btn-sm btn-primary">
                                <i class="fas fa-filter"></i> Apply Filters
                            </button>
                            <a href="<?= htmlspecialchars($base) ?>/hr/employees" class="btn btn-sm btn-secondary ml-2">
                                Reset
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Employees Table -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">All Employees (<?= (int) ($totalCount ?? 0) ?> result<?= ((int) ($totalCount ?? 0) === 1) ? '' : 's' ?>, Page <?= $page ?? 1 ?>/<?= $totalPages ?? 1 ?>)</h6>
                </div>
                <div class="card-body">
                    <?php if (empty($employees)): ?>
                        <p class="text-muted">No employees found.</p>
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
                                                <a href="<?= htmlspecialchars($base) ?>/hr/employees/accountability/<?= $emp['employee_id'] ?>" 
                                                   class="btn btn-sm btn-success">
                                                    <i class="fas fa-download"></i> Form
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <nav>
                            <ul class="pagination">
                                <?php if ($page > 1): ?>
                                    <li class="page-item"><a class="page-link" href="<?= $paginationPrefix ?>page=1">First</a></li>
                                    <li class="page-item"><a class="page-link" href="<?= $paginationPrefix ?>page=<?= $page - 1 ?>">Previous</a></li>
                                <?php endif; ?>

                                <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                                    <li class="page-item <?= ($i === $page) ? 'active' : '' ?>">
                                        <a class="page-link" href="<?= $paginationPrefix ?>page=<?= $i ?>"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>

                                <?php if ($page < $totalPages): ?>
                                    <li class="page-item"><a class="page-link" href="<?= $paginationPrefix ?>page=<?= $page + 1 ?>">Next</a></li>
                                    <li class="page-item"><a class="page-link" href="<?= $paginationPrefix ?>page=<?= $totalPages ?>">Last</a></li>
                                <?php endif; ?>
                            </ul>
                        </nav>
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
