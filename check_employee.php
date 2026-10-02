<?php
require 'config/config.php';

// Check if HR user has an employee record
$stmt = $pdo->query('SELECT employee_id, account_id, firstname, lastname FROM tblemployee WHERE account_id = 2200601');
$emp = $stmt->fetch(PDO::FETCH_ASSOC);

echo "HR Employee Record:\n";
if ($emp) {
    echo "✓ Employee record FOUND:\n";
    var_dump($emp);
} else {
    echo "✗ NO EMPLOYEE RECORD for account_id=2200601\n";
    echo "\nThis is the problem! The HR user doesn't have an employee record.\n";
    echo "When getEmployeeDetail() is called with account_id instead of employee_id, it returns NULL.\n";
}

// Also count how many employees exist
$stmt2 = $pdo->query('SELECT COUNT(*) as count FROM tblemployee');
$count = $stmt2->fetch(PDO::FETCH_ASSOC);
echo "\nTotal employees in database: " . $count['count'] . "\n";
?>
