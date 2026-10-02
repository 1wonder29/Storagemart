<?php
/**
 * Direct Database Fix - Add Missing Columns to tblticket
 * Hardcoded database credentials from .env
 */

echo "========================================\n";
echo "Adding Missing Database Columns\n";
echo "========================================\n\n";

try {
    // Hardcoded from .env
    $dbHost = 'localhost';
    $dbName = 'howard_tms';
    $dbUser = 'root';
    $dbPass = '';
    
    echo "Connecting to: $dbHost / $dbName\n";
    
    // Connect to database
    $pdo = new PDO(
        "mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4",
        $dbUser,
        $dbPass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    echo "✓ Connected to database\n\n";
    
    // Check if rating column exists
    $sql = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS 
            WHERE TABLE_NAME = 'tbltickets' AND COLUMN_NAME = 'rating'
            AND TABLE_SCHEMA = '" . $dbName . "'";
    $stmt = $pdo->query($sql);
    $ratingExists = $stmt->fetch();
    
    // Check if technical_report_path column exists
    $sql = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS 
            WHERE TABLE_NAME = 'tbltickets' AND COLUMN_NAME = 'technical_report_path'
            AND TABLE_SCHEMA = '" . $dbName . "'";
    $stmt = $pdo->query($sql);
    $reportPathExists = $stmt->fetch();
    
    $modified = false;
    
    // Add rating column if missing
    if (!$ratingExists) {
        echo "Adding 'rating' column to tbltickets...\n";
        $pdo->exec("ALTER TABLE `tbltickets` ADD COLUMN `rating` int(1) DEFAULT NULL COMMENT 'User rating for ticket resolution (1-5)'");
        echo "✓ Added 'rating' column\n";
        $modified = true;
    } else {
        echo "✓ 'rating' column already exists\n";
    }
    
    // Add technical_report_path column if missing
    if (!$reportPathExists) {
        echo "Adding 'technical_report_path' column to tbltickets...\n";
        $pdo->exec("ALTER TABLE `tbltickets` ADD COLUMN `technical_report_path` varchar(255) DEFAULT NULL COMMENT 'Path to the technical report'");
        echo "✓ Added 'technical_report_path' column\n";
        $modified = true;
    } else {
        echo "✓ 'technical_report_path' column already exists\n";
    }
    
    if ($modified) {
        echo "\n========================================\n";
        echo "✅ Database columns added successfully!\n";
        echo "========================================\n";
        echo "\nYou can now try uploading again.\n";
    } else {
        echo "\n========================================\n";
        echo "✓ All columns already exist\n";
        echo "========================================\n";
        echo "\nIf upload still fails, check your application code.\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Code: " . $e->getCode() . "\n";
    exit(1);
}
?>
