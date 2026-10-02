<?php

/**
 * Full-access admins (tblaccounts.is_superuser = 1) can open every role's pages
 * (HR, IT, Operations, Department Head) from the Admin sidebar.
 *
 * Their stored role stays ADMIN. For a request to another role's area, the router
 * calls actAs() so the existing role checks pass for that one request; the real
 * role is put back at shutdown, and restoreRealRole() at the start of every
 * request is a second guard in case a swap was ever left in the session.
 */
class SuperUser
{
    private const AREA_ROLES = [
        'hr'   => 'HR',
        'it'   => 'IT',
        'hom'  => 'HOM',
        'head' => 'HEAD',
    ];

    public static function isCurrent(): bool
    {
        $accountId = (int) ($_SESSION['account_id'] ?? 0);
        if ($accountId <= 0) {
            return false;
        }
        $realRole = strtoupper((string) ($_SESSION['superuser_real_usertype'] ?? $_SESSION['usertype'] ?? ''));
        if ($realRole !== 'ADMIN') {
            return false;
        }

        if (($_SESSION['is_superuser_for'] ?? null) !== $accountId) {
            global $pdo;
            try {
                $stmt = $pdo->prepare('SELECT is_superuser FROM tblaccounts WHERE account_id = ? LIMIT 1');
                $stmt->execute([$accountId]);
                $_SESSION['is_superuser'] = (int) $stmt->fetchColumn() === 1;
            } catch (Throwable $e) {
                // Column not migrated yet: nobody is a superuser.
                $_SESSION['is_superuser'] = false;
            }
            $_SESSION['is_superuser_for'] = $accountId;
        }

        return (bool) $_SESSION['is_superuser'];
    }

    /** Role whose area a request path belongs to, e.g. '/hr/uniforms' => 'HR'. */
    public static function roleForPath(string $path): ?string
    {
        $first = strtolower(explode('/', trim($path, '/'))[0] ?? '');
        return self::AREA_ROLES[$first] ?? null;
    }

    public static function actAs(string $role): void
    {
        $_SESSION['superuser_real_usertype'] = $_SESSION['usertype'] ?? 'ADMIN';
        $_SESSION['usertype'] = $role;
        register_shutdown_function([self::class, 'restoreRealRole']);
    }

    public static function restoreRealRole(): void
    {
        if (isset($_SESSION['superuser_real_usertype'])) {
            $_SESSION['usertype'] = $_SESSION['superuser_real_usertype'];
            unset($_SESSION['superuser_real_usertype']);
        }
    }

    /**
     * Some role pages re-read the role from the database instead of the session.
     * While acting in a role area, report the acting role for the user's own record.
     */
    public static function withActingRole(?array $user, int $accountId): ?array
    {
        if ($user !== null
            && self::isActingInRoleArea()
            && $accountId === (int) ($_SESSION['account_id'] ?? 0)) {
            $user['usertype'] = $_SESSION['usertype'];
        }
        return $user;
    }

    /** True while a full-access admin is viewing another role's area. */
    public static function isActingInRoleArea(): bool
    {
        return isset($_SESSION['superuser_real_usertype']);
    }
}
