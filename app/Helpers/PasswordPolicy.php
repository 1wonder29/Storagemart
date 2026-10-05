<?php

/**
 * Password rules from the TMS policy: at least 8 characters with an uppercase
 * letter, a lowercase letter, a number and a symbol.
 */
class PasswordPolicy
{
    public const HINT = 'At least 8 characters with an uppercase letter, a lowercase letter, a number and a symbol.';

    /** The reason a password breaks the policy, or null when it complies. */
    public static function problem(string $password): ?string
    {
        if (strlen($password) < 8
            || !preg_match('/[A-Z]/', $password)
            || !preg_match('/[a-z]/', $password)
            || !preg_match('/\d/', $password)
            || !preg_match('/[^A-Za-z0-9]/', $password)) {
            return 'Password must be at least 8 characters with an uppercase letter, a lowercase letter, a number and a symbol.';
        }
        return null;
    }
}
