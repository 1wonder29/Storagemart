<?php
/** New user requests: HR sends, HR Head approves, Admin creates. Shared by /hr/user-requests and /admin/user-requests. */
$base = rtrim(BASE_URL, '/');
$h = static fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
$rows = $rows ?? [];
$isAdminArea = $area === 'admin';
$isHrHead = $isHrHead ?? false;
$me = (int) ($me ?? 0);
$branchNames = [];
foreach ((new Account())->fetchBranches() as $b) {
    $branchNames[(int) $b['branch_id']] = (string) $b['branchName'];
}
$waiting = array_values(array_filter($rows, static fn($r) => in_array($r['status'], ['HR_HEAD', 'ADMIN', 'CREATING'], true)));
$done = array_values(array_filter($rows, static fn($r) => !in_array($r['status'], ['HR_HEAD', 'ADMIN', 'CREATING'], true)));
$countHead = count(array_filter($waiting, static fn($r) => $r['status'] === 'HR_HEAD'));
$countAdmin = count(array_filter($waiting, static fn($r) => $r['status'] === 'ADMIN'));
$date = static fn($d) => $d ? date('M j, Y g:i A', strtotime((string) $d)) : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Storage Mart | New User Requests</title>
    <link href="<?= $h($base) ?>/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900" rel="stylesheet">
    <link rel="icon" href="<?= $h($base) ?>/assets/img/sm_favicon.png" type="image/x-icon">
    <link href="<?= $h($base) ?>/assets/css/storagemart.css" rel="stylesheet">
    <?php if ($isAdminArea): ?>
        <link href="<?= $h($base) ?>/assets/css/admin-users.css" rel="stylesheet">
    <?php else: ?>
        <link href="<?= $h($base) ?>/assets/css/hr-dashboard.css?v=20261007" rel="stylesheet">
    <?php endif; ?>
    <style>
        .ur-card { border: 1px solid #e6ecf7; border-radius: 1rem; background: #fff; padding: 14px 16px; margin-bottom: 12px; box-shadow: 0 6px 18px -12px rgba(15, 37, 99, .3); }
        .ur-card h5 { font-size: 1.05rem; font-weight: 800; color: #19398a; margin: 0; }
        .ur-meta { display: grid; grid-template-columns: repeat(auto-fill, minmax(190px, 1fr)); gap: 6px 16px; margin: 10px 0; font-size: .85rem; }
        .ur-meta span { display: block; font-size: .68rem; font-weight: 800; letter-spacing: .05em; text-transform: uppercase; color: #94a3b8; }
        .ur-track { display: flex; flex-wrap: wrap; gap: 6px; align-items: center; font-size: .78rem; margin: 6px 0 10px; }
        .ur-track .st { padding: 3px 10px; border-radius: 999px; background: #eef2f9; color: #64748b; font-weight: 700; }
        .ur-track .st.ok { background: #e7f7ef; color: #0e7a52; }
        .ur-track .st.now { background: #fff1e6; color: #c2410c; }
        .ur-track .st.no { background: #fde8e8; color: #b91c1c; }
        .ur-actions { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; }
        .ur-actions .form-control { max-width: 320px; }
        html.it-dark .ur-card { background: #232838; border-color: #343a4f; }
        html.it-dark .ur-card h5 { color: #8fb0ff; }
    </style>
</head>
<body id="page-top">
<div id="wrapper">
    <?php
    if ($isAdminArea) {
        $activePage = 'users';
        $userSubPage = 'requests';
        require_once dirname(__DIR__) . '/partials/admin/sidebar_topbar.php';
    } else {
        $activePage = 'user-requests';
        require_once dirname(__DIR__) . '/partials/hr/sidebar_topbar.php';
    }
    ?>
    <div class="container-fluid <?= $isAdminArea ? 'admin-users-page' : 'hr-dashboard-page' ?>">
        <div class="page-hero <?= $isAdminArea ? 'hero-accounts' : '' ?>">
            <div class="row align-items-center">
                <div class="col-lg-7">
                    <h1><i class="fas fa-user-plus mr-2"></i>New User Requests</h1>
                    <p>HR sends the new employee's account. The HR Head approves first, then the Admin approves and the account is created.</p>
                    <?php if (!$isAdminArea): ?>
                        <a href="<?= $h($base) ?>/hr/user-requests/new" class="btn btn-light btn-sm mt-2"><i class="fas fa-plus"></i> Request a new user</a>
                    <?php else: ?>
                        <a href="<?= $h($base) ?>/admin/account" class="btn btn-light btn-sm mt-2"><i class="fas fa-arrow-left"></i> Users &amp; Employees</a>
                    <?php endif; ?>
                </div>
                <div class="col-lg-5 mt-3 mt-lg-0">
                    <div class="row">
                        <div class="col-6"><div class="hero-stat"><div class="stat-value"><?= $countHead ?></div><div class="stat-label">Waiting for HR Head</div></div></div>
                        <div class="col-6"><div class="hero-stat"><div class="stat-value"><?= $countAdmin ?></div><div class="stat-label">Waiting for Admin</div></div></div>
                    </div>
                </div>
            </div>
        </div>

        <?php foreach (['flash_success' => 'success', 'flash_error' => 'danger'] as $key => $type): ?>
            <?php if (!empty($_SESSION[$key])): ?>
                <div class="alert alert-<?= $type ?>"><?= $h($_SESSION[$key]) ?></div>
                <?php unset($_SESSION[$key]); ?>
            <?php endif; ?>
        <?php endforeach; ?>

        <?php if (!empty($setupMissing)): ?>
            <div class="alert alert-warning">Please import the database update (update O) first.</div>
        <?php endif; ?>

        <?php if ($isAdminArea && empty($setupMissing)): ?>
            <form method="POST" action="<?= $h($base) ?>/admin/user-requests/hr-head" class="card shadow-sm mb-3">
                <div class="card-body py-3 d-flex flex-wrap align-items-center" style="gap:10px">
                    <input type="hidden" name="csrf_token" value="<?= $h($_SESSION['csrf_token'] ?? '') ?>">
                    <i class="fas fa-user-tie text-primary"></i>
                    <label for="hrHead" class="mb-0 font-weight-bold">HR Head (approves first)</label>
                    <select id="hrHead" name="hr_head_account_id" class="custom-select custom-select-sm" style="max-width:280px">
                        <?php
                        $autoNames = [];
                        foreach ($hrAccounts as $acc) {
                            if (in_array((int) $acc['account_id'], $hrHeadIds, true)) {
                                $autoNames[] = $acc['name'];
                            }
                        }
                        ?>
                        <option value="0">Automatic<?= !$hrHeadChosen && $autoNames ? ': ' . $h(implode(', ', $autoNames)) : '' ?></option>
                        <?php foreach ($hrAccounts as $acc): ?>
                            <option value="<?= (int) $acc['account_id'] ?>"<?= (int) $acc['account_id'] === (int) $hrHeadChosen ? ' selected' : '' ?>><?= $h($acc['name'] . ($acc['position'] ? ' (' . $acc['position'] . ')' : '')) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-save"></i> Save</button>
                    <small class="text-muted w-100">Automatic = the HR account whose position says Manager / Head.</small>
                </div>
            </form>
        <?php endif; ?>

        <h6 class="font-weight-bold text-primary mb-2"><i class="fas fa-hourglass-half mr-1"></i> Waiting (<?= count($waiting) ?>)</h6>
        <?php if (!$waiting): ?>
            <div class="card shadow-sm mb-4"><div class="card-body text-muted"><i class="fas fa-check-circle text-success mr-1"></i> Nothing is waiting.</div></div>
        <?php endif; ?>
        <?php foreach ($waiting as $r):
            $d = $r['data'];
            $p = (array) ($d['personal'] ?? []);
            $mine = (int) $r['requested_by'] === $me;
        ?>
            <div class="ur-card">
                <div class="d-flex flex-wrap justify-content-between align-items-start" style="gap:8px">
                    <div>
                        <h5><?= $h($r['full_name']) ?></h5>
                        <div class="small text-muted"><?= $h($r['username']) ?> · <?= $h(RoleLabel::of($r['usertype'])) ?></div>
                    </div>
                    <span class="badge badge-<?= UserRequestModel::STATUS_LABELS[$r['status']][1] ?? 'secondary' ?>"><?= $h(UserRequestModel::STATUS_LABELS[$r['status']][0] ?? $r['status']) ?></span>
                </div>
                <div class="ur-track">
                    <span class="st ok"><i class="fas fa-check"></i> Sent by <?= $h($r['requested_name']) ?></span>
                    <i class="fas fa-chevron-right text-muted"></i>
                    <span class="st <?= $r['status'] === 'HR_HEAD' ? 'now' : 'ok' ?>"><?= $r['status'] === 'HR_HEAD' ? '<i class="fas fa-hourglass-half"></i> HR Head' : '<i class="fas fa-check"></i> HR Head: ' . $h($r['hr_head_name']) ?></span>
                    <i class="fas fa-chevron-right text-muted"></i>
                    <span class="st <?= $r['status'] === 'ADMIN' ? 'now' : '' ?>"><i class="fas fa-user-shield"></i> Admin</span>
                </div>
                <div class="ur-meta">
                    <div><span>Employee ID</span><?= $h($d['employee_id'] ?? '') ?></div>
                    <div><span>Branch</span><?= $h($branchNames[(int) ($d['branch_id'] ?? 0)] ?? '—') ?></div>
                    <div><span>Department / position</span><?= $h(($d['department'] ?? '') . ' · ' . ($d['position'] ?? '')) ?></div>
                    <div><span>Email</span><?= $h(($d['email'] ?? '') ?: '—') ?></div>
                    <div><span>Birthday / start date</span><?= $h(($p['birthdate'] ?? '') ?: '—') ?> / <?= $h(($p['date_hired'] ?? '') ?: '—') ?></div>
                    <div><span>Sent</span><?= $h($date($r['requested_at'])) ?></div>
                    <?php if (!empty($d['reason'])): ?><div style="grid-column:1/-1"><span>Note</span><?= $h($d['reason']) ?></div><?php endif; ?>
                </div>
                <?php
                $canDecide = $isAdminArea ? true : ($r['status'] === 'HR_HEAD' && $isHrHead);
                $approveLabel = $isAdminArea ? 'Approve and create the account' : 'Approve (HR Head)';
                $action = $base . ($isAdminArea ? '/admin/user-requests/decide' : '/hr/user-requests/decide');
                ?>
                <div class="ur-actions">
                    <?php if ($canDecide && empty($setupMissing) && ($isAdminArea ? $r['status'] === 'ADMIN' : true)): ?>
                        <form method="POST" action="<?= $h($action) ?>" class="d-inline" onsubmit="return confirm('<?= $isAdminArea ? 'Create this account now?' : 'Approve this request?' ?>');">
                            <input type="hidden" name="csrf_token" value="<?= $h($_SESSION['csrf_token'] ?? '') ?>">
                            <input type="hidden" name="request_id" value="<?= (int) $r['request_id'] ?>">
                            <input type="hidden" name="decision" value="approve">
                            <button type="submit" class="btn btn-sm btn-success"><i class="fas fa-check"></i> <?= $h($approveLabel) ?></button>
                        </form>
                    <?php elseif ($isAdminArea && $r['status'] === 'HR_HEAD'): ?>
                        <span class="small text-muted"><i class="fas fa-info-circle"></i> Waiting for the HR Head first.</span>
                    <?php endif; ?>
                    <?php if ($canDecide && empty($setupMissing)): ?>
                        <form method="POST" action="<?= $h($action) ?>" class="d-flex flex-wrap" style="gap:6px">
                            <input type="hidden" name="csrf_token" value="<?= $h($_SESSION['csrf_token'] ?? '') ?>">
                            <input type="hidden" name="request_id" value="<?= (int) $r['request_id'] ?>">
                            <input type="hidden" name="decision" value="reject">
                            <input type="text" name="note" class="form-control form-control-sm" placeholder="Reason for rejecting" maxlength="255" required>
                            <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-times"></i> Reject</button>
                        </form>
                    <?php endif; ?>
                    <?php if (!$isAdminArea && $mine): ?>
                        <form method="POST" action="<?= $h($action) ?>" class="d-inline" onsubmit="return confirm('Cancel this request?');">
                            <input type="hidden" name="csrf_token" value="<?= $h($_SESSION['csrf_token'] ?? '') ?>">
                            <input type="hidden" name="request_id" value="<?= (int) $r['request_id'] ?>">
                            <input type="hidden" name="decision" value="cancel">
                            <button type="submit" class="btn btn-sm btn-outline-secondary"><i class="fas fa-ban"></i> Cancel my request</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>

        <h6 class="font-weight-bold text-primary mt-4 mb-2"><i class="fas fa-history mr-1"></i> Done</h6>
        <div class="card shadow mb-4">
            <div class="card-body p-0">
                <?php if (!$done): ?>
                    <p class="text-muted p-3 mb-0">No finished requests yet.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead><tr><th>Name</th><th>Role</th><th>Sent by</th><th>Result</th><th>Note</th></tr></thead>
                            <tbody>
                            <?php foreach ($done as $r): ?>
                                <tr>
                                    <td><strong><?= $h($r['full_name']) ?></strong><div class="small text-muted"><?= $h($r['username']) ?></div></td>
                                    <td><?= $h(RoleLabel::of($r['usertype'])) ?></td>
                                    <td><?= $h($r['requested_name']) ?><div class="small text-muted"><?= $h($date($r['requested_at'])) ?></div></td>
                                    <td><span class="badge badge-<?= UserRequestModel::STATUS_LABELS[$r['status']][1] ?? 'secondary' ?>"><?= $h(UserRequestModel::STATUS_LABELS[$r['status']][0] ?? $r['status']) ?></span>
                                        <?php if ($r['admin_name'] || $r['hr_head_name']): ?><div class="small text-muted"><?= $h($r['admin_name'] ?: $r['hr_head_name']) ?></div><?php endif; ?></td>
                                    <td class="small"><?= $h($r['decision_note'] ?? '') ?></td>
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
<script src="<?= $h($base) ?>/assets/vendor/jquery/jquery.min.js"></script>
<script src="<?= $h($base) ?>/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="<?= $h($base) ?>/assets/js/storagemart.min.js"></script>
</body>
</html>
