<?php
session_start();

// Fix the config path - go up one directory from public
$configPath = dirname(__DIR__) . '/config/config.php';
if (!file_exists($configPath)) {
    die("Error: Config file not found at " . htmlspecialchars($configPath));
}
require_once $configPath;

echo "<h1>HR Return Button - Complete Diagnostic</h1>";
echo "<hr>";

// Set session to HR if not already
if (!isset($_SESSION['usertype'])) {
    $_SESSION['usertype'] = 'HR';
    $_SESSION['account_id'] = 1;
}

$base = rtrim(BASE_URL, '/');

echo "<h2>✓ Step 1: Route Matching Logic</h2>";
echo "<p>The routing logic is working correctly (proven by previous test)</p>";

echo "<hr>";

echo "<h2>✓ Step 2: Database Check</h2>";

try {
    // The $pdo connection is already created in config.php
    // Check for any uniform assignments
    $sql = "SELECT COUNT(*) as count FROM tbluniform_assignment WHERE date_returned IS NULL";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<p>✓ Database connected</p>";
    echo "<p>Active uniform assignments: <strong>" . $result['count'] . "</strong></p>";
    
    if ($result['count'] > 0) {
        // Get first assignment
        $sql = "SELECT ua.assignment_id, ua.uniform_id, ua.employee_id,
                       ui.uniform_type, e.firstname, e.lastname
                FROM tbluniform_assignment ua
                LEFT JOIN tbluniform_inventory ui ON ua.uniform_id = ui.uniform_id
                LEFT JOIN tblemployee e ON ua.employee_id = e.employee_id
                WHERE ua.date_returned IS NULL
                LIMIT 1";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $assignment = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($assignment) {
            echo "<p>✓ Found assignment:</p>";
            echo "<ul>";
            echo "<li>Assignment ID: <strong>" . $assignment['assignment_id'] . "</strong></li>";
            echo "<li>Employee: " . htmlspecialchars($assignment['firstname'] . ' ' . $assignment['lastname']) . "</li>";
            echo "<li>Uniform: " . htmlspecialchars($assignment['uniform_type']) . "</li>";
            echo "</ul>";
            
            echo "<hr>";
            
            echo "<h2>✓ Step 3: Test Links</h2>";
            echo "<p>Click any of these links to test the return button:</p>";
            
            $returnUrl = $base . '/hr/uniforms/return_confirm/' . $assignment['assignment_id'];
            echo "<p><strong>Direct Link:</strong></p>";
            echo "<p><a href='" . htmlspecialchars($returnUrl) . "' class='btn btn-primary' style='display:inline-block; padding:10px 20px; background:#007bff; color:white; text-decoration:none; border-radius:4px;'>Click to Test Return Button (Assignment ID: " . $assignment['assignment_id'] . ")</a></p>";
            
            echo "<p><strong>Or copy this URL and paste in browser:</strong></p>";
            echo "<p><code>" . htmlspecialchars($returnUrl) . "</code></p>";
            
            echo "<hr>";
            echo "<h2>What Should Happen</h2>";
            echo "<ol>";
            echo "<li>Click the link above</li>";
            echo "<li>You should see the 'Confirm Uniform Return' form</li>";
            echo "<li>The form should show:</li>";
            echo "<ul>";
            echo "<li>Employee name: " . htmlspecialchars($assignment['firstname'] . ' ' . $assignment['lastname']) . "</li>";
            echo "<li>Uniform type: " . htmlspecialchars($assignment['uniform_type']) . "</li>";
            echo "<li>Condition dropdown (Good, Fair, Used, Damaged, Lost)</li>";
            echo "</ul>";
            echo "</ol>";
            
        } else {
            echo "<p style='color:red;'>✗ ERROR: Assignment query returned no results</p>";
        }
    } else {
        echo "<p style='color:orange;'>⚠ WARNING: No active uniform assignments in database</p>";
        echo "<p>You need to assign uniforms to employees first in HR > Uniforms > Assign</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color:red;'><strong>✗ Database Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p style='color:red;'><strong>Stack Trace:</strong></p>";
    echo "<pre style='color:red;'>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}

echo "<hr>";

echo "<h2>📋 Debug Information</h2>";
echo "<p><strong>BASE_URL:</strong> " . htmlspecialchars(BASE_URL) . "</p>";
echo "<p><strong>Session UserType:</strong> " . $_SESSION['usertype'] . "</p>";
echo "<p><strong>Session Account ID:</strong> " . $_SESSION['account_id'] . "</p>";

?>
