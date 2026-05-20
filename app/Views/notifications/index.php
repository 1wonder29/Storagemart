<?php
/** @var string $base */
/** @var string $loggedFirstname */
/** @var string $loggedPosition */
/** @var int $count */
/** @var array $notifications */
/** @var string $role */

$base = rtrim($base ?? (defined('BASE_URL') ? BASE_URL : '/'), '/');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Storage Mart | Alerts Center</title>

    <link href="<?= htmlspecialchars($base) ?>/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/storagemart.css" rel="stylesheet">
</head>
<body id="page-top">
<div id="wrapper">
    <?php
    $activePage = 'notifications';
    switch (strtoupper($role ?? '')) {
        case 'ADMIN':
            require_once __DIR__ . '/../partials/admin/sidebar_topbar.php';
            break;
        case 'IT':
            require_once __DIR__ . '/../partials/it/sidebar_topbar.php';
            break;
        case 'HR':
            require_once __DIR__ . '/../partials/hr/sidebar_topbar.php';
            break;
        case 'HEAD':
            require_once __DIR__ . '/../partials/head/sidebar_topbar.php';
            break;
        case 'AOM':
            require_once __DIR__ . '/../partials/aom/sidebar_topbar.php';
            break;
        case 'OM':
            require_once __DIR__ . '/../partials/om/sidebar_topbar.php';
            break;
        default:
            require_once __DIR__ . '/../partials/employee/sidebar_topbar.php';
            break;
    }
    ?>

    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Alerts Center</h1>
            <span class="small text-muted">
                Unread: <?= (int)($count ?? 0) ?>
            </span>
        </div>

        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Latest Alerts</h6>
            </div>
            <div class="card-body p-0">
                <?php if (empty($notifications)): ?>
                    <div class="p-4 text-center text-muted">
                        No alerts yet.
                    </div>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($notifications as $n): ?>
                            <a class="list-group-item list-group-item-action d-flex align-items-center notification-item <?= !empty($n['is_read']) ? 'notification-read' : 'notification-unread' ?>"
                               href="<?= htmlspecialchars($n['action_url'] ?? '#') ?>"
                               data-id="<?= (int)$n['id'] ?>">
                                <div class="mr-3">
                                    <div class="icon-circle bg-<?= htmlspecialchars($n['bg_color'] ?? 'primary') ?>">
                                        <i class="fas <?= htmlspecialchars($n['icon'] ?? 'fa-bell') ?> text-white"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="small text-gray-500">
                                        <?= !empty($n['created_at']) ? date('F d, Y h:i A', strtotime($n['created_at'])) : '' ?>
                                    </div>
                                    <div><?= htmlspecialchars($n['message'] ?? '') ?></div>
                                </div>
                                <div class="ml-2 text-gray-400">
                                    <i class="fas fa-chevron-right"></i>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

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

