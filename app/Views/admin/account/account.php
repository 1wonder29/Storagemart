<?php
$base = BASE_URL !== '' ? rtrim(BASE_URL, '/') : '';
require_once __DIR__ . '/../../partials/admin/account_view_helpers.php';
require_once __DIR__ . '/../../../Helpers/RoleLabel.php';
require_once __DIR__ . '/../../../Helpers/LoginThrottle.php';

$users = $users ?? [];
$historyCounts = $historyCounts ?? [];
$departmentLabels = $departmentLabels ?? [];
$resetRequests = $resetRequests ?? [];
$lockedUsernames = $lockedUsernames ?? [];

$roleOptions = [];
$departmentOptions = [];
$branchOptions = [];
$totalUsers = 0;
$activeUsers = 0;
$adminCount = 0;

foreach ($users as $row) {
    if (Account::isSystemAccount($row)) {
        continue;
    }
    $totalUsers++;
    if (strtoupper((string) ($row['status'] ?? '')) === 'ACTIVE') {
        $activeUsers++;
    }
    foreach ([$row['usertype'] ?? '', $row['secondary_usertype'] ?? ''] as $type) {
        $type = strtoupper(trim((string) $type));
        if ($type !== '') {
            $roleOptions[$type] = RoleLabel::of($type);
        }
    }
    if (strtoupper((string) ($row['usertype'] ?? '')) === 'ADMIN') {
        $adminCount++;
    }
    $dept = trim((string) ($row['department'] ?? ''));
    if ($dept !== '') {
        $departmentOptions[$dept] = $departmentLabels[$dept] ?? $dept;
    }
    $branch = trim((string) ($row['branchName'] ?? ''));
    if ($branch !== '') {
        $branchOptions[$branch] = true;
    }
}
asort($roleOptions);
asort($departmentOptions);
ksort($branchOptions);
?>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Storage Mart | Users &amp; Employees</title>
    <link href="<?= htmlspecialchars($base) ?>/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/storagemart.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/admin-users.css?v=20261002c" rel="stylesheet">
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
                    <div class="col-lg-6">
                        <h1><i class="fas fa-users mr-2"></i>Users &amp; Employees</h1>
                        <p>Every person in one place — their login, role, department, and branch. Add, edit, or remove them here.</p>
                    </div>
                    <div class="col-lg-6">
                        <div class="row mt-3 mt-lg-0">
                            <div class="col-4">
                                <div class="hero-stat">
                                    <div class="stat-value"><?= $totalUsers ?></div>
                                    <div class="stat-label">Total</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="hero-stat">
                                    <div class="stat-value"><?= $activeUsers ?></div>
                                    <div class="stat-label">Active</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="hero-stat">
                                    <div class="stat-value"><?= $adminCount ?></div>
                                    <div class="stat-label">Admins</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php if ($resetRequests): ?>
                <div class="card data-list-card shadow mb-4" id="reset-requests">
                    <div class="card-header d-flex align-items-center justify-content-between flex-wrap">
                        <h6 class="m-0 font-weight-bold text-warning">
                            <i class="fas fa-key mr-1"></i> Password reset requests
                        </h6>
                        <span class="badge badge-warning"><?= count($resetRequests) ?> pending</span>
                    </div>
                    <div class="card-body p-0">
                        <p class="px-3 pt-3 mb-2 text-muted small">
                            Confirm it's really the person (call them or see them in person) before setting a temporary password.
                            Tell them the password directly, never by email or chat, and ask them to change it after logging in.
                        </p>
                        <div class="table-responsive">
                            <table class="table mb-0">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Username</th>
                                        <th>Requested</th>
                                        <th>How to reach them</th>
                                        <th class="text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($resetRequests as $req):
                                        $reqName = trim(($req['firstname'] ?? '') . ' ' . ($req['lastname'] ?? '')) ?: (string) $req['username'];
                                        $reqDate = admin_account_format_date((string) $req['requested_at']);
                                    ?>
                                        <tr>
                                            <td>
                                                <div class="employee-name"><?= htmlspecialchars($reqName) ?></div>
                                                <?php if (!empty($req['position'])): ?>
                                                    <div class="meta-hint"><?= htmlspecialchars((string) $req['position']) ?></div>
                                                <?php endif; ?>
                                                <?php if (strtoupper((string) $req['status']) !== 'ACTIVE'): ?>
                                                    <span class="badge badge-secondary">Account <?= htmlspecialchars(ucfirst(strtolower((string) $req['status']))) ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= htmlspecialchars((string) $req['username']) ?></td>
                                            <td class="date-cell">
                                                <div class="date-main"><?= htmlspecialchars($reqDate['main']) ?></div>
                                                <div class="date-time"><?= htmlspecialchars($reqDate['time']) ?></div>
                                            </td>
                                            <td><?= $req['note'] !== '' ? htmlspecialchars((string) $req['note']) : '<span class="text-muted">—</span>' ?></td>
                                            <td class="text-right">
                                                <div class="action-btn-group">
                                                    <a class="btn btn-sm btn-primary" href="<?= htmlspecialchars($base) ?>/admin/account/edit?account_id=<?= (int) $req['account_id'] ?>&amp;reset=1">
                                                        <i class="fas fa-key mr-1"></i> Set temporary password
                                                    </a>
                                                    <form method="POST" action="<?= htmlspecialchars($base) ?>/admin/account" class="d-inline">
                                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                                                        <input type="hidden" name="action" value="dismiss_reset">
                                                        <input type="hidden" name="request_id" value="<?= (int) $req['request_id'] ?>">
                                                        <input type="hidden" name="display_name" value="<?= htmlspecialchars($reqName) ?>">
                                                        <button type="submit" class="btn btn-sm btn-outline-secondary" title="Not a real request">Dismiss</button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="filter-toolbar">
                <div class="row align-items-end">
                    <div class="col-lg-3 col-sm-6 mb-2 mb-lg-0">
                        <label for="userRoleFilter">Role</label>
                        <select id="userRoleFilter" class="form-control form-control-sm">
                            <option value="">All Roles</option>
                            <?php foreach ($roleOptions as $type => $label): ?>
                                <option value="<?= htmlspecialchars(strtolower($type)) ?>"><?= htmlspecialchars($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-lg-3 col-sm-6 mb-2 mb-lg-0">
                        <label for="userDepartmentFilter">Department</label>
                        <select id="userDepartmentFilter" class="form-control form-control-sm">
                            <option value="">All Departments</option>
                            <?php foreach ($departmentOptions as $code => $label): ?>
                                <option value="<?= htmlspecialchars(strtolower($code)) ?>"><?= htmlspecialchars($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-lg-2 col-sm-6 mb-2 mb-lg-0">
                        <label for="userBranchFilter">Branch</label>
                        <select id="userBranchFilter" class="form-control form-control-sm">
                            <option value="">All Branches</option>
                            <?php foreach (array_keys($branchOptions) as $branch): ?>
                                <option value="<?= htmlspecialchars(strtolower($branch)) ?>"><?= htmlspecialchars($branch) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-lg-2 col-sm-6 mb-2 mb-lg-0">
                        <label for="userStatusFilter">Status</label>
                        <select id="userStatusFilter" class="form-control form-control-sm">
                            <option value="">All</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="no-login">No login</option>
                        </select>
                    </div>
                    <div class="col-lg-2 text-lg-right">
                        <button type="button" id="userClearFilters" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-undo mr-1"></i> Clear Filters
                        </button>
                    </div>
                </div>
            </div>

            <div class="card data-list-card shadow mb-4">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-users-cog mr-1"></i> User Directory
                    </h6>
                    <div class="card-header-actions">
                        <span class="badge badge-primary"><?= $totalUsers ?> user<?= $totalUsers === 1 ? '' : 's' ?></span>
                        <a href="<?= htmlspecialchars($base) ?>/admin/account/add" class="btn btn-sm btn-primary">
                            <i class="fas fa-plus mr-1"></i> Add User
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($users)): ?>
                        <div class="empty-state">
                            <i class="fas fa-user-slash d-block"></i>
                            No users found.
                        </div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="userDirectory" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Role</th>
                                    <th>Department / Position</th>
                                    <th>Branch</th>
                                    <th>Status</th>
                                    <th>Date Created</th>
                                    <th class="text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $row):
                                    $accountId = (int) ($row['account_id'] ?? 0);
                                    $employeeId = (int) ($row['employee_id'] ?? 0);
                                    $isSystem = Account::isSystemAccount($row);
                                    $usertype = (string) ($row['usertype'] ?? '');
                                    $secondaryType = (string) ($row['secondary_usertype'] ?? '');
                                    $department = trim((string) ($row['department'] ?? ''));
                                    $position = trim((string) ($row['position'] ?? ''));
                                    $branch = trim((string) ($row['branchName'] ?? ''));
                                    $fullName = $employeeId > 0 ? admin_employee_full_name($row) : (string) ($row['username'] ?? '—');
                                    $status = $accountId > 0 ? strtoupper((string) ($row['status'] ?? '')) : 'NO LOGIN';
                                    $statusKey = $accountId > 0 ? strtolower($status) : 'no-login';
                                    $date = admin_account_format_date((string) ($row['datecreated'] ?? ''));
                                    $history = $historyCounts[$employeeId] ?? ['tickets' => 0, 'items' => 0, 'assets' => 0];
                                    $isGeneralManagerAccount = $accountId === 2200616;
                                    $departmentLabel = $departmentLabels[$department] ?? $department;
                                    $primaryRoleText = admin_account_role_badge_text($usertype, $departmentLabel);
                                    $secondaryRoleText = $isGeneralManagerAccount && $position !== ''
                                        ? $position
                                        : admin_account_role_badge_text($secondaryType, $departmentLabel);
                                    $roleTokens = array_filter([strtolower(trim($usertype)), strtolower(trim($secondaryType))]);
                                    $isLocked = $accountId > 0 && isset($lockedUsernames[LoginThrottle::key((string) ($row['username'] ?? ''))]);
                                ?>
                                    <tr data-role="<?= htmlspecialchars(implode(' ', $roleTokens)) ?>"
                                        data-department="<?= htmlspecialchars(strtolower($department)) ?>"
                                        data-branch="<?= htmlspecialchars(strtolower($branch)) ?>"
                                        data-status="<?= htmlspecialchars($statusKey) ?>">
                                        <td>
                                            <div class="employee-name">
                                                <?= htmlspecialchars($fullName) ?>
                                                <?php if ($isSystem): ?>
                                                    <span class="badge badge-secondary ml-1">System</span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="employee-meta">
                                                <?php if ($employeeId > 0): ?>
                                                    <span class="employee-id">#<?= $employeeId ?></span>
                                                <?php endif; ?>
                                                <?php if ($accountId > 0): ?>
                                                    <span class="ml-2">Acct #<?= $accountId ?></span>
                                                <?php endif; ?>
                                            </div>
                                            <?php if (!empty($row['username'])): ?>
                                                <div class="meta-hint"><i class="fas fa-user mr-1"></i><?= htmlspecialchars((string) $row['username']) ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($usertype !== ''): ?>
                                                <span class="role-badge <?= admin_account_usertype_class($usertype) ?>">
                                                    <i class="fas fa-shield-alt"></i>
                                                    <?= htmlspecialchars($primaryRoleText) ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted">—</span>
                                            <?php endif; ?>
                                            <?php if ($secondaryType !== ''): ?>
                                                <span class="role-badge role-badge-secondary <?= admin_account_usertype_class($secondaryType) ?>">
                                                    <i class="fas fa-shield-alt"></i>
                                                    <?= htmlspecialchars($secondaryRoleText) ?>
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($department !== ''): ?>
                                                <span class="dept-badge <?= admin_employee_department_class($department) ?>">
                                                    <i class="fas fa-building"></i>
                                                    <?= htmlspecialchars($departmentLabels[$department] ?? $department) ?>
                                                </span>
                                            <?php endif; ?>
                                            <?php if ($position !== ''): ?>
                                                <div class="position-text"><?= htmlspecialchars($position) ?></div>
                                            <?php endif; ?>
                                            <?php if ($department === '' && $position === ''): ?>
                                                <span class="text-muted">—</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($branch !== ''): ?>
                                                <span class="branch-pill"><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($branch) ?></span>
                                            <?php else: ?>
                                                <span class="text-muted">—</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($status === 'ACTIVE'): ?>
                                                <span class="badge badge-success">Active</span>
                                            <?php elseif ($status === 'NO LOGIN'): ?>
                                                <span class="badge badge-warning" title="Employee record without a login account">No login</span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary"><?= htmlspecialchars(ucfirst(strtolower($status))) ?></span>
                                            <?php endif; ?>
                                            <?php if ($isLocked): ?>
                                                <span class="badge badge-danger" title="Locked for <?= LoginThrottle::LOCK_MINUTES ?> minutes after <?= LoginThrottle::MAX_ATTEMPTS ?> wrong passwords">Locked</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="date-cell" data-order="<?= (int) $date['order'] ?>">
                                            <div class="date-main"><?= htmlspecialchars($date['main']) ?></div>
                                            <?php if ($date['time'] !== ''): ?>
                                                <div class="date-time"><?= htmlspecialchars($date['time']) ?></div>
                                            <?php endif; ?>
                                            <?php if (!empty($row['createdby'])): ?>
                                                <div class="meta-hint">by <?= htmlspecialchars((string) $row['createdby']) ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-right">
                                            <div class="action-btn-group">
                                                <?php if ($isLocked): ?>
                                                    <form method="POST" action="<?= htmlspecialchars($base) ?>/admin/account" class="d-inline">
                                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                                                        <input type="hidden" name="action" value="unlock">
                                                        <input type="hidden" name="account_id" value="<?= $accountId ?>">
                                                        <input type="hidden" name="display_name" value="<?= htmlspecialchars($fullName) ?>">
                                                        <button type="submit" class="btn btn-sm btn-outline-warning btn-action-icon" title="Unlock login now">
                                                            <i class="fas fa-unlock"></i>
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                                <?php if ($employeeId > 0): ?>
                                                    <a href="<?= htmlspecialchars($base) ?>/admin/assets/view?employee_id=<?= $employeeId ?>"
                                                       class="btn btn-sm btn-outline-info btn-action-icon" title="View assigned assets">
                                                        <i class="fas fa-box-open"></i>
                                                    </a>
                                                <?php endif; ?>
                                                <?php if ($accountId > 0): ?>
                                                    <a href="<?= htmlspecialchars($base) ?>/admin/account/edit?account_id=<?= $accountId ?>"
                                                       class="btn btn-sm btn-outline-primary btn-action-icon" title="Edit user">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                <?php endif; ?>
                                                <?php if (!$isSystem): ?>
                                                    <button type="button" class="btn btn-sm btn-outline-danger btn-action-icon js-manage-user" title="Delete or deactivate"
                                                            data-account-id="<?= $accountId ?>"
                                                            data-employee-id="<?= $employeeId ?>"
                                                            data-name="<?= htmlspecialchars($fullName) ?>"
                                                            data-status="<?= htmlspecialchars($statusKey) ?>"
                                                            data-tickets="<?= (int) $history['tickets'] ?>"
                                                            data-items="<?= (int) $history['items'] ?>"
                                                            data-assets="<?= (int) $history['assets'] ?>">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                <?php endif; ?>
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
        </div>
    </div>

    <div class="modal fade" id="manageUserModal" tabindex="-1" role="dialog" aria-labelledby="manageUserTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <form class="modal-content" method="POST" action="<?= htmlspecialchars($base) ?>/admin/account" id="manageUserForm">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                <input type="hidden" name="account_id" id="manageUserAccountId">
                <input type="hidden" name="employee_id" id="manageUserEmployeeId">
                <input type="hidden" name="display_name" id="manageUserDisplayName">
                <input type="hidden" name="action" id="manageUserAction">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="manageUserTitle"><i class="fas fa-exclamation-triangle mr-1"></i> Remove User</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <p class="mb-3"><strong id="manageUserName"></strong></p>
                    <div id="manageUserHistory" class="alert alert-warning d-none"></div>
                    <div id="manageUserDeletable" class="alert alert-light border d-none">
                        Deleting removes this person's <strong>login and employee record together</strong>. This cannot be undone.
                        <div id="manageUserAssets" class="mt-2 d-none"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning d-none" id="manageUserDeactivateBtn" data-action="deactivate">
                        <i class="fas fa-user-slash mr-1"></i> Deactivate
                    </button>
                    <button type="submit" class="btn btn-success d-none" id="manageUserActivateBtn" data-action="activate">
                        <i class="fas fa-user-check mr-1"></i> Reactivate
                    </button>
                    <button type="submit" class="btn btn-danger d-none" id="manageUserDeleteBtn" data-action="delete">
                        <i class="fas fa-trash mr-1"></i> Delete permanently
                    </button>
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
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/datatables/datatables.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/js/admin-accounts.js?v=20261002c"></script>
    <?php require __DIR__ . '/../../partials/flash_modal.php'; ?>
</body>

</html>
