<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>OM Rate & Download Diagnostic</h2>";

// Check if OMTicketController exists and can be loaded
echo "<h3>1. Checking OMTicketController</h3>";
$controllerPath = __DIR__ . '/app/Controllers/om/OMTicketController.php';
if (file_exists($controllerPath)) {
    echo "✓ OMTicketController.php exists<br>";
    
    try {
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
    }
} else {
    echo "✗ OMTicketController.php NOT found<br>";
}

// Check if rate.php view exists
echo "<h3>2. Checking rate.php view</h3>";
$ratePath = __DIR__ . '/app/Views/om/ticket/rate.php';
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
    
    // Check for key variables
    if (strpos($content, '$ticketId') !== false) {
        echo "  ✓ Uses \$ticketId variable<br>";
    }
    if (strpos($content, '$base') !== false) {
        echo "  ✓ Uses \$base variable<br>";
    }
    if (strpos($content, '$alreadyRated') !== false) {
        echo "  ✓ Uses \$alreadyRated variable<br>";
    }
} else {
    echo "✗ rate.php view NOT found<br>";
}

// Check OMTicketRatingModel
echo "<h3>3. Checking OMTicketRatingModel</h3>";
$modelPath = __DIR__ . '/app/Models/om/TicketRatingModel.php';
if (file_exists($modelPath)) {
    echo "✓ TicketRatingModel.php exists<br>";
} else {
    echo "✗ TicketRatingModel.php NOT found<br>";
}

// Check PdfGeneratorService
echo "<h3>4. Checking PdfGeneratorService</h3>";
$servicePath = __DIR__ . '/app/Services/PdfGeneratorService.php';
if (file_exists($servicePath)) {
    echo "✓ PdfGeneratorService.php exists<br>";
    
    try {
        require_once $servicePath;
        if (method_exists('PdfGeneratorService', 'generateTechnicalRecordDocx')) {
            echo "✓ generateTechnicalRecordDocx() method exists<br>";
        } else {
            echo "✗ generateTechnicalRecordDocx() method NOT found<br>";
        }
    } catch (Exception $e) {
        echo "✗ Error loading PdfGeneratorService: " . $e->getMessage() . "<br>";
    }
} else {
    echo "✗ PdfGeneratorService.php NOT found<br>";
}

// Check routes in public/index.php
echo "<h3>5. Checking routes in public/index.php</h3>";
$routePath = __DIR__ . '/public/index.php';
if (file_exists($routePath)) {
    $routeContent = file_get_contents($routePath);
    
    if (strpos($routeContent, "tickets/rate") !== false) {
        echo "✓ 'tickets/rate' route found<br>";
    } else {
        echo "✗ 'tickets/rate' route NOT found<br>";
    }
    
    if (strpos($routeContent, "tickets/download-record") !== false) {
        echo "✓ 'tickets/download-record' route found<br>";
    } else {
        echo "✗ 'tickets/download-record' route NOT found<br>";
    }
    
    if (strpos($routeContent, "storeRating") !== false) {
        echo "✓ storeRating() call found in routes<br>";
    } else {
        echo "✗ storeRating() call NOT found in routes<br>";
    }
    
    if (strpos($routeContent, "downloadTechnicalRecord") !== false) {
        echo "✓ downloadTechnicalRecord() call found in routes<br>";
    } else {
        echo "✗ downloadTechnicalRecord() call NOT found in routes<br>";
    }
} else {
    echo "✗ public/index.php NOT found<br>";
}

echo "<h3>Diagnostic Complete</h3>";
?>
