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
    <title>Storage Mart | AOM Dashboard</title>

    <!-- Custom fonts for this template -->
    <link href="<?= htmlspecialchars($base) ?>/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="icon" href="<?= htmlspecialchars($base) ?>/assets/img/sm_favicon.png" type="image/x-icon">

    <!-- Custom styles for this template -->
    <link href="<?= htmlspecialchars($base) ?>/assets/css/storagemart.css" rel="stylesheet">
</head>

<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper">
    <?php 
    $activePage = 'dashboard';
    require_once __DIR__ . '/../partials/aom/sidebar_topbar.php';?>
        <!-- Page Content -->
        <div class="container-fluid">
            <!-- Page Heading -->
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Assigned Branches</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['total_branches'] ?? 0; ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-success shadow h-100 py-2">
                        <div class="card-body">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Employees</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['total_employees'] ?? 0; ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-warning shadow h-100 py-2">
                        <div class="card-body">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Pending Tickets</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['pending_tickets'] ?? 0; ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-info shadow h-100 py-2">
                        <div class="card-body">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Resolved (This Month)</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['resolved_this_month'] ?? 0; ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions and Branches -->
            <div class="row mb-4">
                <div class="col-xl-4 col-lg-5">
                    <div class="card shadow">
                        <div class="card-header py-3 bg-primary">
                            <h6 class="m-0 font-weight-bold text-white">Quick Actions</h6>
                        </div>
                        <div class="card-body">
                            <a href="<?= htmlspecialchars($base) ?>/aom/tickets/create" class="btn btn-primary btn-block mb-2">
                                <i class="fas fa-plus"></i> Create Ticket
                            </a>
                            <a href="<?= htmlspecialchars($base) ?>/aom/employees" class="btn btn-success btn-block mb-2">
                                <i class="fas fa-users"></i> View Employees
                            </a>
                            <a href="<?= htmlspecialchars($base) ?>/aom/tickets" class="btn btn-info btn-block">
                                <i class="fas fa-list"></i> View All Tickets
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-xl-8 col-lg-7">
                    <div class="card shadow">
                        <div class="card-header py-3 bg-primary">
                            <h6 class="m-0 font-weight-bold text-white">Assigned Branches</h6>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($branches)): ?>
                                <div class="list-group">
                                    <?php foreach ($branches as $branch): ?>
                                        <a href="<?= htmlspecialchars($base) ?>/aom/branches/detail?id=<?php echo $branch['branch_id']; ?>" class="list-group-item list-group-item-action">
                                            <div class="d-flex w-100 justify-content-between align-items-center">
                                                <div>
                                                    <h6 class="mb-1"><i class="fas fa-building"></i> <?php echo htmlspecialchars($branch['branchName']); ?></h6>
                                                    <small class="text-muted"><?php echo htmlspecialchars($branch['branchAddress'] ?? ''); ?></small>
                                                </div>
                                                <span class="badge badge-primary badge-pill"><?php echo $branch['employee_count'] ?? 0; ?> Employees</span>
                                            </div>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <p class="text-muted text-center py-3"><i class="fas fa-inbox"></i> No branches assigned yet.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Tickets and Statistics -->
            <div class="row">
                <div class="col-xl-8 col-lg-7 mb-4">
                    <div class="card shadow">
                        <div class="card-header py-3 bg-primary">
                            <h6 class="m-0 font-weight-bold text-white">Recent Tickets</h6>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($tickets)): ?>
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover">
                                        <thead>
                                            <tr>
                                                <th>Ticket #</th>
                                                <th>Employee</th>
                                                <th>Status</th>
                                                <th>Date</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach (array_slice($tickets, 0, 5) as $ticket): ?>
                                                <tr>
                                                    <td><strong><?php echo htmlspecialchars($ticket['ticket_number'] ?? $ticket['ticket_id']); ?></strong></td>
                                                    <td><?php
                                                        $first = $ticket['firstname'] ?? ($ticket['employee_firstname'] ?? '');
                                                        $last  = $ticket['lastname'] ?? ($ticket['employee_lastname'] ?? '');
                                                        $empName = trim($first . ' ' . $last);
                                                        echo htmlspecialchars($empName !== '' ? $empName : '—');
                                                    ?></td>
                                                    <td><?php $statusClass = $ticket['status'] === 'Pending' ? 'warning' : ($ticket['status'] === 'In Progress' ? 'info' : ($ticket['status'] === 'Resolved' ? 'success' : 'secondary')); ?><span class="badge badge-<?php echo $statusClass; ?>"><?php echo htmlspecialchars($ticket['status']); ?></span></td>
                                                    <td><?php echo !empty($ticket['date_filed']) ? date('M d, Y', strtotime($ticket['date_filed'])) : '—'; ?></td>
                                                    <td><a href="<?= htmlspecialchars($base) ?>/aom/tickets/view?id=<?php echo $ticket['ticket_id']; ?>" class="btn btn-sm btn-primary">View</a></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <p class="text-muted text-center py-3"><i class="fas fa-inbox"></i> No tickets found.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 col-lg-5 mb-4">
                    <div class="card shadow">
                        <div class="card-header py-3 bg-primary">
                            <h6 class="m-0 font-weight-bold text-white">Ticket Statistics</h6>
                        </div>
                        <div class="card-body text-center">
                            <?php if (!empty($ticketStats)): ?>
                                <div style="height: 250px;">
                                    <canvas id="ticketStatsChart"></canvas>
                                </div>
                            <?php else: ?>
                                <p class="text-muted">No ticket data available.</p>
                            <?php endif; ?>
                        </div>
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
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/js/sb-admin-2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        // Initialize Ticket Statistics Chart
        <?php if (!empty($ticketStats)): ?>
        var ctx = document.getElementById('ticketStatsChart').getContext('2d');
        var labels = [];
        var data = [];
        <?php foreach ($ticketStats as $status => $count): ?>
        labels.push('<?php echo htmlspecialchars($status); ?>');
        data.push(<?php echo $count; ?>);
        <?php endforeach; ?>

        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: ['#4e73df', '#1cc88a', '#f6c23e', '#e74c3c'],
                    borderColor: '#fff',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: {
                    position: 'bottom'
                }
            }
        });
        <?php endif; ?>
    </script>

    </div>
    <!-- End of Page Wrapper -->
</body>
</html>
