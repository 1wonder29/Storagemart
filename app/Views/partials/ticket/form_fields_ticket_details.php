<?php
require_once __DIR__ . '/../../../Helpers/TicketFormFields.php';

$submitLabel = $submitLabel ?? 'Create Ticket';
$cancelUrl = $cancelUrl ?? '#';
$descriptionRequired = !isset($descriptionRequired) || $descriptionRequired;
$formBase = rtrim(defined('BASE_URL') ? BASE_URL : '', '/');

// Which category list to show first. The browser re-evaluates this whenever the
// selected branch changes (see ticket-form.js), so this only sets the initial state.
$branchNameHint = (string) ($inventory['branchName'] ?? $profile['branchName'] ?? '');
$branchCodeHint = (string) ($inventory['branchCode'] ?? $profile['branchCode'] ?? '');
$branchIdHint = (int) ($inventory['branch_id'] ?? $profile['branch_id'] ?? 0);
$isHeadOfficeForm = TicketFormFields::isHeadOffice($branchCodeHint, $branchNameHint)
    || ($branchNameHint === '' && TicketFormFields::isHeadOfficeBranchId($branchIdHint));
$categoryOptions = TicketFormFields::categoriesFor($isHeadOfficeForm);
?>
<div class="ticket-issue-layout">
    <div class="ticket-issue-main">
        <div class="form-section">
            <div class="form-section-title">
                <i class="fas fa-clipboard-list"></i> The Issue
            </div>
            <p class="required-note">Fields marked with <span class="text-danger">*</span> are required.</p>

            <div class="mb-3">
                <label for="subject" class="form-label">
                    <i class="fas fa-heading"></i> Subject <span class="text-danger">*</span>
                </label>
                <input type="text" id="subject" name="subject" class="form-control form-control-lg" maxlength="255" required
                       placeholder="Brother MFC-T810W Printer is not working">
                <small class="form-text text-muted">Brief description of the issue.</small>
            </div>

            <div class="mb-3">
                <label for="concern_details" class="form-label">
                    <i class="fas fa-align-left"></i> Description <?= $descriptionRequired ? '<span class="text-danger">*</span>' : '' ?>
                </label>
                <textarea id="concern_details" name="concern_details" class="form-control form-control-lg" rows="6"
                          placeholder="Describe the issue in detail..." maxlength="1000"<?= $descriptionRequired ? ' required' : '' ?>></textarea>
                <small class="form-text text-muted">
                    Please describe the issue in detail. Include any error messages, steps to reproduce the issue,
                    and other relevant information. Maximum 1,000 characters.
                </small>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="category" class="form-label">
                        <i class="fas fa-tag"></i> Category
                    </label>
                    <select id="category" name="category" class="form-control form-control-lg"
                            data-head-office="<?= htmlspecialchars(json_encode(TicketFormFields::HEAD_OFFICE_CATEGORIES), ENT_QUOTES, 'UTF-8') ?>"
                            data-branch="<?= htmlspecialchars(json_encode(TicketFormFields::BRANCH_CATEGORIES), ENT_QUOTES, 'UTF-8') ?>">
                        <option value="">-- Select Category --</option>
                        <?php foreach ($categoryOptions as $categoryOption): ?>
                            <option value="<?= htmlspecialchars($categoryOption) ?>"><?= htmlspecialchars($categoryOption) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="priority" class="form-label">
                        <i class="fas fa-exclamation-triangle"></i> Priority
                    </label>
                    <select id="priority" name="priority" class="form-control form-control-lg">
                        <?php foreach (TicketFormFields::PRIORITIES as $priorityOption): ?>
                            <option value="<?= $priorityOption ?>"<?= $priorityOption === 'Medium' ? ' selected' : '' ?>><?= $priorityOption ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="priority-legend mb-0">
                <strong>Priority guide</strong>
                <ul class="mb-0">
                    <li><strong>Low</strong> — Non-urgent; can wait for regular maintenance.</li>
                    <li><strong>Medium</strong> — Standard-priority issue.</li>
                    <li><strong>High</strong> — Urgent; requires immediate attention.</li>
                    <li><strong>Critical</strong> — Major outage affecting critical operations or multiple departments; needs an immediate response.</li>
                </ul>
            </div>
        </div>
    </div>

    <aside class="ticket-live-preview" aria-label="Live preview of your ticket">
        <div class="live-preview-heading"><i class="fas fa-eye"></i> Live Preview</div>
        <div class="live-preview-card">
            <span class="live-preview-badge">New</span>
            <h5 class="live-preview-subject is-placeholder" id="previewSubject"
                data-placeholder="Your ticket subject will appear here">Your ticket subject will appear here</h5>
            <p class="live-preview-description is-placeholder" id="previewDescription"
               data-placeholder="A short preview of your description will appear here">A short preview of your description will appear here</p>
            <ul class="live-preview-meta">
                <li><span>Status:</span> <strong>Open</strong></li>
                <li><span>Priority:</span> <strong id="previewPriority">Medium</strong></li>
                <li id="previewCategoryRow" hidden><span>Category:</span> <strong id="previewCategory"></strong></li>
            </ul>
        </div>
    </aside>
</div>

<div class="ticket-form-actions">
    <button type="submit" class="btn btn-primary">
        <i class="fas fa-check"></i> <?= htmlspecialchars($submitLabel) ?>
    </button>
    <a href="<?= htmlspecialchars($cancelUrl) ?>" class="btn btn-secondary">
        <i class="fas fa-times"></i> Cancel
    </a>
</div>
<script src="<?= htmlspecialchars($formBase) ?>/assets/js/ticket-form.js"></script>
