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
        $sql = "SELECT t.ticket_id, t.ticket_number, t.category, t.status, t.employee_id, t.assigned_to, t.created_by,
                       TRIM(CONCAT(COALESCE(req.firstname, ''), ' ', COALESCE(req.lastname, ''))) AS requester_name,
                       TRIM(CONCAT(COALESCE(it.firstname, ''), ' ', COALESCE(it.lastname, ''))) AS it_name,
                       lc.comment_id AS last_comment_id, lc.comment_text AS last_text, lc.account_id AS last_account_id,
                       CASE WHEN UPPER(lc.author_role) = 'ADMIN' THEN 'Admin' ELSE lc.author_name END AS last_author,
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
                'last_text'       => $r['last_text'] !== null ? mb_substr((string) $r['last_text'], 0, 120) : '',
                'last_author'     => (string) ($r['last_author'] ?? ''),
                'last_is_mine'    => (int) ($r['last_account_id'] ?? 0) === $accountId,
                'last_activity'   => (string) $r['last_activity'],
                'my_last_comment_id' => (int) ($r['my_last_comment_id'] ?? 0),
                'other_comment_ids' => $others[(int) $r['ticket_id']] ?? [],
                'view_url'        => rtrim(BASE_URL, '/') . $notifications->getTicketViewUrlForRole($usertype, (int) $r['ticket_id']),
            ];
        }, $rows);

        echo json_encode(['success' => true, 'account_id' => $accountId, 'threads' => $threads]);
    }
}
