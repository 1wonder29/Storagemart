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

    <title>Storage Mart | IT  Tickets - Tables</title>

    <!-- Custom fonts for this template -->
    <link href="<?= htmlspecialchars($base)?>/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="<?= htmlspecialchars($base)?>/assets/css/storagemart.css" rel="stylesheet">
        <link rel="icon" href="<?= htmlspecialchars($base)?>/assets/img/favicon.ico" type="image/x-icon">

    <!-- Custom styles for this page -->
    <link href="<?= htmlspecialchars($base)?>/assets/vendor/datatables/datatables.min.css" rel="stylesheet">

</head>

<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper">
    <?php 
    $activePage = 'tickets';
    require_once __DIR__ . '/../../partials/it/sidebar_topbar.php';?>
        <!-- End of Sidebar -->
                <div class="container-fluid">
                    <h1 class="h3 mb-2 text-gray-800">Tables</h1>
                    <p class="mb-4">DataTables used below. For more: <a target="_blank" href="https://datatables.net">DataTables docs</a>.</p>
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">List of Pending Tickets</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <!-- expose current employee id to JS via data attribute -->
                                <table class="table table-bordered" id="IT-TicketDatables" data-employee-id="<?= (int)$employeeId ?>" width="100%" cellspacing="0">

                                    <thead>
                                        <tr>
                                            <th>Ticket #</th>
                                            <th>Category</th>
                                            <th>Priority</th>
                                            <th>Concern Details</th>
                                            <th>Status</th>
                                            <th>Remarks</th>
                                            <th>Employee Name</th>
                                            <th>Assigned To</th>
                                            <th>Branch</th>
                                            <th>Date Filed</th>
                                            <th>Asset Info</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                        <tbody>
                                        <?php foreach ($tickets as $row): 
                                            $isAssignedToMe = (
                                                $row['assigned_to'] !== null &&
                                                (int)$row['assigned_to'] === (int)$employeeId
                                            );
                                        ?>
                                            <tr>
                                                <td><?= htmlspecialchars($row['ticket_number']) ?></td>
                                                <td><?= htmlspecialchars($row['category']) ?></td>
                                                <td><?= htmlspecialchars($row['priority']) ?></td>
                                                <td><?= htmlspecialchars($row['concern_details']) ?></td>
                                                <td><?= htmlspecialchars($row['status']) ?></td>
                                                <td><?= htmlspecialchars($row['remarks']) ?></td>
                                                <td><?= htmlspecialchars($row['employee_name']) ?></td>
                                                <td>
                                                    <?= htmlspecialchars($row['assigned_to_name'] ?? 'Unassigned') ?>
                                                    <?php if ($isAssignedToMe): ?>
                                                        <span class="badge badge-success ml-1">Mine</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= htmlspecialchars($row['branchName']) ?></td>
                                                <td><?= htmlspecialchars($row['date_filed']) ?></td>
                                                <td><?= htmlspecialchars($row['asset_info']) ?></td>
                                                <td>
                                                    <?php if ($isAssignedToMe): ?>
                                                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown<?= $row['ticket_id'] ?>" role="button"
                                                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                            <span class="mr-2 d-none d-lg-inline text-gray-600">Action</span>
                                                        </a>
                                                        <div class="dropdown-menu dropdown-menu-right shadow" aria-labelledby="userDropdown<?= $row['ticket_id'] ?>">
                                                            <a href="#" class="dropdown-item openModalBtn" data-action="Resolve" data-ticket-id="<?= $row['ticket_id']; ?>" data-assigned="<?= $row['assigned_to']; ?>">
                                                                <i class="fas fa-check fa-sm fa-fw mr-2 text-black-400"></i> Resolved
                                                            </a>
                                                            <a href="#" class="dropdown-item openModalBtn" data-action="On Hold" data-ticket-id="<?= $row['ticket_id']; ?>" data-assigned="<?= $row['assigned_to']; ?>">
                                                                <i class="fas fa-pause fa-sm fa-fw mr-2 text-black-400"></i> On Hold
                                                            </a>
                                                            <a href="#" class="dropdown-item openModalBtn" data-action="Unresolved" data-ticket-id="<?= $row['ticket_id']; ?>" data-assigned="<?= $row['assigned_to']; ?>">
                                                                <i class="fas fa-times fa-sm fa-fw mr-2 text-black-400"></i> Unresolved
                                                            </a>
                                                        </div>
                                                    <?php else: ?>
                                                        <span class="text-muted">Not assigned to you</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                        </tbody>

                                </table>
                            </div>
                        </div>
                    </div>

                </div> <!-- /.container-fluid -->
            </div> <!-- End of Main Content -->
        </div> <!-- End of Content Wrapper -->
    </div> <!-- End of Page Wrapper -->

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top"><i class="fas fa-angle-up"></i></a>

    <!-- Logout Modal-->
    <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document"><div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Ready to Leave?</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">Select "Logout" below if you are ready to end your current session.</div>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                <a class="btn btn-primary" href="<?= htmlspecialchars($base) ?>/logout">Logout</a>
            </div>
        </div></div>
    </div>

    <!-- ✅ Ticket Modal (unchanged fields) -->
    <div class="modal fade" id="ticketModal" tabindex="-1" role="dialog" aria-labelledby="ticketModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg" style="margin-top: 100px;" role="document">
        <form method="POST" action="<?= htmlspecialchars($base) ?>/it/tickets/update">
          <div class="modal-content shadow-lg border-0" style="margin:auto; max-width:850px;">
            <div class="modal-header bg-primary text-white text-center">
              <h5 class="modal-title w-100" id="ticketModalLabel">Update Ticket</h5>
              <button type="button" class="close text-white position-absolute" style="right:15px;" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>

            <div class="modal-body px-4 py-3">
              <input type="hidden" name="ticket_id" id="ticket_id">
              <input type="hidden" name="action" id="ticket_action">

              <h5 class="text-primary text-center mb-3">Technical Details</h5>
              <div class="row mb-3">
                <div class="col-md-6">
                  <label>Technical Purpose</label>
                  <select class="form-control" name="technical_purpose" required>
                    <option value="">-- Select Technical Purpose --</option>
                    <option>Desktop / Laptop Issue</option>
                    <option>Network Issue</option>
                    <option>Software Installation / Activation</option>
                    <option>Application Issue</option>
                    <option>Phone Issue</option>
                    <option>Others</option>
                  </select>
                </div>
                <div class="col-md-6">
                  <label>Status</label>
                  <select class="form-control" name="status" required>
                    <option value="Resolved">Resolved</option>
                    <option value="On Hold">On Hold</option>
                    <option value="Unresolved">Unresolved</option>
                  </select>
                </div>
              </div>

              <div class="row mb-3">
                <div class="col-md-6">
                  <label>Action Taken</label>
                  <textarea class="form-control" name="action_taken" rows="3" required></textarea>
                </div>
                <div class="col-md-6">
                  <label>After Service Note</label>
                  <textarea class="form-control" name="result" rows="3" required></textarea>
                </div>
              </div>

              <div class="form-group">
                <label>Remarks</label>
                <textarea class="form-control" name="remarks" rows="3"></textarea>
              </div>
            </div>

            <div class="modal-footer justify-content-center">
              <button type="button" class="btn btn-secondary px-4" data-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-success px-4" id="modalSubmitBtn">Submit</button>
            </div>
          </div>
        </form>
      </div>
    </div>

    <!-- Scripts -->
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/jquery/jquery.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/js/sb-admin-2.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/datatables/datatables.min.js"></script>

<script>
$(document).ready(function() {
  // Initialize datatable
  $('#IT-TicketDatables').DataTable();

  // Current employee id from data attribute
  const currentEmployeeId = parseInt($('#IT-TicketDatables').data('employee-id'), 10);

  // Handle ticket action modal with guard
  $('.openModalBtn').click(function(e) {
    e.preventDefault();

    const ticketId = $(this).data('ticket-id');
    const action = $(this).data('action'); // Resolve / On Hold / Unresolved
    const assignedTo = $(this).data('assigned'); // may exist

    // Client-side guard: if assigned exists and doesn't match, block
    if (typeof assignedTo !== 'undefined' && parseInt(assignedTo, 10) !== currentEmployeeId) {
      alert('You cannot modify tickets not assigned to you.');
      return;
    }

    const btnColor = action === 'Resolve' ? 'btn-success' :
                     action === 'On Hold' ? 'btn-warning' :
                     'btn-danger';

    $('#ticket_id').val(ticketId);
    $('#ticket_action').val(action);
    $('#ticketModalLabel').text(action + ' Ticket');
    $('#modalSubmitBtn')
      .removeClass('btn-success btn-warning btn-danger')
      .addClass(btnColor)
      .text(action);

    $('#ticketModal').modal('show');
  });
});
</script>
<?php require __DIR__ . '/../../partials/flash_modal.php'; ?>
</body>
</html>
