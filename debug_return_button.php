<?php
session_start();
require_once 'config/config.php';

echo "<h1>Debugging Return Button Issue</h1>";

// Get the employee ID from URL parameter
$employee_id = $_GET['emp_id'] ?? 1;

echo "<h2>Testing for Employee ID: " . (int)$employee_id . "</h2>";

require_once 'app/Models/hr/HRModel.php';
require_once 'app/Models/hr/EmployeeModel.php';
require_once 'app/Models/hr/UniformModel.php';

try {
    // Test 1: Get employee
    $employeeModel = new EmployeeModel();
    $employee = $employeeModel->getEmployeeDetail($employee_id);
    
    echo "<h3>1. Employee Details:</h3>";
    if ($employee) {
        echo "<p>✓ Employee found: " . htmlspecialchars($employee['firstname'] . ' ' . $employee['lastname']) . "</p>";
    } else {
        echo "<p style='color: red;'>✗ Employee not found</p>";
    }
    
    // Test 2: Get current uniforms
    echo "<h3>2. Current Uniforms (date_returned IS NULL):</h3>";
    $uniforms = $employeeModel->getEmployeeCurrentUniforms($employee_id);
    echo "<p>Count: " . count($uniforms) . "</p>";
    
    if ($uniforms) {
        foreach ($uniforms as $unif) {
            echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 10px 0;'>";
            echo "<p><strong>Assignment ID:</strong> " . $unif['assignment_id'] . "</p>";
            echo "<p><strong>Uniform Type:</strong> " . htmlspecialchars($unif['uniform_type']) . "</p>";
            echo "<p><strong>Size:</strong> " . htmlspecialchars($unif['size']) . "</p>";
            echo "<p><strong>Test Link:</strong> <a href='/hr/uniforms/return_confirm/" . $unif['assignment_id'] . "' target='_blank'>Click to test</a></p>";
            
            // Test 3: Check if assignment can be retrieved
            echo "<h4>Testing getAssignmentById(" . $unif['assignment_id'] . "):</h4>";
            $uniformModel = new UniformModel();
            $assignment = $uniformModel->getAssignmentById($unif['assignment_id']);
            
            if ($assignment) {
                echo "<p style='color: green;'>✓ Assignment found</p>";
                echo "<pre style='background: #f0f0f0; padding: 10px;'>";
                print_r($assignment);
                echo "</pre>";
            } else {
                echo "<p style='color: red;'>✗ Assignment NOT found (This is the problem!)</p>";
            }
            echo "</div>";
        }
    } else {
        echo "<p style='color: red;'>No current uniforms found for this employee</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'><strong>ERROR:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    error_log("Debug script error: " . $e->getMessage());
}

// Show last 10 lines of error log
echo "<h3>Recent Error Log (last 10 lines):</h3>";
echo "<pre style='background: #f0f0f0; padding: 10px; max-height: 300px; overflow-y: auto;'>";
$error_log_path = dirname(__FILE__) . '/error_log';
if (file_exists($error_log_path)) {
    $lines = file($error_log_path);
    $last_lines = array_slice($lines, -10);
    echo htmlspecialchars(implode('', $last_lines));
} else {
    echo "No error log found at " . htmlspecialchars($error_log_path);
}
echo "</pre>";
?>
