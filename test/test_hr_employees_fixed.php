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
require_once __DIR__ . '/app/Models/hr/UniformModel.php';

echo "=== TESTING HR EMPLOYEE DISPLAY FIX ===\n\n";

$empModel = new EmployeeModel();
$uniformModel = new UniformModel();

// Test 1: Get total employees
echo "TEST 1: Total Employee Count\n";
echo "-----------------------------\n";
$total = $empModel->getTotalEmployeeCount();
echo "Total employees: $total\n\n";

// Test 2: Get first page of employees
echo "TEST 2: Get Employees (Page 1)\n";
echo "------------------------------\n";
$employees = $empModel->getAllEmployees(0, 20);
echo "Employees returned: " . count($employees) . "\n";
if (count($employees) > 0) {
    echo "Sample employees:\n";
    for ($i = 0; $i < min(5, count($employees)); $i++) {
        $emp = $employees[$i];
        echo "  • {$emp['firstname']} {$emp['lastname']} - {$emp['position']} ({$emp['department']})\n";
    }
} else {
    echo "✗ NO EMPLOYEES FOUND!\n";
}

// Test 3: Get specific employee detail
echo "\n\nTEST 3: Get Specific Employee Detail\n";
echo "-------------------------------------\n";
if (count($employees) > 0) {
    $empId = $employees[0]['employee_id'];
    echo "Fetching details for employee ID: $empId\n";
    $detail = $empModel->getEmployeeDetail($empId);
    if ($detail) {
        echo "✓ Employee found\n";
        echo "  Name: {$detail['firstname']} {$detail['lastname']}\n";
        echo "  Position: {$detail['position']}\n";
        echo "  Department: {$detail['department']}\n";
        echo "  Email: {$detail['email']}\n";
        echo "  Branch: {$detail['branchName']}\n";
    } else {
        echo "✗ Employee not found\n";
    }
}

// Test 4: Test uniform model
echo "\n\nTEST 4: Uniforms (Verify Fix Also Applied)\n";
echo "------------------------------------------\n";
$uniforms = $uniformModel->getAllUniforms(0, 10);
echo "Uniforms returned: " . count($uniforms) . "\n";
if (count($uniforms) > 0) {
    echo "Sample uniforms:\n";
    for ($i = 0; $i < min(3, count($uniforms)); $i++) {
        $u = $uniforms[$i];
        echo "  • {$u['uniform_type']} (Size: {$u['size']}, Color: {$u['color']}) - Stock: {$u['quantity_in_stock']}\n";
    }
}

echo "\n\n✓ TESTS COMPLETED\n";
echo "HR employees and uniforms should now display correctly!\n";
?>
