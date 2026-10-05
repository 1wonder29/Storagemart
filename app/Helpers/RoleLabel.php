<?php

/**
 * Display names for account roles (tblaccounts.usertype), worded like the job
 * titles on the company masterlist. Only the wording changes; the stored codes
 * and every access check keep using the codes.
 */
class RoleLabel
{
    /** Roles an account can be given, in the order the forms list them. */
    private const LABELS = [
        'ADMIN'    => 'Administrator',
        'HEAD'     => 'Department Head',
        'HR'       => 'Human Resources',
        'IT'       => 'Information Technology',
        'HOM'      => 'Operations Manager',
        'AOM'      => 'Area Operations Manager',
        'EMPLOYEE' => 'Employee',
    ];

    /** e.g. 'HOM' => 'Operations Manager'. Unknown values are shown as they are. */
    public static function of(?string $usertype): string
    {
        $value = trim((string) $usertype);
        return self::LABELS[strtoupper($value)] ?? $value;
    }

    /** @return array<string, string> code => label */
    public static function assignable(): array
    {
        return self::LABELS;
    }
}
