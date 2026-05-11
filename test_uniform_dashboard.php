<?php
// Test script to debug uniform assignments on dashboard

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/app/Models/hr/UniformModel.php';

try {
    $uniformModel = new UniformModel();
    
    echo "<h2>Testing Uniform Assignments</h2>";
    
    // Test 1: Get employees with uniforms
    echo "<h3>1. Employees with Uniforms:</h3>";
    $employees = $uniformModel->getEmployeesWithUniforms(10);
    echo "<pre>" . print_r($employees, true) . "</pre>";
    
    // Test 2: Total count
    echo "<h3>2. Total Employees with Uniforms:</h3>";
    $total = $uniformModel->getTotalEmployeesWithUniforms();
    echo "<p>Total: " . $total . "</p>";
    
    // Test 3: Check raw database query
    echo "<h3>3. Raw Database Check:</h3>";
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME,
        DB_USER,
        DB_PASS
    );
    
    $sql = "SELECT 
                COUNT(DISTINCT e.employee_id) as total_employees,
                COUNT(ua.assignment_id) as total_assignments,
                COUNT(CASE WHEN ua.date_returned IS NULL THEN 1 END) as active_assignments
            FROM tblemployee e
            LEFT JOIN tbluniform_assignment ua ON e.employee_id = ua.employee_id";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<pre>" . print_r($result, true) . "</pre>";
    
    // Test 4: Show sample assignments
    echo "<h3>4. Sample Active Assignments:</h3>";
    $sql2 = "SELECT 
                e.employee_id,
                e.firstname,
                e.lastname,
                e.position,
                e.department,
                ui.uniform_type,
                ui.size,
                ui.color,
                ua.date_issued,
                ua.date_returned
            FROM tblemployee e
            INNER JOIN tbluniform_assignment ua ON e.employee_id = ua.employee_id
            INNER JOIN tbluniform_inventory ui ON ua.uniform_id = ui.uniform_id
            WHERE ua.date_returned IS NULL
            LIMIT 10";
    
    $stmt2 = $pdo->prepare($sql2);
    $stmt2->execute();
    $samples = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    echo "<pre>" . print_r($samples, true) . "</pre>";
    
} catch (\Throwable $e) {
    echo "<h3 style='color:red;'>Error: " . $e->getMessage() . "</h3>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>
