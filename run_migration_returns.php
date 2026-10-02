<?php
/**
 * Run Returns Tracking Migration
 * Execute this file from browser: http://localhost/run_migration_returns.php
 */

require_once __DIR__ . '/config/config.php';

try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8',
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Read and execute the migration
    $migrationSQL = file_get_contents(__DIR__ . '/scripts/migration_add_returns_tracking.sql');
    
    // Execute each statement separately
    $statements = array_filter(array_map('trim', explode(';', $migrationSQL)));
    
    foreach ($statements as $statement) {
        if (!empty($statement)) {
            $pdo->exec($statement);
            echo "<p>✓ Executed: " . substr($statement, 0, 60) . "...</p>";
        }
    }

    echo "<h2 style='color: green;'>✓ Migration completed successfully!</h2>";
    echo "<p><a href='/hr/uniforms'>Go to Uniforms</a></p>";

} catch (PDOException $e) {
    echo "<h2 style='color: red;'>✗ Migration failed!</h2>";
    echo "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}
?>
