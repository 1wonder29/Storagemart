<?php
// Test script to debug the return button
session_start();
$_SESSION['account_id'] = 1;
$_SESSION['usertype'] = 'HR';

require_once 'config/config.php';

echo "<h2>Testing Return Button Route</h2>";

// Test URL generation
$base = rtrim(BASE_URL, '/');
$assignment_id = 1;

echo "<p><strong>Base URL:</strong> " . htmlspecialchars($base) . "</p>";
echo "<p><strong>Assignment ID:</strong> " . $assignment_id . "</p>";
echo "<p><strong>Expected URL:</strong> " . htmlspecialchars($base) . "/hr/uniforms/return_confirm/" . $assignment_id . "</p>";

// Test model
require_once 'app/Models/hr/HRModel.php';
require_once 'app/Models/hr/UniformModel.php';

try {
    $uniform = new UniformModel();
    $assignment = $uniform->getAssignmentById($assignment_id);
    
    echo "<h3>Assignment Data:</h3>";
    if ($assignment) {
        echo "<pre>";
        print_r($assignment);
        echo "</pre>";
    } else {
        echo "<p style='color: red;'><strong>ERROR: Assignment not found!</strong></p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'><strong>ERROR:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    error_log("Test return button error: " . $e->getMessage());
}
?>
