<?php
// Test the admin ticket history endpoint
session_start();

// Simulate admin session
$_SESSION['account_id'] = 1;
$_SESSION['usertype'] = 'ADMIN';

// Simulate the request
$_GET['ticket_id'] = 86; // From the screenshot

// Capture any output
ob_start();

// Require the controller and test it
require_once __DIR__ . '/../app/Controllers/admin/TicketController.php';

$controller = new TicketController();

// Call the history method (but need to handle it differently since it echos directly)
// Let's just check the model instead

require_once __DIR__ . '/../app/Models/admin/Ticket.php';

try {
    $ticketModel = new Ticket();
    $history = $ticketModel->fetchTicketHistory(86);
    
    // Clean output buffer
    ob_clean();
    
    // Output as JSON
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($history ?: []);
    
} catch (Exception $e) {
    // Clean output buffer to prevent HTML errors
    ob_clean();
    
    header('Content-Type: application/json; charset=utf-8');
    
    echo json_encode([
        'error' => 'Failed to fetch ticket history',
        'details' => $e->getMessage(),
        'line' => $e->getLine(),
        'file' => $e->getFile()
    ]);
}

// Get buffered output (if any)
$buffered = ob_get_clean();
if ($buffered) {
    echo "\n<!-- BUFFERED OUTPUT: -->\n" . htmlspecialchars($buffered);
}
