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

    <title>Storage Mart Tickets - List of Tickets</title>

    <!-- Custom fonts for this template -->
    <link href="<?= htmlspecialchars($base)?>/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">

    <!-- Custom styles for this template -->
        <!-- Custom styles for this template -->
        <link href="<?= htmlspecialchars($base) ?>/assets/css/storagemart.css" rel="stylesheet">
        <link rel="icon" href="<?= htmlspecialchars($base) ?>/assets/img/favicon.png" type="image/png">
        <!-- Custom styles for this page -->
        <link href="<?= htmlspecialchars($base) ?>/assets/vendor/datatables/datatables.min.css" rel="stylesheet">

</head>


<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper">
        <?php 
        $activePage = 'tickets';
        require_once __DIR__ . '/../../partials/hr/sidebar_topbar.php';?>

            <!-- Begin Page Content -->
            <div class="container-fluid">

                <!-- Page Heading -->
                <h1 class="h3 mb-2 text-gray-800">List of Tickets</h1>


                <!-- Main content -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-primary">List of Tickets</h6>
                        <a href="<?= htmlspecialchars($base) ?>/hr/tickets/create" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Add Ticket
                        </a>
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
                                    <tr>

                                    </tr>
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
                                                <button class="btn btn-sm btn-info uploadBtn"
                                                    data-ticketid="<?= $row['ticket_id'] ?>"
                                                    data-ticketnum="<?= htmlspecialchars($row['ticket_number']) ?>">
                                                    <i class="fas fa-upload"></i> Upload
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
            <!-- /.container-fluid -->

    </div>
    <!-- End of Page Wrapper -->

    <!-- Scroll to Top Button-->
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
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-dismiss="modal">Close</button>
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
    <script src="<?= htmlspecialchars($base) ?>/assets/js/sb-admin-2.min.js"></script>

    <!-- Page level plugins -->
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/datatables/datatables.min.js"></script>

    <!-- Page level custom scripts -->
    <script>
        // Set global BASE_URL for all scripts
        window.BASE_URL = "<?= htmlspecialchars($base) ?>";
    </script>

    <script>
$(document).ready(function() {
    const base = "<?= htmlspecialchars($base) ?>";

    // Initialize Tickets DataTable
    var ticketsTable = $("#ticketsTable").DataTable({
        responsive: true,
        pageLength: 10,
        "order": [[6, 'desc']]
    });

    // View Ticket Modal Handler
    $('#ticketsTable').on("click", ".viewBtn", function() {
        const id = $(this).data("ticketid");
        const status = $(this).data("status") || "";

        // Fill ticket info
        $("#ticket_number").val($(this).data("ticketnum") || "");
        $("#employee").val($(this).data("employee") || "");
        $("#priority").val($(this).data("priority") || "");
        $("#status").val(status);

        // Clear previous history records
        $("#ticketHistoryTable tbody").empty();

        // Fetch history records
        if (id) {
            $.ajax({
                url: base + '/hr/tickets/fetch-history/' + id,
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success && response.data) {
                        response.data.forEach(function(record) {
                            const row = `
                                <tr>
                                    <td>${record.action_taken || '-'}</td>
                                    <td>${record.technician_name || '-'}</td>
                                    <td>${record.old_status || '-'}</td>
                                    <td>${record.new_status || '-'}</td>
                                    <td>${record.date || '-'}</td>
                                </tr>
                            `;
                            $("#ticketHistoryTable tbody").append(row);
                        });
                    } else {
                        $("#ticketHistoryTable tbody").html('<tr><td colspan="5" class="text-center">No history records found</td></tr>');
                    }
                },
                error: function() {
                    $("#ticketHistoryTable tbody").html('<tr><td colspan="5" class="text-center text-danger">Error loading history</td></tr>');
                }
            });
        }

        // Show modal
        $('#viewTicketModal').modal('show');
    });
});
    </script>

</body>

</html>
