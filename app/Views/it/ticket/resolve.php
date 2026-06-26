<?php
$base = rtrim(BASE_URL, '/');
require_once __DIR__ . '/../../partials/it/ticket_view_helpers.php';

$branches = [];
$purposes = [];
$results = [];

foreach ($tickets as $t) {
    $bn = trim((string) ($t['branchName'] ?? ''));
    if ($bn !== '') {
        $branches[$bn] = true;
    }
    $tp = trim((string) ($t['technical_purpose'] ?? ''));
    if ($tp !== '') {
        $purposes[$tp] = true;
    }
    $res = trim((string) ($t['result'] ?? ''));
    if ($res !== '') {
        $results[$res] = true;
    }
}

ksort($branches);
ksort($purposes);
ksort($results);

$resultClass = static function (string $result): string {
    $r = strtolower(trim($result));
    if (in_array($r, ['working', 'fixed', 'resolved', 'ok', 'done'], true)) {
        return 'success';
    }
    if (in_array($r, ['partial', 'pending verification', 'on hold'], true)) {
        return 'warning';
    }
    return 'neutral';
};
?>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>Storage Mart | IT Resolved Tickets</title>

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

            <!-- Page hero -->
            <div class="page-hero hero-resolved">
                <div class="row align-items-center">
                    <div class="col-lg-12">
                        <h1><i class="fas fa-check-circle mr-2"></i>Resolved Tickets</h1>
                        <p>Completed IT support records — search by ticket, employee, branch, or issue type.</p>
                    </div>
                </div>
            </div>

            <?php
            $summaryActiveStatus = 'Resolved';
            require __DIR__ . '/../../partials/it/ticket_summary_stats.php';
            ?>

            <!-- Quick filters -->
            <div class="filter-toolbar">
                <div class="row align-items-end">
                    <div class="col-md-3 col-sm-6 mb-2 mb-md-0">
                        <label for="resolveBranchFilter">Branch</label>
                        <select id="resolveBranchFilter" class="form-control form-control-sm">
                            <option value="">All Branches</option>
                            <?php foreach (array_keys($branches) as $branch): ?>
                                <option value="<?= htmlspecialchars($branch) ?>"><?= htmlspecialchars($branch) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-2 mb-md-0">
                        <label for="resolvePurposeFilter">Issue Type</label>
                        <select id="resolvePurposeFilter" class="form-control form-control-sm">
                            <option value="">All Issue Types</option>
                            <?php foreach (array_keys($purposes) as $purpose): ?>
                                <option value="<?= htmlspecialchars($purpose) ?>"><?= htmlspecialchars($purpose) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-2 mb-md-0">
                        <label for="resolveResultFilter">Result</label>
                        <select id="resolveResultFilter" class="form-control form-control-sm">
                            <option value="">All Results</option>
                            <?php foreach (array_keys($results) as $result): ?>
                                <option value="<?= htmlspecialchars($result) ?>"><?= htmlspecialchars($result) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3 col-sm-6 text-md-right">
                        <button type="button" id="resolveClearFilters" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-undo mr-1"></i> Clear Filters
                        </button>
                    </div>
                </div>
            </div>

            <!-- Tickets table -->
            <div class="card ticket-list-card shadow mb-4">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-list-ul mr-1"></i> Resolution Records
                    </h6>
                    <span class="badge badge-success"><?= count($tickets) ?> ticket<?= count($tickets) === 1 ? '' : 's' ?></span>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($tickets)): ?>
                        <div class="empty-state">
                            <i class="fas fa-inbox d-block"></i>
                            No resolved tickets yet.
                        </div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 ticket-realtime-table" id="ticketTables" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Ticket</th>
                                    <th>Requester</th>
                                    <th>Issue</th>
                                    <th>Resolution</th>
                                    <th>Date Resolved</th>
                                    <th class="text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                    <?php foreach ($tickets as $row):
                                        $ticketId   = (int) ($row['ticket_id'] ?? 0);
                                        $ticketNum  = (string) ($row['ticket_number'] ?? '');
                                        $employee   = (string) ($row['employee_name'] ?? '');
                                        $branch     = (string) ($row['branchName'] ?? '');
                                        $asset      = (string) ($row['asset'] ?? '');
                                        $purpose    = (string) ($row['technical_purpose'] ?? '');
                                        $action     = (string) ($row['action_taken'] ?? '');
                                        $result     = (string) ($row['result'] ?? '');
                                        $remarks    = (string) ($row['remarks'] ?? '');
                                        $performed  = (string) ($row['date_performed'] ?? '');
                                        $ts         = strtotime($performed);
                                        $rClass     = $resultClass($result);
                                    ?>
                                        <tr data-ticket-id="<?= $ticketId ?>">
                                            <td>
                                                <div class="ticket-id-wrap">
                                                    <span class="ticket-id"><?= htmlspecialchars($ticketNum) ?></span>
                                                    <span class="resolved-badge status-badge" data-ticket-status>
                                                        <i class="fas fa-check"></i> Resolved
                                                    </span>
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
                                                <?php if ($purpose !== ''): ?>
                                                    <span class="purpose-pill" title="<?= htmlspecialchars($purpose) ?>">
                                                        <?= htmlspecialchars($purpose) ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-muted">—</span>
                                                <?php endif; ?>
                                                <?php if ($asset !== '' && $asset !== 'N/A - N/A'): ?>
                                                    <div class="asset-hint" title="<?= htmlspecialchars($asset) ?>">
                                                        <i class="fas fa-laptop mr-1"></i><?= htmlspecialchars($asset) ?>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="resolution-block">
                                                    <?php if ($action !== ''): ?>
                                                        <div class="action-text" title="<?= htmlspecialchars($action) ?>">
                                                            <?= htmlspecialchars(it_ticket_truncate($action, 70)) ?>
                                                        </div>
                                                    <?php endif; ?>
                                                    <?php if ($result !== ''): ?>
                                                        <span class="result-pill <?= $rClass ?>">
                                                            <i class="fas fa-<?= $rClass === 'success' ? 'check-circle' : ($rClass === 'warning' ? 'exclamation-circle' : 'info-circle') ?>"></i>
                                                            <?= htmlspecialchars($result) ?>
                                                        </span>
                                                    <?php endif; ?>
                                                    <?php if ($remarks !== ''): ?>
                                                        <div class="remarks-hint" title="<?= htmlspecialchars($remarks) ?>">
                                                            <?= htmlspecialchars(it_ticket_truncate($remarks, 50)) ?>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td class="date-cell" data-order="<?= $ts ?: 0 ?>">
                                                <?php if ($ts): ?>
                                                    <div class="date-main"><?= date('M j, Y', $ts) ?></div>
                                                    <div class="date-time"><?= date('g:i A', $ts) ?></div>
                                                <?php else: ?>
                                                    <span class="text-muted">—</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-right">
                                                <div class="action-btn-group">
                                                    <a href="<?= htmlspecialchars($base) ?>/it/tickets/view?id=<?= $ticketId ?>&from=resolve"
                                                       class="btn btn-sm btn-outline-primary" title="View ticket details">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="<?= htmlspecialchars($base) ?>/assets/generatePDF/generate_technical.php?ticket_id=<?= $ticketId ?>"
                                                       class="btn btn-sm btn-success" title="Generate technical report">
                                                        <i class="fas fa-file-word"></i>
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
</div>

    <script src="<?= htmlspecialchars($base)?>/assets/vendor/jquery/jquery.min.js"></script>
    <script src="<?= htmlspecialchars($base)?>/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="<?= htmlspecialchars($base)?>/assets/vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="<?= htmlspecialchars($base)?>/assets/js/sb-admin-2.min.js"></script>
    <script src="<?= htmlspecialchars($base)?>/assets/vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="<?= htmlspecialchars($base)?>/assets/vendor/datatables/datatables.min.js"></script>
    <script src="<?= htmlspecialchars($base)?>/assets/js/it-resolve-tickets.js"></script>
</body>

</html>
