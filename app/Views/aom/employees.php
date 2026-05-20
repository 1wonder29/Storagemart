<?php
$base = rtrim(BASE_URL, '/');
$loggedFirstname = $ctx['loggedFirstname'] ?? 'AOM';
$loggedLastname = $ctx['loggedLastname'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Storage Mart | AOM Employees</title>

    <!-- Custom fonts for this template -->
    <link href="<?= htmlspecialchars($base) ?>/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900" rel="stylesheet">
    <link rel="icon" href="<?= htmlspecialchars($base) ?>/assets/img/sm_favicon.png" type="image/x-icon">

    <!-- Custom styles for this template -->
    <link href="<?= htmlspecialchars($base) ?>/assets/css/storagemart.css" rel="stylesheet">
</head>

<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper">
    <?php 
    $activePage = 'employees';
    require_once __DIR__ . '/../partials/aom/sidebar_topbar.php';?>
        <!-- Page Content -->
        <div class="container-fluid">
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">Employees</h1>
            </div>

            <!-- Branch Filter Card -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card shadow">
                        <div class="card-body">
                            <label class="form-label text-xs font-weight-bold text-gray-600 text-uppercase mb-2">Filter by Branch</label>
                            <select id="branchFilter" class="form-control form-control-sm border-left-primary">
                                <option value="">All Branches</option>
                                <?php foreach ($branches as $branch): ?>
                                    <option value="<?php echo $branch['branch_id']; ?>">
                                        <?php echo htmlspecialchars($branch['branchName']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Employees Table Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Employee List</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover" id="employeesTable">
                            <thead class="table-light">
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Position</th>
                                    <th>Department</th>
                                    <th>Branch</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($employees)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted">
                                            <i class="fas fa-inbox"></i> No employees found in your assigned branches.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($employees as $emp): ?>
                                        <tr data-branch-id="<?php echo (int)($emp['branch_id'] ?? 0); ?>">
                                            <td><strong><?php echo htmlspecialchars($emp['firstname'] . ' ' . $emp['lastname']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($emp['email']); ?></td>
                                            <td><?php echo htmlspecialchars($emp['position'] ?? '-'); ?></td>
                                            <td><?php echo htmlspecialchars($emp['department'] ?? '-'); ?></td>
                                            <td><span class="badge badge-secondary"><?php echo htmlspecialchars($emp['branchName']); ?></span></td>
                                            <td><?php if ($emp['status'] === 'ACTIVE'): ?><span class="badge badge-success">Active</span><?php else: ?><span class="badge badge-secondary">Inactive</span><?php endif; ?></td>
                                            <td><a href="<?= htmlspecialchars($base) ?>/aom/employees/detail?id=<?php echo $emp['employee_id']; ?>" class="btn btn-sm btn-primary"><i class="fas fa-eye"></i> View</a></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <!-- End of Page Content -->
    </div>
    <!-- End of Main Content -->

    </div>
    <!-- End of Content Wrapper -->

    <!-- Scripts -->
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/jquery/jquery.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/js/sb-admin-2.min.js"></script>

    <script>
        $(document).ready(function() {
            // Simple client-side filtering
            $('#branchFilter').on('change', function() {
                const filterValue = String($(this).val() || '').trim();
                $('#employeesTable tbody tr').each(function() {
                    const $row = $(this);
                    if ($row.find('td[colspan]').length) {
                        $row.toggle(filterValue === '');
                        return;
                    }
                    if (filterValue === '') {
                        $row.show();
                        return;
                    }
                    const bid = String($row.attr('data-branch-id') || '');
                    $row.toggle(bid === filterValue);
                });
            });
        });
    </script>
</body>
</html>
