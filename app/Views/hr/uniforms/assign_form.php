<?php
$base = rtrim(BASE_URL, '/');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Storage Mart | Assign Uniform</title>
    <link href="<?= htmlspecialchars($base) ?>/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900" rel="stylesheet">
    <link rel="icon" href="<?= htmlspecialchars($base) ?>/assets/img/sm_favicon.png" type="image/x-icon">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/storagemart.css" rel="stylesheet">
</head>
<body id="page-top">
    <div id="wrapper">
    <?php 
    $activePage = 'uniforms';
    require_once dirname(dirname(__DIR__)) . '/partials/hr/sidebar_topbar.php';?>
        <div class="container-fluid">
            <h1 class="h3 mb-4 text-gray-800">Assign Uniform to Employee</h1>

            <!-- Messages -->
            <?php if (isset($_SESSION['successMessage'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($_SESSION['successMessage']) ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <?php unset($_SESSION['successMessage']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['errorMessage'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($_SESSION['errorMessage']) ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <?php unset($_SESSION['errorMessage']); ?>
            <?php endif; ?>

            <!-- Assignment Form -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Assign Uniform</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?= htmlspecialchars($base) ?>/hr/uniforms/assign" id="assignForm">
                        
                        <!-- Employee Selection -->
                        <div class="form-group">
                            <label for="employee_id"><strong>Select Employee</strong></label>
                            <select class="form-control" id="employee_id" name="employee_id" required>
                                <option value="">-- Choose an employee --</option>
                                <?php foreach ($employees as $emp): ?>
                                    <option value="<?= $emp['employee_id'] ?>">
                                        <?= htmlspecialchars($emp['firstname'] . ' ' . $emp['lastname'] . ' (' . $emp['position'] . ')') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Uniform Type Selection -->
                        <div class="form-group">
                            <label for="uniform_type"><strong>Select Uniform Type</strong></label>
                            <select class="form-control" id="uniform_type" required>
                                <option value="">-- Choose a uniform type --</option>
                                <?php foreach ($uniformTypes as $type): ?>
                                    <option value="<?= htmlspecialchars($type) ?>">
                                        <?= htmlspecialchars($type) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Uniform Selection -->
                        <div class="form-group">
                            <label for="uniform_id"><strong>Select Specific Uniform</strong></label>
                            <select class="form-control" id="uniform_id" name="uniform_id" required disabled>
                                <option value="">-- Select a type first --</option>
                            </select>
                            <small class="form-text text-muted">Showing: Size, Color, Available Stock</small>
                        </div>

                        <!-- Quantity -->
                        <div class="form-group">
                            <label for="quantity_issued"><strong>Quantity to Issue</strong></label>
                            <input type="number" class="form-control" id="quantity_issued" name="quantity_issued" value="1" min="1" required>
                        </div>

                        <!-- Condition -->
                        <div class="form-group">
                            <label for="condition_upon_issue"><strong>Condition Upon Issue</strong></label>
                            <select class="form-control" id="condition_upon_issue" name="condition_upon_issue">
                                <option value="GOOD">Good</option>
                                <option value="FAIR">Fair</option>
                                <option value="USED">Used</option>
                            </select>
                        </div>

                        <!-- Remarks -->
                        <div class="form-group">
                            <label for="remarks"><strong>Remarks (Optional)</strong></label>
                            <textarea class="form-control" id="remarks" name="remarks" rows="3" placeholder="Add any notes..."></textarea>
                        </div>

                        <!-- Buttons -->
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-check"></i> Assign Uniform
                            </button>
                            <a href="<?= htmlspecialchars($base) ?>/hr/uniforms" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Recent Assignments -->
            <div class="card shadow mt-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">How to Assign</h6>
                </div>
                <div class="card-body">
                    <ol>
                        <li>Select the employee who will receive the uniform</li>
                        <li>Choose the type of uniform (e.g., Polo Shirt, Cap, ID Badge)</li>
                        <li>Select the specific uniform (filtered by type, showing size, color, available stock)</li>
                        <li>Enter the quantity to issue</li>
                        <li>Set the condition (Good, Fair, Used)</li>
                        <li>Add any remarks if needed</li>
                        <li>Click "Assign Uniform" to complete</li>
                    </ol>
                </div>
            </div>

        </div>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/jquery/jquery.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <script>
    $(document).ready(function() {
        // When uniform type is selected, populate uniform dropdown
        $('#uniform_type').change(function() {
            var uniformType = $(this).val();
            var uniformSelect = $('#uniform_id');
            
            if (uniformType === '') {
                uniformSelect.html('<option value="">-- Select a type first --</option>').prop('disabled', true);
                return;
            }

            $.ajax({
                url: '<?= htmlspecialchars($base) ?>/hr/uniforms/get-by-type',
                type: 'GET',
                data: { type: uniformType },
                dataType: 'json',
                success: function(response) {
                    if (response.success && response.data.length > 0) {
                        var html = '<option value="">-- Choose a uniform --</option>';
                        $.each(response.data, function(index, uniform) {
                            html += '<option value="' + uniform.uniform_id + '">';
                            html += uniform.uniform_type + ' - Size: ' + uniform.size + ', Color: ' + uniform.color;
                            html += ' (Stock: ' + uniform.quantity_in_stock + ')';
                            html += '</option>';
                        });
                        uniformSelect.html(html).prop('disabled', false);
                    } else {
                        uniformSelect.html('<option value="">No uniforms available</option>').prop('disabled', true);
                    }
                },
                error: function() {
                    uniformSelect.html('<option value="">Error loading uniforms</option>').prop('disabled', true);
                }
            });
        });

        // Update quantity max based on available stock
        $('#uniform_id').change(function() {
            var selected = $(this).find('option:selected').text();
            var match = selected.match(/Stock: (\d+)/);
            if (match) {
                var maxStock = parseInt(match[1]);
                $('#quantity_issued').attr('max', maxStock);
                if ($('#quantity_issued').val() > maxStock) {
                    $('#quantity_issued').val(1);
                }
            }
        });
    });
    </script>
</body>
</html>
