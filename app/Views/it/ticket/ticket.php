<?php
$base = rtrim(BASE_URL, '/');
require_once __DIR__ . '/../../partials/it/ticket_view_helpers.php';

$totalTickets = count($tickets);
$branches = [];
$priorities = [];
$statuses = [];
$statusCounts = [];
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
    $st = trim((string) ($t['status'] ?? ''));
    if ($st !== '') {
        $statuses[$st] = true;
        $statusCounts[$st] = ($statusCounts[$st] ?? 0) + 1;
    }
    $df = strtotime((string) ($t['date_filed'] ?? ''));
    if ($df && (int) date('Y', $df) === (int) date('Y', $now) && (int) date('n', $df) === (int) date('n', $now)) {
        $thisMonth++;
    }
}

ksort($branches);
ksort($statuses);
$priorityOptions = it_ticket_priority_options(array_keys($priorities));

$openCount = ($statusCounts['Pending'] ?? 0) + ($statusCounts['In Progress'] ?? 0) + ($statusCounts['On Hold'] ?? 0) + ($statusCounts['Reopened'] ?? 0);
?>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Storage Mart | IT My Tickets</title>

    <link href="<?= htmlspecialchars($base)?>/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
        <link href="<?= htmlspecialchars($base) ?>/assets/css/storagemart.css" rel="stylesheet">
        <?php require_once __DIR__ . '/../../partials/it/theme_head.php'; ?>
        <link rel="icon" href="<?= htmlspecialchars($base) ?>/assets/img/favicon.png" type="image/png">
    <link href="<?= htmlspecialchars($base) ?>/assets/vendor/datatables/datatables.min.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/it-ticket-list.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/ticket-history-modal.css" rel="stylesheet">
</head>

<body id="page-top">

    <div id="wrapper">
        <?php
        $activePage = 'tickets';
        require_once __DIR__ . '/../../partials/it/sidebar_topbar.php';
        ?>

        <div class="container-fluid it-ticket-page">

            <div class="page-hero hero-my-tickets">
                <div class="row align-items-center">
                    <div class="col-lg-7">
                        <h1><i class="fas fa-ticket-alt mr-2"></i>My Tickets</h1>
                        <p>Tickets you filed — track status, view history, and follow up on open requests.</p>
                        <div class="quick-nav mt-3">
                            <a href="<?= htmlspecialchars($base) ?>/it/tickets/in_progress" class="btn btn-sm btn-outline-light mr-1">
                                <i class="fas fa-spinner mr-1"></i> In Progress
                            </a>
                            <a href="<?= htmlspecialchars($base) ?>/it/tickets/resolve" class="btn btn-sm btn-outline-light mr-1">
                                <i class="fas fa-check-circle mr-1"></i> Resolved
                            </a>
                            <a href="<?= htmlspecialchars($base) ?>/it/tickets/cancelled" class="btn btn-sm btn-outline-light">
                                <i class="fas fa-ban mr-1"></i> Cancel History
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-5">
                        <div class="row mt-3 mt-lg-0">
                            <div class="col-4">
                                <div class="hero-stat">
                                    <div class="stat-value"><?= (int) $totalTickets ?></div>
                                    <div class="stat-label">Total</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="hero-stat">
                                    <div class="stat-value"><?= (int) $openCount ?></div>
                                    <div class="stat-label">Open</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="hero-stat">
                                    <div class="stat-value"><?= (int) $thisMonth ?></div>
                                    <div class="stat-label">This Month</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="filter-toolbar">
                <div class="row align-items-end">
                    <div class="col-md-3 col-sm-6 mb-2 mb-md-0">
                        <label for="myBranchFilter">Branch</label>
                        <select id="myBranchFilter" class="form-control form-control-sm">
                            <option value="">All Branches</option>
                            <?php foreach (array_keys($branches) as $branch): ?>
                                <option value="<?= htmlspecialchars($branch) ?>"><?= htmlspecialchars($branch) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-2 mb-md-0">
                        <label for="myPriorityFilter">Priority</label>
                        <select id="myPriorityFilter" class="form-control form-control-sm">
                            <option value="">All Priorities</option>
                            <?php foreach ($priorityOptions as $priority): ?>
                                <option value="<?= htmlspecialchars($priority) ?>"><?= htmlspecialchars($priority) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-2 mb-md-0">
                        <label for="myStatusFilter">Status</label>
                        <select id="myStatusFilter" class="form-control form-control-sm">
                            <option value="">All Statuses</option>
                            <option value="<?= htmlspecialchars(it_ticket_status_filter_open_value()) ?>">Open</option>
                            <?php foreach (array_keys($statuses) as $status): ?>
                                <option value="<?= htmlspecialchars($status) ?>"><?= htmlspecialchars($status) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3 col-sm-6 text-md-right">
                        <button type="button" id="myClearFilters" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-undo mr-1"></i> Clear Filters
                        </button>
                    </div>
                </div>
            </div>

            <div class="card ticket-list-card shadow mb-4">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-list-ul mr-1"></i> My Ticket History
                    </h6>
                    <span class="badge badge-primary"><?= (int) $totalTickets ?> ticket<?= $totalTickets === 1 ? '' : 's' ?></span>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($tickets)): ?>
                        <div class="empty-state">
                            <i class="fas fa-inbox d-block"></i>
                            You haven't filed any tickets yet.
                        </div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 ticket-realtime-table" id="ticketsTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Ticket</th>
                                    <th>Concern</th>
                                    <th>Branch</th>
                                    <th>Priority</th>
                                    <th>Status</th>
                                    <th>Date Filed</th>
                                    <th class="text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                    <?php foreach ($tickets as $row):
                                        $ticketId = (int) ($row['ticket_id'] ?? 0);
                                        $status = (string) ($row['status'] ?? '');
                                        $priority = (string) ($row['priority'] ?? '');
                                        $date = it_ticket_format_date((string) ($row['date_filed'] ?? ''));
                                    ?>
                                        <tr data-ticket-id="<?= $ticketId ?>"
                                            data-branch="<?= htmlspecialchars(strtolower(trim((string) ($row['branchName'] ?? '')))) ?>"
                                            data-priority="<?= htmlspecialchars(strtolower(trim($priority))) ?>"
                                            data-status="<?= htmlspecialchars(strtolower(trim($status))) ?>">
                                            <td>
                                                <div class="ticket-id-wrap">
                                                    <span class="ticket-id"><?= htmlspecialchars($row['ticket_number']) ?></span>
                                                    <?php if (!empty($row['category'])): ?>
                                                        <span class="category-pill"><?= htmlspecialchars($row['category']) ?></span>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="concern-text" title="<?= htmlspecialchars($row['concern_details'] ?? '') ?>">
                                                    <?= htmlspecialchars(it_ticket_truncate((string) ($row['concern_details'] ?? ''), 90)) ?: '—' ?>
                                                </div>
                                                <?php if (!empty($row['employee_name'])): ?>
                                                    <div class="assignee-hint">
                                                        <i class="fas fa-user mr-1"></i><?= htmlspecialchars($row['employee_name']) ?>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if (!empty($row['branchName'])): ?>
                                                    <span class="branch-pill">
                                                        <i class="fas fa-map-marker-alt mr-1"></i><?= htmlspecialchars($row['branchName']) ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-muted">—</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($priority !== ''): ?>
                                                    <span class="priority-pill <?= it_ticket_priority_class($priority) ?>" data-ticket-priority>
                                                        <i class="fas fa-flag"></i> <?= htmlspecialchars($priority) ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-muted">—</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($status !== ''): ?>
                                                    <span class="status-badge <?= it_ticket_status_class($status) ?>" data-ticket-status>
                                                        <?= htmlspecialchars($status) ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-muted">—</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="date-cell" data-order="<?= (int) $date['order'] ?>">
                                                <div class="date-main"><?= htmlspecialchars($date['main']) ?></div>
                                                <?php if ($date['time'] !== ''): ?>
                                                    <div class="date-time"><?= htmlspecialchars($date['time']) ?></div>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-right">
                                                <div class="action-btn-group">
                                                    <a href="<?= htmlspecialchars($base) ?>/it/tickets/view?id=<?= $ticketId ?>"
                                                       class="btn btn-sm btn-outline-primary" title="View full detail">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-outline-info viewBtn"
                                                        title="View history"
                                                        data-ticketid="<?= $ticketId ?>"
                                                        data-ticketnum="<?= htmlspecialchars($row['ticket_number']) ?>"
                                                        data-employee="<?= htmlspecialchars($row['employee_name'] ?? '') ?>"
                                                        data-branch="<?= htmlspecialchars($row['branchName'] ?? '') ?>"
                                                        data-priority="<?= htmlspecialchars($priority) ?>"
                                                        data-status="<?= htmlspecialchars($status) ?>">
                                                        <i class="fas fa-history"></i>
                                                    </button>
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

    <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Ready to Leave?</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">Select "Logout" below if you are ready to end your current session.</div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                    <a class="btn btn-primary" href="<?= htmlspecialchars($base) ?>/logout">Logout</a>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade ticket-history-modal theme-it" id="viewTicketModal" tabindex="-1" aria-labelledby="viewTicketLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="modal-title-wrap">
                        <span class="modal-icon"><i class="fas fa-history"></i></span>
                        <h5 class="modal-title" id="viewTicketLabel">Ticket History</h5>
                    </div>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="modal-meta-grid">
                        <div class="modal-meta-field">
                            <label for="ticket_number">Ticket Number</label>
                            <input type="text" id="ticket_number" class="form-control form-control-sm" readonly>
                        </div>
                        <div class="modal-meta-field">
                            <label for="status">Status</label>
                            <input type="text" id="status" class="form-control form-control-sm" readonly>
                        </div>
                        <div class="modal-meta-field">
                            <label for="employee">Employee</label>
                            <input type="text" id="employee" class="form-control form-control-sm" readonly>
                        </div>
                        <div class="modal-meta-field">
                            <label for="priority">Priority</label>
                            <input type="text" id="priority" class="form-control form-control-sm" readonly>
                        </div>
                    </div>

                    <h6 class="modal-section-title"><i class="fas fa-list-alt"></i>History Records</h6>
                    <div class="modal-table-card">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0" id="ticketHistoryTable" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>Action Taken</th>
                                        <th>Technician</th>
                                        <th>Old Status</th>
                                        <th>New Status</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>

                    <?php
                    $ticketId = 0;
                    $canPostComments = true;
                    require __DIR__ . '/../../partials/ticket/comments_section.php';
                    ?>
                </div>
                <div class="modal-footer">
                    <a href="#" id="viewFullDetailLink" class="btn btn-sm btn-view-full-detail mr-auto">
                        <i class="fas fa-external-link-alt mr-1"></i> View Full Detail
                    </a>
                    <button class="btn btn-sm btn-modal-close" data-dismiss="modal">Close</button>
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
    window.BASE_URL = "<?= htmlspecialchars($base) ?>";
    const base = window.BASE_URL;
    </script>
    <script src="<?= htmlspecialchars($base) ?>/assets/js/ticket-status-filter.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/js/it-my-tickets.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/js/ticket/fetch_ticket_history.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/js/ticket/ticket_comments.js"></script>
    <?php require __DIR__ . '/../../partials/flash_modal.php'; ?>
</body>
</html>
