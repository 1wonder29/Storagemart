<?php
// Script to add failed login attempt tracking to accounts table
require_once __DIR__ . '/../config/config.php';

try {
    $pdo = new PDO('mysql:host=localhost;dbname=howard_tms', 'root', '');
    
    // Add columns if they don't exist
    $pdo->exec("ALTER TABLE tblaccounts ADD COLUMN IF NOT EXISTS failed_attempts INT DEFAULT 0");
    $pdo->exec("ALTER TABLE tblaccounts ADD COLUMN IF NOT EXISTS last_attempt_time DATETIME NULL");
    
    echo "✅ Database schema updated successfully!\n";
    echo "Added columns: failed_attempts, last_attempt_time\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>
