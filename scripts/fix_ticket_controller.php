<?php
// Read the original file
$filePath = __DIR__ . '/../app/Controllers/admin/TicketController.php';
$content = file_get_contents($filePath);

// Use regex to find and replace the history method
// This handles special characters better
$pattern = '/\/\/ fetch ticket history\s+public function history\(\)\s+\{[^}]*?\$history = \$ticketModel->fetchTicketHistory\(\$ticketId\);[^}]*?echo json_encode\(\$history\);\s+\}/s';

$replacement = <<<'EOD'
    // fetch ticket history
    public function history()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        header('Content-Type: application/json; charset=utf-8');

        // Auth check – only ADMIN allowed (same rule as ticket())
        if (empty($_SESSION['account_id']) || strtoupper($_SESSION['usertype'] ?? '') !== 'ADMIN') {
            http_response_code(403);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }

        $ticketId = isset($_GET['ticket_id']) ? (int) $_GET['ticket_id'] : 0;

        if ($ticketId <= 0) {
            http_response_code(400);
            echo json_encode([]);
            exit;
        }

        try {
            $ticketModel = new Ticket();
            $history = $ticketModel->fetchTicketHistory($ticketId);
            echo json_encode($history ?: []);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to fetch ticket history']);
        }
        exit;
    }
EOD;

// Try regex replacement
$newContent = preg_replace($pattern, $replacement, $content, 1, $count);

if ($count === 0) {
    echo "ERROR: Could not find the old method using regex.\n";
    echo "Will try manual line-based replacement instead...\n";
    
    // Alternative: Find the method by line inspection
    $lines = explode("\n", $content);
    $foundStart = -1;
    
    for ($i = 0; $i < count($lines); $i++) {
        if (strpos($lines[$i], 'public function history()') !== false) {
            $foundStart = $i;
            echo "Found history() method at line " . ($i + 1) . "\n";
            break;
        }
    }
    
    if ($foundStart === -1) {
        echo "ERROR: Could not find history() method at all\n";
        exit(1);
    }
    
    // Find the closing brace of the method
    $braceCount = 0;
    $foundEnd = -1;
    for ($i = $foundStart; $i < count($lines); $i++) {
        $braceCount += substr_count($lines[$i], '{');
        $braceCount -= substr_count($lines[$i], '}');
        if ($braceCount === 0 && $i > $foundStart) {
            $foundEnd = $i;
            break;
        }
    }
    
    if ($foundEnd === -1) {
        echo "ERROR: Could not find closing brace for history() method\n";
        exit(1);
    }
    
    echo "Found closing brace at line " . ($foundEnd + 1) . "\n";
    
    // Replace the lines
    $newMethodLines = explode("\n", $replacement);
    array_splice($lines, $foundStart, $foundEnd - $foundStart + 1, $newMethodLines);
    
    $newContent = implode("\n", $lines);
}

// Write the new content back to the file
file_put_contents($filePath, $newContent);

echo "✓ TicketController history() method updated successfully with error handling\n";

