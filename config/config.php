<?php

// SET TIMEZONE FIRST - before any other code
// This ensures all date() calls use the correct timezone
if (function_exists('date_default_timezone_set')) {
    date_default_timezone_set('Asia/Manila');
}

// Load environment variables from .env
function loadEnv($file = __DIR__ . '/../.env') {
    if (file_exists($file)) {
        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
                [$key, $value] = explode('=', $line, 2);
                $key = trim($key);
                // CRITICAL FIX: Only trim whitespace and quotes properly, don't use character class
                // that includes letters like 't' or 'r'
                $value = trim($value);
                // Remove surrounding quotes if present
                if ((substr($value, 0, 1) === '"' && substr($value, -1) === '"') ||
                    (substr($value, 0, 1) === "'" && substr($value, -1) === "'")) {
                    $value = substr($value, 1, -1);
                }
                if (!isset($_ENV[$key])) {
                    putenv("$key=$value");
                }
            }
        }
    }
}
loadEnv();

$baseUrl = getenv('BASE_URL') ?: '';

// CRITICAL SECURITY: Ensure BASE_URL never has a port that could cause CORS issues
// If somehow BASE_URL got set to localhost:8000 or similar, strip the port
if (!empty($baseUrl) && strpos($baseUrl, ':') !== false) {
    // Extract just the scheme and host, remove port
    $parsed = parse_url($baseUrl);
    $scheme = $parsed['scheme'] ?? 'http';
    $host = $parsed['host'] ?? '';
    $path = $parsed['path'] ?? '';
    if (!empty($host)) {
        error_log("WARNING: BASE_URL had port. Original: $baseUrl");
        $baseUrl = "$scheme://$host$path";
        error_log("WARNING: BASE_URL stripped. Now: $baseUrl");
    }
}

define('BASE_URL', $baseUrl);

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
    // Set database session timezone to match application timezone
    $pdo->exec("SET time_zone = '+08:00'");
} catch (PDOException $e) {
    // Log the error securely
    error_log('Database connection failed: ' . $e->getMessage());
    // Show generic error to user
    http_response_code(500);
    die('Service temporarily unavailable. Please try again later.');
}
