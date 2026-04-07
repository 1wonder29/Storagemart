<?php

define('BASE_URL', '');

$host = getenv('MYSQLHOST');
$port = getenv('MYSQLPORT');
$db   = getenv('MYSQL_DATABASE');
$user = getenv('MYSQLUSER');
$pass = getenv('MYSQLPASSWORD');

/**
 * PDO (new / MVC / future)
 */
try {
    $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    die("Database connection failed");
}

/**
 * mysqli (legacy support)
 */
$link = mysqli_connect($host, $user, $pass, $db, $port);

if (!$link) {
    die("MySQLi connection failed: " . mysqli_connect_error());
}

date_default_timezone_set("Asia/Manila");
