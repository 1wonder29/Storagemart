<?php

/**
 * Anti-spam: how often one signed-in person may repeat an action (chat messages, comments,
 * chat requests, birthday greetings). Kept in the session, so it costs no database work.
 *
 *   $wait = RateLimit::hit('chat_send', 15, 30); // at most 15 times per 30 seconds
 *   if ($wait > 0) { ... "Please wait $wait seconds" ... }
 */
class RateLimit
{
    /** Records one use of $action. Returns 0 when allowed, otherwise the seconds to wait. */
    public static function hit(string $action, int $max, int $seconds): int
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $now = time();
        $recent = [];
        foreach ((array) ($_SESSION['rate_limit'][$action] ?? []) as $time) {
            if (is_int($time) && $time > $now - $seconds) {
                $recent[] = $time;
            }
        }
        if (count($recent) >= $max) {
            $_SESSION['rate_limit'][$action] = $recent;
            return max(1, $recent[0] + $seconds - $now);
        }
        $recent[] = $now;
        $_SESSION['rate_limit'][$action] = $recent;
        return 0;
    }

    public static function message(int $wait): string
    {
        return 'You are sending too fast. Please wait ' . $wait . ' second' . ($wait === 1 ? '' : 's') . ' and try again.';
    }
}
