<?php
require '../vendor/autoload.php';
require_once "config.php";
include("session-checker.php");

use PhpOffice\PhpWord\TemplateProcessor;

// ✅ 1. Get employee_id
if (!isset($_GET['employee_id']) && !isset($_POST['employee_id'])) {
    die('❌ No employee ID provided.');
}
$employee_id = $_GET['employee_id'] ?? $_POST['employee_id'];

// ✅ 2. Fetch employee info
$query = "
    SELECT firstname, middlename, lastname, position, department
    FROM tblemployee 
    WHERE employee_id = ?
";
if ($stmt = mysqli_prepare($link, $query)) {
    mysqli_stmt_bind_param($stmt, "i", $employee_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $firstname, $middlename, $lastname, $position, $department);
    if (!mysqli_stmt_fetch($stmt)) {
        die('❌ Employee not found.');
    }
    mysqli_stmt_close($stmt);
} else {
    die('❌ Query error: ' . mysqli_error($link));
}
$fullname = trim("$firstname $middlename $lastname");

// ✅ 3. Fetch employee’s assigned assets with latest dateReturned
$assets = [];
$sqlAssets = "
    SELECT 
        i.assetNumber, 
        i.serialNumber, 
        i.itemInfo, 
        i.createdby, 
        a.datecreated AS dateIssued,
        (
            SELECT aa.dateReturned 
            FROM tblassets_assignment aa 
            WHERE aa.inventory_id = i.inventory_id
            ORDER BY aa.datecreated DESC
            LIMIT 1
        ) AS latestReturned
    FROM tblassets_inventory i
    LEFT JOIN tblassets_assignment a ON i.inventory_id = a.inventory_id 
    WHERE a.employee_id = ?
    GROUP BY i.inventory_id
    ORDER BY i.assetNumber ASC
";
if ($stmtAssets = mysqli_prepare($link, $sqlAssets)) {
    mysqli_stmt_bind_param($stmtAssets, "i", $employee_id);
    mysqli_stmt_execute($stmtAssets);
    $result = mysqli_stmt_get_result($stmtAssets);
    while ($row = mysqli_fetch_assoc($result)) {
        $assets[] = $row;
    }
    mysqli_stmt_close($stmtAssets);
} else {
    die('❌ Error fetching assets: ' . mysqli_error($link));
}

// ✅ 4. Load template
$templatePath = __DIR__ . '/template_accountability.docx';
if (!file_exists($templatePath)) {
    die('❌ Template file not found: ' . $templatePath);
}
$template = new TemplateProcessor($templatePath);

// ✅ 5. Fill employee info
$template->setValue('name', $fullname);
$template->setValue('department', $department ?? 'N/A');
$template->setValue('position', $position ?? 'N/A');
$template->setValue('employee_id', $employee_id);
$template->setValue('date_issued', date('F d, Y'));

// ✅ 6. Clone asset rows
if (!empty($assets)) {
    $template->cloneRow('itemInfo', count($assets));
    $i = 1;
    foreach ($assets as $asset) {
        $template->setValue("itemInfo#{$i}", $asset['itemInfo']);
        $template->setValue("createdby#{$i}", $asset['createdby']);
        $template->setValue("dateReturned#{$i}", $asset['latestReturned'] ?? 'N/A');
        $template->setValue("dateissued#{$i}", $asset['dateIssued']);
        $i++;
    }
} else {
    $template->setValue('itemInfo', 'No assets assigned');
    $template->setValue('createdby', '');
    $template->setValue('dateReturned', '');
}

// ✅ 7. Save & download
$outputFile = __DIR__ . "/filled_accountability_form_{$employee_id}.docx";
$template->saveAs($outputFile);

header("Content-Description: File Transfer");
header("Content-Disposition: attachment; filename=" . basename($outputFile));
header("Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document");
header("Content-Length: " . filesize($outputFile));
readfile($outputFile);
unlink($outputFile);
exit;
?>
