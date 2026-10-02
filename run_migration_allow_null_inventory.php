<?php
/**
 * Migration: Allow NULL inventory_id in tbltickets
 * This allows general tickets that are not asset-specific
 */

require_once __DIR__ . '/config/config.php';

try {
    // Connect to database
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
        DB_USER,
        DB_PASSWORD,
        array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION)
    );

    echo "<h2>Running Migration: Allow NULL inventory_id</h2>";

    // Run migration
    $sql = "ALTER TABLE `tbltickets` MODIFY COLUMN `inventory_id` int(11) NULL;";
    
    echo "<p>Executing: " . htmlspecialchars($sql) . "</p>";
    
    $pdo->exec($sql);
    
    echo "<p style='color: green;'>✓ Migration completed successfully!</p>";

    // Verify the change
    $result = $pdo->query("DESC `tbltickets`");
    echo "<h3>Table Structure:</h3>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        if ($row['Field'] === 'inventory_id') {
            echo "<tr style='background-color: #ffffcc;'>";
        } else {
            echo "<tr>";
        }
        echo "<td>" . htmlspecialchars($row['Field']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Null']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Key']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Default'] ?? '-') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    http_response_code(500);
}
?>
