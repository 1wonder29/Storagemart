<?php

define('BASE_URL', '/Storage-Mart-copy/StorageMart/public');
// config/config.php — use PDO (recommended)
$db_host = '127.0.0.1';
$db_name = 'storagemart';     // your DB name
$db_user = 'root';           // your DB user
$db_pass = '';               // your DB password

$dsn = "mysql:host={$db_host};dbname={$db_name};charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    echo "Database connection error: " . $e->getMessage();
    exit;
}
