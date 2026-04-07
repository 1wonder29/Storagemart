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
    <link href="<?= htmlspecialchars($base) ?>/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">

    <!-- Custom styles for this template -->
    <!-- Custom styles for this template -->
    <link href="<?= htmlspecialchars($base) ?>/assets/css/storagemart.css" rel="stylesheet">
    <link rel="icon" href="<?= htmlspecialchars($base) ?>/assets/img/favicon.ico" type="image/x-icon">
    <link href="<?= htmlspecialchars($base) ?>/assets/vendor/datatables/datatables.min.css" rel="stylesheet">
</head>

<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper">
            <?php 
            $activePage = 'tickets';
            require_once __DIR__ . '/../../partials/admin/sidebar_topbar.php';?>
                <!-- End of Topbar -->

                <!-- Begin Page Content -->
                <div class="container-fluid">

                    <!-- Page Heading -->
                    <h1 class="h3 mb-2 text-gray-800">Tables</h1>
                    <p class="mb-4">DataTables is a third party plugin that is used to generate the demo table below.
                        For more information about DataTables, please visit the <a target="_blank"
                            href="https://datatables.net">official DataTables documentation</a>.</p>

                    <!-- Main conctent -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">List of Tickets</h6>
                        </div>
                        <div class="d-flex flex-column align-items-end" style="gap: 10px; margin-right: 40px; margin-top: 40px;">
                            <a href="<?= htmlspecialchars($base) ?>/admin/tickets/add" class="btn btn-primary" style="width:160px;"><i class="fas fa-plus"></i> Add Ticket</a>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="logsTicket" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>Ticket #</th>
                                            <th>Employee</th>
                                            <th>Branch</th>
                                            <th>Category</th>
                                            <th>Priority</th>
                                            <th>Status</th>
                                            <th>Date Filed</th>
                                            <th>Assigned To</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($tickets as $row): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($row['ticket_number']) ?></td>
                                                <td><?= htmlspecialchars($row['employee_name']) ?></td>
                                                <td><?= htmlspecialchars($row['branchName']) ?></td>
                                                <td><?= htmlspecialchars($row['category']) ?></td>
                                                <td><?= htmlspecialchars($row['priority']) ?></td>
                                                <td><?= htmlspecialchars($row['status']) ?></td>
                                                <td><?= htmlspecialchars($row['date_filed']) ?></td>
                                                <td><?= htmlspecialchars($row['assigned_to_name'] ?: 'Unassigned') ?></td>
                                                <td>
                                                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown<?= $row['ticket_id'] ?>" role="button"
                                                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                        <span class="mr-2 d-none d-lg-inline text-gray-600">Action</span>
                                                    </a>
                                                    <div class="dropdown-menu dropdown-menu-right shadow" aria-labelledby="userDropdown<?= $row['ticket_id'] ?>">
                                                        <a href="#" class="dropdown-item viewBtn" data-action="View"
                                                        data-ticketid="<?= $row['ticket_id'] ?>"
                                                        data-ticketnum="<?= htmlspecialchars($row['ticket_number']) ?>"
                                                        data-employee="<?= htmlspecialchars($row['employee_name']) ?>"
                                                        data-branch="<?= htmlspecialchars($row['branchName']) ?>"
                                                        data-priority="<?= htmlspecialchars($row['priority']) ?>"
                                                        data-status="<?= htmlspecialchars($row['status']) ?>">
                                                            <i class="fas fa-eye fa-sm fa-fw mr-2 text-black-400"></i> View
                                                        </a>
                                                            <?php if (strcasecmp($row['status'], 'resolved') !== 0): ?>
                                                                <a href="#" class="dropdown-item openUpdateAssignBtn"
                                                                data-ticket-id="<?= $row['ticket_id']; ?>"
                                                                data-assignedid="<?= htmlspecialchars($row['assigned_to_id']) ?>"
                                                                data-status="<?= htmlspecialchars($row['status']) ?>">
                                                                    <i class="fas fa-edit fa-sm fa-fw mr-2 text-black-400"></i>
                                                                    Update Assignment
                                                                </a>
                                                            <?php else: ?>
                                                                <span class="dropdown-item text-muted" style="cursor:not-allowed;">
                                                                    <i class="fas fa-lock fa-sm fa-fw mr-2"></i>
                                                                    Assignment Locked
                                                                </span>
                                                            <?php endif; ?>
                                                    </div>
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
        </div>
        
                </div>
                <!-- /.container-fluid -->

            </div>
            <!-- End of Main Content -->

            <!-- Footer -->
            <!-- End of Footer -->

        </div>
        <!-- End of Content Wrapper -->

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
    <!-- Update Assignment Modal -->
    <div class="modal fade" id="updateAssignModal" tabindex="-1" role="dialog" aria-labelledby="updateAssignLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <form method="POST" action="<?= htmlspecialchars($base) ?>/admin/tickets/update-assignment" id="updateAssignForm">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
            <h5 class="modal-title" id="updateAssignLabel">Update Ticket Assignment</h5>
            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            </div>

            <div class="modal-body">
            <input type="hidden" name="ticket_id" id="update_ticket_id" value="">

            <div class="form-group">
                <label>Assign To (IT Staff)</label>
                    <select class="form-control" name="assigned_to" id="assigned_to_select" required>
                        <option value="">-- Select IT Staff --</option>
                        <?php foreach ($itStaff as $it): ?>
                            <option value="<?= (int)$it['employee_id'] ?>">
                                <?= htmlspecialchars($it['firstname'] . ' ' . $it['lastname']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
            </div>

            <div class="form-group">
                <label>Remarks (optional)</label>
                <textarea class="form-control" name="remarks" rows="3" placeholder="Add a short note (optional)"></textarea>
            </div>

            <div class="alert alert-info small">
                Note: Reassignment is <strong>not allowed</strong> if ticket status = <em>Resolved</em>.
            </div>
            </div>

            <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">Save Assignment</button>
            </div>
        </div>
        </form>
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
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/datatables/datatables.min.js"></script>

    <!-- Page level custom scripts -->
    <script src="<?= htmlspecialchars($base) ?>/assets/js/demo/datatables-demo.js"></script>
    <script>
        window.BASE_URL = "<?= htmlspecialchars($base) ?>";
    </script>
    <script src="<?= htmlspecialchars($base) ?>/assets/js/fetch_ticket_history.js"></script>
    <script src='<?= htmlspecialchars($base) ?>/assets/js/edit_ticket_action.js'></script>  
        <?php require __DIR__ . '/../../partials/flash_modal.php'; ?>                             
</body>

</html>