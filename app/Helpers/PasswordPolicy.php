<?php

/**
 * Password rules from the TMS policy: at least 8 characters with an uppercase
 * letter, a lowercase letter, a number and a symbol.
 */
class PasswordPolicy
{
    public const HINT = 'At least 8 characters with an uppercase letter, a lowercase letter, a number and a symbol.';

    /** Admin setting a password for someone: no strength rule (e.g. just the surname, VILLAROS). */
    public const ADMIN_HINT = 'Any password of 4 or more characters, for example the employee\'s surname (VILLAROS). '
        . 'The employee can change it from Change password after logging in.';

    /** Rule when Admin sets or resets another account's password. */
    public static function adminSetProblem(string $password): ?string
    {
        if (strlen($password) < 4) {
            return 'Password must be at least 4 characters.';
        }
        if (strlen($password) > 72) {
            return 'Password is too long (max 72 characters).';
        }
        return null;
    }

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
