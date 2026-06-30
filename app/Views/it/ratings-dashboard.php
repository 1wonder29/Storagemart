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

function it_rating_stars_fa(float $rating): string
{
    $html = '';
    $rating = max(0, min(5, $rating));
    $full = (int) floor($rating);
    $fraction = $rating - $full;
    $hasHalf = $fraction >= 0.25 && $fraction < 0.75;
    if ($fraction >= 0.75) {
        $full++;
        $hasHalf = false;
    }

    for ($i = 1; $i <= 5; $i++) {
        if ($i <= $full) {
            $html .= '<i class="fas fa-star"></i>';
        } elseif ($hasHalf && $i === $full + 1) {
            $html .= '<i class="fas fa-star-half-alt"></i>';
        } else {
            $html .= '<i class="far fa-star"></i>';
        }
    }

    return $html;
}

function it_rating_stars_badge(int $rating): string
{
    $rating = max(0, min(5, $rating));
    return it_rating_stars_fa((float) $rating)
        . '<span class="rating-stars-count">' . $rating . '/5</span>';
}

function it_rating_stars_display(int $rating): string
{
    return it_rating_stars_badge($rating);
}

function it_rating_class(int $rating): string
{
    $rating = max(1, min(5, $rating));
    return 'rating-' . $rating;
}

function it_perf_label(float $avg): string
{
    if ($avg >= 4.5) return 'Excellent';
    if ($avg >= 4.0) return 'Very Good';
    if ($avg >= 3.0) return 'Good';
    if ($avg > 0) return 'Needs Improvement';
    return 'No Ratings Yet';
}

function it_perf_tone(float $avg): string
{
    if ($avg >= 4.5) return 'excellent';
    if ($avg >= 4.0) return 'very-good';
    if ($avg >= 3.0) return 'good';
    if ($avg > 0) return 'needs-work';
    return 'none';
}

$barColors = [1 => 'bar-1', 2 => 'bar-2', 3 => 'bar-3', 4 => 'bar-4', 5 => 'bar-5'];
$chartData = [];
for ($i = 5; $i >= 1; $i--) {
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
        <div class="page-hero ratings-hero-layout">
            <div class="ratings-hero-grid">
                <div class="ratings-hero-profile">
                    <div class="ratings-hero-icon" aria-hidden="true">
                        <i class="fas fa-star"></i>
                    </div>
                    <div class="ratings-hero-copy">
                        <span class="ratings-hero-eyebrow">Performance Feedback</span>
                        <h1>My Ratings</h1>
                        <p>Feedback from colleagues on tickets you've resolved, <strong><?= htmlspecialchars($displayName) ?></strong>.</p>
                        <span class="perf-badge tone-<?= htmlspecialchars(it_perf_tone($avgRating)) ?>">
                            <i class="fas fa-award"></i>
                            <?= htmlspecialchars(it_perf_label($avgRating)) ?>
                        </span>
                    </div>
                </div>

                <div class="ratings-hero-analytics">
                    <div class="ratings-hero-score-panel" aria-label="Average rating <?= number_format($avgRating, 2) ?> out of 5">
                        <div class="ratings-score-main">
                            <span class="ratings-score-value"><?= number_format($avgRating, 2) ?></span>
                            <span class="ratings-score-max">/ 5</span>
                        </div>
                        <div class="avg-rating-stars avg-rating-stars-fa"><?= it_rating_stars_fa($avgRating) ?></div>
                        <span class="ratings-score-caption">Average rating</span>
                    </div>

                    <div class="ratings-hero-stats-panel">
                        <div class="ratings-hero-metric">
                            <span class="ratings-hero-metric-label">Total ratings</span>
                            <span class="ratings-hero-metric-value"><?= $totalRatings ?></span>
                        </div>
                        <div class="ratings-hero-metric">
                            <span class="ratings-hero-metric-label">Highest</span>
                            <span class="ratings-hero-metric-value"><?= $maxRating ?>/5</span>
                        </div>
                        <div class="ratings-hero-metric">
                            <span class="ratings-hero-metric-label">Lowest</span>
                            <span class="ratings-hero-metric-value"><?= $minRating ?>/5</span>
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
                            <div class="rating-bar-row<?= $count === 0 ? ' is-empty' : '' ?>">
                                <div class="rating-bar-label">
                                    <span class="stars stars-fa"><?= it_rating_stars_fa((float) $i) ?></span>
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
                                <th>Technician</th>
                                <th>Rating</th>
                                <th>Date</th>
                                <th class="text-center action-col">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($ratings as $rating):
                                $raterName = trim(($rating['firstname'] ?? '') . ' ' . ($rating['lastname'] ?? ''));
                                $starRating = (int)($rating['rating'] ?? 0);
                                $comment = (string)($rating['comment'] ?? '');
                                $dept = (string)($rating['department'] ?? '');
                                $dateLabel = !empty($rating['created_at']) ? date('M d, Y g:i A', strtotime($rating['created_at'])) : '—';
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
                                    <span class="tech-name"><?= htmlspecialchars($displayName) ?></span>
                                </td>
                                <td>
                                    <span class="rating-stars <?= htmlspecialchars(it_rating_class($starRating)) ?>">
                                        <?= it_rating_stars_display($starRating) ?>
                                    </span>
                                </td>
                                <td class="date-cell">
                                    <?= htmlspecialchars($dateLabel) ?>
                                </td>
                                <td class="text-center action-col">
                                    <button type="button"
                                        class="btn-rating-details"
                                        title="View rating details"
                                        data-ticket="<?= htmlspecialchars($rating['ticket_number'] ?? '') ?>"
                                        data-rating="<?= $starRating ?>"
                                        data-rater="<?= htmlspecialchars($raterName !== '' ? $raterName : '—') ?>"
                                        data-technician="<?= htmlspecialchars($displayName) ?>"
                                        data-date="<?= htmlspecialchars($dateLabel) ?>"
                                        data-comment="<?= htmlspecialchars($comment, ENT_QUOTES, 'UTF-8') ?>">
                                        <span class="btn-rating-details-icon"><i class="fas fa-comment-dots"></i></span>
                                        <span class="btn-rating-details-label">View Details</span>
                                    </button>
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

<div class="modal fade it-rating-details-modal" id="ratingDetailsModal" tabindex="-1" role="dialog" aria-labelledby="ratingDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <p class="rating-details-eyebrow mb-1">Ticket Rating</p>
                    <h5 class="modal-title mb-0" id="ratingDetailsModalLabel">Rating Details</h5>
                </div>
                <button type="button" class="close rating-details-close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="rating-details-summary">
                    <div class="rating-details-summary-item">
                        <span class="summary-label">Ticket #</span>
                        <span class="summary-value ticket-id" id="ratingDetailTicket"></span>
                    </div>
                    <div class="rating-details-summary-item">
                        <span class="summary-label">Rating</span>
                        <span class="summary-value" id="ratingDetailStars"></span>
                    </div>
                    <div class="rating-details-summary-item">
                        <span class="summary-label">Rater</span>
                        <span class="summary-value" id="ratingDetailRater"></span>
                    </div>
                    <div class="rating-details-summary-item">
                        <span class="summary-label">Technician</span>
                        <span class="summary-value tech-name" id="ratingDetailTechnician"></span>
                    </div>
                    <div class="rating-details-summary-item rating-details-summary-item-wide">
                        <span class="summary-label">Submitted</span>
                        <span class="summary-value" id="ratingDetailDate"></span>
                    </div>
                </div>
                <div class="rating-details-comment">
                    <div class="rating-details-comment-head">
                        <i class="fas fa-quote-left"></i>
                        <span>Feedback Comment</span>
                    </div>
                    <p id="ratingDetailComment" class="mb-0"></p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-rating-modal-close" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i> Close
                </button>
            </div>
        </div>
    </div>
</div>

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
            labels: ['5 Stars', '4 Stars', '3 Stars', '2 Stars', '1 Star'],
            datasets: [{
                label: 'Ratings',
                data: <?= json_encode(array_values($chartData)) ?>,
                backgroundColor: ['#27ae60', '#58d68d', '#f6c23e', '#f39c12', '#e74a3b'],
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
    function ratingStars(rating) {
        rating = Math.max(0, Math.min(5, parseInt(rating, 10) || 0));
        var html = '';
        for (var i = 1; i <= 5; i++) {
            if (i <= rating) {
                html += '<i class="fas fa-star"></i>';
            } else {
                html += '<i class="far fa-star"></i>';
            }
        }
        return html + '<span class="rating-stars-count">' + rating + '/5</span>';
    }

    $(document).on('click', '.btn-rating-details', function() {
        var $btn = $(this);
        var rating = parseInt($btn.attr('data-rating'), 10) || 0;
        var comment = String($btn.attr('data-comment') || '').trim();
        var ticket = $btn.attr('data-ticket') || '—';

        $('#ratingDetailsModalLabel').text('Ticket ' + ticket);
        $('#ratingDetailTicket').text(ticket);
        $('#ratingDetailStars').html(
            '<span class="rating-stars ' + 'rating-' + rating + '">' + ratingStars(rating) + '</span>'
        );
        $('#ratingDetailRater').text($btn.attr('data-rater') || '—');
        $('#ratingDetailTechnician').text($btn.attr('data-technician') || '—');
        $('#ratingDetailDate').text($btn.attr('data-date') || '—');
        $('#ratingDetailComment')
            .text(comment !== '' ? comment : 'No comment was provided for this rating.')
            .toggleClass('is-empty', comment === '');

        $('#ratingDetailsModal').modal('show');
    });

    $('#ratingsTable').DataTable({
        pageLength: 10,
        order: [[6, 'desc']],
        columnDefs: [{ targets: [7], orderable: false, className: 'text-center action-col' }]
    });
});
</script>
<?php endif; ?>

<?php require __DIR__ . '/../partials/flash_modal.php'; ?>
</body>
</html>
