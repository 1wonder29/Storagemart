<?php
$base = rtrim(BASE_URL, '/');
$isEditing = $isEditing ?? false;
$uniform = $uniform ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Storage Mart | <?= $isEditing ? 'Edit' : 'Add' ?> Uniform</title>
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
            <div class="row mb-4">
                <div class="col-12">
                    <a href="<?= htmlspecialchars($base) ?>/hr/uniforms" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                </div>
            </div>

            <h1 class="h3 mb-4 text-gray-800"><?= $isEditing ? 'Edit' : 'Add' ?> Uniform</h1>

            <div class="card shadow">
                <div class="card-body">
                    <form method="POST" action="<?= $isEditing ? htmlspecialchars($base) . '/hr/uniforms/edit/' . $uniform['uniform_id'] : htmlspecialchars($base) . '/hr/uniforms/add' ?>" class="needs-validation">
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="uniform_type" class="form-label">Uniform Type <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="uniform_type" name="uniform_type" 
                                       value="<?= $uniform ? htmlspecialchars($uniform['uniform_type']) : '' ?>" 
                                       placeholder="e.g., Polo Shirt, Cap, ID Badge" required>
                            </div>
                            <div class="col-md-6">
                                <label for="size" class="form-label">Size <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="size" name="size" 
                                       value="<?= $uniform ? htmlspecialchars($uniform['size']) : '' ?>" 
                                       placeholder="e.g., S, M, L, XL, One Size" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="quantity_in_stock" class="form-label">Quantity in Stock <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="quantity_in_stock" name="quantity_in_stock" 
                                       value="<?= $uniform ? (int)$uniform['quantity_in_stock'] : 0 ?>" min="0" required>
                            </div>
                            <div class="col-md-6">
                                <label for="reorder_level" class="form-label">Reorder Level</label>
                                <input type="number" class="form-control" id="reorder_level" name="reorder_level" 
                                       value="<?= $uniform ? (int)$uniform['reorder_level'] : 5 ?>" min="0">
                                <small class="form-text text-muted">Alert when stock falls below this level</small>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> <?= $isEditing ? 'Update' : 'Add' ?> Uniform
                                </button>
                                <a href="<?= htmlspecialchars($base) ?>/hr/uniforms" class="btn btn-secondary">Cancel</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    </div>

    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/jquery/jquery.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/js/storagemart.min.js"></script>
</body>
</html>
