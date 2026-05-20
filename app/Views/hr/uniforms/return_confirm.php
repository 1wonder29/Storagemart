<?php
$base = rtrim(BASE_URL, '/');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Storage Mart | Confirm Return</title>
    <link href="<?= htmlspecialchars($base) ?>/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900" rel="stylesheet">
    <link rel="icon" href="<?= htmlspecialchars($base) ?>/assets/img/sm_favicon.png" type="image/x-icon">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/storagemart.css" rel="stylesheet">
</head>
<body id="page-top">
    <div id="wrapper">
        <?php
        $activePage = 'uniforms';
        require_once __DIR__ . '/../../partials/hr/sidebar_topbar.php';
        ?>
        <div class="container-fluid">
            <h1 class="h3 mb-4 text-gray-800">Confirm Uniform Return</h1>

            <?php if (empty($assignment)): ?>
                <div class="alert alert-danger">
                    <h5>Assignment Not Found</h5>
                    <p>The uniform assignment could not be found in the system.</p>
                    <a href="<?= htmlspecialchars($base) ?>/hr/employees" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Employees
                    </a>
                </div>
            <?php else: ?>
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Return Details</h6>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <p><strong>Employee:</strong> <?= htmlspecialchars($assignment['employee_name'] ?? '') ?></p>
                                <p><strong>Uniform Type:</strong> <?= htmlspecialchars($assignment['uniform_type'] ?? '') ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Quantity:</strong> <?= (int) ($assignment['quantity_issued'] ?? 0) ?></p>
                                <p><strong>Date Issued:</strong> <?= !empty($assignment['date_issued']) ? htmlspecialchars(date('Y-m-d', strtotime($assignment['date_issued']))) : '-' ?></p>
                            </div>
                        </div>

                        <form method="post" action="<?= htmlspecialchars($base) ?>/hr/uniforms/return/<?= (int) $assignment['assignment_id'] ?>">
                            <div class="form-group mb-3">
                                <label for="condition_upon_return"><strong>Condition Upon Return *</strong></label>
                                <select class="form-control" id="condition_upon_return" name="condition_upon_return" required>
                                    <option value="">-- Select condition --</option>
                                    <option value="GOOD">Good</option>
                                    <option value="FAIR">Fair</option>
                                    <option value="USED">Used</option>
                                    <option value="DAMAGED">Damaged</option>
                                    <option value="LOST">Lost</option>
                                </select>
                            </div>

                            <div class="form-group mb-3">
                                <label for="remarks"><strong>Return Remarks (Optional)</strong></label>
                                <textarea class="form-control" id="remarks" name="remarks" rows="4" placeholder="Add any notes about the return..."></textarea>
                            </div>

                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-check"></i> Confirm Return
                            </button>
                            <a href="<?= htmlspecialchars($base) ?>/hr/employees/detail/<?= (int) ($assignment['employee_id'] ?? 0) ?>" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
