<?php

define('BASE_URL', '/');

$host = 'localhost';
$port = 3306;
$db   = 'howard_tms';
$user = 'howard_tms';
$pass = 'stor@geIT2025!'; // change this in cPanel

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
