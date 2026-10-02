<?php

// Test script to debug PDF generation
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/app/Models/hr/EmployeeModel.php';
require_once __DIR__ . '/app/Models/hr/UniformModel.php';
require_once __DIR__ . '/app/Services/PdfGeneratorService.php';

try {
    // Get test employee
    $employeeModel = new EmployeeModel();
    $employee = $employeeModel->getEmployeeDetail(55);
    
    if (!$employee) {
        die("Employee not found");
    }
    
    echo "<h2>Testing PDF Generation</h2>";
    echo "<p>Employee: " . $employee['firstname'] . " " . $employee['lastname'] . "</p>";
    
    // Get assets and uniforms
    $assets = $employeeModel->getEmployeeAssets(55);
    $uniforms = $employeeModel->getEmployeeCurrentUniforms(55);
    
    echo "<p>Assets: " . count($assets) . "</p>";
    echo "<p>Uniforms: " . count($uniforms) . "</p>";
    
    // Try to generate PDF
    echo "<h3>Attempting PDF Generation...</h3>";
    $pdfService = new PdfGeneratorService();
    
    ob_start();
    $pdfService->generateAccountabilityForm($employee, $assets, $uniforms);
    $output = ob_get_clean();
    
    echo "<p>If you see this, PDF was not sent (redirected instead)</p>";
    
} catch (\Throwable $e) {
    echo "<h3>Error</h3>";
    echo "<pre>" . htmlspecialchars($e->getMessage()) . "\n" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}

?>
