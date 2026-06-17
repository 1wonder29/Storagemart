<?php
$base = rtrim(BASE_URL, '/');
$count = (int) ($count ?? 0);
$notifications = $notifications ?? [];
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
    <a class="nav-link dropdown-toggle" href="#" id="alertsDropdown"
       role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        <i class="fas fa-bell fa-fw"></i>
        <?php if ($count > 0): ?>
            <span class="badge badge-danger badge-counter"><?= $count > 9 ? '9+' : $count ?></span>
        <?php endif; ?>
    </a>

    <div class="dropdown-menu dropdown-menu-right shadow notification-dropdown animated--grow-in"
         aria-labelledby="alertsDropdown">

        <div class="notification-dropdown-header">
            <div class="notification-dropdown-title">
                <i class="fas fa-bell"></i>
                <span>Notifications</span>
            </div>
            <?php if ($count > 0): ?>
                <span class="notification-unread-pill"><?= $count > 9 ? '9+' : $count ?> new</span>
            <?php endif; ?>
        </div>

        <?php if (empty($notifications)): ?>
            <div class="notification-empty">
                <i class="fas fa-check-circle d-block mb-2"></i>
                You're all caught up
            </div>
        <?php else: ?>
            <div class="notification-scroll">
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
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="notification-dropdown-footer">
            <a href="<?= htmlspecialchars($notifShowAllUrl) ?>">View all notifications</a>
        </div>
    </div>
</li>
