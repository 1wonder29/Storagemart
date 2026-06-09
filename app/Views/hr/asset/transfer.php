<?php
$base = rtrim(BASE_URL, '/');
$returnEmployeeId = (int) ($return_employee_id ?? 0);
$cancelUrl = $returnEmployeeId > 0
    ? $base . '/hr/employees/detail/' . $returnEmployeeId
    : $base . '/hr/employees';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Storage Mart | Transfer Asset</title>
    <link href="<?= htmlspecialchars($base) ?>/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/storagemart.css" rel="stylesheet">
    <link rel="icon" href="<?= htmlspecialchars($base) ?>/assets/img/sm_favicon.png" type="image/x-icon">
</head>
<body id="page-top">
    <div id="wrapper">
        <?php
        $activePage = 'employees';
        require_once dirname(dirname(__DIR__)) . '/partials/hr/sidebar_topbar.php';
        ?>
        <div class="container-fluid">
            <div class="row mb-4">
                <div class="col-12">
                    <a href="<?= htmlspecialchars($cancelUrl) ?>" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                </div>
            </div>

            <h1 class="h3 mb-4 text-gray-800">Transfer Asset</h1>

            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Asset Details</h6>
                </div>
                <div class="card-body">
                    <p><strong>Item:</strong> <?= htmlspecialchars($inventory['itemInfo'] ?? '') ?></p>
                    <p><strong>Asset Number:</strong> <?= htmlspecialchars($inventory['assetNumber'] ?? '') ?></p>
                    <p><strong>Serial Number:</strong> <?= htmlspecialchars($inventory['serialNumber'] ?? '') ?></p>
                </div>
            </div>

            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Transfer To Employee</h6>
                </div>
                <div class="card-body">
                    <form action="<?= htmlspecialchars($base) ?>/hr/assets/transfer?inventory_id=<?= (int) ($inventory['inventory_id'] ?? 0) ?>" method="POST" id="transferForm">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                        <input type="hidden" name="item_id" value="<?= (int) ($inventory['inventory_id'] ?? 0) ?>">
                        <input type="hidden" name="return_employee_id" value="<?= $returnEmployeeId ?>">

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="employee_search" class="form-label">Search Employee</label>
                                <div class="input-group">
                                    <input type="text" id="employee_search" class="form-control" placeholder="Type employee name or ID">
                                    <button type="button" class="btn btn-primary" id="btnSearchEmployee">Search</button>
                                </div>
                                <input type="hidden" id="employee_id" name="employee_id">
                            </div>
                            <div class="col-md-6">
                                <label for="branchName" class="form-label">Employee Branch</label>
                                <input type="text" class="form-control" id="branchName" name="branchName" readonly>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="transferDetails" class="form-label">Transfer Details</label>
                                <textarea id="transferDetails" name="transferDetails" class="form-control" rows="6" maxlength="1000" required></textarea>
                                <small class="form-text text-muted">Maximum 1000 characters.</small>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">Submit Transfer</button>
                        <a class="btn btn-secondary" href="<?= htmlspecialchars($cancelUrl) ?>">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/jquery/jquery.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/js/storagemart.min.js"></script>
    <script>
    document.getElementById('btnSearchEmployee').addEventListener('click', function () {
        var q = document.getElementById('employee_search').value.trim();
        if (!q) {
            alert('Enter employee id or name');
            return;
        }

        var url = "<?= htmlspecialchars($base, ENT_QUOTES) ?>/hr/assets/search-employee?q=" + encodeURIComponent(q);

        fetch(url, { credentials: 'same-origin' })
            .then(function (r) {
                if (!r.ok) {
                    return r.text().then(function (t) { throw new Error('HTTP ' + r.status + ': ' + t); });
                }
                return r.json();
            })
            .then(function (data) {
                if (data.success) {
                    document.getElementById('employee_id').value = data.employee_id;
                    document.getElementById('employee_search').value = data.full_name || '';
                    document.getElementById('branchName').value = data.branchName || '';
                } else {
                    alert(data.message || 'Employee not found');
                }
            })
            .catch(function (err) {
                console.error(err);
                alert('Error contacting server: ' + err.message);
            });
    });
    </script>
</body>
</html>
