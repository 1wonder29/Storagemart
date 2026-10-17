<?php
require_once __DIR__ . '/AppSettings.php';

/**
 * The shared built-in "admin" account is used by one IT officer (for example Kenneth Dador).
 * When the Admin links it to that person, the work done in the admin account (ticket ratings)
 * is counted for the person, and "Admin ADMIN" is no longer listed as a separate technician.
 * The person's own account keeps its own role; nothing about logins or rights changes.
 */
class AdminAccountPerson
{
    public const SETTING = 'admin_account_person';

    /** employee_id of the built-in admin account (username "admin"), or 0. */
    public static function adminEmployeeId(PDO $pdo): int
    {
        try {
            $stmt = $pdo->query("SELECT e.employee_id FROM tblaccounts a JOIN tblemployee e ON e.account_id = a.account_id
                    WHERE LOWER(a.username) = 'admin' LIMIT 1");
            return (int) ($stmt->fetchColumn() ?: 0);
        } catch (Throwable $e) {
            return 0;
        }
    }

    /** employee_id of the person who handles the admin account, or 0 (not linked). */
    public static function personEmployeeId(): int
    {
        return max(0, (int) AppSettings::get(self::SETTING, '0'));
    }

    /** [admin employee_id, person employee_id] when linked, otherwise [0, 0]. */
    public static function link(PDO $pdo): array
    {
        $person = self::personEmployeeId();
        $admin = $person > 0 ? self::adminEmployeeId($pdo) : 0;
        return ($admin > 0 && $person > 0 && $admin !== $person) ? [$admin, $person] : [0, 0];
    }

    /** IT department people (not the admin account) who can be linked. */
    public static function candidates(PDO $pdo): array
    {
        $admin = self::adminEmployeeId($pdo);
        $stmt = $pdo->prepare("SELECT employee_id, CONCAT(firstname, ' ', lastname) AS name FROM tblemployee
                WHERE UPPER(department) = 'IT' AND employee_id <> ? ORDER BY firstname, lastname");
        $stmt->execute([$admin]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}
