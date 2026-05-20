<?php
// Quick diagnostic script to check AOM assignments

require_once __DIR__ . '/config/config.php';

echo "=== Checking tblom_employee_assignments ===\n\n";

// Check structure
try {
    $stmt = $pdo->query("DESCRIBE tblom_employee_assignments");
    echo "Table Structure:\n";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "  " . $row['Field'] . " - " . $row['Type'] . "\n";
    }
    echo "\n";
} catch (Exception $e) {
    echo "Error describing table: " . $e->getMessage() . "\n";
}

// Check data with joins
echo "=== All OM Employee Assignments ===\n";
try {
    $query = "
        SELECT 
            oea.assignment_id,
            oea.om_employee_id,
            om.firstname as om_fname,
            om.lastname as om_lname,
            oea.employee_id,
            e.firstname as emp_fname,
            e.lastname as emp_lname,
            oea.aom_id,
            aom.firstname as aom_fname,
            aom.lastname as aom_lname,
            oea.assignment_date,
            oea.is_active,
            oea.notes
        FROM tblom_employee_assignments oea
        LEFT JOIN tblemployee om ON oea.om_employee_id = om.employee_id
        LEFT JOIN tblemployee e ON oea.employee_id = e.employee_id
        LEFT JOIN tblemployee aom ON oea.aom_id = aom.employee_id
    ";
    
    $stmt = $pdo->query($query);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($results)) {
        echo "No assignments found in tblom_employee_assignments\n\n";
    } else {
        echo "Found " . count($results) . " assignments:\n";
        foreach ($results as $row) {
            echo sprintf("  %s %s -> %s %s (AOM: %s %s) [Active: %s]\n",
                $row['emp_fname'], $row['emp_lname'],
                $row['om_fname'], $row['om_lname'],
                $row['aom_fname'], $row['aom_lname'],
                $row['is_active'] ? 'Yes' : 'No'
            );
        }
    }
} catch (Exception $e) {
    echo "Error querying assignments: " . $e->getMessage() . "\n";
}

echo "\n=== Checking tblbranch_assignments ===\n";
try {
    $query = "
        SELECT 
            ba.assignment_id,
            ba.branch_id,
            b.branchName,
            ba.aom_employee_id,
            aom.firstname,
            aom.lastname,
            ba.is_active,
            ba.assignment_date
        FROM tblbranch_assignments ba
        LEFT JOIN tblbranch b ON ba.branch_id = b.branch_id
        LEFT JOIN tblemployee aom ON ba.aom_employee_id = aom.employee_id
    ";
    
    $stmt = $pdo->query($query);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($results)) {
        echo "No assignments found in tblbranch_assignments\n";
    } else {
        echo "Found " . count($results) . " branch assignments\n";
        foreach ($results as $row) {
            echo sprintf("  Branch: %s -> AOM: %s %s [Active: %s]\n",
                $row['branchName'],
                $row['firstname'], $row['lastname'],
                $row['is_active'] ? 'Yes' : 'No'
            );
        }
    }
} catch (Exception $e) {
    echo "Error querying branch assignments: " . $e->getMessage() . "\n";
}

echo "\n=== Employees by Branch ===\n";
try {
    $query = "
        SELECT 
            e.firstname,
            e.lastname,
            e.branch_id,
            b.branchName
        FROM tblemployee e
        LEFT JOIN tblbranch b ON e.branch_id = b.branch_id
        GROUP BY e.branch_id, b.branchName
        ORDER BY b.branchName
    ";
    
    $stmt = $pdo->query($query);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Employee distribution:\n";
    foreach ($results as $row) {
        echo sprintf("  Branch: %s (Branch ID: %s)\n", $row['branchName'] ?? 'NULL', $row['branch_id'] ?? 'NULL');
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
