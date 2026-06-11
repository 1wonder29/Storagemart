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
    <title>Storage Mart | Cancel History</title>
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
        $ticketSubPage = 'cancelled';
        require_once __DIR__ . '/../../partials/admin/sidebar_topbar.php';
        ?>

        <div class="container-fluid admin-ticket-page">

            <div class="page-hero hero-cancelled">
                <div class="row align-items-center">
                    <div class="col-lg-7">
                        <h1><i class="fas fa-ban mr-2"></i>Cancel History</h1>
                        <p>Review all cancelled tickets — who cancelled them, why, and what status they had before.</p>
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
                            <i class="fas fa-check-circle d-block"></i>
                            No cancelled tickets found.
                        </div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 ticket-realtime-table" id="cancelledTicketTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Ticket</th>
                                    <th>Employee</th>
                                    <th>Branch</th>
                                    <th>Priority / Status</th>
                                    <th>Cancel Reason</th>
                                    <th>Cancelled By</th>
                                    <th>Date Cancelled</th>
                                    <th class="text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($tickets as $row):
                                    $ticketId = (int) ($row['ticket_id'] ?? 0);
                                    $priority = (string) ($row['priority'] ?? '');
                                    $oldStatus = (string) ($row['old_status'] ?? '');
                                    $date = it_ticket_format_date((string) ($row['date_cancelled'] ?? ''));
                                    $reason = (string) ($row['cancel_reason'] ?? $row['action_details'] ?? '');
                                ?>
                                    <tr data-ticket-id="<?= $ticketId ?>"
                                        data-priority="<?= htmlspecialchars(strtolower(trim($priority))) ?>"
                                        data-status="cancelled">
                                        <td>
                                            <div class="ticket-id-wrap">
                                                <span class="ticket-id"><?= htmlspecialchars((string) ($row['ticket_number'] ?? '')) ?></span>
                                                <?php if (!empty($row['category'])): ?>
                                                    <span class="category-pill"><?= htmlspecialchars((string) $row['category']) ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="employee-name"><?= htmlspecialchars((string) ($row['employee_name'] ?? '')) ?></div>
                                        </td>
                                        <td>
                                            <?php if (!empty($row['branchName'])): ?>
                                                <span class="branch-pill">
                                                    <i class="fas fa-map-marker-alt mr-1"></i><?= htmlspecialchars((string) $row['branchName']) ?>
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
                                            <?php endif; ?>
                                            <?php if ($oldStatus !== ''): ?>
                                                <span class="status-badge status-cancelled mt-1" data-ticket-status>
                                                    Was: <?= htmlspecialchars($oldStatus) ?>
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="cancel-reason" title="<?= htmlspecialchars($reason) ?>">
                                                <?= htmlspecialchars(it_ticket_truncate($reason, 80)) ?: '—' ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="employee-name"><?= htmlspecialchars((string) ($row['cancelled_by_name'] ?? '')) ?></div>
                                            <?php if (!empty($row['performed_role'])): ?>
                                                <div class="assignee-hint"><?= htmlspecialchars((string) $row['performed_role']) ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="date-cell" data-order="<?= (int) $date['order'] ?>">
                                            <div class="date-main"><?= htmlspecialchars($date['main']) ?></div>
                                            <?php if ($date['time'] !== ''): ?>
                                                <div class="date-time"><?= htmlspecialchars($date['time']) ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-right">
                                            <a href="<?= htmlspecialchars($base) ?>/admin/tickets/view?id=<?= (int) ($row['ticket_id'] ?? 0) ?>"
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i> View
                                            </a>
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

    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/jquery/jquery.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/js/sb-admin-2.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/datatables/datatables.min.js"></script>
    <script>
    $(function () {
        var $table = $('#cancelledTicketTable');
        if (!$table.length || $table.find('tbody td[colspan]').length) {
            return;
        }
        new DataTable('#cancelledTicketTable', {
            order: [[6, 'desc']],
            pageLength: 10,
            columnDefs: [{ targets: [7], orderable: false, searchable: false }],
        });
    });
    </script>
</body>

</html>
