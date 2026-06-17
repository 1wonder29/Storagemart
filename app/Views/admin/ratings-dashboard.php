<?php
$base = rtrim(BASE_URL, '/');

if (!function_exists('admin_perf_badge')) {
    function admin_perf_badge(float $avg): array
    {
        if ($avg >= 4.5) return ['perf-excellent', 'Excellent'];
        if ($avg >= 4.0) return ['perf-very-good', 'Very Good'];
        if ($avg >= 3.0) return ['perf-good', 'Good'];
        return ['perf-needs-improvement', 'Needs Improvement'];
    }
}

if (!function_exists('admin_rating_stars')) {
    function admin_rating_stars(int $rating): string
    {
        return str_repeat('★', $rating) . str_repeat('☆', 5 - $rating);
    }
}

$totalRatings = (int) ($stats['total_ratings'] ?? 0);
$staffCount = count($itStaffPerformance);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Ratings Report - Storage Mart Admin</title>
    <link href="<?= htmlspecialchars($base) ?>/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/storagemart.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/admin-ratings.css" rel="stylesheet">
    <link rel="icon" href="<?= htmlspecialchars($base) ?>/assets/img/favicon.ico" type="image/x-icon">
    <link href="<?= htmlspecialchars($base) ?>/assets/vendor/datatables/datatables.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
</head>

<body id="page-top">
    <div id="wrapper">
        <?php
        $activePage = 'ratings';
        require_once __DIR__ . '/../partials/admin/sidebar_topbar.php';
        ?>

        <div class="container-fluid admin-ratings-page">

            <div class="page-hero">
                <h1><i class="fas fa-star mr-2"></i>Ratings & Performance Reports</h1>
                <p>Track IT staff satisfaction scores, rating trends, and individual performance across resolved tickets.</p>
            </div>

            <ul class="nav ratings-tabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" href="#overview" data-toggle="tab" role="tab">
                        <i class="fas fa-chart-pie mr-1"></i> Overview
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#staff-performance" data-toggle="tab" role="tab">
                        <i class="fas fa-users-cog mr-1"></i> Staff Performance
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#all-ratings" data-toggle="tab" role="tab">
                        <i class="fas fa-list mr-1"></i> All Ratings
                    </a>
                </li>
            </ul>

            <div class="tab-content">
                <div id="overview" class="tab-pane fade show active">
                    <div class="row mb-4">
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="stat-card stat-card-total">
                                <div class="stat-card-icon"><i class="fas fa-star"></i></div>
                                <div>
                                    <span class="stat-card-label">Total Ratings</span>
                                    <span class="stat-card-value"><?= $totalRatings ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="stat-card stat-card-avg">
                                <div class="stat-card-icon"><i class="fas fa-chart-line"></i></div>
                                <div>
                                    <span class="stat-card-label">Average Rating</span>
                                    <span class="stat-card-value"><?= number_format((float) ($stats['avg_rating'] ?? 0), 2) ?>/5</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="stat-card stat-card-five">
                                <div class="stat-card-icon"><i class="fas fa-award"></i></div>
                                <div>
                                    <span class="stat-card-label">5-Star Ratings</span>
                                    <span class="stat-card-value"><?= (int) ($stats['count_5star'] ?? 0) ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="stat-card stat-card-staff">
                                <div class="stat-card-icon"><i class="fas fa-user-cog"></i></div>
                                <div>
                                    <span class="stat-card-label">IT Staff Count</span>
                                    <span class="stat-card-value"><?= $staffCount ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-xl-6 mb-4">
                            <div class="card dash-card shadow">
                                <div class="card-header d-flex align-items-center">
                                    <span class="header-icon"><i class="fas fa-chart-pie"></i></span>
                                    <h6>Overall Rating Distribution</h6>
                                </div>
                                <div class="card-body">
                                    <div class="chart-wrap">
                                        <canvas id="overallDistributionChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-6 mb-4">
                            <div class="card dash-card shadow">
                                <div class="card-header d-flex align-items-center">
                                    <span class="header-icon"><i class="fas fa-sliders-h"></i></span>
                                    <h6>Rating Breakdown</h6>
                                </div>
                                <div class="card-body">
                                    <?php for ($i = 5; $i >= 1; $i--):
                                        $count = (int) ($stats['count_' . $i . 'star'] ?? 0);
                                        $total = max($totalRatings, 1);
                                        $percent = round(($count / $total) * 100);
                                    ?>
                                    <div class="rating-breakdown-row">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="rating-breakdown-label">
                                                <span class="star-num"><?= $i ?></span> <i class="fas fa-star text-warning" style="font-size:0.7rem;"></i>
                                            </span>
                                            <span class="rating-breakdown-meta"><?= $count ?> (<?= $percent ?>%)</span>
                                        </div>
                                        <div class="rating-breakdown-track">
                                            <div class="rating-breakdown-fill rating-fill-<?= $i ?>" style="width: <?= $percent ?>%;"></div>
                                        </div>
                                    </div>
                                    <?php endfor; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="staff-performance" class="tab-pane fade">
                    <div class="card report-list-card shadow mb-4">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <h6><i class="fas fa-trophy mr-1 text-warning"></i> IT Staff Performance Ranking</h6>
                            <span class="badge badge-light"><?= $staffCount ?> technician<?= $staffCount === 1 ? '' : 's' ?></span>
                        </div>
                        <div class="card-body p-0">
                            <?php if (empty($itStaffPerformance)): ?>
                                <div class="empty-state">
                                    <i class="fas fa-users d-block"></i>
                                    No IT staff or ratings found.
                                </div>
                            <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0" id="performanceTable" width="100%" cellspacing="0">
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
                                        <?php $rank = 1; foreach ($itStaffPerformance as $staff):
                                            $avgRating = (float) ($staff['avg_rating'] ?? 0);
                                            [$perfClass, $perfLabel] = admin_perf_badge($avgRating);
                                        ?>
                                        <tr>
                                            <td>
                                                <span class="rank-badge<?= $rank <= 3 ? ' rank-' . $rank : '' ?>"><?= $rank ?></span>
                                            </td>
                                            <td><span class="tech-name"><?= htmlspecialchars($staff['firstname'] . ' ' . $staff['lastname']) ?></span></td>
                                            <td><?= (int) ($staff['total_ratings'] ?? 0) ?></td>
                                            <td>
                                                <span class="avg-rating-pill">
                                                    <i class="fas fa-star"></i> <?= number_format($avgRating, 2) ?>/5
                                                </span>
                                            </td>
                                            <td><?= (int) ($staff['count_5star'] ?? 0) ?></td>
                                            <td><span class="perf-badge <?= $perfClass ?>"><?= $perfLabel ?></span></td>
                                        </tr>
                                        <?php $rank++; endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div id="all-ratings" class="tab-pane fade">
                    <div class="card report-list-card shadow mb-4">
                        <div class="card-header d-flex align-items-center justify-content-between flex-wrap">
                            <h6><i class="fas fa-filter mr-1 text-primary"></i> All Ratings</h6>
                            <button class="btn btn-sm btn-outline-primary btn-filter-toggle" type="button" data-toggle="collapse" data-target="#filterPanel">
                                <i class="fas fa-sliders-h mr-1"></i> Filters
                            </button>
                        </div>

                        <div id="filterPanel" class="collapse filter-toolbar">
                            <form id="filterForm">
                                <div class="row align-items-end">
                                    <div class="col-md-3 col-sm-6 mb-3 mb-md-0">
                                        <label for="filterStartDate">Start Date</label>
                                        <input type="date" class="form-control" id="filterStartDate" name="start_date">
                                    </div>
                                    <div class="col-md-3 col-sm-6 mb-3 mb-md-0">
                                        <label for="filterEndDate">End Date</label>
                                        <input type="date" class="form-control" id="filterEndDate" name="end_date">
                                    </div>
                                    <div class="col-md-2 col-sm-6 mb-3 mb-md-0">
                                        <label for="filterItId">Technician</label>
                                        <select class="form-control" id="filterItId" name="it_id">
                                            <option value="">All</option>
                                            <?php foreach ($itStaffList as $staff): ?>
                                            <option value="<?= (int) $staff['employee_id'] ?>">
                                                <?= htmlspecialchars($staff['firstname'] . ' ' . $staff['lastname']) ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-2 col-sm-6 mb-3 mb-md-0">
                                        <label for="filterRating">Rating</label>
                                        <select class="form-control" id="filterRating" name="rating">
                                            <option value="">All</option>
                                            <option value="5">5 Stars</option>
                                            <option value="4">4 Stars</option>
                                            <option value="3">3 Stars</option>
                                            <option value="2">2 Stars</option>
                                            <option value="1">1 Star</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2 col-sm-12 text-md-right">
                                        <button type="button" class="btn btn-primary btn-apply mr-2 mb-2 mb-md-0" id="applyFilters">
                                            <i class="fas fa-search mr-1"></i> Apply
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary btn-clear mb-2 mb-md-0" id="clearFilters">Clear</button>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <div class="card-body p-0">
                            <?php if (empty($ratings)): ?>
                                <div class="empty-state">
                                    <i class="fas fa-star d-block"></i>
                                    No ratings found.
                                </div>
                            <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0" id="allRatingsTable" width="100%" cellspacing="0">
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
                                    <tbody id="ratingsTableBody">
                                        <?php foreach ($ratings as $rating):
                                            $ratingVal = (int) $rating['rating'];
                                            $comment = (string) ($rating['comment'] ?? '');
                                        ?>
                                        <tr>
                                            <td><span class="ticket-id"><?= htmlspecialchars($rating['ticket_number']) ?></span></td>
                                            <td><span class="category-pill"><?= htmlspecialchars($rating['category']) ?></span></td>
                                            <td><?= htmlspecialchars($rating['rater_firstname'] . ' ' . $rating['rater_lastname']) ?></td>
                                            <td><?= htmlspecialchars($rating['rater_department']) ?></td>
                                            <td><span class="tech-name"><?= htmlspecialchars($rating['tech_firstname'] . ' ' . $rating['tech_lastname']) ?></span></td>
                                            <td>
                                                <span class="rating-stars rating-<?= $ratingVal ?>">
                                                    <?= admin_rating_stars($ratingVal) ?>
                                                </span>
                                            </td>
                                            <td class="date-cell"><?= date('M d, Y g:i A', strtotime($rating['created_at'])) ?></td>
                                            <td class="text-center action-col">
                                                <button type="button"
                                                    class="btn-rating-details"
                                                    title="View rating details"
                                                    data-ticket="<?= htmlspecialchars($rating['ticket_number']) ?>"
                                                    data-rating="<?= $ratingVal ?>"
                                                    data-rater="<?= htmlspecialchars($rating['rater_firstname'] . ' ' . $rating['rater_lastname']) ?>"
                                                    data-technician="<?= htmlspecialchars($rating['tech_firstname'] . ' ' . $rating['tech_lastname']) ?>"
                                                    data-date="<?= htmlspecialchars(date('M d, Y g:i A', strtotime($rating['created_at']))) ?>"
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
            </div>

        </div>

            </div>
        </div>
    </div>

    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <div class="modal fade rating-details-modal" id="ratingDetailsModal" tabindex="-1" role="dialog" aria-labelledby="ratingDetailsModalLabel" aria-hidden="true">
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
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/datatables/datatables.min.js"></script>

    <script>
    $(document).ready(function() {
        const base = "<?= htmlspecialchars($base) ?>";

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text || '';
            return div.innerHTML;
        }

        function ratingStars(rating) {
            return '★'.repeat(rating) + '☆'.repeat(5 - rating);
        }

        function escapeAttr(text) {
            return String(text || '')
                .replace(/&/g, '&amp;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;');
        }

        function buildDetailsButton(row, dateLabel) {
            const rater = (row.rater_firstname || '') + ' ' + (row.rater_lastname || '');
            const technician = (row.tech_firstname || '') + ' ' + (row.tech_lastname || '');
            const comment = row.comment || '';

            return '<button type="button" class="btn-rating-details" title="View rating details"' +
                ' data-ticket="' + escapeAttr(row.ticket_number) + '"' +
                ' data-rating="' + escapeAttr(row.rating) + '"' +
                ' data-rater="' + escapeAttr(rater.trim()) + '"' +
                ' data-technician="' + escapeAttr(technician.trim()) + '"' +
                ' data-date="' + escapeAttr(dateLabel) + '"' +
                ' data-comment="' + escapeAttr(comment) + '">' +
                '<span class="btn-rating-details-icon"><i class="fas fa-comment-dots"></i></span>' +
                '<span class="btn-rating-details-label">View Details</span></button>';
        }

        function buildRatingRow(row) {
            const date = row.created_at ? new Date(row.created_at).toLocaleString('en-US', {
                month: 'short', day: 'numeric', year: 'numeric',
                hour: 'numeric', minute: '2-digit'
            }) : '';

            return '<tr>' +
                '<td><span class="ticket-id">' + escapeHtml(row.ticket_number) + '</span></td>' +
                '<td><span class="category-pill">' + escapeHtml(row.category) + '</span></td>' +
                '<td>' + escapeHtml(row.rater_firstname + ' ' + row.rater_lastname) + '</td>' +
                '<td>' + escapeHtml(row.rater_department) + '</td>' +
                '<td><span class="tech-name">' + escapeHtml(row.tech_firstname + ' ' + row.tech_lastname) + '</span></td>' +
                '<td><span class="rating-stars rating-' + row.rating + '">' + ratingStars(row.rating) + '</span></td>' +
                '<td class="date-cell">' + escapeHtml(date) + '</td>' +
                '<td class="text-center action-col">' + buildDetailsButton(row, date) + '</td>' +
                '</tr>';
        }

        $(document).on('click', '.btn-rating-details', function() {
            const $btn = $(this);
            const rating = parseInt($btn.attr('data-rating'), 10) || 0;
            const comment = String($btn.attr('data-comment') || '').trim();

            const ticket = $btn.attr('data-ticket') || '—';

            $('#ratingDetailsModalLabel').text('Ticket ' + ticket);
            $('#ratingDetailTicket').text(ticket);
            $('#ratingDetailStars').html(
                '<span class="rating-stars rating-' + rating + '">' + ratingStars(rating) + '</span>'
            );
            $('#ratingDetailRater').text($btn.attr('data-rater') || '—');
            $('#ratingDetailTechnician').text($btn.attr('data-technician') || '—');
            $('#ratingDetailDate').text($btn.attr('data-date') || '—');
            $('#ratingDetailComment')
                .text(comment !== '' ? comment : 'No comment was provided for this rating.')
                .toggleClass('is-empty', comment === '');

            $('#ratingDetailsModal').modal('show');
        });

        <?php if (!empty($itStaffPerformance)): ?>
        $('#performanceTable').DataTable({
            pageLength: 10,
            order: [[3, 'desc']]
        });
        <?php endif; ?>

        const ratingsTableOptions = {
            pageLength: 10,
            order: [[6, 'desc']],
            columnDefs: [
                { orderable: false, targets: 7, className: 'text-center action-col' }
            ]
        };

        <?php if (!empty($ratings)): ?>
        $('#allRatingsTable').DataTable(ratingsTableOptions);
        <?php endif; ?>

        const chartEl = document.getElementById('overallDistributionChart');
        if (chartEl) {
            new Chart(chartEl.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: ['5 Stars', '4 Stars', '3 Stars', '2 Stars', '1 Star'],
                    datasets: [{
                        data: [
                            <?= (int) ($stats['count_5star'] ?? 0) ?>,
                            <?= (int) ($stats['count_4star'] ?? 0) ?>,
                            <?= (int) ($stats['count_3star'] ?? 0) ?>,
                            <?= (int) ($stats['count_2star'] ?? 0) ?>,
                            <?= (int) ($stats['count_1star'] ?? 0) ?>
                        ],
                        backgroundColor: ['#1cc88a', '#58d68d', '#f6c23e', '#f39c12', '#e74a3b'],
                        hoverBackgroundColor: ['#17a673', '#45b06a', '#dda20a', '#d68910', '#c0392b'],
                        borderColor: '#fff',
                        borderWidth: 2
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    cutout: '68%',
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: { usePointStyle: true, padding: 14 }
                        }
                    }
                }
            });
        }

        $('#applyFilters').click(function() {
            const filters = {
                start_date: $('#filterStartDate').val(),
                end_date: $('#filterEndDate').val(),
                it_id: $('#filterItId').val(),
                rating: $('#filterRating').val()
            };

            $.getJSON(base + '/admin/ratings/data', filters, function(data) {
                const table = $.fn.DataTable.isDataTable('#allRatingsTable')
                    ? $('#allRatingsTable').DataTable()
                    : null;

                if (table) {
                    table.destroy();
                }

                const tbody = $('#ratingsTableBody');
                tbody.empty();

                if (!data.length) {
                    tbody.append('<tr><td colspan="8" class="text-center text-muted py-4">No ratings found with these filters.</td></tr>');
                    return;
                }

                data.forEach(function(row) {
                    tbody.append(buildRatingRow(row));
                });

                $('#allRatingsTable').DataTable(ratingsTableOptions);
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
