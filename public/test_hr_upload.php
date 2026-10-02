<?php
/**
 * HR Upload Test - Diagnose the exact issue
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../config/config.php';

echo "<h1>HR Upload Diagnostic</h1>";
echo "<hr>";

// 1. Check database connection
echo "<h2>1. Database Connection</h2>";
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
        DB_USER,
        DB_PASS
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p style='color:green'>✓ Connected to database</p>";
} catch (Exception $e) {
    echo "<p style='color:red'>✗ Cannot connect: " . $e->getMessage() . "</p>";
    exit;
}

// 2. Check if columns exist
echo "<h2>2. Database Column Check</h2>";
try {
    $sql = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS 
            WHERE TABLE_NAME = 'tblticket' 
            AND COLUMN_NAME IN ('technical_report_path', 'rating')
            AND TABLE_SCHEMA = '" . DB_NAME . "'";
    
    $stmt = $pdo->query($sql);
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<p>Found columns: " . implode(', ', $columns) . "</p>";
    
    if (count($columns) === 2) {
        echo "<p style='color:green'>✓ Both required columns exist</p>";
    } else {
        echo "<p style='color:red'>✗ Missing columns. Expected 2, found " . count($columns) . "</p>";
    }
} catch (Exception $e) {
    echo "<p style='color:red'>✗ Error checking columns: " . $e->getMessage() . "</p>";
}

// 3. Check upload directory
echo "<h2>3. Upload Directory</h2>";
$uploadsDir = __DIR__ . '/../app/uploads/technical_reports';
echo "<p>Path: " . $uploadsDir . "</p>";
echo "<p>Exists: " . (is_dir($uploadsDir) ? 'Yes' : 'No') . "</p>";
echo "<p>Writable: " . (is_writable(dirname($uploadsDir)) ? 'Yes' : 'No') . "</p>";

if (is_dir($uploadsDir)) {
    $files = scandir($uploadsDir);
    $files = array_diff($files, array('.', '..'));
    echo "<p>Files in directory: " . count($files) . "</p>";
    if (count($files) > 0) {
        echo "<ul>";
        foreach (array_slice($files, -5) as $file) {
            $fullPath = $uploadsDir . '/' . $file;
            $size = filesize($fullPath);
            $date = date('Y-m-d H:i:s', filemtime($fullPath));
            echo "<li>$file - " . ($size / 1024) . "KB - $date</li>";
        }
        echo "</ul>";
    }
}

// 4. Check PHP upload_tmp_dir
echo "<h2>4. PHP Upload Settings</h2>";
echo "<p>upload_tmp_dir: " . ini_get('upload_tmp_dir') . "</p>";
echo "<p>upload_max_filesize: " . ini_get('upload_max_filesize') . "</p>";
echo "<p>post_max_size: " . ini_get('post_max_size') . "</p>";

// 5. Check a sample ticket
echo "<h2>5. Sample Ticket</h2>";
try {
    $sql = "SELECT ticket_id, ticket_number, status, technical_report_path FROM tblticket WHERE status = 'Resolved' LIMIT 1";
    $stmt = $pdo->query($sql);
    $ticket = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($ticket) {
        echo "<p>Found resolved ticket: #{$ticket['ticket_number']} (ID: {$ticket['ticket_id']})</p>";
        echo "<p>Current technical_report_path: " . ($ticket['technical_report_path'] ?: 'NULL') . "</p>";
    } else {
        echo "<p style='color:orange'>⚠ No resolved tickets found for testing</p>";
    }
} catch (Exception $e) {
    echo "<p style='color:red'>✗ Error: " . $e->getMessage() . "</p>";
}

// 6. Check error log
echo "<h2>6. Recent Error Log</h2>";
$errorLogFiles = [
    'C:\xampp\php\logs\php_error_log',
    __DIR__ . '/../error_log',
    __DIR__ . '/../../error_log'
];

$found = false;
foreach ($errorLogFiles as $logFile) {
    if (file_exists($logFile)) {
        echo "<p>Found error log: $logFile</p>";
        $lines = file($logFile);
        $lastLines = array_slice($lines, -10);
        echo "<pre>";
        foreach ($lastLines as $line) {
            if (strpos($line, 'uploadTechnicalReport') !== false || 
                strpos($line, 'upload') !== false ||
                strpos($line, 'Error') !== false) {
                echo htmlspecialchars($line);
            }
        }
        echo "</pre>";
        $found = true;
        break;
    }
}

if (!$found) {
    echo "<p style='color:orange'>⚠ Could not find error log files</p>";
}

echo "<hr>";
echo "<p><strong>Summary:</strong> Check the above for any issues marked in red.</p>";
?>
