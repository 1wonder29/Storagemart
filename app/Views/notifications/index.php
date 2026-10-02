<?php
/** @var string $base */
/** @var string $loggedFirstname */
/** @var string $loggedPosition */
/** @var int $count */
/** @var array $notifications */
/** @var string $role */

$base = rtrim($base ?? (defined('BASE_URL') ? BASE_URL : '/'), '/');
$totalAlerts = count($notifications ?? []);
$unreadCount = (int) ($count ?? 0);
$readCount = max(0, $totalAlerts - $unreadCount);

if (!function_exists('tms_notification_time')) {
    function tms_notification_time(?string $createdAt): string
    {
        if (!$createdAt) {
            return '';
        }
        $ts = strtotime($createdAt);
        if ($ts === false) {
            return '';
        }
        return date('M d, Y, g:i A', $ts);
    }
}
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
    <link rel="icon" href="<?= htmlspecialchars($base) ?>/assets/img/favicon.ico" type="image/x-icon">
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
        case 'HOM':
            require_once __DIR__ . '/../partials/om/sidebar_topbar.php';
            break;
        default:
            require_once __DIR__ . '/../partials/employee/sidebar_topbar.php';
            break;
    }
    ?>

    <div class="container-fluid alerts-center-page">
        <div class="page-hero hero-alerts">
            <div class="row align-items-center">
                <div class="col-lg-7">
                    <h1><i class="fas fa-bell mr-2"></i>Alerts Center</h1>
                    <p>Stay on top of new tickets, comments, and updates across the system.</p>
                </div>
                <div class="col-lg-5">
                    <div class="row mt-3 mt-lg-0">
                        <div class="col-4">
                            <div class="hero-stat">
                                <div class="stat-value"><?= (int) $totalAlerts ?></div>
                                <div class="stat-label">Shown</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="hero-stat">
                                <div class="stat-value" id="alertsUnreadStat"><?= (int) $unreadCount ?></div>
                                <div class="stat-label">Unread</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="hero-stat">
                                <div class="stat-value" id="alertsReadStat"><?= (int) $readCount ?></div>
                                <div class="stat-label">Read</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="alerts-feed-wrap">
            <div class="alerts-feed-card shadow">
                <div class="alerts-feed-header">
                    <div class="alerts-feed-title">
                        <i class="fas fa-inbox"></i>
                        <span>Latest Alerts</span>
                    </div>
                    <div class="alerts-feed-actions">
                        <?php if ($unreadCount > 0): ?>
                            <button type="button" class="notification-mark-all-read alerts-mark-all-read" aria-label="Mark all notifications as read">
                                Mark all read
                            </button>
                        <?php endif; ?>
                        <span class="alerts-unread-pill<?= $unreadCount > 0 ? '' : ' d-none' ?>" id="alertsUnreadPill">
                            <?= $unreadCount > 9 ? '9+' : $unreadCount ?> unread
                        </span>
                    </div>
                </div>

                <?php if (empty($notifications)): ?>
                    <div class="notification-empty">
                        <i class="fas fa-check-circle d-block mb-2"></i>
                        You're all caught up — no alerts yet.
                    </div>
                <?php else: ?>
                    <div class="alerts-feed-list" id="alertsFeedList">
                        <?php foreach ($notifications as $n):
                            $isRead = !empty($n['is_read']);
                            $bgColor = (string) ($n['bg_color'] ?? 'primary');
                            $icon = (string) ($n['icon'] ?? 'fa-bell');
                        ?>
                            <a class="notification-item <?= $isRead ? 'notification-read' : 'notification-unread' ?>"
                               href="<?= htmlspecialchars((string) ($n['action_url'] ?? '#')) ?>"
                               data-id="<?= (int) ($n['id'] ?? 0) ?>"
                               data-related="<?= (int) ($n['related_id'] ?? 0) ?>">
                                <span class="notification-indicator" aria-hidden="true"></span>
                                <div class="notification-icon bg-<?= htmlspecialchars($bgColor) ?>">
                                    <i class="fas <?= htmlspecialchars($icon) ?>"></i>
                                </div>
                                <div class="notification-body">
                                    <div class="notification-message"><?= htmlspecialchars((string) ($n['message'] ?? '')) ?></div>
                                    <div class="notification-time"><?= htmlspecialchars(tms_notification_time($n['created_at'] ?? null)) ?></div>
                                </div>
                                <span class="alerts-feed-chevron" aria-hidden="true">
                                    <i class="fas fa-chevron-right"></i>
                                </span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
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
    <?php require __DIR__ . '/../partials/realtime_scripts.php'; ?>
    <?php require __DIR__ . '/../partials/flash_modal.php'; ?>
</body>
</html>
