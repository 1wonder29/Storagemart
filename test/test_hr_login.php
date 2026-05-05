<?php
require 'config/config.php';
require 'app/Models/admin/Account.php';

// Simulate HR login attempt
$username = 'hr_test';
$password = 'hr123456';

echo "Simulating HR login:\n";
echo "Username: $username\n";
echo "Password: $password\n\n";

$accountModel = new Account();

// Try to find the user
$user = $accountModel->findByUsername($username);
echo "User found: " . ($user ? 'YES' : 'NO') . "\n";

if ($user) {
    echo "User details:\n";
    echo "  ID: {$user['account_id']}\n";
    echo "  Username: {$user['username']}\n";
    echo "  Type: {$user['usertype']}\n";
    echo "  Status: {$user['status']}\n";
    echo "  Password hash: " . substr($user['password'], 0, 30) . "...\n";
    
    // Test password verification
    $passwordMatch = false;
    if (!empty($user['password']) && (strpos($user['password'], '$2y$') === 0 || strpos($user['password'], '$argon2') === 0)) {
        echo "\nPassword is hashed (bcrypt/argon2)\n";
        $passwordMatch = password_verify($password, $user['password']);
    } else {
        echo "\nPassword is plain text\n";
        $passwordMatch = ($user['password'] === $password);
    }
    
    echo "Password matches: " . ($passwordMatch ? 'YES ✓' : 'NO ✗') . "\n";
    
    // Check status
    if (isset($user['status']) && strtolower($user['status']) === "inactive") {
        echo "Account status: INACTIVE (would fail)\n";
    } else {
        echo "Account status: ACTIVE (would pass)\n";
    }
    
    if ($passwordMatch && isset($user['status']) && strtolower($user['status']) !== "inactive") {
        echo "\n✓ Login would SUCCEED\n";
        echo "Session would be set:\n";
        echo "  account_id: {$user['account_id']}\n";
        echo "  username: {$user['username']}\n";
        echo "  usertype: {$user['usertype']}\n";
        echo "  Redirect to: /hr/dashboard\n";
    }
} else {
    echo "✗ User not found in database\n";
}
?>
