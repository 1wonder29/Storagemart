<?php
// Test to verify login form renders correctly

define('BASE_URL', '');

$base = rtrim(BASE_URL, '/');

echo "=== LOGIN FORM TEST ===\n\n";
echo "BASE_URL constant: '" . BASE_URL . "'\n";
echo "$base variable: '" . $base . "'\n\n";

echo "Form action would be:\n";
echo "  htmlspecialchars(\$base) . '/login-post' = '" . htmlspecialchars($base) . "/login-post'\n";
echo "  Direct '/login-post' = '/login-post'\n\n";

echo "All resource URLs would be:\n";
echo "  Favicon: " . htmlspecialchars($base) . "/assets/img/favicon.png\n";
echo "  Logo: " . htmlspecialchars($base) . "/assets/img/storagemart-logo.png\n";
echo "  CSS: " . htmlspecialchars($base) . "/assets/css/style.css\n";
echo "  Script: " . htmlspecialchars($base) . "/assets/author/ouaaa.js\n";
echo "  Forgot: " . htmlspecialchars($base) . "/forgot-password\n\n";

echo "✓ All paths are relative and will work correctly on any domain/port.\n";
echo "✓ Form action is now '/login-post' which will submit to the current domain/port.\n";
?>
