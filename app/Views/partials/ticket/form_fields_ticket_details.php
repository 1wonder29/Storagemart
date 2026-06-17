<?php
$extendedCategories = !empty($extendedCategories);
$submitLabel = $submitLabel ?? 'Create Ticket';
$cancelUrl = $cancelUrl ?? '#';
$descriptionRequired = !isset($descriptionRequired) || $descriptionRequired;
?>
<div class="form-section">
    <div class="form-section-title">
        <i class="fas fa-clipboard-list"></i> Ticket Details
    </div>

    <div class="priority-legend">
        <strong>Priority guide</strong>
        <ul class="mb-0">
            <li><strong>Low</strong> — Non-urgent; can wait for regular maintenance.</li>
            <li><strong>Medium</strong> — Should be addressed within a reasonable timeframe.</li>
            <li><strong>High</strong> — Urgent; requires immediate attention.</li>
        </ul>
    </div>

    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="category" class="form-label">
                <i class="fas fa-tag"></i> Category
            </label>
            <select id="category" name="category" class="form-control form-control-lg">
                <option value="">-- Select Category --</option>
                <?php if ($extendedCategories): ?>
                    <option value="Hardware">Hardware</option>
                    <option value="Software">Software</option>
                    <option value="Network">Network</option>
                    <option value="Facility">Facility</option>
                    <option value="Other">Other</option>
                <?php else: ?>
                    <option value="Hardware">Hardware</option>
                    <option value="Software">Software</option>
                    <option value="Network">Network</option>
                <?php endif; ?>
            </select>
        </div>
        <div class="col-md-6 mb-3">
            <label for="priority" class="form-label">
                <i class="fas fa-exclamation-triangle"></i> Priority
            </label>
            <select id="priority" name="priority" class="form-control form-control-lg">
                <option value="Low">Low</option>
                <option value="Medium" selected>Medium</option>
                <option value="High">High</option>
            </select>
        </div>
    </div>

    <div class="mb-0">
        <label for="concern_details" class="form-label">
            <i class="fas fa-align-left"></i> Ticket Description <?= $descriptionRequired ? '<span class="text-danger">*</span>' : '' ?>
        </label>
        <textarea id="concern_details" name="concern_details" class="form-control form-control-lg" rows="6"
                  placeholder="Describe the issue or request..." maxlength="1000"<?= $descriptionRequired ? ' required' : '' ?>></textarea>
        <small class="form-text text-muted">Maximum 1,000 characters.</small>
    </div>
</div>

<div class="ticket-form-actions">
    <button type="submit" class="btn btn-primary">
        <i class="fas fa-check"></i> <?= htmlspecialchars($submitLabel) ?>
    </button>
    <a href="<?= htmlspecialchars($cancelUrl) ?>" class="btn btn-secondary">
        <i class="fas fa-times"></i> Cancel
    </a>
</div>
