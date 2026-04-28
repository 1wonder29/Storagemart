<?php

// Load environment variables from .env
function loadEnv($file = __DIR__ . '/../.env') {
    if (file_exists($file)) {
        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
                [$key, $value] = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value, '" \t\n\r\0\x0B');
                if (!isset($_ENV[$key])) {
                    putenv("$key=$value");
                }
            }
        }
    }
}
loadEnv();

define('BASE_URL', getenv('BASE_URL') ?: '');

$host = getenv('DB_HOST') ?: 'localhost';
$port = getenv('DB_PORT') ?: '3306';
$db   = getenv('DB_NAME') ?: 'howard_tms';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') ?: '';

try {
    $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    // Log the error securely
    error_log('Database connection failed: ' . $e->getMessage());
    // Show generic error to user
    http_response_code(500);
    die('Service temporarily unavailable. Please try again later.');
}
