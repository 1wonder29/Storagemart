<?php
echo "=== SERVER CONFIGURATION DIAGNOSTIC ===\n\n";

echo "1. SERVER PORT INFORMATION:\n";
echo "   HTTP_HOST: " . ($_SERVER['HTTP_HOST'] ?? 'NOT SET') . "\n";
echo "   SERVER_NAME: " . ($_SERVER['SERVER_NAME'] ?? 'NOT SET') . "\n";
echo "   SERVER_PORT: " . ($_SERVER['SERVER_PORT'] ?? 'NOT SET') . "\n";
echo "   REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'NOT SET') . "\n\n";

echo "2. PROTOCOL INFORMATION:\n";
echo "   REQUEST_SCHEME: " . ($_SERVER['REQUEST_SCHEME'] ?? 'NOT SET') . "\n";
echo "   HTTPS: " . ($_SERVER['HTTPS'] ?? 'NOT SET') . "\n\n";

echo "3. RECONSTRUCTED URL:\n";
$scheme = ($_SERVER['REQUEST_SCHEME'] ?? 'http');
$host = ($_SERVER['HTTP_HOST'] ?? 'localhost');
$uri = ($_SERVER['REQUEST_URI'] ?? '/');
$full_url = "$scheme://$host$uri";
echo "   Full URL: $full_url\n\n";

echo "4. BASE_URL CONFIGURATION:\n";
echo "   BASE_URL constant: '" . (defined('BASE_URL') ? BASE_URL : 'NOT DEFINED') . "'\n";
echo "   getenv('BASE_URL'): '" . (getenv('BASE_URL') ?: 'EMPTY') . "'\n\n";

echo "5. EXPECTED vs ACTUAL:\n";
echo "   Expected: App accessible at http://localhost/\n";
echo "   Actual: App accessible at $full_url\n\n";

if (strpos($host, ':8000') !== false) {
    echo "⚠️  FOUND: App is running on port 8000!\n";
    echo "   This is where the localhost:8000 error is coming from.\n\n";
    echo "   SOLUTION OPTIONS:\n";
    echo "   1. Access app at http://localhost/ (port 80) instead of :8000\n";
    echo "   2. If you need port 8000, update BASE_URL in .env to 'http://localhost:8000' (not recommended)\n";
} else {
    echo "✓ App is running on the expected port.\n";
    echo "   If you're still seeing localhost:8000 errors, they're coming from:\n";
    echo "   - A different server instance running on port 8000\n";
    echo "   - A development tool (BrowserSync, Vite, etc.) on port 8000\n";
    echo "   - An iframe loading from port 8000\n\n";
    echo "   NEXT STEPS:\n";
    echo "   1. Check what's running on port 8000: netstat -ano | findstr :8000\n";
    echo "   2. Make sure you're accessing the correct URL\n";
    echo "   3. Clear browser cache and cookies\n";
}
?>
