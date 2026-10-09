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

// What the user typed before a rule sent them back (TicketRules::rejectAndReturnPath); shown once.
$oldTicketInput = $_SESSION['ticket_old_input'] ?? [];
unset($_SESSION['ticket_old_input']);
$oldSubject = (string) ($oldTicketInput['subject'] ?? '');
$oldDescription = (string) ($oldTicketInput['concern_details'] ?? '');
$oldCategory = (string) ($oldTicketInput['category'] ?? '');
$oldPriority = (string) ($oldTicketInput['priority'] ?? 'Medium');
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
                       value="<?= htmlspecialchars($oldSubject) ?>"
                       placeholder="Brother MFC-T810W printer at the front desk shows Paper Jam">
                <small class="form-text text-muted">Brief description of the issue: device brand/model, where, and what happens.</small>
            </div>

            <div class="mb-3">
                <label for="concern_details" class="form-label">
                    <i class="fas fa-align-left"></i> Description <?= $descriptionRequired ? '<span class="text-danger">*</span>' : '' ?>
                </label>
                <textarea id="concern_details" name="concern_details" class="form-control form-control-lg" rows="6"
                          placeholder="Describe the issue in detail..." maxlength="1000"<?= $descriptionRequired ? ' required' : '' ?>><?= htmlspecialchars($oldDescription) ?></textarea>
                <small class="form-text text-muted">
                    Be specific: the device brand/model (e.g. Brother DCP-L2540DW), where it is (e.g. front desk,
                    2nd floor), what happens and any error message. Vague tickets such as "Printer is not working"
                    are not accepted. Maximum 1,000 characters.
                </small>
                <div class="ticket-specific-hint text-warning small mt-1" id="ticketSpecificHint" hidden></div>
            </div>

            <div class="mb-3 ticket-photos">
                <label class="form-label"><i class="fas fa-camera"></i> Photos of the problem <span class="text-muted small">(optional, up to 4)</span></label>
                <div class="d-flex flex-wrap align-items-center" style="gap:8px">
                    <label class="btn btn-outline-primary btn-sm mb-0">
                        <i class="fas fa-images mr-1"></i> Choose photos
                        <input type="file" name="problem_photos[]" class="d-none ticket-photo-input" accept="image/*" multiple>
                    </label>
                    <label class="btn btn-outline-secondary btn-sm mb-0">
                        <i class="fas fa-camera mr-1"></i> Take a photo
                        <input type="file" name="problem_photos_camera[]" class="d-none ticket-photo-input" accept="image/*" capture="environment">
                    </label>
                    <span class="small text-muted" id="ticketPhotoCount"></span>
                </div>
                <div class="d-flex flex-wrap mt-2" style="gap:6px" id="ticketPhotoPreview"></div>
                <small class="form-text text-muted">A photo of the screen, error message or device helps IT fix it faster. Max 10 MB each.</small>
            </div>
            <script>
            (function () {
                var box = document.currentScript.previousElementSibling;
                var form = box.closest('form');
                if (form) { form.enctype = 'multipart/form-data'; form.encoding = 'multipart/form-data'; }
                var inputs = box.querySelectorAll('.ticket-photo-input');
                var preview = box.querySelector('#ticketPhotoPreview');
                var count = box.querySelector('#ticketPhotoCount');
                function refresh() {
                    preview.textContent = '';
                    var files = [];
                    inputs.forEach(function (input) { Array.prototype.forEach.call(input.files || [], function (f) { files.push(f); }); });
                    var tooBig = files.some(function (f) { return f.size > 10 * 1024 * 1024; });
                    if (files.length > 4 || tooBig) {
                        alert(tooBig ? 'Each photo must be 10 MB or less.' : 'You can add up to 4 photos.');
                        inputs.forEach(function (input) { input.value = ''; });
                        files = [];
                    }
                    files.forEach(function (f) {
                        var img = document.createElement('img');
                        img.alt = f.name;
                        img.style.cssText = 'width:72px;height:72px;object-fit:cover;border-radius:8px;border:1px solid #dee2e6';
                        if (window.URL && URL.createObjectURL) img.src = URL.createObjectURL(f);
                        preview.appendChild(img);
                    });
                    count.textContent = files.length ? files.length + ' photo' + (files.length === 1 ? '' : 's') + ' added' : '';
                }
                // Shrink phone photos before the form is sent (keeps uploads small on the server).
                function shrinkInput(input) {
                    var files = Array.prototype.slice.call(input.files || []);
                    if (!files.length || !window.TMSShrinkImage || typeof DataTransfer === 'undefined') { refresh(); return; }
                    Promise.all(files.map(function (f) { return window.TMSShrinkImage(f); })).then(function (small) {
                        try {
                            var dt = new DataTransfer();
                            small.forEach(function (f) { dt.items.add(f); });
                            input.files = dt.files;
                        } catch (e) { /* keep the originals */ }
                        refresh();
                    });
                }
                inputs.forEach(function (input) { input.addEventListener('change', function () { shrinkInput(input); }); });
            })();
            </script>

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
                            <option value="<?= htmlspecialchars($categoryOption) ?>"<?= $categoryOption === $oldCategory ? ' selected' : '' ?>><?= htmlspecialchars($categoryOption) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="priority" class="form-label">
                        <i class="fas fa-exclamation-triangle"></i> Priority
                    </label>
                    <select id="priority" name="priority" class="form-control form-control-lg">
                        <?php foreach (TicketFormFields::PRIORITIES as $priorityOption): ?>
                            <option value="<?= $priorityOption ?>"<?= $priorityOption === $oldPriority ? ' selected' : '' ?>><?= $priorityOption ?></option>
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
<script src="<?= htmlspecialchars($formBase) ?>/assets/js/ticket-form.js?v=<?= (int) @filemtime(__DIR__ . '/../../../../public/assets/js/ticket-form.js') ?>"></script>
<script src="<?= htmlspecialchars($formBase) ?>/assets/js/ticket-guide.js?v=<?= (int) @filemtime(__DIR__ . '/../../../../public/assets/js/ticket-guide.js') ?>"></script>
