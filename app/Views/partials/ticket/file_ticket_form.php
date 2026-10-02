<?php
$formAction = $formAction ?? '';
$cancelUrl = $cancelUrl ?? '#';
$inventory = $inventory ?? [];
$csrf_token = $csrf_token ?? '';
$submitLabel = $submitLabel ?? 'Submit Ticket';
$showAssetSection = !empty($showAssetSection);
?>
<form method="POST" action="<?= htmlspecialchars($formAction) ?>">
    <input type="hidden" name="branch_id" value="<?= htmlspecialchars((string) ($inventory['branch_id'] ?? '')) ?>">
    <input type="hidden" name="inventory_id" value="<?= htmlspecialchars((string) ($inventory['inventory_id'] ?? '0')) ?>">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>">

    <div class="form-section">
        <div class="form-section-title">
            <i class="fas fa-user"></i> Employee Details
        </div>
        <div class="row readonly-grid">
            <div class="col-md-6">
                <label for="employee_id" class="form-label">Employee ID</label>
                <input type="text" class="form-control form-control-lg" id="employee_id" name="employee_id"
                       value="<?= htmlspecialchars((string) ($inventory['employee_id'] ?? '')) ?>" readonly>
            </div>
            <div class="col-md-6">
                <label for="fullname" class="form-label">Full Name</label>
                <input type="text" class="form-control form-control-lg" id="fullname" name="fullname"
                       value="<?= htmlspecialchars((string) ($inventory['fullname'] ?? '')) ?>" readonly>
            </div>
            <div class="col-md-6">
                <label for="department" class="form-label">Department</label>
                <input type="text" class="form-control form-control-lg" id="department" name="department"
                       value="<?= htmlspecialchars((string) ($inventory['department'] ?? '')) ?>" readonly>
            </div>
            <div class="col-md-6">
                <label for="branchName" class="form-label">Branch</label>
                <input type="text" class="form-control form-control-lg" id="branchName" name="branchName"
                       value="<?= htmlspecialchars((string) ($inventory['branchName'] ?? '')) ?>" readonly>
            </div>
        </div>
    </div>

    <?php if ($showAssetSection): ?>
    <div class="form-section">
        <div class="form-section-title">
            <i class="fas fa-archive"></i> Asset Details
        </div>
        <div class="row readonly-grid">
            <div class="col-md-6">
                <label for="assetNumber" class="form-label">Asset Number</label>
                <input type="text" class="form-control form-control-lg" id="assetNumber" name="assetNumber"
                       value="<?= htmlspecialchars((string) ($inventory['assetNumber'] ?? '')) ?>" readonly>
            </div>
            <div class="col-md-6">
                <label for="groupName" class="form-label">Model / Group</label>
                <input type="text" class="form-control form-control-lg" id="groupName" name="groupName"
                       value="<?= htmlspecialchars((string) ($inventory['groupName'] ?? '')) ?>" readonly>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php
    $extendedCategories = false;
    require __DIR__ . '/form_fields_ticket_details.php';
    ?>
</form>
