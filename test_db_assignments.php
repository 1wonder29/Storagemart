<?php
session_start();
require_once 'config/config.php';

// This script tests directly querying the database for assignment data

echo "<h1>Direct Database Test</h1>";

try {
    // Connect to database
    $host = DB_HOST;
    $db = DB_NAME;
    $user = DB_USER;
    $pass = DB_PASS;
    
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_THROW);
    
    echo "<h2>Testing database connection</h2>";
    echo "<p>✓ Connected to database successfully</p>";
    
    // Test 1: Check if tbluniform_assignment table exists and has data
    echo "<h2>Checking tbluniform_assignment table</h2>";
    
    $sql = "SELECT COUNT(*) as count FROM tbluniform_assignment WHERE date_returned IS NULL";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<p>Active assignments (not returned): " . $result['count'] . "</p>";
    
    if ($result['count'] > 0) {
        // Get the first assignment
        $sql = "SELECT ua.assignment_id, ua.uniform_id, ua.employee_id, ua.date_issued, ua.quantity_issued,
                       ui.uniform_type, ui.size, ui.color,
                       CONCAT(e.firstname, ' ', e.lastname) as employee_name
                FROM tbluniform_assignment ua
                LEFT JOIN tbluniform_inventory ui ON ua.uniform_id = ui.uniform_id
                LEFT JOIN tblemployee e ON ua.employee_id = e.employee_id
                WHERE ua.date_returned IS NULL
                LIMIT 1";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $assignment = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo "<h3>First Active Assignment:</h3>";
        echo "<pre>";
        print_r($assignment);
        echo "</pre>";
        
        if ($assignment) {
            $assignment_id = $assignment['assignment_id'];
            echo "<p><strong>Test Link:</strong> <a href='/hr/uniforms/return_confirm/" . $assignment_id . "' target='_blank'>/hr/uniforms/return_confirm/" . $assignment_id . "</a></p>";
        }
    }
    
    // Test 2: Check if we can find a specific assignment
    echo "<h2>Testing getAssignmentById logic</h2>";
    $test_id = 1;
    
    $sql = "SELECT ua.*, ui.uniform_type, ui.size, ui.color, ui.uniform_id, ua.quantity_issued,
                   CONCAT(e.firstname, ' ', e.lastname) as employee_name
            FROM tbluniform_assignment ua
            LEFT JOIN tbluniform_inventory ui ON ua.uniform_id = ui.uniform_id
            LEFT JOIN tblemployee e ON ua.employee_id = e.employee_id
            WHERE ua.assignment_id = ? LIMIT 1";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$test_id]);
    $assignment = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<p>Testing assignment_id = " . $test_id . "</p>";
    if ($assignment) {
        echo "<p style='color: green;'>✓ Found assignment</p>";
        echo "<pre>";
        print_r($assignment);
        echo "</pre>";
    } else {
        echo "<p style='color: red;'>✗ Assignment not found</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'><strong>ERROR:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
