<?php
$base = rtrim(BASE_URL, '/');
require_once __DIR__ . '/../../partials/it/ticket_view_helpers.php';

$tickets = $tickets ?? [];
$totalPending = count($tickets);
$departments = [];
$branches = [];
$priorities = [];
$thisMonth = 0;
$now = time();

foreach ($tickets as $t) {
    $dept = trim((string) ($t['department'] ?? ''));
    if ($dept !== '') {
        $departments[$dept] = true;
    }
    $bn = trim((string) ($t['branchName'] ?? ''));
    if ($bn !== '') {
        $branches[$bn] = true;
    }
    $pr = trim((string) ($t['priority'] ?? ''));
    if ($pr !== '') {
        $priorities[$pr] = true;
    }
    $df = strtotime((string) ($t['date_filed'] ?? ''));
    if ($df && (int) date('Y', $df) === (int) date('Y', $now) && (int) date('n', $df) === (int) date('n', $now)) {
        $thisMonth++;
    }
}

ksort($departments);
ksort($branches);
$priorityOptions = it_ticket_priority_options(array_keys($priorities));
?>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Storage Mart | Pending Tickets</title>
    <link href="<?= htmlspecialchars($base) ?>/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/storagemart.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/admin-ticket-list.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/ticket-history-modal.css" rel="stylesheet">
    <link rel="icon" href="<?= htmlspecialchars($base) ?>/assets/img/favicon.ico" type="image/x-icon">
    <link href="<?= htmlspecialchars($base) ?>/assets/vendor/datatables/datatables.min.css" rel="stylesheet">
</head>

<body id="page-top">

    <div id="wrapper">
        <?php
        $activePage = 'pendings';
        require_once __DIR__ . '/../../partials/admin/sidebar_topbar.php';
        ?>

        <div class="container-fluid admin-ticket-page">

            <div class="page-hero hero-pending">
                <div class="row align-items-center">
                    <div class="col-lg-7">
                        <h1><i class="fas fa-folder-open mr-2"></i>Open Tickets</h1>
                        <p>Review newly filed tickets — approve and assign to IT to move them to In Progress.</p>
                        <div class="quick-nav mt-3">
                            <a href="<?= htmlspecialchars($base) ?>/admin/tickets" class="btn btn-sm btn-outline-light">
                                <i class="fas fa-ticket-alt mr-1"></i> All Tickets
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-5">
                        <div class="row mt-3 mt-lg-0">
                            <div class="col-4">
                                <div class="hero-stat">
                                    <div class="stat-value"><?= (int) $totalPending ?></div>
                                    <div class="stat-label">Open</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="hero-stat">
                                    <div class="stat-value"><?= count($priorities) ?></div>
                                    <div class="stat-label">Priorities</div>
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
                        <label for="pendingDeptFilter">Department</label>
                        <select id="pendingDeptFilter" class="form-control form-control-sm">
                            <option value="">All Departments</option>
                            <?php foreach (array_keys($departments) as $dept): ?>
                                <option value="<?= htmlspecialchars($dept) ?>"><?= htmlspecialchars($dept) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-2 mb-md-0">
                        <label for="pendingBranchFilter">Branch</label>
                        <select id="pendingBranchFilter" class="form-control form-control-sm">
                            <option value="">All Branches</option>
                            <?php foreach (array_keys($branches) as $branch): ?>
                                <option value="<?= htmlspecialchars($branch) ?>"><?= htmlspecialchars($branch) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-2 mb-md-0">
                        <label for="pendingPriorityFilter">Priority</label>
                        <select id="pendingPriorityFilter" class="form-control form-control-sm">
                            <option value="">All Priorities</option>
                            <?php foreach ($priorityOptions as $priority): ?>
                                <option value="<?= htmlspecialchars($priority) ?>"><?= htmlspecialchars($priority) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3 col-sm-6 text-md-right">
                        <button type="button" id="pendingClearFilters" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-undo mr-1"></i> Clear Filters
                        </button>
                    </div>
                </div>
            </div>

            <div class="card ticket-list-card shadow mb-4">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-inbox mr-1"></i> Awaiting Review
                    </h6>
                    <span class="badge badge-warning text-dark"><?= (int) $totalPending ?> ticket<?= $totalPending === 1 ? '' : 's' ?></span>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($tickets)): ?>
                        <div class="empty-state">
                            <i class="fas fa-check-circle d-block"></i>
                            No pending tickets — all caught up!
                        </div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 ticket-realtime-table" id="pendings" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Ticket</th>
                                    <th>Employee</th>
                                    <th>Branch</th>
                                    <th>Concern</th>
                                    <th>Priority</th>
                                    <th>Status</th>
                                    <th>Date Filed</th>
                                    <th class="text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($tickets as $row):
                                    $ticketId = (int) ($row['ticket_id'] ?? 0);
                                    $priority = (string) ($row['priority'] ?? '');
                                    $status = (string) ($row['status'] ?? '');
                                    $department = (string) ($row['department'] ?? '');
                                    $branch = (string) ($row['branchName'] ?? '');
                                    $date = it_ticket_format_date((string) ($row['date_filed'] ?? ''));
                                    $concern = (string) ($row['concern_details'] ?? '');
                                    $assetInfo = (string) ($row['asset_info'] ?? '');
                                ?>
                                    <tr data-ticket-id="<?= $ticketId ?>"
                                        data-department="<?= htmlspecialchars(strtolower(trim($department))) ?>"
                                        data-branch="<?= htmlspecialchars(strtolower(trim($branch))) ?>"
                                        data-priority="<?= htmlspecialchars(strtolower(trim($priority))) ?>"
                                        data-status="<?= htmlspecialchars(strtolower(trim($status))) ?>">
                                        <td>
                                            <div class="ticket-id-wrap">
                                                <span class="ticket-id"><?= htmlspecialchars((string) ($row['ticket_number'] ?? '')) ?></span>
                                                <?php if (!empty($row['category'])): ?>
                                                    <span class="category-pill"><?= htmlspecialchars((string) $row['category']) ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="employee-name"><?= htmlspecialchars((string) ($row['fullname'] ?? '')) ?></div>
                                            <?php if ($department !== ''): ?>
                                                <div class="assignee-hint">
                                                    <i class="fas fa-building mr-1"></i><?= htmlspecialchars($department) ?>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($branch !== ''): ?>
                                                <span class="branch-pill">
                                                    <i class="fas fa-map-marker-alt mr-1"></i><?= htmlspecialchars($branch) ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted">—</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="concern-text" title="<?= htmlspecialchars($concern) ?>">
                                                <?= htmlspecialchars(it_ticket_truncate($concern, 80)) ?: '—' ?>
                                            </div>
                                            <?php if ($assetInfo !== ''): ?>
                                                <div class="asset-hint">
                                                    <i class="fas fa-desktop mr-1"></i><?= htmlspecialchars(it_ticket_truncate($assetInfo, 50)) ?>
                                                </div>
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
                                                <a href="<?= htmlspecialchars($base) ?>/admin/tickets/view?id=<?= $ticketId ?>"
                                                   class="btn btn-sm btn-outline-primary" title="View full detail">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <button type="button" class="btn btn-sm btn-outline-info viewTicketBtn"
                                                    title="View &amp; comments"
                                                    data-ticket-id="<?= $ticketId ?>"
                                                    data-ticket-num="<?= htmlspecialchars((string) ($row['ticket_number'] ?? '')) ?>"
                                                    data-employee="<?= htmlspecialchars((string) ($row['fullname'] ?? '')) ?>"
                                                    data-priority="<?= htmlspecialchars($priority) ?>"
                                                    data-status="<?= htmlspecialchars($status) ?>"
                                                    data-concern="<?= htmlspecialchars($concern) ?>">
                                                    <i class="fas fa-comments"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-success"
                                                    title="Approve &amp; assign"
                                                    data-toggle="modal" data-target="#approveAssignModal"
                                                    data-ticket-id="<?= $ticketId ?>">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                                <div class="dropdown">
                                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button"
                                                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="More actions">
                                                        <i class="fas fa-ellipsis-v"></i>
                                                    </button>
                                                    <div class="dropdown-menu dropdown-menu-right shadow">
                                                        <button type="button" class="dropdown-item text-danger"
                                                            data-toggle="modal" data-target="#declineModal"
                                                            data-ticket-id="<?= $ticketId ?>">
                                                            <i class="fas fa-times mr-2"></i> Decline
                                                        </button>
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

    <div class="modal fade" id="approveAssignModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <form method="POST" action="<?= htmlspecialchars($base) ?>/admin/tickets/approve-assign">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                <input type="hidden" name="ticket_id" id="approve_ticket_id" value="">
                <div class="modal-content">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title mb-0">Approve &amp; Assign</h5>
                        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Select IT Staff</label>
                            <select class="form-control" name="assigned_to" required>
                                <option value="">-- Select IT Staff --</option>
                                <?php foreach ($itStaff as $s): ?>
                                    <option value="<?= (int) $s['employee_id'] ?>">
                                        <?= htmlspecialchars($s['firstname'] . ' ' . $s['lastname']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group mb-0">
                            <label>Remarks (optional)</label>
                            <textarea name="remarks" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Approve &amp; Assign</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="declineModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <form method="POST" action="<?= htmlspecialchars($base) ?>/admin/tickets/decline">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                <input type="hidden" name="ticket_id" id="decline_ticket_id" value="">
                <input type="hidden" name="action" value="Decline">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title mb-0">Decline Ticket</h5>
                        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Reason for Decline</label>
                            <textarea name="decline_reason" class="form-control" rows="4" required></textarea>
                        </div>
                        <div class="form-group mb-0">
                            <label>Remarks (optional)</label>
                            <textarea name="remarks" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Confirm Decline</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade ticket-communication-modal theme-communication" id="viewTicketModal" tabindex="-1" role="dialog" aria-labelledby="viewTicketLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="modal-title-wrap">
                        <span class="modal-icon"><i class="fas fa-comments"></i></span>
                        <h5 class="modal-title" id="viewTicketLabel">Ticket Communication</h5>
                    </div>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="modal-meta-grid cols-4">
                        <div class="modal-meta-field">
                            <label for="view_ticket_number">Ticket #</label>
                            <input type="text" id="view_ticket_number" class="form-control form-control-sm" readonly>
                        </div>
                        <div class="modal-meta-field">
                            <label for="view_employee">Employee</label>
                            <input type="text" id="view_employee" class="form-control form-control-sm" readonly>
                        </div>
                        <div class="modal-meta-field">
                            <label for="view_priority">Priority</label>
                            <input type="text" id="view_priority" class="form-control form-control-sm" readonly>
                        </div>
                        <div class="modal-meta-field">
                            <label for="view_status">Status</label>
                            <input type="text" id="view_status" class="form-control form-control-sm" readonly>
                        </div>
                    </div>
                    <div class="modal-meta-field modal-concern-field mb-3">
                        <label for="view_concern">Concern</label>
                        <textarea id="view_concern" class="form-control form-control-sm" rows="2" readonly></textarea>
                    </div>
                    <?php
                    $ticketId = 0;
                    $canPostComments = true;
                    require __DIR__ . '/../../partials/ticket/comments_section.php';
                    ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-modal-close" data-dismiss="modal">Close</button>
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
    </script>
    <script src="<?= htmlspecialchars($base) ?>/assets/js/admin-pendings.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/js/ticket/ticket_comments.js"></script>
    <?php require __DIR__ . '/../../partials/flash_modal.php'; ?>
</body>

</html>
