<?php
$base = rtrim(BASE_URL, '/');
$fullName = trim(
    ($profile['firstname'] ?? '') . ' ' .
    ($profile['middlename'] ?? '') . ' ' .
    ($profile['lastname'] ?? '')
);
$fullName = preg_replace('/\s+/', ' ', $fullName);
$status = strtolower($profile['status'] ?? '');
$isActive = in_array($status, ['active', '1', 'enabled'], true);
$dateCreated = $profile['account_datecreated'] ?? $profile['employee_datecreated'] ?? null;
$dateCreatedLabel = $dateCreated ? date('M d, Y', strtotime($dateCreated)) : '-';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Storage Mart | AOM Profile</title>

    <link href="<?= htmlspecialchars($base) ?>/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900" rel="stylesheet">
    <link rel="icon" href="<?= htmlspecialchars($base) ?>/assets/img/sm_favicon.png" type="image/x-icon">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/storagemart.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/aom-profile.css" rel="stylesheet">
</head>
<body id="page-top">

<div id="wrapper">
    <?php
    $activePage = 'profile';
    require_once __DIR__ . '/../../partials/aom/sidebar_topbar.php';
    ?>

    <div class="container-fluid aom-profile-page">

        <!-- Profile Hero -->
        <div class="profile-hero">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <div class="d-flex align-items-center">
                        <div class="profile-avatar mr-3">
                            <i class="fas fa-user-tie"></i>
                        </div>
                        <div>
                            <h1><?= htmlspecialchars($fullName !== '' ? $fullName : ($loggedFirstname . ' ' . $loggedLastname)) ?></h1>
                            <div class="profile-role">
                                <?= htmlspecialchars($profile['position'] ?? $loggedPosition ?: 'Area Operation Manager') ?>
                            </div>
                            <span class="profile-badge <?= $isActive ? 'badge-active' : 'badge-inactive' ?>">
                                <?= htmlspecialchars(ucfirst($profile['status'] ?? 'Unknown')) ?>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 mt-4 mt-lg-0">
                    <div class="d-flex justify-content-lg-end flex-wrap" style="gap: 0.65rem;">
                        <div class="hero-stat">
                            <div class="stat-value"><?= (int)($stats['total_branches'] ?? 0) ?></div>
                            <div class="stat-label">Branches</div>
                        </div>
                        <div class="hero-stat">
                            <div class="stat-value"><?= (int)($stats['total_employees'] ?? 0) ?></div>
                            <div class="stat-label">Employees</div>
                        </div>
                        <div class="hero-stat">
                            <div class="stat-value"><?= (int)($stats['pending_tickets'] ?? 0) ?></div>
                            <div class="stat-label">Pending</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Account Information -->
            <div class="col-lg-7 mb-4">
                <div class="card profile-card shadow">
                    <div class="card-header">
                        <h6><i class="fas fa-id-card"></i>Account Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="info-row">
                            <div class="info-icon"><i class="fas fa-user"></i></div>
                            <div>
                                <div class="info-label">Username</div>
                                <div class="info-value"><?= htmlspecialchars($profile['username'] ?? '-') ?></div>
                            </div>
                        </div>
                        <div class="info-row">
                            <div class="info-icon"><i class="fas fa-hashtag"></i></div>
                            <div>
                                <div class="info-label">Employee ID</div>
                                <div class="info-value"><?= htmlspecialchars((string)($profile['employee_id'] ?? '-')) ?></div>
                            </div>
                        </div>
                        <div class="info-row">
                            <div class="info-icon"><i class="fas fa-shield-alt"></i></div>
                            <div>
                                <div class="info-label">User Type</div>
                                <div class="info-value"><?= htmlspecialchars($profile['usertype'] ?? 'AOM') ?></div>
                            </div>
                        </div>
                        <div class="info-row">
                            <div class="info-icon"><i class="fas fa-building"></i></div>
                            <div>
                                <div class="info-label">Department</div>
                                <div class="info-value"><?= htmlspecialchars($profile['department'] ?? '-') ?></div>
                            </div>
                        </div>
                        <div class="info-row">
                            <div class="info-icon"><i class="fas fa-envelope"></i></div>
                            <div>
                                <div class="info-label">Email</div>
                                <div class="info-value"><?= htmlspecialchars($profile['email'] ?? '-') ?></div>
                            </div>
                        </div>
                        <div class="info-row">
                            <div class="info-icon"><i class="fas fa-map-marker-alt"></i></div>
                            <div>
                                <div class="info-label">Home Branch</div>
                                <div class="info-value"><?= htmlspecialchars($profile['branchName'] ?? '-') ?></div>
                            </div>
                        </div>
                        <div class="info-row">
                            <div class="info-icon"><i class="fas fa-calendar-alt"></i></div>
                            <div>
                                <div class="info-label">Member Since</div>
                                <div class="info-value"><?= htmlspecialchars($dateCreatedLabel) ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Assigned Branches -->
            <div class="col-lg-5 mb-4">
                <div class="card profile-card shadow">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h6><i class="fas fa-store"></i>Assigned Branches</h6>
                        <a href="<?= htmlspecialchars($base) ?>/aom/dashboard" class="btn btn-sm btn-outline-primary" style="border-radius:2rem;font-size:0.75rem;">
                            View Dashboard
                        </a>
                    </div>
                    <div class="card-body" style="max-height:420px;overflow-y:auto;">
                        <?php if (!empty($branches)): ?>
                            <?php foreach ($branches as $branch): ?>
                                <div class="branch-item">
                                    <div class="branch-icon">
                                        <i class="fas fa-store"></i>
                                    </div>
                                    <div>
                                        <div class="branch-name"><?= htmlspecialchars($branch['branchName'] ?? '-') ?></div>
                                        <div class="branch-meta">
                                            <?= htmlspecialchars($branch['branchCode'] ?? '') ?>
                                            <?php if (!empty($branch['branchAddress'])): ?>
                                                &middot; <?= htmlspecialchars($branch['branchAddress']) ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <span class="branch-count">
                                        <?= (int)($branch['employee_count'] ?? 0) ?> staff
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="empty-branches">
                                <i class="fas fa-store-slash"></i>
                                <p class="mb-0">No branches assigned yet.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <?php
                $history_title = 'Branch Assignment Transfer History';
                require __DIR__ . '/../../partials/aom/branch_assignment_history.php';
                ?>
            </div>
        </div>

    </div>
    <!-- End of Page Content -->

    </div>
    <!-- End of Main Content -->

</div>
<!-- End of Content Wrapper -->

</div>
<!-- End of Page Wrapper -->

<script src="<?= htmlspecialchars($base) ?>/assets/vendor/jquery/jquery.min.js"></script>
<script src="<?= htmlspecialchars($base) ?>/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="<?= htmlspecialchars($base) ?>/assets/vendor/jquery-easing/jquery.easing.min.js"></script>
<script src="<?= htmlspecialchars($base) ?>/assets/js/sb-admin-2.min.js"></script>
</body>
</html>
