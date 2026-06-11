<?php
$base = rtrim(BASE_URL, '/');
require_once __DIR__ . '/../../partials/it/ticket_view_helpers.php';

$totalCancelled = count($tickets);
$branches = [];
$priorities = [];
$thisMonth = 0;
$now = time();

foreach ($tickets as $t) {
    $bn = trim((string) ($t['branchName'] ?? ''));
    if ($bn !== '') {
        $branches[$bn] = true;
    }
    $pr = trim((string) ($t['priority'] ?? ''));
    if ($pr !== '') {
        $priorities[$pr] = true;
    }
    $dc = strtotime((string) ($t['date_cancelled'] ?? ''));
    if ($dc && (int) date('Y', $dc) === (int) date('Y', $now) && (int) date('n', $dc) === (int) date('n', $now)) {
        $thisMonth++;
    }
}

ksort($branches);
ksort($priorities);
?>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Storage Mart | IT Cancel History</title>

    <link href="<?= htmlspecialchars($base)?>/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="<?= htmlspecialchars($base)?>/assets/css/storagemart.css" rel="stylesheet">
    <?php require_once __DIR__ . '/../../partials/it/theme_head.php'; ?>
    <link rel="icon" href="<?= htmlspecialchars($base)?>/assets/img/favicon.png" type="image/png">
    <link href="<?= htmlspecialchars($base)?>/assets/vendor/datatables/datatables.min.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($base)?>/assets/css/it-ticket-list.css" rel="stylesheet">
</head>

<body id="page-top">

    <div id="wrapper">
        <?php
        $activePage = 'tickets';
        require_once __DIR__ . '/../../partials/it/sidebar_topbar.php';
        ?>

        <div class="container-fluid it-ticket-page">

            <div class="page-hero hero-cancelled">
                <div class="row align-items-center">
                    <div class="col-lg-7">
                        <h1><i class="fas fa-ban mr-2"></i>Cancel History</h1>
                        <p>Cancelled tickets linked to you — filed by you, assigned to you, or cancelled by you.</p>
                        <div class="quick-nav mt-3">
                            <a href="<?= htmlspecialchars($base) ?>/it/tickets/in_progress" class="btn btn-sm btn-outline-light mr-1">
                                <i class="fas fa-spinner mr-1"></i> In Progress
                            </a>
                            <a href="<?= htmlspecialchars($base) ?>/it/tickets/resolve" class="btn btn-sm btn-outline-light mr-1">
                                <i class="fas fa-check-circle mr-1"></i> Resolved
                            </a>
                            <a href="<?= htmlspecialchars($base) ?>/it/tickets" class="btn btn-sm btn-outline-light">
                                <i class="fas fa-ticket-alt mr-1"></i> My Tickets
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-5">
                        <div class="row mt-3 mt-lg-0">
                            <div class="col-4">
                                <div class="hero-stat">
                                    <div class="stat-value"><?= (int) $totalCancelled ?></div>
                                    <div class="stat-label">Cancelled</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="hero-stat">
                                    <div class="stat-value"><?= (int) $thisMonth ?></div>
                                    <div class="stat-label">This Month</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="hero-stat">
                                    <div class="stat-value"><?= count($branches) ?></div>
                                    <div class="stat-label">Branches</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="filter-toolbar">
                <div class="row align-items-end">
                    <div class="col-md-4 col-sm-6 mb-2 mb-md-0">
                        <label for="cancelBranchFilter">Branch</label>
                        <select id="cancelBranchFilter" class="form-control form-control-sm">
                            <option value="">All Branches</option>
                            <?php foreach (array_keys($branches) as $branch): ?>
                                <option value="<?= htmlspecialchars($branch) ?>"><?= htmlspecialchars($branch) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4 col-sm-6 mb-2 mb-md-0">
                        <label for="cancelPriorityFilter">Priority</label>
                        <select id="cancelPriorityFilter" class="form-control form-control-sm">
                            <option value="">All Priorities</option>
                            <?php foreach (array_keys($priorities) as $priority): ?>
                                <option value="<?= htmlspecialchars($priority) ?>"><?= htmlspecialchars($priority) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4 col-sm-6 text-md-right">
                        <button type="button" id="cancelClearFilters" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-undo mr-1"></i> Clear Filters
                        </button>
                    </div>
                </div>
            </div>

            <div class="card ticket-list-card shadow mb-4">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-history mr-1"></i> Cancelled Tickets
                    </h6>
                    <span class="badge badge-danger"><?= (int) $totalCancelled ?> ticket<?= $totalCancelled === 1 ? '' : 's' ?></span>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($tickets)): ?>
                        <div class="empty-state">
                            <i class="fas fa-inbox d-block"></i>
                            No cancelled tickets in your history.
                        </div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 ticket-realtime-table" id="ticketTables" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Ticket</th>
                                    <th>Requester</th>
                                    <th>Category</th>
                                    <th>Cancel Reason</th>
                                    <th>Cancelled By</th>
                                    <th>Date Cancelled</th>
                                    <th class="text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($tickets as $row):
                                    $ticketId = (int) ($row['ticket_id'] ?? 0);
                                    $ticketNum = (string) ($row['ticket_number'] ?? '');
                                    $employee = (string) ($row['employee_name'] ?? '');
                                    $branch = (string) ($row['branchName'] ?? '');
                                    $category = (string) ($row['category'] ?? '');
                                    $priority = (string) ($row['priority'] ?? '');
                                    $reason = (string) ($row['cancel_reason'] ?? $row['action_details'] ?? '');
                                    $cancelledBy = (string) ($row['cancelled_by_name'] ?? '');
                                    $oldStatus = (string) ($row['old_status'] ?? '');
                                    $dateInfo = it_ticket_format_date((string) ($row['date_cancelled'] ?? ''));
                                ?>
                                    <tr data-ticket-id="<?= $ticketId ?>"
                                        data-priority="<?= htmlspecialchars(strtolower(trim($priority))) ?>"
                                        data-status="cancelled">
                                        <td>
                                            <div class="ticket-id-wrap">
                                                <span class="ticket-id"><?= htmlspecialchars($ticketNum) ?></span>
                                                <span class="status-badge status-cancelled mt-1" data-ticket-status>
                                                    <i class="fas fa-ban"></i> Cancelled
                                                </span>
                                                <?php if ($priority !== ''): ?>
                                                    <span class="priority-pill <?= it_ticket_priority_class($priority) ?>" data-ticket-priority>
                                                        <?= htmlspecialchars($priority) ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="employee-name"><?= htmlspecialchars($employee) ?></div>
                                            <?php if ($branch !== ''): ?>
                                                <span class="branch-pill">
                                                    <i class="fas fa-map-marker-alt mr-1"></i><?= htmlspecialchars($branch) ?>
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($category !== ''): ?>
                                                <span class="purpose-pill"><?= htmlspecialchars($category) ?></span>
                                            <?php else: ?>
                                                <span class="text-muted">—</span>
                                            <?php endif; ?>
                                            <?php if ($oldStatus !== ''): ?>
                                                <div class="remarks-hint">Was: <?= htmlspecialchars($oldStatus) ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($reason !== ''): ?>
                                                <div class="action-text" title="<?= htmlspecialchars($reason) ?>">
                                                    <?= htmlspecialchars(it_ticket_truncate($reason, 80)) ?>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-muted">—</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="employee-name"><?= htmlspecialchars($cancelledBy) ?></div>
                                            <?php if (!empty($row['performed_role'])): ?>
                                                <span class="branch-pill"><?= htmlspecialchars((string) $row['performed_role']) ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="date-cell" data-order="<?= (int) $dateInfo['order'] ?>">
                                            <?php if ($dateInfo['main'] !== '—'): ?>
                                                <div class="date-main"><?= htmlspecialchars($dateInfo['main']) ?></div>
                                                <div class="date-time"><?= htmlspecialchars($dateInfo['time']) ?></div>
                                            <?php else: ?>
                                                <span class="text-muted">—</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-right">
                                            <div class="action-btn-group">
                                                <a href="<?= htmlspecialchars($base) ?>/it/tickets/view?id=<?= $ticketId ?>&from=cancelled"
                                                   class="btn btn-sm btn-outline-primary" title="View ticket details">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </div>
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

    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <script src="<?= htmlspecialchars($base)?>/assets/vendor/jquery/jquery.min.js"></script>
    <script src="<?= htmlspecialchars($base)?>/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="<?= htmlspecialchars($base)?>/assets/vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="<?= htmlspecialchars($base)?>/assets/js/sb-admin-2.min.js"></script>
    <script src="<?= htmlspecialchars($base)?>/assets/vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="<?= htmlspecialchars($base)?>/assets/vendor/datatables/datatables.min.js"></script>
    <script src="<?= htmlspecialchars($base)?>/assets/js/it-cancelled-tickets.js"></script>
</body>

</html>
