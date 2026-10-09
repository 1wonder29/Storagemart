<?php

require_once __DIR__ . '/AuthController.php';
require_once __DIR__ . '/../Models/NotificationModel.php';

/**
 * Data for the floating ticket chat head: the signed-in user's active tickets
 * (tickets they filed or created, and for IT the tickets assigned to them).
 * Messages themselves go through /ticket-comments/fetch and /ticket-comments/add.
 */
class TicketChatController extends AuthController
{
    /** Ticket statuses that no longer show a chat head. */
    private const CLOSED_STATUSES = ['Resolved', 'Closed', 'Cancelled', 'Decline'];

    /** GET /ticket-chat/threads */
    public function threads(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');

        $accountId = (int) ($_SESSION['account_id'] ?? 0);
        if ($accountId <= 0) {
            http_response_code(401);
            echo json_encode(['success' => false, 'threads' => []]);
            return;
        }

        global $pdo;
        $usertype = strtoupper((string) ($_SESSION['usertype'] ?? ''));
        $stmt = $pdo->prepare('SELECT employee_id FROM tblemployee WHERE account_id = ? LIMIT 1');
        $stmt->execute([$accountId]);
        $employeeId = (int) ($stmt->fetchColumn() ?: 0);

        $placeholders = implode(',', array_fill(0, count(self::CLOSED_STATUSES), '?'));
        $helpColumns = self::hasHelpColumns($pdo)
            ? 't.manual_sent_at, t.help_requested_at,'
            : 'NULL AS manual_sent_at, NULL AS help_requested_at,';
        $sql = "SELECT t.ticket_id, t.ticket_number, t.category, t.status, t.employee_id, t.assigned_to, t.created_by, {$helpColumns}
                       TRIM(CONCAT(COALESCE(req.firstname, ''), ' ', COALESCE(req.lastname, ''))) AS requester_name,
                       TRIM(CONCAT(COALESCE(it.firstname, ''), ' ', COALESCE(it.lastname, ''))) AS it_name,
                       lc.comment_id AS last_comment_id, lc.comment_text AS last_text, lc.account_id AS last_account_id,
                       CASE WHEN UPPER(lc.author_role) = 'ADMIN' THEN 'Admin' ELSE lc.author_name END AS last_author,
                       lc.author_role AS last_role, lc.created_at AS last_created_at,
                       COALESCE(lc.created_at, t.last_updated, t.date_filed) AS last_activity,
                       (SELECT MAX(mc.comment_id) FROM tblticket_comments mc
                         WHERE mc.ticket_id = t.ticket_id AND mc.account_id = ?) AS my_last_comment_id
                FROM tbltickets t
                LEFT JOIN tblemployee req ON req.employee_id = t.employee_id
                LEFT JOIN tblemployee it ON it.employee_id = t.assigned_to
                LEFT JOIN tblticket_comments lc ON lc.comment_id = (
                    SELECT MAX(c.comment_id) FROM tblticket_comments c WHERE c.ticket_id = t.ticket_id)
                WHERE t.status NOT IN ({$placeholders})
                  AND (t.employee_id = ? OR t.created_by = ?" . ($usertype === 'IT' ? ' OR t.assigned_to = ?' : '') . ")
                ORDER BY last_activity DESC
                LIMIT 20";
        $params = array_merge([$accountId], self::CLOSED_STATUSES, [$employeeId, $accountId]);
        if ($usertype === 'IT') {
            $params[] = $employeeId;
        }
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        // Recent comment ids from other people, so the browser can count unread messages
        // against the last message it has seen (kept per user in localStorage).
        $others = [];
        if ($rows) {
            $ids = array_map(static fn($r) => (int) $r['ticket_id'], $rows);
            $in = implode(',', array_fill(0, count($ids), '?'));
            $stmt = $pdo->prepare("SELECT ticket_id, comment_id FROM tblticket_comments
                                   WHERE ticket_id IN ({$in}) AND account_id <> ?
                                   ORDER BY comment_id DESC LIMIT 400");
            $stmt->execute(array_merge($ids, [$accountId]));
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $c) {
                $others[(int) $c['ticket_id']][] = (int) $c['comment_id'];
            }
        }

        $notifications = new NotificationModel();
        $threads = array_map(function (array $r) use ($employeeId, $accountId, $usertype, $others, $notifications) {
            $iAmRequester = (int) $r['employee_id'] === $employeeId || (int) $r['created_by'] === $accountId;
            $otherName = $iAmRequester
                ? ($r['it_name'] !== '' ? $r['it_name'] : 'IT Support')
                : ($r['requester_name'] !== '' ? $r['requester_name'] : 'Requester');
            return [
                'ticket_id'       => (int) $r['ticket_id'],
                'ticket_number'   => (string) ($r['ticket_number'] ?: ('#' . $r['ticket_id'])),
                'category'        => (string) ($r['category'] ?? ''),
                'status'          => (string) $r['status'],
                'other_name'      => $otherName,
                'other_role'      => $iAmRequester ? ($r['it_name'] !== '' ? 'IT Support' : 'Waiting for IT assignment') : 'Requester',
                'last_comment_id' => (int) ($r['last_comment_id'] ?? 0),
                'last_text'       => $r['last_text'] !== null
                    ? (trim((string) $r['last_text']) !== '' ? mb_substr((string) $r['last_text'], 0, 120) : 'Sent an attachment')
                    : '',
                'last_author'     => (string) ($r['last_author'] ?? ''),
                'last_is_mine'    => (int) ($r['last_account_id'] ?? 0) === $accountId,
                'last_activity'   => (string) $r['last_activity'],
                'my_last_comment_id' => (int) ($r['my_last_comment_id'] ?? 0),
                'other_comment_ids' => $others[(int) $r['ticket_id']] ?? [],
                'i_am_requester'  => $iAmRequester,
                'has_it'          => (int) ($r['assigned_to'] ?? 0) > 0,
                'last_role'       => strtoupper((string) ($r['last_role'] ?? '')),
                'last_created_at' => (string) ($r['last_created_at'] ?? ''),
                'manual_sent_at'  => (string) ($r['manual_sent_at'] ?? ''),
                'help_requested_at' => (string) ($r['help_requested_at'] ?? ''),
                'view_url'        => rtrim(BASE_URL, '/') . $notifications->getTicketViewUrlForRole($usertype, (int) $r['ticket_id']),
            ];
        }, $rows);

        // Open the chat once, right after the user filed a ticket (set in TicketFormFields::ticketCreated).
        $openTicketId = (int) ($_SESSION['tms_open_chat_ticket'] ?? 0);
        if ($openTicketId > 0 && in_array($openTicketId, array_column($threads, 'ticket_id'), true)) {
            unset($_SESSION['tms_open_chat_ticket']);
        } else {
            $openTicketId = 0;
        }

        echo json_encode([
            'success'        => true,
            'account_id'     => $accountId,
            'usertype'       => $usertype,
            'server_now'     => (string) $pdo->query('SELECT NOW()')->fetchColumn(),
            'open_ticket_id' => $openTicketId,
            'threads'        => $threads,
        ]);
    }

    /** POST /ticket-chat/help: the requester tried the manual and still needs IT. */
    public function requestHelp(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        header('Content-Type: application/json; charset=utf-8');

        $accountId = (int) ($_SESSION['account_id'] ?? 0);
        $ticketId = (int) ($_POST['ticket_id'] ?? 0);
        if ($accountId <= 0 || $ticketId <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid request.']);
            return;
        }

        global $pdo;
        try {
            $stmt = $pdo->prepare('SELECT t.ticket_id, t.ticket_number, t.status, t.assigned_to, t.employee_id, t.created_by,
                        t.help_requested_at, e.account_id AS owner_account_id
                    FROM tbltickets t LEFT JOIN tblemployee e ON e.employee_id = t.employee_id
                    WHERE t.ticket_id = ? LIMIT 1');
            $stmt->execute([$ticketId]);
            $ticket = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'The database update for this feature has not been run yet.']);
            return;
        }

        $isRequester = $ticket
            && ((int) $ticket['owner_account_id'] === $accountId || (int) $ticket['created_by'] === $accountId);
        if (!$isRequester || in_array($ticket['status'], self::CLOSED_STATUSES, true)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Only the requester of an active ticket can ask for help.']);
            return;
        }
        if (!empty($ticket['help_requested_at'])) {
            echo json_encode(['success' => true, 'already' => true, 'help_requested_at' => $ticket['help_requested_at']]);
            return;
        }

        require_once __DIR__ . '/../Models/TicketCommentModel.php';
        $commentModel = new TicketCommentModel();
        $author = $commentModel->getAuthorDisplayInfo($accountId);

        $pdo->prepare('UPDATE tbltickets SET help_requested_at = NOW() WHERE ticket_id = ?')->execute([$ticketId]);
        $commentModel->addComment($ticketId, $accountId, $author['role'], $author['name'],
            'I tried the troubleshooting guide but still need help from IT personnel.');

        // Alert the assigned IT person; with nobody assigned yet, alert every active IT account.
        $message = $author['name'] . ' needs help from IT on ticket ' . $ticket['ticket_number'] . ' (the guide did not fix it).';
        $recipients = [];
        if ((int) $ticket['assigned_to'] > 0) {
            $stmt = $pdo->prepare('SELECT account_id FROM tblemployee WHERE employee_id = ? LIMIT 1');
            $stmt->execute([(int) $ticket['assigned_to']]);
            $recipients[] = (int) $stmt->fetchColumn();
        } else {
            $recipients = array_map('intval', $pdo->query("SELECT account_id FROM tblaccounts
                WHERE usertype = 'IT' AND UPPER(status) = 'ACTIVE'")->fetchAll(PDO::FETCH_COLUMN));
        }
        $notifications = new NotificationModel();
        foreach (array_filter(array_unique($recipients)) as $recipient) {
            $notifications->create($recipient, $message, 'fa-life-ring', 'danger', '/it/tickets/view?id=' . $ticketId, $ticketId);
        }

        echo json_encode(['success' => true, 'help_requested_at' => (string) $pdo->query('SELECT NOW()')->fetchColumn()]);
    }

    private static function hasHelpColumns(PDO $pdo): bool
    {
        static $has = null;
        if ($has === null) {
            $stmt = $pdo->query("SELECT COUNT(*) FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tbltickets'
                  AND COLUMN_NAME IN ('manual_sent_at', 'help_requested_at')");
            $has = (int) $stmt->fetchColumn() === 2;
        }
        return $has;
    }
}
