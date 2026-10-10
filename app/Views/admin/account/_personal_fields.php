<?php
// Personal details block for Add User / Edit User. Needs $personalReady (columns exist) and $personal (values).
if (empty($personalReady)) {
    return;
}
$personal = is_array($personal ?? null) ? $personal : [];
$pv = static fn(string $key): string => htmlspecialchars((string) ($personal[$key] ?? ''));
?>
<input type="hidden" name="personal_form" value="1">
<div class="mt-2 mb-2 pt-3 border-top">
    <h6 class="font-weight-bold text-primary mb-1"><i class="fas fa-id-card mr-1"></i> Personal details</h6>
    <p class="small text-muted mb-0">Optional. The birthday is used for the birthday greetings (other users only see the day and month). These show on the employee's profile and in HR's employee page.</p>
</div>
<div class="row form-row-gap">
    <div class="col-md-6">
        <label for="birthdate" class="form-label">Birthday</label>
        <input type="date" class="form-control" id="birthdate" name="birthdate" value="<?= $pv('birthdate') ?>" min="1930-01-01" max="<?= date('Y-m-d') ?>">
    </div>
    <div class="col-md-6">
        <label for="date_hired" class="form-label">Start date (date hired)</label>
        <input type="date" class="form-control" id="date_hired" name="date_hired" value="<?= $pv('date_hired') ?>" min="1990-01-01" max="<?= date('Y-m-d', strtotime('+1 year')) ?>">
    </div>
</div>
<div class="row form-row-gap">
    <div class="col-md-6">
        <label for="gender" class="form-label">Gender</label>
        <select id="gender" name="gender" class="form-control">
            <option value="">-- Not set --</option>
            <?php foreach (EmployeePersonal::GENDERS as $g): ?>
                <option value="<?= htmlspecialchars($g) ?>"<?= ($personal['gender'] ?? '') === $g ? ' selected' : '' ?>><?= htmlspecialchars($g) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-6">
        <label for="phone" class="form-label">Phone number</label>
        <input type="tel" class="form-control" id="phone" name="phone" value="<?= $pv('phone') ?>" placeholder="e.g. 0917 123 4567" maxlength="20">
    </div>
</div>
