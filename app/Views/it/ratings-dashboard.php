<?php
$base = rtrim(BASE_URL, '/');

$totalRatings = (int)($stats['total_ratings'] ?? 0);
$avgRating = (float)($stats['avg_rating'] ?? 0);
$maxRating = (int)($stats['max_rating'] ?? 0);
$minRating = (int)($stats['min_rating'] ?? 0);
$displayName = trim($loggedFirstname ?? '') ?: 'IT User';

$distributionMap = [];
foreach ($distribution ?? [] as $d) {
    $distributionMap[(int)$d['rating']] = (int)$d['count'];
}

function it_rating_stars_display(int $rating): string
{
    return str_repeat('★', $rating) . str_repeat('☆', max(0, 5 - $rating));
}

function it_perf_label(float $avg): string
{
    if ($avg >= 4.5) return 'Excellent';
    if ($avg >= 4.0) return 'Very Good';
    if ($avg >= 3.0) return 'Good';
    if ($avg > 0) return 'Needs Improvement';
    return 'No Ratings Yet';
}

$barColors = [1 => 'bar-1', 2 => 'bar-2', 3 => 'bar-3', 4 => 'bar-4', 5 => 'bar-5'];
$chartData = [];
for ($i = 1; $i <= 5; $i++) {
    $chartData[$i] = $distributionMap[$i] ?? 0;
}
$hasChartData = array_sum($chartData) > 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Storage Mart | My Ratings</title>

    <link href="<?= htmlspecialchars($base) ?>/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900" rel="stylesheet">
    <link rel="icon" href="<?= htmlspecialchars($base) ?>/assets/img/sm_favicon.png" type="image/x-icon">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/storagemart.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/it-ratings.css" rel="stylesheet">
    <?php require_once __DIR__ . '/../partials/it/theme_head.php'; ?>
    <link href="<?= htmlspecialchars($base) ?>/assets/vendor/datatables/datatables.min.css" rel="stylesheet">
</head>
<body id="page-top">

<div id="wrapper">
    <?php
    $activePage = 'ratings';
    require_once __DIR__ . '/../partials/it/sidebar_topbar.php';
    ?>

    <div class="container-fluid it-ratings-page">

        <!-- Hero -->
        <div class="page-hero">
            <div class="row align-items-center">
                <div class="col-lg-4">
                    <h1><i class="fas fa-star mr-2"></i>My Ratings</h1>
                    <p>Feedback from colleagues on tickets you've resolved, <?= htmlspecialchars($displayName) ?>.</p>
                    <span class="perf-badge">
                        <i class="fas fa-award"></i>
                        <?= htmlspecialchars(it_perf_label($avgRating)) ?>
                    </span>
                </div>
                <div class="col-lg-3 mt-3 mt-lg-0">
                    <div class="avg-rating-box">
                        <div class="avg-rating-value"><?= number_format($avgRating, 2) ?></div>
                        <div class="avg-rating-stars"><?= it_rating_stars_display((int) round($avgRating)) ?></div>
                        <div class="avg-rating-of">out of 5.00</div>
                    </div>
                </div>
                <div class="col-lg-5 mt-3 mt-lg-0">
                    <div class="row">
                        <div class="col-4">
                            <div class="hero-stat">
                                <div class="stat-value"><?= $totalRatings ?></div>
                                <div class="stat-label">Total</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="hero-stat">
                                <div class="stat-value"><?= $maxRating ?>★</div>
                                <div class="stat-label">Highest</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="hero-stat">
                                <div class="stat-value"><?= $minRating ?>★</div>
                                <div class="stat-label">Lowest</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts -->
        <div class="row mb-4">
            <div class="col-xl-6 mb-4 mb-xl-0">
                <div class="card dash-card shadow">
                    <div class="card-header">
                        <h6><i class="fas fa-chart-bar"></i>Rating Distribution</h6>
                    </div>
                    <div class="card-body">
                        <?php if ($hasChartData): ?>
                            <div class="chart-wrap">
                                <canvas id="ratingChart"></canvas>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-chart-bar"></i>
                                <p class="mb-0">No ratings to chart yet.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-xl-6">
                <div class="card dash-card shadow">
                    <div class="card-header">
                        <h6><i class="fas fa-sliders-h"></i>Rating Breakdown</h6>
                    </div>
                    <div class="card-body">
                        <?php if ($totalRatings > 0): ?>
                            <?php for ($i = 5; $i >= 1; $i--):
                                $count = $distributionMap[$i] ?? 0;
                                $percent = $totalRatings > 0 ? round(($count / $totalRatings) * 100) : 0;
                            ?>
                            <div class="rating-bar-row">
                                <div class="rating-bar-label">
                                    <span class="stars"><?= it_rating_stars_display($i) ?></span>
                                    <span><?= $count ?> (<?= $percent ?>%)</span>
                                </div>
                                <div class="rating-bar-track">
                                    <div class="rating-bar-fill <?= $barColors[$i] ?>" style="width: <?= $percent ?>%"></div>
                                </div>
                            </div>
                            <?php endfor; ?>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-star-half-alt"></i>
                                <p class="mb-0">No breakdown available yet.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ratings Table -->
        <div class="card dash-card shadow mb-4">
            <div class="card-header d-flex align-items-center justify-content-between flex-wrap">
                <h6><i class="fas fa-list"></i>Your Recent Ratings</h6>
                <?php if ($totalRatings > 0): ?>
                    <span class="badge badge-primary" style="border-radius:2rem;padding:0.4rem 0.75rem;">
                        <?= $totalRatings ?> rating<?= $totalRatings === 1 ? '' : 's' ?>
                    </span>
                <?php endif; ?>
            </div>
            <div class="card-body p-0">
                <?php if (empty($ratings)): ?>
                    <div class="empty-state">
                        <i class="fas fa-star"></i>
                        <p class="mb-0">No ratings yet. Keep up the great work!</p>
                    </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="ratingsTable" width="100%" cellspacing="0">
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
                            <?php foreach ($ratings as $rating):
                                $raterName = trim(($rating['firstname'] ?? '') . ' ' . ($rating['lastname'] ?? ''));
                                $starRating = (int)($rating['rating'] ?? 0);
                                $comment = (string)($rating['comment'] ?? '');
                                $dept = (string)($rating['department'] ?? '');
                            ?>
                            <tr>
                                <td>
                                    <span class="ticket-id"><?= htmlspecialchars($rating['ticket_number'] ?? '') ?></span>
                                </td>
                                <td><?= htmlspecialchars($rating['category'] ?? '—') ?></td>
                                <td><span class="rater-name"><?= htmlspecialchars($raterName !== '' ? $raterName : '—') ?></span></td>
                                <td>
                                    <?php if ($dept !== ''): ?>
                                        <span class="dept-pill"><?= htmlspecialchars($dept) ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="star-rating">
                                        <?= it_rating_stars_display($starRating) ?>
                                        <span class="rating-num"><?= $starRating ?>/5</span>
                                    </span>
                                </td>
                                <td>
                                    <span class="comment-text" title="<?= htmlspecialchars($comment) ?>">
                                        <?= htmlspecialchars($comment !== '' ? (strlen($comment) > 60 ? substr($comment, 0, 60) . '…' : $comment) : '—') ?>
                                    </span>
                                </td>
                                <td class="date-cell">
                                    <?= !empty($rating['created_at']) ? date('M d, Y', strtotime($rating['created_at'])) : '—' ?>
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
    <!-- End of Page Content -->

    </div>
    <!-- End of Main Content -->

</div>
<!-- End of Content Wrapper -->

</div>
<!-- End of Page Wrapper -->

<a class="scroll-to-top rounded" href="#page-top">
    <i class="fas fa-angle-up"></i>
</a>

<script src="<?= htmlspecialchars($base) ?>/assets/vendor/jquery/jquery.min.js"></script>
<script src="<?= htmlspecialchars($base) ?>/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="<?= htmlspecialchars($base) ?>/assets/vendor/jquery-easing/jquery.easing.min.js"></script>
<script src="<?= htmlspecialchars($base) ?>/assets/js/sb-admin-2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<?php if (!empty($ratings)): ?>
<script src="<?= htmlspecialchars($base) ?>/assets/vendor/datatables/jquery.dataTables.min.js"></script>
<script src="<?= htmlspecialchars($base) ?>/assets/vendor/datatables/datatables.min.js"></script>
<?php endif; ?>

<?php if ($hasChartData): ?>
<script>
(function () {
    var ctx = document.getElementById('ratingChart');
    if (!ctx) return;

    var dark = document.documentElement.classList.contains('it-dark');
    var textColor = dark ? '#a8aeb8' : '#858796';
    var gridColor = dark ? 'rgba(56, 62, 72, 0.6)' : 'rgb(234, 236, 244)';

    new Chart(ctx.getContext('2d'), {
        type: 'bar',
        data: {
            labels: ['1 Star', '2 Stars', '3 Stars', '4 Stars', '5 Stars'],
            datasets: [{
                label: 'Ratings',
                data: <?= json_encode(array_values($chartData)) ?>,
                backgroundColor: ['#e74c3c', '#f39c12', '#f6c23e', '#a9dfbf', '#27ae60'],
                borderRadius: 6,
                borderSkipped: false
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { color: textColor, font: { size: 11 } }
                },
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1,
                        color: textColor
                    },
                    grid: { color: gridColor }
                }
            }
        }
    });
})();
</script>
<?php endif; ?>

<?php if (!empty($ratings)): ?>
<script>
$(document).ready(function () {
    $('#ratingsTable').DataTable({
        pageLength: 10,
        order: [[6, 'desc']],
        columnDefs: [{ targets: [5], orderable: false }]
    });
});
</script>
<?php endif; ?>

<?php require __DIR__ . '/../partials/flash_modal.php'; ?>
</body>
</html>
