<?php

require_once __DIR__ . '/AuthController.php';
require_once __DIR__ . '/../Models/TicketCommentModel.php';
require_once __DIR__ . '/../Models/NotificationModel.php';

class TicketCommentController extends AuthController
{
    public function fetchComments(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        header('Content-Type: application/json; charset=utf-8');

        if (empty($_SESSION['account_id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized', 'comments' => []]);
            return;
        }

        $ticketId = (int) ($_GET['ticket_id'] ?? 0);
        if ($ticketId <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid ticket.', 'comments' => []]);
            return;
        }

        $access = $this->resolveAccess($ticketId, (int) $_SESSION['account_id'], strtoupper((string) ($_SESSION['usertype'] ?? '')));
        if (!$access['canView']) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Access denied.', 'comments' => []]);
            return;
        }

        try {
            $model = new TicketCommentModel();
            $sinceId = (int) ($_GET['since_id'] ?? 0);
            $comments = $sinceId > 0
                ? $model->getCommentsSince($ticketId, $sinceId)
                : $model->getCommentsByTicketId($ticketId);
            echo json_encode([
                'success'  => true,
                'comments' => $comments,
                'canPost'  => $access['canPost'],
                'partial'  => $sinceId > 0,
            ]);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to load comments.', 'comments' => []]);
        }
    }

    public function addComment(): void
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

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
            return;
        }

        $ticketId = (int) ($_POST['ticket_id'] ?? 0);
        $commentText = trim((string) ($_POST['comment'] ?? ''));

        if ($ticketId <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid ticket.']);
            return;
        }

        if ($commentText === '') {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Comment cannot be empty.']);
            return;
        }

        if (mb_strlen($commentText) > 2000) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Comment is too long (max 2000 characters).']);
            return;
        }

        $accountId = (int) $_SESSION['account_id'];
        $usertype = strtoupper((string) ($_SESSION['usertype'] ?? ''));
        $access = $this->resolveAccess($ticketId, $accountId, $usertype);

        if (!$access['canPost']) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'You are not allowed to comment on this ticket.']);
            return;
        }

        try {
            $model = new TicketCommentModel();
            $author = $model->getAuthorDisplayInfo($accountId);
            $commentId = $model->addComment(
                $ticketId,
                $accountId,
                $author['role'],
                $author['name'],
                $commentText
            );

            try {
                $notificationModel = new NotificationModel();
                $notificationModel->notifyTicketComment(
                    $ticketId,
                    $accountId,
                    $author['name'],
                    $author['role'],
                    $commentText
                );
            } catch (Throwable $notifyError) {
                error_log('Ticket comment notification failed: ' . $notifyError->getMessage());
            }

            echo json_encode([
                'success' => true,
                'message' => 'Comment posted.',
                'comment' => [
                    'comment_id'   => $commentId,
                    'ticket_id'    => $ticketId,
                    'account_id'   => $accountId,
                    'author_role'  => $author['role'],
                    'author_name'  => $author['name'],
                    'comment_text' => $commentText,
                    'created_at'   => date('Y-m-d H:i:s'),
                ],
            ]);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to post comment.']);
        }
    }

    private function resolveAccess(int $ticketId, int $accountId, string $usertype): array
    {
        $model = new TicketCommentModel();

        if (!$model->ticketExists($ticketId)) {
            return ['canView' => false, 'canPost' => false];
        }

        if ($accountId <= 0) {
            return ['canView' => false, 'canPost' => false];
        }

        // All authenticated roles can view and post on tickets in the shared thread
        return ['canView' => true, 'canPost' => true];
    }
}
