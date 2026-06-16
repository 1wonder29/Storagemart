<?php
$base = rtrim(BASE_URL, '/');
$loggedFirstname = $loggedFirstname ?? 'Employee';
$hasAssets = (int) ($assetsCount ?? 0) > 0;
$noAssetsTitle = 'You need at least one assigned asset to create a ticket.';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Storage Mart | My Tickets</title>

    <link href="<?= htmlspecialchars($base) ?>/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900" rel="stylesheet">
    <link rel="icon" href="<?= htmlspecialchars($base) ?>/assets/img/sm_favicon.png" type="image/x-icon">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/storagemart.css" rel="stylesheet">
</head>

<body id="page-top">

<div id="wrapper">
    <?php
    $activePage = 'tickets';
    require_once __DIR__ . '/../../partials/employee/sidebar_topbar.php';
    ?>
    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Tickets</h1>
            <?php if ($hasAssets): ?>
            <a href="<?= htmlspecialchars($base) ?>/employee/assets/file_ticket" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                <i class="fas fa-plus fa-sm text-white-50"></i> Create New Ticket
            </a>
            <?php else: ?>
            <span class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm disabled"
                  title="<?= htmlspecialchars($noAssetsTitle) ?>"
                  data-toggle="tooltip"
                  data-placement="bottom">
                <i class="fas fa-plus fa-sm text-white-50"></i> Create New Ticket
            </span>
            <?php endif; ?>
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

        <div class="row">
            <?php foreach ($ticketStats ?? [] as $status => $count): ?>
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-<?php echo $status === 'Pending' ? 'warning' : ($status === 'In Progress' ? 'info' : ($status === 'Resolved' ? 'success' : 'secondary')); ?> shadow h-100 py-2">
                        <div class="card-body">
                            <div class="text-xs font-weight-bold text-<?php echo $status === 'Pending' ? 'warning' : ($status === 'In Progress' ? 'info' : ($status === 'Resolved' ? 'success' : 'secondary')); ?> text-uppercase mb-1">
                                <?php echo htmlspecialchars((string) $status); ?>
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo (int) $count; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Filters</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <label class="form-label text-xs font-weight-bold text-gray-600 text-uppercase mb-2">Status</label>
                        <select id="statusFilter" class="form-control form-control-sm">
                            <option value="">All Status</option>
                            <option value="Pending">Pending</option>
                            <option value="In Progress">In Progress</option>
                            <option value="Resolved">Resolved</option>
                            <option value="Closed">Closed</option>
                            <option value="Cancelled">Cancelled</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-xs font-weight-bold text-gray-600 text-uppercase mb-2">Priority</label>
                        <select id="priorityFilter" class="form-control form-control-sm">
                            <option value="">All Priority</option>
                            <option value="Low">Low</option>
                            <option value="Medium">Medium</option>
                            <option value="High">High</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-xs font-weight-bold text-gray-600 text-uppercase mb-2">Search</label>
                        <input type="text" id="searchInput" class="form-control form-control-sm" placeholder="Ticket number...">
                    </div>
                    <div class="col-md-3 align-self-end">
                        <button class="btn btn-secondary btn-sm w-100" onclick="resetFilters()">
                            <i class="fas fa-redo"></i> Reset
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">All Tickets</h6>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0 ticket-realtime-table" id="employeeTicketsTable">
                    <thead class="bg-light">
                        <tr>
                            <th>Ticket #</th>
                            <th>Category</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Filed Date</th>
                            <th>Branch</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($tickets)): ?>
                            <?php foreach ($tickets as $ticket): ?>
                                <tr data-ticket-id="<?= (int) ($ticket['ticket_id'] ?? 0) ?>"
                                    data-priority="<?= htmlspecialchars(strtolower(trim((string) ($ticket['priority'] ?? '')))) ?>"
                                    data-status="<?= htmlspecialchars(strtolower(trim((string) ($ticket['status'] ?? '')))) ?>">
                                    <td class="font-weight-bold"><?php echo htmlspecialchars((string) ($ticket['ticket_number'] ?? '')); ?></td>
                                    <td><?php echo htmlspecialchars((string) ($ticket['category'] ?? '')); ?></td>
                                    <td>
                                        <?php
                                        $priority = (string) ($ticket['priority'] ?? 'Low');
                                        $priorityClass = $priority === 'High' ? 'danger' : ($priority === 'Medium' ? 'warning' : 'success');
                                        ?>
                                        <span class="badge badge-<?php echo $priorityClass; ?>" data-ticket-priority>
                                            <?php echo htmlspecialchars($priority); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php
                                        $status = (string) ($ticket['status'] ?? 'Pending');
                                        $statusClass = $status === 'Pending' ? 'warning' : ($status === 'In Progress' ? 'info' : ($status === 'Resolved' ? 'success' : 'secondary'));
                                        ?>
                                        <span class="badge badge-<?php echo $statusClass; ?> status-badge" data-ticket-status>
                                            <?php echo htmlspecialchars($status); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars(date('M d, Y', strtotime((string) ($ticket['date_filed'] ?? '')))); ?></td>
                                    <td><?php echo htmlspecialchars((string) ($ticket['branchName'] ?? '')); ?></td>
                                    <td>
                                        <div class="action-btn-group">
                                        <a href="<?= htmlspecialchars($base) ?>/employee/tickets/view?id=<?php echo (int) ($ticket['ticket_id'] ?? 0); ?>" class="btn btn-sm btn-info" title="View ticket">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <?php
                                        $ticketId = (int) ($ticket['ticket_id'] ?? 0);
                                        $ticketStatus = (string) ($ticket['status'] ?? '');
                                        $ticketNumber = (string) ($ticket['ticket_number'] ?? '');
                                        require __DIR__ . '/../../partials/ticket/cancel_ticket_button.php';
                                        ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
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

        document.querySelectorAll('#employeeTicketsTable tbody tr').forEach(function (row) {
            const cells = row.querySelectorAll('td');
            if (cells.length < 7) return;

            const ticketNum = (cells[0].textContent || '').toLowerCase();
            const category = (cells[1].textContent || '').toLowerCase();
            const priority = (cells[2].textContent || '').toLowerCase();
            const status = (cells[3].textContent || '').toLowerCase();
            const branch = (cells[5].textContent || '').toLowerCase();

            const matchesStatus = !statusVal || status.includes(statusVal);
            const matchesPriority = !priorityVal || priority.includes(priorityVal);
            const matchesSearch = !searchVal || ticketNum.includes(searchVal) || category.includes(searchVal) || branch.includes(searchVal);

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
