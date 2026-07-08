<?php
$base = rtrim(BASE_URL, '/');
$loggedFirstname = $ctx['loggedFirstname'] ?? 'AOM';
$loggedLastname = $ctx['loggedLastname'] ?? '';
$branchId = (int)($_GET['id'] ?? 0);

$branchName = '';
if (!empty($branches) && $branchId > 0) {
    foreach ($branches as $b) {
        if ((int)($b['branch_id'] ?? 0) === $branchId) {
            $branchName = $b['branchName'] ?? '';
            break;
        }
    }
}

$employeeCount = count($employees ?? []);
$ticketCount = count($tickets ?? []);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Storage Mart | Branch Details</title>

    <link href="<?= htmlspecialchars($base) ?>/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900" rel="stylesheet">
    <link rel="icon" href="<?= htmlspecialchars($base) ?>/assets/img/sm_favicon.png" type="image/x-icon">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/storagemart.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/aom-dashboard.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/role-list-page.css" rel="stylesheet">
</head>

<body id="page-top">
<div id="wrapper">
<?php
$activePage = 'branches';
require_once __DIR__ . '/../partials/aom/sidebar_topbar.php';
?>

<div class="container-fluid aom-dashboard-page aom-detail-page role-form-page">

    <div class="page-hero">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1><i class="fas fa-building mr-2"></i>Branch Details<?= $branchName ? ' — ' . htmlspecialchars($branchName) : '' ?></h1>
                <p>Review employees and recent tickets for this branch.</p>
            </div>
            <div class="col-lg-4 mt-3 mt-lg-0 text-lg-right">
                <div class="row mb-3 mb-lg-0">
                    <div class="col-6">
                        <div class="hero-stat">
                            <div class="stat-value"><?= (int) $employeeCount ?></div>
                            <div class="stat-label">Employees</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="hero-stat">
                            <div class="stat-value"><?= (int) $ticketCount ?></div>
                            <div class="stat-label">Tickets</div>
                        </div>
                    </div>
                </div>
                <a href="<?= htmlspecialchars($base) ?>/aom/dashboard" class="btn btn-light btn-sm shadow-sm">
                    <i class="fas fa-arrow-left fa-sm"></i> Back
                </a>
            </div>
        </div>
    </div>

    <div class="card data-card shadow mb-4">
        <div class="card-header">
            <h6><i class="fas fa-users mr-1"></i>Employees</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Position</th>
                            <th>Department</th>
                            <th class="text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($employees)): ?>
                        <tr><td colspan="5" class="text-center text-muted py-4">No employees found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($employees as $e): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars(($e['firstname'] ?? '') . ' ' . ($e['lastname'] ?? '')) ?></strong></td>
                                <td><?= htmlspecialchars($e['email'] ?? '') ?></td>
                                <td><?= htmlspecialchars($e['position'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($e['department'] ?? '-') ?></td>
                                <td class="text-right">
                                    <a class="btn btn-sm btn-primary"
                                       href="<?= htmlspecialchars($base) ?>/aom/employees/detail?id=<?= (int)($e['employee_id'] ?? 0) ?>">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card data-card shadow mb-4">
        <div class="card-header">
            <h6><i class="fas fa-ticket-alt mr-1"></i>Recent Tickets</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Ticket #</th>
                            <th>Employee</th>
                            <th>Status</th>
                            <th>Priority</th>
                            <th>Date</th>
                            <th class="text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($tickets)): ?>
                        <tr><td colspan="6" class="text-center text-muted py-4">No tickets found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($tickets as $t): ?>
                            <tr>
                                <td><div class="ticket-id"><?= htmlspecialchars($t['ticket_number'] ?? $t['ticket_id'] ?? '') ?></div></td>
                                <td><?= htmlspecialchars(trim(($t['employee_firstname'] ?? '') . ' ' . ($t['employee_lastname'] ?? '')) ?: 'Unassigned') ?></td>
                                <td><?= htmlspecialchars($t['status'] ?? '') ?></td>
                                <td><?= htmlspecialchars($t['priority'] ?? '') ?></td>
                                <td><?= !empty($t['date_filed']) ? date('M d, Y', strtotime($t['date_filed'])) : '-' ?></td>
                                <td class="text-right">
                                    <a class="btn btn-sm btn-primary"
                                       href="<?= htmlspecialchars($base) ?>/aom/tickets/view?id=<?= (int)($t['ticket_id'] ?? 0) ?>">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
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
</body>
</html>
