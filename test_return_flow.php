<?php
session_start();
require_once 'config/config.php';

if (!isset($_SESSION['account_id'])) {
    $_SESSION['account_id'] = 1;
    $_SESSION['usertype'] = 'HR';
}

echo "<h1>Return Button Flow Test</h1>";
echo "<hr>";

// Step 1: Load an employee with uniforms
require_once 'app/Models/hr/HRModel.php';
require_once 'app/Models/hr/EmployeeModel.php';
require_once 'app/Models/hr/UniformModel.php';

$employeeModel = new EmployeeModel();
$uniformModel = new UniformModel();

echo "<h2>Step 1: Find Employee with Current Uniforms</h2>";

// Get the first employee
$employees = $employeeModel->getAllEmployees(0, 1);
if ($employees) {
    $employee = $employees[0];
    $employee_id = $employee['employee_id'];
    
    echo "<p>✓ Found employee: " . htmlspecialchars($employee['firstname'] . ' ' . $employee['lastname']) . " (ID: " . $employee_id . ")</p>";
    
    // Get their current uniforms
    $uniforms = $employeeModel->getEmployeeCurrentUniforms($employee_id);
    echo "<p>✓ Found " . count($uniforms) . " current uniform assignment(s)</p>";
    
    if ($uniforms) {
        foreach ($uniforms as $i => $unif) {
            echo "<div style='border: 1px solid #ddd; padding: 10px; margin: 10px 0; background: #f9f9f9;'>";
            echo "<h3>Uniform " . ($i+1) . "</h3>";
            echo "<p><strong>Assignment ID:</strong> " . $unif['assignment_id'] . "</p>";
            echo "<p><strong>Type:</strong> " . htmlspecialchars($unif['uniform_type']) . "</p>";
            echo "<p><strong>Size:</strong> " . htmlspecialchars($unif['size']) . "</p>";
            echo "<p><strong>Issued Date:</strong> " . htmlspecialchars($unif['date_issued']) . "</p>";
            
            echo "<h4>Step 2: Test getAssignmentById()</h4>";
            $assignment = $uniformModel->getAssignmentById($unif['assignment_id']);
            
            if ($assignment) {
                echo "<p style='color: green;'>✓ Assignment found by ID</p>";
                echo "<pre style='background: #f0f0f0; padding: 10px; overflow-x: auto;'>";
                echo "Assignment ID: " . $assignment['assignment_id'] . "\n";
                echo "Employee Name: " . ($assignment['employee_name'] ?? 'NULL') . "\n";
                echo "Uniform Type: " . ($assignment['uniform_type'] ?? 'NULL') . "\n";
                echo "Size: " . ($assignment['size'] ?? 'NULL') . "\n";
                echo "Date Issued: " . ($assignment['date_issued'] ?? 'NULL') . "\n";
                echo "</pre>";
                
                echo "<h4>Step 3: Render the Return Button</h4>";
                $base = rtrim(BASE_URL, '/');
                $returnUrl = $base . '/hr/uniforms/return_confirm/' . $unif['assignment_id'];
                echo "<p>Return URL: <code>" . htmlspecialchars($returnUrl) . "</code></p>";
                echo "<p><a href='" . htmlspecialchars($returnUrl) . "' class='btn btn-primary' target='_blank'>Click to Test Return Button</a></p>";
                
                echo "<h4>Step 4: Test Direct Navigation</h4>";
                echo "<p>Or navigate directly to: <code>" . htmlspecialchars($returnUrl) . "</code></p>";
                
            } else {
                echo "<p style='color: red;'>✗ ERROR: Assignment NOT found by ID!</p>";
                echo "<p>This is the problem - the getAssignmentById() method returned null</p>";
            }
            
            echo "</div>";
        }
    } else {
        echo "<p style='color: orange;'>⚠ WARNING: Employee has no current uniform assignments</p>";
        echo "<p>You need to assign uniforms to an employee first before testing returns</p>";
    }
} else {
    echo "<p style='color: red;'>✗ ERROR: No employees found in the system</p>";
}

echo "<hr>";
echo "<h2>Recent Error Log</h2>";
echo "<pre style='background: #f0f0f0; padding: 10px; max-height: 400px; overflow-y: auto; font-size: 0.9em;'>";
$error_log_file = __DIR__ . '/error_log';
if (file_exists($error_log_file)) {
    $lines = file($error_log_file);
    $recent = array_slice($lines, -20);
    foreach ($recent as $line) {
        echo htmlspecialchars($line);
    }
} else {
    echo "Error log not found at " . htmlspecialchars($error_log_file);
}
echo "</pre>";

?>
