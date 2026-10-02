<?php
$base = rtrim(BASE_URL, '/');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Storage Mart | Assign Item</title>
    <link href="<?= htmlspecialchars($base) ?>/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900" rel="stylesheet">
    <link rel="icon" href="<?= htmlspecialchars($base) ?>/assets/img/sm_favicon.png" type="image/x-icon">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/storagemart.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/hr-uniforms.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/searchable-select.css" rel="stylesheet">
</head>
<body id="page-top">
    <div id="wrapper">
    <?php 
    $activePage = 'uniforms';
    require_once dirname(dirname(__DIR__)) . '/partials/uniform_sidebar_topbar.php';?>
        <div class="container-fluid hr-uniform-page">
            <div class="page-hero">
                <h1><i class="fas fa-user-tag mr-2"></i>Assign Item to Employee</h1>
                <p>Issue inventory items with complete details, quantity, and condition tracking in one consistent workflow.</p>
            </div>

            <!-- Messages -->
            <?php if (isset($_SESSION['successMessage'])): ?>
                <div class="alert alert-success alert-dismissible fade show alert-modern" role="alert">
                    <?= htmlspecialchars($_SESSION['successMessage']) ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <?php unset($_SESSION['successMessage']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['errorMessage'])): ?>
                <div class="alert alert-danger alert-dismissible fade show alert-modern" role="alert">
                    <?= htmlspecialchars($_SESSION['errorMessage']) ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <?php unset($_SESSION['errorMessage']); ?>
            <?php endif; ?>

            <!-- Assignment Form -->
            <div class="card shadow uniform-card">
                <div class="card-header py-3">
                    <h6><i class="fas fa-archive"></i>Assign Item</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?= htmlspecialchars($base) ?>/hr/uniforms/assign" id="assignForm">
                        <div class="form-section">
                            <div class="section-title"><i class="fas fa-user"></i>Employee</div>
                            <div class="form-group mb-0">
                                <label class="form-label" for="employee_id">Select Employee</label>
                                <select class="form-control" id="employee_id" name="employee_id" required>
                                    <option value="">-- Choose an employee --</option>
                                    <?php foreach ($employees as $emp): ?>
                                        <option value="<?= $emp['employee_id'] ?>">
                                            <?= htmlspecialchars($emp['firstname'] . ' ' . $emp['lastname'] . ' (' . $emp['position'] . ')') ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-section">
                            <div class="section-title"><i class="fas fa-archive"></i>Item Selection</div>
                            <div class="form-group">
                                <label class="form-label" for="uniform_type">Item Type</label>
                                <select class="form-control" id="uniform_type" required>
                                    <option value="">-- Select an item type --</option>
                                    <?php foreach ($uniformTypes as $type): ?>
                                        <option value="<?= htmlspecialchars($type) ?>">
                                            <?= htmlspecialchars($type) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="uniform_id">Specific Item</label>
                                <select class="form-control" id="uniform_id" name="uniform_id" required disabled>
                                    <option value="">-- Select a type first --</option>
                                </select>
                                <small class="form-text text-muted">Shows size, color, and available stock.</small>
                            </div>
                            <div class="form-group mb-0">
                                <label class="form-label" for="quantity_issued">Quantity to Issue</label>
                                <input type="number" class="form-control" id="quantity_issued" name="quantity_issued" value="1" min="1" required>
                            </div>
                        </div>

                        <div class="form-section">
                            <div class="section-title"><i class="fas fa-layer-group"></i>Additional Items</div>
                            <div id="specialistUniformsContainer"></div>
                            <button type="button" class="btn btn-sm btn-success" id="addMoreBtn">
                                <i class="fas fa-plus"></i> Add More Item
                            </button>
                        </div>

                        <div class="form-section">
                            <div class="section-title"><i class="fas fa-clipboard-check"></i>Issue Details</div>
                            <div class="form-group">
                                <label class="form-label" for="condition_upon_issue">Condition Upon Issue</label>
                                <select class="form-control" id="condition_upon_issue" name="condition_upon_issue">
                                    <option value="GOOD">Good</option>
                                    <option value="FAIR">Fair</option>
                                    <option value="USED">Used</option>
                                </select>
                            </div>
                            <div class="form-group mb-0">
                                <label class="form-label" for="remarks">Remarks (Optional)</label>
                                <textarea class="form-control" id="remarks" name="remarks" rows="3" placeholder="Add notes or special handling details..."></textarea>
                            </div>
                        </div>

                        <div class="page-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-check"></i> Assign Item
                            </button>
                            <a href="<?= htmlspecialchars($base) ?>/hr/uniforms" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Back
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Recent Assignments -->
            <div class="card shadow mt-4 uniform-card">
                <div class="card-header py-3">
                    <h6><i class="fas fa-info-circle"></i>How to Assign</h6>
                </div>
                <div class="card-body">
                    <ol class="instruction-list">
                        <li>Select the employee who will receive the item</li>
                        <li>Choose the item type (e.g., Polo Shirt, ID Badge, ID Lace, Radio, Laptop)</li>
                        <li>Select the specific item (filtered by type, showing size, color, available stock)</li>
                        <li>Enter the quantity to issue</li>
                        <li>Set the condition (Good, Fair, Used)</li>
                        <li>Add any remarks if needed</li>
                        <li>Click "Assign Item" to complete</li>
                    </ol>
                </div>
            </div>

        </div>
    </div>

    <!-- Bootstrap JS and dependencies -->
            </div>
        </div>
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/jquery/jquery.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/js/searchable-select.js"></script>

    <script>
    $(document).ready(function() {
        let specificCount = 0;

        function makeSearchable(selectEl, placeholder, noResultsText) {
            if (!selectEl || typeof window.initSearchableSelect !== 'function') return;
            window.initSearchableSelect(selectEl, {
                placeholder: placeholder,
                noResultsText: noResultsText
            });
        }

        makeSearchable(document.getElementById('employee_id'), '-- Search employee --', 'No employees found');
        makeSearchable(document.getElementById('uniform_type'), '-- Search item type --', 'No item types found');
        makeSearchable(document.getElementById('uniform_id'), '-- Search specific item --', 'No items found');

        // Handle primary uniform type change
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
                        var html = '<option value="">-- Choose an item --</option>';
                        $.each(response.data, function(index, uniform) {
                            html += '<option value="' + uniform.uniform_id + '">';
                            html += uniform.uniform_type + ' - Size: ' + uniform.size + ', Color: ' + uniform.color;
                            html += ' (Stock: ' + uniform.quantity_in_stock + ')';
                            html += '</option>';
                        });
                        uniformSelect.html(html).prop('disabled', false);
                        makeSearchable(document.getElementById('uniform_id'), '-- Search specific item --', 'No items found');
                    } else {
                        uniformSelect.html('<option value="">No items available</option>').prop('disabled', true);
                    }
                },
                error: function() {
                    uniformSelect.html('<option value="">Error loading items</option>').prop('disabled', true);
                }
            });
        });

        // Update quantity max based on available stock for primary uniform
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

        // Handle additional uniform type change
        $(document).on('change', '[name^="specific_uniform_type_"]', function() {
            var uniformType = $(this).val();
            var container = $(this).closest('.specific-uniform-item');
            var uniformSelect = container.find('[name^="specific_uniform_id_"]');
            
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
                        var html = '<option value="">-- Choose an item --</option>';
                        $.each(response.data, function(index, uniform) {
                            html += '<option value="' + uniform.uniform_id + '">';
                            html += uniform.uniform_type + ' - Size: ' + uniform.size + ', Color: ' + uniform.color;
                            html += ' (Stock: ' + uniform.quantity_in_stock + ')';
                            html += '</option>';
                        });
                        uniformSelect.html(html).prop('disabled', false);
                        makeSearchable(uniformSelect.get(0), '-- Search specific item --', 'No items found');
                    } else {
                        uniformSelect.html('<option value="">No items available</option>').prop('disabled', true);
                    }
                },
                error: function() {
                    uniformSelect.html('<option value="">Error loading items</option>').prop('disabled', true);
                }
            });
        });

        // Add more specific uniform
        $('#addMoreBtn').click(function(e) {
            e.preventDefault();
            specificCount++;
            var html = '<div class="specific-uniform-item uniform-extra-item">';
            html += '<button type="button" class="btn btn-sm btn-outline-danger remove-specific uniform-extra-remove" title="Remove this item">';
            html += '<i class="fas fa-times"></i></button>';
            html += '<label class="form-label" for="specific_uniform_type_' + specificCount + '">Item Type</label>';
            html += '<select class="form-control mb-2" id="specific_uniform_type_' + specificCount + '" name="specific_uniform_type_' + specificCount + '">';
            html += '<option value="">-- Select a type first --</option>';
            <?php foreach ($uniformTypes as $type): ?>
                html += '<option value="<?= htmlspecialchars($type) ?>"><?= htmlspecialchars($type) ?></option>';
            <?php endforeach; ?>
            html += '</select>';
            html += '<select class="form-control mb-2" id="specific_uniform_id_' + specificCount + '" name="specific_uniform_id_' + specificCount + '" disabled>';
            html += '<option value="">-- Select a type first --</option>';
            html += '</select>';
            html += '<small class="form-text text-muted">Showing: Size, Color, Available Stock</small>';
            html += '<div class="mt-2">';
            html += '<label class="form-label" for="specific_quantity_' + specificCount + '">Quantity</label>';
            html += '<input type="number" class="form-control" id="specific_quantity_' + specificCount + '" name="specific_quantity_' + specificCount + '" value="1" min="1">';
            html += '</div>';
            html += '</div>';
            
            $('#specialistUniformsContainer').append(html);
            makeSearchable(document.getElementById('specific_uniform_type_' + specificCount), '-- Search item type --', 'No item types found');
            makeSearchable(document.getElementById('specific_uniform_id_' + specificCount), '-- Search specific item --', 'No items found');
        });

        // Remove specific uniform
        $(document).on('click', '.remove-specific', function(e) {
            e.preventDefault();
            $(this).closest('.specific-uniform-item').remove();
        });
    });
    </script>
</body>
</html>
