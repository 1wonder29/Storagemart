<?php
$base = rtrim(BASE_URL, '/');
require_once __DIR__ . '/../../partials/admin/account_view_helpers.php';

$employees = $employees ?? [];
$departmentLabel = htmlspecialchars($department ?? 'Department');
$totalEmployees = count($employees);
$branches = [];
$positions = [];

foreach ($employees as $row) {
    $branch = trim((string) ($row['branchName'] ?? ''));
    if ($branch !== '') {
        $branches[$branch] = true;
    }
    $position = trim((string) ($row['position'] ?? ''));
    if ($position !== '') {
        $positions[$position] = true;
    }
}

ksort($branches);
ksort($positions);
?>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Storage Mart | Department Employees</title>
    <link href="<?= htmlspecialchars($base) ?>/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/storagemart.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/head-employees.css" rel="stylesheet">
    <link rel="icon" href="<?= htmlspecialchars($base) ?>/assets/img/favicon.png" type="image/png">
    <link href="<?= htmlspecialchars($base) ?>/assets/vendor/datatables/datatables.min.css" rel="stylesheet">
</head>

<body id="page-top">

    <div id="wrapper">
        <?php
        $activePage = 'employee';
        require_once __DIR__ . '/../../partials/head/sidebar_topbar.php';
        ?>

        <div class="container-fluid head-employees-page">

            <div class="page-hero">
                <div class="row align-items-center">
                    <div class="col-lg-7">
                        <h1><i class="fas fa-user-friends mr-2"></i>Department Employees</h1>
                        <p>View and manage staff in your department — browse profiles, tickets, and assigned assets.</p>
                        <div class="hero-dept"><i class="fas fa-building mr-1"></i><?= $departmentLabel ?></div>
                        <div class="quick-nav mt-3">
                            <a href="<?= htmlspecialchars($base) ?>/head/dashboard" class="btn btn-sm btn-outline-light">
                                <i class="fas fa-tachometer-alt mr-1"></i> Dashboard
                            </a>
                            <a href="<?= htmlspecialchars($base) ?>/head/tickets" class="btn btn-sm btn-outline-light">
                                <i class="fas fa-ticket-alt mr-1"></i> Tickets
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-5">
                        <div class="row mt-3 mt-lg-0">
                            <div class="col-4">
                                <div class="hero-stat">
                                    <div class="stat-value"><?= (int) $totalEmployees ?></div>
                                    <div class="stat-label">Total</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="hero-stat">
                                    <div class="stat-value"><?= count($positions) ?></div>
                                    <div class="stat-label">Positions</div>
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
                    <div class="col-md-4 col-sm-6 mb-2 mb-md-0">
                        <label for="employeeBranchFilter">Branch</label>
                        <select id="employeeBranchFilter" class="form-control form-control-sm">
                            <option value="">All Branches</option>
                            <?php foreach (array_keys($branches) as $branch): ?>
                                <option value="<?= htmlspecialchars($branch) ?>"><?= htmlspecialchars($branch) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4 col-sm-6 mb-2 mb-md-0">
                        <label for="employeePositionFilter">Position</label>
                        <select id="employeePositionFilter" class="form-control form-control-sm">
                            <option value="">All Positions</option>
                            <?php foreach (array_keys($positions) as $position): ?>
                                <option value="<?= htmlspecialchars($position) ?>"><?= htmlspecialchars($position) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4 col-sm-6 text-md-right">
                        <button type="button" id="employeeClearFilters" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-undo mr-1"></i> Clear Filters
                        </button>
                    </div>
                </div>
            </div>

            <div class="card data-list-card shadow mb-4">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-address-book mr-1"></i> Employee Directory
                    </h6>
                    <span class="badge badge-primary"><?= (int) $totalEmployees ?> employee<?= $totalEmployees === 1 ? '' : 's' ?></span>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($employees)): ?>
                        <div class="empty-state">
                            <i class="fas fa-users-slash d-block"></i>
                            No employees found in this department.
                        </div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="employee-table" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Employee</th>
                                    <th>Department / Position</th>
                                    <th>Branch</th>
                                    <th>Email</th>
                                    <th>Date Created</th>
                                    <th class="text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($employees as $row):
                                    $employeeId = (int) ($row['employee_id'] ?? 0);
                                    $fullName = admin_employee_full_name($row);
                                    $dept = (string) ($row['department'] ?? '');
                                    $position = (string) ($row['position'] ?? '');
                                    $date = admin_account_format_date((string) ($row['datecreated'] ?? ''));
                                ?>
                                    <tr data-branch="<?= htmlspecialchars(strtolower(trim((string) ($row['branchName'] ?? '')))) ?>"
                                        data-position="<?= htmlspecialchars(strtolower(trim($position))) ?>">
                                        <td>
                                            <div class="employee-name"><?= htmlspecialchars($fullName) ?></div>
                                            <div class="employee-meta">
                                                <span class="employee-id">#<?= $employeeId ?></span>
                                                <?php if (!empty($row['account_id'])): ?>
                                                    <span class="ml-2">Acct #<?= htmlspecialchars((string) $row['account_id']) ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <?php if ($dept !== ''): ?>
                                                <span class="dept-badge <?= admin_employee_department_class($dept) ?>">
                                                    <i class="fas fa-building"></i>
                                                    <?= htmlspecialchars($dept) ?>
                                                </span>
                                            <?php endif; ?>
                                            <?php if ($position !== ''): ?>
                                                <div class="position-text"><?= htmlspecialchars($position) ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($row['branchName'])): ?>
                                                <span class="branch-pill">
                                                    <i class="fas fa-map-marker-alt"></i>
                                                    <?= htmlspecialchars((string) $row['branchName']) ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted">—</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($row['email'])): ?>
                                                <div class="email-text">
                                                    <i class="fas fa-envelope mr-1 text-muted"></i>
                                                    <?= htmlspecialchars((string) $row['email']) ?>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-muted">—</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="date-cell" data-order="<?= (int) $date['order'] ?>">
                                            <div class="date-main"><?= htmlspecialchars($date['main']) ?></div>
                                            <?php if ($date['time'] !== ''): ?>
                                                <div class="date-time"><?= htmlspecialchars($date['time']) ?></div>
                                            <?php endif; ?>
                                            <?php if (!empty($row['createdby'])): ?>
                                                <div class="meta-hint">by <?= htmlspecialchars((string) $row['createdby']) ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-right">
                                            <div class="action-btn-group">
                                                <button type="button"
                                                    class="btn btn-sm btn-outline-primary viewEmployeeTicketsBtn"
                                                    data-employee-id="<?= $employeeId ?>"
                                                    data-name="<?= htmlspecialchars($fullName) ?>"
                                                    title="View tickets">
                                                    <i class="fas fa-ticket-alt mr-1"></i> Tickets
                                                </button>
                                                <button type="button"
                                                    class="btn btn-sm btn-outline-success viewEmployeeAssetsBtn"
                                                    data-employee-id="<?= $employeeId ?>"
                                                    data-name="<?= htmlspecialchars($fullName) ?>"
                                                    title="View assets">
                                                    <i class="fas fa-box mr-1"></i> Assets
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
        </div>
    </div>

    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <!-- Employee Tickets Modal -->
    <div class="modal fade head-employee-modal" id="employeeTicketsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="modal-title-wrap">
                        <span class="modal-icon"><i class="fas fa-ticket-alt"></i></span>
                        <h5 class="modal-title mb-0">Employee Tickets</h5>
                    </div>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="modal-meta-grid">
                        <div class="modal-meta-field">
                            <label for="ticketEmployeeName">Employee</label>
                            <input type="text" id="ticketEmployeeName" class="form-control form-control-sm" readonly>
                        </div>
                        <div class="modal-meta-field">
                            <label for="ticketEmployeeId">Employee ID</label>
                            <input type="text" id="ticketEmployeeId" class="form-control form-control-sm" readonly>
                        </div>
                    </div>
                    <div class="modal-table-card">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0" id="employeeTicketsTable" width="100%" data-dt-ignore="true">
                                <thead>
                                    <tr>
                                        <th>Ticket #</th>
                                        <th>Category</th>
                                        <th>Priority</th>
                                        <th>Status</th>
                                        <th>Date Filed</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Employee Assets Modal -->
    <div class="modal fade head-employee-modal" id="employeeAssetsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="modal-title-wrap">
                        <span class="modal-icon"><i class="fas fa-box"></i></span>
                        <h5 class="modal-title mb-0">Employee Assets</h5>
                    </div>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="modal-meta-grid">
                        <div class="modal-meta-field">
                            <label for="assetEmployeeName">Employee</label>
                            <input type="text" id="assetEmployeeName" class="form-control form-control-sm" readonly>
                        </div>
                        <div class="modal-meta-field">
                            <label for="assetEmployeeId">Employee ID</label>
                            <input type="text" id="assetEmployeeId" class="form-control form-control-sm" readonly>
                        </div>
                    </div>
                    <div class="modal-table-card">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0" id="employeeAssetsTable" data-dt-ignore="true" width="100%">
                                <thead>
                                    <tr>
                                        <th>Asset #</th>
                                        <th>Model</th>
                                        <th>Description</th>
                                        <th>Item Info</th>
                                        <th>Serial Number</th>
                                        <th class="text-right">Action</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Asset Tickets Modal -->
    <div class="modal fade head-employee-modal" id="assetTicketsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="modal-title-wrap">
                        <span class="modal-icon"><i class="fas fa-history"></i></span>
                        <h5 class="modal-title mb-0">Asset Ticket History</h5>
                    </div>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="modal-meta-grid">
                        <div class="modal-meta-field">
                            <label for="assetTicketItemInfo">Item Info</label>
                            <input type="text" id="assetTicketItemInfo" class="form-control form-control-sm" readonly>
                        </div>
                        <div class="modal-meta-field">
                            <label for="assetTicketAssetNumber">Asset Number</label>
                            <input type="text" id="assetTicketAssetNumber" class="form-control form-control-sm" readonly>
                        </div>
                    </div>
                    <div class="modal-table-card">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0" id="assetTicketsTable" data-dt-ignore="true" width="100%">
                                <thead>
                                    <tr>
                                        <th>Ticket #</th>
                                        <th>Category</th>
                                        <th>Priority</th>
                                        <th>Status</th>
                                        <th>Date Filed</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    (function () {
        const originalDataTable = window.DataTable;
        window.DataTable = function (selector, options) {
            const el = document.querySelector(selector);
            if (el && el.dataset.dtIgnore === "true") {
                return;
            }
            return new originalDataTable(selector, options);
        };
    })();
    </script>

    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/jquery/jquery.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/js/sb-admin-2.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/datatables/datatables.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/js/head-employees.js"></script>

    <script>
    $(document).on('click', '.viewEmployeeTicketsBtn', function () {
        const employeeId = $(this).data('employee-id');
        const name = $(this).data('name');

        $('#ticketEmployeeName').val(name);
        $('#ticketEmployeeId').val(employeeId);

        $('#employeeTicketsTable').DataTable({
            destroy: true,
            ajax: {
                url: "<?= htmlspecialchars($base) ?>/head/employee/tickets",
                data: { employee_id: employeeId },
                dataSrc: 'data'
            },
            columns: [
                { data: 'ticket_number' },
                { data: 'category' },
                { data: 'priority' },
                { data: 'status' },
                { data: 'date_filed' }
            ]
        });

        $('#employeeTicketsModal').modal('show');
    });

    $(document).on('click', '.viewEmployeeAssetsBtn', function () {
        const employeeId = $(this).data('employee-id');
        const name = $(this).data('name');

        $('#assetEmployeeName').val(name);
        $('#assetEmployeeId').val(employeeId);

        $('#employeeAssetsTable').DataTable({
            destroy: true,
            ajax: {
                url: "<?= htmlspecialchars($base) ?>/head/employee/assets",
                data: { employee_id: employeeId },
                dataSrc: 'data'
            },
            columns: [
                { data: 'assetNumber' },
                { data: 'groupName' },
                { data: 'description' },
                { data: 'itemInfo' },
                { data: 'serialNumber' },
                {
                    data: null,
                    orderable: false,
                    render: function (row) {
                        return '<button class="btn btn-sm btn-view-asset-tickets viewAssetTicketsBtn"' +
                            ' data-inventory-id="' + row.inventory_id + '"' +
                            ' data-iteminfo="' + $('<span>').text(row.itemInfo || '').html() + '"' +
                            ' data-assetnumber="' + $('<span>').text(row.assetNumber || '').html() + '">' +
                            '<i class="fas fa-ticket-alt mr-1"></i> View Tickets</button>';
                    }
                }
            ]
        });

        $('#employeeAssetsModal').modal('show');
    });

    $(document).on('click', '.viewAssetTicketsBtn', function () {
        const inventoryId = $(this).data('inventory-id');

        $('#assetTicketItemInfo').val($(this).data('iteminfo'));
        $('#assetTicketAssetNumber').val($(this).data('assetnumber'));

        $('#assetTicketsTable').DataTable({
            destroy: true,
            ajax: {
                url: "<?= htmlspecialchars($base) ?>/head/employee/assets/tickets",
                data: { inventory_id: inventoryId },
                dataSrc: 'data'
            },
            columns: [
                { data: 'ticket_number' },
                { data: 'category' },
                { data: 'priority' },
                { data: 'status' },
                { data: 'date_filed' }
            ]
        });

        $('#assetTicketsModal').modal('show');
    });
    </script>

</body>
</html>
