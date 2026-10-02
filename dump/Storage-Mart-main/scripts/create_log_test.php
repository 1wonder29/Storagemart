<?php
// scripts/create_log_test.php
$logsDir = __DIR__ . '/../app/logs';
$logFile = $logsDir . '/login_debug.log';

// create logs directory if missing
if (!is_dir($logsDir)) {
    if (!mkdir($logsDir, 0755, true)) {
        echo "Failed to create logs directory: $logsDir";
        exit;
    }
}

// try writing a test line
$ok = @file_put_contents($logFile, "[".date('Y-m-d H:i:s')."] TEST LOG ENTRY\n", FILE_APPEND);

if ($ok === false) {
    echo "Failed to write to $logFile — check filesystem permissions.";
} else {
    echo "Wrote test entry to: $logFile<br>";
    echo "Open this file in File Explorer (do not rely on the browser):<br>";
    echo "<code>$logFile</code>";
}
