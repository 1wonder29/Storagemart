<?php
require 'config/config.php';

try {
    $stmt = $pdo->query('SELECT account_id, username, usertype, status, password FROM tblaccounts WHERE usertype="HR"');
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "HR Accounts in database:\n";
    echo "Count: " . count($rows) . "\n\n";
    
    foreach ($rows as $row) {
        echo "ID: {$row['account_id']}\n";
        echo "Username: {$row['username']}\n";
        echo "Status: {$row['status']}\n";
        echo "Password (first 30 chars): " . substr($row['password'], 0, 30) . "...\n";
        echo "Password length: " . strlen($row['password']) . "\n";
        echo "---\n";
    }
    
    if (empty($rows)) {
        echo "NO HR ACCOUNTS FOUND!\n";
    }
    
    // Also check what's in the Account model
    echo "\n\nNow checking if hr_test user exists:\n";
    $stmt2 = $pdo->query('SELECT account_id, username, usertype, status FROM tblaccounts WHERE username="hr_test"');
    $hr_test = $stmt2->fetch(PDO::FETCH_ASSOC);
    
    if ($hr_test) {
        echo "✓ hr_test FOUND: ID={$hr_test['account_id']}, type={$hr_test['usertype']}, status={$hr_test['status']}\n";
    } else {
        echo "✗ hr_test NOT FOUND\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
