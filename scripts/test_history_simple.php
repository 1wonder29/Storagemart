<?php
// Simple test of the fetchTicketHistory method

// Suppress all output initially
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Now get the required files
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/Models/admin/Ticket.php';

try {
    $ticketModel = new Ticket();
    
    // Test the method
    $history = $ticketModel->fetchTicketHistory(86);
    
    // Output JSON
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => true,
        'ticket_id' => 86,
        'history_count' => count($history),
        'history' => $history
    ]);
    
} catch (Throwable $e) {
    header('Content-Type: application/json; charset=utf-8');
    http_response_code(500);
    
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'error_line' => $e->getLine(),
        'error_file' => $e->getFile()
    ]);
}
