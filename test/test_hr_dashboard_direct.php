<?php
// Test HR Dashboard direct access

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Load configuration
require 'config/config.php';
require 'app/Helpers/Session.php';

echo "=== TESTING HR DASHBOARD DIRECT ACCESS ===\n\n";

// Set session as if user just logged in
$_SESSION['account_id'] = 2200601;
$_SESSION['username'] = 'hr_test';
$_SESSION['usertype'] = 'HR';

echo "Session data set:\n";
echo "- account_id: {$_SESSION['account_id']}\n";
echo "- username: {$_SESSION['username']}\n";
echo "- usertype: {$_SESSION['usertype']}\n\n";

// Test 1: Check database connectivity
echo "TEST 1: Database Connectivity\n";
echo "-----------------------------\n";
try {
    $stmt = $pdo->query('SELECT COUNT(*) FROM tblemployee');
    $count = $stmt->fetchColumn();
    echo "✓ Database connected - Employee count: $count\n\n";
} catch (\Throwable $e) {
    echo "✗ Database error: " . $e->getMessage() . "\n\n";
    exit;
}

// Test 2: Load and call HR controller
echo "TEST 2: Loading HR Controller\n";
echo "-----------------------------\n";
try {
    require_once 'app/Controllers/hr/HrController.php';
    echo "✓ HrController loaded\n";
    
    // Create instance
    $hrController = new HrController();
    echo "✓ HrController instantiated\n\n";
    
    // Test 3: Call dashboard method - this will require output buffering
    echo "TEST 3: Calling Dashboard Method\n";
    echo "--------------------------------\n";
    
    // Buffer output to capture any errors
    ob_start();
    
    try {
        $hrController->dashboard();
        $output = ob_get_clean();
        
        // Check if output contains expected dashboard content
        if (strpos($output, 'HR Dashboard') !== false || strpos($output, 'dashboard') !== false) {
            echo "✓ Dashboard method executed\n";
            echo "✓ Output contains dashboard content\n";
            echo "\n✅ SUCCESS: HR Dashboard should be loading correctly\n";
        } else {
            echo "⚠ Dashboard method executed but output seems incomplete\n";
            echo "Output length: " . strlen($output) . " bytes\n";
            echo "First 200 chars: " . substr($output, 0, 200) . "\n";
        }
    } catch (\Throwable $e) {
        ob_end_clean();
        echo "✗ Error calling dashboard method:\n";
        echo "  Message: " . $e->getMessage() . "\n";
        echo "  File: " . $e->getFile() . "\n";
        echo "  Line: " . $e->getLine() . "\n";
    }
    
} catch (\Throwable $e) {
    echo "✗ Error loading HrController:\n";
    echo "  Message: " . $e->getMessage() . "\n";
    echo "  File: " . $e->getFile() . "\n";
    echo "  Line: " . $e->getLine() . "\n";
}

?>
