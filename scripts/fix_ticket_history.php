<?php
require_once __DIR__ . '/../config/config.php';

try {
    $pdo = new PDO('mysql:host=localhost;dbname=howard_tms', 'root', '');
    
    // Check if tblticket_history table exists
    $stmt = $pdo->prepare("SHOW TABLES LIKE 'tblticket_history'");
    $stmt->execute();
    $tableExists = $stmt->fetch();
    
    if (!$tableExists) {
        echo "❌ Table tblticket_history does NOT exist. Creating it...\n";
        
        $pdo->exec("CREATE TABLE IF NOT EXISTS tblticket_history (
            history_id INT AUTO_INCREMENT PRIMARY KEY,
            ticket_id INT NOT NULL,
            action_details VARCHAR(255),
            old_status VARCHAR(50),
            new_status VARCHAR(50),
            date_logged DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (ticket_id) REFERENCES tbltickets(ticket_id) ON DELETE CASCADE
        )");
        
        echo "✅ Table tblticket_history created successfully!\n";
    } else {
        echo "✅ Table tblticket_history exists.\n";
        
        // Check table structure
        $stmt = $pdo->prepare("DESCRIBE tblticket_history");
        $stmt->execute();
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "\nTable Structure:\n";
        foreach ($columns as $col) {
            echo "  - " . $col['Field'] . " (" . $col['Type'] . ")\n";
        }
    }
    
    // Test query with ticket_id = 1
    $stmt = $pdo->prepare("SELECT * FROM tblticket_history WHERE ticket_id = ? LIMIT 5");
    $stmt->execute([1]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "\nSample query result (first 5 rows for ticket_id=1):\n";
    echo json_encode($rows, JSON_PRETTY_PRINT) . "\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>
