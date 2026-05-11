<?php
$base = rtrim(BASE_URL, '/');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Confirm Return</title>
</head>
<body>
    <h3>Confirm Return</h3>
    <?php if (empty($assignment)): ?>
        <p>Assignment not found.</p>
        <p><a href="<?= htmlspecialchars($base) ?>/hr/uniforms">Back</a></p>
    <?php else: ?>
        <p>Employee: <?= htmlspecialchars($assignment['employee_name'] ?? '') ?></p>
        <p>Quantity: <?= (int)($assignment['quantity_issued'] ?? 0) ?></p>
        <p>Date Issued: <?= htmlspecialchars($assignment['date_issued'] ?? '') ?></p>

        <form method="post" action="<?= htmlspecialchars($base) ?>/hr/uniforms/return/<?= $assignment['assignment_id'] ?>">
            <button type="submit">Confirm Return</button>
            <a href="<?= htmlspecialchars($base) ?>/hr/uniforms/assignments/<?= $assignment['uniform_id'] ?>">Cancel</a>
        </form>
    <?php endif; ?>
</body>
</html>
