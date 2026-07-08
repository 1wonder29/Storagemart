<?php
$base = rtrim(BASE_URL, '/');
$loggedFirstname = $ctx['loggedFirstname'] ?? 'AOM';
$loggedLastname = $ctx['loggedLastname'] ?? '';

$rawTicketStats = $ticketStats ?? [];
$statusOrder = ['Pending', 'In Progress', 'Cancelled', 'Resolved'];
$summaryTicketStats = [];
foreach ($statusOrder as $status) {
    $count = (int)($rawTicketStats[$status] ?? 0);
    if ($count > 0 || $status === 'Resolved') {
        $summaryTicketStats[$status] = $count;
    }
}
foreach ($rawTicketStats as $status => $count) {
    if (!isset($summaryTicketStats[$status])) {
        $summaryTicketStats[$status] = (int)$count;
    }
}

$aomTicketStatTone = static function (string $status): string {
    if ($status === 'Pending') {
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
$openCount = (int) ($summaryTicketStats['Pending'] ?? 0) + (int) ($summaryTicketStats['In Progress'] ?? 0);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Storage Mart | AOM Tickets</title>

    <link href="<?= htmlspecialchars($base) ?>/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900" rel="stylesheet">
    <link rel="icon" href="<?= htmlspecialchars($base) ?>/assets/img/sm_favicon.png" type="image/x-icon">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/storagemart.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/aom-dashboard.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/role-list-page.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/searchable-select.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/bulk-transfer-modal.css" rel="stylesheet">
</head>

<body id="page-top">

<div id="wrapper">
    <?php
    $activePage = 'tickets';
    require_once __DIR__ . '/../partials/aom/sidebar_topbar.php';
    ?>
    <div class="container-fluid aom-dashboard-page aom-ticket-page role-list-page">

        <div class="page-hero">
            <div class="row align-items-center">
                <div class="col-lg-7">
                    <h1><i class="fas fa-ticket-alt mr-2"></i>Tickets</h1>
                    <p>Manage branch support tickets, bulk transfers, and employee requests in one place.</p>
                    <div class="hero-actions mt-3">
                        <?php if (!empty($branches)): ?>
                        <button type="button" class="btn btn-sm btn-warning shadow-sm mr-2" data-toggle="modal" data-target="#bulkTransferModal">
                            <i class="fas fa-exchange-alt fa-sm"></i> Bulk Transfer
                        </button>
                        <?php endif; ?>
                        <a href="<?= htmlspecialchars($base) ?>/aom/tickets/create/my" class="btn btn-sm btn-outline-light mr-1">
                            <i class="fas fa-user fa-sm"></i> My Ticket
                        </a>
                        <a href="<?= htmlspecialchars($base) ?>/aom/tickets/create/employee" class="btn btn-sm btn-light shadow-sm">
                            <i class="fas fa-plus fa-sm"></i> Employee Ticket
                        </a>
                    </div>
                </div>
                <div class="col-lg-5 mt-3 mt-lg-0">
                    <div class="row">
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
                                <div class="stat-value"><?= (int) ($summaryTicketStats['Resolved'] ?? 0) ?></div>
                                <div class="stat-label">Resolved</div>
                            </div>
                        </div>
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
            <?php foreach ($summaryTicketStats as $status => $count): ?>
                <?php $tone = $aomTicketStatTone($status); ?>
                <div class="summary-stat-card stat-<?= htmlspecialchars($tone) ?>">
                    <div class="stat-label"><?= htmlspecialchars($status) ?></div>
                    <div class="stat-value"><?= (int) $count ?></div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="filter-toolbar">
            <div class="row align-items-end">
                <div class="col-md-4 col-sm-6 mb-2 mb-md-0">
                    <label for="statusFilter">Status</label>
                    <select id="statusFilter" class="form-control form-control-sm">
                        <option value="">All Status</option>
                        <option value="Pending">Pending</option>
                        <option value="In Progress">In Progress</option>
                        <option value="Cancelled">Cancelled</option>
                        <option value="Resolved">Resolved</option>
                        <option value="Closed">Closed</option>
                    </select>
                </div>
                <div class="col-md-4 col-sm-6 mb-2 mb-md-0">
                    <label for="branchFilter">Branch</label>
                    <select id="branchFilter" class="form-control form-control-sm">
                        <option value="">All Branches</option>
                        <?php foreach ($branches as $branch): ?>
                            <?php $bn = trim((string)($branch['branchName'] ?? '')); ?>
                            <option value="<?php echo htmlspecialchars($bn, ENT_QUOTES, 'UTF-8'); ?>">
                                <?php echo htmlspecialchars($bn, ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4 col-sm-6 mb-2 mb-md-0">
                    <label for="priorityFilter">Priority</label>
                    <select id="priorityFilter" class="form-control form-control-sm">
                        <option value="">All Priorities</option>
                        <option value="Low">Low</option>
                        <option value="Medium">Medium</option>
                        <option value="High">High</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="card ticket-list-card shadow mb-4">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h6><i class="fas fa-list-ul mr-1"></i>All Tickets</h6>
                <span class="ticket-count-badge"><?= (int) $totalTickets ?> ticket<?= $totalTickets === 1 ? '' : 's' ?></span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 ticket-realtime-table" id="aomTicketsTable">
                        <thead>
                            <tr>
                                <th>Ticket #</th>
                                <th>Employee</th>
                                <th>Branch</th>
                                <th>Category</th>
                                <th>Priority</th>
                                <th>Status</th>
                                <th>Date Filed</th>
                                <th class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($tickets)): ?>
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-muted">
                                        <i class="fas fa-inbox"></i> No tickets found.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($tickets as $ticket): ?>
                                    <?php
                                    $fb = strtolower(trim((string)($ticket['branchName'] ?? '')));
                                    $fs = strtolower(trim((string)($ticket['status'] ?? '')));
                                    $fp = strtolower(trim((string)($ticket['priority'] ?? '')));
                                    $priorityClass = $ticket['priority'] === 'High' ? 'danger' :
                                                    ($ticket['priority'] === 'Medium' ? 'warning' : 'info');
                                    $statusClass = $ticket['status'] === 'Pending' ? 'warning' :
                                                  ($ticket['status'] === 'In Progress' ? 'info' :
                                                   ($ticket['status'] === 'Resolved' ? 'success' : 'secondary'));
                                    ?>
                                    <tr data-ticket-id="<?= (int) ($ticket['ticket_id'] ?? 0) ?>"
                                        data-filter-branch="<?php echo htmlspecialchars($fb, ENT_QUOTES, 'UTF-8'); ?>"
                                        data-filter-status="<?php echo htmlspecialchars($fs, ENT_QUOTES, 'UTF-8'); ?>"
                                        data-filter-priority="<?php echo htmlspecialchars($fp, ENT_QUOTES, 'UTF-8'); ?>"
                                        data-status="<?php echo htmlspecialchars($fs, ENT_QUOTES, 'UTF-8'); ?>"
                                        data-priority="<?php echo htmlspecialchars($fp, ENT_QUOTES, 'UTF-8'); ?>">
                                        <td><div class="ticket-id"><?php echo htmlspecialchars($ticket['ticket_number']); ?></div></td>
                                        <td>
                                            <?php
                                            $first = $ticket['firstname'] ?? ($ticket['employee_firstname'] ?? '');
                                            $last  = $ticket['lastname'] ?? ($ticket['employee_lastname'] ?? '');
                                            $fullName = trim($first . ' ' . $last);
                                            echo htmlspecialchars($fullName !== '' ? $fullName : 'Unassigned');
                                            ?>
                                        </td>
                                        <td>
                                            <span class="badge badge-secondary">
                                                <?php echo htmlspecialchars($ticket['branchName']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($ticket['category'] ?? '-'); ?></td>
                                        <td>
                                            <span class="badge badge-<?php echo $priorityClass; ?>" data-ticket-priority>
                                                <?php echo htmlspecialchars($ticket['priority']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge badge-<?php echo $statusClass; ?> status-badge" data-ticket-status>
                                                <?php echo htmlspecialchars($ticket['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($ticket['date_filed'])); ?></td>
                                        <td class="text-right">
                                            <div class="action-btn-group">
                                            <a href="<?= htmlspecialchars($base) ?>/aom/tickets/view?id=<?php echo (int) $ticket['ticket_id']; ?>"
                                               class="btn btn-sm btn-primary" title="View ticket">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <?php
                                            $ticketId = (int) ($ticket['ticket_id'] ?? 0);
                                            $ticketStatus = (string) ($ticket['status'] ?? '');
                                            $ticketNumber = (string) ($ticket['ticket_number'] ?? '');
                                            require __DIR__ . '/../partials/ticket/cancel_ticket_button.php';
                                            ?>
                                            </div>
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
<script src="<?= htmlspecialchars($base) ?>/assets/js/searchable-select.js"></script>
<script src="<?= htmlspecialchars($base) ?>/assets/js/bulk-transfer-tickets.js"></script>

<script>
    $(document).ready(function() {
        function applyFilters() {
            const statusFilter = ($('#statusFilter').val() || '').trim().toLowerCase();
            const branchFilter = ($('#branchFilter').val() || '').trim().toLowerCase();
            const priorityFilter = ($('#priorityFilter').val() || '').trim().toLowerCase();
            const anyFilter = !!(statusFilter || branchFilter || priorityFilter);

            $('#aomTicketsTable tbody tr').each(function() {
                const $row = $(this);
                if ($row.find('td[colspan]').length) {
                    $row.toggle(!anyFilter);
                    return;
                }
                const branch = ($row.attr('data-filter-branch') || '').trim().toLowerCase();
                const status = ($row.attr('data-filter-status') || '').trim().toLowerCase();
                const priority = ($row.attr('data-filter-priority') || '').trim().toLowerCase();

                let show = true;
                if (statusFilter && status !== statusFilter) show = false;
                if (branchFilter && branch !== branchFilter) show = false;
                if (priorityFilter && priority !== priorityFilter) show = false;

                $row.toggle(show);
            });
        }

        $('#statusFilter, #branchFilter, #priorityFilter').on('change', applyFilters);

        if (window.initBulkTransferTickets) {
            window.initBulkTransferTickets({
                base: <?= json_encode($base) ?>,
                routePrefix: 'aom',
                allOperationsEmployees: <?= json_encode($operationsEmployees ?? []) ?>
            });
        }
    });
</script>
<?php if (!empty($branches)): ?>
<?php
$routePrefix = 'aom';
$bulkTransferAction = rtrim($base, '/') . '/aom/tickets/transfer';
require __DIR__ . '/../partials/ticket/bulk_transfer_modal.php';
?>
<?php endif; ?>
<?php require __DIR__ . '/../partials/ticket/cancel_ticket_modal.php'; ?>
</body>
</html>
