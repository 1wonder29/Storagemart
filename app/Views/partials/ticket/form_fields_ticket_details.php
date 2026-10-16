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
                    <label class="btn btn-outline-primary btn-sm mb-0 ticket-photo-add">
                        <i class="fas fa-images mr-1"></i> Add photos
                        <input type="file" class="d-none ticket-photo-picker" accept="image/*" multiple>
                    </label>
                    <button type="button" class="btn btn-outline-secondary btn-sm ticket-photo-camera">
                        <i class="fas fa-camera mr-1"></i> Take a photo
                    </button>
                    <input type="file" class="d-none ticket-photo-camera-input" accept="image/*" capture="environment" tabindex="-1" aria-hidden="true">
                    <input type="file" name="problem_photos[]" class="d-none ticket-photo-files" accept="image/*" multiple tabindex="-1" aria-hidden="true">
                    <span class="small text-muted" id="ticketPhotoCount"></span>
                </div>
                <div class="ticket-photo-grid mt-2" id="ticketPhotoPreview"></div>
                <div class="small text-warning mt-1" id="ticketPhotoNote" hidden></div>
                <small class="form-text text-muted">A photo of the screen, error message or device helps IT fix it faster. Add them one at a time or several at once; click &times; on a photo to remove it. Max 10 MB each.</small>
            </div>
            <script>
            (function () {
                var box = document.currentScript.previousElementSibling;
                var form = box.closest('form');
                if (form) { form.enctype = 'multipart/form-data'; form.encoding = 'multipart/form-data'; }
                var MAX = 4, MAX_BYTES = 10 * 1024 * 1024;
                var picker = box.querySelector('.ticket-photo-picker');
                var cameraBtn = box.querySelector('.ticket-photo-camera');
                var cameraInput = box.querySelector('.ticket-photo-camera-input');
                var filesInput = box.querySelector('.ticket-photo-files');
                var preview = box.querySelector('#ticketPhotoPreview');
                var count = box.querySelector('#ticketPhotoCount');
                var note = box.querySelector('#ticketPhotoNote');
                var canKeep = typeof DataTransfer !== 'undefined';
                var photos = [];
                if (!canKeep) {
                    // Very old browser: each button sends its own photos (choosing again replaces them).
                    picker.name = 'problem_photos[]';
                    cameraInput.name = 'problem_photos_camera[]';
                    filesInput.removeAttribute('name');
                }
                function say(text) {
                    note.textContent = text || '';
                    note.hidden = !text;
                }
                function sync() {
                    if (canKeep) {
                        try {
                            var dt = new DataTransfer();
                            photos.forEach(function (f) { dt.items.add(f); });
                            filesInput.files = dt.files;
                        } catch (e) { /* keep what the browser has */ }
                    }
                    preview.textContent = '';
                    var shown = canKeep ? photos : [].concat(
                        Array.prototype.slice.call(picker.files || []), Array.prototype.slice.call(cameraInput.files || []));
                    shown.forEach(function (f, i) {
                        var tile = document.createElement('div');
                        tile.className = 'ticket-photo-tile';
                        var img = document.createElement('img');
                        img.alt = f.name || 'Photo ' + (i + 1);
                        if (window.URL && URL.createObjectURL) img.src = URL.createObjectURL(f);
                        tile.appendChild(img);
                        if (canKeep) {
                            var x = document.createElement('button');
                            x.type = 'button';
                            x.className = 'ticket-photo-remove';
                            x.setAttribute('aria-label', 'Remove photo ' + (i + 1));
                            x.title = 'Remove';
                            x.innerHTML = '&times;';
                            x.addEventListener('click', function () {
                                photos.splice(i, 1);
                                say('');
                                sync();
                            });
                            tile.appendChild(x);
                        }
                        preview.appendChild(tile);
                    });
                    count.textContent = shown.length ? shown.length + ' of ' + MAX + ' photos added' : '';
                    var full = canKeep && shown.length >= MAX;
                    picker.disabled = full;
                    cameraBtn.disabled = full;
                    picker.closest('label').classList.toggle('disabled', full);
                }
                function add(files) {
                    files = files.filter(Boolean);
                    if (!files.length) return;
                    var shrink = window.TMSShrinkImage
                        ? Promise.all(files.map(function (f) { return window.TMSShrinkImage(f); }))
                        : Promise.resolve(files);
                    shrink.then(function (small) {
                        var tooBig = 0, extra = 0;
                        small.forEach(function (f) {
                            if (f.size > MAX_BYTES) { tooBig++; return; }
                            if (photos.length >= MAX) { extra++; return; }
                            photos.push(f);
                        });
                        say(tooBig ? 'A photo was not added: each photo must be 10 MB or less.'
                            : (extra ? 'Only ' + MAX + ' photos can be added. Remove one to add another.' : ''));
                        sync();
                    });
                }
                picker.addEventListener('change', function () {
                    if (!canKeep) { sync(); return; }
                    add(Array.prototype.slice.call(picker.files || []));
                    picker.value = '';
                });
                cameraInput.addEventListener('change', function () {
                    if (!canKeep) { sync(); return; }
                    add(Array.prototype.slice.call(cameraInput.files || []));
                    cameraInput.value = '';
                });
                cameraBtn.addEventListener('click', function () {
                    if (!window.TMSCamera) { cameraInput.click(); return; }
                    window.TMSCamera.take({ fallbackInput: cameraInput }).then(function (file) {
                        if (file) add([file]);
                    });
                });
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
<?php /* Suggestions while typing the subject and description (what device, where, what happens). */ ?>
<script src="<?= htmlspecialchars($formBase) ?>/assets/js/ticket-writer.js?v=<?= (int) @filemtime(__DIR__ . '/../../../../public/assets/js/ticket-writer.js') ?>"></script>
