<?php
require_once __DIR__ . '/../config/config.php';

try {
    $pdo = new PDO('mysql:host=localhost;dbname=howard_tms', 'root', '');
    
    // Fix double slashes
    $pdo->exec("UPDATE notifications SET action_url = REPLACE(action_url, '//', '/') WHERE action_url LIKE '//%'");
    
    // Fix /head/employee to /head/tickets
    $pdo->exec("UPDATE notifications SET action_url = REPLACE(action_url, '/head/employee', '/head/tickets') WHERE action_url = '/head/employee'");
    
    echo "✅ Fixed notification URLs!\n";
    
    // Show updated URLs
    $stmt = $pdo->query('SELECT id, action_url FROM notifications ORDER BY id DESC LIMIT 20');
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "\nUpdated Notification URLs:\n";
    echo str_repeat("-", 80) . "\n";
    foreach ($rows as $row) {
        echo 'ID: ' . $row['id'] . ' | URL: ' . $row['action_url'] . "\n";
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>
