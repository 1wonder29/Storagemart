<?php
/**
 * Migration Runner: Add Rating and Technical Report Path to Tickets
 * This script adds the required columns for the HR ticket upload feature
 */

require_once __DIR__ . '/../config/config.php';

echo "========================================\n";
echo "Running Migration: Add Rating and Technical Report Path\n";
echo "========================================\n\n";

try {
    // Read migration file
    $migrationFile = __DIR__ . '/migration_add_ticket_rating_and_report.sql';
    
    if (!file_exists($migrationFile)) {
        echo "❌ Migration file not found: $migrationFile\n";
        exit(1);
    }
    
    $sql = file_get_contents($migrationFile);
    
    // Split by semicolon and execute each statement
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    echo "Found " . count($statements) . " SQL statement(s) to execute.\n\n";
    
    $executedCount = 0;
    foreach ($statements as $statement) {
        if (!empty($statement) && !preg_match('/^--/', trim($statement))) {
            try {
                $pdo->exec($statement);
                $executedCount++;
                echo "✓ Executed statement " . $executedCount . "\n";
                echo "  " . substr(str_replace("\n", " ", $statement), 0, 70) . "...\n";
            } catch (Exception $stmtError) {
                // Column might already exist - that's okay
                if (strpos($stmtError->getMessage(), 'Duplicate column name') !== false) {
                    echo "ℹ Column already exists (skipping): " . substr($statement, 0, 50) . "...\n";
                } else {
                    throw $stmtError;
                }
            }
        }
    }
    
    echo "\n========================================\n";
    echo "✅ Migration completed successfully!\n";
    echo "========================================\n";
    echo "\nVerifying column structure...\n";
    
    // Verify columns exist
    $checkSql = "SELECT COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE 
                 FROM INFORMATION_SCHEMA.COLUMNS 
                 WHERE TABLE_NAME = 'tblticket' 
                 AND TABLE_SCHEMA = '" . DB_NAME . "'
                 AND COLUMN_NAME IN ('technical_report_path', 'rating')
                 ORDER BY COLUMN_NAME";
    
    $stmt = $pdo->query($checkSql);
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($columns) === 2) {
        echo "\n✓ Both required columns are present:\n";
        foreach ($columns as $col) {
            echo "  - {$col['COLUMN_NAME']}: {$col['COLUMN_TYPE']} (Nullable: {$col['IS_NULLABLE']})\n";
        }
        echo "\n✅ Upload feature is now ready to use!\n";
    } else {
        echo "\n⚠ Warning: Expected 2 columns but found " . count($columns) . "\n";
        foreach ($columns as $col) {
            echo "  - {$col['COLUMN_NAME']}\n";
        }
    }
    
} catch (Exception $e) {
    echo "\n========================================\n";
    echo "❌ Migration failed!\n";
    echo "========================================\n";
    echo "Error: " . $e->getMessage() . "\n";
    echo "\nPlease check:\n";
    echo "1. Database credentials in config/config.php\n";
    echo "2. Database connection status\n";
    echo "3. The tblticket table exists\n";
    exit(1);
}
?>
