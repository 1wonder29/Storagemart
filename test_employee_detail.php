<?php
session_start();
define('BASE_URL', '');

// Get first employee ID from database
$pdo = new PDO('mysql:host=localhost;dbname=howard_tms;charset=utf8mb4', 'root', '');
$stmt = $pdo->prepare("SELECT employee_id, firstname, lastname FROM tblemployee LIMIT 1");
$stmt->execute();
$employee = $stmt->fetch(PDO::FETCH_ASSOC);

if ($employee) {
    echo "=== TESTING EMPLOYEE DETAIL QUERY ===\n\n";
    echo "First employee in database:\n";
    echo "- employee_id: {$employee['employee_id']}\n";
    echo "- firstname: {$employee['firstname']}\n";
    echo "- lastname: {$employee['lastname']}\n\n";

    // Now test the EmployeeModel
    require_once __DIR__ . '/app/Models/hr/HRModel.php';
    require_once __DIR__ . '/app/Models/hr/EmployeeModel.php';

    $empModel = new EmployeeModel();
    $detail = $empModel->getEmployeeDetail($employee['employee_id']);
    
    echo "Query result from EmployeeModel::getEmployeeDetail():\n";
    if ($detail) {
        echo "✓ SUCCESS - Employee found\n";
        echo "Details:\n";
        print_r($detail);
    } else {
        echo "✗ FAILED - Employee not found\n";
    }

    // Test getAllEmployees
    echo "\n\nTesting getAllEmployees:\n";
    $all = $empModel->getAllEmployees(0, 5);
    echo "Count: " . count($all) . "\n";
    if (count($all) > 0) {
        echo "First employee:\n";
        print_r($all[0]);
    }
} else {
    echo "No employees in database!\n";
}
?>
