<?php
$base = rtrim(BASE_URL, '/');
$routePrefix = $routePrefix ?? (($user_role ?? '') === 'HOM' ? 'hom' : 'om');
$dashboardTitle = ($user_role ?? '') === 'HOM' ? 'HOM Dashboard' : 'OM Dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Storage Mart | <?= htmlspecialchars($dashboardTitle) ?></title>

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
    $activePage = 'dashboard';
    require_once __DIR__ . '/../partials/om/sidebar_topbar.php';?>
        <!-- Page Content -->
        <div class="container-fluid">
            <!-- Page Heading -->
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
            </div>

            <!-- Messages -->
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($_SESSION['success_message']) ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <?php unset($_SESSION['success_message']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($_SESSION['error_message']) ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <?php unset($_SESSION['error_message']); ?>
            <?php endif; ?>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Assignments</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['total_assignments'] ?? 0; ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-success shadow h-100 py-2">
                        <div class="card-body">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Active Assignments</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['active_assignments'] ?? 0; ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-warning shadow h-100 py-2">
                        <div class="card-body">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Assigned Employees</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['assigned_employee_count'] ?? 0; ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-info shadow h-100 py-2">
                        <div class="card-body">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total Tickets</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $ticketStats['total'] ?? 0; ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Ticket Statistics Cards -->
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-warning shadow h-100 py-2">
                        <div class="card-body">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Open Tickets</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $ticketStats['open'] ?? 0; ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-info shadow h-100 py-2">
                        <div class="card-body">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">In Progress</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $ticketStats['in_progress'] ?? 0; ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-success shadow h-100 py-2">
                        <div class="card-body">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Completed</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $ticketStats['completed'] ?? 0; ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions & Recent Tickets -->
            <div class="row mb-4">
                <div class="col-xl-4 col-lg-5 mb-4 mb-lg-0">
                    <div class="card shadow h-100">
                        <div class="card-header py-3 bg-primary">
                            <h6 class="m-0 font-weight-bold text-white">Quick Actions</h6>
                        </div>
                        <div class="card-body">
                            <a href="<?= htmlspecialchars($base) ?>/<?= htmlspecialchars($routePrefix) ?>/new-assignment" class="btn btn-primary btn-block mb-2">
                                <i class="fas fa-plus"></i> Create Assignment
                            </a>
                            <a href="<?= htmlspecialchars($base) ?>/<?= htmlspecialchars($routePrefix) ?>/assignments" class="btn btn-success btn-block mb-2">
                                <i class="fas fa-list"></i> View Assignments
                            </a>
                            <a href="<?= htmlspecialchars($base) ?>/<?= htmlspecialchars($routePrefix) ?>/employees" class="btn btn-info btn-block mb-2">
                                <i class="fas fa-users"></i> Manage Employees
                            </a>
                            <a href="<?= htmlspecialchars($base) ?>/<?= htmlspecialchars($routePrefix) ?>/tickets/create" class="btn btn-warning btn-block mb-2">
                                <i class="fas fa-ticket-alt"></i> Create Ticket
                            </a>
                            <a href="<?= htmlspecialchars($base) ?>/<?= htmlspecialchars($routePrefix) ?>/tickets" class="btn btn-secondary btn-block">
                                <i class="fas fa-list"></i> View All Tickets
                            </a>
                        </div>
                    </div>
                </div>

                <div class="col-xl-8 col-lg-7">
                    <div class="card shadow h-100">
                        <div class="card-header py-3 bg-primary">
                            <h6 class="m-0 font-weight-bold text-white">Recent Tickets</h6>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($recentTickets)): ?>
                            <div class="table-responsive">
                                <table class="table table-sm table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>Ticket #</th>
                                            <th>Employee</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recentTickets as $ticket): ?>
                                        <?php
                                            $ticketNumber = (string) ($ticket['ticket_number'] ?? $ticket['ticket_id'] ?? 'N/A');
                                            $employeeName = (string) ($ticket['employee_name'] ?? trim(($ticket['firstname'] ?? '') . ' ' . ($ticket['lastname'] ?? '')));
                                            if ($employeeName === '') {
                                                $employeeName = '—';
                                            }
                                            $status = (string) ($ticket['status'] ?? 'Pending');
                                            $priority = (string) ($ticket['priority'] ?? 'Low');
                                            $branchName = (string) ($ticket['branchName'] ?? 'N/A');
                                            $dateFiled = !empty($ticket['date_filed'])
                                                ? date('M d, Y', strtotime((string) $ticket['date_filed']))
                                                : '—';
                                            $description = (string) ($ticket['concern_details'] ?? '');
                                        ?>
                                        <tr>
                                            <td><strong><?= htmlspecialchars($ticketNumber) ?></strong></td>
                                            <td><?= htmlspecialchars($employeeName) ?></td>
                                            <td>
                                                <button type="button"
                                                    class="btn btn-sm btn-primary viewTicketBtn"
                                                    data-ticket-number="<?= htmlspecialchars($ticketNumber, ENT_QUOTES) ?>"
                                                    data-employee="<?= htmlspecialchars($employeeName, ENT_QUOTES) ?>"
                                                    data-status="<?= htmlspecialchars($status, ENT_QUOTES) ?>"
                                                    data-priority="<?= htmlspecialchars($priority, ENT_QUOTES) ?>"
                                                    data-branch="<?= htmlspecialchars($branchName, ENT_QUOTES) ?>"
                                                    data-date-filed="<?= htmlspecialchars($dateFiled, ENT_QUOTES) ?>"
                                                    data-description="<?= htmlspecialchars($description, ENT_QUOTES) ?>">
                                                    <i class="fas fa-eye"></i> Details
                                                </button>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php else: ?>
                            <p class="text-muted text-center py-3 mb-0">
                                <i class="fas fa-inbox"></i> No tickets created yet.
                                <a href="<?= htmlspecialchars($base) ?>/<?= htmlspecialchars($routePrefix) ?>/tickets/create">Create a ticket</a>
                            </p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Assignments -->
            <?php if (!empty($assignments)): ?>
            <div class="row mb-4">
                <div class="col-lg-12">
                    <div class="card shadow">
                        <div class="card-header py-3 bg-primary">
                            <h6 class="m-0 font-weight-bold text-white">Recent Assignments</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>Employee</th>
                                            <th>AOM</th>
                                            <th>Date</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach (array_slice($assignments, 0, 5) as $assignment): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($assignment['employee_firstname'] . ' ' . $assignment['employee_lastname']) ?></td>
                                            <td><?= htmlspecialchars($assignment['aom_firstname'] . ' ' . $assignment['aom_lastname']) ?></td>
                                            <td><?= date('M d, Y', strtotime($assignment['assignment_date'])) ?></td>
                                            <td><span class="badge badge-<?= $assignment['is_active'] ? 'success' : 'secondary' ?>"><?= $assignment['is_active'] ? 'Active' : 'Inactive' ?></span></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <!-- /.container-fluid -->

    </div>
    <!-- End of Main Content -->

</div>
<!-- End of Content Wrapper -->

</div>
<!-- End of Page Wrapper -->

<!-- Scroll to Top Button-->
<a class="scroll-to-top rounded" href="#page-top">
    <i class="fas fa-angle-up"></i>
</a>

<!-- Ticket Details Modal -->
<div class="modal fade" id="ticketDetailModal" tabindex="-1" aria-labelledby="ticketDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="ticketDetailModalLabel">Ticket Details</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="small text-muted text-uppercase mb-1">Ticket Number</label>
                        <p class="font-weight-bold mb-0" id="modalTicketNumber">—</p>
                    </div>
                    <div class="col-md-6">
                        <label class="small text-muted text-uppercase mb-1">Employee</label>
                        <p class="font-weight-bold mb-0" id="modalEmployee">—</p>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="small text-muted text-uppercase mb-1">Status</label>
                        <p class="mb-0"><span class="badge" id="modalStatusBadge">—</span></p>
                    </div>
                    <div class="col-md-4">
                        <label class="small text-muted text-uppercase mb-1">Priority</label>
                        <p class="mb-0"><span class="badge" id="modalPriorityBadge">—</span></p>
                    </div>
                    <div class="col-md-4">
                        <label class="small text-muted text-uppercase mb-1">Filed Date</label>
                        <p class="mb-0" id="modalDateFiled">—</p>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-12">
                        <label class="small text-muted text-uppercase mb-1">Branch</label>
                        <p class="mb-0" id="modalBranch">—</p>
                    </div>
                </div>
                <hr>
                <label class="small text-muted text-uppercase mb-1">Description</label>
                <p class="mb-0" id="modalDescription">—</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap core JavaScript-->
<script src="<?= htmlspecialchars($base) ?>/assets/vendor/jquery/jquery.min.js"></script>
<script src="<?= htmlspecialchars($base) ?>/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

<!-- Core plugin JavaScript-->
<script src="<?= htmlspecialchars($base) ?>/assets/vendor/jquery-easing/jquery.easing.min.js"></script>

<!-- Custom scripts for all pages-->
<script src="<?= htmlspecialchars($base) ?>/assets/js/sb-admin-2.min.js"></script>

<script>
(function () {
    function statusBadgeClass(status) {
        if (status === 'Pending') return 'badge-warning';
        if (status === 'In Progress') return 'badge-info';
        if (status === 'Resolved' || status === 'Completed') return 'badge-success';
        if (status === 'Closed') return 'badge-secondary';
        return 'badge-primary';
    }

    function priorityBadgeClass(priority) {
        if (priority === 'High') return 'badge-danger';
        if (priority === 'Medium') return 'badge-warning';
        return 'badge-success';
    }

    $(document).on('click', '.viewTicketBtn', function () {
        const $btn = $(this);

        $('#modalTicketNumber').text($btn.data('ticketNumber') || '—');
        $('#modalEmployee').text($btn.data('employee') || '—');
        $('#modalBranch').text($btn.data('branch') || '—');
        $('#modalDateFiled').text($btn.data('dateFiled') || '—');
        $('#modalDescription').text($btn.data('description') || 'No description provided.');

        const status = $btn.data('status') || '—';
        const priority = $btn.data('priority') || '—';

        $('#modalStatusBadge')
            .removeClass()
            .addClass('badge ' + statusBadgeClass(status))
            .text(status);

        $('#modalPriorityBadge')
            .removeClass()
            .addClass('badge ' + priorityBadgeClass(priority))
            .text(priority);

        $('#ticketDetailModal').modal('show');
    });
})();
</script>

</body>
</html>
