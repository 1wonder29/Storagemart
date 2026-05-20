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
        <link rel="icon" href="<?= htmlspecialchars($base) ?>/assets/img/favicon.ico" type="image/x-icon">
        <!-- Custom styles for this page -->
        <link href="<?= htmlspecialchars($base) ?>/assets/vendor/datatables/datatables.min.css" rel="stylesheet">

</head>


<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper">
        <?php 
        $activePage = 'tickets';
        require_once __DIR__ . '/../../partials/employee/sidebar_topbar.php';?>

            <!-- Begin Page Content -->
            <div class="container-fluid">

                <!-- Page Heading -->
                <h1 class="h3 mb-2 text-gray-800">List of Tickets</h1>


                <!-- Main content -->
                <div class="card shadow mb-4">
                    <div class="d-flex flex-column align-items-end" style="gap: 10px; margin-right: 40px; margin-top: 40px;">
                        <a href="<?= htmlspecialchars($base) ?>/employee/assets/file_ticket" class="btn btn-primary" style="width:160px;">
                            <i class="fas fa-plus"></i> Add Ticket
                        </a>
                    </div>
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
                    <button class="btn btn-success" id="downloadRecordBtn" style="display: none;">
                        <i class="fas fa-download"></i> Download Technical Record
                    </button>
                    <button class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Rate Ticket Modal is in sidebar_topbar.php -->
    <div class="modal fade d-none" id="rateTicketModalDuplicate" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-warning text-white">
                    <h5 class="modal-title" id="rateTicketLabel">
                        <i class="fas fa-star"></i> Rate IT Support
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body" id="rateModalContent">
                    <form id="rateTicketForm">
                        <input type="hidden" name="ticket_id" id="rateTicketId" value="">
                        <div class="form-group">
                            <label>Rating</label>
                            <select name="rating" class="form-control" required>
                                <option value="">Select rating</option>
                                <option value="5">★★★★★</option>
                                <option value="4">★★★★</option>
                                <option value="3">★★★</option>
                                <option value="2">★★</option>
                                <option value="1">★</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Comment (optional)</label>
                            <textarea name="comment" class="form-control"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block">Submit Rating</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Upload Technical Report Modal -->
    <div class="modal fade" id="uploadReportModal" tabindex="-1" aria-labelledby="uploadReportLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="uploadReportLabel">
                        <i class="fas fa-upload"></i> Upload Signed Technical Report
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <strong>Accepted Formats:</strong> PDF, DOCX, JPG, PNG (Max 10MB)
                    </div>
                    <form id="uploadReportForm">
                        <input type="hidden" name="ticket_id" id="uploadTicketId" value="">
                        
                        <div class="form-group">
                            <label for="reportFile">Select File</label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="reportFile" name="report_file" accept=".pdf,.docx,.jpg,.png" required>
                                <label class="custom-file-label" for="reportFile">Choose file...</label>
                            </div>
                            <small class="form-text text-muted">
                                File size must not exceed 10MB
                            </small>
                        </div>

                        <div id="uploadMessage"></div>
                        
                        <button type="submit" class="btn btn-primary btn-block" id="uploadSubmitBtn">
                            <i class="fas fa-upload"></i> Upload Report
                        </button>
                    </form>
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

    <script>
    // Set global BASE_URL for all scripts including fetch_ticket_history.js
    window.BASE_URL = "<?= htmlspecialchars($base) ?>";
    </script>

    <script>
$(document).ready(function () {
    // Global variable for base URL - inside ready to ensure jQuery is loaded
    const base = "<?= htmlspecialchars($base) ?>";

    // ==============================
    // Initialize Tickets DataTable
    // ==============================
    var ticketsTable = $("#ticketsTable").DataTable({
        responsive: true,
        pageLength: 10
    });

    // ==============================
    // View Ticket Modal
    // ==============================
    $('#ticketsTable').on("click", ".viewBtn", function () {
        const id = $(this).data("ticketid");
        const status = $(this).data("status") || "";

        // Fill main ticket info
        $("#ticket_number").val($(this).data("ticketnum") || "");
        $("#employee").val($(this).data("employee") || "");
        $("#priority").val($(this).data("priority") || "");
        $("#status").val(status);

        // Show/hide download technical record button based on status
        if (status.toLowerCase() === 'resolved') {
            $("#downloadRecordBtn")
                .data("ticketid", id)
                .show();
        } else {
            $("#downloadRecordBtn").hide();
        }

        // Clear history table
        $("#ticketHistoryTable tbody").empty();

        // Fetch history via AJAX
        $.getJSON(base + "/employee/tickets/history/fetch", { ticket_id: id })
            .done(function (data) {
                if (Array.isArray(data) && data.length > 0) {
                    data.forEach((row) => {
                        $("#ticketHistoryTable tbody").append(`
                            <tr>
                                <td>${escapeHtml(row.action_details)}</td>
                                <td>${escapeHtml(row.performed_by)}</td>
                                <td>${escapeHtml(row.old_status || "")}</td>
                                <td>${escapeHtml(row.new_status || "")}</td>
                                <td>${escapeHtml(row.date_logged || "")}</td>
                            </tr>
                        `);
                    });
                } else {
                    $("#ticketHistoryTable tbody").append(
                        `<tr><td colspan="5" class="text-center">No history found.</td></tr>`
                    );
                }
            })
            .fail(function () {
                $("#ticketHistoryTable tbody").append(
                    `<tr><td colspan="5" class="text-center text-danger">Failed to load history.</td></tr>`
                );
            });

        $("#viewTicketModal").modal("show");
    });

    // ==============================
    // Download Technical Record
    // ==============================
    $(document).on('click', '#downloadRecordBtn', function () {
        const ticketId = $(this).data("ticketid");
        if (!ticketId) {
            alert('Invalid ticket ID');
            return;
        }

        // Trigger download by navigating to the endpoint
        window.location.href = base + '/employee/tickets/download-record?id=' + ticketId;
    });

    // ==============================
    // Upload Technical Report
    // ==============================
    $('#ticketsTable').on('click', '.uploadBtn', function () {
        const ticketId = $(this).data('ticketid');
        const ticketNum = $(this).data('ticketnum');
        
        $('#uploadTicketId').val(ticketId);
        $('#uploadReportForm')[0].reset();
        $('#uploadMessage').html('');
        $('.custom-file-label').text('Choose file...');
        
        $('#uploadReportModal').modal('show');
    });

    // Handle file input label update
    $(document).on('change', '#reportFile', function () {
        const fileName = $(this).val().split('\\').pop() || 'Choose file...';
        $(this).siblings('.custom-file-label').text(fileName);
    });

    // Submit upload form
    $(document).on('submit', '#uploadReportForm', function (e) {
        e.preventDefault();
        
        const form = $(this)[0];
        const ticketId = $('#uploadTicketId').val();
        const fileInput = $('#reportFile')[0];
        const submitBtn = $('#uploadSubmitBtn');
        
        if (!fileInput.files.length) {
            $('#uploadMessage').html('<div class="alert alert-warning">Please select a file</div>');
            return;
        }

        const file = fileInput.files[0];
        const maxSize = 10 * 1024 * 1024; // 10MB
        
        if (file.size > maxSize) {
            $('#uploadMessage').html('<div class="alert alert-danger">File size exceeds 10MB limit</div>');
            return;
        }

        const formData = new FormData();
        formData.append('ticket_id', ticketId);
        formData.append('report_file', file);

        console.log('Upload starting - File:', file.name, 'Size:', file.size, 'Type:', file.type);
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Uploading...');
        $('#uploadMessage').html('');

        $.ajax({
            url: base + '/employee/tickets/upload-report',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function (response) {
                console.log('Upload success:', response);
                if (response.success) {
                    $('#uploadMessage').html(
                        '<div class="alert alert-success">' +
                        '<i class="fas fa-check-circle"></i> ' + response.message +
                        '</div>'
                    );
                    setTimeout(function () {
                        $('#uploadReportModal').modal('hide');
                        location.reload();
                    }, 1500);
                } else {
                    console.error('Upload failed:', response.message);
                    $('#uploadMessage').html(
                        '<div class="alert alert-danger">' +
                        '<i class="fas fa-exclamation-circle"></i> ' + response.message +
                        '</div>'
                    );
                    submitBtn.prop('disabled', false).html('<i class="fas fa-upload"></i> Upload Report');
                }
            },
            error: function (xhr, status, error) {
                console.error('AJAX Error:', {status: status, error: error, xhr: xhr});
                console.error('Response text:', xhr.responseText);
                let errorMsg = 'An error occurred during upload';
                
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.message) {
                        errorMsg = response.message;
                    }
                } catch (e) {
                    // Response is not JSON, use status text
                    if (xhr.statusText) {
                        errorMsg = 'Error (' + xhr.status + '): ' + xhr.statusText;
                    }
                }
                
                $('#uploadMessage').html(
                    '<div class="alert alert-danger">' +
                    '<i class="fas fa-exclamation-circle"></i> ' + errorMsg +
                    '</div>'
                );
                submitBtn.prop('disabled', false).html('<i class="fas fa-upload"></i> Upload Report');
            }
        });
    });

    // ==============================
    // Rate Ticket Modal
    // ==============================
    $('#ticketsTable').on('click', '.rateBtn', function () {
        const ticketId = $(this).data('ticketid');
        $('#rateTicketModalBody').html('<div class="text-center py-3"><i class="fas fa-spinner fa-spin fa-2x"></i></div>');
        $('#rateTicketModal').modal('show');
        $.get(base + '/employee/tickets/rate', { id: ticketId })
            .done(function (html) {
                $('#rateTicketModalBody').html(html);
            })
            .fail(function () {
                $('#rateTicketModalBody').html('<div class="alert alert-danger">Failed to load. Please try again.</div>');
            });
    });

    // Submit rating form via AJAX
    $(document).on('submit', '#rateTicketForm', function (e) {
        e.preventDefault();
        const form = $(this);
        const submitBtn = form.find('button[type="submit"]');
        submitBtn.prop('disabled', true).text('Submitting...');

        $.post(base + '/employee/tickets/rate', form.serialize())
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

    // ==============================
    // Helper: escape HTML
    // ==============================
    function escapeHtml(text) {
        if (text === null || text === undefined) return "";
        return String(text)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

});
</script>

    <?php require __DIR__ . '/../../partials/flash_modal.php'; ?>

</body>

</html>