<?php

/**
 * Data for the shared header / sidebar (name, email, position of the signed-in user).
 * Cached in the session; Session::regenerate() clears it on every login.
 */
class AppShell
{
    /** Shared logins shown as a role ("Admin") instead of a person. Any other Admin account shows its own name. */
    private const SHARED_ADMIN_USERNAMES = ['admin'];

    /** @return array{firstname: string, lastname: string, fullname: string, email: string, position: string} */
    public static function currentUser(): array
    {
        $isSharedAdmin = strtoupper((string) ($_SESSION['usertype'] ?? '')) === 'ADMIN'
            && in_array(strtolower(trim((string) ($_SESSION['username'] ?? ''))), self::SHARED_ADMIN_USERNAMES, true);

        if ($isSharedAdmin) {
            require_once __DIR__ . '/../Services/TicketMailer.php';
            return [
                'firstname' => 'Admin',
                'lastname' => '',
                'fullname' => 'Admin',
                'email' => TicketMailer::adminRecipients()[0] ?? '',
                'position' => 'Administrator',
            ];
        }

        if (!empty($_SESSION['app_shell_user']) && is_array($_SESSION['app_shell_user'])) {
            return $_SESSION['app_shell_user'];
        }

        $user = ['firstname' => '', 'lastname' => '', 'fullname' => '', 'email' => '', 'position' => ''];
        $accountId = (int) ($_SESSION['account_id'] ?? 0);

        if ($accountId > 0) {
            global $pdo;
            try {
                $stmt = $pdo->prepare(
                    'SELECT e.firstname, e.lastname, e.email, e.position
                     FROM tblemployee e
                     WHERE e.account_id = ?
                     LIMIT 1'
                );
                $stmt->execute([$accountId]);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($row) {
                    $user['firstname'] = trim((string) $row['firstname']);
                    $user['lastname'] = trim((string) $row['lastname']);
                    $user['fullname'] = trim($user['firstname'] . ' ' . $user['lastname']);
                    $user['email'] = trim((string) $row['email']);
                    $user['position'] = trim((string) $row['position']);
                }
            } catch (Throwable $e) {
                error_log('AppShell::currentUser failed: ' . $e->getMessage());
            }
        }

        if ($user['fullname'] === '') {
            $user['fullname'] = (string) ($_SESSION['username'] ?? '');
        }

        $_SESSION['app_shell_user'] = $user;
        return $user;
    }
}
