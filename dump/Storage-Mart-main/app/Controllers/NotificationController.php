<?php

require_once __DIR__ . '/../Models/NotificationModel.php';

class NotificationController
{
    public function getData($accountId)
    {
        $notificationModel = new NotificationModel();

        return [
            'count' => $notificationModel->getUnreadCount($accountId),
            'notifications' => $notificationModel->getLatest($accountId, 5)
        ];
    }

    public function markRead()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (empty($_SESSION['account_id']) || empty($_POST['id'])) {
            http_response_code(400);
            exit;
        }

        $notificationId = (int) $_POST['id'];
        $userId = (int) $_SESSION['account_id'];

        $model = new NotificationModel();
        $model->markAsRead($notificationId, $userId);

        echo json_encode(['success' => true]);
    }
}
