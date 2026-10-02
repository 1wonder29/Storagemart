<?php
session_start();
require_once 'config/config.php';

echo "<h1>HR Return Button Diagnostic</h1>";
echo "<hr>";

$base = rtrim(BASE_URL, '/');

echo "<h2>System Information</h2>";
echo "<p><strong>BASE_URL:</strong> " . htmlspecialchars(BASE_URL) . "</p>";
echo "<p><strong>Trimmed base:</strong> " . htmlspecialchars($base) . "</p>";
echo "<p><strong>Session User Type:</strong> " . ($_SESSION['usertype'] ?? 'NOT SET') . "</p>";
echo "<p><strong>Session Account ID:</strong> " . ($_SESSION['account_id'] ?? 'NOT SET') . "</p>";

echo "<hr>";

// Create a test URL
$test_assignment_id = 1;
$test_url = $base . '/hr/uniforms/return_confirm/' . $test_assignment_id;

echo "<h2>Test URLs</h2>";
echo "<p><strong>Full URL:</strong> " . htmlspecialchars($test_url) . "</p>";
echo "<p><strong>Escaped URL:</strong> " . htmlspecialchars(htmlspecialchars($test_url)) . "</p>";

echo "<h3>Test Links</h3>";
echo "<p><a href='" . htmlspecialchars($test_url) . "' class='btn btn-primary'>Test Return Link (Assignment ID 1)</a></p>";

echo "<hr>";

// Check database for uniforms
require_once 'app/Models/hr/HRModel.php';
require_once 'app/Models/hr/EmployeeModel.php';

try {
    $empModel = new EmployeeModel();
    
    // Get first employee with uniforms
    $sql = "SELECT e.employee_id, e.firstname, e.lastname,
                   COUNT(ua.assignment_id) as uniform_count
            FROM tblemployee e
            LEFT JOIN tbluniform_assignment ua ON e.employee_id = ua.employee_id AND ua.date_returned IS NULL
            GROUP BY e.employee_id
            HAVING uniform_count > 0
            LIMIT 1";
    
    // We need PDO for this
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $employee = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($employee) {
        echo "<h2>Found Employee with Uniforms</h2>";
        echo "<p><strong>Employee:</strong> " . htmlspecialchars($employee['firstname'] . ' ' . $employee['lastname']) . " (ID: " . $employee['employee_id'] . ")</p>";
        echo "<p><strong>Current Uniforms:</strong> " . $employee['uniform_count'] . "</p>";
        
        // Get their uniforms
        $uniforms = $empModel->getEmployeeCurrentUniforms($employee['employee_id']);
        
        if ($uniforms) {
            echo "<p>✓ Loaded " . count($uniforms) . " uniforms from database</p>";
            
            foreach ($uniforms as $i => $unif) {
                echo "<div style='border: 1px solid #ddd; padding: 10px; margin: 10px 0;'>";
                echo "<strong>Uniform " . ($i+1) . "</strong><br>";
                echo "Assignment ID: " . $unif['assignment_id'] . "<br>";
                echo "Type: " . htmlspecialchars($unif['uniform_type']) . "<br>";
                echo "Size: " . htmlspecialchars($unif['size']) . "<br>";
                
                $uniform_url = $base . '/hr/uniforms/return_confirm/' . $unif['assignment_id'];
                echo "<p><a href='" . htmlspecialchars($uniform_url) . "' class='btn btn-sm btn-primary'>Test Return Button</a></p>";
                echo "</div>";
            }
        }
    } else {
        echo "<p>No employees with current uniforms found</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'><strong>ERROR:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
