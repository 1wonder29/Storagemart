<?php

/**
 * Gate for the whole HR module (dashboard, employees, uniforms, HR tickets/assets).
 *
 * Access is by role only: an account must have usertype = 'HR'. There is no
 * department-based bypass — a Department Head, including the Head of HRMD, does not
 * get HR access just by being in that department. Give someone HR access by setting
 * their account's usertype to HR (Admin -> Users -> Accounts -> Edit).
 */
class HrDepartmentAccess
{
    public static function canAccessHr(): bool
    {
        return strtoupper($_SESSION['usertype'] ?? '') === 'HR';
    }
}
