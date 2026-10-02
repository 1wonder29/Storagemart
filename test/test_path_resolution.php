<?php
$dashboardFile = 'c:\xampp\htdocs\be\Storagemart\app\Views\hr\dashboard.php';

// Simulate __DIR__ in dashboard.php
$dashboardDir = 'c:\xampp\htdocs\be\Storagemart\app\Views\hr';

// Test the path resolution
$testPaths = [
    '/../partials/hr/sidebar_topbar.php' => $dashboardDir . '/../partials/hr/sidebar_topbar.php',
    '/../../../partials/hr/sidebar_topbar.php' => $dashboardDir . '/../../../partials/hr/sidebar_topbar.php',
    '/../../partials/hr/sidebar_topbar.php' => $dashboardDir . '/../../partials/hr/sidebar_topbar.php',
];

echo "Testing path resolution from dashboard.php:\n";
echo "Dashboard DIR: $dashboardDir\n\n";

foreach ($testPaths as $relative => $fullPath) {
    $normalized = realpath(dirname($dashboardDir)) . $relative;
    $normalized = str_replace('/', '\\', $normalized);
    $exists = file_exists($normalized);
    
    echo "Path: $relative\n";
    echo "  Resolves to: $normalized\n";
    echo "  Exists: " . ($exists ? 'YES ✓' : 'NO ✗') . "\n\n";
}

// Test what PHP actually sees
echo "\n\nTesting from actual file:\n";
$testFile = 'app/Views/hr/dashboard.php';
if (file_exists($testFile)) {
    echo "✓ Dashboard file exists\n";
    $dir = dirname($testFile);
    $relPath = $dir . '/../partials/hr/sidebar_topbar.php';
    $normalized = realpath($relPath);
    echo "Normalized path: $normalized\n";
    echo "Exists: " . (file_exists($normalized) ? 'YES ✓' : 'NO ✗') . "\n";
} else {
    echo "✗ Dashboard file not found\n";
}
?>
