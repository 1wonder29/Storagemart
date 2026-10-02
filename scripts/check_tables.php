<?php
echo "Database Tables in howard_tms:\n";
echo "==============================\n\n";

try {
    $pdo = new PDO(
        "mysql:host=localhost;dbname=howard_tms;charset=utf8mb4",
        'root',
        '',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    $sql = "SHOW TABLES";
    $stmt = $pdo->query($sql);
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Found " . count($tables) . " tables:\n";
    foreach ($tables as $table) {
        echo "  - $table\n";
    }
    
    // Look for ticket-related tables
    echo "\nLooking for ticket tables:\n";
    foreach ($tables as $table) {
        if (stripos($table, 'ticket') !== false) {
            echo "  ✓ Found: $table\n";
            
            // Show columns
            $sql = "DESCRIBE `$table`";
            $stmt = $pdo->query($sql);
            $columns = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
            foreach ($columns as $col) {
                echo "      - $col\n";
            }
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
