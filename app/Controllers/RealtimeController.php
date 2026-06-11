<?php

require_once __DIR__ . '/AuthController.php';
require_once __DIR__ . '/../Models/NotificationModel.php';
require_once __DIR__ . '/../Models/RealtimeModel.php';

class RealtimeController extends AuthController
{
    public function pollNotifications(): void
    {
        $this->jsonAuthRequired(function (int $accountId) {
            $model = new NotificationModel();
            echo json_encode([
                'success'       => true,
                'count'         => $model->getUnreadCount($accountId),
                'notifications' => $model->getLatest($accountId, 5),
                'server_time'   => date('Y-m-d H:i:s'),
            ]);
        });
    }

    public function pollTickets(): void
    {
        $this->jsonAuthRequired(function (int $accountId) {
            $since = trim((string) ($_GET['since'] ?? ''));
            $ticketId = (int) ($_GET['ticket_id'] ?? 0);
            $usertype = strtoupper((string) ($_SESSION['usertype'] ?? ''));

            $model = new RealtimeModel();

            if ($ticketId > 0) {
                $ticket = $model->getTicketSnapshot($ticketId, $accountId, $usertype);
                echo json_encode([
                    'success'     => true,
                    'ticket'      => $ticket,
                    'server_time' => date('Y-m-d H:i:s'),
                ]);
                return;
            }

            $updates = $model->getTicketUpdates($accountId, $usertype, $since);
            echo json_encode([
                'success'     => true,
                'tickets'     => $updates,
                'server_time' => date('Y-m-d H:i:s'),
            ]);
        });
    }

    private function jsonAuthRequired(callable $handler): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        header('Content-Type: application/json; charset=utf-8');

        if (empty($_SESSION['account_id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }

        try {
            $handler((int) $_SESSION['account_id']);
        } catch (Throwable $e) {
            error_log('Realtime poll error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Poll failed.']);
        }
    }
}
