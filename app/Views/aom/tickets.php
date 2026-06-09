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
    <title>Storage Mart | AOM Tickets</title>

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
    $activePage = 'tickets';
    require_once __DIR__ . '/../partials/aom/sidebar_topbar.php';?>
        <!-- Page Content -->
        <div class="container-fluid">
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">Tickets</h1>
                <a href="<?= htmlspecialchars($base) ?>/aom/tickets/create" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                    <i class="fas fa-plus fa-sm text-white-50"></i> Create New Ticket
                </a>
            </div>

            <!-- Ticket Statistics Cards -->
            <div class="row">
                <?php foreach ($ticketStats as $status => $count): ?>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-<?php echo $status === 'Pending' ? 'warning' : ($status === 'In Progress' ? 'info' : ($status === 'Resolved' ? 'success' : 'secondary')); ?> shadow h-100 py-2">
                            <div class="card-body">
                                <div class="text-xs font-weight-bold text-<?php echo $status === 'Pending' ? 'warning' : ($status === 'In Progress' ? 'info' : ($status === 'Resolved' ? 'success' : 'secondary')); ?> text-uppercase mb-1">
                                    <?php echo htmlspecialchars($status); ?>
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <?php echo $count; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Filters Card -->
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
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-xs font-weight-bold text-gray-600 text-uppercase mb-2">Branch</label>
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
                        <div class="col-md-3">
                            <label class="form-label text-xs font-weight-bold text-gray-600 text-uppercase mb-2">Priority</label>
                            <select id="priorityFilter" class="form-control form-control-sm">
                                <option value="">All Priorities</option>
                                <option value="Low">Low</option>
                                <option value="Medium">Medium</option>
                                <option value="High">High</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tickets Table Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">All Tickets</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover" id="ticketsTable">
                            <thead class="table-light">
                                <tr>
                                    <th>Ticket #</th>
                                    <th>Employee</th>
                                    <th>Branch</th>
                                    <th>Category</th>
                                    <th>Priority</th>
                                    <th>Status</th>
                                    <th>Date Filed</th>
                                    <th>Actions</th>
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
                                        ?>
                                        <tr data-filter-branch="<?php echo htmlspecialchars($fb, ENT_QUOTES, 'UTF-8'); ?>"
                                            data-filter-status="<?php echo htmlspecialchars($fs, ENT_QUOTES, 'UTF-8'); ?>"
                                            data-filter-priority="<?php echo htmlspecialchars($fp, ENT_QUOTES, 'UTF-8'); ?>">
                                            <td>
                                                <strong><?php echo htmlspecialchars($ticket['ticket_number']); ?></strong>
                                            </td>
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
                                                <?php 
                                                $priorityClass = $ticket['priority'] === 'High' ? 'danger' : 
                                                                ($ticket['priority'] === 'Medium' ? 'warning' : 'info');
                                                ?>
                                                <span class="badge badge-<?php echo $priorityClass; ?>">
                                                    <?php echo htmlspecialchars($ticket['priority']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php 
                                                $statusClass = $ticket['status'] === 'Pending' ? 'warning' : 
                                                              ($ticket['status'] === 'In Progress' ? 'info' : 
                                                               ($ticket['status'] === 'Resolved' ? 'success' : 'secondary'));
                                                ?>
                                                <span class="badge badge-<?php echo $statusClass; ?>">
                                                    <?php echo htmlspecialchars($ticket['status']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('M d, Y', strtotime($ticket['date_filed'])); ?></td>
                                            <td>
                                                <a href="<?= htmlspecialchars($base) ?>/aom/tickets/view?id=<?php echo $ticket['ticket_id']; ?>" 
                                                   class="btn btn-sm btn-primary">
                                                    <i class="fas fa-eye"></i> View
                                                </a>
                                                <?php
                                                $ticketId = (int) ($ticket['ticket_id'] ?? 0);
                                                $ticketStatus = (string) ($ticket['status'] ?? '');
                                                $ticketNumber = (string) ($ticket['ticket_number'] ?? '');
                                                require __DIR__ . '/../partials/ticket/cancel_ticket_button.php';
                                                ?>
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
            // Client-side filter using row data-* (avoids badge/whitespace mismatches vs td:text())
            function applyFilters() {
                const statusFilter = ($('#statusFilter').val() || '').trim().toLowerCase();
                const branchFilter = ($('#branchFilter').val() || '').trim().toLowerCase();
                const priorityFilter = ($('#priorityFilter').val() || '').trim().toLowerCase();
                const anyFilter = !!(statusFilter || branchFilter || priorityFilter);

                $('#ticketsTable tbody tr').each(function() {
                    const $row = $(this);
                    // "No tickets" placeholder row
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
        });
    </script>
<?php require __DIR__ . '/../partials/ticket/cancel_ticket_modal.php'; ?>
</body>
</html>
