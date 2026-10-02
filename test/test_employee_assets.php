<?php
session_start();
define('BASE_URL', '');

// Initialize global PDO
global $pdo;
$pdo = new PDO('mysql:host=localhost;dbname=howard_tms;charset=utf8mb4', 'root', '');

// Set up HR session
$_SESSION['account_id'] = 2200601;
$_SESSION['username'] = 'hr_test';
$_SESSION['usertype'] = 'HR';

require_once __DIR__ . '/app/Models/hr/EmployeeModel.php';

echo "=== TESTING EMPLOYEE ASSETS QUERY ===\n\n";

$empModel = new EmployeeModel();

// Get first employee
$employees = $empModel->getAllEmployees(0, 1);
if (count($employees) > 0) {
    $empId = $employees[0]['employee_id'];
    echo "Testing with employee ID: $empId\n";
    echo "Employee: {$employees[0]['firstname']} {$employees[0]['lastname']}\n\n";
    
    // Get assets
    echo "Fetching assets...\n";
    $assets = $empModel->getEmployeeAssets($empId);
    echo "Assets found: " . count($assets) . "\n";
    
    if (count($assets) > 0) {
        echo "\nAsset details:\n";
        foreach ($assets as $asset) {
            echo "  • {$asset['itemInfo']} ({$asset['assetCode']})\n";
            echo "    Category: {$asset['categoryName']}\n";
            echo "    Group: {$asset['groupName']}\n";
            echo "    Status: {$asset['asset_status']}\n";
        }
    } else {
        echo "No assets assigned to this employee\n";
    }
    
    echo "\n✓ Query executed without errors!\n";
} else {
    echo "No employees found\n";
}
?>
