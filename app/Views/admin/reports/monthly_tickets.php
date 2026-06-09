<?php
$base = rtrim(BASE_URL, '/');
require_once __DIR__ . '/../../partials/it/ticket_view_helpers.php';

$months = [
    1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
    5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
    9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December',
];
$yearOptions = range((int) date('Y'), (int) date('Y') - 5);
$ticketCount = (int) ($ticketCount ?? 0);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Monthly Ticket Report - Storage Mart Admin</title>
    <link href="<?= htmlspecialchars($base) ?>/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/storagemart.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/admin-ticket-list.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/admin-monthly-report.css" rel="stylesheet">
    <link rel="icon" href="<?= htmlspecialchars($base) ?>/assets/img/favicon.ico" type="image/x-icon">
    <link href="<?= htmlspecialchars($base) ?>/assets/vendor/datatables/datatables.min.css" rel="stylesheet">
</head>

<body id="page-top">
    <div id="wrapper">
        <?php
        $activePage = 'monthly_report';
        require_once __DIR__ . '/../../partials/admin/sidebar_topbar.php';
        ?>

        <div class="container-fluid admin-ticket-page admin-monthly-report-page">

            <div class="page-hero">
                <h1><i class="fas fa-clipboard-list mr-2"></i>Monthly Ticket Report</h1>
                <p>Generate and export ticket activity by month — review filed tickets, statuses, and assignments for any period.</p>
            </div>

            <div class="filter-toolbar">
                <div class="toolbar-title"><i class="fas fa-calendar-alt"></i>Select Report Period</div>
                <form method="GET" action="<?= htmlspecialchars($base) ?>/admin/reports/monthly-tickets">
                    <div class="row align-items-end">
                        <div class="col-md-3 col-sm-6 mb-3 mb-md-0">
                            <label for="month">Month</label>
                            <select name="month" id="month" class="form-control">
                                <?php foreach ($months as $num => $name): ?>
                                    <option value="<?= $num ?>" <?= ($selectedMonth ?? (int) date('n')) === $num ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($name) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-3 mb-md-0">
                            <label for="year">Year</label>
                            <select name="year" id="year" class="form-control">
                                <?php foreach ($yearOptions as $y): ?>
                                    <option value="<?= $y ?>" <?= ($selectedYear ?? (int) date('Y')) === $y ? 'selected' : '' ?>>
                                        <?= $y ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 col-sm-12 text-md-right">
                            <button type="submit" class="btn btn-primary btn-view mr-2 mb-2 mb-md-0">
                                <i class="fas fa-search mr-1"></i> View Report
                            </button>
                            <a href="<?= htmlspecialchars($base) ?>/admin/reports/monthly-tickets/export?month=<?= (int) ($selectedMonth ?? date('n')) ?>&year=<?= (int) ($selectedYear ?? date('Y')) ?>"
                               class="btn btn-export mb-2 mb-md-0">
                                <i class="fas fa-file-excel mr-1"></i> Download Excel
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            <div class="row summary-row">
                <div class="col-md-6 mb-3 mb-md-0">
                    <div class="summary-card summary-card-period">
                        <div class="summary-card-icon"><i class="fas fa-calendar-check"></i></div>
                        <div>
                            <span class="summary-card-label">Report Period</span>
                            <span class="summary-card-value"><?= htmlspecialchars($monthLabel ?? '') ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="summary-card summary-card-total">
                        <div class="summary-card-icon"><i class="fas fa-ticket-alt"></i></div>
                        <div>
                            <span class="summary-card-label">Total Tickets</span>
                            <span class="summary-card-value"><?= $ticketCount ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card report-list-card ticket-list-card shadow mb-4">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap">
                    <h6><i class="fas fa-list-ul mr-1 text-primary"></i> Tickets for <?= htmlspecialchars($monthLabel ?? '') ?></h6>
                    <span class="ticket-count-badge"><?= $ticketCount ?> ticket<?= $ticketCount === 1 ? '' : 's' ?></span>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($tickets)): ?>
                        <div class="empty-state">
                            <i class="fas fa-inbox d-block"></i>
                            No tickets found for this month.
                        </div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="monthlyTicketsTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Ticket</th>
                                    <th>Employee</th>
                                    <th>Branch</th>
                                    <th>Category</th>
                                    <th>Priority / Status</th>
                                    <th>Date Filed</th>
                                    <th>Assigned To</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($tickets as $row):
                                    $status = (string) ($row['status'] ?? '');
                                    $priority = (string) ($row['priority'] ?? '');
                                    $date = it_ticket_format_date((string) ($row['date_filed'] ?? ''));
                                    $assignedName = trim((string) ($row['assigned_to_name'] ?? ''));
                                ?>
                                    <tr>
                                        <td>
                                            <span class="ticket-id"><?= htmlspecialchars($row['ticket_number'] ?? '') ?></span>
                                        </td>
                                        <td>
                                            <div class="employee-name"><?= htmlspecialchars(trim($row['employee_name'] ?? '')) ?></div>
                                        </td>
                                        <td>
                                            <?php if (!empty($row['branchName'])): ?>
                                                <span class="branch-pill">
                                                    <i class="fas fa-map-marker-alt mr-1"></i><?= htmlspecialchars($row['branchName']) ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted">—</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($row['category'])): ?>
                                                <span class="category-pill"><?= htmlspecialchars($row['category']) ?></span>
                                            <?php else: ?>
                                                <span class="text-muted">—</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($priority !== ''): ?>
                                                <span class="priority-pill <?= it_ticket_priority_class($priority) ?>">
                                                    <i class="fas fa-flag"></i> <?= htmlspecialchars($priority) ?>
                                                </span>
                                            <?php endif; ?>
                                            <?php if ($status !== ''): ?>
                                                <span class="status-badge <?= it_ticket_status_class($status) ?> mt-1">
                                                    <?= htmlspecialchars($status) ?>
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="date-cell" data-order="<?= (int) $date['order'] ?>">
                                            <div class="date-main"><?= htmlspecialchars($date['main']) ?></div>
                                            <?php if ($date['time'] !== ''): ?>
                                                <div class="date-time"><?= htmlspecialchars($date['time']) ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($assignedName !== ''): ?>
                                                <span class="assignee-hint">
                                                    <i class="fas fa-user-cog mr-1"></i><?= htmlspecialchars($assignedName) ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="not-assigned-label">Unassigned</span>
                                            <?php endif; ?>
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
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/js/sb-admin-2.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/datatables/datatables.min.js"></script>
    <script>
    $(document).ready(function () {
        <?php if (!empty($tickets)): ?>
        $('#monthlyTicketsTable').DataTable({
            order: [[5, 'asc']],
            pageLength: 25
        });
        <?php endif; ?>
    });
    </script>

    <?php require __DIR__ . '/../../partials/flash_modal.php'; ?>
</body>

</html>
