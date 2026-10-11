<?php
require_once __DIR__ . '/MailService.php';

/**
 * "Your TMS password was changed" e-mail to the account owner, after the Admin resets it
 * or the person changes it in Change password. Tells them to contact the IT Department if it
 * was not them. Never throws: a mail problem must not block the password change.
 */
class PasswordChangeMailer
{
    public static function send(PDO $pdo, int $accountId, bool $byAdmin): void
    {
        try {
            $stmt = $pdo->prepare('SELECT a.username, e.email, e.firstname
                    FROM tblaccounts a LEFT JOIN tblemployee e ON e.account_id = a.account_id
                    WHERE a.account_id = ? LIMIT 1');
            $stmt->execute([$accountId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$row) {
                return;
            }
            $to = trim((string) ($row['email'] ?? ''));
            if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
                $to = trim((string) ($row['username'] ?? '')); // usernames are the company e-mail
            }
            if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
                return;
            }

            $e = static fn(string $v): string => htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
            $name = trim((string) ($row['firstname'] ?? '')) ?: 'there';
            $when = date('F j, Y \a\t g:i A');
            $how = $byAdmin
                ? 'The TMS Administrator set a new password for your account.'
                : 'Your password was changed from the Change password page.';
            $loginUrl = MailService::siteUrl() . '/login';

            $body = '<div style="font-family:Arial,Helvetica,sans-serif;font-size:14px;color:#1f2937;max-width:560px">'
                . '<div style="background:#19398a;color:#fff;padding:14px 18px;border-bottom:4px solid #f85f00;font-weight:bold">StorageMart TMS</div>'
                . '<div style="padding:18px">'
                . '<p>Hi ' . $e($name) . ',</p>'
                . '<p>Your TMS password was changed on <b>' . $e($when) . '</b>.<br>' . $e($how) . '</p>'
                . ($byAdmin ? '<p>Sign in with the new password, then change it to your own in <b>Change password</b>.</p>' : '')
                . '<p style="background:#fff4ec;border-left:4px solid #f85f00;padding:10px 12px">'
                . '<b>If you did not do this or did not ask for it, please contact the IT Department for assistance right away.</b></p>'
                . '<p><a href="' . $e($loginUrl) . '">Open the TMS</a></p>'
                . '<p style="color:#6b7280;font-size:12px">This is an automatic message from the StorageMart Ticket Management System. Please do not reply.</p>'
                . '</div></div>';

            MailService::send($to, 'Your TMS password was changed', $body);
        } catch (Throwable $e) {
            error_log('PasswordChangeMailer::send failed: ' . $e->getMessage());
        }
    }
}
