<?php
/**
 * Shared helpers for admin account list views.
 */
if (!function_exists('admin_account_usertype_class')) {
    function admin_account_usertype_class(string $usertype): string
    {
        $map = [
            'ADMIN'    => 'role-admin',
            'IT'       => 'role-it',
            'HR'       => 'role-hr',
            'HEAD'     => 'role-head',
            'OM'       => 'role-om',
            'AOM'      => 'role-aom',
            'EMPLOYEE' => 'role-employee',
        ];
        return $map[strtoupper(trim($usertype))] ?? 'role-default';
    }
}

if (!function_exists('admin_account_format_date')) {
    function admin_account_format_date(string $date): array
    {
        $ts = strtotime($date);
        if (!$ts) {
            return ['main' => $date !== '' ? $date : '—', 'time' => '', 'order' => 0];
        }
        return [
            'main'  => date('M j, Y', $ts),
            'time'  => date('g:i A', $ts),
            'order' => $ts,
        ];
    }
}

if (!function_exists('admin_employee_full_name')) {
    function admin_employee_full_name(array $row): string
    {
        $parts = array_filter([
            trim((string) ($row['firstname'] ?? '')),
            trim((string) ($row['middlename'] ?? '')),
            trim((string) ($row['lastname'] ?? '')),
        ]);
        return $parts ? implode(' ', $parts) : '—';
    }
}

if (!function_exists('admin_employee_department_class')) {
    function admin_employee_department_class(string $department): string
    {
        $map = [
            'IT'          => 'dept-it',
            'Operations'  => 'dept-operations',
            'Accounting'  => 'dept-accounting',
            'Sales'       => 'dept-sales',
            'HR'          => 'dept-hr',
            'Admin'       => 'dept-admin',
        ];
        return $map[trim($department)] ?? 'dept-default';
    }
}
