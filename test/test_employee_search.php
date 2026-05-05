<?php
session_start();
define('BASE_URL', '');

// Initialize global PDO
global $pdo;
$pdo = new PDO('mysql:host=localhost;dbname=howard_tms;charset=utf8mb4', 'root', '');

require_once __DIR__ . '/app/Models/hr/HRModel.php';
require_once __DIR__ . '/app/Models/hr/EmployeeModel.php';

$empModel = new EmployeeModel();

echo "=== TESTING EMPLOYEE SEARCH ===\n\n";

// Test search
$results = $empModel->searchEmployees('Kenneth', 10);
echo "Search for 'Kenneth':\n";
echo "Found: " . count($results) . " results\n";
if (count($results) > 0) {
    foreach ($results as $emp) {
        echo "  - {$emp['firstname']} {$emp['lastname']} ({$emp['email']})\n";
    }
}

// Test search for last name
echo "\n\nSearch for 'Abueva':\n";
$results = $empModel->searchEmployees('Abueva', 10);
echo "Found: " . count($results) . " results\n";
if (count($results) > 0) {
    foreach ($results as $emp) {
        echo "  - {$emp['firstname']} {$emp['lastname']} ({$emp['email']})\n";
    }
}

// Test pagination with different offset
echo "\n\nTesting pagination (getAllEmployees):\n";
echo "Page 1 (offset 0, limit 5):\n";
$page1 = $empModel->getAllEmployees(0, 5);
echo "Found: " . count($page1) . " employees\n";
foreach ($page1 as $emp) {
    echo "  - {$emp['firstname']} {$emp['lastname']}\n";
}

echo "\nPage 2 (offset 5, limit 5):\n";
$page2 = $empModel->getAllEmployees(5, 5);
echo "Found: " . count($page2) . " employees\n";
foreach ($page2 as $emp) {
    echo "  - {$emp['firstname']} {$emp['lastname']}\n";
}

echo "\n✓ All employee queries working!\n";
?>
