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

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Storage Mart | Admin Dashboard</title>

    <!-- Custom fonts for this template -->
    <link href="<?= htmlspecialchars($base) ?>/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900" rel="stylesheet">
    <!-- Custom styles for this template -->
    <link href="<?= htmlspecialchars($base) ?>/assets/css/storagemart.css" rel="stylesheet">
</head>

<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper">
    <?php 
    $activePage = 'dashboard';
    require_once __DIR__ . '/../../partials/head/sidebar_topbar.php';?>
                <!-- Page Content -->
            <!-- Page Content -->
            <div class="container-fluid">
                <h1 class="h3 mb-4 text-gray-800">My Dashboard</h1>

                <div class="row">
                    <!-- Assets Card -->
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-primary shadow h-100 py-2">
                            <div class="card-body">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Your Assets</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $totalAssets ?></div>
                            </div>
                        </div>
                    </div>

                    <!-- Total Tickets -->
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-success shadow h-100 py-2">
                            <div class="card-body">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Tickets</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $totalTickets ?></div>
                            </div>
                        </div>
                    </div>

                    <!-- Pending Tickets -->
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-warning shadow h-100 py-2">
                            <div class="card-body">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Pending Tickets</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $pendingTickets ?></div>
                            </div>
                        </div>
                    </div>

                    <!-- Resolved Tickets -->
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-info shadow h-100 py-2">
                            <div class="card-body">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Resolved</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $resolvedTickets ?></div>
                            </div>
                        </div>
                    </div>
                </div>


                <h1 class="h3 mb-4 text-gray-800">My Department</h1>

                <div class="row">
                    <!-- Assets Card -->
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-primary shadow h-100 py-2">
                            <div class="card-body">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Department Assets</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $departmentAssets?></div>
                            </div>
                        </div>
                    </div>

                    <!-- Total Tickets -->
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-success shadow h-100 py-2">
                            <div class="card-body">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Tickets</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $totalDepartmentTickets ?></div>
                            </div>
                        </div>
                    </div>

                    <!-- Pending Tickets -->
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-warning shadow h-100 py-2">
                            <div class="card-body">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Pending Tickets</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $pendingDepartmentTickets ?></div>
                            </div>
                        </div>
                    </div>

                    <!-- Resolved Tickets -->
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-info shadow h-100 py-2">
                            <div class="card-body">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Resolved</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $resolvedDepartmentTickets ?></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                                <h6 class="m-0 font-weight-bold text-primary">Department Tickets</h6>
                                <a href="<?= htmlspecialchars($base) ?>/head/tickets" class="btn btn-primary btn-sm">
                                    View All Tickets
                                </a>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($tickets)): ?>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover" id="departmentTicketsTable" width="100%" cellspacing="0">
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
                                                <td><?= htmlspecialchars($row['ticket_number']) ?></td>
                                                <td><?= htmlspecialchars($row['employee_name']) ?></td>
                                                <td>
                                                    <button class="btn btn-sm btn-primary viewBtn"
                                                        data-ticketid="<?= (int)$row['ticket_id'] ?>"
                                                        data-ticketnum="<?= htmlspecialchars($row['ticket_number']) ?>"
                                                        data-employee="<?= htmlspecialchars($row['employee_name']) ?>"
                                                        data-branch="<?= htmlspecialchars($row['branchName'] ?? '') ?>"
                                                        data-priority="<?= htmlspecialchars($row['priority']) ?>"
                                                        data-status="<?= htmlspecialchars($row['status']) ?>">
                                                        <i class="fas fa-eye"></i> Ticket Details
                                                    </button>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <?php else: ?>
                                <p class="text-muted text-center py-3 mb-0">
                                    <i class="fas fa-inbox"></i> No tickets found for this department.
                                </p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- End Page Content -->
            </div>
        </div>
    </div>
<!--This is flash card -->

    <!-- Scroll to Top -->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>
    <!-- View Ticket Modal -->
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
