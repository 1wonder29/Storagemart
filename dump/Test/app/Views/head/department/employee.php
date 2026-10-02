<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Storage Mart Employee - Tables</title>
    <!-- Custom fonts for this template -->
    <link href="<?= htmlspecialchars($base)?>/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="<?= htmlspecialchars($base) ?>/assets/css/StorageMart.css" rel="stylesheet">
    <link rel="icon" href="<?= htmlspecialchars($base) ?>/assets/img/favicon.ico" type="image/x-icon">
    <link href="<?= htmlspecialchars($base) ?>/assets/vendor/datatables/dataTables.min.css" rel="stylesheet">
</head>

<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper">
            <?php 
            $activePage = 'employee';
            require_once __DIR__ . '/../../partials/head/sidebar_topbar.php';?>
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
                            <h6 class="m-0 font-weight-bold text-primary">List of Employee</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="employee-table" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>Employee ID</th>
											<th>Account ID</th>
                                            <th>Last name</th>
                                            <th>First name</th>
                                            <th>Middle name</th>
											<th>Department</th>
                                            <th>Position</th>
											<th>Branch</th>
                                            <th>Email</th>
                                            <th>Created by</th>
                                            <th>Date created</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($employees) && is_array($employees)): ?>
                                            <?php foreach ($employees as $row): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($row['employee_id']) ?></td>
                                            <td><?= htmlspecialchars($row['account_id']) ?></td>
                                            <td><?= htmlspecialchars($row['lastname']) ?></td>
                                            <td><?= htmlspecialchars($row['firstname']) ?></td>
                                            <td><?= htmlspecialchars($row['middlename']) ?></td>
                                            <td><?= htmlspecialchars($row['department']) ?></td>
                                            <td><?= htmlspecialchars($row['position']) ?></td>
                                            <td><?= htmlspecialchars($row['branchName']) ?></td>
                                            <td><?= htmlspecialchars($row['email']) ?></td>
                                            <td><?= htmlspecialchars($row['createdby']) ?></td>
                                            <td><?= htmlspecialchars($row['datecreated']) ?></td>
                                                <td>
                                                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                                                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                        <span class="mr-2 d-none d-lg-inline text-gray-600 ">
                                                            Action</span>
                                                    </a>
                                                    <div class="dropdown-menu dropdown-menu-right shadow">
                                                        <a href="#"
                                                        class="dropdown-item viewEmployeeTicketsBtn"
                                                        data-employee-id="<?= (int)$row['employee_id'] ?>"
                                                        data-name="<?= htmlspecialchars($row['firstname'].' '.$row['lastname']) ?>">
                                                            <i class="fas fa-ticket-alt fa-sm fa-fw mr-2"></i>
                                                            View Tickets
                                                        </a>
                                                        <a href="#"
                                                        class="dropdown-item viewEmployeeAssetsBtn"
                                                        data-employee-id="<?= (int)$row['employee_id'] ?>"
                                                        data-name="<?= htmlspecialchars($row['firstname'].' '.$row['lastname']) ?>">
                                                            <i class="fas fa-box fa-sm fa-fw mr-2"></i>
                                                            View Assets
                                                        </a>
                                                    </div>
                                                </td>
                                        </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
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
<!-- Employee Tickets Modal -->
<div class="modal fade" id="employeeTicketsModal" tabindex="-1">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title">
          Tickets 
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
      </div>

      <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label>Employee</label>
                        <input type="text" id="ticketEmployeeName" class="form-control" readonly>
                    </div>
                    <div class="col-md-6">
                        <label>Employee ID</label>
                        <input type="text" id="ticketEmployeeId" class="form-control" readonly>
                    </div>
                </div>
        <div class="table-responsive">
          <table class="table table-bordered" id="employeeTicketsTable" width="100%">
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
<!-- Employee Assets Modal -->
<div class="modal fade" id="employeeAssetsModal" tabindex="-1">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title">
          Assets
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
      </div>

      <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label>Employee</label>
                        <input type="text" id="assetEmployeeName" class="form-control" readonly>
                    </div>
                    <div class="col-md-6">
                        <label>Employee ID</label>
                        <input type="text" id="assetEmployeeId" class="form-control" readonly>
                    </div>
                </div>
        <div class="table-responsive">
          <table class="table table-bordered" id="employeeAssetsTable" width="100%">
            <thead>
              <tr>
                <th>Asset #</th>
                <th>Model</th>
                <th>Description</th>
                <th>Item Info</th>
                <th>Serial Number</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Asset Tickets Modal -->
<div class="modal fade" id="assetTicketsModal" tabindex="-1">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title">Asset Ticket History</h5>
        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
      </div>

      <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label>Item Info</label>
                        <input type="text" id="assetTicketItemInfo" class="form-control" readonly>
                    </div>
                    <div class="col-md-6">
                        <label>Employee ID</label>
                        <input type="text" id="assetTicketAssetNumber" class="form-control" readonly>
                    </div>
                </div>
        <div class="table-responsive">
          <table class="table table-bordered" id="assetTicketsTable" width="100%">
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

    <!-- Bootstrap core JavaScript-->
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/jquery/jquery.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Core plugin JavaScript-->
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/jquery-easing/jquery.easing.min.js"></script>
    <!-- Custom scripts for all pages-->
    <script src="<?= htmlspecialchars($base) ?>/assets/js/sb-admin-2.min.js"></script>

    <!-- Page level plugins -->
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/datatables/dataTables.min.js"></script>

    <!-- Page level custom scripts -->
    <script src="<?= htmlspecialchars($base) ?>/assets/js/demo/datatables-demo.js"></script>
<script>
$(document).on('click', '.viewEmployeeTicketsBtn', function () {
    const employeeId = $(this).data('employee-id');
    const name = $(this).data('name');

    $('#ticketEmployeeName').val(name);
    $('#ticketEmployeeId').val(employeeId);

    $('#employeeTicketsTable').DataTable({
        destroy: true,
        ajax: {
            url: "<?= $base ?>/head/employee/tickets",
            data: { employee_id: employeeId },
            dataSrc: ''
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
</script>
<script>
$(document).on('click', '.viewEmployeeAssetsBtn', function () {
    const employeeId = $(this).data('employee-id');
    const name = $(this).data('name');

    $('#assetEmployeeName').val(name);
    $('#assetEmployeeId').val(employeeId);

    $('#employeeAssetsTable').DataTable({
        destroy: true,
        ajax: {
            url: "<?= $base ?>/head/employee/assets",
            data: { employee_id: employeeId },
            dataSrc: ''
        },
        columns: [
            { data: 'assetNumber' },
            { data: 'groupName' },
            { data: 'description' },
            { data: 'itemInfo' },
            { data: 'serialNumber' },
            {
                data: null,
                render: function (row) {
                    return `
                        <button class="btn btn-sm btn-primary viewAssetTicketsBtn"
                            data-inventory-id="${row.inventory_id}"
                            data-iteminfo="${row.itemInfo}"
                            data-assetnumber="${row.assetNumber}">
                            View Tickets
                        </button>
                    `;
                }
            }
        ]
    });

    $('#employeeAssetsModal').modal('show');
});
</script>

<script>
$(document).on('click', '.viewAssetTicketsBtn', function () {
    const inventoryId = $(this).data('inventory-id');

    $('#assetTicketItemInfo').val($(this).data('iteminfo'));
    $('#assetTicketAssetNumber').val($(this).data('assetnumber'));

    $('#assetTicketsTable').DataTable({
        destroy: true,
        ajax: {
            url: "<?= $base ?>/head/employee/assets/tickets",
            data: { inventory_id: inventoryId },
            dataSrc: ''
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