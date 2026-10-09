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
        require_once __DIR__ . '/../Helpers/TicketAttachments.php';
        $files = TicketAttachments::filesFrom($_FILES['attachment'] ?? null);

        if ($ticketId <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid ticket.']);
            return;
        }

        if ($commentText === '' && !$files) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Type a message or attach a file.']);
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

        $attachment = null;
        if ($files) {
            try {
                $attachment = TicketAttachments::store($files[0], $ticketId);
            } catch (RuntimeException $e) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
                return;
            }
        }

        try {
            $model = new TicketCommentModel();
            $author = $model->getAuthorDisplayInfo($accountId);
            $commentId = $model->addComment(
                $ticketId,
                $accountId,
                $author['role'],
                $author['name'],
                $commentText,
                $attachment
            );

            try {
                $notificationModel = new NotificationModel();
                $notificationModel->notifyTicketComment(
                    $ticketId,
                    $accountId,
                    $author['name'],
                    $author['role'],
                    $commentText !== '' ? $commentText
                        : ($attachment && TicketAttachments::isImageMime($attachment['mime']) ? '[sent a photo]' : '[sent a file]')
                );
            } catch (Throwable $notifyError) {
                error_log('Ticket comment notification failed: ' . $notifyError->getMessage());
            }

            echo json_encode([
                'success' => true,
                'message' => 'Comment posted.',
                'comment' => TicketCommentModel::presentComment([
                    'comment_id'      => $commentId,
                    'ticket_id'       => $ticketId,
                    'account_id'      => $accountId,
                    'author_role'     => $author['role'],
                    'author_name'     => $author['name'],
                    'comment_text'    => $commentText,
                    'created_at'      => date('Y-m-d H:i:s'),
                    'attachment_path' => $attachment['path'] ?? null,
                    'attachment_name' => $attachment['name'] ?? null,
                    'attachment_mime' => $attachment['mime'] ?? null,
                    'attachment_size' => $attachment['size'] ?? null,
                ]),
            ]);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to post comment.']);
        }
    }

    /** GET /ticket-comments/file?id= : a chat attachment, for people who can see the ticket. */
    public function downloadAttachment(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $accountId = (int) ($_SESSION['account_id'] ?? 0);
        $model = new TicketCommentModel();
        $comment = $accountId > 0 ? $model->getCommentById((int) ($_GET['id'] ?? 0)) : null;
        $access = $comment ? $this->resolveAccess((int) $comment['ticket_id'], $accountId, strtoupper((string) ($_SESSION['usertype'] ?? ''))) : null;
        require_once __DIR__ . '/../Helpers/TicketAttachments.php';
        $path = ($comment && !empty($comment['attachment_path'])) ? TicketAttachments::absolutePath((string) $comment['attachment_path']) : null;
        if (!$access || !$access['canView'] || $path === null) {
            http_response_code($accountId > 0 ? 404 : 401);
            echo 'File not found.';
            return;
        }
        $mime = (string) $comment['attachment_mime'];
        $inline = TicketAttachments::isImageMime($mime) || $mime === 'application/pdf';
        $name = str_replace(['"', "\r", "\n"], '', (string) $comment['attachment_name']);
        header('Content-Type: ' . ($inline ? $mime : 'application/octet-stream'));
        header('Content-Length: ' . filesize($path));
        header('X-Content-Type-Options: nosniff');
        header('Cache-Control: private, max-age=86400');
        header('Content-Disposition: ' . ($inline ? 'inline' : 'attachment') . '; filename="' . $name . '"');
        readfile($path);
    }

    /**
     * Who may read / post on a ticket's comment thread: the shared ticket visibility rules
     * (TicketAccess), plus the ticket's own requester or creator in any role, plus Operations
     * managers on Operations tickets (they manage those from the HOM ticket pages).
     * Previously every signed-in user could read and post on every ticket.
     */
    private function resolveAccess(int $ticketId, int $accountId, string $usertype): array
    {
        $model = new TicketCommentModel();

        if ($accountId <= 0 || !$model->ticketExists($ticketId)) {
            return ['canView' => false, 'canPost' => false];
        }

        global $pdo;
        require_once __DIR__ . '/../Helpers/TicketAccess.php';
        $ticket = TicketAccess::fetchTicketRow($pdo, $ticketId);
        if ($ticket === null) {
            return ['canView' => false, 'canPost' => false];
        }

        $allowed = TicketAccess::canViewTicket($pdo, $ticket, $accountId, $usertype);

        if (!$allowed) {
            $ownEmployeeId = (int) ($model->getEmployeeIdByAccountId($accountId) ?? 0);
            $allowed = ($ownEmployeeId > 0 && (int) $ticket['employee_id'] === $ownEmployeeId)
                || (int) ($ticket['created_by'] ?? 0) === $accountId;
        }

        if (!$allowed && in_array($usertype, ['HOM', 'OM'], true)) {
            $stmt = $pdo->prepare('SELECT e.department FROM tbltickets t JOIN tblemployee e ON e.employee_id = t.employee_id
                                   WHERE t.ticket_id = ? LIMIT 1');
            $stmt->execute([$ticketId]);
            $allowed = strcasecmp((string) $stmt->fetchColumn(), 'Operations') === 0;
        }

        return ['canView' => $allowed, 'canPost' => $allowed];
    }
}
