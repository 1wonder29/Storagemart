<?php

require_once __DIR__ . '/SuperUser.php';

/**
 * Admin pages that only the system administrators use (System settings and the Audit Trail).
 * The General Manager (full-access account) keeps every other Admin page but not these.
 */
class AdminAccess
{
    public static function canManageSystem(): bool
    {
        $realRole = strtoupper((string) ($_SESSION['superuser_real_usertype'] ?? $_SESSION['usertype'] ?? ''));
        return !empty($_SESSION['account_id']) && $realRole === 'ADMIN' && !SuperUser::isCurrent();
    }
}
