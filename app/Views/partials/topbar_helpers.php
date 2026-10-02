<?php

if (!function_exists('tms_normalize_topbar_label')) {
    function tms_normalize_topbar_label(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }

        if ($value === strtoupper($value) && preg_match('/[A-Z]/', $value)) {
            return ucwords(strtolower($value));
        }

        return $value;
    }
}

if (!function_exists('tms_topbar_labels_are_redundant')) {
    function tms_topbar_labels_are_redundant(string $name, string $role): bool
    {
        $nameKey = strtolower(preg_replace('/[^a-z0-9]/', '', $name));
        $roleKey = strtolower(preg_replace('/[^a-z0-9]/', '', $role));

        if ($nameKey === '' || $roleKey === '') {
            return false;
        }

        if ($nameKey === $roleKey) {
            return true;
        }

        if (str_contains($nameKey, $roleKey) || str_contains($roleKey, $nameKey)) {
            return true;
        }

        $equivalentPairs = [
            ['administrator', 'admin'],
            ['employee', 'emp'],
        ];

        foreach ($equivalentPairs as [$left, $right]) {
            if (
                ($nameKey === $left && $roleKey === $right)
                || ($nameKey === $right && $roleKey === $left)
            ) {
                return true;
            }
        }

        return false;
    }
}

if (!function_exists('tms_topbar_user_labels')) {
    /**
     * @return array{name: string, role: string}
     */
    function tms_topbar_user_labels(string $firstname, string $position, string $usertype = ''): array
    {
        $firstname = trim($firstname);
        $position = trim($position);
        $usertype = trim($usertype);

        $role = $position !== '' ? $position : $usertype;

        if ($firstname === '') {
            $firstname = $role !== '' ? $role : 'User';
            $role = '';
        }

        $firstname = tms_normalize_topbar_label($firstname);
        $role = tms_normalize_topbar_label($role);

        if ($role !== '' && tms_topbar_labels_are_redundant($firstname, $role)) {
            $role = '';
        }

        return [
            'name' => $firstname,
            'role' => $role,
        ];
    }
}
