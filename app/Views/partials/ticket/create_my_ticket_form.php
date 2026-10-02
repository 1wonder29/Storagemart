<?php
$base = rtrim(BASE_URL, '/');
$formAction = $formAction ?? '';
$cancelUrl = $cancelUrl ?? '';
$myAssets = $myAssets ?? [];
$profile = $profile ?? [];
$csrf_token = $csrf_token ?? '';
$hasAssets = !empty($myAssets);

$fullName = trim(
    ($profile['lastname'] ?? '') . ', ' . ($profile['firstname'] ?? '') . ' ' . ($profile['middlename'] ?? '')
);
?>
<form method="POST" action="<?= htmlspecialchars($formAction) ?>">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>">
    <input type="hidden" name="employee_id" value="<?= (int) ($profile['employee_id'] ?? 0) ?>">
    <input type="hidden" name="branch_id" id="branch_id" value="<?= (int) ($profile['branch_id'] ?? 0) ?>">

    <div class="form-section">
        <div class="form-section-title">
            <i class="fas fa-user"></i> Filed For
        </div>
        <div class="mb-3">
            <label class="form-label"><i class="fas fa-id-badge"></i> Employee</label>
            <input type="text" class="form-control form-control-lg" value="<?= htmlspecialchars($fullName) ?>" readonly>
            <small class="form-text text-muted">This ticket will be filed under your employee record.</small>
        </div>
        <div class="row readonly-grid">
            <div class="col-md-6">
                <label class="form-label">Department</label>
                <input type="text" class="form-control form-control-lg" value="<?= htmlspecialchars((string) ($profile['department'] ?? '')) ?>" readonly>
            </div>
            <div class="col-md-6">
                <label class="form-label">Branch</label>
                <input type="text" id="branchNameDisplay" class="form-control form-control-lg" value="<?= htmlspecialchars((string) ($profile['branchName'] ?? '')) ?>" readonly>
            </div>
        </div>
    </div>

    <?php if (!$hasAssets): ?>
        <div class="alert alert-warning alert-modern">
            <i class="fas fa-exclamation-triangle mr-1"></i>
            You need at least one assigned asset before filing a personal ticket.
        </div>
        <div class="ticket-form-actions">
            <a href="<?= htmlspecialchars($cancelUrl) ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Tickets
            </a>
        </div>
    <?php else: ?>
        <div class="form-section">
            <div class="form-section-title">
                <i class="fas fa-archive"></i> Asset &amp; Issue
            </div>
            <div class="mb-3">
                <label for="inventory_id" class="form-label">
                    <i class="fas fa-archive"></i> Asset <span class="text-danger">*</span>
                </label>
                <select id="inventory_id" name="inventory_id" class="form-control form-control-lg" required>
                    <option value="">-- Select Asset --</option>
                    <?php foreach ($myAssets as $asset): ?>
                        <option value="<?= (int) ($asset['inventory_id'] ?? 0) ?>"
                                data-branch-id="<?= (int) ($asset['branch_id'] ?? 0) ?>"
                                data-branch-name="<?= htmlspecialchars((string) ($asset['branchName'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                            <?= htmlspecialchars((string) ($asset['assetNumber'] ?? '')) ?>
                            — <?= htmlspecialchars((string) ($asset['groupName'] ?? '')) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <?php
        $submitLabel = 'Create My Ticket';
        $extendedCategories = false;
        require __DIR__ . '/form_fields_ticket_details.php';
        ?>
    <?php endif; ?>
</form>

<?php if ($hasAssets): ?>
<script>
(function () {
    var assetSelect = document.getElementById('inventory_id');
    var branchInput = document.getElementById('branch_id');
    var branchDisplay = document.getElementById('branchNameDisplay');
    if (!assetSelect || !branchInput) return;

    assetSelect.addEventListener('change', function () {
        var option = assetSelect.options[assetSelect.selectedIndex];
        if (!option || !option.value) return;
        branchInput.value = option.getAttribute('data-branch-id') || '';
        if (branchDisplay) {
            branchDisplay.value = option.getAttribute('data-branch-name') || '';
        }
    });
})();
</script>
<?php endif; ?>
