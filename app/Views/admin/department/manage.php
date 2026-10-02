<?php
$base = rtrim(BASE_URL, '/');
$departments = $departments ?? [];
$totalDepartments = count($departments);
?>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Storage Mart | Manage Departments</title>
    <link href="<?= htmlspecialchars($base) ?>/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/storagemart.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/admin-users.css?v=20261002c" rel="stylesheet">
    <link rel="icon" href="<?= htmlspecialchars($base) ?>/assets/img/favicon.ico" type="image/x-icon">
</head>

<body id="page-top">

    <div id="wrapper">
        <?php
        $activePage = 'users';
        $userSubPage = 'department';
        require_once __DIR__ . '/../../partials/admin/sidebar_topbar.php';
        ?>

        <div class="container-fluid admin-users-page department-page">

            <div class="page-hero hero-accounts">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <h1><i class="fas fa-building mr-2"></i>Manage Departments</h1>
                        <p>Add, rename, or remove department options used on Add/Edit Account.</p>
                    </div>
                    <div class="col-lg-4">
                        <div class="hero-stat mt-3 mt-lg-0">
                            <div class="stat-value"><?= (int) $totalDepartments ?></div>
                            <div class="stat-label">Departments</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card form-card shadow mb-4">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">Add Department</h6>
                </div>
                <div class="card-body">
                    <form action="<?= htmlspecialchars($base) ?>/admin/department" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="code" class="form-label">Code <span class="text-danger">*</span></label>
                                <input type="text" name="code" class="form-control" id="code" placeholder="e.g. Construction" required>
                                <small class="form-text text-muted">The value saved on each employee. Renaming it later moves those employees automatically.</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="label" class="form-label">Display Label <span class="text-danger">*</span></label>
                                <input type="text" name="label" class="form-control" id="label" placeholder="e.g. Construction Department" required>
                                <small class="form-text text-muted">What people see in dropdowns and lists.</small>
                            </div>
                        </div>
                        <div class="department-form-actions">
                            <button type="submit" class="btn btn-primary" name="btnSubmit">
                                <i class="fas fa-plus mr-1"></i> Add Department
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card data-list-card shadow mb-4">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-list-ul mr-1"></i> Department List
                    </h6>
                    <span class="badge badge-info"><?= (int) $totalDepartments ?> department<?= $totalDepartments === 1 ? '' : 's' ?></span>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($departments)): ?>
                        <div class="empty-state">
                            <i class="fas fa-building d-block"></i>
                            No departments found. Add your first one above.
                        </div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>Display Label</th>
                                    <th>Employees</th>
                                    <th class="text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($departments as $dept):
                                    $empCount = (int) ($dept['employee_count'] ?? 0);
                                ?>
                                    <tr>
                                        <td><span class="asset-number"><?= htmlspecialchars((string) ($dept['code'] ?? '')) ?></span></td>
                                        <td><?= htmlspecialchars((string) ($dept['label'] ?? '')) ?></td>
                                        <td>
                                            <span class="badge <?= $empCount > 0 ? 'badge-primary' : 'badge-light' ?>"><?= $empCount ?></span>
                                        </td>
                                        <td class="text-right">
                                            <div class="action-btn-group">
                                                <a href="<?= htmlspecialchars($base) ?>/admin/department/update?department_id=<?= (int) ($dept['department_id'] ?? 0) ?>"
                                                   class="btn btn-sm btn-outline-primary" title="Edit department">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button type="button" class="btn btn-sm btn-outline-danger js-delete-department" title="Delete department"
                                                        data-id="<?= (int) ($dept['department_id'] ?? 0) ?>"
                                                        data-code="<?= htmlspecialchars((string) ($dept['code'] ?? '')) ?>"
                                                        data-label="<?= htmlspecialchars((string) ($dept['label'] ?? '')) ?>"
                                                        data-count="<?= $empCount ?>">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>

    <div class="modal fade" id="deleteDepartmentModal" tabindex="-1" role="dialog" aria-labelledby="deleteDepartmentTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <form class="modal-content" method="POST" action="<?= htmlspecialchars($base) ?>/admin/department/delete" id="deleteDepartmentForm">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                <input type="hidden" name="department_id" id="deleteDepartmentId" value="">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="deleteDepartmentTitle"><i class="fas fa-exclamation-triangle mr-1"></i> Delete Department</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <p class="mb-2">You are about to delete <strong id="deleteDepartmentName"></strong>.</p>
                    <div id="deleteDepartmentEmpty" class="alert alert-light border mb-0">
                        No employees are assigned to this department. It can be deleted safely.
                    </div>
                    <div id="deleteDepartmentHasEmployees" class="d-none">
                        <div class="alert alert-warning">
                            <strong id="deleteDepartmentCount"></strong> employee(s) are in this department.
                            Employees are <strong>not deleted</strong> — they will be moved to the department you choose below.
                        </div>
                        <label for="deleteDepartmentMoveTo" class="form-label">Move employees to <span class="text-danger">*</span></label>
                        <select class="form-control" name="move_to" id="deleteDepartmentMoveTo">
                            <option value="">-- Select Department --</option>
                            <?php foreach ($departments as $dept): ?>
                                <option value="<?= htmlspecialchars((string) $dept['code']) ?>"><?= htmlspecialchars((string) $dept['label']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger"><i class="fas fa-trash mr-1"></i> <span id="deleteDepartmentSubmitText">Delete</span></button>
                </div>
            </form>
        </div>
    </div>

    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/jquery/jquery.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/js/sb-admin-2.min.js"></script>
    <script>
    (function ($) {
        var $modal = $('#deleteDepartmentModal');
        var $moveTo = $('#deleteDepartmentMoveTo');

        $('.js-delete-department').on('click', function () {
            var $btn = $(this);
            var code = String($btn.data('code'));
            var count = parseInt($btn.data('count'), 10) || 0;

            $('#deleteDepartmentId').val($btn.data('id'));
            $('#deleteDepartmentName').text($btn.data('label') + ' (' + code + ')');
            $moveTo.val('');
            $moveTo.find('option').each(function () {
                $(this).prop('hidden', this.value === code).prop('disabled', this.value === code);
            });

            if (count > 0) {
                $('#deleteDepartmentCount').text(count);
                $('#deleteDepartmentHasEmployees').removeClass('d-none');
                $('#deleteDepartmentEmpty').addClass('d-none');
                $moveTo.prop('required', true);
                $('#deleteDepartmentSubmitText').text('Move & Delete');
            } else {
                $('#deleteDepartmentHasEmployees').addClass('d-none');
                $('#deleteDepartmentEmpty').removeClass('d-none');
                $moveTo.prop('required', false);
                $('#deleteDepartmentSubmitText').text('Delete');
            }
            $modal.modal('show');
        });
    })(jQuery);
    </script>
    <?php require __DIR__ . '/../../partials/flash_modal.php'; ?>
</body>

</html>
