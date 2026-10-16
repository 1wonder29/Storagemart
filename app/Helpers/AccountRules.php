<?php

/**
 * Checks shared by Add User (Admin), Edit User and New user request (HR), so a mistake is
 * caught with a clear message instead of a database error or a half-wrong account:
 *  - Employee ID: numbers only, fits the database, not used by someone else
 *  - Email: a full address, not used by another employee (the company Gmail is personal)
 *  - Role and department go together (IT staff must be in IT to be assigned tickets, ...)
 *  - Company Gmail: all set-up steps ticked before the TMS account is made
 */
class AccountRules
{
    /** Roles that only work in one department (codes from tbldepartments). */
    public const DEPARTMENT_FOR_ROLE = [
        'IT'  => 'IT',
        'HR'  => 'HRMD',
        'AOM' => 'Operations',
        'HOM' => 'Operations',
    ];

    public const MAX_EMPLOYEE_ID = 2147483647;

    public static function employeeIdProblem(PDO $pdo, string $id, ?int $currentEmployeeId = null): ?string
    {
        $id = trim($id);
        if ($id === '' || !ctype_digit($id) || (int) $id <= 0) {
            return 'Employee ID is required and must be numbers only (for example 2600192).';
        }
        if (strlen($id) > 10 || (float) $id > self::MAX_EMPLOYEE_ID) {
            return 'Employee ID ' . $id . ' is too long. Use the Employee ID from the masterlist (for example 2600192).';
        }
        if ($currentEmployeeId !== null && (int) $id === $currentEmployeeId) {
            return null;
        }
        $owner = self::employee($pdo, 'e.employee_id = ?', [(int) $id]);
        if ($owner) {
            return 'Employee ID ' . $id . ' already belongs to ' . $owner . '. Check the Employee ID in the masterlist.';
        }
        return null;
    }

    public static function emailProblem(PDO $pdo, string $email, ?int $currentEmployeeId = null): ?string
    {
        $email = trim($email);
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return 'Enter a full email address with @, for example storagemart.juandelacruz@gmail.com.';
        }
        $owner = self::employee($pdo, 'LOWER(TRIM(e.email)) = LOWER(?)' . ($currentEmployeeId !== null ? ' AND e.employee_id <> ' . (int) $currentEmployeeId : ''), [$email]);
        if ($owner) {
            return 'The email ' . $email . ' is already used by ' . $owner . '. Each employee needs their own company Gmail.';
        }
        return null;
    }

    /** The department a role needs, when that department exists in the list (else no rule). */
    public static function requiredDepartment(PDO $pdo, string $role): ?string
    {
        $code = self::DEPARTMENT_FOR_ROLE[strtoupper(trim($role))] ?? null;
        if ($code === null) {
            return null;
        }
        try {
            $stmt = $pdo->prepare('SELECT code FROM tbldepartments WHERE code = ? LIMIT 1');
            $stmt->execute([$code]);
            return $stmt->fetchColumn() ? $code : null;
        } catch (Throwable $e) {
            return null;
        }
    }

    public static function departmentProblem(PDO $pdo, string $role, string $department): ?string
    {
        $need = self::requiredDepartment($pdo, $role);
        if ($need !== null && strcasecmp(trim($department), $need) !== 0) {
            require_once __DIR__ . '/RoleLabel.php';
            return RoleLabel::of(strtoupper($role)) . ' accounts must be in the ' . $need . ' department'
                . ($need === 'IT' ? ' (only the IT department can be assigned tickets).' : '.');
        }
        return null;
    }

    /** Role => department for the forms (only rules whose department exists). */
    public static function departmentMap(PDO $pdo): array
    {
        $map = [];
        foreach (array_keys(self::DEPARTMENT_FOR_ROLE) as $role) {
            if ($code = self::requiredDepartment($pdo, $role)) {
                $map[$role] = $code;
            }
        }
        return $map;
    }

    /** The company Gmail is made first: every set-up step must be ticked. */
    public static function gmailChecksProblem(array $checks): ?string
    {
        require_once __DIR__ . '/../Models/CompanyGmailModel.php';
        foreach (array_keys(CompanyGmailModel::CHECKS) as $key) {
            if (empty($checks[$key])) {
                return 'Create the company Gmail first and tick all ' . count(CompanyGmailModel::CHECKS)
                    . ' Company Gmail steps (account created, password given, phone and recovery email verified, 2-step on).';
            }
        }
        return null;
    }

    /** "Juan Dela Cruz (Employee ID 2600192)" for the first employee matching $where, or null. */
    private static function employee(PDO $pdo, string $where, array $params): ?string
    {
        $stmt = $pdo->prepare("SELECT e.employee_id, TRIM(CONCAT(COALESCE(e.firstname, ''), ' ', COALESCE(e.lastname, ''))) AS name
                               FROM tblemployee e WHERE $where LIMIT 1");
        $stmt->execute($params);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (($row['name'] !== '' ? $row['name'] : 'another employee') . ' (Employee ID ' . $row['employee_id'] . ')') : null;
    }
}
