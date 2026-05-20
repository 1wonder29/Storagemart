<?php
// Quick diagnostic script to check AOM assignments

require_once __DIR__ . '/../config/config.php';

echo "<pre style='font-family: monospace; background: #f5f5f5; padding: 15px;'>";

echo "=== Checking tblom_employee_assignments ===\n\n";

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
            echo sprintf("  %s %s -> OM: %s %s (AOM: %s %s) [Active: %s]\n",
                $row['emp_fname'], $row['emp_lname'],
                $row['om_fname'], $row['om_lname'],
                $row['aom_fname'], $row['aom_lname'],
                $row['is_active'] ? 'Yes' : 'No'
            );
        }
    }
    echo "\n";
} catch (Exception $e) {
    echo "Error querying assignments: " . $e->getMessage() . "\n\n";
}

echo "=== Checking tblbranch_assignments ===\n";
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
        echo "No assignments found in tblbranch_assignments\n\n";
    } else {
        echo "Found " . count($results) . " branch assignments\n";
        foreach ($results as $row) {
            echo sprintf("  Branch: %s -> AOM: %s %s [Active: %s]\n",
                $row['branchName'],
                $row['firstname'], $row['lastname'],
                $row['is_active'] ? 'Yes' : 'No'
            );
        }
        echo "\n";
    }
} catch (Exception $e) {
    echo "Error querying branch assignments: " . $e->getMessage() . "\n\n";
}

echo "=== Checking getAllEmployeesWithAOMAssignments query (FIXED) ===\n";
try {
    $query = "
        SELECT 
            e.employee_id,
            e.firstname,
            e.lastname,
            e.email,
            e.position,
            e.department,
            e.branch_id,
            b.branchName,
            oea.assignment_id,
            aom.employee_id as aom_id,
            aom.firstname as aom_firstname,
            aom.lastname as aom_lastname,
            oea.is_active,
            oea.assignment_date
        FROM tblemployee e
        LEFT JOIN tblbranch b ON e.branch_id = b.branch_id
        LEFT JOIN tblom_employee_assignments oea ON e.employee_id = oea.employee_id
        LEFT JOIN tblemployee aom ON oea.aom_id = aom.employee_id
        ORDER BY e.firstname, e.lastname
        LIMIT 10
    ";
    
    $stmt = $pdo->query($query);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Sample employees (first 10) - with FIXED query:\n";
    foreach ($results as $row) {
        $aom_name = (!empty($row['aom_firstname'])) ? $row['aom_firstname'] . ' ' . $row['aom_lastname'] : 'UNASSIGNED';
        echo sprintf("  %s %s (Branch: %s) -> AOM: %s\n",
            $row['firstname'], $row['lastname'],
            $row['branchName'] ?? 'NULL',
            $aom_name
        );
    }
    echo "\n";
} catch (Exception $e) {
    echo "Error in query: " . $e->getMessage() . "\n\n";
}

echo "</pre>";
?>
