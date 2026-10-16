<?php

/**
 * "Go back to where you came from" for edit pages and their Cancel / Save buttons
 * (for example Defective Items > Edit > Save goes back to Defective Items, with its filters).
 *
 * The page to return to comes from a return_to field or query value, else from the page the
 * person came from (Referer). Only paths inside this site that start with $prefix are used,
 * so a link can never send someone to another website.
 */
class ReturnTo
{
    /** App path such as "/admin/assets/defective?month=10&year=2026", or $fallback. */
    public static function path(string $fallback, string $prefix = '/admin/', ?string $notPath = null): string
    {
        $candidates = [
            $_POST['return_to'] ?? null,
            $_GET['return_to'] ?? null,
            $_SERVER['HTTP_REFERER'] ?? null,
        ];
        foreach ($candidates as $candidate) {
            $path = self::clean($candidate, $prefix);
            if ($path === null) {
                continue;
            }
            if ($notPath !== null && strtok($path, '?') === $notPath) {
                continue;
            }
            return $path;
        }
        return $fallback;
    }

    /** The app path in $url (a path, or a full URL on this site), or null when it is not safe. */
    public static function clean($url, string $prefix = '/admin/'): ?string
    {
        $url = trim((string) $url);
        if ($url === '' || preg_match('/[\x00-\x1f\\\\]/', $url)) {
            return null;
        }
        if (preg_match('#^https?://#i', $url)) {
            $host = strtolower((string) parse_url($url, PHP_URL_HOST));
            $port = parse_url($url, PHP_URL_PORT);
            $here = strtolower((string) ($_SERVER['HTTP_HOST'] ?? ''));
            if ($host === '' || ($host . ($port ? ':' . $port : '')) !== $here && $host !== $here) {
                return null;
            }
            $query = parse_url($url, PHP_URL_QUERY);
            $url = (string) parse_url($url, PHP_URL_PATH) . ($query !== null && $query !== '' ? '?' . $query : '');
        }
        if ($url === '' || $url[0] !== '/' || strpos($url, '//') === 0) {
            return null;
        }
        // Strip the site's sub-folder (e.g. /tms/public) so the result is an app path.
        $basePath = rtrim((string) (parse_url(defined('BASE_URL') ? BASE_URL : '', PHP_URL_PATH) ?? ''), '/');
        if ($basePath !== '' && strpos($url, $basePath . '/') === 0) {
            $url = substr($url, strlen($basePath));
        }
        return strpos($url, $prefix) === 0 ? $url : null;
    }
}
