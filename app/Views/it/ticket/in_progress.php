<?php
$base = rtrim(BASE_URL, '/');
require_once __DIR__ . '/../../partials/it/ticket_view_helpers.php';
$ticketMode = $ticketMode ?? 'in_progress';
$isPendingMode = ($ticketMode === 'pending');
$pageTitle = $isPendingMode ? 'Pending Tickets' : 'In Progress Tickets';
$heroIcon = $isPendingMode ? 'fa-clock' : 'fa-spinner';
$heroDescription = $isPendingMode
    ? 'Tickets waiting in pending status. Move them back to progress or resolve when ready.'
    : 'Active assignments — update status, add notes, or resolve when work is complete.';
$queueTitle = $isPendingMode ? 'Pending Queue' : 'Active Queue';
$emptyState = $isPendingMode
    ? "No pending tickets right now. You're all caught up!"
    : "No tickets in progress. You're all caught up!";
$realtimeRefreshUrl = $isPendingMode
    ? '/it/tickets/pending?realtime_rows=1'
    : '/it/tickets/in_progress?realtime_rows=1';
$realtimeKeepStatus = $isPendingMode ? 'pending' : 'in progress';

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
ksort($statuses);
$priorityOptions = it_ticket_priority_options(array_keys($priorities));
?>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Storage Mart | IT <?= htmlspecialchars($pageTitle) ?></title>

    <link href="<?= htmlspecialchars($base)?>/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="<?= htmlspecialchars($base)?>/assets/css/storagemart.css" rel="stylesheet">
    <?php require_once __DIR__ . '/../../partials/it/theme_head.php'; ?>
    <link rel="icon" href="<?= htmlspecialchars($base)?>/assets/img/favicon.png" type="image/png">
    <link href="<?= htmlspecialchars($base)?>/assets/vendor/datatables/datatables.min.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($base)?>/assets/css/it-ticket-list.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/ticket-history-modal.css" rel="stylesheet">
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
                        <h1><i class="fas <?= htmlspecialchars($heroIcon) ?> mr-2"></i><?= htmlspecialchars($pageTitle) ?></h1>
                        <p><?= htmlspecialchars($heroDescription) ?></p>
                        <div class="quick-nav mt-3">
                            <a href="<?= htmlspecialchars($base) ?>/it/tickets/in_progress" class="btn btn-sm btn-outline-light mr-1">
                                <i class="fas fa-spinner mr-1"></i> In Progress
                            </a>
                            <a href="<?= htmlspecialchars($base) ?>/it/tickets/pending" class="btn btn-sm btn-outline-light mr-1">
                                <i class="fas fa-clock mr-1"></i> Pending
                            </a>
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
                            <?php foreach ($priorityOptions as $priority): ?>
                                <option value="<?= htmlspecialchars($priority) ?>"><?= htmlspecialchars($priority) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-2 mb-md-0">
                        <label for="ipStatusFilter">Status</label>
                        <select id="ipStatusFilter" class="form-control form-control-sm">
                            <option value="">All Statuses</option>
                            <option value="<?= htmlspecialchars(it_ticket_status_filter_open_value()) ?>">Open</option>
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
                        <i class="fas fa-list-ul mr-1"></i> <?= htmlspecialchars($queueTitle) ?>
                    </h6>
                    <span class="badge badge-info"><?= (int) $totalTickets ?> ticket<?= $totalTickets === 1 ? '' : 's' ?></span>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($tickets)): ?>
                        <div class="empty-state">
                            <i class="fas fa-inbox d-block"></i>
                            <?= htmlspecialchars($emptyState) ?>
                        </div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 ticket-realtime-table" id="IT-TicketDatables"
                               data-employee-id="<?= (int) $employeeId ?>"
                               data-realtime-refresh-url="<?= htmlspecialchars($base . $realtimeRefreshUrl) ?>"
                               data-realtime-keep-status="<?= htmlspecialchars($realtimeKeepStatus) ?>"
                               width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Ticket</th>
                                    <th>Requester</th>
                                    <th>Issue</th>
                                    <th>Priority</th>
                                    <th>Status</th>
                                    <th>Assignment</th>
                                    <th>Date Filed</th>
                                    <th class="text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php require __DIR__ . '/../../partials/it/in_progress_ticket_rows.php'; ?>
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

    <div class="modal fade it-update-ticket-modal" id="ticketModal" tabindex="-1" role="dialog" aria-labelledby="ticketModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered it-update-ticket-dialog" role="document">
            <div class="modal-content it-ticket-detail-card shadow">
                <form method="POST" action="<?= htmlspecialchars($base) ?>/it/tickets/update" id="ticketUpdateForm">
                    <div class="card-header py-3 bg-primary it-ticket-modal-header">
                        <h6 class="m-0 font-weight-bold text-white">
                            <i class="fas fa-ticket-alt"></i>
                            <span id="ticketModalTicketNum">Ticket</span>
                        </h6>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <div class="card-body it-ticket-modal-body">
                        <input type="hidden" name="ticket_id" id="ticket_id">
                        <input type="hidden" name="action" id="ticket_action">

                        <p class="it-ticket-modal-action-title mb-3" id="ticketModalLabel">Update Ticket</p>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <div class="small text-gray-500 text-uppercase font-weight-bold">Ticket ID</div>
                                <div class="h6 mb-0" id="ticketModalTicketId">—</div>
                            </div>
                            <div class="col-md-4">
                                <div class="small text-gray-500 text-uppercase font-weight-bold">Employee</div>
                                <div class="h6 mb-0" id="ticketModalEmployee">—</div>
                            </div>
                            <div class="col-md-4">
                                <div class="small text-gray-500 text-uppercase font-weight-bold">Branch</div>
                                <div class="h6 mb-0" id="ticketModalBranch">—</div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <div class="small text-gray-500 text-uppercase font-weight-bold">New Status</div>
                                <div class="h6 mb-0" id="ticketModalNewStatus">—</div>
                            </div>
                            <div class="col-md-4">
                                <div class="small text-gray-500 text-uppercase font-weight-bold">Priority</div>
                                <div class="h6 mb-0" id="ticketModalPriority">—</div>
                            </div>
                            <div class="col-md-4">
                                <div class="small text-gray-500 text-uppercase font-weight-bold">Filed</div>
                                <div class="h6 mb-0" id="ticketModalFiled">—</div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <div class="small text-gray-500 text-uppercase font-weight-bold">Department</div>
                                <div class="h6 mb-0" id="ticketModalDepartment">—</div>
                            </div>
                            <div class="col-md-4">
                                <div class="small text-gray-500 text-uppercase font-weight-bold">Category</div>
                                <div class="h6 mb-0" id="ticketModalCategory">—</div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="small text-gray-500 text-uppercase font-weight-bold">Concern</div>
                            <div class="p-3 bg-light rounded border it-ticket-readonly-box" id="ticketModalConcern">—</div>
                        </div>

                        <div class="mb-3">
                            <div class="small text-gray-500 text-uppercase font-weight-bold">Technical Purpose <span class="text-danger">*</span></div>
                            <select class="form-control it-ticket-field-box" id="technical_purpose" name="technical_purpose" required>
                                <option value="">Select technical purpose</option>
                                <option>Desktop / Laptop Issue</option>
                                <option>Network Issue</option>
                                <option>Software Installation / Activation</option>
                                <option>Application Issue</option>
                                <option>Phone Issue</option>
                                <option>Others</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <div class="small text-gray-500 text-uppercase font-weight-bold">Action Taken <span class="text-danger">*</span></div>
                            <textarea class="form-control it-ticket-field-box" id="action_taken" name="action_taken" rows="4" required placeholder="Describe what was done to address the issue"></textarea>
                        </div>

                        <div class="mb-3">
                            <div class="small text-gray-500 text-uppercase font-weight-bold">Resolution Details <span class="text-danger">*</span></div>
                            <textarea class="form-control it-ticket-field-box" id="result" name="result" rows="4" required placeholder="Outcome or follow-up notes for the requester"></textarea>
                        </div>

                        <div class="mb-0">
                            <div class="small text-gray-500 text-uppercase font-weight-bold">Remarks</div>
                            <textarea class="form-control it-ticket-field-box" id="remarks" name="remarks" rows="3" placeholder="Optional internal remarks"></textarea>
                        </div>
                    </div>

                    <div class="card-footer it-ticket-modal-footer bg-white">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" id="modalSubmitBtn">Submit</button>
                    </div>
                </form>
            </div>
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
    <?php require __DIR__ . '/../../partials/ticket/cancel_ticket_modal.php'; ?>
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/datatables/datatables.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/js/ticket-status-filter.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/js/it-in-progress-tickets.js"></script>

    <script>
    $(document).ready(function() {
        const currentEmployeeId = parseInt($('#IT-TicketDatables').data('employee-id'), 10);

        const actionConfig = {
            'Resolve': {
                title: 'Resolve Ticket',
                statusLabel: 'Resolved',
                submitClass: 'btn-success',
                submitLabel: 'Mark Resolved'
            },
            'Pending': {
                title: 'Mark Ticket Pending',
                statusLabel: 'Pending',
                submitClass: 'btn-warning',
                submitLabel: 'Mark Pending'
            }
        };

        function displayValue(value) {
            const text = String(value || '').trim();
            return text !== '' ? text : '—';
        }

        $(document).on('click', '.openModalBtn', function(e) {
            e.preventDefault();
            const $btn = $(this);
            const ticketId = $btn.data('ticket-id');
            const ticketNum = $btn.data('ticket-num') || '';
            const action = $btn.data('action');
            const assignedTo = $btn.data('assigned');

            if (typeof assignedTo !== 'undefined' && parseInt(assignedTo, 10) !== currentEmployeeId) {
                alert('You cannot modify tickets not assigned to you.');
                return;
            }

            const cfg = actionConfig[action];
            if (!cfg) {
                return;
            }

            $('#ticketUpdateForm')[0].reset();
            $('#ticket_id').val(ticketId);
            $('#ticket_action').val(action);

            $('#ticketModalTicketNum').text(displayValue(ticketNum));
            $('#ticketModalLabel').text(cfg.title);
            $('#ticketModalTicketId').text(ticketId || '—');
            $('#ticketModalEmployee').text(displayValue($btn.data('employee')));
            $('#ticketModalBranch').text(displayValue($btn.data('branch')));
            $('#ticketModalNewStatus').text(cfg.statusLabel);
            $('#ticketModalPriority').text(displayValue($btn.data('priority')));
            $('#ticketModalFiled').text(displayValue($btn.data('filed')));
            $('#ticketModalDepartment').text(displayValue($btn.data('department')));
            $('#ticketModalCategory').text(displayValue($btn.data('category')));
            $('#ticketModalConcern').text(displayValue($btn.data('concern')));

            $('#modalSubmitBtn')
                .removeClass('btn-success btn-warning btn-danger btn-primary')
                .addClass(cfg.submitClass)
                .text(cfg.submitLabel);

            $('#ticketModal').modal('show');
        });

        $(document).on('click', '.viewTicketBtn', function(e) {
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
    <?php require __DIR__ . '/../../partials/flash_modal.php'; ?>
</body>
</html>
