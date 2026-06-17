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

        <title>Storage Mart | Assets</title>

        <!-- Custom fonts for this template -->
        <link href="<?= htmlspecialchars($base) ?>/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
        <link
            href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
            rel="stylesheet">

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
            $activePage = 'users';
            $userSubPage = 'accounts';
            require_once __DIR__ . '/../../partials/admin/sidebar_topbar.php';?>
                    <!-- End of Topbar -->

                    <!-- Begin Page Content -->
                    <div class="container-fluid">

                        <!-- Page Heading -->
                        <h1 class="h3 mb-2 text-gray-800">Tables</h1>


                        <!-- Main conctent -->
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">My Assets</h6>
                            </div>
                            <div class="d-flex flex-column align-items-end" style="gap: 10px; margin-right: 40px; margin-top: 40px;">
                                <a href="<?= htmlspecialchars($base) ?>/assets/generatePDF/generate_accountability.php?employee_id=<?= $employee_id ?>" 
                                class="btn btn-primary" style="width:260px;">
                                <i class="fas fa-file-word"></i> Generate Accountability Form
                                </a>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered" id="assetUser" width="100%" cellspacing="0">
                                        <thead>
                                            <tr>
                                                <th>Asset Number</th>
                                                <th>Model</th>
                                                <th>Description</th>
                                                <th>Item Info</th>
                                                <th>Serial Number</th>
                                                <th class="text-right">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <?php if (!empty($assets) && is_array($assets)): ?>
                                            <?php foreach ($assets as $row): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($row['assetNumber']);?> </td>
                                                <td><?= htmlspecialchars($row['groupName']);?> </td>
                                                <td><?= htmlspecialchars($row['description']);?> </td>
                                                <td><?= htmlspecialchars($row['itemInfo']); ?></td>
                                                <td><?= htmlspecialchars($row['serialNumber']);?> </td>
                                                <td class="text-right">
                                                    <button type="button"
                                                            class="btn btn-sm btn-warning btn-return-asset"
                                                            title="Return Asset"
                                                            data-inventory-id="<?= (int) ($row['inventory_id'] ?? 0) ?>"
                                                            data-asset-number="<?= htmlspecialchars((string) ($row['assetNumber'] ?? '')) ?>">
                                                        <i class="fas fa-undo mr-1"></i> Return
                                                    </button>
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

        <div class="modal fade" id="returnAssetModal" tabindex="-1" role="dialog" aria-labelledby="returnAssetModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <form method="POST" action="<?= htmlspecialchars($base) ?>/admin/assets/return">
                        <div class="modal-header bg-warning text-white">
                            <h5 class="modal-title" id="returnAssetModalLabel">
                                <i class="fas fa-undo mr-1"></i> Return Asset
                            </h5>
                            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                            <input type="hidden" name="employee_id" value="<?= (int) ($employee_id ?? 0) ?>">
                            <input type="hidden" name="inventory_id" id="returnInventoryId" value="">
                            <p class="mb-3">You are returning asset <strong id="returnAssetNumber"></strong> from this employee. The item will be marked as <strong>unassigned</strong> and can then be marked defective from Asset Inventory if needed.</p>
                            <div class="form-group mb-0">
                                <label for="returnReason">Reason <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="returnReason" name="reason" rows="4" required
                                          placeholder="Describe the condition or reason for return"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-undo mr-1"></i> Return Asset
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

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
        (function ($) {
            $(document).on('click', '.btn-return-asset', function () {
                $('#returnInventoryId').val($(this).data('inventory-id'));
                $('#returnAssetNumber').text($(this).data('asset-number') || '');
                $('#returnReason').val('');
                $('#returnAssetModal').modal('show');
            });
        })(jQuery);
        </script>
        <?php require __DIR__ . '/../../partials/flash_modal.php'; ?>
    </body>

    </html>