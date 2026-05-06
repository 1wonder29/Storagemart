<?php
// Run migration: Allow NULL inventory_id for general tickets
require_once __DIR__ . '/../config/config.php';

// The $pdo variable is already created in config.php
try {
    // Modify the column to allow NULL
    $sql = "ALTER TABLE `tbltickets` MODIFY COLUMN `inventory_id` int(11) NULL";
    $pdo->exec($sql);
    
    echo "✓ Migration successful: inventory_id column now allows NULL\n";
    
    // Verify
    $result = $pdo->query("DESC tbltickets");
    $columns = $result->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($columns as $col) {
        if ($col['Field'] === 'inventory_id') {
            echo "✓ Column status: " . $col['Null'] . " (allows null)\n";
            break;
        }
    }
    
} catch (PDOException $e) {
    echo "✗ Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
?>
