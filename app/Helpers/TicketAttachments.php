<?php

/**
 * Files and photos sent in a ticket's chat (and photos added when filing a ticket).
 * Stored outside the public folder (app/uploads/ticket_chat/<ticket id>/) and served only
 * through /ticket-comments/file after the ticket access check.
 */
class TicketAttachments
{
    public const MAX_BYTES = 10 * 1024 * 1024;

    /** extension => MIME types accepted for it */
    private const ALLOWED = [
        'jpg'  => ['image/jpeg'],
        'jpeg' => ['image/jpeg'],
        'png'  => ['image/png'],
        'gif'  => ['image/gif'],
        'webp' => ['image/webp'],
        'pdf'  => ['application/pdf'],
        'doc'  => ['application/msword', 'application/octet-stream'],
        'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/zip', 'application/octet-stream'],
        'xls'  => ['application/vnd.ms-excel', 'application/octet-stream'],
        'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/zip', 'application/octet-stream'],
        'csv'  => ['text/csv', 'text/plain', 'application/csv'],
        'txt'  => ['text/plain'],
    ];

    public const ACCEPT = '.jpg,.jpeg,.png,.gif,.webp,.pdf,.doc,.docx,.xls,.xlsx,.csv,.txt';

    public static function baseDir(): string
    {
        return dirname(__DIR__) . '/uploads/ticket_chat';
    }

    /**
     * Validate and store one uploaded file ($_FILES entry).
     * @return array{path: string, name: string, mime: string, size: int}
     * @throws RuntimeException with a message for the user
     */
    public static function store(array $file, int $ticketId, bool $imagesOnly = false): array
    {
        $error = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);
        if ($error === UPLOAD_ERR_INI_SIZE || $error === UPLOAD_ERR_FORM_SIZE) {
            throw new RuntimeException('The file is too large (max 10 MB).');
        }
        if ($error !== UPLOAD_ERR_OK || !is_uploaded_file((string) ($file['tmp_name'] ?? ''))) {
            throw new RuntimeException('The file could not be uploaded. Please try again.');
        }
        $size = (int) ($file['size'] ?? 0);
        if ($size <= 0) {
            throw new RuntimeException('The file is empty.');
        }
        if ($size > self::MAX_BYTES) {
            throw new RuntimeException('The file is too large (max 10 MB).');
        }

        $name = trim(preg_replace('/[\x00-\x1F\/\\\\]+/', '', basename((string) ($file['name'] ?? 'file'))));
        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        if (!isset(self::ALLOWED[$ext]) || ($imagesOnly && !self::isImageExt($ext))) {
            throw new RuntimeException($imagesOnly
                ? 'Only photos can be added here (JPG, PNG, GIF, WEBP).'
                : 'This file type is not allowed. Send a photo, PDF, Word, Excel or text file.');
        }
        $mime = self::detectMime((string) $file['tmp_name']) ?: 'application/octet-stream';
        if (!in_array($mime, self::ALLOWED[$ext], true)) {
            throw new RuntimeException('The file does not look like a .' . $ext . ' file.');
        }
        if (self::isImageExt($ext) && @getimagesize((string) $file['tmp_name']) === false) {
            throw new RuntimeException('The photo could not be read.');
        }

        $dir = self::baseDir() . '/' . $ticketId;
        if (!is_dir($dir) && !@mkdir($dir, 0755, true) && !is_dir($dir)) {
            throw new RuntimeException('The file could not be saved (upload folder missing).');
        }
        $stored = bin2hex(random_bytes(12)) . '.' . $ext;
        if (!move_uploaded_file((string) $file['tmp_name'], $dir . '/' . $stored)) {
            throw new RuntimeException('The file could not be saved. Please try again.');
        }
        return [
            'path' => $ticketId . '/' . $stored,
            'name' => mb_substr($name !== '' ? $name : 'file.' . $ext, 0, 255),
            'mime' => $mime,
            'size' => $size,
        ];
    }

    /** Normalise $_FILES['x'] (single or multiple) into a list of file arrays. */
    public static function filesFrom(?array $input): array
    {
        if (!$input || !isset($input['name'])) {
            return [];
        }
        if (!is_array($input['name'])) {
            return ($input['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE ? [] : [$input];
        }
        $files = [];
        foreach ($input['name'] as $i => $name) {
            if (($input['error'][$i] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
                continue;
            }
            $files[] = [
                'name' => $name,
                'type' => $input['type'][$i] ?? '',
                'tmp_name' => $input['tmp_name'][$i] ?? '',
                'error' => $input['error'][$i] ?? UPLOAD_ERR_NO_FILE,
                'size' => $input['size'][$i] ?? 0,
            ];
        }
        return $files;
    }

    public static function absolutePath(string $relative): ?string
    {
        if (!preg_match('#^\d+/[a-f0-9]{24}\.[a-z0-9]{2,5}$#', $relative)) {
            return null;
        }
        $path = self::baseDir() . '/' . $relative;
        return is_file($path) ? $path : null;
    }

    public static function isImageMime(?string $mime): bool
    {
        return in_array((string) $mime, ['image/jpeg', 'image/png', 'image/gif', 'image/webp'], true);
    }

    private static function isImageExt(string $ext): bool
    {
        return in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true);
    }

    private static function detectMime(string $path): string
    {
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = (string) finfo_file($finfo, $path);
            finfo_close($finfo);
            return $mime;
        }
        if (function_exists('mime_content_type')) {
            return (string) mime_content_type($path);
        }
        $info = @getimagesize($path);
        return $info['mime'] ?? '';
    }
}
