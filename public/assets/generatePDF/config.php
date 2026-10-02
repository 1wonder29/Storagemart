<?php

define('BASE_URL', '/');

$host = 'localhost';
$port = 3306;
$db   = 'howard_tms';
$user = 'root';  // Changed from howard_tms to match main config
$pass = '';      // Changed from stor@geIT2025! to empty, matching main config

/**
 * PDO
 */
try {
    $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

/**
 * mysqli
 */
$link = mysqli_connect($host, $user, $pass, $db, $port);

if (!$link) {
    die("MySQLi connection failed: " . mysqli_connect_error());
}

date_default_timezone_set("Asia/Manila");
