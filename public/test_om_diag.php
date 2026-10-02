<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>OM Rate & Download Diagnostic</h2>";

// Check if OMTicketController exists and can be loaded
echo "<h3>1. Checking OMTicketController</h3>";
$controllerPath = __DIR__ . '/../app/Controllers/om/OMTicketController.php';
if (file_exists($controllerPath)) {
    echo "✓ OMTicketController.php exists<br>";
    
    try {
        require_once __DIR__ . '/../app/Controllers/AuthController.php';
        require_once __DIR__ . '/../app/Models/employee/Employee.php';
        require_once __DIR__ . '/../app/Models/employee/Ticket.php';
        require_once __DIR__ . '/../app/Helpers/Session.php';
        require_once __DIR__ . '/../app/Models/admin/Logger.php';
        require_once __DIR__ . '/../app/Models/NotificationModel.php';
        require_once $controllerPath;
        echo "✓ OMTicketController loaded successfully<br>";
        
        // Check if methods exist
        if (method_exists('OMTicketController', 'rate')) {
            echo "✓ rate() method exists<br>";
        } else {
            echo "✗ rate() method NOT found<br>";
        }
        
        if (method_exists('OMTicketController', 'storeRating')) {
            echo "✓ storeRating() method exists<br>";
        } else {
            echo "✗ storeRating() method NOT found<br>";
        }
        
        if (method_exists('OMTicketController', 'downloadTechnicalRecord')) {
            echo "✓ downloadTechnicalRecord() method exists<br>";
        } else {
            echo "✗ downloadTechnicalRecord() method NOT found<br>";
        }
    } catch (Exception $e) {
        echo "✗ Error loading OMTicketController: " . $e->getMessage() . "<br>";
    } catch (Throwable $e) {
        echo "✗ Error (Throwable) loading OMTicketController: " . $e->getMessage() . "<br>";
    }
} else {
    echo "✗ OMTicketController.php NOT found at: " . $controllerPath . "<br>";
}

// Check if rate.php view exists
echo "<h3>2. Checking rate.php view</h3>";
$ratePath = __DIR__ . '/../app/Views/om/ticket/rate.php';
if (file_exists($ratePath)) {
    echo "✓ rate.php view exists<br>";
    
    // Check for DOMContentLoaded listeners
    $content = file_get_contents($ratePath);
    $domCount = substr_count($content, "document.addEventListener('DOMContentLoaded'");
    echo "  - Found {$domCount} DOMContentLoaded listener(s)<br>";
    
    if ($domCount > 1) {
        echo "  ✗ WARNING: Multiple DOMContentLoaded listeners could cause issues!<br>";
    } else {
        echo "  ✓ Single DOMContentLoaded listener<br>";
    }
} else {
    echo "✗ rate.php view NOT found at: " . $ratePath . "<br>";
}

echo "<h3>Diagnostic Complete</h3>";
?>
