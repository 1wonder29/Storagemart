<?php
$base = rtrim(BASE_URL, '/');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Storage Mart | Confirm Return</title>
    <link href="<?= htmlspecialchars($base) ?>/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900" rel="stylesheet">
    <link rel="icon" href="<?= htmlspecialchars($base) ?>/assets/img/sm_favicon.png" type="image/x-icon">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/storagemart.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/hr-uniforms.css" rel="stylesheet">
</head>
<body id="page-top">
    <div id="wrapper">
        <?php
        $activePage = 'uniforms';
        require_once __DIR__ . '/../../partials/hr/sidebar_topbar.php';
        ?>
        <div class="container-fluid hr-uniform-page">
            <div class="page-hero">
                <h1><i class="fas fa-undo-alt mr-2"></i>Confirm Uniform Return</h1>
                <p>Split returned quantity by condition to record mixed returns accurately.</p>
            </div>

            <?php if (empty($assignment)): ?>
                <div class="alert alert-danger">
                    <h5>Assignment Not Found</h5>
                    <p>The uniform assignment could not be found in the system.</p>
                    <a href="<?= htmlspecialchars($base) ?>/hr/employees" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Employees
                    </a>
                </div>
            <?php else: ?>
                <div class="card shadow mb-4 uniform-card">
                    <div class="card-header py-3">
                        <h6 class="m-0"><i class="fas fa-clipboard-check"></i>Return Details</h6>
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

                        <form method="post" action="<?= htmlspecialchars($base) ?>/hr/uniforms/return/<?= (int) $assignment['assignment_id'] ?>" id="returnConfirmForm">
                            <?php $issuedQty = (int) ($assignment['quantity_issued'] ?? 0); ?>
                            <input type="hidden" id="issued_quantity" value="<?= $issuedQty ?>">

                            <div class="form-group mb-3">
                                <label><strong>Returned Quantity Breakdown *</strong></label>
                                <small class="form-text text-muted mb-2">Enter how many are Good/Damaged/Lost. Total can be any value from <strong>1</strong> to <strong><?= $issuedQty ?></strong>.</small>
                                <div class="row align-items-end">
                                    <div class="col-md-4 mb-2">
                                        <label class="small text-muted mb-1" for="return_qty_good">Good</label>
                                        <input type="number" class="form-control return-qty-input" id="return_qty_good" name="return_qty_good" min="0" value="0">
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <label class="small text-muted mb-1" for="return_qty_damaged">Damaged</label>
                                        <input type="number" class="form-control return-qty-input" id="return_qty_damaged" name="return_qty_damaged" min="0" value="0">
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <label class="small text-muted mb-1" for="return_qty_lost">Lost</label>
                                        <input type="number" class="form-control return-qty-input" id="return_qty_lost" name="return_qty_lost" min="0" value="0">
                                    </div>
                                </div>
                                <div id="returnQtySummary" class="small font-weight-bold mt-2 text-muted">
                                    Total entered: 0 / <?= $issuedQty ?>
                                </div>
                            </div>

                            <div class="form-group mb-3">
                                <label for="remarks"><strong>Return Remarks (Optional)</strong></label>
                                <textarea class="form-control" id="remarks" name="remarks" rows="4" placeholder="Add any notes about the return..."></textarea>
                            </div>

                            <button type="submit" class="btn btn-primary" id="confirmReturnBtn">
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
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/jquery/jquery.min.js"></script>
    <script>
    (function () {
        var issuedQty = parseInt(document.getElementById('issued_quantity') ? document.getElementById('issued_quantity').value : '0', 10) || 0;
        var inputs = Array.prototype.slice.call(document.querySelectorAll('.return-qty-input'));
        var summaryEl = document.getElementById('returnQtySummary');
        var formEl = document.getElementById('returnConfirmForm');

        function toInt(value) {
            var n = parseInt(value, 10);
            return Number.isFinite(n) && n > 0 ? n : 0;
        }

        function updateSummary() {
            var total = inputs.reduce(function (sum, input) {
                return sum + toInt(input.value);
            }, 0);

            if (!summaryEl) return total;
            summaryEl.textContent = 'Total entered: ' + total + ' / ' + issuedQty;
            if (total > 0 && total <= issuedQty) {
                summaryEl.classList.remove('text-danger');
                summaryEl.classList.add('text-success');
            } else {
                summaryEl.classList.remove('text-success');
                summaryEl.classList.add('text-danger');
            }
            return total;
        }

        inputs.forEach(function (input) {
            input.addEventListener('input', updateSummary);
        });

        if (formEl) {
            formEl.addEventListener('submit', function (event) {
                var total = updateSummary();
                if (total <= 0 || total > issuedQty) {
                    event.preventDefault();
                    alert('Returned quantity breakdown must be between 1 and ' + issuedQty + '.');
                }
            });
        }

        updateSummary();
    })();
    </script>
</body>
</html>
