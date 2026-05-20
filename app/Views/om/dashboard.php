<?php
$base = rtrim(BASE_URL, '/');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Storage Mart | OM Dashboard</title>

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

            <!-- Quick Actions -->
            <div class="row mb-4">
                <div class="col-lg-4">
                    <div class="card shadow">
                        <div class="card-header py-3 bg-primary">
                            <h6 class="m-0 font-weight-bold text-white">Quick Actions</h6>
                        </div>
                        <div class="card-body">
                            <a href="<?= htmlspecialchars($base) ?>/om/new-assignment" class="btn btn-primary btn-block mb-2">
                                <i class="fas fa-plus"></i> Create Assignment
                            </a>
                            <a href="<?= htmlspecialchars($base) ?>/om/assignments" class="btn btn-success btn-block mb-2">
                                <i class="fas fa-list"></i> View Assignments
                            </a>
                            <a href="<?= htmlspecialchars($base) ?>/om/employees" class="btn btn-info btn-block mb-2">
                                <i class="fas fa-users"></i> Manage Employees
                            </a>
                            <a href="<?= htmlspecialchars($base) ?>/om/tickets/create" class="btn btn-warning btn-block mb-2">
                                <i class="fas fa-ticket-alt"></i> Create Ticket
                            </a>
                            <a href="<?= htmlspecialchars($base) ?>/om/tickets" class="btn btn-secondary btn-block">
                                <i class="fas fa-list"></i> View All Tickets
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Recent Assignments -->
                <?php if (!empty($assignments)): ?>
                <div class="col-lg-8">
                    <div class="card shadow">
                        <div class="card-header py-3 bg-primary">
                            <h6 class="m-0 font-weight-bold text-white">Recent Assignments</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
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
                <?php endif; ?>
            </div>

            <!-- Recent Tickets -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="card shadow">
                        <div class="card-header py-3 bg-info">
                            <h6 class="m-0 font-weight-bold text-white">Recent Tickets</h6>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($recentTickets)): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Ticket #</th>
                                            <th>Employee</th>
                                            <th>Department</th>
                                            <th>Priority</th>
                                            <th>Status</th>
                                            <th>Created</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recentTickets as $ticket): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($ticket['ticket_id'] ?? 'N/A') ?></td>
                                            <td><?= htmlspecialchars(($ticket['firstname'] ?? '') . ' ' . ($ticket['lastname'] ?? '')) ?></td>
                                            <td><?= htmlspecialchars($ticket['department'] ?? 'N/A') ?></td>
                                            <td>
                                                <span class="badge badge-<?= 
                                                    (($ticket['priority'] ?? 'Low') === 'High' ? 'danger' : (($ticket['priority'] ?? 'Low') === 'Medium' ? 'warning' : 'info'))
                                                ?>">
                                                    <?= htmlspecialchars($ticket['priority'] ?? 'Low') ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge badge-<?= 
                                                    (($ticket['status'] ?? 'Open') === 'Completed' ? 'success' : (($ticket['status'] ?? 'Open') === 'In Progress' ? 'warning' : 'primary'))
                                                ?>">
                                                    <?= htmlspecialchars($ticket['status'] ?? 'Open') ?>
                                                </span>
                                            </td>
                                            <td><?= date('M d, Y', strtotime($ticket['created_at'] ?? 'now')) ?></td>
                                            <td>
                                                <a href="<?= htmlspecialchars($base) ?>/om/tickets/view?id=<?= htmlspecialchars($ticket['ticket_id'] ?? '') ?>" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-eye"></i> View
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php else: ?>
                            <p class="text-muted">No tickets created yet. <a href="<?= htmlspecialchars($base) ?>/om/tickets/create">Create a ticket</a></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
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

<!-- Bootstrap core JavaScript-->
<script src="<?= htmlspecialchars($base) ?>/assets/vendor/jquery/jquery.min.js"></script>
<script src="<?= htmlspecialchars($base) ?>/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

<!-- Core plugin JavaScript-->
<script src="<?= htmlspecialchars($base) ?>/assets/vendor/jquery-easing/jquery.easing.min.js"></script>

<!-- Custom scripts for all pages-->
<script src="<?= htmlspecialchars($base) ?>/assets/js/sb-admin-2.min.js"></script>

</body>
</html>
