<?php
$base = rtrim(BASE_URL, '/');
?>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Storage Mart | Audit Trail</title>

    <!-- Custom fonts for this template-->
    <link href="<?= htmlspecialchars($base) ?>/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="<?= htmlspecialchars($base) ?>/assets/css/storagemart.css" rel="stylesheet">

</head>

<body id="page-top">    

    <!-- Page Wrapper -->
    <div id="wrapper">
        <?php 
        $activePage = 'audit_trail';
        require_once __DIR__ . '/../../partials/admin/sidebar_topbar.php';
        ?>
        <!-- End of Topbar -->

        <!-- Begin Page Content -->
        <div class="container-fluid">

            <!-- Page Heading -->
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">
                    <i class="fas fa-history text-primary"></i> Audit Trail
                </h1>
            </div>

            <!-- Summary Cards Row -->
            <div class="row mb-4">
                <!-- Recent Deletions Card -->
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-danger shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                        Deletions (7 Days)</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo count($recentDeletes); ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-trash fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Logs Card -->
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        Total Logs</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $totalCount; ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-list fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Affected Modules Card -->
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-success shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                        Modules Affected</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo count($deletesSummary); ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-cube fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filter Section -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-filter"></i> Filter Audit Logs
                    </h6>
                </div>
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Filter Type</label>
                            <select name="type" class="form-control" onchange="this.form.submit()">
                                <option value="all" <?php echo $filterType === 'all' ? 'selected' : ''; ?>>All Entries</option>
                                <option value="deletes" <?php echo $filterType === 'deletes' ? 'selected' : ''; ?>>Delete Operations Only</option>
                                <option value="by-module" <?php echo $filterType === 'by-module' ? 'selected' : ''; ?>>By Module</option>
                                <option value="by-user" <?php echo $filterType === 'by-user' ? 'selected' : ''; ?>>By User</option>
                                <option value="by-date" <?php echo $filterType === 'by-date' ? 'selected' : ''; ?>>By Date Range</option>
                                <option value="search" <?php echo $filterType === 'search' ? 'selected' : ''; ?>>Search</option>
                            </select>
                        </div>

                        <?php if ($filterType === 'search'): ?>
                            <div class="col-md-6">
                                <label class="form-label">Search Term</label>
                                <input type="text" name="search" class="form-control" placeholder="Search action, module, ID, or performer..." 
                                       value="<?php echo htmlspecialchars($searchTerm ?? ''); ?>">
                            </div>
                        <?php endif; ?>

                        <?php if ($filterType === 'by-module'): ?>
                            <div class="col-md-6">
                                <label class="form-label">Module</label>
                                <select name="module" class="form-control">
                                    <option value="">Select a module...</option>
                                    <?php if (isset($deletesSummary) && !empty($deletesSummary)): ?>
                                        <?php foreach ($deletesSummary as $item): ?>
                                            <option value="<?php echo htmlspecialchars($item['module']); ?>" 
                                                    <?php echo ($module === $item['module']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($item['module']); ?> (<?php echo $item['delete_count']; ?> deletes)
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        <?php endif; ?>

                        <?php if ($filterType === 'by-date'): ?>
                            <div class="col-md-3">
                                <label class="form-label">Start Date</label>
                                <input type="date" name="start_date" class="form-control" value="<?php echo htmlspecialchars($startDate ?? ''); ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">End Date</label>
                                <input type="date" name="end_date" class="form-control" value="<?php echo htmlspecialchars($endDate ?? ''); ?>">
                            </div>
                        <?php endif; ?>

                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-search"></i> Apply Filter
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Audit Logs Table -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-list"></i> Activity Logs
                        <span class="float-right">
                            <small>Page <?php echo $page; ?> of <?php echo max(1, $totalPages); ?></small>
                        </span>
                    </h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th width="12%">Date</th>
                                <th width="8%">Time</th>
                                <th width="30%">Action</th>
                                <th width="15%">Module</th>
                                <th width="12%">Record ID</th>
                                <th width="15%">Performed By</th>
                                <th width="8%">View</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($logs)): ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        <i class="fas fa-inbox"></i> No audit logs found
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($logs as $log): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($log['datelog'] ?? ''); ?></strong>
                                        </td>
                                        <td class="small text-muted">
                                            <?php echo htmlspecialchars($log['timelog'] ?? ''); ?>
                                        </td>
                                        <td>
                                            <?php 
                                                $action = $log['action'] ?? '';
                                                $isDelete = strpos($action, '[DELETE]') !== false;
                                                if ($isDelete): 
                                            ?>
                                                <span class="text-danger font-weight-bold">
                                                    <i class="fas fa-trash"></i> <?php echo htmlspecialchars(substr($action, 10)); ?>
                                                </span>
                                            <?php else: ?>
                                                <?php echo htmlspecialchars(substr($action, 0, 80)); ?>
                                                <?php if (strlen($action) > 80): ?>
                                                    <span class="text-muted">...</span>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge badge-info">
                                                <?php echo htmlspecialchars($log['module'] ?? ''); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <code><?php echo htmlspecialchars($log['ID'] ?? ''); ?></code>
                                        </td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($log['performedby'] ?? 'System'); ?></strong>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-info" 
                                                   onclick="viewDetails('<?php echo htmlspecialchars($log['ID']); ?>')">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <nav class="d-flex justify-content-center mb-4">
                    <ul class="pagination">
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=1&type=<?php echo urlencode($filterType); ?>&limit=<?php echo $limit; ?>">
                                    <i class="fas fa-chevron-double-left"></i>
                                </a>
                            </li>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $page - 1; ?>&type=<?php echo urlencode($filterType); ?>&limit=<?php echo $limit; ?>">
                                    Previous
                                </a>
                            </li>
                        <?php endif; ?>

                        <?php 
                        $start = max(1, $page - 2);
                        $end = min($totalPages, $page + 2);
                        
                        for ($i = $start; $i <= $end; $i++): 
                        ?>
                            <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>&type=<?php echo urlencode($filterType); ?>&limit=<?php echo $limit; ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>

                        <?php if ($page < $totalPages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $page + 1; ?>&type=<?php echo urlencode($filterType); ?>&limit=<?php echo $limit; ?>">
                                    Next
                                </a>
                            </li>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $totalPages; ?>&type=<?php echo urlencode($filterType); ?>&limit=<?php echo $limit; ?>">
                                    <i class="fas fa-chevron-double-right"></i>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php endif; ?>

            <!-- Deletions Summary by Module -->
            <?php if (!empty($deletesSummary)): ?>
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-trash"></i> Delete Operations Summary by Module
                        </h6>
                    </div>
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead class="bg-light">
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
                                            <span class="badge badge-warning">
                                                <?php echo htmlspecialchars($summary['module']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <strong class="text-danger">
                                                <?php echo $summary['delete_count']; ?>
                                            </strong>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                <?php echo htmlspecialchars($summary['last_delete'] ?? 'N/A'); ?>
                                            </small>
                                        </td>
                                        <td>
                                            <a href="?type=by-module&module=<?php echo urlencode($summary['module']); ?>&filter_delete=1" 
                                               class="btn btn-sm btn-outline-danger">
                                                <i class="fas fa-search"></i> View Deletes
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>

        </div>
        <!-- End of Container Fluid -->

    </div>
    <!-- End of Page Wrapper -->

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <!-- Logout Modal-->
    <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Ready to Leave?</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
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

    <!-- Bootstrap core JavaScript-->
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/jquery/jquery.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Core plugin JavaScript-->
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/jquery-easing/jquery.easing.min.js"></script>

    <!-- Custom scripts for all pages-->
    <script src="<?= htmlspecialchars($base) ?>/assets/js/storagemart.min.js"></script>

    <!-- Audit Detail Modal -->
    <div class="modal fade" id="auditDetailModal" tabindex="-1" role="dialog" aria-labelledby="auditDetailLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="auditDetailLabel">
                        <i class="fas fa-history"></i> Detailed Audit Trail
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="auditDetailContent">
                    <div class="text-center">
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

    <script>
        function viewDetails(recordId) {
            event.preventDefault();
            const base = '<?php echo htmlspecialchars($base); ?>';
            
            fetch(`${base}/admin/audit-detail?record_id=${encodeURIComponent(recordId)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.data.length > 0) {
                        let html = `
                            <div class="alert alert-info">
                                <h6><strong>Record ID:</strong> ${escapeHtml(recordId)}</h6>
                                <p class="mb-0"><strong>Total Activities:</strong> ${data.data.length}</p>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover table-bordered">
                                    <thead class="bg-light">
                                        <tr>
                                            <th width="12%">Date</th>
                                            <th width="10%">Time</th>
                                            <th width="25%">Action</th>
                                            <th width="15%">Module</th>
                                            <th width="15%">Performed By</th>
                                            <th width="23%">Details</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                        `;
                        
                        data.data.forEach(log => {
                            const action = escapeHtml(log.action || '');
                            const isDelete = action.includes('[DELETE]');
                            const actionClass = isDelete ? 'text-danger font-weight-bold' : 'text-dark';
                            
                            // Try to parse metadata if it exists
                            let details = '';
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
                            } catch (e) {
                                details = '<small class="text-muted">No additional details</small>';
                            }
                            
                            if (!details) {
                                details = '<small class="text-muted">No additional details</small>';
                            }
                            
                            html += `<tr>
                                <td><strong>${escapeHtml(log.datelog || 'N/A')}</strong></td>
                                <td class="text-muted small">${escapeHtml(log.timelog || 'N/A')}</td>
                                <td class="${actionClass}">${action}</td>
                                <td><span class="badge badge-info">${escapeHtml(log.module || 'N/A')}</span></td>
                                <td><strong>${escapeHtml(log.performedby || 'System')}</strong></td>
                                <td>${details}</td>
                            </tr>`;
                        });
                        
                        html += `
                                    </tbody>
                                </table>
                            </div>
                        `;
                        
                        document.getElementById('auditDetailContent').innerHTML = html;
                        $('#auditDetailModal').modal('show');
                    } else {
                        document.getElementById('auditDetailContent').innerHTML = `
                            <div class="alert alert-warning" role="alert">
                                <i class="fas fa-info-circle"></i> No audit trail found for Record ID: ${escapeHtml(recordId)}
                            </div>
                        `;
                        $('#auditDetailModal').modal('show');
                    }
                })
                .catch(error => {
                    document.getElementById('auditDetailContent').innerHTML = `
                        <div class="alert alert-danger" role="alert">
                            <i class="fas fa-exclamation-circle"></i> Error fetching audit details: ${escapeHtml(error.message)}
                        </div>
                    `;
                    $('#auditDetailModal').modal('show');
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
