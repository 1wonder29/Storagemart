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
    <title>Storage Mart | All Tickets</title>
    <link href="<?= htmlspecialchars($base) ?>/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/storagemart.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/admin-ticket-list.css" rel="stylesheet">
    <link rel="icon" href="<?= htmlspecialchars($base) ?>/assets/img/favicon.ico" type="image/x-icon">
    <link href="<?= htmlspecialchars($base) ?>/assets/vendor/datatables/datatables.min.css" rel="stylesheet">
</head>

<body id="page-top">

    <div id="wrapper">
        <?php
        $activePage = 'tickets';
        $ticketSubPage = 'all';
        require_once __DIR__ . '/../../partials/admin/sidebar_topbar.php';
        ?>

        <div class="container-fluid admin-ticket-page">

            <div class="page-hero hero-all-tickets">
                <div class="row align-items-center">
                    <div class="col-lg-7">
                        <h1><i class="fas fa-ticket-alt mr-2"></i>All Tickets</h1>
                        <p>Manage every ticket across branches — assign staff, track status, and review history.</p>
                        <div class="quick-nav mt-3">
                            <a href="<?= htmlspecialchars($base) ?>/admin/tickets/cancelled" class="btn btn-sm btn-outline-light">
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
                        <label for="adminBranchFilter">Branch</label>
                        <select id="adminBranchFilter" class="form-control form-control-sm">
                            <option value="">All Branches</option>
                            <?php foreach (array_keys($branches) as $branch): ?>
                                <option value="<?= htmlspecialchars($branch) ?>"><?= htmlspecialchars($branch) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-2 mb-md-0">
                        <label for="adminPriorityFilter">Priority</label>
                        <select id="adminPriorityFilter" class="form-control form-control-sm">
                            <option value="">All Priorities</option>
                            <?php foreach ($priorityOptions as $priority): ?>
                                <option value="<?= htmlspecialchars($priority) ?>"><?= htmlspecialchars($priority) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-2 mb-md-0">
                        <label for="adminStatusFilter">Status</label>
                        <select id="adminStatusFilter" class="form-control form-control-sm">
                            <option value="">All Statuses</option>
                            <?php foreach (array_keys($statuses) as $status): ?>
                                <option value="<?= htmlspecialchars($status) ?>"><?= htmlspecialchars($status) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3 col-sm-6 text-md-right">
                        <button type="button" id="adminClearFilters" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-undo mr-1"></i> Clear Filters
                        </button>
                    </div>
                </div>
            </div>

            <div class="card ticket-list-card shadow mb-4">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-list-ul mr-1"></i> Ticket Directory
                    </h6>
                    <div class="card-header-actions">
                        <span class="badge badge-primary"><?= (int) $totalTickets ?> ticket<?= $totalTickets === 1 ? '' : 's' ?></span>
                        <a href="<?= htmlspecialchars($base) ?>/admin/tickets/add" class="btn btn-sm btn-primary">
                            <i class="fas fa-plus mr-1"></i> Add Ticket
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($tickets)): ?>
                        <div class="empty-state">
                            <i class="fas fa-inbox d-block"></i>
                            No tickets found.
                        </div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 ticket-realtime-table" id="logsTicket" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Ticket</th>
                                    <th>Employee</th>
                                    <th>Branch</th>
                                    <th>Priority</th>
                                    <th>Status</th>
                                    <th>Date Filed</th>
                                    <th>Assigned To</th>
                                    <th class="text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($tickets as $row):
                                    $ticketId = (int) ($row['ticket_id'] ?? 0);
                                    $status = (string) ($row['status'] ?? '');
                                    $priority = (string) ($row['priority'] ?? '');
                                    $date = it_ticket_format_date((string) ($row['date_filed'] ?? ''));
                                    $assignedName = trim((string) ($row['assigned_to_name'] ?? ''));
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
                                            <div class="employee-name"><?= htmlspecialchars($row['employee_name']) ?></div>
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
                                        <td>
                                            <?php if ($assignedName !== ''): ?>
                                                <span class="assignee-hint">
                                                    <i class="fas fa-user-cog mr-1"></i><?= htmlspecialchars($assignedName) ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="not-assigned-label">Unassigned</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-right">
                                            <div class="action-btn-group">
                                                <a href="<?= htmlspecialchars($base) ?>/admin/tickets/view?id=<?= $ticketId ?>"
                                                   class="btn btn-sm btn-outline-primary" title="View full detail">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <?php if (strcasecmp($status, 'resolved') === 0): ?>
                                                <a href="<?= htmlspecialchars($base) ?>/admin/tickets/download-record?id=<?= $ticketId ?>"
                                                   class="btn btn-sm btn-success" title="Generate technical report">
                                                    <i class="fas fa-file-word"></i>
                                                </a>
                                                <?php endif; ?>
                                                <button type="button" class="btn btn-sm btn-outline-info viewBtn"
                                                    title="View history"
                                                    data-ticketid="<?= $ticketId ?>"
                                                    data-ticketnum="<?= htmlspecialchars($row['ticket_number']) ?>"
                                                    data-employee="<?= htmlspecialchars($row['employee_name']) ?>"
                                                    data-branch="<?= htmlspecialchars($row['branchName'] ?? '') ?>"
                                                    data-priority="<?= htmlspecialchars($priority) ?>"
                                                    data-status="<?= htmlspecialchars($status) ?>">
                                                    <i class="fas fa-history"></i>
                                                </button>
                                                <div class="dropdown">
                                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button"
                                                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="More actions">
                                                        <i class="fas fa-ellipsis-v"></i>
                                                    </button>
                                                    <div class="dropdown-menu dropdown-menu-right shadow">
                                                        <?php if (strcasecmp($status, 'resolved') !== 0): ?>
                                                            <a href="#" class="dropdown-item openUpdateAssignBtn"
                                                            data-ticket-id="<?= $ticketId ?>"
                                                            data-assignedid="<?= htmlspecialchars((string) ($row['assigned_to_id'] ?? '')) ?>"
                                                            data-status="<?= htmlspecialchars($status) ?>">
                                                                <i class="fas fa-user-edit fa-sm fa-fw mr-2 text-gray-400"></i>
                                                                Update Assignment
                                                            </a>
                                                        <?php else: ?>
                                                            <span class="dropdown-item text-muted" style="cursor:not-allowed;">
                                                                <i class="fas fa-lock fa-sm fa-fw mr-2"></i>
                                                                Assignment Locked
                                                            </span>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
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

    <div class="modal fade" id="viewTicketModal" tabindex="-1" aria-labelledby="viewTicketLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="viewTicketLabel">Ticket History</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label>Ticket Number</label>
                            <input type="text" id="ticket_number" class="form-control" readonly>
                        </div>
                        <div class="col-md-6">
                            <label>Status</label>
                            <input type="text" id="status" class="form-control" readonly>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label>Employee</label>
                            <input type="text" id="employee" class="form-control" readonly>
                        </div>
                        <div class="col-md-6">
                            <label>Priority</label>
                            <input type="text" id="priority" class="form-control" readonly>
                        </div>
                    </div>

                    <h6 class="mt-4">History Records</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered" id="ticketHistoryTable" width="100%" cellspacing="0">
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

                    <?php
                    $ticketId = 0;
                    $canPostComments = true;
                    require __DIR__ . '/../../partials/ticket/comments_section.php';
                    ?>
                </div>
                <div class="modal-footer">
                    <a href="#" id="viewFullDetailLink" class="btn btn-info mr-auto">
                        <i class="fas fa-external-link-alt"></i> View Full Detail
                    </a>
                    <button class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="updateAssignModal" tabindex="-1" role="dialog" aria-labelledby="updateAssignLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <form method="POST" action="<?= htmlspecialchars($base) ?>/admin/tickets/update-assignment" id="updateAssignForm">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="updateAssignLabel">Update Ticket Assignment</h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="ticket_id" id="update_ticket_id" value="">
                        <div class="form-group">
                            <label>Assign To (IT Staff)</label>
                            <select class="form-control" name="assigned_to" id="assigned_to_select" required>
                                <option value="">-- Select IT Staff --</option>
                                <?php foreach ($itStaff as $it): ?>
                                    <option value="<?= (int) $it['employee_id'] ?>">
                                        <?= htmlspecialchars($it['firstname'] . ' ' . $it['lastname']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Remarks (optional)</label>
                            <textarea class="form-control" name="remarks" rows="3" placeholder="Add a short note (optional)"></textarea>
                        </div>
                        <div class="alert alert-info small mb-0">
                            Note: Reassignment is <strong>not allowed</strong> if ticket status = <em>Resolved</em>.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Assignment</button>
                    </div>
                </div>
            </form>
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
    </script>
    <script src="<?= htmlspecialchars($base) ?>/assets/js/admin-tickets.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/js/fetch_ticket_history.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/js/ticket/ticket_comments.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/js/edit_ticket_action.js"></script>
    <?php require __DIR__ . '/../../partials/flash_modal.php'; ?>
</body>

</html>
