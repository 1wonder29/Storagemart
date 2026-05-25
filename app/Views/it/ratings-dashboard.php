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
    <title>Ratings Dashboard - Storage Mart</title>

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
        require_once __DIR__ . '/../partials/it/sidebar_topbar.php';?>

        <div class="container-fluid">
            <!-- Page Heading -->
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">Your Ratings Dashboard</h1>
            </div>

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
                            <div class="text-info text-uppercase mb-1">Highest Rating</div>
                            <div class="h3 mb-0 font-weight-bold text-gray-800">
                                <?= (int)($stats['max_rating'] ?? 0) ?>/5 ★
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-warning shadow h-100 py-2">
                        <div class="card-body">
                            <div class="text-warning text-uppercase mb-1">Lowest Rating</div>
                            <div class="h3 mb-0 font-weight-bold text-gray-800">
                                <?= (int)($stats['min_rating'] ?? 0) ?>/5 ★
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Rating Distribution Chart -->
            <div class="row mb-4">
                <div class="col-xl-6">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Rating Distribution</h6>
                        </div>
                        <div class="card-body">
                            <canvas id="ratingChart"></canvas>
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
                                <?php 
                                $distribution = array_column($distribution ?? [], null, 'rating');
                                for ($i = 5; $i >= 1; $i--):
                                    $count = $distribution[$i]['count'] ?? 0;
                                    $total = (int)($stats['total_ratings'] ?? 1);
                                    $percent = round(($count / $total) * 100);
                                ?>
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span><?= $i ?> ★</span>
                                        <span><?= $count ?> (<?= $percent ?>%)</span>
                                    </div>
                                    <div class="progress" style="height: 10px;">
                                        <div class="progress-bar bg-success" role="progressbar" style="width: <?= $percent ?>%"></div>
                                    </div>
                                </div>
                                <?php endfor; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Ratings Table -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Your Recent Ratings</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="ratingsTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Ticket #</th>
                                    <th>Category</th>
                                    <th>Rater</th>
                                    <th>Department</th>
                                    <th>Rating</th>
                                    <th>Comment</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($ratings as $rating): ?>
                                <tr>
                                    <td><?= htmlspecialchars($rating['ticket_number']) ?></td>
                                    <td><?= htmlspecialchars($rating['category']) ?></td>
                                    <td><?= htmlspecialchars($rating['firstname'] . ' ' . $rating['lastname']) ?></td>
                                    <td><?= htmlspecialchars($rating['department']) ?></td>
                                    <td>
                                        <span class="badge badge-success">
                                            <?= str_repeat('★', (int)$rating['rating']) . str_repeat('☆', 5 - (int)$rating['rating']) ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars(substr($rating['comment'], 0, 50)) . (strlen($rating['comment']) > 50 ? '...' : '') ?></td>
                                    <td><?= date('M d, Y', strtotime($rating['created_at'])) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php if (empty($ratings)): ?>
                        <div class="alert alert-info">No ratings yet. Keep up the great work!</div>
                        <?php endif; ?>
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
        // Initialize DataTable
        $('#ratingsTable').DataTable({
            pageLength: 10,
            order: [[6, 'desc']]
        });

        // Chart data
        const distributionData = <?php echo json_encode(array_fill_keys(range(1, 5), 0)); ?>;
        <?php foreach($distribution as $d): ?>
        distributionData[<?= (int)$d['rating'] ?>] = <?= (int)$d['count'] ?>;
        <?php endforeach; ?>

        // Create chart
        const ctx = document.getElementById('ratingChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['1 Star', '2 Stars', '3 Stars', '4 Stars', '5 Stars'],
                datasets: [{
                    label: 'Number of Ratings',
                    data: [
                        distributionData[1] || 0,
                        distributionData[2] || 0,
                        distributionData[3] || 0,
                        distributionData[4] || 0,
                        distributionData[5] || 0
                    ],
                    backgroundColor: [
                        '#e74c3c',
                        '#f39c12',
                        '#f1c40f',
                        '#a9dfbf',
                        '#27ae60'
                    ],
                    borderColor: [
                        '#c0392b',
                        '#d68910',
                        '#d4ac0d',
                        '#82e0aa',
                        '#229954'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    });
    </script>

    <?php require __DIR__ . '/../partials/flash_modal.php'; ?>
</body>

</html>
