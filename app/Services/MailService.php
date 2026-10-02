<?php

/**
 * Thin wrapper around PHP's mail(). Never throws: failures are logged and reported as false,
 * so a mail problem can never block creating or assigning a ticket.
 *
 * Optional .env settings:
 *   MAIL_FROM            sender address (default: no-reply@<site host>)
 *   MAIL_ENABLED=false   turn all outgoing mail off
 */
class MailService
{
    public static function isEnabled(): bool
    {
        return strtolower((string) getenv('MAIL_ENABLED')) !== 'false';
    }

    public static function fromAddress(): string
    {
        $from = trim((string) getenv('MAIL_FROM'));
        if ($from !== '' && filter_var($from, FILTER_VALIDATE_EMAIL)) {
            return $from;
        }

        $host = parse_url((string) (defined('BASE_URL') ? BASE_URL : ''), PHP_URL_HOST);
        if (!$host) {
            $host = preg_replace('/:\d+$/', '', (string) ($_SERVER['HTTP_HOST'] ?? 'localhost'));
        }
        return 'no-reply@' . ($host ?: 'localhost');
    }

    public static function send(string $to, string $subject, string $htmlBody, ?string $replyTo = null): bool
    {
        if (!self::isEnabled()) {
            return false;
        }

        $to = self::clean($to);
        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            self::log($to, $subject, 'skipped (invalid recipient)');
            return false;
        }

        $subject = self::clean($subject);
        $headers = [
            'MIME-Version: 1.0',
            'Content-Type: text/html; charset=UTF-8',
            'From: Storage Mart TMS <' . self::fromAddress() . '>',
            'X-Mailer: StorageMart-TMS',
        ];
        if ($replyTo !== null && filter_var(self::clean($replyTo), FILTER_VALIDATE_EMAIL)) {
            $headers[] = 'Reply-To: ' . self::clean($replyTo);
        }

        $encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';

        try {
            $sent = @mail($to, $encodedSubject, $htmlBody, implode("\r\n", $headers));
        } catch (Throwable $e) {
            error_log('MailService::send failed: ' . $e->getMessage());
            $sent = false;
        }

        self::log($to, $subject, $sent ? 'sent' : 'failed');
        return (bool) $sent;
    }

    /** Header values must never contain line breaks. */
    private static function clean(string $value): string
    {
        return trim(str_replace(["\r", "\n", "\0"], ' ', $value));
    }

    private static function log(string $to, string $subject, string $result): void
    {
        $dir = __DIR__ . '/../logs';
        if (!is_dir($dir) || !is_writable($dir)) {
            return;
        }
        $line = sprintf("[%s] to=%s subject=%s result=%s\n", date('Y-m-d H:i:s'), $to, $subject, $result);
        @file_put_contents($dir . '/mail.log', $line, FILE_APPEND | LOCK_EX);
    }
}
