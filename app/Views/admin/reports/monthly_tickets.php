<?php
$base = rtrim(BASE_URL, '/');
$months = [
    1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
    5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
    9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December',
];
$yearOptions = range((int) date('Y'), (int) date('Y') - 5);
?>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Monthly Ticket Report - Storage Mart Admin</title>

    <link href="<?= htmlspecialchars($base) ?>/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/storagemart.css" rel="stylesheet">
    <link rel="icon" href="<?= htmlspecialchars($base) ?>/assets/img/favicon.ico" type="image/x-icon">
    <link href="<?= htmlspecialchars($base) ?>/assets/vendor/datatables/datatables.min.css" rel="stylesheet">
</head>

<body id="page-top">
    <div id="wrapper">
        <?php
        $activePage = 'monthly_report';
        require_once __DIR__ . '/../../partials/admin/sidebar_topbar.php';
        ?>

        <div class="container-fluid">
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">Monthly Ticket Report</h1>
            </div>

            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Select Report Period</h6>
                </div>
                <div class="card-body">
                    <form method="GET" action="<?= htmlspecialchars($base) ?>/admin/reports/monthly-tickets" class="form-inline flex-wrap">
                        <div class="form-group mr-3 mb-2">
                            <label for="month" class="mr-2">Month</label>
                            <select name="month" id="month" class="form-control">
                                <?php foreach ($months as $num => $name): ?>
                                    <option value="<?= $num ?>" <?= ($selectedMonth ?? (int) date('n')) === $num ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($name) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group mr-3 mb-2">
                            <label for="year" class="mr-2">Year</label>
                            <select name="year" id="year" class="form-control">
                                <?php foreach ($yearOptions as $y): ?>
                                    <option value="<?= $y ?>" <?= ($selectedYear ?? (int) date('Y')) === $y ? 'selected' : '' ?>>
                                        <?= $y ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary mb-2 mr-2">
                            <i class="fas fa-search"></i> View Report
                        </button>
                        <a href="<?= htmlspecialchars($base) ?>/admin/reports/monthly-tickets/export?month=<?= (int) ($selectedMonth ?? date('n')) ?>&year=<?= (int) ($selectedYear ?? date('Y')) ?>"
                           class="btn btn-success mb-2">
                            <i class="fas fa-file-excel"></i> Download Excel
                        </a>
                    </form>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-xl-4 col-md-6 mb-4">
                    <div class="card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="text-primary text-uppercase mb-1">Report Period</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= htmlspecialchars($monthLabel ?? '') ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 col-md-6 mb-4">
                    <div class="card border-left-info shadow h-100 py-2">
                        <div class="card-body">
                            <div class="text-info text-uppercase mb-1">Total Tickets</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= (int) ($ticketCount ?? 0) ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Tickets for <?= htmlspecialchars($monthLabel ?? '') ?></h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="monthlyTicketsTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Ticket #</th>
                                    <th>Employee</th>
                                    <th>Branch</th>
                                    <th>Category</th>
                                    <th>Priority</th>
                                    <th>Status</th>
                                    <th>Date Filed</th>
                                    <th>Assigned To</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($tickets)): ?>
                                    <tr>
                                        <td colspan="8" class="text-center text-muted">No tickets found for this month.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($tickets as $row): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($row['ticket_number'] ?? '') ?></td>
                                            <td><?= htmlspecialchars(trim($row['employee_name'] ?? '')) ?></td>
                                            <td><?= htmlspecialchars($row['branchName'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($row['category'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($row['priority'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($row['status'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($row['date_filed'] ?? '') ?></td>
                                            <td><?= htmlspecialchars(trim($row['assigned_to_name'] ?? '') ?: 'Unassigned') ?></td>
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

    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/jquery/jquery.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/js/sb-admin-2.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/datatables/datatables.min.js"></script>
    <script>
    $(document).ready(function () {
        <?php if (!empty($tickets)): ?>
        $('#monthlyTicketsTable').DataTable({
            order: [[6, 'asc']],
            pageLength: 25
        });
        <?php endif; ?>
    });
    </script>

    <?php require __DIR__ . '/../../partials/flash_modal.php'; ?>
</body>

</html>
