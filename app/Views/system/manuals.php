<?php
$base = rtrim(BASE_URL, '/');
$isIt = ($role ?? '') === 'IT';
$manuals = $manuals ?? [];
$groupNames = $groupNames ?? TicketManuals::GROUPS;
$source = $old ?: ($editing ?: []);
$formId = (int) ($old['manual_id'] ?? ($editing['manual_id'] ?? 0));
$selectedCategories = isset($old['categories'])
    ? (array) $old['categories']
    : array_filter(array_map('trim', explode(',', (string) ($editing['categories'] ?? ''))));
$h = static function ($value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};
// A new guide starts with the IT Department's standard Action text.
$actionValue = array_key_exists('action_text', $source) ? (string) $source['action_text'] : ($formId ? '' : ManualPresentation::ACTION_DEFAULT);
$existingPhotos = [];
if ($editing && !$old) {
    foreach (ManualPresentation::stepPhotos($editing) as $index => $file) {
        if (TicketManuals::stepPhotoPath($file) !== null) {
            $existingPhotos[$index] = $base . '/manuals/photo?id=' . (int) $editing['manual_id'] . '&step=' . $index;
        }
    }
}
$byGroup = [];
foreach ($manuals as $manual) {
    $byGroup[TicketManuals::groupOf($manual)][] = $manual;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Storage Mart | Troubleshooting Manuals</title>
    <link href="<?= $h($base) ?>/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900" rel="stylesheet">
    <link href="<?= $h($base) ?>/assets/css/storagemart.css" rel="stylesheet">
    <link href="<?= $h($base) ?>/assets/css/admin-users.css" rel="stylesheet">
    <link rel="icon" href="<?= $h($base) ?>/assets/img/sm_favicon.png" type="image/x-icon">
    <?php if ($isIt) { require_once __DIR__ . '/../partials/it/theme_head.php'; } ?>
    <style>
        .manual-cats { display:grid; grid-template-columns:repeat(auto-fill, minmax(230px, 1fr)); gap:4px 16px; }
        .manual-chip { display:inline-block; padding:1px 8px; margin:0 4px 4px 0; border-radius:999px; font-size:.72rem; font-weight:700; background:#eef2ff; color:#4e73df; }
        .manual-chip.kw { background:#f3f4f6; color:#4b5563; font-weight:600; }
        .manual-chip.it { background:#1F3A93; color:#fff; }
        .manual-steps { margin:0; padding-left:18px; font-size:.82rem; color:#4b5563; }
        .manual-default { background:#fff7ed; color:#c2410c; }
        .manual-match { border-left:4px solid #f26522; padding:8px 12px; background:#fffaf5; border-radius:6px; margin-bottom:8px; }
        .slide-part { border-left:4px solid #F26522; padding:2px 0 2px 12px; margin-bottom:14px; }
        .slide-part > label { font-weight:700; color:#1F3A93; }
        .syntax { font-size:.78rem; background:#f8f9fc; border:1px dashed #d1d5e5; border-radius:8px; padding:8px 10px; margin-top:6px; }
        .syntax code { color:#c2410c; }
        .step-photo-row { display:flex; align-items:center; gap:10px; padding:6px 0; border-bottom:1px dashed #e3e6f0; font-size:.8rem; }
        .step-photo-row .n { flex:0 0 auto; font-weight:800; color:#F26522; min-width:58px; }
        .step-photo-row .t { flex:1; min-width:0; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; color:#4b5563; }
        .step-photo-row img { width:64px; height:40px; object-fit:cover; border:1px solid #1F3A93; border-radius:4px; }
        .step-photo-row input[type=file] { max-width:190px; font-size:.75rem; }
        .group-title { font-size:.8rem; font-weight:800; color:#1F3A93; text-transform:uppercase; letter-spacing:.04em; background:#f8f9fc; padding:8px 16px; border-bottom:1px solid #e3e6f0; }
        .guide-details summary { cursor:pointer; font-size:.78rem; color:#4e73df; }
        html.it-dark .syntax, html.it-dark .group-title { background:#252a3a; border-color:#343a4f; color:#cbd5e1; }
        html.it-dark .step-photo-row .t { color:#cbd5e1; }
    </style>
</head>

<body id="page-top">

    <div id="wrapper">
        <?php
        if ($isIt) {
            $activePage = 'manuals';
            require_once __DIR__ . '/../partials/it/sidebar_topbar.php';
        } else {
            $activePage = 'system';
            $systemSubPage = 'manuals';
            require_once __DIR__ . '/../partials/admin/sidebar_topbar.php';
        }
        ?>

        <div class="container-fluid admin-users-page">

            <div class="page-hero hero-accounts">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <h1><i class="fas fa-book-medical mr-2"></i>Troubleshooting Manuals</h1>
                        <p>Every guide becomes slides and a PowerPoint in the IT Department's manual design (logo, Introduction,
                            Main Issue, Factors, Action, Solution, STEP pages, Note, Thank You). After a ticket is filed,
                            the best-matching guide is sent in the ticket chat.</p>
                        <a href="<?= $h($base) ?>/manuals" target="_blank" rel="noopener" class="btn btn-light btn-sm mt-1">
                            <i class="fas fa-th-large mr-1"></i>Open the manuals library</a>
                    </div>
                    <div class="col-lg-4">
                        <div class="hero-stat mt-3 mt-lg-0">
                            <div class="stat-value"><?= count($manuals) ?></div>
                            <div class="stat-label">Guides</div>
                        </div>
                    </div>
                </div>
            </div>

            <?php if (!empty($setupMissing)): ?>
                <div class="alert alert-warning"><i class="fas fa-database mr-1"></i>
                    The database update has not been run yet. Import the update SQL in phpMyAdmin first.</div>
            <?php endif; ?>

            <div class="row">
                <div class="col-xl-6">
                    <div class="card form-card shadow mb-4" id="manual-form">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="fas <?= $formId ? 'fa-edit' : 'fa-plus' ?> mr-1"></i><?= $formId ? 'Edit guide' : 'Add a guide' ?>
                            </h6>
                            <div>
                                <?php if ($formId): ?>
                                    <a href="<?= $h($base) ?>/manuals/view?id=<?= $formId ?>" target="_blank" rel="noopener" class="btn btn-sm btn-primary">
                                        <i class="fas fa-desktop mr-1"></i>Preview slides</a>
                                    <a href="<?= $h($base . $home) ?>" class="btn btn-sm btn-light">New guide</a>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="<?= $h($base . $home) ?>" enctype="multipart/form-data">
                                <input type="hidden" name="csrf_token" value="<?= $h($csrf_token ?? '') ?>">
                                <input type="hidden" name="action" value="save">
                                <input type="hidden" name="manual_id" value="<?= $formId ?>">

                                <div class="form-row">
                                    <div class="form-group col-md-7">
                                        <label for="m_title">Title <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="m_title" name="title" maxlength="150" required
                                               value="<?= $h($source['title'] ?? '') ?>" placeholder="e.g. Printer Paper Jam (Brother DCP-L2540DW)">
                                    </div>
                                    <div class="form-group col-md-5">
                                        <label for="m_group">Group <small class="text-muted">(library folder)</small></label>
                                        <input type="text" class="form-control" id="m_group" name="manual_group" maxlength="100" list="m_groups"
                                               value="<?= $h($source['manual_group'] ?? '') ?>" placeholder="Printer Issues">
                                        <datalist id="m_groups">
                                            <?php foreach ($groupNames as $groupName): ?><option value="<?= $h($groupName) ?>"><?php endforeach; ?>
                                        </datalist>
                                    </div>
                                </div>

                                <p class="small text-muted mb-2"><i class="fas fa-magic mr-1"></i>Each box below is one page of the slides.
                                    Leave a box empty to skip that page.</p>

                                <div class="slide-part form-group">
                                    <label for="m_intro">Introduction</label>
                                    <textarea class="form-control" id="m_intro" name="intro" rows="3"
                                              placeholder="In this procedure the facilitator can..."><?= $h($source['intro'] ?? '') ?></textarea>
                                </div>
                                <div class="slide-part form-group">
                                    <label for="m_issue">Main Issue</label>
                                    <textarea class="form-control" id="m_issue" name="main_issue" rows="2"
                                              placeholder="What the user sees, e.g. The printer stops and the LCD shows a jam message."><?= $h($source['main_issue'] ?? '') ?></textarea>
                                </div>
                                <div class="slide-part form-group">
                                    <label for="m_factors">Factors <small class="text-muted">(one per line)</small></label>
                                    <textarea class="form-control" id="m_factors" name="factors" rows="4"
                                              placeholder="Paper tray is overfilled&#10;Curled or damp paper"><?= $h($source['factors'] ?? '') ?></textarea>
                                </div>
                                <div class="slide-part form-group">
                                    <label for="m_action">Action</label>
                                    <textarea class="form-control" id="m_action" name="action_text" rows="2"><?= $h($actionValue) ?></textarea>
                                </div>
                                <div class="slide-part form-group">
                                    <label for="m_solution">Solution</label>
                                    <textarea class="form-control" id="m_solution" name="solution" rows="3"
                                              placeholder="The facilitator can... If it still fails, report it to IT."><?= $h($source['solution'] ?? '') ?></textarea>
                                </div>
                                <div class="slide-part form-group">
                                    <label for="m_steps">Steps <small class="text-muted">(one STEP page per line)</small></label>
                                    <textarea class="form-control" id="m_steps" name="steps" rows="8"
                                              placeholder="Pull the paper tray completely out of the printer.&#10;Slowly pull out the jammed paper."><?= $h($source['steps'] ?? '') ?></textarea>
                                    <div class="syntax">
                                        <code># 1. Jam Tray1 (Paper Tray)</code> &rarr; a section page (STEP numbers start again at 1)<br>
                                        <code>## What is LOS? :: LOS means...</code> &rarr; a page with its own heading<br>
                                        <code> || </code> starts a new line, and a line starting with <code>- </code> is a bullet.
                                    </div>
                                </div>
                                <div class="slide-part form-group">
                                    <label class="d-block">Step photos <small class="text-muted">(optional, JPG or PNG, shown on the left of the STEP page)</small></label>
                                    <div id="stepPhotos" data-existing="<?= $h(json_encode((object) $existingPhotos)) ?>"></div>
                                    <div class="small text-muted" id="stepPhotosEmpty">Type the steps above first.</div>
                                </div>
                                <div class="slide-part form-group">
                                    <label for="m_note">Note</label>
                                    <textarea class="form-control" id="m_note" name="note" rows="3"
                                              placeholder="Things to avoid, when to call IT, sources."><?= $h($source['note'] ?? '') ?></textarea>
                                </div>

                                <div class="form-group">
                                    <label for="m_desc">Short description <small class="text-muted">(for lists; the Main Issue is used when empty)</small></label>
                                    <input type="text" class="form-control" id="m_desc" name="description" maxlength="255"
                                           value="<?= $h($source['description'] ?? '') ?>">
                                </div>
                                <div class="form-group">
                                    <label for="m_file">Your own PowerPoint or PDF <small class="text-muted">(optional, max 25 MB)</small></label>
                                    <input type="file" class="form-control-file" id="m_file" name="manual_file" accept=".pptx,.ppt,.pdf">
                                    <small class="form-text text-muted">For example from Canva: Share &gt; Download &gt; PDF. A PDF is shown under the slides.</small>
                                    <?php if (!empty($editing['file_name'])): ?>
                                        <div class="custom-control custom-checkbox mt-1">
                                            <input type="checkbox" class="custom-control-input" id="m_remove_file" name="remove_file" value="1">
                                            <label class="custom-control-label small" for="m_remove_file">Remove the current file (<?= $h($editing['file_name']) ?>)</label>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="form-group">
                                    <label for="m_url">Online link <small class="text-muted">(optional; Canva / Google Drive links may ask for a login)</small></label>
                                    <input type="url" class="form-control" id="m_url" name="url" maxlength="500"
                                           value="<?= $h($source['url'] ?? '') ?>" placeholder="https://">
                                </div>
                                <div class="form-group">
                                    <label class="d-block">Send for these ticket categories</label>
                                    <div class="manual-cats">
                                        <?php foreach ($allCategories as $i => $category): ?>
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" id="m_cat_<?= $i ?>" name="categories[]"
                                                       value="<?= $h($category) ?>" <?= in_array($category, $selectedCategories, true) ? 'checked' : '' ?>>
                                                <label class="custom-control-label small" for="m_cat_<?= $i ?>"><?= $h($category) ?></label>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="m_kw">Keywords <small class="text-muted">(comma separated; found in the subject or description)</small></label>
                                    <input type="text" class="form-control" id="m_kw" name="keywords" maxlength="500"
                                           value="<?= $h($source['keywords'] ?? '') ?>" placeholder="paper jam, jammed, stuck paper">
                                </div>
                                <div class="custom-control custom-switch mb-2">
                                    <input type="checkbox" class="custom-control-input" id="m_it_only" name="it_only" value="1"
                                        <?= !empty($source['it_only']) ? 'checked' : '' ?>>
                                    <label class="custom-control-label" for="m_it_only">IT only (setup / maintenance guide: only IT and Admin can open it, never sent to requesters)</label>
                                </div>
                                <?php if (!$isIt): ?>
                                    <div class="custom-control custom-switch mb-3">
                                        <input type="checkbox" class="custom-control-input" id="m_default" name="is_default" value="1"
                                            <?= !empty($source['is_default']) ? 'checked' : '' ?>>
                                        <label class="custom-control-label" for="m_default">Default guide (sent when no other guide matches)</label>
                                    </div>
                                <?php endif; ?>
                                <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i>Save guide</button>
                            </form>
                        </div>
                    </div>

                    <div class="card form-card shadow mb-4">
                        <div class="card-header">
                            <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-vial mr-1"></i>Test: which guide is sent?</h6>
                        </div>
                        <div class="card-body">
                            <form method="GET" action="<?= $h($base . $home) ?>">
                                <div class="form-group">
                                    <select class="form-control" name="test_category" aria-label="Ticket category">
                                        <option value="">-- Category --</option>
                                        <?php foreach ($allCategories as $category): ?>
                                            <option value="<?= $h($category) ?>" <?= $testCategory === $category ? 'selected' : '' ?>><?= $h($category) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <input type="text" class="form-control" name="test_text" value="<?= $h($testText) ?>"
                                           placeholder="Ticket subject / description" aria-label="Ticket text">
                                </div>
                                <button type="submit" class="btn btn-outline-primary btn-sm">Check</button>
                            </form>
                            <?php if ($testResult !== null): ?>
                                <hr>
                                <?php foreach ($testResult as $i => $match): ?>
                                    <div class="manual-match">
                                        <strong><?= $i === 0 ? 'Sent: ' : 'Also matches: ' ?><?= $h($match['title']) ?></strong>
                                        <span class="small text-muted">(score <?= (int) $match['score'] ?>)</span>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="col-xl-6">
                    <div class="card data-list-card shadow mb-4">
                        <div class="card-header d-flex align-items-center justify-content-between flex-wrap">
                            <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-list mr-1"></i>Guides</h6>
                            <input type="search" id="guideFilter" class="form-control form-control-sm mt-1 mt-sm-0" style="max-width:240px"
                                   placeholder="Filter guides" aria-label="Filter guides">
                        </div>
                        <div class="card-body p-0">
                            <?php if (!$manuals): ?>
                                <p class="text-muted p-4 mb-0">No guides yet.</p>
                            <?php endif; ?>
                            <?php foreach ($byGroup as $groupName => $items): ?>
                                <div class="guide-group">
                                    <div class="group-title"><?= $h($groupName) ?> (<?= count($items) ?>)</div>
                                    <div class="list-group list-group-flush">
                                        <?php foreach ($items as $manual):
                                            $steps = TicketManuals::steps((string) ($manual['steps'] ?? ''));
                                            $cats = array_filter(array_map('trim', explode(',', (string) ($manual['categories'] ?? ''))));
                                            $kws = array_filter(array_map('trim', explode(',', (string) ($manual['keywords'] ?? ''))));
                                            $photoCount = count(ManualPresentation::stepPhotos($manual));
                                        ?>
                                            <div class="list-group-item guide-row" data-search="<?= $h(strtolower($manual['title'] . ' ' . ($manual['keywords'] ?? '') . ' ' . $groupName)) ?>">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div class="pr-3">
                                                        <h6 class="mb-1 font-weight-bold">
                                                            <?= $h($manual['title']) ?>
                                                            <?php if ((int) $manual['is_default'] === 1): ?>
                                                                <span class="manual-chip manual-default">Default</span>
                                                            <?php endif; ?>
                                                            <?php if (!empty($manual['it_only'])): ?>
                                                                <span class="manual-chip it">IT only</span>
                                                            <?php endif; ?>
                                                        </h6>
                                                        <?php if (!empty($manual['description'])): ?>
                                                            <div class="small text-muted mb-1"><?= $h($manual['description']) ?></div>
                                                        <?php endif; ?>
                                                        <div>
                                                            <?php foreach ($cats as $cat): ?><span class="manual-chip"><?= $h($cat) ?></span><?php endforeach; ?>
                                                            <?php foreach (array_slice($kws, 0, 6) as $kw): ?><span class="manual-chip kw"><?= $h($kw) ?></span><?php endforeach; ?>
                                                        </div>
                                                        <?php if ($steps): ?>
                                                            <details class="guide-details mt-1">
                                                                <summary><?= count($steps) ?> steps<?= $photoCount ? ', ' . $photoCount . ' with photos' : '' ?></summary>
                                                                <ol class="manual-steps mt-1">
                                                                    <?php foreach ($steps as $step): ?><li><?= $h($step) ?></li><?php endforeach; ?>
                                                                </ol>
                                                            </details>
                                                        <?php endif; ?>
                                                        <div class="mt-2">
                                                            <a class="btn btn-sm btn-primary" href="<?= $h($base) ?>/manuals/view?id=<?= (int) $manual['manual_id'] ?>" target="_blank" rel="noopener">
                                                                <i class="fas fa-desktop mr-1"></i>View slides</a>
                                                            <a class="btn btn-sm btn-outline-primary" href="<?= $h($base) ?>/manuals/pptx?id=<?= (int) $manual['manual_id'] ?>">
                                                                <i class="fas fa-file-powerpoint mr-1"></i>PowerPoint</a>
                                                            <?php if (!empty($manual['file_name'])): ?>
                                                                <a class="btn btn-sm btn-outline-secondary" href="<?= $h($base) ?>/manuals/file?id=<?= (int) $manual['manual_id'] ?>" target="_blank" rel="noopener">
                                                                    <i class="fas fa-paperclip mr-1"></i><?= $h($manual['file_name']) ?></a>
                                                            <?php endif; ?>
                                                            <?php if (!empty($manual['canva_design_id'])): ?>
                                                                <a class="btn btn-sm btn-outline-secondary" target="_blank" rel="noopener noreferrer"
                                                                   href="https://www.canva.com/design/<?= rawurlencode((string) $manual['canva_design_id']) ?>/edit"
                                                                   title="Opens the original design in Canva (Canva login needed)">
                                                                    <i class="fas fa-palette mr-1"></i>Canva</a>
                                                            <?php endif; ?>
                                                        </div>
                                                        <?php if (!empty($manual['url'])): ?>
                                                            <a class="small" href="<?= $h($manual['url']) ?>" target="_blank" rel="noopener noreferrer">
                                                                <i class="fas fa-external-link-alt mr-1"></i><?= $h($manual['url']) ?>
                                                            </a>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="text-nowrap">
                                                        <a href="<?= $h($base . $home) ?>?edit=<?= (int) $manual['manual_id'] ?>#manual-form"
                                                           class="btn btn-sm btn-outline-primary" title="Edit"><i class="fas fa-edit"></i></a>
                                                        <?php if ((int) $manual['is_default'] !== 1): ?>
                                                            <form method="POST" action="<?= $h($base . $home) ?>" class="d-inline"
                                                                  onsubmit="return confirm('Delete this guide?');">
                                                                <input type="hidden" name="csrf_token" value="<?= $h($csrf_token ?? '') ?>">
                                                                <input type="hidden" name="action" value="delete">
                                                                <input type="hidden" name="manual_id" value="<?= (int) $manual['manual_id'] ?>">
                                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete"><i class="fas fa-trash"></i></button>
                                                            </form>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

            </div>
        </div>
    </div>

    <script src="<?= $h($base) ?>/assets/vendor/jquery/jquery.min.js"></script>
    <script src="<?= $h($base) ?>/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="<?= $h($base) ?>/assets/vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="<?= $h($base) ?>/assets/js/sb-admin-2.min.js"></script>
    <script>
    (function () {
        // One photo field per STEP line (section "#" and page "##" lines have no photo).
        var steps = document.getElementById('m_steps');
        var box = document.getElementById('stepPhotos');
        var empty = document.getElementById('stepPhotosEmpty');
        var existing = JSON.parse(box.getAttribute('data-existing') || '{}');
        var rows = {};
        function stepLines() {
            var out = [], number = 0;
            steps.value.split(/\r?\n/).forEach(function (line) {
                line = line.trim();
                if (!line) return;
                if (line.charAt(0) === '#') { if (line.charAt(1) !== '#') number = 0; return; }
                number++;
                out.push({ number: number, text: line.replace(/\s*\|\|\s*/g, ' ') });
            });
            return out;
        }
        function row(index) {
            if (rows[index]) return rows[index];
            var el = document.createElement('div');
            el.className = 'step-photo-row';
            el.innerHTML = '<span class="n"></span><span class="t"></span>';
            if (existing[index]) {
                var img = document.createElement('img');
                img.src = existing[index];
                img.alt = 'Current photo';
                el.appendChild(img);
                var rm = document.createElement('label');
                rm.className = 'mb-0 text-nowrap';
                rm.innerHTML = '<input type="checkbox" name="remove_step_photo[' + index + ']" value="1"> Remove';
                el.appendChild(rm);
            }
            var input = document.createElement('input');
            input.type = 'file';
            input.name = 'step_photo[' + index + ']';
            input.accept = 'image/jpeg,image/png';
            input.setAttribute('aria-label', 'Photo for step line ' + index);
            el.appendChild(input);
            rows[index] = el;
            return el;
        }
        function render() {
            var list = stepLines();
            Object.keys(rows).forEach(function (k) { if (k > list.length) { rows[k].remove(); delete rows[k]; } });
            list.forEach(function (step, i) {
                var el = row(i + 1);
                el.querySelector('.n').textContent = 'STEP ' + step.number;
                el.querySelector('.t').textContent = step.text;
                el.querySelector('.t').title = step.text;
                box.appendChild(el);
            });
            empty.style.display = list.length ? 'none' : '';
        }
        steps.addEventListener('input', render);
        render();

        var filter = document.getElementById('guideFilter');
        filter.addEventListener('input', function () {
            var words = filter.value.toLowerCase().split(/\s+/).filter(Boolean);
            document.querySelectorAll('.guide-group').forEach(function (group) {
                var any = false;
                group.querySelectorAll('.guide-row').forEach(function (row) {
                    var text = row.getAttribute('data-search');
                    var ok = words.every(function (w) { return text.indexOf(w) !== -1; });
                    row.style.display = ok ? '' : 'none';
                    if (ok) any = true;
                });
                group.style.display = any ? '' : 'none';
            });
        });
    })();
    </script>
    <?php require __DIR__ . '/../partials/flash_modal.php'; ?>
</body>

</html>
