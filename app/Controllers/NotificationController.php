<?php

require_once __DIR__ . '/AuthController.php';
require_once __DIR__ . '/../Models/NotificationModel.php';

class NotificationController extends AuthController
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

    public function markAllRead()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        header('Content-Type: application/json');

        if (empty($_SESSION['account_id'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Unauthorized']);
            exit;
        }

        $userId = (int) $_SESSION['account_id'];
        $model = new NotificationModel();
        $model->markAllAsRead($userId);

        echo json_encode([
            'success' => true,
            'count' => $model->getUnreadCount($userId),
        ]);
    }

    public function index()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (empty($_SESSION['account_id'])) {
            $_SESSION['loginMessage'] = 'Please log in to view notifications.';
            $this->redirect('/login');
        }

        $ctx = $this->getLoggedUserContext();
        $base = $ctx['base'];
        $loggedFirstname = $ctx['loggedFirstname'];
        $loggedPosition  = $ctx['loggedPosition'];

        $model = new NotificationModel();
        $userId = (int)$_SESSION['account_id'];
        $count = $model->getUnreadCount($userId);
        $notifications = $model->getLatest($userId, 50);

        // choose sidebar/topbar based on role (used by the view)
        $role = strtoupper($_SESSION['usertype'] ?? '');

        require __DIR__ . '/../Views/notifications/index.php';
    }
}
