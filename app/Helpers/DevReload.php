<?php

class DevReload
{
    /** @var string[]|null */
    private static $watchDirs = null;

    public static function isEnabled(): bool
    {
        return (getenv('APP_ENV') ?: '') === 'development';
    }

    public static function version(): int
    {
        static $cached = null;
        static $cachedAt = 0.0;

        $now = microtime(true);
        if ($cached !== null && ($now - $cachedAt) < 0.8) {
            return $cached;
        }

        $max = 0;
        foreach (self::watchDirs() as $dir) {
            $max = max($max, self::maxMtime($dir));
        }

        $cached = $max;
        $cachedAt = $now;

        return $max;
    }

    /** @return string[] */
    private static function watchDirs(): array
    {
        if (self::$watchDirs !== null) {
            return self::$watchDirs;
        }

        $root = dirname(__DIR__, 2);
        self::$watchDirs = [
            $root . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Views',
            $root . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Controllers',
            $root . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'css',
            $root . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'js',
            $root . DIRECTORY_SEPARATOR . 'config',
        ];

        return self::$watchDirs;
    }

    private static function maxMtime(string $path): int
    {
        if (!file_exists($path)) {
            return 0;
        }

        if (is_file($path)) {
            return (int) filemtime($path);
        }

        $max = (int) filemtime($path);
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if (!$file->isFile()) {
                continue;
            }

            $ext = strtolower($file->getExtension());
            if (!in_array($ext, ['php', 'css', 'js'], true)) {
                continue;
            }

            $max = max($max, (int) $file->getMTime());
        }

        return $max;
    }
}
