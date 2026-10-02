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
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        header('Content-Type: application/json; charset=utf-8');

        $notificationId = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
        if (empty($_SESSION['account_id']) || $notificationId === false || $notificationId <= 0) {
            http_response_code(empty($_SESSION['account_id']) ? 401 : 400);
            echo json_encode(['success' => false, 'error' => 'Invalid notification request.']);
            exit;
        }

        $userId = (int) $_SESSION['account_id'];

        try {
            $model = new NotificationModel();
            $updated = $model->markAsRead($notificationId, $userId);
            if (!$updated) {
                throw new RuntimeException('Notification could not be marked as read.');
            }

            echo json_encode(['success' => true]);
        } catch (Throwable $e) {
            error_log('NotificationController::markRead failed: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Could not mark notification as read.']);
        }
    }

    public function markAllRead()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        header('Content-Type: application/json; charset=utf-8');

        if (empty($_SESSION['account_id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Unauthorized']);
            exit;
        }

        $userId = (int) $_SESSION['account_id'];
        try {
            $model = new NotificationModel();
            if (!$model->markAllAsRead($userId)) {
                throw new RuntimeException('Notifications could not be marked as read.');
            }

            echo json_encode([
                'success' => true,
                'count' => $model->getUnreadCount($userId),
            ]);
        } catch (Throwable $e) {
            error_log('NotificationController::markAllRead failed: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Could not mark notifications as read.']);
        }
    }

    public function index()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

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
