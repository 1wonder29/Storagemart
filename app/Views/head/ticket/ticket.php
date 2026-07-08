<?php
$base = rtrim(BASE_URL, '/');
require_once __DIR__ . '/../../partials/it/ticket_view_helpers.php';

$rawTicketStats = $ticketStats ?? [];
$totalTickets = count($tickets ?? []);
$openCount = (int) ($rawTicketStats['Pending'] ?? 0) + (int) ($rawTicketStats['In Progress'] ?? 0);

$headTicketStatTone = static function (string $status): string {
    if ($status === 'Pending') {
        return 'warning';
    }
    if ($status === 'In Progress') {
        return 'info';
    }
    if ($status === 'Resolved') {
        return 'success';
    }
    if ($status === 'Closed') {
        return 'secondary';
    }
    return 'secondary';
};
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Storage Mart | Department Tickets</title>

    <link href="<?= htmlspecialchars($base) ?>/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900" rel="stylesheet">
    <link rel="icon" href="<?= htmlspecialchars($base) ?>/assets/img/sm_favicon.png" type="image/x-icon">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/storagemart.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/head-dashboard.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/role-list-page.css" rel="stylesheet">
</head>

<body id="page-top">

<div id="wrapper">
    <?php
    $activePage = 'tickets';
    require_once __DIR__ . '/../../partials/head/sidebar_topbar.php';
    ?>

    <div class="container-fluid head-dashboard-page head-ticket-page role-list-page">

        <div class="page-hero">
            <div class="row align-items-center">
                <div class="col-lg-7">
                    <h1><i class="fas fa-ticket-alt mr-2"></i>Department Tickets</h1>
                    <p>View and manage support tickets filed by employees in your department.</p>
                    <div class="hero-actions">
                        <div class="quick-nav mb-0">
                            <a href="<?= htmlspecialchars($base) ?>/head/dashboard" class="btn btn-sm btn-outline-light mr-1">
                                <i class="fas fa-tachometer-alt mr-1"></i> Dashboard
                            </a>
                            <a href="<?= htmlspecialchars($base) ?>/head/assets" class="btn btn-sm btn-outline-light">
                                <i class="fas fa-archive mr-1"></i> My Assets
                            </a>
                        </div>
                        <a href="<?= htmlspecialchars($base) ?>/head/tickets/create" class="btn btn-sm btn-create-ticket">
                            <i class="fas fa-plus mr-1"></i> Create New Ticket
                        </a>
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="row mt-3 mt-lg-0">
                        <div class="col-4">
                            <div class="hero-stat">
                                <div class="stat-value"><?= (int) $totalTickets ?></div>
                                <div class="stat-label">Total</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="hero-stat">
                                <div class="stat-value"><?= (int) $openCount ?></div>
                                <div class="stat-label">Open</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="hero-stat">
                                <div class="stat-value"><?= (int) ($rawTicketStats['Resolved'] ?? 0) ?></div>
                                <div class="stat-label">Resolved</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php if (!empty($_SESSION['flash_success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle"></i> <?= htmlspecialchars((string) $_SESSION['flash_success']) ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <?php unset($_SESSION['flash_success']); ?>
        <?php endif; ?>
        <?php if (!empty($_SESSION['flash_error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars((string) $_SESSION['flash_error']) ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <?php unset($_SESSION['flash_error']); ?>
        <?php endif; ?>

        <div class="summary-stats">
            <?php foreach ($rawTicketStats as $status => $count): ?>
                <?php $tone = $headTicketStatTone((string) $status); ?>
                <div class="summary-stat-card stat-<?= htmlspecialchars($tone) ?>">
                    <div class="stat-label"><?= htmlspecialchars((string) $status) ?></div>
                    <div class="stat-value"><?= (int) $count ?></div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="filter-toolbar">
            <div class="row align-items-end">
                <div class="col-md-3 col-sm-6 mb-2 mb-md-0">
                    <label for="statusFilter">Status</label>
                    <select id="statusFilter" class="form-control form-control-sm">
                        <option value="">All Status</option>
                        <option value="Pending">Pending</option>
                        <option value="In Progress">In Progress</option>
                        <option value="Resolved">Resolved</option>
                        <option value="Closed">Closed</option>
                    </select>
                </div>
                <div class="col-md-3 col-sm-6 mb-2 mb-md-0">
                    <label for="priorityFilter">Priority</label>
                    <select id="priorityFilter" class="form-control form-control-sm">
                        <option value="">All Priority</option>
                        <option value="Low">Low</option>
                        <option value="Medium">Medium</option>
                        <option value="High">High</option>
                    </select>
                </div>
                <div class="col-md-3 col-sm-6 mb-2 mb-md-0">
                    <label for="searchInput">Search</label>
                    <input type="text" id="searchInput" class="form-control form-control-sm" placeholder="Ticket number...">
                </div>
                <div class="col-md-3 col-sm-6 text-md-right">
                    <button type="button" class="btn btn-sm btn-reset-filters" onclick="resetFilters()">
                        <i class="fas fa-redo mr-1"></i> Reset
                    </button>
                </div>
            </div>
        </div>

        <div class="card ticket-list-card shadow mb-4">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h6><i class="fas fa-list-ul"></i>All Tickets</h6>
                <span class="ticket-count-badge"><?= (int) $totalTickets ?> ticket<?= $totalTickets === 1 ? '' : 's' ?></span>
            </div>
            <div class="card-body p-0">
                <?php if (empty($tickets)): ?>
                    <div class="empty-state">
                        <i class="fas fa-inbox d-block"></i>
                        <p class="mb-0">No tickets found.</p>
                    </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0 ticket-realtime-table" id="headTicketsTable">
                        <thead>
                            <tr>
                                <th>Ticket #</th>
                                <th>Employee</th>
                                <th>Category</th>
                                <th>Priority</th>
                                <th>Status</th>
                                <th>Filed Date</th>
                                <th>Branch</th>
                                <th class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tickets as $ticket):
                                $ticketId = (int) ($ticket['ticket_id'] ?? 0);
                                $ticketNumber = (string) ($ticket['ticket_number'] ?? '');
                                $employeeName = (string) ($ticket['employee_name'] ?? '');
                                $category = (string) ($ticket['category'] ?? '');
                                $priority = (string) ($ticket['priority'] ?? 'Low');
                                $status = (string) ($ticket['status'] ?? 'Pending');
                                $branchName = (string) ($ticket['branchName'] ?? '');
                                $date = it_ticket_format_date((string) ($ticket['date_filed'] ?? ''));
                            ?>
                                <tr data-ticket-id="<?= $ticketId ?>"
                                    data-priority="<?= htmlspecialchars(strtolower(trim($priority))) ?>"
                                    data-status="<?= htmlspecialchars(strtolower(trim($status))) ?>">
                                    <td>
                                        <div class="ticket-id"><?= htmlspecialchars($ticketNumber) ?></div>
                                    </td>
                                    <td><?= htmlspecialchars($employeeName) ?></td>
                                    <td><?= htmlspecialchars($category) ?></td>
                                    <td>
                                        <span class="priority-pill <?= it_ticket_priority_class($priority) ?>" data-ticket-priority>
                                            <?= htmlspecialchars($priority) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="status-badge <?= it_ticket_status_class($status) ?>" data-ticket-status>
                                            <?= htmlspecialchars($status) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="date-main"><?= htmlspecialchars($date['main']) ?></div>
                                    </td>
                                    <td>
                                        <?php if ($branchName !== ''): ?>
                                            <span class="branch-pill"><?= htmlspecialchars($branchName) ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-right">
                                        <div class="action-btn-group">
                                            <a href="<?= htmlspecialchars($base) ?>/head/tickets/view?id=<?= $ticketId ?>"
                                               class="btn btn-sm btn-view-ticket"
                                               title="View ticket">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <?php
                                            $ticketNumber = (string) ($ticket['ticket_number'] ?? '');
                                            $ticketStatus = $status;
                                            require __DIR__ . '/../../partials/ticket/cancel_ticket_button.php';
                                            ?>
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

            </div>
        </div>
<script src="<?= htmlspecialchars($base) ?>/assets/vendor/jquery/jquery.min.js"></script>
<script src="<?= htmlspecialchars($base) ?>/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="<?= htmlspecialchars($base) ?>/assets/js/sb-admin-2.min.js"></script>
<script>
    function resetFilters() {
        document.getElementById('statusFilter').value = '';
        document.getElementById('priorityFilter').value = '';
        document.getElementById('searchInput').value = '';
        filterTickets();
    }

    function filterTickets() {
        const statusVal = (document.getElementById('statusFilter').value || '').toLowerCase();
        const priorityVal = (document.getElementById('priorityFilter').value || '').toLowerCase();
        const searchVal = (document.getElementById('searchInput').value || '').toLowerCase();

        document.querySelectorAll('#headTicketsTable tbody tr').forEach(function (row) {
            const cells = row.querySelectorAll('td');
            if (cells.length < 8) return;

            const ticketNum = (cells[0].textContent || '').toLowerCase();
            const employee = (cells[1].textContent || '').toLowerCase();
            const category = (cells[2].textContent || '').toLowerCase();
            const priority = (cells[3].textContent || '').toLowerCase();
            const status = (cells[4].textContent || '').toLowerCase();
            const branch = (cells[6].textContent || '').toLowerCase();

            const matchesStatus = !statusVal || status.includes(statusVal);
            const matchesPriority = !priorityVal || priority.includes(priorityVal);
            const matchesSearch = !searchVal || ticketNum.includes(searchVal) || employee.includes(searchVal) || category.includes(searchVal) || branch.includes(searchVal);

            row.style.display = (matchesStatus && matchesPriority && matchesSearch) ? '' : 'none';
        });
    }

    document.getElementById('statusFilter').addEventListener('change', filterTickets);
    document.getElementById('priorityFilter').addEventListener('change', filterTickets);
    document.getElementById('searchInput').addEventListener('input', filterTickets);
</script>

<?php require __DIR__ . '/../../partials/flash_modal.php'; ?>
<?php require __DIR__ . '/../../partials/ticket/cancel_ticket_modal.php'; ?>
</body>
</html>
