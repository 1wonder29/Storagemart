<?php
require '../vendor/autoload.php';
require_once "config.php";
include("../Employee/session-checker.php");

use PhpOffice\PhpWord\TemplateProcessor;

if (!isset($_GET['ticket_id'])) {
    die('No ticket ID provided.');
}
$ticket_id = intval($_GET['ticket_id']);

$sql = "

SELECT 
    t.ticket_number,
    t.concern_details,
    t.priority,
    t.date_filed,
    t.status,

    e.firstname AS emp_firstname,
    e.middlename AS emp_middlename,
    e.lastname AS emp_lastname,
    e.position AS emp_position,

    b.branchName,

    tt.technical_purpose,
    tt.action_taken,
    tt.result,
    tt.remarks,
    tt.date_performed,

    it.firstname AS it_firstname,
    it.lastname AS it_lastname

FROM tbltickets t

JOIN tblemployee e 
    ON t.employee_id = e.employee_id

JOIN tblbranch b 
    ON e.branch_id = b.branch_id

JOIN (
    SELECT ticket_id, MAX(date_performed) AS max_date
    FROM tblticket_technical
    GROUP BY ticket_id
) x ON x.ticket_id = t.ticket_id

JOIN tblticket_technical tt
    ON tt.ticket_id = x.ticket_id
   AND tt.date_performed = x.max_date

JOIN tblemployee it 
    ON tt.performed_by = it.employee_id

WHERE t.ticket_id = ?
";


$stmt = mysqli_prepare($link, $sql);
mysqli_stmt_bind_param($stmt, "i", $ticket_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
if (!$result || mysqli_num_rows($result) === 0) {
    die('No data found for this ticket.');
}
$data = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

$fullname = trim($data['emp_firstname'] . ' ' . $data['emp_lastname']);
$performedby = trim($data['it_firstname'] . ' ' . $data['it_lastname']);
$date_filed = date('F d, Y', strtotime($data['date_filed']));
$date_performed = date('F d, Y', strtotime($data['date_performed']));

$templatePath = __DIR__ . '/template_technical.docx';
if (!file_exists($templatePath)) {
    die('Template file not found: ' . $templatePath);
}

$template = new TemplateProcessor($templatePath);

// XML-safe replacements
$template->setValue('date_filed', htmlspecialchars($date_filed));
$template->setValue('fullname', htmlspecialchars($fullname));
$template->setValue('branchName', htmlspecialchars($data['branchName']));
$template->setValue('performedby', htmlspecialchars($performedby));
$template->setValue('technical_purpose', htmlspecialchars($data['technical_purpose']));
$template->setValue('concern_details', htmlspecialchars($data['concern_details']));
$template->setValue('action_taken', htmlspecialchars($data['action_taken']));
$template->setValue('result', htmlspecialchars($data['result']));
$template->setValue('date_performed', htmlspecialchars($date_performed));

$outputFile = __DIR__ . "/Technical_Report_" . preg_replace('/[^A-Za-z0-9_-]/', '', $data['ticket_number']) . ".docx";
$template->saveAs($outputFile);

// Clean output buffers before sending
if (ob_get_length()) ob_end_clean();
header("Content-Description: File Transfer");
header("Content-Disposition: attachment; filename=" . basename($outputFile));
header("Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document");
header("Content-Length: " . filesize($outputFile));
flush();
readfile($outputFile);
unlink($outputFile);
exit;
?>
