<?php
// Simulate the actual flow: login -> session -> redirect -> dashboard access

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Load configuration and models
require 'config/config.php';
require 'app/Helpers/Session.php';
require 'app/Models/admin/Account.php';

echo "=== SIMULATING HR LOGIN & DASHBOARD ACCESS ===\n\n";

// Step 1: Simulate Login
echo "STEP 1: LOGIN ATTEMPT\n";
echo "-------------------\n";

$username = 'hr_test';
$password = 'hr123456';

$accountModel = new Account();
$user = $accountModel->findByUsername($username);

if ($user) {
    echo "✓ User found: $username\n";
    echo "  - Account ID: {$user['account_id']}\n";
    echo "  - User Type: {$user['usertype']}\n";
    echo "  - Status: {$user['status']}\n";
    
    // Verify password
    $passwordMatch = password_verify($password, $user['password']);
    echo "  - Password matches: " . ($passwordMatch ? '✓ YES' : '✗ NO') . "\n";
    
    if ($passwordMatch && strtolower($user['status']) !== 'inactive') {
        // Set session
        Session::regenerate();
        $_SESSION['account_id'] = $user['account_id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['usertype'] = $user['usertype'];
        
        echo "\n✓ Login successful - Session set\n";
        echo "  - account_id: {$_SESSION['account_id']}\n";
        echo "  - username: {$_SESSION['username']}\n";
        echo "  - usertype: {$_SESSION['usertype']}\n";
        
        // Step 2: Check authorization for HR
        echo "\n\nSTEP 2: HR AUTHORIZATION CHECK\n";
        echo "------------------------------\n";
        
        if (empty($_SESSION['account_id'])) {
            echo "✗ FAIL: account_id not in session\n";
            exit;
        }
        
        if (strtoupper($_SESSION['usertype'] ?? '') !== 'HR') {
            echo "✗ FAIL: User type is not HR (got: {$_SESSION['usertype']})\n";
            exit;
        }
        
        echo "✓ Authorization checks passed\n";
        
        // Step 3: Test HrController instantiation and dashboard method
        echo "\n\nSTEP 3: HR CONTROLLER INSTANTIATION\n";
        echo "----------------------------------\n";
        
        try {
            require_once 'app/Controllers/hr/HrController.php';
            $hrController = new HrController();
            echo "✓ HrController instantiated\n";
            
            // Test models
            echo "\n\nSTEP 4: TESTING MODELS\n";
            echo "---------------------\n";
            
            $totalEmployees = $hrController->employeeModel->getTotalEmployeeCount();
            echo "✓ Total employees count: $totalEmployees\n";
            
            $uniformsNeedingReorder = count($hrController->uniformModel->getUniformsNeedingReorder());
            echo "✓ Uniforms needing reorder: $uniformsNeedingReorder\n";
            
            $uniformStats = $hrController->uniformModel->getAssignmentStats();
            echo "✓ Uniform stats retrieved: " . json_encode($uniformStats) . "\n";
            
            $recentLogs = $hrController->hrModel->getRecentLogs(7);
            echo "✓ Recent logs retrieved: " . count($recentLogs) . " entries\n";
            
            $notifications = $hrController->notificationModel->getLatest($_SESSION['account_id'], 10);
            echo "✓ Notifications retrieved: " . count($notifications) . " items\n";
            
            echo "\n\n✅ ALL TESTS PASSED - HR DASHBOARD SHOULD LOAD SUCCESSFULLY\n";
            
        } catch (\Throwable $e) {
            echo "✗ Error: " . $e->getMessage() . "\n";
            echo "File: " . $e->getFile() . "\n";
            echo "Line: " . $e->getLine() . "\n";
            echo "Trace: " . $e->getTraceAsString() . "\n";
        }
    } else {
        echo "✗ Login failed: Password mismatch or inactive account\n";
    }
} else {
    echo "✗ User not found: $username\n";
}
?>
