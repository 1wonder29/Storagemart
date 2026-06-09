<?php

require_once __DIR__ . '/AuthController.php';
require_once __DIR__ . '/../Models/TicketCancelModel.php';
require_once __DIR__ . '/../Models/NotificationModel.php';
require_once __DIR__ . '/../Models/admin/Logger.php';

class TicketCancelController extends AuthController
{
    public function cancel(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $isAjax = (
            !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
        ) || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

        if (empty($_SESSION['account_id'])) {
            $this->respond(false, 'Unauthorized.', $isAjax, 401);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->respond(false, 'Invalid request method.', $isAjax, 405);
            return;
        }

        $ticketId = (int) ($_POST['ticket_id'] ?? 0);
        $reason = trim((string) ($_POST['cancel_reason'] ?? ''));

        if ($ticketId <= 0) {
            $this->respond(false, 'Invalid ticket.', $isAjax, 400);
            return;
        }

        if ($reason === '') {
            $this->respond(false, 'Please provide a reason for cancellation.', $isAjax, 400);
            return;
        }

        if (mb_strlen($reason) > 500) {
            $this->respond(false, 'Cancellation reason is too long (max 500 characters).', $isAjax, 400);
            return;
        }

        $accountId = (int) $_SESSION['account_id'];
        $usertype = strtoupper((string) ($_SESSION['usertype'] ?? ''));

        $model = new TicketCancelModel();

        if (!$model->canUserCancelTicket($ticketId, $accountId, $usertype)) {
            $this->respond(false, 'You are not allowed to cancel this ticket.', $isAjax, 403);
            return;
        }

        $performedRole = TicketCancelModel::mapPerformedRole($usertype);
        $ok = $model->cancelTicket($ticketId, $reason, $accountId, $performedRole);

        if (!$ok) {
            $this->respond(false, 'Failed to cancel ticket. It may no longer be cancellable.', $isAjax, 500);
            return;
        }

        $ticket = $model->getTicketRow($ticketId);
        $ticketNumber = $ticket['ticket_number'] ?? ('#' . $ticketId);

        $logger = new Logger();
        $logger->log('Cancel', 'Ticket Management', (string) $ticketId, $_SESSION['username'] ?? 'Unknown');

        $ownerAccountId = $model->getEmployeeAccountIdByTicketId($ticketId);
        if ($ownerAccountId && $ownerAccountId !== $accountId) {
            $notificationModel = new NotificationModel();
            $notificationModel->create(
                $ownerAccountId,
                "Ticket {$ticketNumber} has been cancelled.",
                'fa-ban',
                'warning',
                '/employee/tickets/view?id=' . $ticketId,
                $ticketId
            );
        }

        $this->respond(true, 'Ticket cancelled successfully.', $isAjax);
    }

    private function respond(bool $success, string $message, bool $asJson, int $httpCode = 200): void
    {
        if ($asJson) {
            http_response_code($httpCode);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => $success, 'message' => $message]);
            return;
        }

        $_SESSION[$success ? 'flash_success' : 'flash_error'] = $message;
        $this->redirect($this->resolveRedirectPath());
    }

    private function resolveRedirectPath(): string
    {
        $usertype = strtoupper((string) ($_SESSION['usertype'] ?? ''));
        $map = [
            'ADMIN'    => '/admin/tickets',
            'EMPLOYEE' => '/employee/tickets',
            'HEAD'     => '/head/tickets',
            'HR'       => '/hr/tickets',
            'IT'       => '/it/tickets/in_progress',
            'AOM'      => '/aom/tickets',
            'HOM'      => '/hom/tickets',
            'OM'       => '/om/tickets',
        ];
        return $map[$usertype] ?? '/login';
    }
}
