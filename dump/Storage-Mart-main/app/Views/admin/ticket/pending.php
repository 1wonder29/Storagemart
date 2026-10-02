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

    <title>Storage Mart | Admin Pending Tickets - Tables</title>

    <!-- Custom fonts for this template -->
    <link href="<?= htmlspecialchars($base) ?>/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="<?= htmlspecialchars($base) ?>/assets/css/storagemart.css" rel="stylesheet">
    <link rel="icon" href="<?= htmlspecialchars($base) ?>/assets/img/favicon.ico" type="image/x-icon">
    <link href="<?= htmlspecialchars($base) ?>/assets/vendor/datatables/datatables.min.css" rel="stylesheet">
</head>

<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper">
            <?php 
            $activePage = 'pendings';
            require_once __DIR__ . '/../../partials/admin/sidebar_topbar.php';?>

                <!-- Begin Page Content -->
                <div class="container-fluid">

                    <!-- Page Heading -->
                    <h1 class="h3 mb-2 text-gray-800">Tables</h1>
                    <p class="mb-4">DataTables is a third party plugin that is used to generate the demo table below.
                        For more information about DataTables, please visit the <a target="_blank"
                            href="https://datatables.net">official DataTables documentation</a>.</p>
                <?php require __DIR__ . '/../../partials/flash_modal.php'; ?>  
                    <!-- DataTales Example -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">List of Pending Tickets</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="pendings" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>Ticket ID</th>
                                            <th>Employee Name</th>
                                            <th>Department</th>
                                            <th>Branch</th>
											<th>Asset Info</th>
											<th>Category</th>
                                            <th>Priority</th>
                                            <th>Concern Details</th>
                                            <th>Date Filed</th>
                                            <th>Status</th>
											<th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($tickets as $row): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($row['ticket_number']); ?></td>
                                            <td><?= htmlspecialchars($row['fullname']); ?></td>
                                            <td><?= htmlspecialchars($row['department']); ?></td>
                                            <td><?= htmlspecialchars($row['branchName']); ?></td>
                                            <td><?= htmlspecialchars($row['asset_info']); ?></td>
                                            <td><?= htmlspecialchars($row['category']); ?></td>
                                            <td><?= htmlspecialchars($row['priority']); ?></td>
                                            <td><?= htmlspecialchars($row['concern_details']); ?></td>
                                            <td><?= htmlspecialchars($row['date_filed']); ?></td>
                                            <td><?= htmlspecialchars($row['status']); ?></td>
                                            <td>
                                                <div class="dropdown">
                                                    <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" id="actionDropdown<?= $row['ticket_id'] ?>" data-toggle="dropdown" aria-expanded="false">
                                                        Actions
                                                    </button>
                                                    <div class="dropdown-menu dropdown-menu-right shadow" aria-labelledby="actionDropdown<?= $row['ticket_id'] ?>">
                                                            <button class="dropdown-item text-success" data-toggle="modal" data-target="#approveAssignModal" data-ticket-id="<?= (int)$row['ticket_id'] ?>">Approve & Assign</button>
                                                            <button class="dropdown-item text-danger" data-toggle="modal" data-target="#declineModal" data-ticket-id="<?= (int)$row['ticket_id'] ?>">Decline</button>
                                                        </div>
                                                    </div>
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
                    <a class="btn btn-primary" href="<?= htmlspecialchars($base)?>/logout">Logout</a>
                </div>
            </div>
        </div>
    </div>
    <!-- Approve modal -->
    <div class="modal fade" id="approveAssignModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="<?= htmlspecialchars($base) ?>/admin/tickets/approve-assign">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
        <input type="hidden" name="ticket_id" id="approve_ticket_id" value="">
        <div class="modal-content">
        <div class="modal-header bg-success text-white"><h5>Approve & Assign</h5></div>
        <div class="modal-body">
            <div class="form-group">
            <label>Select IT Staff</label>
            <select class="form-control" name="assigned_to" required>
                <option value="">-- Select IT Staff --</option>
                <?php foreach ($itStaff as $s): ?>
                <option value="<?= (int)$s['employee_id'] ?>"><?= htmlspecialchars($s['firstname'].' '.$s['lastname']) ?></option>
                <?php endforeach; ?>
            </select>
            </div>
            <div class="form-group">
            <label>Remarks (optional)</label>
            <textarea name="remarks" class="form-control" rows="3"></textarea>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-success">Approve & Assign</button>
        </div>
        </div>
        </form>
    </div>
    </div>

    <!-- Decline modal -->
    <div class="modal fade" id="declineModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="<?= htmlspecialchars($base) ?>/admin/tickets/decline">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
        <input type="hidden" name="ticket_id" id="decline_ticket_id" value="">
        <input type="hidden" name="action" value="Decline">
        <div class="modal-content">
        <div class="modal-header"><h5>Decline Ticket</h5></div>
        <div class="modal-body">
            <div class="form-group">
            <label>Reason for Decline</label>
            <textarea name="decline_reason" class="form-control" rows="4" required></textarea>
            </div>
            <div class="form-group">
            <label>Remarks (optional)</label>
            <textarea name="remarks" class="form-control" rows="3"></textarea>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-danger">Confirm Decline</button>
        </div>
        </div>
        </form>
    </div>
    </div>

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
    <?php require __DIR__ . '/../../partials/flash_modal.php'; ?>  
    <!-- put this AFTER your jQuery / bootstrap / datatables script tags -->
<script>
  // CDN fallback: if local jQuery didn't load, load from Google CDN
  (function() {
    if (typeof window.jQuery === 'undefined') {
      console.warn('Local jQuery not loaded — attempting CDN fallback...');
      var s = document.createElement('script');
      s.src = "https://code.jquery.com/jquery-3.6.0.min.js";
      s.integrity = "sha256-/xUj+3OJ+Y3yYtD2y0h3gZ+Yh3yQ9Z3Zq2n6jv9gX0E="; // optional
      s.crossOrigin = "anonymous";
      s.onload = function() { initPendings(); };
      s.onerror = function() {
        console.error('Failed to load jQuery from CDN. Check network or script paths.');
      };
      document.head.appendChild(s);
    } else {
      // jQuery already present
      initPendings();
    }

    function initPendings() {
      // ensure $ exists
      if (typeof window.$ === 'undefined') {
        console.error('jQuery is still undefined. Aborting DataTable init.');
        return;
      }

      $(function(){
        // NOTE: your table id is "pendings" — use that selector:
        if ($('#pendings').length) {
          try {
            $('#pendings').DataTable();
          } catch (e) {
            console.error('DataTables init error:', e);
          }
        } else {
          console.warn('Table #pendings not found — skipping DataTable init.');
        }

        // Wire modals (populate hidden inputs)
        $('#approveAssignModal').on('show.bs.modal', function (e) {
          var button = $(e.relatedTarget);
          var ticketId = button.data('ticket-id') || '';
          $(this).find('#approve_ticket_id').val(ticketId);
        });

        $('#declineModal').on('show.bs.modal', function (e) {
          var button = $(e.relatedTarget);
          var ticketId = button.data('ticket-id') || '';
          $(this).find('#decline_ticket_id').val(ticketId);
        });
      });
    }
  })();
</script>

<script>
  $(function(){
    $('#pendingsTable').DataTable();

    $('#approveAssignModal').on('show.bs.modal', function (e) {
      var button = $(e.relatedTarget);
      var ticketId = button.data('ticket-id');
      $(this).find('#approve_ticket_id').val(ticketId);
    });

    $('#declineModal').on('show.bs.modal', function (e) {
      var button = $(e.relatedTarget);
      var ticketId = button.data('ticket-id');
      $(this).find('#decline_ticket_id').val(ticketId);
    });
  });
</script>
</body>

</html>