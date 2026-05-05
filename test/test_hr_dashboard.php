<?php
session_start();
require 'config/config.php';

// Simulate HR login
$_SESSION['account_id'] = 2200601;
$_SESSION['username'] = 'hr_test';
$_SESSION['usertype'] = 'HR';

echo "=== Testing HR Dashboard Access ===\n\n";

echo "Session data:\n";
var_dump($_SESSION);

// Test 1: Check if file paths exist
$files_to_check = [
    'app/Controllers/hr/HrController.php',
    'app/Models/hr/HRModel.php',
    'app/Models/hr/EmployeeModel.php',
    'app/Models/hr/UniformModel.php',
    'app/Views/hr/dashboard.php',
];

echo "\n\n=== Checking file paths ===\n";
foreach ($files_to_check as $file) {
    $exists = file_exists($file);
    echo "$file: " . ($exists ? '✓ EXISTS' : '✗ MISSING') . "\n";
}

// Test 2: Try to instantiate HR controller
echo "\n\n=== Testing HrController instantiation ===\n";
try {
    require_once 'app/Controllers/hr/HrController.php';
    $hr = new HrController();
    echo "✓ HrController instantiated successfully\n";
    
    // Check if methods exist
    $methods = ['requireHR', 'dashboard', 'employees'];
    foreach ($methods as $method) {
        $hasMethod = method_exists($hr, $method);
        echo "  - $method: " . ($hasMethod ? '✓ EXISTS' : '✗ MISSING') . "\n";
    }
} catch (\Throwable $e) {
    echo "✗ Error instantiating HrController:\n";
    echo "  " . $e->getMessage() . "\n";
    echo "  File: " . $e->getFile() . "\n";
    echo "  Line: " . $e->getLine() . "\n";
}

// Test 3: Check models
echo "\n\n=== Testing HR Models ===\n";
try {
    require_once 'app/Models/hr/EmployeeModel.php';
    $empModel = new EmployeeModel();
    $count = $empModel->getTotalEmployeeCount();
    echo "✓ EmployeeModel works - Total employees: $count\n";
} catch (\Throwable $e) {
    echo "✗ Error with EmployeeModel:\n";
    echo "  " . $e->getMessage() . "\n";
}

echo "\n\n=== HR Dashboard Access Test Complete ===\n";
?>
