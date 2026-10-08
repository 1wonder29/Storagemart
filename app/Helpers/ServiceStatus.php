<?php

require_once __DIR__ . '/AppSettings.php';

/**
 * Quick health check for email / Outlook tickets (IT only): is the company domain's
 * DNS answering (Namecheap), is the website / mail server up (HostGator)?
 * Neither provider has a public status API, so the official status pages are linked.
 */
class ServiceStatus
{
    public const NAMECHEAP_STATUS_URL = 'https://www.namecheap.com/status-updates/';
    public const HOSTGATOR_STATUS_URL = 'https://www.hostgator.com/status';

    /** True when the ticket looks like an email / Outlook problem. */
    public static function isEmailTicket(array $ticket): bool
    {
        $text = ($ticket['category'] ?? '') . ' ' . ($ticket['subject'] ?? '') . ' ' . ($ticket['concern_details'] ?? '');
        return (bool) preg_match('/\b(e-?mail|outlook|webmail|mailbox|inbox|smtp|imap)\b/i', $text)
            || stripos((string) ($ticket['category'] ?? ''), 'Email') !== false;
    }

    public static function domain(): string
    {
        $domain = strtolower(trim((string) AppSettings::get('status_company_domain', 'storagemart.com.ph')));
        return preg_match('/^[a-z0-9.-]+\.[a-z]{2,}$/', $domain) ? $domain : 'storagemart.com.ph';
    }

    /**
     * @return array{checked_at:string, domain:string, overall:string, checks:array<int, array{name:string, provider:string, ok:?bool, detail:string, ms:int}>}
     */
    public static function run(): array
    {
        $domain = self::domain();
        $checks = [];

        // 1. DNS (Namecheap): the domain's name servers answer.
        $t = microtime(true);
        $ns = @dns_get_record($domain, DNS_NS) ?: [];
        $a = @dns_get_record($domain, DNS_A) ?: [];
        $nsHosts = array_column($ns, 'target');
        $checks[] = [
            'name' => 'Domain DNS (' . $domain . ')',
            'provider' => self::providerOf(implode(' ', $nsHosts), 'DNS'),
            'ok' => (bool) ($ns || $a),
            'detail' => $ns || $a
                ? 'Name servers: ' . (implode(', ', array_slice($nsHosts, 0, 3)) ?: 'answering')
                : 'No DNS answer for ' . $domain . '. The DNS host may be down or the domain (Namecheap) expired.',
            'ms' => (int) round((microtime(true) - $t) * 1000),
        ];

        // 2. Mail server (MX) accepts connections.
        $t = microtime(true);
        $mx = @dns_get_record($domain, DNS_MX) ?: [];
        usort($mx, static function ($x, $y) { return ($x['pri'] ?? 0) <=> ($y['pri'] ?? 0); });
        $mxHost = (string) ($mx[0]['target'] ?? '');
        $mailOk = null;
        $mailDetail = 'No MX record found for ' . $domain . '.';
        if ($mxHost !== '') {
            // Outlook connects to the IMAP / SMTP host, not the MX; try the provider's host first.
            $hosts = [];
            if (stripos($mxHost, 'privateemail.com') !== false) {
                $hosts[] = 'mail.privateemail.com';
            } elseif (stripos($mxHost, 'google') !== false) {
                $hosts[] = 'imap.gmail.com';
            }
            $hosts[] = 'mail.' . $domain;
            $tried = [];
            foreach (array_unique($hosts) as $host) {
                foreach ([993, 465] as $port) {
                    $errno = 0;
                    $errstr = '';
                    $socket = @fsockopen('ssl://' . $host, $port, $errno, $errstr, 3);
                    $tried[] = $host . ':' . $port;
                    if ($socket) {
                        fclose($socket);
                        $mailOk = true;
                        $mailDetail = $host . ' (Outlook ' . ($port === 993 ? 'IMAP' : 'SMTP') . ') answered on port ' . $port . '. MX: ' . $mxHost . '.';
                        break 2;
                    }
                }
            }
            if ($mailOk === null) {
                $mailOk = false;
                $mailDetail = 'No answer from ' . implode(', ', $tried) . '. MX: ' . $mxHost . '.';
            }
        } else {
            $mailOk = false;
        }
        $checks[] = [
            'name' => 'Mail server',
            'provider' => self::providerOf($mxHost, 'Mail host'),
            'ok' => $mailOk,
            'detail' => $mailDetail,
            'ms' => (int) round((microtime(true) - $t) * 1000),
        ];

        // 3. Websites on the hosting server (the TMS login page goes through the rewrite rules).
        foreach (['https://' . $domain, 'https://tms.' . $domain . '/login'] as $url) {
            $checks[] = self::http('Website ' . parse_url($url, PHP_URL_HOST), self::providerOf(implode(' ', $nsHosts), 'Hosting'), $url);
        }

        $failed = count(array_filter($checks, static function ($c) { return $c['ok'] === false; }));
        return [
            'checked_at' => date('Y-m-d H:i:s'),
            'domain' => $domain,
            'overall' => $failed === 0 ? 'ok' : ($failed === count($checks) ? 'down' : 'partial'),
            'checks' => $checks,
            'links' => [
                ['label' => 'Namecheap status', 'url' => self::NAMECHEAP_STATUS_URL],
                ['label' => 'HostGator status', 'url' => self::HOSTGATOR_STATUS_URL],
            ],
        ];
    }

    private static function providerOf(string $hosts, string $fallback): string
    {
        $hosts = strtolower($hosts);
        if (strpos($hosts, 'hostgator') !== false) {
            return 'HostGator';
        }
        if (strpos($hosts, 'privateemail') !== false || strpos($hosts, 'namecheap') !== false || strpos($hosts, 'registrar-servers') !== false) {
            return 'Namecheap';
        }
        if (strpos($hosts, 'google') !== false) {
            return 'Google';
        }
        return $fallback;
    }

    private static function http(string $name, string $provider, string $url): array
    {
        $t = microtime(true);
        $code = 0;
        $error = '';
        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_NOBODY => false,
                CURLOPT_RANGE => '0-1023',
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_MAXREDIRS => 3,
                CURLOPT_TIMEOUT => 6,
                CURLOPT_CONNECTTIMEOUT => 4,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_USERAGENT => 'StorageMart-TMS-status-check',
            ]);
            curl_exec($ch);
            $code = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
            $error = curl_error($ch);
            curl_close($ch);
        } else {
            $context = stream_context_create(['http' => ['method' => 'HEAD', 'timeout' => 6, 'ignore_errors' => true]]);
            $headers = @get_headers($url, false, $context);
            if ($headers && preg_match('#\s(\d{3})\s#', (string) $headers[0], $m)) {
                $code = (int) $m[1];
            }
        }
        $ms = (int) round((microtime(true) - $t) * 1000);
        $ok = $code > 0 && $code < 500;
        return [
            'name' => $name,
            'provider' => $provider,
            'ok' => $ok,
            'detail' => $ok
                ? 'Responded with HTTP ' . $code . ($ms > 3000 ? ' (slow)' : '') . '.'
                : ($code ? 'Server error HTTP ' . $code . '.' : 'No response' . ($error !== '' ? ': ' . $error : '') . '.'),
            'ms' => $ms,
        ];
    }
}
