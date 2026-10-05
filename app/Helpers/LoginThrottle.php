<?php

/**
 * Failed-login lock. Three wrong passwords lock that username for 15 minutes for
 * the network address the attempts came from, then the lock lifts by itself.
 * Because the lock is per address, someone guessing from elsewhere cannot lock
 * the real user (or every admin) out of the system. Admins can unlock early.
 *
 * Never throws: if the table can't be used, login simply isn't throttled.
 */
class LoginThrottle
{
    public const MAX_ATTEMPTS = 3;
    public const LOCK_MINUTES = 15;

    private static bool $ready = false;

    public static function clientIp(): string
    {
        return substr((string) ($_SERVER['REMOTE_ADDR'] ?? 'unknown'), 0, 45);
    }

    /** Minutes left on the lock for this username from this address; 0 when not locked. */
    public static function minutesLeft(PDO $pdo, string $username, string $ip): int
    {
        try {
            self::ensureTable($pdo);
            $stmt = $pdo->prepare(
                'SELECT attempts, TIMESTAMPDIFF(SECOND, NOW(), last_attempt + INTERVAL ' . self::LOCK_MINUTES . ' MINUTE) AS secs
                 FROM tbllogin_attempts WHERE username = ? AND ip_address = ?'
            );
            $stmt->execute([self::key($username), $ip]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$row || (int) $row['attempts'] < self::MAX_ATTEMPTS || (int) $row['secs'] <= 0) {
                return 0;
            }
            return (int) ceil((int) $row['secs'] / 60);
        } catch (Throwable $e) {
            error_log('LoginThrottle::minutesLeft: ' . $e->getMessage());
            return 0;
        }
    }

    /** Records a wrong password and returns the consecutive failures in the current window. */
    public static function recordFailure(PDO $pdo, string $username, string $ip): int
    {
        try {
            self::ensureTable($pdo);
            // A failure after the window has passed starts a new count.
            $pdo->prepare(
                'INSERT INTO tbllogin_attempts (username, ip_address, attempts, last_attempt) VALUES (?, ?, 1, NOW())
                 ON DUPLICATE KEY UPDATE
                    attempts = IF(last_attempt < NOW() - INTERVAL ' . self::LOCK_MINUTES . ' MINUTE, 1, attempts + 1),
                    last_attempt = NOW()'
            )->execute([self::key($username), $ip]);

            $stmt = $pdo->prepare('SELECT attempts FROM tbllogin_attempts WHERE username = ? AND ip_address = ?');
            $stmt->execute([self::key($username), $ip]);
            return max(1, (int) $stmt->fetchColumn());
        } catch (Throwable $e) {
            error_log('LoginThrottle::recordFailure: ' . $e->getMessage());
            return 1;
        }
    }

    /** Successful login from this address. */
    public static function clear(PDO $pdo, string $username, string $ip): void
    {
        self::run($pdo, 'DELETE FROM tbllogin_attempts WHERE username = ? AND ip_address = ?', [self::key($username), $ip]);
    }

    /** Admin unlock: lift the lock from every address. */
    public static function clearAll(PDO $pdo, string $username): void
    {
        self::run($pdo, 'DELETE FROM tbllogin_attempts WHERE username = ?', [self::key($username)]);
    }

    /** @return array<string, true> lowercased usernames that are locked right now */
    public static function lockedUsernames(PDO $pdo): array
    {
        try {
            self::ensureTable($pdo);
            $stmt = $pdo->query(
                'SELECT DISTINCT username FROM tbllogin_attempts
                 WHERE attempts >= ' . self::MAX_ATTEMPTS . ' AND last_attempt > NOW() - INTERVAL ' . self::LOCK_MINUTES . ' MINUTE'
            );
            return array_fill_keys($stmt->fetchAll(PDO::FETCH_COLUMN), true);
        } catch (Throwable $e) {
            error_log('LoginThrottle::lockedUsernames: ' . $e->getMessage());
            return [];
        }
    }

    public static function key(string $username): string
    {
        return substr(strtolower(trim($username)), 0, 100);
    }

    private static function run(PDO $pdo, string $sql, array $params): void
    {
        try {
            self::ensureTable($pdo);
            $pdo->prepare($sql)->execute($params);
        } catch (Throwable $e) {
            error_log('LoginThrottle: ' . $e->getMessage());
        }
    }

    private static function ensureTable(PDO $pdo): void
    {
        if (self::$ready) {
            return;
        }
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS tbllogin_attempts (
                username VARCHAR(100) NOT NULL,
                ip_address VARCHAR(45) NOT NULL,
                attempts INT NOT NULL DEFAULT 0,
                last_attempt DATETIME NOT NULL,
                PRIMARY KEY (username, ip_address)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
        );
        self::$ready = true;
    }
}
