<?php
require 'config/config.php';

// Get the HR account
$stmt = $pdo->query('SELECT password FROM tblaccounts WHERE username="hr_test"');
$account = $stmt->fetch(PDO::FETCH_ASSOC);

$storedHash = $account['password'];
$testPasswords = ['hr123456', 'hr_test', 'password', '123456'];

echo "Testing password verification for hr_test account:\n";
echo "Stored password hash: " . substr($storedHash, 0, 30) . "...\n\n";

foreach ($testPasswords as $testPass) {
    $verified = password_verify($testPass, $storedHash);
    echo "Testing password: '$testPass' => " . ($verified ? '✓ MATCHES' : '✗ NO MATCH') . "\n";
}

// Also verify the hash was created properly
$rehashed = password_hash('hr123456', PASSWORD_BCRYPT);
echo "\n\nTesting if we can recreate the hash:\n";
echo "New hash for 'hr123456': " . substr($rehashed, 0, 30) . "...\n";
echo "Can verify new hash with 'hr123456': " . (password_verify('hr123456', $rehashed) ? '✓ YES' : '✗ NO') . "\n";
?>
