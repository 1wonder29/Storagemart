<?php
// Defensive defaults (PREVENT NOTICES)
$totalAssets              = $totalAssets              ?? 0;
$totalTickets             = $totalTickets             ?? 0;
$pendingTickets           = $pendingTickets           ?? 0;
$resolvedTickets          = $resolvedTickets          ?? 0;

$departmentAssets         = $departmentAssets         ?? 0;
$totalDepartmentTickets   = $totalDepartmentTickets   ?? 0;
$pendingDepartmentTickets = $pendingDepartmentTickets ?? 0;
$resolvedDepartmentTickets= $resolvedDepartmentTickets?? 0;

$tickets = $tickets ?? [];

$base = rtrim(BASE_URL, '/');
$headName = htmlspecialchars($loggedFirstname ?? 'Head');
$departmentLabel = htmlspecialchars($department ?? 'Department');
$todayLabel = date('l, F j, Y');
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Storage Mart | Head Dashboard</title>

    <link href="<?= htmlspecialchars($base) ?>/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/storagemart.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/head-dashboard.css" rel="stylesheet">
</head>

<body id="page-top">

    <div id="wrapper">
    <?php
    $activePage = 'dashboard';
    require_once __DIR__ . '/../../partials/head/sidebar_topbar.php';
    ?>

            <div class="container-fluid head-dashboard-page">

                <div class="page-hero">
                    <h1><i class="fas fa-tachometer-alt mr-2"></i>Head Dashboard</h1>
                    <p>Welcome back, <?= $headName ?> — track your personal tickets and monitor <?= $departmentLabel ?> at a glance.</p>
                    <div class="hero-dept"><i class="fas fa-building mr-1"></i><?= $departmentLabel ?></div>
                    <div class="hero-date"><i class="far fa-calendar-alt mr-1"></i><?= $todayLabel ?></div>
                    <div class="quick-nav mt-3">
                        <a href="<?= htmlspecialchars($base) ?>/head/tickets" class="btn btn-sm btn-outline-light">
                            <i class="fas fa-ticket-alt mr-1"></i> View Tickets
                        </a>
                        <a href="<?= htmlspecialchars($base) ?>/head/assets" class="btn btn-sm btn-outline-light">
                            <i class="fas fa-archive mr-1"></i> My Assets
                        </a>
                        <a href="<?= htmlspecialchars($base) ?>/head/employee" class="btn btn-sm btn-outline-light">
                            <i class="fas fa-user-friends mr-1"></i> Employees
                        </a>
                    </div>
                </div>

                <div class="section-heading">
                    <span class="section-icon"><i class="fas fa-user"></i></span>
                    <h2>My Dashboard</h2>
                </div>

                <div class="row dashboard-section">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <a href="<?= htmlspecialchars($base) ?>/head/assets" class="stat-card stat-card-assets">
                            <div class="stat-card-icon"><i class="fas fa-archive"></i></div>
                            <div>
                                <span class="stat-card-label">Your Assets</span>
                                <span class="stat-card-value"><?= (int) $totalAssets ?></span>
                                <span class="stat-card-hint">Assigned to you</span>
                            </div>
                        </a>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <a href="<?= htmlspecialchars($base) ?>/head/tickets" class="stat-card stat-card-tickets">
                            <div class="stat-card-icon"><i class="fas fa-ticket-alt"></i></div>
                            <div>
                                <span class="stat-card-label">Total Tickets</span>
                                <span class="stat-card-value"><?= (int) $totalTickets ?></span>
                                <span class="stat-card-hint">Filed by you</span>
                            </div>
                        </a>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="stat-card stat-card-pending stat-card-static">
                            <div class="stat-card-icon"><i class="fas fa-clock"></i></div>
                            <div>
                                <span class="stat-card-label">Pending Tickets</span>
                                <span class="stat-card-value"><?= (int) $pendingTickets ?></span>
                                <span class="stat-card-hint">Awaiting resolution</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="stat-card stat-card-resolved stat-card-static">
                            <div class="stat-card-icon"><i class="fas fa-check-circle"></i></div>
                            <div>
                                <span class="stat-card-label">Resolved</span>
                                <span class="stat-card-value"><?= (int) $resolvedTickets ?></span>
                                <span class="stat-card-hint">Completed tickets</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="section-heading mt-2">
                    <span class="section-icon dept"><i class="fas fa-users"></i></span>
                    <h2>My Department</h2>
                </div>

                <div class="row dashboard-section">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <a href="<?= htmlspecialchars($base) ?>/head/employee" class="stat-card stat-card-assets">
                            <div class="stat-card-icon"><i class="fas fa-boxes"></i></div>
                            <div>
                                <span class="stat-card-label">Department Assets</span>
                                <span class="stat-card-value"><?= (int) $departmentAssets ?></span>
                                <span class="stat-card-hint">Across all staff</span>
                            </div>
                        </a>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <a href="<?= htmlspecialchars($base) ?>/head/tickets" class="stat-card stat-card-tickets">
                            <div class="stat-card-icon"><i class="fas fa-ticket-alt"></i></div>
                            <div>
                                <span class="stat-card-label">Total Tickets</span>
                                <span class="stat-card-value"><?= (int) $totalDepartmentTickets ?></span>
                                <span class="stat-card-hint">Department-wide</span>
                            </div>
                        </a>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="stat-card stat-card-pending stat-card-static">
                            <div class="stat-card-icon"><i class="fas fa-hourglass-half"></i></div>
                            <div>
                                <span class="stat-card-label">Pending Tickets</span>
                                <span class="stat-card-value"><?= (int) $pendingDepartmentTickets ?></span>
                                <span class="stat-card-hint">Needs attention</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="stat-card stat-card-resolved stat-card-static">
                            <div class="stat-card-icon"><i class="fas fa-check-double"></i></div>
                            <div>
                                <span class="stat-card-label">Resolved</span>
                                <span class="stat-card-value"><?= (int) $resolvedDepartmentTickets ?></span>
                                <span class="stat-card-hint">Closed department tickets</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row dashboard-section">
                    <div class="col-lg-12">
                        <div class="card dash-card shadow mb-4">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center">
                                    <span class="header-icon"><i class="fas fa-list-alt"></i></span>
                                    <h6>Department Tickets</h6>
                                </div>
                                <a href="<?= htmlspecialchars($base) ?>/head/tickets" class="btn btn-primary btn-sm btn-view-all">
                                    <i class="fas fa-external-link-alt mr-1"></i> View All Tickets
                                </a>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($tickets)): ?>
                                <div class="table-responsive">
                                    <table class="table table-hover tickets-table" id="departmentTicketsTable" width="100%" cellspacing="0">
                                        <thead>
                                            <tr>
                                                <th>Ticket #</th>
                                                <th>Employee Name</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($tickets as $row): ?>
                                            <tr>
                                                <td><strong><?= htmlspecialchars($row['ticket_number']) ?></strong></td>
                                                <td><?= htmlspecialchars($row['employee_name']) ?></td>
                                                <td>
                                                    <button class="btn btn-sm btn-primary btn-view-ticket viewBtn"
                                                        data-ticketid="<?= (int)$row['ticket_id'] ?>"
                                                        data-ticketnum="<?= htmlspecialchars($row['ticket_number']) ?>"
                                                        data-employee="<?= htmlspecialchars($row['employee_name']) ?>"
                                                        data-branch="<?= htmlspecialchars($row['branchName'] ?? '') ?>"
                                                        data-priority="<?= htmlspecialchars($row['priority']) ?>"
                                                        data-status="<?= htmlspecialchars($row['status']) ?>">
                                                        <i class="fas fa-eye mr-1"></i> Details
                                                    </button>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <?php else: ?>
                                <div class="empty-state">
                                    <i class="fas fa-inbox"></i>
                                    <p>No tickets found for this department.</p>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
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
                    <h5 class="modal-title" id="viewTicketLabel">Ticket Details</h5>
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
                    <a id="downloadPdfBtn" class="btn btn-success d-none" download>
                        <i class="fas fa-download"></i> Download Technical Report
                    </a>
                    <button class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/jquery/jquery.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/js/sb-admin-2.min.js"></script>

    <script>
    $(document).ready(function () {
        const base = "<?= htmlspecialchars($base) ?>";

        function escapeHtml(text) {
            if (text === null || text === undefined) return "";
            return String(text)
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }

        $('#departmentTicketsTable').on("click", ".viewBtn", function () {
            const id = $(this).data("ticketid");
            const status = $(this).data("status") || "";
            $("#ticket_number").val($(this).data("ticketnum") || "");
            $("#employee").val($(this).data("employee") || "");
            $("#priority").val($(this).data("priority") || "");
            $("#status").val(status);

            if (status.toLowerCase() === 'resolved') {
                $("#downloadPdfBtn")
                    .attr("href", base + "/head/tickets/download-record?id=" + id)
                    .removeClass("d-none");
            } else {
                $("#downloadPdfBtn").addClass("d-none");
            }

            $("#ticketHistoryTable tbody").empty();

            $.getJSON(base + "/head/tickets/history/fetch", { ticket_id: id })
                .done(function (data) {
                    if (Array.isArray(data) && data.length > 0) {
                        data.forEach((row) => {
                            $("#ticketHistoryTable tbody").append(
                                '<tr>' +
                                '<td>' + escapeHtml(row.action_details) + '</td>' +
                                '<td>' + escapeHtml(row.performed_by) + '</td>' +
                                '<td>' + escapeHtml(row.old_status || "") + '</td>' +
                                '<td>' + escapeHtml(row.new_status || "") + '</td>' +
                                '<td>' + escapeHtml(row.date_logged || "") + '</td>' +
                                '</tr>'
                            );
                        });
                    } else {
                        $("#ticketHistoryTable tbody").append(
                            '<tr><td colspan="5" class="text-center">No history found.</td></tr>'
                        );
                    }
                })
                .fail(function () {
                    $("#ticketHistoryTable tbody").append(
                        '<tr><td colspan="5" class="text-center text-danger">Failed to load history.</td></tr>'
                    );
                });

            if (window.TicketComments) {
                TicketComments.load('#viewTicketModal .ticket-comments-section', id);
            }

            $("#viewTicketModal").modal("show");
        });
    });
    </script>
    <script src="<?= htmlspecialchars($base) ?>/assets/js/ticket/ticket_comments.js"></script>

</body>
</html>
