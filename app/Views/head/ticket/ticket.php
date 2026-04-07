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

    <title>Storage Mart Tickets - Tables</title>

    <!-- Custom fonts for this template -->
    <link href="<?= htmlspecialchars($base)?>/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">

    <!-- Custom styles for this template -->
        <!-- Custom styles for this template -->
        <link href="<?= htmlspecialchars($base) ?>/assets/css/storagemart.css" rel="stylesheet">
        <link rel="icon" href="<?= htmlspecialchars($base) ?>/assets/img/favicon.ico" type="image/x-icon">
        <!-- Custom styles for this page -->
        <link href="<?= htmlspecialchars($base) ?>/assets/vendor/datatables/datatables.min.css" rel="stylesheet">

</head>

<body id="page-top">

    <div id="wrapper">
        <?php 
        $activePage = 'tickets';
        require_once __DIR__ . '/../../partials/head/sidebar_topbar.php';?>

            <div class="container-fluid">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">List of Tickets</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="ticketsTable" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>Ticket #</th>
                                        <th>Concern Details</th>
                                        <th>Branch</th>
                                        <th>Category</th>
                                        <th>Priority</th>
                                        <th>Status</th>
                                        <th>Date Filed</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tfoot>

                                </tfoot>
                                <tbody>
                                    <?php foreach($tickets as $row): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($row['ticket_number']) ?></td>
                                            <td><?= htmlspecialchars($row['concern_details']) ?></td>
                                            <td><?= htmlspecialchars($row['branchName']) ?></td>
                                            <td><?= htmlspecialchars($row['category']) ?></td>
                                            <td><?= htmlspecialchars($row['priority']) ?></td>
                                            <td><?= htmlspecialchars($row['status']) ?></td>
                                            <td><?= htmlspecialchars($row['date_filed']) ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-primary viewBtn" 
                                                    data-ticketid="<?= $row['ticket_id'] ?>" 
                                                    data-ticketnum="<?= htmlspecialchars($row['ticket_number']) ?>"
                                                    data-employee="<?= htmlspecialchars($row['employee_name']) ?>"
                                                    data-branch="<?= htmlspecialchars($row['branchName']) ?>"
                                                    data-priority="<?= htmlspecialchars($row['priority']) ?>"
                                                    data-status="<?= htmlspecialchars($row['status']) ?>">
                                                    View
                                                </button>
                                                <?php if (strtolower($row['status']) === 'resolved'): ?>
                                                <button class="btn btn-sm btn-warning rateBtn"
                                                    data-ticketid="<?= $row['ticket_id'] ?>">
                                                    <i class="fas fa-star"></i> Rate
                                                </button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

    </div>

    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <!-- View Ticket Modal -->
    <div class="modal fade" id="viewTicketModal" tabindex="-1" aria-labelledby="viewTicketLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="viewTicketLabel">Ticket History</h5>
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
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/jquery/jquery.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/js/sb-admin-2.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/datatables/jquery.datatables.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/datatables/datatables.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/js/demo/datatables-demo.js"></script>
    <script>const base = "<?= htmlspecialchars($base) ?>";</script>
    <script src="<?= htmlspecialchars($base) ?>/assets/js/ticket/fetch_ticket_history.js"></script>

    <script>
    $(document).ready(function () {

        function escapeHtml(text) {
            if (text === null || text === undefined) return "";
            return String(text)
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }

        // View Ticket Modal
        $('#ticketsTable').on("click", ".viewBtn", function () {
            const id = $(this).data("ticketid");
            $("#ticket_number").val($(this).data("ticketnum") || "");
            $("#employee").val($(this).data("employee") || "");
            $("#priority").val($(this).data("priority") || "");
            $("#status").val($(this).data("status") || "");
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

            $("#viewTicketModal").modal("show");
        });

        // Rate button click
        $('#ticketsTable').on('click', '.rateBtn', function () {
            const ticketId = $(this).data('ticketid');
            $('#rateTicketModalBody').html('<div class="text-center py-3"><i class="fas fa-spinner fa-spin fa-2x"></i></div>');
            $('#rateTicketModal').modal('show');
            $.get(base + '/head/tickets/rate', { id: ticketId })
                .done(function (html) {
                    $('#rateTicketModalBody').html(html);
                })
                .fail(function () {
                    $('#rateTicketModalBody').html('<div class="alert alert-danger">Failed to load. Please try again.</div>');
                });
        });

        // Submit rating form
        $(document).on('submit', '#rateTicketForm', function (e) {
            e.preventDefault();
            const form = $(this);
            const submitBtn = form.find('button[type="submit"]');
            submitBtn.prop('disabled', true).text('Submitting...');

            $.post(base + '/head/tickets/rate', form.serialize())
                .done(function () {
                    $('#rateTicketModalBody').html(
                        '<div class="alert alert-success text-center">' +
                        '<i class="fas fa-check-circle fa-2x mb-2"></i><br>' +
                        'Thank you for rating IT support! 🎉' +
                        '</div>'
                    );
                    setTimeout(function () {
                        $('#rateTicketModal').modal('hide');
                        location.reload();
                    }, 1500);
                })
                .fail(function () {
                    submitBtn.prop('disabled', false).text('Submit Rating');
                    form.prepend('<div class="alert alert-danger">Something went wrong. Please try again.</div>');
                });
        });

    });
    </script>

    <?php require __DIR__ . '/../../partials/flash_modal.php'; ?>

</body>

</html>