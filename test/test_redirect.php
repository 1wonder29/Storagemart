<?php
require 'config/config.php';

echo "BASE_URL constant: '" . BASE_URL . "'\n";
echo "BASE_URL length: " . strlen(BASE_URL) . "\n";
echo "BASE_URL is empty: " . (empty(BASE_URL) ? 'YES' : 'NO') . "\n";

$_SESSION = [];
$_SESSION['account_id'] = 2200601;
$_SESSION['username'] = 'hr_test';
$_SESSION['usertype'] = 'HR';

echo "\nSession data set:\n";
var_dump($_SESSION);

// Test redirect construction
$base = BASE_URL;
if ($base === '') $base = '/';

$testPaths = ['/hr/dashboard', '/login', '/admin'];
foreach ($testPaths as $path) {
    if ($path[0] === '/') {
        $target = rtrim($base, '/') . $path;
    } else {
        $target = rtrim($base, '/') . '/' . $path;
    }
    echo "\nRedirect for '$path': '" . $target . "'\n";
}
?>
