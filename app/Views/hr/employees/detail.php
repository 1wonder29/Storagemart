<?php
$base = rtrim(BASE_URL, '/');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Storage Mart | Employee Detail</title>
    <link href="<?= htmlspecialchars($base) ?>/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900" rel="stylesheet">
    <link rel="icon" href="<?= htmlspecialchars($base) ?>/assets/img/sm_favicon.png" type="image/x-icon">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/storagemart.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/hr-dashboard.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/hr-pages.css" rel="stylesheet">
</head>
<body id="page-top">
    <div id="wrapper">
    <?php 
    $activePage = 'employees';
    require_once dirname(dirname(__DIR__)) . '/partials/hr/sidebar_topbar.php';?>
        <div class="container-fluid hr-dashboard-page hr-employee-detail-page">
            <div class="page-hero">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <h1><i class="fas fa-id-badge mr-2"></i><?= htmlspecialchars(($employee['firstname'] ?? '') . ' ' . ($employee['lastname'] ?? '')) ?></h1>
                        <p>Employee profile, assets, and uniform accountability overview.</p>
                    </div>
                    <div class="col-lg-4 mt-3 mt-lg-0 text-lg-right">
                        <a href="<?= htmlspecialchars($base) ?>/hr/employees" class="btn btn-light btn-sm">
                            <i class="fas fa-arrow-left"></i> Back to Employees
                        </a>
                    </div>
                </div>
            </div>

            <?php if (!empty($_SESSION['successMessage'])): ?>
                <div class="alert alert-success alert-dismissible fade show alert-modern" role="alert">
                    <?= htmlspecialchars($_SESSION['successMessage']) ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <?php unset($_SESSION['successMessage']); ?>
            <?php endif; ?>

            <?php if (!empty($_SESSION['errorMessage'])): ?>
                <div class="alert alert-danger alert-dismissible fade show alert-modern" role="alert">
                    <?= htmlspecialchars($_SESSION['errorMessage']) ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <?php unset($_SESSION['errorMessage']); ?>
            <?php endif; ?>

            <!-- Employee Info -->
            <div class="card shadow mb-4 detail-card">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-user mr-1"></i> Employee Information</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="detail-grid-item">
                                <span class="detail-label">Name</span>
                                <div class="detail-value"><?= htmlspecialchars(($employee['firstname'] ?? '') . ' ' . ($employee['lastname'] ?? '')) ?></div>
                            </div>
                            <div class="detail-grid-item">
                                <span class="detail-label">Position</span>
                                <div class="detail-value"><?= htmlspecialchars($employee['position'] ?? '') ?></div>
                            </div>
                            <div class="detail-grid-item">
                                <span class="detail-label">Department</span>
                                <div class="detail-value"><?= htmlspecialchars($employee['department'] ?? '') ?></div>
                            </div>
                            <div class="detail-grid-item">
                                <span class="detail-label">Branch</span>
                                <div class="detail-value"><?= htmlspecialchars($employee['branchName'] ?? 'N/A') ?></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="detail-grid-item">
                                <span class="detail-label">Email</span>
                                <div class="detail-value"><?= htmlspecialchars($employee['email'] ?? '') ?></div>
                            </div>
                            <div class="detail-grid-item">
                                <span class="detail-label">User Type</span>
                                <div class="detail-value"><span class="badge bg-primary"><?= htmlspecialchars($employee['usertype'] ?? '') ?></span></div>
                            </div>
                            <div class="detail-grid-item">
                                <span class="detail-label">Status</span>
                                <div class="detail-value"><span class="badge bg-<?= ($employee['status'] === 'ACTIVE') ? 'success' : 'danger' ?>"><?= htmlspecialchars($employee['status'] ?? '') ?></span></div>
                            </div>
                            <div class="detail-grid-item">
                                <span class="detail-label">Date Created</span>
                                <div class="detail-value"><?= !empty($employee['datecreated']) ? date('M d, Y', strtotime($employee['datecreated'])) : '-' ?></div>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="actions-inline">
                    <a href="<?= htmlspecialchars($base) ?>/hr/employees/accountability/<?= $employee['employee_id'] ?>" class="btn btn-success">
                        <i class="fas fa-download"></i> Download Accountability Form
                    </a>
                    </div>
                </div>
            </div>

            <!-- Accountability Form Preview -->
            <div class="card shadow mb-4 data-card" id="accountability-form">
                <div class="card-header py-3 d-flex align-items-center justify-content-between flex-wrap">
                    <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-file-signature mr-1"></i> Accountability Form</h6>
                    <span class="badge badge-light"><?= count($accountabilityAssets ?? []) + count($accountabilityUniforms ?? []) ?> item(s)</span>
                </div>
                <div class="card-body">
                    <?php if (empty($accountabilityAssets) && empty($accountabilityUniforms)): ?>
                        <p class="text-muted mb-0">No accountability records yet.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th>Issued</th>
                                        <th>Returned</th>
                                        <th>Status</th>
                                        <th>Remarks</th>
                                        <th class="text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach (($accountabilityAssets ?? []) as $row): ?>
                                        <?php
                                        $isReturned = strtoupper((string) ($row['accountability_status'] ?? '')) === 'RETURNED';
                                        $itemLabel = trim((string) ($row['itemInfo'] ?? $row['assetNumber'] ?? 'Asset'));
                                        ?>
                                        <tr>
                                            <td>
                                                <div class="font-weight-bold"><?= htmlspecialchars($itemLabel) ?></div>
                                                <small class="text-muted"><?= htmlspecialchars((string) ($row['assetNumber'] ?? '')) ?></small>
                                            </td>
                                            <td><?= !empty($row['dateIssued']) ? date('M d, Y', strtotime((string) $row['dateIssued'])) : '—' ?></td>
                                            <td><?= !empty($row['dateReturned']) ? date('M d, Y', strtotime((string) $row['dateReturned'])) : '—' ?></td>
                                            <td>
                                                <span class="badge bg-<?= $isReturned ? 'secondary' : 'info' ?>">
                                                    <?= htmlspecialchars((string) ($row['accountability_status'] ?? 'ASSIGNED')) ?>
                                                </span>
                                            </td>
                                            <td class="accountability-remarks-cell"><?= htmlspecialchars((string) ($row['remarks'] ?? '')) ?></td>
                                            <td class="text-right">
                                                <?php if ($isReturned && !empty($row['assignment_id'])): ?>
                                                    <button type="button"
                                                            class="btn btn-sm btn-outline-primary btn-edit-remarks"
                                                            data-assignment-id="<?= (int) $row['assignment_id'] ?>"
                                                            data-remarks="<?= htmlspecialchars((string) ($row['remarks'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                                        <i class="fas fa-edit"></i> Edit Remarks
                                                    </button>
                                                <?php else: ?>
                                                    <span class="text-muted">—</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>

                                    <?php foreach (($accountabilityUniforms ?? []) as $row): ?>
                                        <?php
                                        $isReturned = strtoupper((string) ($row['accountability_status'] ?? '')) === 'RETURNED';
                                        $itemLabel = trim((string) (($row['uniform_type'] ?? 'Uniform') . ' ' . ($row['size'] ?? '') . ' ' . ($row['color'] ?? '')));
                                        ?>
                                        <tr>
                                            <td>
                                                <div class="font-weight-bold"><?= htmlspecialchars($itemLabel) ?></div>
                                                <small class="text-muted">Uniform</small>
                                            </td>
                                            <td><?= !empty($row['date_issued']) ? date('M d, Y', strtotime((string) $row['date_issued'])) : '—' ?></td>
                                            <td><?= !empty($row['date_returned']) ? date('M d, Y', strtotime((string) $row['date_returned'])) : '—' ?></td>
                                            <td>
                                                <span class="badge bg-<?= $isReturned ? 'secondary' : 'info' ?>">
                                                    <?= htmlspecialchars((string) ($row['accountability_status'] ?? 'ASSIGNED')) ?>
                                                </span>
                                            </td>
                                            <td><?= htmlspecialchars((string) ($row['remarks'] ?? '')) ?></td>
                                            <td class="text-right"><span class="text-muted">—</span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- IT Assets -->
            <div class="card shadow mb-4 data-card">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-laptop mr-1"></i> Assigned IT Assets</h6>
                </div>
                <div class="card-body">
                    <?php if (empty($assets)): ?>
                        <p class="text-muted">No assets assigned.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Asset Code</th>
                                        <th>Item Info</th>
                                        <th>Serial #</th>
                                        <th>Category</th>
                                        <th>Issued Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($assets as $asset): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($asset['assetNumber'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($asset['itemInfo'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($asset['serialNumber'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($asset['categoryName'] ?? 'N/A') ?></td>
                                            <td><?= $asset['dateIssued'] ? date('M d, Y', strtotime($asset['dateIssued'])) : 'N/A' ?></td>
                                            <td><span class="badge bg-info"><?= htmlspecialchars($asset['asset_status'] ?? '') ?></span></td>
                                            <td>
                                                <a href="<?= htmlspecialchars($base) ?>/hr/assets/transfer?inventory_id=<?= (int) $asset['inventory_id'] ?>&return_employee_id=<?= (int) $employee['employee_id'] ?>"
                                                   class="btn btn-sm btn-primary">
                                                    <i class="fas fa-exchange-alt"></i> Transfer
                                                </a>
                                                <?php if (strtoupper((string) ($asset['asset_status'] ?? '')) === 'ASSIGNED'): ?>
                                                <button type="button"
                                                        class="btn btn-sm btn-warning btn-return-asset"
                                                        data-inventory-id="<?= (int) ($asset['inventory_id'] ?? 0) ?>"
                                                        data-asset-number="<?= htmlspecialchars((string) ($asset['assetNumber'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                                    <i class="fas fa-undo"></i> Return
                                                </button>
                                                <?php endif; ?>
                                                <a href="<?= htmlspecialchars($base) ?>/hr/assets/transfer-history?inventory_id=<?= (int) $asset['inventory_id'] ?>&return_employee_id=<?= (int) $employee['employee_id'] ?>"
                                                   class="btn btn-sm btn-secondary">
                                                    <i class="fas fa-history"></i> History
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Current Uniforms -->
            <div class="card shadow mb-4 data-card">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-tshirt mr-1"></i> Current Uniforms</h6>
                </div>
                <div class="card-body">
                    <?php if (empty($uniforms)): ?>
                        <p class="text-muted">No uniforms assigned.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Type</th>
                                        <th>Size</th>
                                        <th>Quantity</th>
                                        <th>Issued</th>
                                        <th>Condition</th>
                                        <th>Return</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($uniforms as $unif): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($unif['uniform_type'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($unif['size'] ?? '') ?></td>
                                            <td><?= $unif['quantity_issued'] ?></td>
                                            <td><?= date('M d, Y', strtotime($unif['date_issued'])) ?></td>
                                            <td><?= htmlspecialchars($unif['condition_upon_issue'] ?? '-') ?></td>
                                            <td>
                                                <a href="<?= htmlspecialchars($base) ?>/hr/uniforms/return_confirm/<?= $unif['assignment_id'] ?>"
                                                   class="btn btn-sm btn-primary">Return</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Uniform History -->
            <?php if (!empty($uniformHistory)): ?>
            <div class="card shadow mb-4 data-card">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-history mr-1"></i> Uniform History (All)</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Size/Color</th>
                                    <th>Issued</th>
                                    <th>Returned</th>
                                    <th>Condition Issue</th>
                                    <th>Condition Return</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($uniformHistory as $hist): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($hist['uniform_type'] ?? '') ?></td>
                                        <td><?= htmlspecialchars(($hist['size'] ?? '') . ' / ' . ($hist['color'] ?? '')) ?></td>
                                        <td><?= date('M d, Y', strtotime($hist['date_issued'])) ?></td>
                                        <td><?= $hist['date_returned'] ? date('M d, Y', strtotime($hist['date_returned'])) : '-' ?></td>
                                        <td><?= htmlspecialchars($hist['condition_upon_issue'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($hist['condition_upon_return'] ?? '-') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endif; ?>

        </div>
    </div>
    </div>

    <div class="modal fade" id="returnAssetModal" tabindex="-1" role="dialog" aria-labelledby="returnAssetModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form method="POST" action="<?= htmlspecialchars($base) ?>/hr/assets/return">
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
                        <input type="hidden" name="employee_id" value="<?= (int) ($employee['employee_id'] ?? 0) ?>">
                        <input type="hidden" name="inventory_id" id="returnInventoryId" value="">
                        <p class="mb-3">Returning asset <strong id="returnAssetNumber"></strong>. The item will be marked as <strong>Returned</strong> and the accountability form will update automatically.</p>
                        <div class="form-group mb-0">
                            <label for="returnRemarks">Additional Remarks <span class="text-muted">(optional)</span></label>
                            <textarea class="form-control" id="returnRemarks" name="remarks" rows="3"
                                      placeholder="Optional notes. System remarks are generated automatically."></textarea>
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

    <div class="modal fade" id="editRemarksModal" tabindex="-1" role="dialog" aria-labelledby="editRemarksModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form method="POST" action="<?= htmlspecialchars($base) ?>/hr/assets/accountability-remarks">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editRemarksModalLabel">Edit Accountability Remarks</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                        <input type="hidden" name="employee_id" value="<?= (int) ($employee['employee_id'] ?? 0) ?>">
                        <input type="hidden" name="assignment_id" id="editAssignmentId" value="">
                        <div class="form-group mb-0">
                            <label for="editRemarksText">Remarks</label>
                            <textarea class="form-control" id="editRemarksText" name="remarks" rows="4" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Remarks</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/jquery/jquery.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/js/storagemart.min.js"></script>
    <script>
    (function () {
        $(document).on('click', '.btn-return-asset', function () {
            $('#returnInventoryId').val($(this).data('inventory-id') || '');
            $('#returnAssetNumber').text($(this).data('asset-number') || '');
            $('#returnRemarks').val('');
            $('#returnAssetModal').modal('show');
        });

        $(document).on('click', '.btn-edit-remarks', function () {
            $('#editAssignmentId').val($(this).data('assignment-id') || '');
            $('#editRemarksText').val($(this).data('remarks') || '');
            $('#editRemarksModal').modal('show');
        });
    })();
    </script>
</body>
</html>
