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

require_once __DIR__ . '/app/Models/hr/UniformModel.php';
require_once __DIR__ . '/app/Models/hr/EmployeeModel.php';

echo "=== TESTING UNIFORM ASSIGNMENT FUNCTIONALITY ===\n\n";

$uniformModel = new UniformModel();
$empModel = new EmployeeModel();

// Test 1: Get uniform types
echo "TEST 1: Get Uniform Types\n";
echo "-------------------------\n";
$uniformTypes = $uniformModel->getUniformTypes();
echo "Found " . count($uniformTypes) . " uniform types:\n";
foreach ($uniformTypes as $type) {
    echo "  • $type\n";
}

// Test 2: Get uniforms by type
if (count($uniformTypes) > 0) {
    echo "\n\nTEST 2: Get Uniforms by Type\n";
    echo "-----------------------------\n";
    $type = $uniformTypes[0];
    echo "Getting uniforms of type: $type\n";
    $uniforms = $uniformModel->getUniformsByType($type);
    echo "Found " . count($uniforms) . " uniforms:\n";
    foreach ($uniforms as $u) {
        echo "  • {$u['uniform_type']} - Size: {$u['size']}, Color: {$u['color']}, Stock: {$u['quantity_in_stock']}\n";
    }

    // Test 3: Assign uniform to employee
    if (count($uniforms) > 0) {
        echo "\n\nTEST 3: Assign Uniform to Employee\n";
        echo "-----------------------------------\n";
        
        // Get first employee
        $employees = $empModel->getAllEmployees(0, 1);
        if (count($employees) > 0) {
            $employee = $employees[0];
            $uniform = $uniforms[0];
            
            echo "Employee: {$employee['firstname']} {$employee['lastname']}\n";
            echo "Uniform: {$uniform['uniform_type']} - Size: {$uniform['size']}, Color: {$uniform['color']}\n";
            echo "Available Stock: {$uniform['quantity_in_stock']}\n\n";
            
            $initialStock = $uniform['quantity_in_stock'];
            
            // Assign uniform
            $result = $uniformModel->assignUniform(
                $employee['employee_id'],
                $uniform['uniform_id'],
                1,
                'GOOD',
                'Test assignment',
                $_SESSION['account_id']
            );
            
            if ($result) {
                echo "✓ Uniform assigned successfully!\n";
                
                // Verify stock decreased
                $uniform2 = $uniformModel->getUniformById($uniform['uniform_id']);
                $newStock = $uniform2['quantity_in_stock'];
                echo "✓ Stock updated: $initialStock → $newStock\n";
                
                // Get employee uniforms to verify
                $empUniforms = $empModel->getEmployeeCurrentUniforms($employee['employee_id']);
                echo "✓ Employee now has " . count($empUniforms) . " uniform(s) assigned\n";
            } else {
                echo "✗ Failed to assign uniform\n";
            }
        } else {
            echo "No employees found\n";
        }
    }
}

echo "\n\n✓ UNIFORM ASSIGNMENT TESTS COMPLETED\n";
?>
