<?php
require_once dirname(__DIR__, 2) . '/Helpers/HrDepartmentAccess.php';

$activePage = $activePage ?? 'uniforms';

if (HrDepartmentAccess::isHrDepartmentHead()) {
    require_once dirname(__DIR__, 2) . '/Models/NotificationModel.php';

    $accountId = (int) ($_SESSION['account_id'] ?? 0);
    $notificationModel = new NotificationModel();
    $count = (int) ($count ?? $notificationModel->getUnreadCount($accountId));
    $notifications = $notifications ?? $notificationModel->getLatest($accountId, 10);

    $loggedFirstname = $loggedFirstname ?? ($_SESSION['display_firstname'] ?? $_SESSION['username'] ?? '');
    $loggedPosition = $loggedPosition ?? ($_SESSION['display_position'] ?? '');

    require __DIR__ . '/head/sidebar_topbar.php';
    return;
}

require __DIR__ . '/hr/sidebar_topbar.php';
