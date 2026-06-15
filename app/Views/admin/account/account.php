<?php
$base = BASE_URL !== '' ? rtrim(BASE_URL, '/') : '';
require_once __DIR__ . '/../../partials/admin/account_view_helpers.php';

$totalAccounts = count($users);
$usertypes = [];
$adminCount = 0;

foreach ($users as $row) {
    $type = strtoupper(trim((string) ($row['usertype'] ?? '')));
    if ($type !== '') {
        $usertypes[$type] = ($usertypes[$type] ?? 0) + 1;
    }
    if ($type === 'ADMIN') {
        $adminCount++;
    }
}

ksort($usertypes);
?>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Storage Mart | Accounts</title>
    <link href="<?= htmlspecialchars($base) ?>/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/storagemart.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/admin-users.css" rel="stylesheet">
    <link rel="icon" href="<?= htmlspecialchars($base) ?>/assets/img/favicon.ico" type="image/x-icon">
    <link href="<?= htmlspecialchars($base) ?>/assets/vendor/datatables/datatables.min.css" rel="stylesheet">
</head>

<body id="page-top">

    <div id="wrapper">
        <?php
        $activePage = 'users';
        $userSubPage = 'accounts';
        require_once __DIR__ . '/../../partials/admin/sidebar_topbar.php';
        ?>

        <div class="container-fluid admin-users-page">

            <div class="page-hero hero-accounts">
                <div class="row align-items-center">
                    <div class="col-lg-7">
                        <h1><i class="fas fa-id-card mr-2"></i>Accounts</h1>
                        <p>Manage system login accounts — usernames, roles, and access levels across the organization.</p>
                        <div class="quick-nav mt-3">
                            <a href="<?= htmlspecialchars($base) ?>/admin/employee" class="btn btn-sm btn-outline-light">
                                <i class="fas fa-user-tie mr-1"></i> Employee Directory
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-5">
                        <div class="row mt-3 mt-lg-0">
                            <div class="col-6">
                                <div class="hero-stat">
                                    <div class="stat-value"><?= (int) $totalAccounts ?></div>
                                    <div class="stat-label">Total</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="hero-stat">
                                    <div class="stat-value"><?= (int) $adminCount ?></div>
                                    <div class="stat-label">Admins</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="filter-toolbar">
                <div class="row align-items-end">
                    <div class="col-md-4 col-sm-6 mb-2 mb-md-0">
                        <label for="accountRoleFilter">Role / Usertype</label>
                        <select id="accountRoleFilter" class="form-control form-control-sm">
                            <option value="">All Roles</option>
                            <?php foreach (array_keys($usertypes) as $type): ?>
                                <option value="<?= htmlspecialchars($type) ?>"><?= htmlspecialchars($type) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-8 col-sm-6 text-md-right">
                        <button type="button" id="accountClearFilters" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-undo mr-1"></i> Clear Filters
                        </button>
                    </div>
                </div>
            </div>

            <div class="card data-list-card shadow mb-4">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-users-cog mr-1"></i> Account Directory
                    </h6>
                    <div class="card-header-actions">
                        <span class="badge badge-primary"><?= (int) $totalAccounts ?> account<?= $totalAccounts === 1 ? '' : 's' ?></span>
                        <a href="<?= htmlspecialchars($base) ?>/admin/account/add" class="btn btn-sm btn-primary">
                            <i class="fas fa-plus mr-1"></i> Add Account
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($users)): ?>
                        <div class="empty-state">
                            <i class="fas fa-user-slash d-block"></i>
                            No accounts found.
                        </div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="account" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Account ID</th>
                                    <th>Username</th>
                                    <th>Role</th>
                                    <th>Date Created</th>
                                    <th class="text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $row):
                                    $usertype = (string) ($row['usertype'] ?? '');
                                    $date = admin_account_format_date((string) ($row['datecreated'] ?? ''));
                                ?>
                                    <tr data-role="<?= htmlspecialchars(strtolower(trim($usertype))) ?>">
                                        <td>
                                            <span class="account-id">#<?= htmlspecialchars((string) ($row['account_id'] ?? '')) ?></span>
                                        </td>
                                        <td>
                                            <div class="username-text"><?= htmlspecialchars((string) ($row['username'] ?? '')) ?></div>
                                        </td>
                                        <td>
                                            <?php if ($usertype !== ''): ?>
                                                <span class="role-badge <?= admin_account_usertype_class($usertype) ?>">
                                                    <i class="fas fa-shield-alt"></i>
                                                    <?= htmlspecialchars($usertype) ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted">—</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="date-cell" data-order="<?= (int) $date['order'] ?>">
                                            <div class="date-main"><?= htmlspecialchars($date['main']) ?></div>
                                            <?php if ($date['time'] !== ''): ?>
                                                <div class="date-time"><?= htmlspecialchars($date['time']) ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-right">
                                            <div class="action-btn-group">
                                                <a href="<?= htmlspecialchars($base) ?>/admin/account/edit?account_id=<?= (int) ($row['account_id'] ?? 0) ?>"
                                                   class="btn btn-sm btn-outline-primary btn-action-icon" title="Edit account">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form method="POST" action="<?= htmlspecialchars($base) ?>/admin/account" class="action-btn-form">
                                                    <input type="hidden" name="id" value="<?= (int) ($row['account_id'] ?? 0) ?>">
                                                    <button type="submit" name="action" value="delete"
                                                        class="btn btn-sm btn-outline-danger btn-action-icon"
                                                        title="Delete account"
                                                        onclick="return confirm('Are you sure you want to delete this account?')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
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

    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/jquery/jquery.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/js/sb-admin-2.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/datatables/datatables.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/js/admin-accounts.js"></script>
    <?php require __DIR__ . '/../../partials/flash_modal.php'; ?>
</body>

</html>
