<?php
$base = rtrim(BASE_URL, '/');
require_once __DIR__ . '/../../partials/it/ticket_view_helpers.php';

$totalTickets = count($tickets);
$branches = [];
$priorities = [];
$statuses = [];
$highPriority = 0;
$now = time();

foreach ($tickets as $t) {
    $bn = trim((string) ($t['branchName'] ?? ''));
    if ($bn !== '') {
        $branches[$bn] = true;
    }
    $pr = trim((string) ($t['priority'] ?? ''));
    if ($pr !== '') {
        $priorities[$pr] = true;
        if (strtolower($pr) === 'high') {
            $highPriority++;
        }
    }
    $st = trim((string) ($t['status'] ?? ''));
    if ($st !== '') {
        $statuses[$st] = true;
    }
}

ksort($branches);
ksort($priorities);
ksort($statuses);
?>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Storage Mart | IT In Progress Tickets</title>

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

            <div class="page-hero hero-in-progress">
                <div class="row align-items-center">
                    <div class="col-lg-7">
                        <h1><i class="fas fa-spinner mr-2"></i>In Progress Tickets</h1>
                        <p>Active assignments — update status, add notes, or resolve when work is complete.</p>
                        <div class="quick-nav mt-3">
                            <a href="<?= htmlspecialchars($base) ?>/it/tickets/resolve" class="btn btn-sm btn-outline-light mr-1">
                                <i class="fas fa-check-circle mr-1"></i> Resolved
                            </a>
                            <a href="<?= htmlspecialchars($base) ?>/it/tickets/cancelled" class="btn btn-sm btn-outline-light mr-1">
                                <i class="fas fa-ban mr-1"></i> Cancel History
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
                                    <div class="stat-value"><?= (int) $totalTickets ?></div>
                                    <div class="stat-label">Active</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="hero-stat">
                                    <div class="stat-value"><?= (int) $highPriority ?></div>
                                    <div class="stat-label">High Priority</div>
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
                    <div class="col-md-3 col-sm-6 mb-2 mb-md-0">
                        <label for="ipBranchFilter">Branch</label>
                        <select id="ipBranchFilter" class="form-control form-control-sm">
                            <option value="">All Branches</option>
                            <?php foreach (array_keys($branches) as $branch): ?>
                                <option value="<?= htmlspecialchars($branch) ?>"><?= htmlspecialchars($branch) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-2 mb-md-0">
                        <label for="ipPriorityFilter">Priority</label>
                        <select id="ipPriorityFilter" class="form-control form-control-sm">
                            <option value="">All Priorities</option>
                            <?php foreach (array_keys($priorities) as $priority): ?>
                                <option value="<?= htmlspecialchars($priority) ?>"><?= htmlspecialchars($priority) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-2 mb-md-0">
                        <label for="ipStatusFilter">Status</label>
                        <select id="ipStatusFilter" class="form-control form-control-sm">
                            <option value="">All Statuses</option>
                            <?php foreach (array_keys($statuses) as $status): ?>
                                <option value="<?= htmlspecialchars($status) ?>"><?= htmlspecialchars($status) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3 col-sm-6 text-md-right">
                        <button type="button" id="ipClearFilters" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-undo mr-1"></i> Clear Filters
                        </button>
                    </div>
                </div>
            </div>

            <div class="card ticket-list-card shadow mb-4">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-list-ul mr-1"></i> Active Queue
                    </h6>
                    <span class="badge badge-info"><?= (int) $totalTickets ?> ticket<?= $totalTickets === 1 ? '' : 's' ?></span>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($tickets)): ?>
                        <div class="empty-state">
                            <i class="fas fa-inbox d-block"></i>
                            No tickets in progress. You're all caught up!
                        </div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="IT-TicketDatables" data-employee-id="<?= (int) $employeeId ?>" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Ticket</th>
                                    <th>Requester</th>
                                    <th>Issue</th>
                                    <th>Assignment</th>
                                    <th>Date Filed</th>
                                    <th class="text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                    <?php foreach ($tickets as $row):
                                        $ticketId = (int) ($row['ticket_id'] ?? 0);
                                        $isAssignedToMe = (
                                            $row['assigned_to'] !== null &&
                                            (int) $row['assigned_to'] === (int) $employeeId
                                        );
                                        $status = (string) ($row['status'] ?? '');
                                        $priority = (string) ($row['priority'] ?? '');
                                        $date = it_ticket_format_date((string) ($row['date_filed'] ?? ''));
                                    ?>
                                        <tr>
                                            <td>
                                                <div class="ticket-id-wrap">
                                                    <span class="ticket-id"><?= htmlspecialchars($row['ticket_number']) ?></span>
                                                    <span class="status-badge <?= it_ticket_status_class($status) ?>">
                                                        <?= htmlspecialchars($status) ?>
                                                    </span>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="employee-name"><?= htmlspecialchars($row['employee_name'] ?? '') ?></div>
                                                <?php if (!empty($row['branchName'])): ?>
                                                    <span class="branch-pill">
                                                        <i class="fas fa-map-marker-alt mr-1"></i><?= htmlspecialchars($row['branchName']) ?>
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if (!empty($row['category'])): ?>
                                                    <span class="category-pill" title="<?= htmlspecialchars($row['category']) ?>">
                                                        <?= htmlspecialchars($row['category']) ?>
                                                    </span>
                                                <?php endif; ?>
                                                <?php if (!empty($row['priority'])): ?>
                                                    <span class="priority-pill <?= it_ticket_priority_class($priority) ?> ml-1">
                                                        <i class="fas fa-flag"></i> <?= htmlspecialchars($priority) ?>
                                                    </span>
                                                <?php endif; ?>
                                                <?php if (!empty($row['concern_details'])): ?>
                                                    <div class="concern-text" title="<?= htmlspecialchars($row['concern_details']) ?>">
                                                        <?= htmlspecialchars(it_ticket_truncate((string) $row['concern_details'], 80)) ?>
                                                    </div>
                                                <?php endif; ?>
                                                <?php
                                                $asset = (string) ($row['asset_info'] ?? '');
                                                if ($asset !== '' && $asset !== 'N/A - General'):
                                                ?>
                                                    <div class="asset-hint" title="<?= htmlspecialchars($asset) ?>">
                                                        <i class="fas fa-laptop mr-1"></i><?= htmlspecialchars($asset) ?>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="employee-name"><?= htmlspecialchars($row['assigned_to_name'] ?? 'Unassigned') ?></div>
                                                <?php if ($isAssignedToMe): ?>
                                                    <span class="mine-badge"><i class="fas fa-user-check mr-1"></i>Assigned to you</span>
                                                <?php endif; ?>
                                                <?php if (!empty($row['remarks'])): ?>
                                                    <div class="remarks-hint" title="<?= htmlspecialchars($row['remarks']) ?>">
                                                        <?= htmlspecialchars(it_ticket_truncate((string) $row['remarks'], 50)) ?>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td class="date-cell" data-order="<?= (int) $date['order'] ?>">
                                                <div class="date-main"><?= htmlspecialchars($date['main']) ?></div>
                                                <?php if ($date['time'] !== ''): ?>
                                                    <div class="date-time"><?= htmlspecialchars($date['time']) ?></div>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-right">
                                                <?php if ($isAssignedToMe): ?>
                                                    <div class="action-btn-group">
                                                        <a href="<?= htmlspecialchars($base) ?>/it/tickets/view?id=<?= $ticketId ?>&from=in_progress"
                                                           class="btn btn-sm btn-outline-primary" title="View full detail">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <button type="button" class="btn btn-sm btn-outline-info viewTicketBtn"
                                                            title="View &amp; comments"
                                                            data-ticket-id="<?= $ticketId ?>"
                                                            data-ticket-num="<?= htmlspecialchars($row['ticket_number']) ?>"
                                                            data-employee="<?= htmlspecialchars($row['employee_name'] ?? '') ?>"
                                                            data-priority="<?= htmlspecialchars($priority) ?>"
                                                            data-status="<?= htmlspecialchars($status) ?>"
                                                            data-concern="<?= htmlspecialchars($row['concern_details'] ?? '') ?>">
                                                            <i class="fas fa-comments"></i>
                                                        </button>
                                                        <div class="dropdown">
                                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button"
                                                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="Update ticket">
                                                                <i class="fas fa-cog"></i>
                                                            </button>
                                                            <div class="dropdown-menu dropdown-menu-right shadow">
                                                                <h6 class="dropdown-header">Update Status</h6>
                                                                <a href="#" class="dropdown-item openModalBtn" data-action="Resolve" data-ticket-id="<?= $ticketId ?>" data-assigned="<?= $row['assigned_to'] ?>">
                                                                    <i class="fas fa-check fa-sm fa-fw mr-2 text-success"></i> Resolved
                                                                </a>
                                                                <a href="#" class="dropdown-item openModalBtn" data-action="On Hold" data-ticket-id="<?= $ticketId ?>" data-assigned="<?= $row['assigned_to'] ?>">
                                                                    <i class="fas fa-pause fa-sm fa-fw mr-2 text-warning"></i> On Hold
                                                                </a>
                                                                <a href="#" class="dropdown-item openModalBtn" data-action="Unresolved" data-ticket-id="<?= $ticketId ?>" data-assigned="<?= $row['assigned_to'] ?>">
                                                                    <i class="fas fa-times fa-sm fa-fw mr-2 text-danger"></i> Unresolved
                                                                </a>
                                                                <div class="dropdown-divider"></div>
                                                                <?php
                                                                $ticketStatus = $status;
                                                                $ticketNumber = (string) ($row['ticket_number'] ?? '');
                                                                $btnClass = 'dropdown-item text-danger';
                                                                require __DIR__ . '/../../partials/ticket/cancel_ticket_button.php';
                                                                ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php else: ?>
                                                    <span class="not-assigned-label">Not assigned to you</span>
                                                <?php endif; ?>
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

    <a class="scroll-to-top rounded" href="#page-top"><i class="fas fa-angle-up"></i></a>

    <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document"><div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Ready to Leave?</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">Select "Logout" below if you are ready to end your current session.</div>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                <a class="btn btn-primary" href="<?= htmlspecialchars($base) ?>/logout">Logout</a>
            </div>
        </div></div>
    </div>

    <div class="modal fade" id="ticketModal" tabindex="-1" role="dialog" aria-labelledby="ticketModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" style="margin-top: 100px;" role="document">
            <form method="POST" action="<?= htmlspecialchars($base) ?>/it/tickets/update">
                <div class="modal-content shadow-lg border-0" style="margin:auto; max-width:850px;">
                    <div class="modal-header bg-primary text-white text-center">
                        <h5 class="modal-title w-100" id="ticketModalLabel">Update Ticket</h5>
                        <button type="button" class="close text-white position-absolute" style="right:15px;" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body px-4 py-3">
                        <input type="hidden" name="ticket_id" id="ticket_id">
                        <input type="hidden" name="action" id="ticket_action">
                        <h5 class="text-primary text-center mb-3">Technical Details</h5>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label>Technical Purpose</label>
                                <select class="form-control" name="technical_purpose" required>
                                    <option value="">-- Select Technical Purpose --</option>
                                    <option>Desktop / Laptop Issue</option>
                                    <option>Network Issue</option>
                                    <option>Software Installation / Activation</option>
                                    <option>Application Issue</option>
                                    <option>Phone Issue</option>
                                    <option>Others</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label>Status</label>
                                <select class="form-control" name="status" required>
                                    <option value="Resolved">Resolved</option>
                                    <option value="On Hold">On Hold</option>
                                    <option value="Unresolved">Unresolved</option>
                                </select>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label>Action Taken</label>
                                <textarea class="form-control" name="action_taken" rows="3" required></textarea>
                            </div>
                            <div class="col-md-6">
                                <label>After Service Note</label>
                                <textarea class="form-control" name="result" rows="3" required></textarea>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Remarks</label>
                            <textarea class="form-control" name="remarks" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer justify-content-center">
                        <button type="button" class="btn btn-secondary px-4" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success px-4" id="modalSubmitBtn">Submit</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="viewTicketModal" tabindex="-1" role="dialog" aria-labelledby="viewTicketLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="viewTicketLabel"><i class="fas fa-comments"></i> Ticket Communication</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="small text-muted text-uppercase">Ticket #</label>
                            <input type="text" id="view_ticket_number" class="form-control form-control-sm" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="small text-muted text-uppercase">Employee</label>
                            <input type="text" id="view_employee" class="form-control form-control-sm" readonly>
                        </div>
                        <div class="col-md-2">
                            <label class="small text-muted text-uppercase">Priority</label>
                            <input type="text" id="view_priority" class="form-control form-control-sm" readonly>
                        </div>
                        <div class="col-md-2">
                            <label class="small text-muted text-uppercase">Status</label>
                            <input type="text" id="view_status" class="form-control form-control-sm" readonly>
                        </div>
                    </div>
                    <div class="form-group mb-3">
                        <label class="small text-muted text-uppercase">Concern</label>
                        <textarea id="view_concern" class="form-control form-control-sm" rows="2" readonly></textarea>
                    </div>
                    <?php
                    $ticketId = 0;
                    $canPostComments = true;
                    require __DIR__ . '/../../partials/ticket/comments_section.php';
                    ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
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
    <script src="<?= htmlspecialchars($base) ?>/assets/js/it-in-progress-tickets.js"></script>

    <script>
    $(document).ready(function() {
        const currentEmployeeId = parseInt($('#IT-TicketDatables').data('employee-id'), 10);

        $('.openModalBtn').click(function(e) {
            e.preventDefault();
            const ticketId = $(this).data('ticket-id');
            const action = $(this).data('action');
            const assignedTo = $(this).data('assigned');

            if (typeof assignedTo !== 'undefined' && parseInt(assignedTo, 10) !== currentEmployeeId) {
                alert('You cannot modify tickets not assigned to you.');
                return;
            }

            const btnColor = action === 'Resolve' ? 'btn-success' :
                             action === 'On Hold' ? 'btn-warning' : 'btn-danger';

            $('#ticket_id').val(ticketId);
            $('#ticket_action').val(action);
            $('#ticketModalLabel').text(action + ' Ticket');
            $('#modalSubmitBtn')
                .removeClass('btn-success btn-warning btn-danger')
                .addClass(btnColor)
                .text(action);

            $('#ticketModal').modal('show');
        });

        $('.viewTicketBtn').click(function(e) {
            e.preventDefault();
            const ticketId = $(this).data('ticket-id');
            $('#view_ticket_number').val($(this).data('ticket-num') || '');
            $('#view_employee').val($(this).data('employee') || '');
            $('#view_priority').val($(this).data('priority') || '');
            $('#view_status').val($(this).data('status') || '');
            $('#view_concern').val($(this).data('concern') || '');
            $('#viewTicketModal').modal('show');

            if (window.TicketComments) {
                TicketComments.load('#viewTicketModal .ticket-comments-section', ticketId);
            }
        });
    });
    </script>
    <script>window.BASE_URL = "<?= htmlspecialchars($base) ?>";</script>
    <script src="<?= htmlspecialchars($base) ?>/assets/js/ticket/ticket_comments.js"></script>
    <?php require __DIR__ . '/../../partials/ticket/cancel_ticket_modal.php'; ?>
    <?php require __DIR__ . '/../../partials/flash_modal.php'; ?>
</body>
</html>
