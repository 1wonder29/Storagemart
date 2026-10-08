<?php

/**
 * Links saved in the database (notification action URLs, for example) are stored as
 * app paths such as "/it/tickets/view?id=3". The site can live in a sub-folder
 * (localhost/tms/public) or at a domain root, so add BASE_URL when showing them.
 */
class AppUrl
{
    public static function resolve(?string $url): string
    {
        $url = trim((string) $url);
        if ($url === '' || $url === '#') {
            return '#';
        }
        if (preg_match('#^(https?:)?//#i', $url) || $url[0] === '#') {
            return $url;
        }
        if ($url[0] !== '/') {
            return $url;
        }

        $base = rtrim((string) (defined('BASE_URL') ? BASE_URL : ''), '/');
        if ($base === '') {
            return $url;
        }
        // Already carries the base (older rows were sometimes saved with it).
        $basePath = rtrim((string) (parse_url($base, PHP_URL_PATH) ?? ''), '/');
        if ($basePath !== '' && ($url === $basePath || strpos($url, $basePath . '/') === 0)) {
            return preg_match('#^https?://#i', $base) ? preg_replace('#^(https?://[^/]+).*$#i', '$1', $base) . $url : $url;
        }
        return $base . $url;
    }
}
