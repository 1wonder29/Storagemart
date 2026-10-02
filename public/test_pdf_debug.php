<?php

// Test script to debug PDF generation
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/Models/hr/EmployeeModel.php';
require_once __DIR__ . '/../app/Models/hr/UniformModel.php';
require_once __DIR__ . '/../app/Services/PdfGeneratorService.php';

try {
    // Get test employee - try to get first one
    $employeeModel = new EmployeeModel();
    
    // List first few employees
    $employees = $employeeModel->getAllEmployees(0, 5);
    echo "<h2>Available Employees</h2>";
    echo "<ul>";
    foreach ($employees as $emp) {
        echo "<li>ID: " . $emp['employee_id'] . " - " . $emp['firstname'] . " " . $emp['lastname'] . "</li>";
    }
    echo "</ul>";
    
    // Use first employee
    if (empty($employees)) {
        die("No employees found in database");
    }
    
    $employee = $employees[0];
    $employeeId = $employee['employee_id'];
    
    echo "<h2>Testing PDF Generation</h2>";
    echo "<p>Selected Employee: " . $employee['firstname'] . " " . $employee['lastname'] . " (ID: " . $employeeId . ")</p>";
    
    // Get assets and uniforms
    $assets = $employeeModel->getEmployeeAssets($employeeId);
    $uniforms = $employeeModel->getEmployeeCurrentUniforms($employeeId);
    
    echo "<p>Assets: " . count($assets) . "</p>";
    echo "<p>Uniforms: " . count($uniforms) . "</p>";
    
    // Try to generate PDF
    echo "<h3>Attempting PDF Generation...</h3>";
    $pdfService = new PdfGeneratorService();
    
    $pdfService->generateAccountabilityForm($employee, $assets, $uniforms);
    
    echo "<p>If you see this, PDF was not sent (maybe it worked and you downloaded it)</p>";
    
} catch (\Throwable $e) {
    echo "<h3>Error</h3>";
    echo "<pre>" . htmlspecialchars($e->getMessage()) . "\n\n" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}

?>
