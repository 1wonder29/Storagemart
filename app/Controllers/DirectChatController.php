<?php

require_once __DIR__ . '/AuthController.php';
require_once __DIR__ . '/../Helpers/Birthdays.php';
require_once __DIR__ . '/../Helpers/ActivityLogger.php';
require_once __DIR__ . '/../Helpers/RateLimit.php';

/**
 * Admin chat requests (direct-chat.js). The Admin starts a chat with any user about a
 * concern and gives a time to answer; the user accepts or declines before the timer runs
 * out. An urgent chat opens right away without accept / decline. Either side can end it.
 *
 * Status: PENDING (waiting for the user) -> ACTIVE -> CLOSED, or DECLINED / EXPIRED.
 */
class DirectChatController extends AuthController
{
    public const MINUTES = [1, 3, 5, 10, 15, 30];

    /** GET /direct-chat/poll : my waiting requests and my chats (with unread counts). */
    public function poll(): void
    {
        $me = $this->account();
        global $pdo;
        $this->expireOld($pdo);
        $stmt = $pdo->prepare("SELECT c.*,
                    (SELECT COUNT(*) FROM tbldirect_messages m WHERE m.chat_id = c.chat_id AND m.account_id <> ?
                        AND m.message_id > IF(c.admin_account_id = ?, c.admin_last_read, c.user_last_read)) AS unread,
                    (SELECT m.body FROM tbldirect_messages m WHERE m.chat_id = c.chat_id ORDER BY m.message_id DESC LIMIT 1) AS last_message
                FROM tbldirect_chats c
                WHERE (c.admin_account_id = ? OR c.user_account_id = ?)
                  AND (c.status IN ('PENDING', 'ACTIVE') OR c.created_at > NOW() - INTERVAL 2 DAY)
                ORDER BY c.status = 'ACTIVE' DESC, c.chat_id DESC LIMIT 30");
        $stmt->execute([$me, $me, $me, $me]);
        $chats = [];
        $requests = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $chat = $this->present($pdo, $row, $me);
            $chat['unread'] = (int) $row['unread'];
            $chat['last_message'] = $row['last_message'] !== null ? mb_substr((string) $row['last_message'], 0, 80) : '';
            if ($chat['status'] === 'PENDING' && !$chat['i_am_admin']) {
                $requests[] = $chat;
            }
            $chats[] = $chat;
        }
        $this->json(['success' => true, 'server_now' => date('Y-m-d\TH:i:s'), 'requests' => $requests, 'chats' => $chats,
            'can_start' => $this->isAdmin()]);
    }

    /** GET /direct-chat/users?q= : people the Admin can chat with. */
    public function users(): void
    {
        $me = $this->account();
        if (!$this->isAdmin()) {
            $this->json(['success' => false, 'message' => 'Only Admin can start a chat.'], 403);
        }
        global $pdo;
        $q = trim((string) ($_GET['q'] ?? ''));
        $like = '%' . $q . '%';
        $stmt = $pdo->prepare("SELECT a.account_id, a.username, a.usertype,
                    TRIM(CONCAT(COALESCE(e.firstname, ''), ' ', COALESCE(e.lastname, ''))) AS name, e.department, b.branchName
                FROM tblaccounts a
                LEFT JOIN tblemployee e ON e.account_id = a.account_id
                LEFT JOIN tblbranch b ON b.branch_id = e.branch_id
                WHERE a.account_id <> ? AND UPPER(a.status) = 'ACTIVE'
                  AND (? = '' OR e.firstname LIKE ? OR e.lastname LIKE ? OR a.username LIKE ? OR e.department LIKE ? OR b.branchName LIKE ?)
                ORDER BY name = '', name, a.username LIMIT 20");
        $stmt->execute([$me, $q, $like, $like, $like, $like, $like]);
        $out = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $out[] = [
                'account_id' => (int) $row['account_id'],
                'name' => $row['name'] !== '' ? $row['name'] : (string) $row['username'],
                'detail' => implode(' · ', array_filter([ucfirst(strtolower((string) $row['usertype'])), (string) $row['department'], (string) $row['branchName']])),
            ];
        }
        $this->json(['success' => true, 'users' => $out]);
    }

    /** POST /direct-chat/start : user_id, concern, minutes, urgent. */
    public function start(): void
    {
        $me = $this->account(true);
        if (!$this->isAdmin()) {
            $this->json(['success' => false, 'message' => 'Only Admin can start a chat.'], 403);
        }
        global $pdo;
        $userId = (int) ($_POST['user_id'] ?? 0);
        $concern = trim((string) ($_POST['concern'] ?? ''));
        $minutes = (int) ($_POST['minutes'] ?? 5);
        $urgent = !empty($_POST['urgent']);
        if (!in_array($minutes, self::MINUTES, true)) {
            $minutes = 5;
        }
        if ($userId <= 0 || $userId === $me || $concern === '' || mb_strlen($concern) > 500) {
            $this->json(['success' => false, 'message' => 'Choose a person and write the concern (max 500 characters).'], 400);
        }
        if (($wait = RateLimit::hit('direct_chat_start', 5, 60)) > 0) {
            $this->json(['success' => false, 'message' => RateLimit::message($wait)], 429);
        }
        $exists = $pdo->prepare("SELECT COUNT(*) FROM tblaccounts WHERE account_id = ? AND UPPER(status) = 'ACTIVE'");
        $exists->execute([$userId]);
        if (!(int) $exists->fetchColumn()) {
            $this->json(['success' => false, 'message' => 'That account is not active.'], 400);
        }
        $pdo->prepare("INSERT INTO tbldirect_chats (admin_account_id, user_account_id, concern, is_urgent, status, expires_at, created_at, responded_at)
                VALUES (?, ?, ?, ?, ?, ?, NOW(), ?)")
            ->execute([$me, $userId, $concern, $urgent ? 1 : 0, $urgent ? 'ACTIVE' : 'PENDING',
                $urgent ? null : date('Y-m-d H:i:s', time() + $minutes * 60), $urgent ? date('Y-m-d H:i:s') : null]);
        $chatId = (int) $pdo->lastInsertId();
        $pdo->prepare('INSERT INTO tbldirect_messages (chat_id, account_id, body, created_at) VALUES (?, ?, ?, NOW())')
            ->execute([$chatId, $me, $concern]);
        $this->bumpRead($pdo, $chatId, $me);
        ActivityLogger::update('Admin Chat', (string) $chatId, ($urgent ? 'Urgent chat' : 'Chat request') . ' to '
            . Birthdays::nameOf($pdo, $userId), $_SESSION['username'] ?? 'Unknown');
        $this->json(['success' => true, 'chat' => $this->load($pdo, $chatId, $me)]);
    }

    /** POST /direct-chat/respond : chat_id, accept (1 / 0), note. */
    public function respond(): void
    {
        $me = $this->account(true);
        global $pdo;
        $this->expireOld($pdo);
        $chat = $this->row($pdo, (int) ($_POST['chat_id'] ?? 0));
        if (!$chat || (int) $chat['user_account_id'] !== $me) {
            $this->json(['success' => false, 'message' => 'Chat not found.'], 404);
        }
        if ($chat['status'] !== 'PENDING') {
            $this->json(['success' => false, 'message' => $chat['status'] === 'EXPIRED' ? 'The time to answer has run out.' : 'Already answered.',
                'chat' => $this->load($pdo, (int) $chat['chat_id'], $me)]);
        }
        $accept = !empty($_POST['accept']);
        $note = mb_substr(trim((string) ($_POST['note'] ?? '')), 0, 255);
        $pdo->prepare("UPDATE tbldirect_chats SET status = ?, responded_at = NOW(), decline_note = ? WHERE chat_id = ? AND status = 'PENDING'")
            ->execute([$accept ? 'ACTIVE' : 'DECLINED', $accept ? null : ($note !== '' ? $note : null), (int) $chat['chat_id']]);
        $this->json(['success' => true, 'chat' => $this->load($pdo, (int) $chat['chat_id'], $me)]);
    }

    /** GET /direct-chat/messages?chat_id=&after= */
    public function messages(): void
    {
        $me = $this->account();
        global $pdo;
        $this->expireOld($pdo);
        $chatId = (int) ($_GET['chat_id'] ?? 0);
        $chat = $this->row($pdo, $chatId);
        if (!$chat || !in_array($me, [(int) $chat['admin_account_id'], (int) $chat['user_account_id']], true)) {
            $this->json(['success' => false, 'message' => 'Chat not found.'], 404);
        }
        $after = (int) ($_GET['after'] ?? 0);
        $messages = [];
        // The user sees the messages once the chat is open (the request popup shows the concern).
        if ($chat['status'] !== 'PENDING' || (int) $chat['admin_account_id'] === $me) {
            $stmt = $pdo->prepare('SELECT message_id, account_id, body, created_at FROM tbldirect_messages
                    WHERE chat_id = ? AND message_id > ? ORDER BY message_id LIMIT 200');
            $stmt->execute([$chatId, $after]);
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $messages[] = [
                    'id' => (int) $row['message_id'],
                    'mine' => (int) $row['account_id'] === $me,
                    'body' => (string) $row['body'],
                    'at' => (string) $row['created_at'],
                ];
            }
            if ($messages) {
                $this->bumpRead($pdo, $chatId, $me);
            }
        }
        $this->json(['success' => true, 'chat' => $this->present($pdo, $chat, $me), 'messages' => $messages]);
    }

    /** POST /direct-chat/send : chat_id, body. */
    public function send(): void
    {
        $me = $this->account(true);
        global $pdo;
        $chat = $this->row($pdo, (int) ($_POST['chat_id'] ?? 0));
        if (!$chat || !in_array($me, [(int) $chat['admin_account_id'], (int) $chat['user_account_id']], true)) {
            $this->json(['success' => false, 'message' => 'Chat not found.'], 404);
        }
        if ($chat['status'] !== 'ACTIVE') {
            $this->json(['success' => false, 'message' => 'This chat is not open.'], 400);
        }
        $body = trim((string) ($_POST['body'] ?? ''));
        if ($body === '' || mb_strlen($body) > 2000) {
            $this->json(['success' => false, 'message' => 'Write a message (max 2,000 characters).'], 400);
        }
        if (($wait = RateLimit::hit('direct_chat_send', 15, 30)) > 0) {
            $this->json(['success' => false, 'message' => RateLimit::message($wait)], 429);
        }
        $pdo->prepare('INSERT INTO tbldirect_messages (chat_id, account_id, body, created_at) VALUES (?, ?, ?, NOW())')
            ->execute([(int) $chat['chat_id'], $me, $body]);
        $this->bumpRead($pdo, (int) $chat['chat_id'], $me);
        $this->json(['success' => true]);
    }

    /** POST /direct-chat/close : chat_id (either side ends the chat; the Admin can also cancel a request). */
    public function close(): void
    {
        $me = $this->account(true);
        global $pdo;
        $chat = $this->row($pdo, (int) ($_POST['chat_id'] ?? 0));
        if (!$chat || !in_array($me, [(int) $chat['admin_account_id'], (int) $chat['user_account_id']], true)) {
            $this->json(['success' => false, 'message' => 'Chat not found.'], 404);
        }
        $pdo->prepare("UPDATE tbldirect_chats SET status = 'CLOSED', closed_at = NOW(), closed_by = ?
                WHERE chat_id = ? AND status IN ('PENDING', 'ACTIVE')")->execute([$me, (int) $chat['chat_id']]);
        $this->json(['success' => true, 'chat' => $this->load($pdo, (int) $chat['chat_id'], $me)]);
    }

    // ------------------------------------------------------------------ helpers

    private function expireOld(PDO $pdo): void
    {
        $pdo->exec("UPDATE tbldirect_chats SET status = 'EXPIRED' WHERE status = 'PENDING' AND expires_at IS NOT NULL AND expires_at <= NOW()");
    }

    private function row(PDO $pdo, int $chatId): ?array
    {
        $stmt = $pdo->prepare('SELECT * FROM tbldirect_chats WHERE chat_id = ? LIMIT 1');
        $stmt->execute([$chatId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    private function load(PDO $pdo, int $chatId, int $me): ?array
    {
        $row = $this->row($pdo, $chatId);
        return $row ? $this->present($pdo, $row, $me) : null;
    }

    private function present(PDO $pdo, array $row, int $me): array
    {
        $iAmAdmin = (int) $row['admin_account_id'] === $me;
        return [
            'chat_id' => (int) $row['chat_id'],
            'i_am_admin' => $iAmAdmin,
            'other_name' => Birthdays::nameOf($pdo, (int) ($iAmAdmin ? $row['user_account_id'] : $row['admin_account_id'])),
            'admin_name' => Birthdays::nameOf($pdo, (int) $row['admin_account_id']),
            'concern' => (string) $row['concern'],
            'is_urgent' => (int) $row['is_urgent'] === 1,
            'status' => (string) $row['status'],
            'expires_at' => $row['expires_at'] ? str_replace(' ', 'T', (string) $row['expires_at']) : null,
            'created_at' => str_replace(' ', 'T', (string) $row['created_at']),
            'decline_note' => (string) ($row['decline_note'] ?? ''),
            'closed_by_me' => (int) ($row['closed_by'] ?? 0) === $me,
        ];
    }

    private function bumpRead(PDO $pdo, int $chatId, int $me): void
    {
        $pdo->prepare('UPDATE tbldirect_chats c SET
                    c.admin_last_read = IF(c.admin_account_id = ?, (SELECT COALESCE(MAX(message_id), 0) FROM tbldirect_messages WHERE chat_id = c.chat_id), c.admin_last_read),
                    c.user_last_read = IF(c.user_account_id = ?, (SELECT COALESCE(MAX(message_id), 0) FROM tbldirect_messages WHERE chat_id = c.chat_id), c.user_last_read)
                WHERE c.chat_id = ?')->execute([$me, $me, $chatId]);
    }

    private function isAdmin(): bool
    {
        return strtoupper((string) ($_SESSION['superuser_real_usertype'] ?? $_SESSION['usertype'] ?? '')) === 'ADMIN';
    }

    private function account(bool $change = false): int
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $me = (int) ($_SESSION['account_id'] ?? 0);
        if ($me <= 0) {
            $this->json(['success' => false, 'message' => 'Please sign in again.'], 401);
        }
        if ($change) {
            $token = (string) ($_POST['csrf_token'] ?? '');
            if (empty($_SESSION['csrf_token']) || !hash_equals((string) $_SESSION['csrf_token'], $token)) {
                $this->json(['success' => false, 'message' => 'Please reload the page and try again.'], 419);
            }
        }
        try {
            global $pdo;
            $pdo->query('SELECT 1 FROM tbldirect_chats LIMIT 1');
        } catch (Throwable $e) {
            $this->json(['success' => false, 'message' => 'Please run the database update first.', 'setup' => false], 200);
        }
        return $me;
    }

    private function json(array $data, int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
}
