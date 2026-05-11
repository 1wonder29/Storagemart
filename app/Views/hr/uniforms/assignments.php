<?php
$base = rtrim(BASE_URL, '/');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Uniform Assignments</title>
</head>
<body>
    <h3>Active Assignments</h3>
    <?php if (empty($assignments)): ?>
        <p>No assignments found for this uniform.</p>
    <?php else: ?>
        <table border="1" cellpadding="6" cellspacing="0">
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Date Issued</th>
                    <th>Quantity</th>
                    <th>Date Returned</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($assignments as $a): ?>
                    <tr>
                        <td><?= htmlspecialchars($a['employee_name'] ?? ($a['employee_id'] ?? '')) ?></td>
                        <td><?= htmlspecialchars($a['date_issued'] ?? '') ?></td>
                        <td><?= (int)($a['quantity_issued'] ?? 0) ?></td>
                        <td><?= htmlspecialchars($a['date_returned'] ?? '') ?></td>
                        <td>
                            <?php if (empty($a['date_returned'])): ?>
                                <a href="<?= htmlspecialchars($base) ?>/hr/uniforms/return_confirm/<?= $a['assignment_id'] ?>">Return</a>
                            <?php else: ?>
                                Returned
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <p><a href="<?= htmlspecialchars($base) ?>/hr/uniforms">Back to uniforms</a></p>
</body>
</html>
