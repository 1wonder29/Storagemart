<?php

require_once __DIR__ . '/AuthController.php';
require_once __DIR__ . '/../Helpers/Birthdays.php';

/**
 * JSON endpoints for the site-wide widgets (global-widgets.js): the birthday panel on the
 * dashboards and the employees' first-time guide. Also the guide page itself.
 */
class WidgetController extends AuthController
{
    /** GET /birthdays/feed : today's celebrants, the countdown list and my greetings. */
    public function birthdayFeed(): void
    {
        $me = $this->jsonAccount();
        global $pdo;
        $people = Birthdays::upcoming($pdo);
        $year = (int) date('Y');
        $greeted = [];
        $counts = [];
        try {
            $ids = array_column(array_filter($people, static function ($p) { return $p['days_left'] === 0; }), 'account_id');
            if ($ids) {
                $in = implode(',', array_fill(0, count($ids), '?'));
                $stmt = $pdo->prepare("SELECT celebrant_account_id, COUNT(*) AS n, SUM(from_account_id = ?) AS mine
                        FROM tblbirthday_greetings WHERE greet_year = ? AND celebrant_account_id IN ($in) GROUP BY celebrant_account_id");
                $stmt->execute(array_merge([$me, $year], $ids));
                foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                    $counts[(int) $row['celebrant_account_id']] = (int) $row['n'];
                    $greeted[(int) $row['celebrant_account_id']] = (int) $row['mine'] > 0;
                }
            }
        } catch (Throwable $e) {
            // greetings table not created yet
        }
        $today = [];
        $upcoming = [];
        foreach ($people as $person) {
            $person['is_me'] = $person['account_id'] === $me;
            if ($person['days_left'] === 0) {
                $person['greet_count'] = $counts[$person['account_id']] ?? 0;
                $person['greeted'] = $greeted[$person['account_id']] ?? false;
                $today[] = $person;
            } else {
                $person['starts_at'] = $person['date'] . 'T00:00:00';
                $upcoming[] = $person;
            }
        }
        $mine = [];
        if (array_filter($today, static function ($p) { return $p['is_me']; })) {
            try {
                $stmt = $pdo->prepare('SELECT from_account_id, message, created_at FROM tblbirthday_greetings
                        WHERE celebrant_account_id = ? AND greet_year = ? ORDER BY greeting_id DESC LIMIT 100');
                $stmt->execute([$me, $year]);
                foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                    $mine[] = [
                        'from' => Birthdays::nameOf($pdo, (int) $row['from_account_id']),
                        'message' => (string) $row['message'],
                        'at' => (string) $row['created_at'],
                    ];
                }
            } catch (Throwable $e) {
                $mine = [];
            }
        }
        $this->json(['success' => true, 'server_now' => date('Y-m-d\TH:i:s'), 'today' => $today, 'upcoming' => $upcoming, 'my_greetings' => $mine]);
    }

    /** POST /birthdays/greet : greet a colleague on their birthday (once a year). */
    public function birthdayGreet(): void
    {
        $me = $this->jsonAccount(true);
        require_once __DIR__ . '/../Helpers/RateLimit.php';
        if (($wait = RateLimit::hit('birthday_greet', 10, 60)) > 0) {
            $this->json(['success' => false, 'message' => RateLimit::message($wait)], 429);
        }
        global $pdo;
        $celebrant = (int) ($_POST['celebrant_id'] ?? 0);
        $message = trim((string) ($_POST['message'] ?? ''));
        if ($message === '') {
            $message = 'Happy birthday! 🎂';
        }
        $message = mb_substr($message, 0, 200);
        $isToday = false;
        foreach (Birthdays::upcoming($pdo) as $person) {
            if ($person['account_id'] === $celebrant && $person['days_left'] === 0) {
                $isToday = true;
            }
        }
        if (!$isToday || $celebrant === $me) {
            $this->json(['success' => false, 'message' => 'You can greet a colleague on their birthday.'], 400);
        }
        try {
            $pdo->prepare('INSERT IGNORE INTO tblbirthday_greetings (celebrant_account_id, from_account_id, greet_year, message, created_at)
                    VALUES (?, ?, ?, ?, NOW())')->execute([$celebrant, $me, (int) date('Y'), $message]);
            $count = $pdo->prepare('SELECT COUNT(*) FROM tblbirthday_greetings WHERE celebrant_account_id = ? AND greet_year = ?');
            $count->execute([$celebrant, (int) date('Y')]);
            $this->json(['success' => true, 'greet_count' => (int) $count->fetchColumn()]);
        } catch (Throwable $e) {
            error_log('WidgetController::birthdayGreet failed: ' . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Could not send the greeting. Please run the database update first.'], 500);
        }
    }

    /** POST /onboarding/done : the employee finished (or skipped) the first-time guide. */
    public function onboardingDone(): void
    {
        $me = $this->jsonAccount(true);
        global $pdo;
        try {
            $pdo->prepare('UPDATE tblaccounts SET onboarding_done_at = NOW() WHERE account_id = ? AND onboarding_done_at IS NULL')->execute([$me]);
        } catch (Throwable $e) {
            // column not added yet: the guide just shows again next time
        }
        $this->json(['success' => true]);
    }

    /** GET /guide : "How to use the TMS" slides (first-time guide; ?embed=1 inside the welcome window). */
    public function guide(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION['account_id'])) {
            $this->redirect('/login');
            exit;
        }
        require_once __DIR__ . '/../Helpers/TmsGuide.php';
        $base = rtrim(BASE_URL, '/');
        $embed = !empty($_GET['embed']);
        // Your own role's guide; IT and Admin can also open the other roles' guides (?role=HR).
        $viewerRole = TmsGuide::currentRole();
        $role = strtoupper((string) ($_GET['role'] ?? $viewerRole));
        if (!TmsGuide::exists($role) || !TmsGuide::canOpen($viewerRole, $role)) {
            $role = $viewerRole;
        }
        $roles = array_values(array_filter(array_keys(TmsGuide::ROLES), static function (string $key) use ($viewerRole): bool {
            return TmsGuide::canOpen($viewerRole, $key);
        }));
        $home = TmsGuide::ROLES[$viewerRole]['home'];
        require __DIR__ . '/../Views/guide/role_guide.php';
    }

    /** Signed-in account id for a JSON call (and the form token for changes). */
    private function jsonAccount(bool $change = false): int
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
