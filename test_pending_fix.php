<?php
require 'config/config.php';
require 'app/Models/BaseModel.php';
require 'app/Models/admin/Ticket.php';

try {
    $model = new Ticket();
    $tickets = $model->fetchPendingTickets();
    echo "Pending tickets count: " . count($tickets) . "\n";
    
    if (count($tickets) > 0) {
        echo "First ticket:\n";
        echo "  Ticket ID: " . $tickets[0]['ticket_id'] . "\n";
        echo "  Ticket #: " . $tickets[0]['ticket_number'] . "\n";
        echo "  Status: " . $tickets[0]['status'] . "\n";
        echo "  Employee: " . $tickets[0]['fullname'] . "\n";
        echo "  Asset Info: " . $tickets[0]['asset_info'] . "\n";
    } else {
        echo "No pending tickets found\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
?>
