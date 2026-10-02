<?php
echo "<h1>Testing Route Parsing</h1>";

// Simulate the routing logic
$assignment_id = 5;  // Test with assignment_id = 5

// This is what the router would get
$uri = "/hr/uniforms/return_confirm/" . $assignment_id;

echo "<p><strong>Input URI:</strong> " . htmlspecialchars($uri) . "</p>";

// Step 1: Check if it starts with /hr
if (strpos($uri, '/hr') === 0) {
    echo "<p>✓ Starts with /hr</p>";
    
    // Step 2: Extract $sub
    $sub = trim(substr($uri, strlen('/hr')), '/');
    echo "<p><strong>$sub after extraction:</strong> " . htmlspecialchars($sub) . "</p>";
    
    // Step 3: Check if it matches the route
    if (strpos($sub, 'uniforms/return_confirm/') === 0) {
        echo "<p>✓ Matches uniforms/return_confirm route</p>";
        
        // Step 4: Extract assignment_id
        $extractedId = (int) substr($sub, strlen('uniforms/return_confirm/'));
        echo "<p><strong>Extracted Assignment ID:</strong> " . $extractedId . "</p>";
        
        if ($extractedId === $assignment_id) {
            echo "<p style='color: green;'><strong>✓ SUCCESS - Route parsing works correctly!</strong></p>";
        }
    } else {
        echo "<p style='color: red;'>✗ Does NOT match uniforms/return_confirm route</p>";
    }
} else {
    echo "<p style='color: red;'>✗ Does NOT start with /hr</p>";
}
?>
