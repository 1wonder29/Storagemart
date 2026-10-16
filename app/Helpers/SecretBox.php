<?php

/**
 * Encrypts short secrets kept in the database (the company Gmail password Admin and HR can see).
 * AES-256-GCM with a random key stored in a file outside the website folder
 * (app/uploads/keys/secret.key, made on first use). A copy of the database alone cannot be read.
 * If the key file is lost, saved secrets cannot be read any more and have to be typed again.
 */
class SecretBox
{
    private const PREFIX = 'v1:';

    public static function available(): bool
    {
        return function_exists('openssl_encrypt') && in_array('aes-256-gcm', openssl_get_cipher_methods(), true);
    }

    public static function encrypt(string $plain): ?string
    {
        $key = self::key();
        if ($key === null || !self::available()) {
            return null;
        }
        $iv = random_bytes(12);
        $tag = '';
        $cipher = openssl_encrypt($plain, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag);
        return $cipher === false ? null : self::PREFIX . base64_encode($iv . $tag . $cipher);
    }

    /** The plain text, or null when it is empty or cannot be read (wrong or missing key). */
    public static function decrypt(?string $stored): ?string
    {
        $stored = (string) $stored;
        if (strpos($stored, self::PREFIX) !== 0 || !self::available()) {
            return null;
        }
        $raw = base64_decode(substr($stored, strlen(self::PREFIX)), true);
        $key = self::key(false);
        if ($raw === false || strlen($raw) < 29 || $key === null) {
            return null;
        }
        $plain = openssl_decrypt(substr($raw, 28), 'aes-256-gcm', $key, OPENSSL_RAW_DATA, substr($raw, 0, 12), substr($raw, 12, 16));
        return $plain === false ? null : $plain;
    }

    private static function key(bool $create = true): ?string
    {
        static $key = null;
        if ($key !== null) {
            return $key;
        }
        $dir = dirname(__DIR__) . '/uploads/keys';
        $file = $dir . '/secret.key';
        if (is_file($file)) {
            $raw = base64_decode(trim((string) file_get_contents($file)), true);
            if ($raw !== false && strlen($raw) === 32) {
                return $key = $raw;
            }
            return null;
        }
        if (!$create) {
            return null;
        }
        if (!is_dir($dir) && !@mkdir($dir, 0700, true)) {
            return null;
        }
        // The folder is outside the public website folder; this also blocks it if that ever changes.
        @file_put_contents($dir . '/.htaccess', "<IfModule mod_authz_core.c>\nRequire all denied\n</IfModule>\n<IfModule !mod_authz_core.c>\nDeny from all\n</IfModule>\n");
        $raw = random_bytes(32);
        if (@file_put_contents($file, base64_encode($raw), LOCK_EX) === false) {
            return null;
        }
        @chmod($file, 0600);
        return $key = $raw;
    }
}
