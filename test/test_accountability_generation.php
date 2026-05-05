<?php
session_start();
define('BASE_URL', '');

// Load autoloader first
require_once __DIR__ . '/public/assets/vendor/autoload.php';

use PhpOffice\PhpWord\TemplateProcessor;

// Initialize global PDO
global $pdo;
$pdo = new PDO('mysql:host=localhost;dbname=howard_tms;charset=utf8mb4', 'root', '');

// Set up HR session
$_SESSION['account_id'] = 2200601;
$_SESSION['username'] = 'hr_test';
$_SESSION['usertype'] = 'HR';

require_once __DIR__ . '/app/Models/hr/EmployeeModel.php';
require_once __DIR__ . '/app/Services/PdfGeneratorService.php';

echo "=== TESTING ACCOUNTABILITY FORM GENERATION ===\n\n";

$empModel = new EmployeeModel();
$pdfService = new PdfGeneratorService();

// Get first employee
$employees = $empModel->getAllEmployees(0, 1);
if (count($employees) > 0) {
    $empId = $employees[0]['employee_id'];
    
    // Get full employee detail
    $employeeDetail = $empModel->getEmployeeDetail($empId);
    
    // Get assets
    $assets = $empModel->getEmployeeAssets($empId);
    
    // Get uniforms
    $uniforms = $empModel->getEmployeeCurrentUniforms($empId);
    
    echo "Employee: {$employeeDetail['firstname']} {$employeeDetail['lastname']}\n";
    echo "Assets: " . count($assets) . " items\n";
    echo "Uniforms: " . count($uniforms) . " items\n\n";
    
    // Try to generate the document
    try {
        // Note: We can't actually call generateAccountabilityForm because it exits after output
        // But we can create a temporary file and then clean it up
        
        echo "Testing accountability form generation...\n\n";
        
        $projectRoot = __DIR__;
        $templatePath = $projectRoot . '/public/assets/generatePDF/template_accountability.docx';
        $outputDir = $projectRoot . '/public/assets/tickets/pdfs';
        
        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }
        
        // Load and test template
        $template = new TemplateProcessor($templatePath);
        
        $fullname = trim($employeeDetail['firstname'] . ' ' . ($employeeDetail['middlename'] ? $employeeDetail['middlename'] . ' ' : '') . $employeeDetail['lastname']);
        
        // Fill in employee information
        $template->setValue('name', htmlspecialchars($fullname));
        $template->setValue('employee_id', htmlspecialchars($employeeDetail['employee_id']));
        $template->setValue('department', htmlspecialchars($employeeDetail['department'] ?? 'N/A'));
        $template->setValue('position', htmlspecialchars($employeeDetail['position'] ?? 'N/A'));
        $template->setValue('date_issued', date('F d, Y'));
        
        echo "✓ Template loaded\n";
        echo "✓ Employee fields populated\n";
        
        // Handle assets
        if (!empty($assets)) {
            try {
                $template->cloneRow('itemInfo', count($assets));
                $i = 1;
                foreach ($assets as $asset) {
                    $template->setValue("itemInfo#{$i}", htmlspecialchars($asset['itemInfo'] ?? 'N/A'));
                    $template->setValue("assetCode#{$i}", htmlspecialchars($asset['assetCode'] ?? 'N/A'));
                    $template->setValue("assetNumber#{$i}", htmlspecialchars($asset['assetNumber'] ?? 'N/A'));
                    $template->setValue("serialNumber#{$i}", htmlspecialchars($asset['serialNumber'] ?? 'N/A'));
                    $i++;
                }
                echo "✓ Asset rows cloned and populated (" . count($assets) . " items)\n";
            } catch (\Exception $e) {
                echo "⚠ Asset row cloning: " . $e->getMessage() . "\n";
            }
        } else {
            $template->setValue('itemInfo', 'No assets assigned');
            echo "✓ No assets - placeholder text set\n";
        }
        
        // Handle uniforms
        if (!empty($uniforms)) {
            try {
                $template->cloneRow('uniform_type', count($uniforms));
                $j = 1;
                foreach ($uniforms as $uniform) {
                    $template->setValue("uniform_type#{$j}", htmlspecialchars($uniform['uniform_type'] ?? 'N/A'));
                    $template->setValue("size#{$j}", htmlspecialchars($uniform['size'] ?? 'N/A'));
                    $template->setValue("color#{$j}", htmlspecialchars($uniform['color'] ?? 'N/A'));
                    $template->setValue("quantity_issued#{$j}", htmlspecialchars($uniform['quantity_issued'] ?? '0'));
                    $j++;
                }
                echo "✓ Uniform rows cloned and populated (" . count($uniforms) . " items)\n";
            } catch (\Exception $e) {
                echo "⚠ Uniform row cloning: " . $e->getMessage() . "\n";
            }
        } else {
            $template->setValue('uniform_type', 'No uniforms assigned');
            echo "✓ No uniforms - placeholder text set\n";
        }
        
        // Generate and save
        $filename = 'accountability_form_' . $employeeDetail['employee_id'] . '_' . date('YmdHis') . '.docx';
        $filepath = $outputDir . '/' . $filename;
        
        $template->saveAs($filepath);
        
        if (file_exists($filepath)) {
            $filesize = filesize($filepath);
            echo "\n✓✓✓ DOCUMENT GENERATED SUCCESSFULLY ✓✓✓\n";
            echo "Filename: $filename\n";
            echo "Size: " . number_format($filesize) . " bytes\n";
            echo "Path: $filepath\n";
            
            // Clean up test file
            unlink($filepath);
            echo "\n✓ Test file cleaned up\n";
        } else {
            echo "\n✗ Failed to generate document\n";
        }
        
    } catch (\Exception $e) {
        echo "✗ Error: " . $e->getMessage() . "\n";
        echo $e->getTraceAsString() . "\n";
    }
} else {
    echo "No employees found\n";
}
?>
