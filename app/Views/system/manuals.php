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
// Who can open it: everyone, some roles, or IT and Admin only.
if ($old) {
    $audienceMode = (string) ($old['audience_mode'] ?? (!empty($old['it_only']) ? 'it' : 'all'));
    $audienceRoles = (array) ($old['audience'] ?? []);
} else {
    $audienceRoles = $editing ? TicketManuals::audience($editing) : [];
    $audienceMode = !empty($editing['it_only']) ? 'it' : ($audienceRoles ? 'some' : 'all');
}
$byGroup = [];
foreach ($manuals as $manual) {
    $byGroup[TicketManuals::groupOf($manual)][] = $manual;
}
// Which tab opens: the editor when editing / after an error / ?new=1, the test when testing, else the list.
$openTab = ($formId || $old || isset($_GET['new']) || !$manuals) ? 'create' : ($testResult !== null ? 'test' : 'list');
$editorConfig = [
    'base' => $base,
    'isNew' => $formId === 0 && !$old,
    'steps' => (string) ($source['steps'] ?? ''),
    'photos' => (object) $existingPhotos,
    'actionDefault' => ManualPresentation::ACTION_DEFAULT,
    'logo' => $base . '/assets/img/storagemart-logo-slide.png',
    'categories' => array_values($allCategories),
];
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
    <link href="<?= $h($base) ?>/assets/css/manual-editor.css?v=20261010" rel="stylesheet">
    <link rel="icon" href="<?= $h($base) ?>/assets/img/sm-icon-32.png?v=20261016" type="image/x-icon">
    <?php if ($isIt) { require_once __DIR__ . '/../partials/it/theme_head.php'; } ?>
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

        <div class="container-fluid admin-users-page manual-editor-page">

            <div class="page-hero hero-accounts">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <h1><i class="fas fa-book-medical mr-2"></i>Troubleshooting Manuals</h1>
                        <p>Make a guide in 4 easy steps. It becomes slides and a PowerPoint in the IT Department's manual design,
                            it is sent in the chat of matching tickets, and everyone finds it in the manuals library.</p>
                        <div class="d-flex flex-wrap" style="gap:8px">
                            <a href="<?= $h($base . $home) ?>?new=1" class="btn btn-warning btn-sm font-weight-bold" id="createBtn"><i class="fas fa-plus mr-1"></i>Create a guide</a>
                            <button type="button" class="btn btn-light btn-sm font-weight-bold" id="tourBtn"><i class="fas fa-play-circle mr-1"></i>How to create a guide</button>
                            <a href="<?= $h($base) ?>/manuals" target="_blank" rel="noopener" class="btn btn-outline-light btn-sm"><i class="fas fa-th-large mr-1"></i>Open the manuals library</a>
                        </div>
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

            <ul class="nav me-tabs mb-3" role="tablist">
                <li><button type="button" class="me-tab<?= $openTab === 'create' ? ' on' : '' ?>" id="tabCreate" data-tab="create" role="tab">
                    <i class="fas <?= $formId ? 'fa-edit' : 'fa-magic' ?> mr-1"></i><?= $formId ? 'Edit guide' : 'Create a guide' ?></button></li>
                <li><button type="button" class="me-tab<?= $openTab === 'list' ? ' on' : '' ?>" id="tabList" data-tab="list" role="tab">
                    <i class="fas fa-list mr-1"></i>All guides <span class="me-count"><?= count($manuals) ?></span></button></li>
                <li><button type="button" class="me-tab<?= $openTab === 'test' ? ' on' : '' ?>" id="tabTest" data-tab="test" role="tab">
                    <i class="fas fa-vial mr-1"></i>Which guide is sent?</button></li>
            </ul>

            <!-- ================================================================ create / edit -->
            <div class="me-pane" data-pane="create" <?= $openTab === 'create' ? '' : 'hidden' ?>>
                <form method="POST" action="<?= $h($base . $home) ?>" enctype="multipart/form-data" id="manualForm" novalidate>
                    <input type="hidden" name="csrf_token" value="<?= $h($csrf_token ?? '') ?>">
                    <input type="hidden" name="action" value="save">
                    <input type="hidden" name="manual_id" value="<?= $formId ?>">
                    <input type="hidden" name="step_photo_map" id="stepPhotoMap" value="">
                    <textarea name="steps" id="m_steps_hidden" hidden><?= $h($source['steps'] ?? '') ?></textarea>

                    <div class="row">
                        <div class="col-xl-7">
                            <div class="card shadow mb-4" id="manual-form">
                                <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                                    <h6 class="m-0 font-weight-bold text-primary">
                                        <i class="fas <?= $formId ? 'fa-edit' : 'fa-magic' ?> mr-1"></i><?= $formId ? 'Edit: ' . $h($source['title'] ?? '') : 'Create a guide' ?>
                                    </h6>
                                    <div class="d-flex" style="gap:6px">
                                        <?php if ($formId): ?>
                                            <a href="<?= $h($base) ?>/manuals/view?id=<?= $formId ?>" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-desktop mr-1"></i>Saved slides</a>
                                            <a href="<?= $h($base . $home) ?>?new=1" class="btn btn-sm btn-light">New guide</a>
                                        <?php else: ?>
                                            <button type="button" class="btn btn-sm btn-outline-primary" id="exampleBtn"><i class="fas fa-lightbulb mr-1"></i>Start from an example</button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <ol class="me-stepper" id="stepper">
                                        <li data-go="1" class="on"><b>1</b><span>Basics</span></li>
                                        <li data-go="2"><b>2</b><span>The problem</span></li>
                                        <li data-go="3"><b>3</b><span>The fix</span></li>
                                        <li data-go="4"><b>4</b><span>Finish</span></li>
                                    </ol>

                                    <!-- Step 1 -->
                                    <section class="me-step" data-step="1">
                                        <div class="me-callout"><i class="fas fa-info-circle"></i>
                                            <div><strong>What is a guide?</strong> Short slides that show how to fix one problem, step by step.
                                                Fill in the boxes; the slides on the right are made for you. Empty boxes are skipped.</div></div>
                                        <div class="form-group" id="wz-title">
                                            <label for="m_title">Title <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control form-control-lg" id="m_title" name="title" maxlength="150" required
                                                   value="<?= $h($source['title'] ?? '') ?>" placeholder="e.g. Printer Paper Jam (Brother DCP-L2540DW)" data-preview="cover">
                                            <small class="form-text text-muted">Say the problem the way users say it, and add the device model. It is the first slide.</small>
                                        </div>
                                        <div class="form-group" id="wz-group">
                                            <label for="m_group">Group <small class="text-muted">(the folder in the library)</small></label>
                                            <input type="text" class="form-control" id="m_group" name="manual_group" maxlength="100" list="m_groups"
                                                   value="<?= $h($source['manual_group'] ?? '') ?>" placeholder="Pick one below or type a new group">
                                            <datalist id="m_groups">
                                                <?php foreach ($groupNames as $groupName): ?><option value="<?= $h($groupName) ?>"><?php endforeach; ?>
                                            </datalist>
                                            <div class="me-chips mt-2">
                                                <?php foreach ($groupNames as $groupName): ?>
                                                    <button type="button" class="me-chip js-group" data-value="<?= $h($groupName) ?>"><?= $h($groupName) ?></button>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                        <div class="form-group" id="wz-audience">
                                            <label class="d-block">Who can open it?</label>
                                            <div class="me-choices">
                                                <label class="me-choice"><input type="radio" name="audience_mode" value="all" <?= $audienceMode === 'all' ? 'checked' : '' ?>>
                                                    <span><i class="fas fa-users"></i><strong>Everyone</strong><small>All users. It can be sent in ticket chats.</small></span></label>
                                                <label class="me-choice"><input type="radio" name="audience_mode" value="some" <?= $audienceMode === 'some' ? 'checked' : '' ?>>
                                                    <span><i class="fas fa-user-tag"></i><strong>Some roles</strong><small>Only the roles you tick (IT and Admin always see it).</small></span></label>
                                                <label class="me-choice"><input type="radio" name="audience_mode" value="it" <?= $audienceMode === 'it' ? 'checked' : '' ?>>
                                                    <span><i class="fas fa-user-shield"></i><strong>IT and Admin only</strong><small>Setup / maintenance guides. Never sent to requesters.</small></span></label>
                                            </div>
                                            <div class="me-roles" id="audienceRoles" <?= $audienceMode === 'some' ? '' : 'hidden' ?>>
                                                <?php foreach (TicketManuals::AUDIENCE_ROLES as $roleKey => $roleLabel): ?>
                                                    <div class="custom-control custom-checkbox custom-control-inline">
                                                        <input type="checkbox" class="custom-control-input" id="aud_<?= $h($roleKey) ?>" name="audience[]" value="<?= $h($roleKey) ?>"
                                                            <?= in_array($roleKey, $audienceRoles, true) ? 'checked' : '' ?>>
                                                        <label class="custom-control-label" for="aud_<?= $h($roleKey) ?>"><?= $h($roleLabel) ?></label>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                        <?php if (!$isIt): ?>
                                            <div class="custom-control custom-switch mb-1">
                                                <input type="checkbox" class="custom-control-input" id="m_default" name="is_default" value="1"
                                                    <?= !empty($source['is_default']) ? 'checked' : '' ?>>
                                                <label class="custom-control-label" for="m_default">Default guide (sent when no other guide matches a ticket)</label>
                                            </div>
                                        <?php endif; ?>
                                    </section>

                                    <!-- Step 2 -->
                                    <section class="me-step" data-step="2" hidden id="wz-problem">
                                        <div class="me-callout"><i class="fas fa-search"></i>
                                            <div><strong>Describe the problem.</strong> Each box is one slide. Short sentences work best.</div></div>
                                        <div class="form-group me-field">
                                            <label for="m_intro">Introduction <span class="me-slide-tag">slide: Introduction</span></label>
                                            <textarea class="form-control" id="m_intro" name="intro" rows="3" data-preview="intro"
                                                      placeholder="e.g. In this procedure the facilitator can clear a paper jam without calling IT."><?= $h($source['intro'] ?? '') ?></textarea>
                                            <small class="form-text text-muted">One or two sentences: what this guide helps with.</small>
                                        </div>
                                        <div class="form-group me-field">
                                            <label for="m_issue">Main Issue <span class="me-slide-tag">slide: Main Issue</span></label>
                                            <textarea class="form-control" id="m_issue" name="main_issue" rows="2" data-preview="issue"
                                                      placeholder="What the user sees, e.g. The printer stops and the screen shows &quot;Paper Jam&quot;."><?= $h($source['main_issue'] ?? '') ?></textarea>
                                            <small class="form-text text-muted">What the user sees: the error message, the light, the sound.</small>
                                        </div>
                                        <div class="form-group me-field">
                                            <label for="m_factors">Factors <span class="me-slide-tag">slide: Factors</span></label>
                                            <textarea class="form-control" id="m_factors" name="factors" rows="4" data-preview="factors"
                                                      placeholder="One possible cause per line, e.g.&#10;Paper tray is overfilled&#10;Curled or damp paper"><?= $h($source['factors'] ?? '') ?></textarea>
                                            <small class="form-text text-muted">Possible causes, one per line. Each line becomes a bullet.</small>
                                        </div>
                                    </section>

                                    <!-- Step 3 -->
                                    <section class="me-step" data-step="3" hidden>
                                        <div class="me-callout"><i class="fas fa-tools"></i>
                                            <div><strong>Explain the fix.</strong> Add one step for each action. A photo of where to click or what to open helps a lot.</div></div>
                                        <div class="form-group me-field">
                                            <label for="m_action">Action <span class="me-slide-tag">slide: Action</span>
                                                <button type="button" class="btn btn-link btn-sm p-0 ml-2" id="actionReset">Use the standard text</button></label>
                                            <textarea class="form-control" id="m_action" name="action_text" rows="2" data-preview="action"><?= $h($actionValue) ?></textarea>
                                        </div>
                                        <div class="form-group me-field">
                                            <label for="m_solution">Solution <span class="me-slide-tag">slide: Solution</span></label>
                                            <textarea class="form-control" id="m_solution" name="solution" rows="3" data-preview="solution"
                                                      placeholder="e.g. The facilitator can remove the jammed paper by following the steps. If it still fails, report it to IT."><?= $h($source['solution'] ?? '') ?></textarea>
                                        </div>
                                        <div class="form-group" id="wz-steps">
                                            <div class="d-flex align-items-center justify-content-between flex-wrap mb-1">
                                                <label class="mb-0">Steps <span class="me-slide-tag">one STEP slide each</span></label>
                                                <button type="button" class="btn btn-link btn-sm p-0" id="textModeBtn"><i class="fas fa-code mr-1"></i>Edit as text</button>
                                            </div>
                                            <div id="stepList" class="me-items"></div>
                                            <div class="me-empty" id="stepEmpty">No steps yet. Click "Add a step".</div>
                                            <div class="me-add">
                                                <button type="button" class="btn btn-primary btn-sm" data-add="step"><i class="fas fa-plus mr-1"></i>Add a step</button>
                                                <button type="button" class="btn btn-outline-primary btn-sm" data-add="section" title="A title page; STEP numbers start again at 1"><i class="fas fa-heading mr-1"></i>Add a section title</button>
                                                <button type="button" class="btn btn-outline-primary btn-sm" data-add="page" title="A page with its own heading and text"><i class="fas fa-file-alt mr-1"></i>Add an info page</button>
                                            </div>
                                            <div id="textMode" hidden>
                                                <textarea class="form-control" id="m_steps" rows="10" aria-label="Steps as text"></textarea>
                                                <div class="me-syntax">
                                                    One line = one STEP. <code># 1. Jam in Tray 1</code> = a section title (numbers start again).
                                                    <code>## What is LOS? :: LOS means...</code> = an info page. <code> || </code> = new line, <code>- </code> = bullet.
                                                </div>
                                            </div>
                                        </div>
                                    </section>

                                    <!-- Step 4 -->
                                    <section class="me-step" data-step="4" hidden>
                                        <div class="form-group me-field">
                                            <label for="m_note">Note <span class="me-slide-tag">slide: Note</span></label>
                                            <textarea class="form-control" id="m_note" name="note" rows="3" data-preview="note"
                                                      placeholder="Things to avoid, when to call IT, sources."><?= $h($source['note'] ?? '') ?></textarea>
                                        </div>
                                        <div id="wz-match">
                                            <label class="d-block">When is it sent in a ticket chat?</label>
                                            <div class="me-match">
                                                <div class="form-group mb-2">
                                                    <label class="small font-weight-bold" for="catFilter">Ticket categories</label>
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
                                                <div class="form-group mb-2">
                                                    <label class="small font-weight-bold" for="m_kw">Keywords <small class="text-muted">(words people type in the subject or description; comma between them)</small></label>
                                                    <div class="input-group">
                                                        <input type="text" class="form-control" id="m_kw" name="keywords" maxlength="500"
                                                               value="<?= $h($source['keywords'] ?? '') ?>" placeholder="paper jam, jammed, stuck paper">
                                                        <div class="input-group-append"><button type="button" class="btn btn-outline-primary" id="suggestKw"><i class="fas fa-magic mr-1"></i>Suggest</button></div>
                                                    </div>
                                                </div>
                                                <div class="me-sentence" id="matchSentence"></div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="m_desc">Short description <small class="text-muted">(shown in lists; the Main Issue is used when empty)</small></label>
                                            <input type="text" class="form-control" id="m_desc" name="description" maxlength="255" value="<?= $h($source['description'] ?? '') ?>">
                                        </div>
                                        <details class="me-more" <?= !empty($source['url']) || !empty($editing['file_name']) ? 'open' : '' ?>>
                                            <summary>Optional: attach your own PowerPoint / PDF, or a link</summary>
                                            <div class="form-group mt-2">
                                                <label for="m_file">PowerPoint or PDF <small class="text-muted">(max 25 MB)</small></label>
                                                <input type="file" class="form-control-file" id="m_file" name="manual_file" accept=".pptx,.ppt,.pdf">
                                                <small class="form-text text-muted">For example from Canva: Share &gt; Download &gt; PDF. A PDF is shown under the slides.</small>
                                                <?php if (!empty($editing['file_name'])): ?>
                                                    <div class="custom-control custom-checkbox mt-1">
                                                        <input type="checkbox" class="custom-control-input" id="m_remove_file" name="remove_file" value="1">
                                                        <label class="custom-control-label small" for="m_remove_file">Remove the current file (<?= $h($editing['file_name']) ?>)</label>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="form-group mb-0">
                                                <label for="m_url">Online link <small class="text-muted">(Canva / Google Drive links may ask for a login)</small></label>
                                                <input type="url" class="form-control" id="m_url" name="url" maxlength="500" value="<?= $h($source['url'] ?? '') ?>" placeholder="https://">
                                            </div>
                                        </details>
                                        <ul class="me-check" id="checklist"></ul>
                                    </section>

                                    <div class="me-nav">
                                        <button type="button" class="btn btn-light" id="backBtn"><i class="fas fa-arrow-left mr-1"></i>Back</button>
                                        <span class="me-progress" id="progressText">Step 1 of 4</span>
                                        <button type="button" class="btn btn-outline-primary" id="nextBtn">Next<i class="fas fa-arrow-right ml-1"></i></button>
                                        <button type="submit" class="btn btn-primary" id="wz-save"><i class="fas fa-save mr-1"></i>Save guide</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-5">
                            <div class="me-sticky">
                                <div class="card shadow mb-4" id="wz-preview">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-eye mr-1"></i>Live preview <span class="me-count" id="slideCount">0</span></h6>
                                        <span class="small text-muted">Click a slide to edit it</span>
                                    </div>
                                    <div class="card-body">
                                        <div class="me-slides" id="slides"></div>
                                    </div>
                                </div>
                                <div class="card shadow mb-4 me-tips">
                                    <div class="card-body small">
                                        <strong><i class="fas fa-star text-warning mr-1"></i>Tips for a good guide</strong>
                                        <ul class="mb-0 pl-3 mt-1">
                                            <li>One problem per guide, 3 to 8 steps.</li>
                                            <li>Start each step with an action word: Open, Click, Pull, Turn off.</li>
                                            <li>Add a photo to the steps people get stuck on.</li>
                                            <li>Say when to stop and call IT (in the Solution or the Note).</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- ================================================================ all guides -->
            <div class="me-pane" data-pane="list" <?= $openTab === 'list' ? '' : 'hidden' ?>>
                <div class="card data-list-card shadow mb-4">
                    <div class="card-header d-flex align-items-center justify-content-between flex-wrap">
                        <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-list mr-1"></i>Guides</h6>
                        <input type="search" id="guideFilter" class="form-control form-control-sm mt-1 mt-sm-0" style="max-width:260px"
                               placeholder="Filter guides" aria-label="Filter guides">
                    </div>
                    <div class="card-body p-0">
                        <?php if (!$manuals): ?>
                            <p class="text-muted p-4 mb-0">No guides yet. Click "Create a guide".</p>
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
                                        $audience = TicketManuals::audience($manual);
                                    ?>
                                        <div class="list-group-item guide-row" data-search="<?= $h(strtolower($manual['title'] . ' ' . ($manual['keywords'] ?? '') . ' ' . $groupName)) ?>">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div class="pr-3">
                                                    <h6 class="mb-1 font-weight-bold">
                                                        <?= $h($manual['title']) ?>
                                                        <?php if ((int) $manual['is_default'] === 1): ?><span class="manual-chip manual-default">Default</span><?php endif; ?>
                                                        <?php if (!empty($manual['it_only'])): ?><span class="manual-chip it">IT only</span><?php endif; ?>
                                                        <?php foreach ($audience as $audRole): ?><span class="manual-chip aud"><?= $h(TicketManuals::AUDIENCE_ROLES[$audRole]) ?></span><?php endforeach; ?>
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
                                                            <i class="fas fa-external-link-alt mr-1"></i><?= $h($manual['url']) ?></a>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="text-nowrap">
                                                    <a href="<?= $h($base . $home) ?>?edit=<?= (int) $manual['manual_id'] ?>"
                                                       class="btn btn-sm btn-outline-primary" title="Edit"><i class="fas fa-edit"></i></a>
                                                    <?php if ((int) $manual['is_default'] !== 1): ?>
                                                        <form method="POST" action="<?= $h($base . $home) ?>" class="d-inline"
                                                              data-confirm="Delete this guide?">
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

            <!-- ================================================================ test -->
            <div class="me-pane" data-pane="test" <?= $openTab === 'test' ? '' : 'hidden' ?>>
                <div class="card form-card shadow mb-4" style="max-width:760px">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-vial mr-1"></i>Test: which guide is sent?</h6>
                    </div>
                    <div class="card-body">
                        <p class="small text-muted">Type a ticket like a user would. The TMS shows which guide it sends in the chat.</p>
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
                                       placeholder="Ticket subject / description, e.g. printer at the front desk paper jam" aria-label="Ticket text">
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
        </div>

            </div>
        </div>
    </div>

    <script src="<?= $h($base) ?>/assets/vendor/jquery/jquery.min.js"></script>
    <script src="<?= $h($base) ?>/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="<?= $h($base) ?>/assets/vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="<?= $h($base) ?>/assets/js/sb-admin-2.min.js"></script>
    <script>window.TMS_MANUAL_EDITOR = <?= json_encode($editorConfig, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) ?>;</script>
    <script src="<?= $h($base) ?>/assets/js/manual-editor.js?v=20261016"></script>
    <?php require __DIR__ . '/../partials/flash_modal.php'; ?>
</body>

</html>
