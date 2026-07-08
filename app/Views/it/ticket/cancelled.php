<?php
$base = rtrim(BASE_URL, '/');
require_once __DIR__ . '/../../partials/it/ticket_view_helpers.php';
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
                    <div class="col-lg-12">
                        <h1><i class="fas fa-ban mr-2"></i>Cancel History</h1>
                        <p>Cancelled tickets linked to you — filed by you, assigned to you, or cancelled by you.</p>
                    </div>
                </div>
            </div>

            <?php
            $summaryActiveStatus = 'Cancelled';
            require __DIR__ . '/../../partials/it/ticket_summary_stats.php';
            ?>

            <div class="card ticket-list-card shadow mb-4">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-history mr-1"></i> Cancelled Tickets
                    </h6>
                    <span class="badge badge-danger"><?= count($tickets) ?> ticket<?= count($tickets) === 1 ? '' : 's' ?></span>
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
                                    <th>Priority</th>
                                    <th>Status</th>
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
                                            <?php if ($priority !== ''): ?>
                                                <span class="priority-pill <?= it_ticket_priority_class($priority) ?>" data-ticket-priority>
                                                    <i class="fas fa-flag"></i> <?= htmlspecialchars($priority) ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted">—</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="status-badge status-cancelled" data-ticket-status>
                                                <i class="fas fa-ban"></i> Cancelled
                                            </span>
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
