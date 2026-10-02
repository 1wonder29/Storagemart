<?php
echo "<h1>Route Matching Test</h1>";

// Test URLs
$testUrls = [
    '/hr/uniforms/return_confirm/1',
    '/hr/uniforms/return_confirm/5',
    '/hr/uniforms/return_confirm/999'
];

foreach ($testUrls as $uri) {
    echo "<h2>Testing: " . htmlspecialchars($uri) . "</h2>";
    
    // This mimics the routing logic
    if (strpos($uri, '/hr') === 0) {
        echo "<p>✓ Starts with /hr</p>";
        
        $sub = trim(substr($uri, strlen('/hr')), '/');
        echo "<p>Extracted \$sub: <code>" . htmlspecialchars($sub) . "</code></p>";
        
        if (strpos($sub, 'uniforms/return_confirm/') === 0) {
            echo "<p style='color: green;'><strong>✓ MATCHES uniforms/return_confirm route</strong></p>";
            
            $assignmentId = (int) substr($sub, strlen('uniforms/return_confirm/'));
            echo "<p>Extracted Assignment ID: <strong>" . $assignmentId . "</strong></p>";
            
            if ($assignmentId > 0) {
                echo "<p style='color: green;'>✓ Valid assignment ID</p>";
            } else {
                echo "<p style='color: red;'>✗ Invalid assignment ID (0 or less)</p>";
            }
        } else {
            echo "<p style='color: red;'><strong>✗ Does NOT match uniforms/return_confirm route</strong></p>";
        }
    } else {
        echo "<p style='color: red;'>✗ Does NOT start with /hr</p>";
    }
    
    echo "<hr>";
}

// Now test the actual server
echo "<h2>Test with actual server</h2>";
echo "<p>Try clicking these links:</p>";

require_once 'config/config.php';
$base = rtrim(BASE_URL, '/');

$testAssignmentIds = [1, 5, 10];

foreach ($testAssignmentIds as $id) {
    $url = $base . '/hr/uniforms/return_confirm/' . $id;
    echo "<p><a href='" . htmlspecialchars($url) . "' target='_blank'>" . htmlspecialchars($url) . "</a></p>";
}
?>
