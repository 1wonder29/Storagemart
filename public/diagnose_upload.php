<?php
/**
 * Database Schema Diagnostic - Check if technical_report_path column exists
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/config/config.php';

echo "<h1>Database Schema Diagnostic</h1>";
echo "<hr>";

try {
    // Create PDO connection
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
        DB_USER,
        DB_PASS
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>✓ Database Connected</h2>";
    
    // Check if technical_report_path column exists
    $sql = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS 
            WHERE TABLE_NAME = 'tblticket' 
            AND COLUMN_NAME = 'technical_report_path'
            AND TABLE_SCHEMA = '" . DB_NAME . "'";
    
    $stmt = $pdo->query($sql);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result) {
        echo "<h2 style='color: green;'>✓ Column EXISTS: technical_report_path</h2>";
        echo "<p>The database column is present. The error must be coming from something else.</p>";
        
        // Check table structure
        echo "<h3>Current table structure for tblticket:</h3>";
        $sql = "DESCRIBE tblticket";
        $stmt = $pdo->query($sql);
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<table border='1' cellpadding='10'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th></tr>";
        foreach ($columns as $col) {
            echo "<tr>";
            echo "<td>" . $col['Field'] . "</td>";
            echo "<td>" . $col['Type'] . "</td>";
            echo "<td>" . $col['Null'] . "</td>";
            echo "<td>" . $col['Key'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<h2 style='color: red;'>✗ Column MISSING: technical_report_path</h2>";
        echo "<p><strong>This is the issue!</strong> The migration has not been run.</p>";
        echo "<p>To fix this, run the migration:</p>";
        echo "<pre>";
        echo file_get_contents(__DIR__ . '/scripts/migration_add_ticket_rating_and_report.sql');
        echo "</pre>";
    }
    
    // Check if uploads directory exists
    $uploadsDir = __DIR__ . '/app/uploads/technical_reports';
    echo "<h2>Upload Directory Check</h2>";
    if (is_dir($uploadsDir)) {
        echo "<p style='color: green;'>✓ Directory exists: " . $uploadsDir . "</p>";
        echo "<p>Permissions: " . substr(sprintf('%o', fileperms($uploadsDir)), -4) . "</p>";
    } else {
        echo "<p style='color: red;'>✗ Directory does NOT exist: " . $uploadsDir . "</p>";
        echo "<p>The directory will be created on first upload attempt.</p>";
    }
    
} catch (Exception $e) {
    echo "<h2 style='color: red;'>✗ Error: " . htmlspecialchars($e->getMessage()) . "</h2>";
    echo "<p>Cannot connect to database. Check your database credentials in config/config.php</p>";
}
?>
