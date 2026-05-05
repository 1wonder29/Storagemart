<?php
// Test script to check what BASE_URL is set to - NO DB CONNECTION
echo "=== BASE_URL DEBUGGING ===\n\n";

// Load environment variables from .env
function loadEnv($file = __DIR__ . '/.env') {
    if (file_exists($file)) {
        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
                [$key, $value] = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value, '" \t\n\r\0\x0B');
                if (!isset($_ENV[$key])) {
                    putenv("$key=$value");
                }
            }
        }
    }
}
loadEnv();

define('BASE_URL', getenv('BASE_URL') ?: '');

echo "getenv('BASE_URL'): '" . getenv('BASE_URL') . "'\n";
echo "BASE_URL constant: '" . BASE_URL . "'\n";

echo "\n=== .ENV FILE CHECK ===\n";
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    echo "✓ .env file exists at: $envFile\n";
    echo "Contents:\n";
    $lines = file($envFile);
    foreach ($lines as $line) {
        echo "  " . trim($line) . "\n";
    }
} else {
    echo "✗ .env file NOT found at: $envFile\n";
}

echo "\n=== SYSTEM ENVIRONMENT VARIABLES ===\n";
$env = getenv();
echo "Total env vars: " . count($env) . "\n";
$found_base = false;
foreach ($env as $key => $value) {
    if (strpos(strtoupper($key), 'BASE') !== false) {
        echo "$key = $value\n";
        $found_base = true;
    }
}
if (!$found_base) echo "No BASE* environment variables found\n";

echo "\n=== Looking for 8000 ===\n";
$found_8000 = false;
foreach ($env as $key => $value) {
    if (strpos($value, '8000') !== false) {
        echo "$key = $value\n";
        $found_8000 = true;
    }
}
if (!$found_8000) echo "No environment variables contain '8000'\n";

echo "\nDone.\n";
?>
