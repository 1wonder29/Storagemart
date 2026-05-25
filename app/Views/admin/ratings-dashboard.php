<?php
$base = rtrim(BASE_URL, '/');
?>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>Ratings Report - Storage Mart Admin</title>

    <link href="<?= htmlspecialchars($base) ?>/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/storagemart.css" rel="stylesheet">
    <link rel="icon" href="<?= htmlspecialchars($base) ?>/assets/img/favicon.ico" type="image/x-icon">
    <link href="<?= htmlspecialchars($base) ?>/assets/vendor/datatables/datatables.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
</head>

<body id="page-top">
    <div id="wrapper">
        <?php 
        $activePage = 'ratings';
        require_once __DIR__ . '/../partials/admin/sidebar_topbar.php';?>

        <div class="container-fluid">
            <!-- Page Heading -->
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">Ratings & Performance Reports</h1>
            </div>

            <!-- Tabs Navigation -->
            <ul class="nav nav-tabs mb-4" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" href="#overview" data-toggle="tab" role="tab">Overview</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#staff-performance" data-toggle="tab" role="tab">Staff Performance</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#all-ratings" data-toggle="tab" role="tab">All Ratings</a>
                </li>
            </ul>

            <!-- Tab Content -->
            <div class="tab-content">
                <!-- Overview Tab -->
                <div id="overview" class="tab-pane fade show active">
                    <!-- Statistics Cards -->
                    <div class="row mb-4">
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-primary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="text-primary text-uppercase mb-1">Total Ratings</div>
                                    <div class="h3 mb-0 font-weight-bold text-gray-800">
                                        <?= (int)($stats['total_ratings'] ?? 0) ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-success shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="text-success text-uppercase mb-1">Average Rating</div>
                                    <div class="h3 mb-0 font-weight-bold text-gray-800">
                                        <?= number_format((float)($stats['avg_rating'] ?? 0), 2) ?>/5.00
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-info shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="text-info text-uppercase mb-1">5-Star Ratings</div>
                                    <div class="h3 mb-0 font-weight-bold text-gray-800">
                                        <?= (int)($stats['count_5star'] ?? 0) ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-warning shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="text-warning text-uppercase mb-1">IT Staff Count</div>
                                    <div class="h3 mb-0 font-weight-bold text-gray-800">
                                        <?= count($itStaffPerformance) ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Charts Row -->
                    <div class="row mb-4">
                        <div class="col-xl-6">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Overall Rating Distribution</h6>
                                </div>
                                <div class="card-body">
                                    <canvas id="overallDistributionChart"></canvas>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-6">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Rating Breakdown</h6>
                                </div>
                                <div class="card-body">
                                    <div class="small text-muted">
                                        <?php for ($i = 5; $i >= 1; $i--):
                                            $count = $stats['count_' . $i . 'star'] ?? 0;
                                            $total = (int)($stats['total_ratings'] ?? 1);
                                            $percent = round(($count / $total) * 100);
                                        ?>
                                        <div class="mb-3">
                                            <div class="d-flex justify-content-between mb-1">
                                                <span><?= $i ?> ★</span>
                                                <span><?= $count ?> (<?= $percent ?>%)</span>
                                            </div>
                                            <div class="progress" style="height: 10px;">
                                                <div class="progress-bar" style="width: <?= $percent ?>%; background-color: <?php 
                                                    if ($i == 5) echo '#27ae60';
                                                    elseif ($i == 4) echo '#a9dfbf';
                                                    elseif ($i == 3) echo '#f1c40f';
                                                    elseif ($i == 2) echo '#f39c12';
                                                    else echo '#e74c3c';
                                                ?>"></div>
                                            </div>
                                        </div>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Staff Performance Tab -->
                <div id="staff-performance" class="tab-pane fade">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">IT Staff Performance Ranking</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="performanceTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>Rank</th>
                                            <th>Technician</th>
                                            <th>Total Ratings</th>
                                            <th>Average Rating</th>
                                            <th>5-Star Count</th>
                                            <th>Performance</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $rank = 1; foreach($itStaffPerformance as $staff): ?>
                                        <tr>
                                            <td><strong><?= $rank++ ?></strong></td>
                                            <td><?= htmlspecialchars($staff['firstname'] . ' ' . $staff['lastname']) ?></td>
                                            <td><?= (int)($staff['total_ratings'] ?? 0) ?></td>
                                            <td>
                                                <span class="badge badge-success">
                                                    <?= number_format((float)($staff['avg_rating'] ?? 0), 2) ?>/5.00
                                                </span>
                                            </td>
                                            <td><?= (int)($staff['count_5star'] ?? 0) ?></td>
                                            <td>
                                                <?php 
                                                    $avgRating = (float)($staff['avg_rating'] ?? 0);
                                                    if ($avgRating >= 4.5) {
                                                        echo '<span class="badge badge-success">Excellent</span>';
                                                    } elseif ($avgRating >= 4) {
                                                        echo '<span class="badge badge-info">Very Good</span>';
                                                    } elseif ($avgRating >= 3) {
                                                        echo '<span class="badge badge-warning">Good</span>';
                                                    } else {
                                                        echo '<span class="badge badge-danger">Needs Improvement</span>';
                                                    }
                                                ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                                <?php if (empty($itStaffPerformance)): ?>
                                <div class="alert alert-info">No IT staff or ratings found.</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- All Ratings Tab -->
                <div id="all-ratings" class="tab-pane fade">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex justify-content-between align-items-center">
                            <h6 class="m-0 font-weight-bold text-primary">All Ratings with Filters</h6>
                            <button class="btn btn-sm btn-secondary" type="button" data-toggle="collapse" data-target="#filterPanel">
                                <i class="fas fa-filter"></i> Filters
                            </button>
                        </div>

                        <!-- Filters -->
                        <div id="filterPanel" class="collapse card-body bg-light">
                            <form id="filterForm" class="form-inline gap-2">
                                <div class="form-group mr-3">
                                    <label for="filterStartDate" class="mr-2">Start Date:</label>
                                    <input type="date" class="form-control" id="filterStartDate" name="start_date">
                                </div>
                                <div class="form-group mr-3">
                                    <label for="filterEndDate" class="mr-2">End Date:</label>
                                    <input type="date" class="form-control" id="filterEndDate" name="end_date">
                                </div>
                                <div class="form-group mr-3">
                                    <label for="filterItId" class="mr-2">Technician:</label>
                                    <select class="form-control" id="filterItId" name="it_id">
                                        <option value="">All</option>
                                        <?php foreach($itStaffList as $staff): ?>
                                        <option value="<?= (int)$staff['employee_id'] ?>">
                                            <?= htmlspecialchars($staff['firstname'] . ' ' . $staff['lastname']) ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group mr-3">
                                    <label for="filterRating" class="mr-2">Rating:</label>
                                    <select class="form-control" id="filterRating" name="rating">
                                        <option value="">All</option>
                                        <option value="5">5 Stars</option>
                                        <option value="4">4 Stars</option>
                                        <option value="3">3 Stars</option>
                                        <option value="2">2 Stars</option>
                                        <option value="1">1 Star</option>
                                    </select>
                                </div>
                                <button type="button" class="btn btn-primary" id="applyFilters">Apply</button>
                                <button type="button" class="btn btn-secondary" id="clearFilters">Clear</button>
                            </form>
                        </div>

                        <!-- Ratings Table -->
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="allRatingsTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>Ticket #</th>
                                            <th>Category</th>
                                            <th>Rater</th>
                                            <th>Department</th>
                                            <th>Technician</th>
                                            <th>Rating</th>
                                            <th>Comment</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody id="ratingsTableBody">
                                        <?php foreach($ratings as $rating): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($rating['ticket_number']) ?></td>
                                            <td><?= htmlspecialchars($rating['category']) ?></td>
                                            <td><?= htmlspecialchars($rating['rater_firstname'] . ' ' . $rating['rater_lastname']) ?></td>
                                            <td><?= htmlspecialchars($rating['rater_department']) ?></td>
                                            <td><?= htmlspecialchars($rating['tech_firstname'] . ' ' . $rating['tech_lastname']) ?></td>
                                            <td>
                                                <span class="badge" style="background-color: <?php 
                                                    if ($rating['rating'] == 5) echo '#27ae60';
                                                    elseif ($rating['rating'] == 4) echo '#a9dfbf';
                                                    elseif ($rating['rating'] == 3) echo '#f1c40f';
                                                    elseif ($rating['rating'] == 2) echo '#f39c12';
                                                    else echo '#e74c3c';
                                                ?>; color: white;">
                                                    <?= str_repeat('★', (int)$rating['rating']) . str_repeat('☆', 5 - (int)$rating['rating']) ?>
                                                </span>
                                            </td>
                                            <td><?= htmlspecialchars(substr($rating['comment'], 0, 50)) . (strlen($rating['comment']) > 50 ? '...' : '') ?></td>
                                            <td><?= date('M d, Y H:i', strtotime($rating['created_at'])) ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                                <?php if (empty($ratings)): ?>
                                <div class="alert alert-info">No ratings found.</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/jquery/jquery.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/js/sb-admin-2.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/datatables/jquery.datatables.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/datatables/datatables.min.js"></script>

    <script>
    $(document).ready(function() {
        const base = "<?= htmlspecialchars($base) ?>";

        // Initialize DataTables
        $('#performanceTable').DataTable({
            pageLength: 10,
            order: [[3, 'desc']]
        });

        $('#allRatingsTable').DataTable({
            pageLength: 10,
            order: [[7, 'desc']]
        });

        // Create overall distribution chart
        const ctx = document.getElementById('overallDistributionChart').getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['5 Stars', '4 Stars', '3 Stars', '2 Stars', '1 Star'],
                datasets: [{
                    data: [
                        <?= (int)($stats['count_5star'] ?? 0) ?>,
                        <?= (int)($stats['count_4star'] ?? 0) ?>,
                        <?= (int)($stats['count_3star'] ?? 0) ?>,
                        <?= (int)($stats['count_2star'] ?? 0) ?>,
                        <?= (int)($stats['count_1star'] ?? 0) ?>
                    ],
                    backgroundColor: ['#27ae60', '#a9dfbf', '#f1c40f', '#f39c12', '#e74c3c']
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Filter functionality
        $('#applyFilters').click(function() {
            const filters = {
                start_date: $('#filterStartDate').val(),
                end_date: $('#filterEndDate').val(),
                it_id: $('#filterItId').val(),
                rating: $('#filterRating').val()
            };

            $.getJSON(base + '/admin/ratings/data', filters, function(data) {
                const tbody = $('#ratingsTableBody');
                tbody.empty();

                if (data.length === 0) {
                    tbody.append('<tr><td colspan="8" class="text-center">No ratings found with these filters.</td></tr>');
                    return;
                }

                data.forEach(function(row) {
                    const stars = '★'.repeat(row.rating) + '☆'.repeat(5 - row.rating);
                    const bgcolor = {
                        5: '#27ae60',
                        4: '#a9dfbf',
                        3: '#f1c40f',
                        2: '#f39c12',
                        1: '#e74c3c'
                    }[row.rating] || '#ccc';

                    tbody.append(
                        '<tr>' +
                        '<td>' + row.ticket_number + '</td>' +
                        '<td>' + row.category + '</td>' +
                        '<td>' + row.rater_firstname + ' ' + row.rater_lastname + '</td>' +
                        '<td>' + row.rater_department + '</td>' +
                        '<td>' + row.tech_firstname + ' ' + row.tech_lastname + '</td>' +
                        '<td><span class="badge" style="background-color: ' + bgcolor + '; color: white;">' + stars + '</span></td>' +
                        '<td>' + (row.comment.substring(0, 50) + (row.comment.length > 50 ? '...' : '')) + '</td>' +
                        '<td>' + new Date(row.created_at).toLocaleDateString() + '</td>' +
                        '</tr>'
                    );
                });
            }).fail(function() {
                alert('Failed to load ratings. Please try again.');
            });
        });

        $('#clearFilters').click(function() {
            $('#filterForm')[0].reset();
            location.reload();
        });
    });
    </script>

    <?php require __DIR__ . '/../partials/flash_modal.php'; ?>
</body>

</html>
