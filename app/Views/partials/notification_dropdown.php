<?php
require_once __DIR__ . '/../../Helpers/AppUrl.php';
$base = rtrim(BASE_URL, '/');
// Always read the bell from the database: some pages never loaded it, and others reuse
// $count as a loop variable (the HOM dashboard showed "1 new" with an empty list).
$bellCount = 0;
$bellItems = [];
if (!empty($_SESSION['account_id'])) {
    try {
        require_once __DIR__ . '/../../Models/NotificationModel.php';
        $bellModel = new NotificationModel();
        $bellCount = $bellModel->getUnreadCount((int) $_SESSION['account_id']);
        $bellItems = $bellModel->getForBell((int) $_SESSION['account_id']);
    } catch (Throwable $e) {
        error_log('Notification bell failed: ' . $e->getMessage());
    }
}
$notifShowAllUrl = $notifShowAllUrl ?? ($base . '/notifications');

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
<li class="nav-item dropdown no-arrow mx-1">
    <a class="nav-link dropdown-toggle topbar-icon-btn" href="#" id="alertsDropdown"
       role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        <i class="fas fa-bell fa-fw"></i>
        <?php if ($bellCount > 0): ?>
            <span class="badge badge-danger badge-counter"><?= $bellCount > 9 ? '9+' : $bellCount ?></span>
        <?php endif; ?>
    </a>

    <div class="dropdown-menu dropdown-menu-right shadow notification-dropdown animated--grow-in"
         aria-labelledby="alertsDropdown">

        <div class="notification-dropdown-header">
            <div class="notification-dropdown-title">
                <i class="fas fa-bell"></i>
                <span>Notifications</span>
            </div>
            <div class="notification-dropdown-actions">
                <?php if ($bellCount > 0): ?>
                    <button type="button" class="notification-mark-all-read" aria-label="Mark all notifications as read">
                        Mark all read
                    </button>
                    <span class="notification-unread-pill"><?= $bellCount > 9 ? '9+' : $bellCount ?> new</span>
                <?php endif; ?>
            </div>
        </div>

        <?php if (empty($bellItems)): ?>
            <div class="notification-empty">
                <i class="fas fa-check-circle d-block mb-2"></i>
                You're all caught up
            </div>
        <?php else: ?>
            <div class="notification-scroll">
                <?php foreach ($bellItems as $n):
                    $isRead = !empty($n['is_read']);
                    $bgColor = (string) ($n['bg_color'] ?? 'primary');
                    $icon = (string) ($n['icon'] ?? 'fa-bell');
                ?>
                    <a class="notification-item <?= $isRead ? 'notification-read' : 'notification-unread' ?>"
                       href="<?= htmlspecialchars(AppUrl::resolve($n['action_url'] ?? null)) ?>"
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
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="notification-dropdown-footer">
            <a href="<?= htmlspecialchars($notifShowAllUrl) ?>">View all notifications</a>
        </div>
    </div>
</li>
