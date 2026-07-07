<?php
$base = rtrim(BASE_URL, '/');
$fullName = trim(($profile['lastname'] ?? '') . ', ' . ($profile['firstname'] ?? '') . ' ' . ($profile['middlename'] ?? ''));
$displayName = $fullName !== ',' ? trim($fullName) : '-';
$dateCreated = $profile['account_datecreated'] ?? $profile['datecreated'] ?? null;
$dateCreatedLabel = $dateCreated ? date('M d, Y', strtotime($dateCreated)) : '-';
$positionLabel = trim((string) ($profile['position'] ?? ''));
if ($positionLabel === '') {
    $positionLabel = trim((string) ($profile['usertype'] ?? ''));
}
$profileValue = static function ($value): string {
    $text = trim((string) $value);
    return $text !== '' ? $text : '—';
};
$profileFields = [
    ['label' => 'Full Name', 'value' => $displayName, 'icon' => 'fa-user', 'tone' => 'tone-user'],
    ['label' => 'Username', 'value' => $profile['username'] ?? '', 'icon' => 'fa-at', 'tone' => 'tone-account'],
    ['label' => 'Employee ID', 'value' => $profile['employee_id'] ?? '', 'icon' => 'fa-fingerprint', 'tone' => 'tone-id'],
    ['label' => 'User Type', 'value' => $profile['usertype'] ?? '', 'icon' => 'fa-user-tag', 'tone' => 'tone-type'],
    ['label' => 'Department', 'value' => $profile['department'] ?? '', 'icon' => 'fa-building', 'tone' => 'tone-dept'],
    ['label' => 'Position', 'value' => $positionLabel, 'icon' => 'fa-briefcase', 'tone' => 'tone-role'],
    ['label' => 'Email', 'value' => $profile['email'] ?? '', 'icon' => 'fa-envelope', 'tone' => 'tone-email'],
    ['label' => 'Branch', 'value' => $profile['branchName'] ?? '', 'icon' => 'fa-map-marker-alt', 'tone' => 'tone-branch'],
    ['label' => 'Status', 'value' => $profile['status'] ?? '', 'icon' => 'fa-shield-alt', 'tone' => 'tone-status', 'badge' => true],
    ['label' => 'Date Created', 'value' => $dateCreatedLabel, 'icon' => 'fa-calendar-alt', 'tone' => 'tone-date'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Storage Mart | Admin Profile</title>

    <link href="<?= htmlspecialchars($base) ?>/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/storagemart.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/profile-page.css?v=2" rel="stylesheet">
</head>
<body id="page-top">
<div id="wrapper">
    <?php
    $activePage = 'profile';
    require_once __DIR__ . '/../../partials/admin/sidebar_topbar.php';
    ?>

    <div class="container-fluid profile-page theme-admin">

        <div class="profile-hero">
            <div class="d-flex align-items-center flex-wrap">
                <div class="profile-avatar mr-3 mb-3 mb-md-0">
                    <i class="fas fa-user-shield"></i>
                </div>
                <div>
                    <h1><?= htmlspecialchars($displayName) ?></h1>
                    <div class="profile-role"><?= htmlspecialchars($profileValue($positionLabel)) ?></div>
                    <?php if (!empty($profile['status'])): ?>
                        <span class="profile-badge profile-badge-hero"><?= htmlspecialchars($profile['status']) ?></span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="card profile-card profile-card-modern shadow mb-4">
            <div class="card-header">
                <div>
                    <h6><i class="fas fa-id-card"></i>Account Information</h6>
                    <p class="profile-card-subtitle mb-0">Your account and employee details at a glance.</p>
                </div>
            </div>
            <div class="card-body">
                <div class="profile-info-grid">
                    <?php foreach ($profileFields as $field):
                        $value = $profileValue($field['value'] ?? '');
                        $isStatus = !empty($field['badge']);
                        $statusClass = $isStatus ? 'profile-status-pill ' . (strcasecmp($value, 'ACTIVE') === 0 ? 'is-active' : 'is-default') : '';
                    ?>
                        <div class="profile-info-item">
                            <div class="profile-info-icon <?= htmlspecialchars($field['tone']) ?>">
                                <i class="fas <?= htmlspecialchars($field['icon']) ?>" aria-hidden="true"></i>
                            </div>
                            <div class="profile-info-content">
                                <span class="profile-info-label"><?= htmlspecialchars($field['label']) ?></span>
                                <?php if ($isStatus && $value !== '—'): ?>
                                    <span class="<?= htmlspecialchars($statusClass) ?>"><?= htmlspecialchars($value) ?></span>
                                <?php else: ?>
                                    <span class="profile-info-value<?= $value === '—' ? ' is-empty' : '' ?>"><?= htmlspecialchars($value) ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

            </div>
        </div>
</div>

<script src="<?= htmlspecialchars($base) ?>/assets/vendor/jquery/jquery.min.js"></script>
<script src="<?= htmlspecialchars($base) ?>/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="<?= htmlspecialchars($base) ?>/assets/vendor/jquery-easing/jquery.easing.min.js"></script>
<script src="<?= htmlspecialchars($base) ?>/assets/js/sb-admin-2.min.js"></script>
</body>
</html>
