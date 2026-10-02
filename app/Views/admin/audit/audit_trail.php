<?php
$base = rtrim(BASE_URL, '/');

if (!function_exists('audit_module_class')) {
    function audit_module_class(string $module): string
    {
        $m = strtolower($module);
        if (strpos($m, 'auth') !== false) return 'module-auth';
        if (strpos($m, 'ticket') !== false) return 'module-ticket';
        if (strpos($m, 'asset') !== false) return 'module-asset';
        if (strpos($m, 'account') !== false || strpos($m, 'user') !== false) return 'module-account';
        return '';
    }
}

if (!function_exists('audit_is_transfer_action')) {
    function audit_is_transfer_action(string $action): bool
    {
        return strpos($action, '[TRANSFER]') !== false
            || stripos($action, 'Transferred asset') !== false
            || stripos($action, 'Transfer Asset') !== false
            || (strpos($action, '[UPDATE]') !== false && stripos($action, 'Transferred') !== false);
    }
}

if (!function_exists('audit_action_class')) {
    function audit_action_class(string $action): string
    {
        if (strpos($action, '[DELETE]') !== false) return 'action-delete';
        if (audit_is_transfer_action($action)) return 'action-transfer';
        if (strpos($action, '[LOGIN]') !== false) return 'action-login';
        if (strpos($action, '[LOGOUT]') !== false) return 'action-logout';
        return '';
    }
}

if (!function_exists('audit_display_action')) {
    function audit_display_action(string $action): string
    {
        if (strpos($action, '[DELETE]') === 0) {
            return substr($action, 10);
        }
        if (strpos($action, '[TRANSFER]') === 0) {
            return substr($action, 11);
        }
        return $action;
    }
}

if (!function_exists('audit_pagination_query')) {
    function audit_pagination_query(array $params): string
    {
        return http_build_query(array_filter($params, static function ($v) {
            return $v !== null && $v !== '';
        }));
    }
}

$deleteCount = count($recentDeletes);
$transferCount = count($recentTransfers ?? []);
$moduleCount = count($deletesSummary);
$transferModuleCount = count($transfersSummary ?? []);
$paginationBase = [
    'type' => $filterType ?? 'all',
    'limit' => $limit ?? 50,
];
if (!empty($searchTerm)) $paginationBase['search'] = $searchTerm;
if (!empty($module)) $paginationBase['module'] = $module;
if (!empty($performer)) $paginationBase['performer'] = $performer;
if (!empty($startDate)) $paginationBase['start_date'] = $startDate;
if (!empty($endDate)) $paginationBase['end_date'] = $endDate;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Storage Mart | Audit Trail</title>
    <link href="<?= htmlspecialchars($base) ?>/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/storagemart.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/admin-audit-trail.css" rel="stylesheet">
</head>

<body id="page-top">

    <div id="wrapper">
        <?php
        $activePage = 'audit_trail';
        require_once __DIR__ . '/../../partials/admin/sidebar_topbar.php';
        ?>

        <div class="container-fluid admin-audit-page">

            <div class="page-hero">
                <h1><i class="fas fa-history mr-2"></i>Audit Trail</h1>
                <p>Monitor system activity, transfers, user actions, and deletion history across all modules.</p>
            </div>

            <div class="row mb-4">
                <div class="col-xl-3 col-md-6 mb-4 mb-xl-0">
                    <div class="stat-card stat-card-transfers">
                        <div class="stat-card-icon"><i class="fas fa-exchange-alt"></i></div>
                        <div>
                            <span class="stat-card-label">Transfers (7 Days)</span>
                            <span class="stat-card-value"><?= $transferCount ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 mb-4 mb-xl-0">
                    <div class="stat-card stat-card-deletes">
                        <div class="stat-card-icon"><i class="fas fa-trash-alt"></i></div>
                        <div>
                            <span class="stat-card-label">Deletions (7 Days)</span>
                            <span class="stat-card-value"><?= $deleteCount ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 mb-4 mb-xl-0">
                    <div class="stat-card stat-card-total">
                        <div class="stat-card-icon"><i class="fas fa-list-alt"></i></div>
                        <div>
                            <span class="stat-card-label">Total Logs</span>
                            <span class="stat-card-value"><?= (int) $totalCount ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="stat-card stat-card-modules">
                        <div class="stat-card-icon"><i class="fas fa-cubes"></i></div>
                        <div>
                            <span class="stat-card-label">Transfer Modules</span>
                            <span class="stat-card-value"><?= $transferModuleCount ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="filter-toolbar">
                <div class="toolbar-title"><i class="fas fa-filter"></i>Filter Audit Logs</div>
                <form method="GET">
                    <div class="row align-items-end">
                        <div class="col-md-3 col-sm-6 mb-3 mb-md-0">
                            <label for="filterType">Filter Type</label>
                            <select name="type" id="filterType" class="form-control" onchange="this.form.submit()">
                                <option value="all" <?= ($filterType ?? 'all') === 'all' ? 'selected' : '' ?>>All Entries</option>
                                <option value="transfers" <?= ($filterType ?? '') === 'transfers' ? 'selected' : '' ?>>Transfer Operations Only</option>
                                <option value="deletes" <?= ($filterType ?? '') === 'deletes' ? 'selected' : '' ?>>Delete Operations Only</option>
                                <option value="by-module" <?= ($filterType ?? '') === 'by-module' ? 'selected' : '' ?>>By Module</option>
                                <option value="by-user" <?= ($filterType ?? '') === 'by-user' ? 'selected' : '' ?>>By User</option>
                                <option value="by-date" <?= ($filterType ?? '') === 'by-date' ? 'selected' : '' ?>>By Date Range</option>
                                <option value="search" <?= ($filterType ?? '') === 'search' ? 'selected' : '' ?>>Search</option>
                            </select>
                        </div>

                        <?php if (($filterType ?? '') === 'search'): ?>
                        <div class="col-md-5 col-sm-6 mb-3 mb-md-0">
                            <label for="searchTerm">Search Term</label>
                            <input type="text" name="search" id="searchTerm" class="form-control"
                                   placeholder="Search action, module, ID, or performer..."
                                   value="<?= htmlspecialchars($searchTerm ?? '') ?>">
                        </div>
                        <?php endif; ?>

                        <?php if (($filterType ?? '') === 'by-module'): ?>
                        <div class="col-md-5 col-sm-6 mb-3 mb-md-0">
                            <label for="moduleSelect">Module</label>
                            <select name="module" id="moduleSelect" class="form-control">
                                <option value="">Select a module...</option>
                                <?php if (!empty($deletesSummary)): ?>
                                    <?php foreach ($deletesSummary as $item): ?>
                                    <option value="<?= htmlspecialchars($item['module']) ?>"
                                        <?= ($module ?? '') === $item['module'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($item['module']) ?> (<?= (int) $item['delete_count'] ?> deletes)
                                    </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <?php endif; ?>

                        <?php if (($filterType ?? '') === 'by-date'): ?>
                        <div class="col-md-3 col-sm-6 mb-3 mb-md-0">
                            <label for="startDate">Start Date</label>
                            <input type="date" name="start_date" id="startDate" class="form-control"
                                   value="<?= htmlspecialchars($startDate ?? '') ?>">
                        </div>
                        <div class="col-md-3 col-sm-6 mb-3 mb-md-0">
                            <label for="endDate">End Date</label>
                            <input type="date" name="end_date" id="endDate" class="form-control"
                                   value="<?= htmlspecialchars($endDate ?? '') ?>">
                        </div>
                        <?php endif; ?>

                        <div class="col-md-2 col-sm-12 text-md-right">
                            <button type="submit" class="btn btn-primary btn-apply">
                                <i class="fas fa-search mr-1"></i> Apply Filter
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="card report-list-card shadow">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap">
                    <h6><i class="fas fa-clipboard-list mr-1 text-primary"></i> Activity Logs</h6>
                    <span class="page-badge">Page <?= (int) $page ?> of <?= max(1, (int) $totalPages) ?></span>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($logs)): ?>
                        <div class="empty-state">
                            <i class="fas fa-inbox d-block"></i>
                            No audit logs found.
                        </div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Date / Time</th>
                                    <th>Action</th>
                                    <th>Module</th>
                                    <th>Record ID</th>
                                    <th>Performed By</th>
                                    <th class="text-center">View</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($logs as $log):
                                    $action = (string) ($log['action'] ?? '');
                                    $isDelete = strpos($action, '[DELETE]') !== false;
                                    $isTransfer = audit_is_transfer_action($action);
                                    $displayAction = audit_display_action($action);
                                    $moduleName = (string) ($log['module'] ?? '');
                                ?>
                                <tr>
                                    <td class="date-cell">
                                        <div class="date-main"><?= htmlspecialchars($log['datelog'] ?? '') ?></div>
                                        <div class="date-time"><?= htmlspecialchars($log['timelog'] ?? '') ?></div>
                                    </td>
                                    <td>
                                        <div class="action-text <?= audit_action_class($action) ?>">
                                            <?php if ($isDelete): ?>
                                                <i class="fas fa-trash-alt mr-1"></i>
                                            <?php elseif ($isTransfer): ?>
                                                <i class="fas fa-exchange-alt mr-1"></i>
                                            <?php elseif (strpos($action, '[LOGIN]') !== false): ?>
                                                <i class="fas fa-sign-in-alt mr-1"></i>
                                            <?php elseif (strpos($action, '[LOGOUT]') !== false): ?>
                                                <i class="fas fa-sign-out-alt mr-1"></i>
                                            <?php endif; ?>
                                            <?= htmlspecialchars(strlen($displayAction) > 80 ? substr($displayAction, 0, 80) . '...' : $displayAction) ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="module-pill <?= audit_module_class($moduleName) ?>">
                                            <?= htmlspecialchars($moduleName) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="record-id"><?= htmlspecialchars($log['ID'] ?? '') ?></span>
                                    </td>
                                    <td>
                                        <span class="performer-name"><?= htmlspecialchars($log['performedby'] ?? 'System') ?></span>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-view-detail"
                                                onclick="viewDetails('<?= htmlspecialchars($log['ID'] ?? '', ENT_QUOTES) ?>')"
                                                title="View details">
                                            <i class="fas fa-eye"></i>
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

            <?php if ($totalPages > 1): ?>
            <nav class="d-flex justify-content-center audit-pagination">
                <ul class="pagination mb-0">
                    <?php if ($page > 1):
                        $prevQ = audit_pagination_query(array_merge($paginationBase, ['page' => $page - 1]));
                        $firstQ = audit_pagination_query(array_merge($paginationBase, ['page' => 1]));
                    ?>
                    <li class="page-item">
                        <a class="page-link" href="?<?= $firstQ ?>"><i class="fas fa-angle-double-left"></i></a>
                    </li>
                    <li class="page-item">
                        <a class="page-link" href="?<?= $prevQ ?>">Previous</a>
                    </li>
                    <?php endif; ?>

                    <?php
                    $start = max(1, $page - 2);
                    $end = min($totalPages, $page + 2);
                    for ($i = $start; $i <= $end; $i++):
                        $pageQ = audit_pagination_query(array_merge($paginationBase, ['page' => $i]));
                    ?>
                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                        <a class="page-link" href="?<?= $pageQ ?>"><?= $i ?></a>
                    </li>
                    <?php endfor; ?>

                    <?php if ($page < $totalPages):
                        $nextQ = audit_pagination_query(array_merge($paginationBase, ['page' => $page + 1]));
                        $lastQ = audit_pagination_query(array_merge($paginationBase, ['page' => $totalPages]));
                    ?>
                    <li class="page-item">
                        <a class="page-link" href="?<?= $nextQ ?>">Next</a>
                    </li>
                    <li class="page-item">
                        <a class="page-link" href="?<?= $lastQ ?>"><i class="fas fa-angle-double-right"></i></a>
                    </li>
                    <?php endif; ?>
                </ul>
            </nav>
            <?php endif; ?>

            <?php if (!empty($transfersSummary)): ?>
            <div class="card report-list-card shadow mb-4">
                <div class="card-header">
                    <h6><i class="fas fa-exchange-alt mr-1 text-primary"></i> Transfer Operations Summary by Module</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Module</th>
                                    <th>Total Transfers</th>
                                    <th>Last Transfer</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($transfersSummary as $summary): ?>
                                <tr>
                                    <td>
                                        <span class="module-pill <?= audit_module_class($summary['module']) ?>">
                                            <?= htmlspecialchars($summary['module']) ?>
                                        </span>
                                    </td>
                                    <td><span class="transfer-count"><?= (int) $summary['transfer_count'] ?></span></td>
                                    <td class="date-cell">
                                        <div class="date-time"><?= htmlspecialchars($summary['last_transfer'] ?? 'N/A') ?></div>
                                    </td>
                                    <td>
                                        <a href="?type=transfers&module=<?= urlencode($summary['module']) ?>"
                                           class="btn btn-sm btn-outline-primary btn-view-transfers">
                                            <i class="fas fa-search mr-1"></i> View Transfers
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($deletesSummary)): ?>
            <div class="card report-list-card shadow mb-4">
                <div class="card-header">
                    <h6><i class="fas fa-trash-alt mr-1 text-danger"></i> Delete Operations Summary by Module</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Module</th>
                                    <th>Total Deletions</th>
                                    <th>Last Deletion</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($deletesSummary as $summary): ?>
                                <tr>
                                    <td>
                                        <span class="module-pill <?= audit_module_class($summary['module']) ?>">
                                            <?= htmlspecialchars($summary['module']) ?>
                                        </span>
                                    </td>
                                    <td><span class="delete-count"><?= (int) $summary['delete_count'] ?></span></td>
                                    <td class="date-cell">
                                        <div class="date-time"><?= htmlspecialchars($summary['last_delete'] ?? 'N/A') ?></div>
                                    </td>
                                    <td>
                                        <a href="?type=by-module&module=<?= urlencode($summary['module']) ?>&filter_delete=1"
                                           class="btn btn-sm btn-outline-danger btn-view-deletes">
                                            <i class="fas fa-search mr-1"></i> View Deletes
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endif; ?>

        </div>

            </div>
        </div>
    </div>

    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <div class="modal fade" id="auditDetailModal" tabindex="-1" role="dialog" aria-labelledby="auditDetailLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header text-white">
                    <h5 class="modal-title" id="auditDetailLabel">
                        <i class="fas fa-history mr-1"></i> Detailed Audit Trail
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="auditDetailContent">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                    </div>
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

    <script>
    function viewDetails(recordId) {
        const base = '<?= htmlspecialchars($base) ?>';

        document.getElementById('auditDetailContent').innerHTML =
            '<div class="text-center py-4"><div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div></div>';
        $('#auditDetailModal').modal('show');

        fetch(base + '/admin/audit-detail?record_id=' + encodeURIComponent(recordId))
            .then(response => response.json())
            .then(data => {
                if (data.success && data.data.length > 0) {
                    let html = '<div class="alert alert-info border-0" style="background:#e8eaf6;color:#3949ab;">' +
                        '<strong>Record ID:</strong> ' + escapeHtml(recordId) +
                        ' &nbsp;|&nbsp; <strong>Total Activities:</strong> ' + data.data.length +
                        '</div><div class="table-responsive"><table class="table table-hover mb-0">' +
                        '<thead><tr><th>Date</th><th>Time</th><th>Action</th><th>Module</th><th>Performed By</th><th>Details</th></tr></thead><tbody>';

                    data.data.forEach(log => {
                        const action = escapeHtml(log.action || '');
                        const isDelete = action.includes('[DELETE]');
                        const actionClass = isDelete ? 'action-delete' : '';
                        let details = '<small class="text-muted">No additional details</small>';

                        try {
                            if (log.metadata) {
                                const meta = typeof log.metadata === 'string' ? JSON.parse(log.metadata) : log.metadata;
                                if (meta) {
                                    details = '<small>';
                                    if (meta.before || meta.after) {
                                        details += '<strong>Changes:</strong><br/>';
                                        if (meta.before) details += '<em>Before:</em> ' + escapeHtml(JSON.stringify(meta.before, null, 2)) + '<br/>';
                                        if (meta.after) details += '<em>After:</em> ' + escapeHtml(JSON.stringify(meta.after, null, 2)) + '<br/>';
                                    } else {
                                        details += escapeHtml(JSON.stringify(meta, null, 2));
                                    }
                                    details += '</small>';
                                }
                            }
                        } catch (e) {}

                        html += '<tr>' +
                            '<td class="date-cell"><div class="date-main">' + escapeHtml(log.datelog || 'N/A') + '</div></td>' +
                            '<td class="date-cell"><div class="date-time">' + escapeHtml(log.timelog || 'N/A') + '</div></td>' +
                            '<td><div class="action-text ' + actionClass + '">' + action + '</div></td>' +
                            '<td><span class="module-pill">' + escapeHtml(log.module || 'N/A') + '</span></td>' +
                            '<td><span class="performer-name">' + escapeHtml(log.performedby || 'System') + '</span></td>' +
                            '<td>' + details + '</td></tr>';
                    });

                    html += '</tbody></table></div>';
                    document.getElementById('auditDetailContent').innerHTML = html;
                } else {
                    document.getElementById('auditDetailContent').innerHTML =
                        '<div class="alert alert-warning"><i class="fas fa-info-circle mr-1"></i> No audit trail found for Record ID: ' + escapeHtml(recordId) + '</div>';
                }
            })
            .catch(error => {
                document.getElementById('auditDetailContent').innerHTML =
                    '<div class="alert alert-danger"><i class="fas fa-exclamation-circle mr-1"></i> Error fetching audit details: ' + escapeHtml(error.message) + '</div>';
            });
    }

    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    </script>

</body>

</html>
