<?php
$base = rtrim(BASE_URL, '/');
$routePrefix = $routePrefix ?? 'hom';
$roleLabel = ($user_role ?? '') === 'OM' ? 'OM' : 'HOM';
$loggedFirstname = $ctx['loggedFirstname'] ?? 'HOM';
$loggedLastname = $ctx['loggedLastname'] ?? '';

$rawTicketStats = $ticketStats ?? [];
$statusOrder = ['Open', 'In Progress', 'Cancelled', 'Resolved', 'Closed'];
$summaryTicketStats = [];
foreach ($statusOrder as $status) {
    $summaryTicketStats[$status] = (int) ($rawTicketStats[$status] ?? 0);
}
foreach ($rawTicketStats as $status => $count) {
    if (!isset($summaryTicketStats[$status])) {
        $summaryTicketStats[$status] = (int) $count;
    }
}

$homTicketStatTone = static function (string $status): string {
    if ($status === 'Open') {
        return 'warning';
    }
    if ($status === 'In Progress') {
        return 'info';
    }
    if ($status === 'Resolved') {
        return 'success';
    }
    if ($status === 'Cancelled') {
        return 'danger';
    }
    return 'secondary';
};

$totalTickets = count($tickets ?? []);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Storage Mart | <?= htmlspecialchars($roleLabel) ?> Tickets</title>

    <link href="<?= htmlspecialchars($base) ?>/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900" rel="stylesheet">
    <link rel="icon" href="<?= htmlspecialchars($base) ?>/assets/img/sm_favicon.png" type="image/x-icon">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/storagemart.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/om-dashboard.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/role-list-page.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/searchable-select.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/bulk-transfer-modal.css" rel="stylesheet">
</head>

<body id="page-top">

<div id="wrapper">
    <?php
    $activePage = 'tickets';
    require_once __DIR__ . '/../../partials/hom/sidebar_topbar.php';
    ?>
    <div class="container-fluid om-dashboard-page hom-ticket-page role-list-page">

        <div class="page-hero">
            <div class="row align-items-center">
                <div class="col-lg-7">
                    <h1><i class="fas fa-ticket-alt mr-2"></i>Tickets</h1>
                    <p>Manage operations support tickets across branches with bulk transfer and quick filters.</p>
                    <div class="hero-actions mt-3">
                        <?php if (!empty($enableBulkTransfer) && !empty($branches)): ?>
                        <button type="button" class="btn btn-sm btn-warning shadow-sm mr-2" data-toggle="modal" data-target="#bulkTransferModal">
                            <i class="fas fa-exchange-alt fa-sm"></i> Bulk Transfer
                        </button>
                        <?php endif; ?>
                        <a href="<?= htmlspecialchars($base) ?>/<?= htmlspecialchars($routePrefix) ?>/tickets/create/my" class="btn btn-sm btn-outline-light mr-1">
                            <i class="fas fa-user fa-sm"></i> My Ticket
                        </a>
                        <a href="<?= htmlspecialchars($base) ?>/<?= htmlspecialchars($routePrefix) ?>/tickets/create/employee" class="btn btn-sm btn-light shadow-sm">
                            <i class="fas fa-plus fa-sm"></i> Employee Ticket
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <?php if (!empty($_SESSION['flash_success'])): ?>
            <div class="alert alert-success alert-dismissible fade show alert-modern" role="alert">
                <i class="fas fa-check-circle"></i> <?= htmlspecialchars((string) $_SESSION['flash_success']) ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <?php unset($_SESSION['flash_success']); ?>
        <?php endif; ?>
        <?php if (!empty($_SESSION['flash_error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show alert-modern" role="alert">
                <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars((string) $_SESSION['flash_error']) ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <?php unset($_SESSION['flash_error']); ?>
        <?php endif; ?>

        <div class="summary-stats">
            <div class="summary-stat-card stat-secondary">
                <div class="stat-label">Total</div>
                <div class="stat-value"><?= (int) $totalTickets ?></div>
            </div>
            <?php foreach ($summaryTicketStats as $status => $count): ?>
                <?php $tone = $homTicketStatTone($status); ?>
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
                        <option value="Open">Open</option>
                        <option value="In Progress">In Progress</option>
                        <option value="Cancelled">Cancelled</option>
                        <option value="Resolved">Resolved</option>
                        <option value="Closed">Closed</option>
                    </select>
                </div>
                <div class="col-md-3 col-sm-6 mb-2 mb-md-0">
                    <label for="branchFilter">Branch</label>
                    <select id="branchFilter" class="form-control form-control-sm">
                        <option value="">All Branches</option>
                        <?php if (!empty($enableBulkTransfer)): ?>
                            <?php foreach ($branches as $branch): ?>
                                <?php $bn = trim((string)($branch['branchName'] ?? '')); ?>
                                <option value="<?php echo htmlspecialchars($bn, ENT_QUOTES, 'UTF-8'); ?>">
                                    <?php echo htmlspecialchars($bn, ENT_QUOTES, 'UTF-8'); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
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
            </div>
            <div class="row mt-2">
                <div class="col-md-3 col-sm-6 text-md-right ml-md-auto">
                    <button type="button" class="btn btn-sm btn-reset-filters w-100" onclick="resetFilters()">
                        <i class="fas fa-redo mr-1"></i> Reset
                    </button>
                </div>
            </div>
        </div>

        <div class="card ticket-list-card shadow mb-4">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h6><i class="fas fa-list-ul mr-1"></i>All Operations Tickets</h6>
                <span class="ticket-count-badge"><?= (int) $totalTickets ?> ticket<?= $totalTickets === 1 ? '' : 's' ?></span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 ticket-realtime-table" id="homTicketsTable">
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
                            <?php if (!empty($tickets)): ?>
                                <?php foreach ($tickets as $ticket): ?>
                                    <?php
                                    $ticketId = (int) ($ticket['ticket_id'] ?? 0);
                                    $priority = (string) ($ticket['priority'] ?? 'Low');
                                    $status = (string) ($ticket['status'] ?? 'Open');
                                    $priorityClass = $priority === 'High' ? 'danger' : ($priority === 'Medium' ? 'warning' : 'success');
                                    $statusClass = $status === 'Open' ? 'warning' : ($status === 'In Progress' ? 'info' : ($status === 'Resolved' ? 'success' : 'secondary'));
                                    ?>
                                    <tr data-ticket-id="<?= $ticketId ?>"
                                        data-filter-branch="<?= htmlspecialchars(strtolower(trim((string)($ticket['branchName'] ?? ''))), ENT_QUOTES, 'UTF-8') ?>"
                                        data-priority="<?= htmlspecialchars(strtolower(trim($priority))) ?>"
                                        data-status="<?= htmlspecialchars(strtolower(trim($status))) ?>">
                                        <td><div class="ticket-id"><?php echo htmlspecialchars((string) ($ticket['ticket_number'] ?? '')); ?></div></td>
                                        <td><?php echo htmlspecialchars((string) ($ticket['employee_name'] ?? '')); ?></td>
                                        <td><?php echo htmlspecialchars((string) ($ticket['category'] ?? '')); ?></td>
                                        <td>
                                            <span class="badge badge-<?php echo $priorityClass; ?>" data-ticket-priority>
                                                <?php echo htmlspecialchars($priority); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge badge-<?php echo $statusClass; ?> status-badge" data-ticket-status>
                                                <?php echo htmlspecialchars($status); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars(date('M d, Y', strtotime((string) ($ticket['date_filed'] ?? '')))); ?></td>
                                        <td><?php echo htmlspecialchars((string) ($ticket['branchName'] ?? '')); ?></td>
                                        <td class="text-right">
                                            <div class="action-btn-group">
                                            <a href="<?= htmlspecialchars($base) ?>/<?= htmlspecialchars($routePrefix) ?>/tickets/view?id=<?php echo (int) ($ticket['ticket_id'] ?? 0); ?>" class="btn btn-sm btn-info" title="View ticket">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <?php
                                            $ticketId = (int) ($ticket['ticket_id'] ?? 0);
                                            $ticketStatus = $status;
                                            $ticketNumber = (string) ($ticket['ticket_number'] ?? '');
                                            require __DIR__ . '/../../partials/ticket/cancel_ticket_button.php';
                                            ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-muted">
                                        No tickets found
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>
</div>

<script src="<?= htmlspecialchars($base) ?>/assets/vendor/jquery/jquery.min.js"></script>
<script src="<?= htmlspecialchars($base) ?>/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="<?= htmlspecialchars($base) ?>/assets/js/sb-admin-2.min.js"></script>
<?php if (!empty($enableBulkTransfer)): ?>
<script src="<?= htmlspecialchars($base) ?>/assets/js/searchable-select.js"></script>
<script src="<?= htmlspecialchars($base) ?>/assets/js/bulk-transfer-tickets.js"></script>
<?php endif; ?>
<script>
    function resetFilters() {
        document.getElementById('statusFilter').value = '';
        document.getElementById('priorityFilter').value = '';
        const branchFilter = document.getElementById('branchFilter');
        if (branchFilter) branchFilter.value = '';
        document.getElementById('searchInput').value = '';
        filterTickets();
    }

    function filterTickets() {
        const statusVal = (document.getElementById('statusFilter').value || '').toLowerCase();
        const priorityVal = (document.getElementById('priorityFilter').value || '').toLowerCase();
        const branchVal = (document.getElementById('branchFilter')?.value || '').toLowerCase();
        const searchVal = (document.getElementById('searchInput').value || '').toLowerCase();

        document.querySelectorAll('#homTicketsTable tbody tr').forEach(function (row) {
            const cells = row.querySelectorAll('td');
            if (cells.length < 8) return;

            const ticketNum = (cells[0].textContent || '').toLowerCase();
            const employee = (cells[1].textContent || '').toLowerCase();
            const category = (cells[2].textContent || '').toLowerCase();
            const priority = (cells[3].textContent || '').toLowerCase();
            const status = (cells[4].textContent || '').toLowerCase();
            const branch = (row.getAttribute('data-filter-branch') || cells[6].textContent || '').toLowerCase();

            const matchesStatus = !statusVal || status.includes(statusVal);
            const matchesPriority = !priorityVal || priority.includes(priorityVal);
            const matchesBranch = !branchVal || branch === branchVal;
            const matchesSearch = !searchVal || ticketNum.includes(searchVal) || employee.includes(searchVal) || category.includes(searchVal) || branch.includes(searchVal);

            row.style.display = (matchesStatus && matchesPriority && matchesBranch && matchesSearch) ? '' : 'none';
        });
    }

    document.getElementById('statusFilter').addEventListener('change', filterTickets);
    document.getElementById('priorityFilter').addEventListener('change', filterTickets);
    const branchFilterEl = document.getElementById('branchFilter');
    if (branchFilterEl) branchFilterEl.addEventListener('change', filterTickets);
    document.getElementById('searchInput').addEventListener('input', filterTickets);

    <?php if (!empty($enableBulkTransfer) && !empty($branches)): ?>
    $(function () {
        if (window.initBulkTransferTickets) {
            window.initBulkTransferTickets({
                base: <?= json_encode($base) ?>,
                routePrefix: 'hom',
                allOperationsEmployees: <?= json_encode($operationsEmployees ?? []) ?>
            });
        }
    });
    <?php endif; ?>
</script>
<?php if (!empty($enableBulkTransfer) && !empty($branches)): ?>
<?php
$routePrefix = 'hom';
require __DIR__ . '/../../partials/ticket/bulk_transfer_modal.php';
?>
<?php endif; ?>
<?php require __DIR__ . '/../../partials/ticket/cancel_ticket_modal.php'; ?>
</body>
</html>
