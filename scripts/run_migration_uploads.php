<?php
// Quick migration runner for the upload table

require_once __DIR__ . '/../config/config.php';

try {
    // Read migration file
    $sql = file_get_contents(__DIR__ . '/migration_add_ticket_uploads.sql');
    
    // Split by semicolon and execute each statement
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    foreach ($statements as $statement) {
        if (!empty($statement)) {
            $pdo->exec($statement);
            echo "✓ Executed: " . substr($statement, 0, 60) . "...\n";
        }
    }
    
    echo "\n✅ Migration completed successfully!\n";
    echo "Table 'tblticket_uploads' created.\n";
    
} catch (Exception $e) {
    echo "❌ Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
?>
