<?php
$host = 'localhost';
$port = '3306';
$db = 'howard_tms';
$user = 'root';
$pass = '';

echo "Testing MySQL Connection:\n\n";
echo "Host: $host\n";
echo "Port: $port\n";
echo "Database: $db\n";
echo "User: $user\n\n";

try {
    $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";
    echo "DSN: $dsn\n\n";
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    echo "✓ Connection successful!\n";
    
    // Test a query
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM tblaccounts");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "✓ Found " . $result['count'] . " accounts in database\n";
} catch (PDOException $e) {
    echo "✗ Connection failed:\n";
    echo "Error: " . $e->getMessage() . "\n";
}
?>
