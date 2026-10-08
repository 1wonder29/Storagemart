<?php
// Retired: this script let any signed-in user download any employee's form. The form is now
// built at /admin/employees/accountability/{id} (Admin) and /hr/employees/accountability/{id} (HR),
// which check the role and let you pick columns, preview, and download PDF or Word.
$employeeId = (int) ($_GET['employee_id'] ?? $_POST['employee_id'] ?? 0);
header('Location: /admin/employees/accountability/' . $employeeId, true, 302);
exit;
