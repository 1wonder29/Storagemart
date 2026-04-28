<?php
require_once __DIR__ . '/../config/config.php';

try {
    $pdo = new PDO('mysql:host=localhost;dbname=howard_tms', 'root', '');
    $stmt = $pdo->query('SELECT id, action_url FROM notifications ORDER BY id DESC LIMIT 20');
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Sample Notification URLs:\n";
    echo str_repeat("-", 80) . "\n";
    foreach ($rows as $row) {
        echo 'ID: ' . $row['id'] . ' | URL: ' . $row['action_url'] . "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
