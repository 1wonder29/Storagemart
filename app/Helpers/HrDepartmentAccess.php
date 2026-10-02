<?php

require_once __DIR__ . '/../Models/employee/Employee.php';

class HrDepartmentAccess
{
    private static ?bool $hrHeadResult = null;

    public static function isHrDepartmentHead(?int $accountId = null): bool
    {
        $accountId = $accountId ?? (int) ($_SESSION['account_id'] ?? 0);
        if ($accountId <= 0) {
            return false;
        }

        if ($accountId === (int) ($_SESSION['account_id'] ?? 0) && self::$hrHeadResult !== null) {
            return self::$hrHeadResult;
        }

        if (strtoupper($_SESSION['usertype'] ?? '') !== 'HEAD') {
            if ($accountId === (int) ($_SESSION['account_id'] ?? 0)) {
                self::$hrHeadResult = false;
            }
            return false;
        }

        $employeeModel = new Employee();
        $user = $employeeModel->fetchUserDetails($accountId);
        if (!$user) {
            if ($accountId === (int) ($_SESSION['account_id'] ?? 0)) {
                self::$hrHeadResult = false;
            }
            return false;
        }

        $headEmployee = $employeeModel->getEmployeeById((int) ($user['employee_id'] ?? 0));
        $department = strtoupper(trim((string) ($headEmployee['department'] ?? '')));
        $isHrHead = ($department === 'HRMD');

        if ($accountId === (int) ($_SESSION['account_id'] ?? 0)) {
            self::$hrHeadResult = $isHrHead;
        }

        return $isHrHead;
    }

    public static function canManageUniforms(): bool
    {
        if (strtoupper($_SESSION['usertype'] ?? '') === 'HR') {
            return true;
        }

        return self::isHrDepartmentHead();
    }
}
