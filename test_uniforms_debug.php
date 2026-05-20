<?php
// Debug script to check uniforms for an employee
session_start();
require_once 'config/config.php';

// Test with employee_id = 1
$employee_id = 1;

require_once 'app/Models/hr/HRModel.php';
require_once 'app/Models/hr/EmployeeModel.php';

try {
    $employeeModel = new EmployeeModel();
    $uniforms = $employeeModel->getEmployeeCurrentUniforms($employee_id);
    
    echo "<h2>Current Uniforms for Employee ID: " . $employee_id . "</h2>";
    echo "<p>Count: " . count($uniforms) . "</p>";
    
    if ($uniforms) {
        echo "<pre>";
        print_r($uniforms);
        echo "</pre>";
    } else {
        echo "<p><strong>No uniforms found</strong></p>";
    }
    
    // Also test the assignment query
    if (!empty($uniforms)) {
        $assignment_id = $uniforms[0]['assignment_id'] ?? null;
        if ($assignment_id) {
            echo "<h2>Testing getAssignmentById(" . $assignment_id . ")</h2>";
            require_once 'app/Models/hr/UniformModel.php';
            $uniformModel = new UniformModel();
            $assignment = $uniformModel->getAssignmentById($assignment_id);
            
            if ($assignment) {
                echo "<pre>";
                print_r($assignment);
                echo "</pre>";
            } else {
                echo "<p style='color: red;'><strong>Assignment not found!</strong></p>";
            }
        }
    }
} catch (Exception $e) {
    echo "<p style='color: red;'><strong>ERROR:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    error_log("Debug script error: " . $e->getMessage());
}
?>
