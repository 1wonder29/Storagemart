<?php
$base = rtrim(BASE_URL, '/');
$h = static function ($value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};
$schedules = $schedules ?? [];
$themes = $themes ?? [];
$fmt = static function (string $value): string {
    return date('M j, Y g:i A', strtotime($value));
};
// Theme form: editing a theme, re-showing a form with errors, or a new theme.
$formOpen = $editTheme !== null || !empty($themeOld) || !empty($_GET['new_theme']);
$form = [
    'key' => $editKey !== '' && $editTheme ? $editKey : '',
    'name' => $editTheme['name'] ?? '',
    'icon' => $editTheme['icon'] ?? 'fa-star',
    'items' => implode(' ', $editTheme['items'] ?? []),
    'main' => $editTheme['colors'][0] ?? '#f26522',
    'accent' => $editTheme['colors'][1] ?? '#1f3a93',
    'message' => $editTheme['message'] ?? '',
    'from' => !empty($editTheme['from']) ? date('Y') . '-' . $editTheme['from'] : '',
    'to' => !empty($editTheme['to']) ? date('Y') . '-' . $editTheme['to'] : '',
    'effects' => $editTheme['effects'] ?? ['confetti'],
];
if (!empty($themeOld)) {
    $form = [
        'key' => (string) ($themeOld['theme_key'] ?? ''),
        'name' => (string) ($themeOld['name'] ?? ''),
        'icon' => (string) ($themeOld['icon'] ?? 'fa-star'),
        'items' => (string) ($themeOld['items'] ?? ''),
        'main' => (string) ($themeOld['color_main'] ?? '#f26522'),
        'accent' => (string) ($themeOld['color_accent'] ?? '#1f3a93'),
        'message' => (string) ($themeOld['message'] ?? ''),
        'from' => (string) ($themeOld['date_from'] ?? ''),
        'to' => (string) ($themeOld['date_to'] ?? ''),
        'effects' => (array) ($themeOld['effects'] ?? []),
    ];
}
$customEffects = $customEffects ?? [];
$customJs = $customJs ?? [];
$effectLabel = static function (string $effect) use ($customEffects): string {
    return $customEffects[$effect]['name'] ?? (SeasonalThemes::EFFECTS[$effect][0] ?? $effect);
};
$effectsJs = [];
foreach ($themes as $key => $theme) {
    $effectsJs[$key] = ['items' => $theme['items'], 'colors' => $theme['colors'], 'effects' => $theme['effects'], 'custom' => (object) $customJs];
}
// Custom effect form: editing one, or a new one.
$effectFormOpen = !empty($editEffect) || !empty($_GET['new_effect']);
$ef = [
    'id' => (int) ($editEffect['id'] ?? 0),
    'name' => (string) ($editEffect['name'] ?? ''),
    'style' => (string) ($editEffect['style'] ?? 'fireworks'),
    'emoji' => implode(' ', $editEffect['emoji'] ?? []),
    'round' => $editEffect ? !empty($editEffect['round']) : true,
    'size' => (int) ($editEffect['size'] ?? 70),
    'amount' => (int) ($editEffect['amount'] ?? 20),
    'images' => $editEffect['images'] ?? [],
];
$effectImageUrl = static fn(string $file): string => $base . '/effects/image?f=' . rawurlencode($file);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Storage Mart | Seasonal Themes</title>
    <link href="<?= $h($base) ?>/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900" rel="stylesheet">
    <link href="<?= $h($base) ?>/assets/css/storagemart.css" rel="stylesheet">
    <link href="<?= $h($base) ?>/assets/css/admin-users.css" rel="stylesheet">
    <link rel="icon" href="<?= $h($base) ?>/assets/img/sm-icon-32.png?v=20261016" type="image/x-icon">
    <style>
        .theme-card { border-radius:14px; overflow:hidden; height:100%; }
        .theme-swatch { height:86px; display:flex; align-items:center; justify-content:center; gap:10px; font-size:1.9rem; color:#fff; }
        .theme-card .card-body { display:flex; flex-direction:column; }
        .theme-live { position:absolute; top:8px; right:8px; background:#1cc88a; color:#fff; font-size:.7rem; font-weight:800; padding:2px 8px; border-radius:999px; }
        .theme-own { position:absolute; top:8px; left:8px; background:rgba(0,0,0,.35); color:#fff; font-size:.65rem; font-weight:800; padding:2px 8px; border-radius:999px; }
        .fx-list { list-style:none; counter-reset:fx; padding:0; margin:0 0 10px; }
        .fx-list li { counter-increment:fx; display:flex; align-items:center; gap:6px; font-size:.8rem; padding:2px 0; }
        .fx-list li::before { content:counter(fx) "."; font-weight:800; color:#f26522; min-width:16px; }
        .fx-play { border:0; background:#eef2ff; color:#4e73df; border-radius:999px; width:22px; height:22px; font-size:.6rem; line-height:22px; padding:0; margin-left:auto; }
        .fx-play:hover { background:#4e73df; color:#fff; }
        .fx-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(210px, 1fr)); gap:4px 14px; }
        .fx-grid .custom-control { display:flex; align-items:center; }
        .fx-grid .fx-play { margin-left:6px; }
        .cfx-list { display:grid; grid-template-columns:repeat(auto-fill, minmax(min(100%, 340px), 1fr)); gap:10px; }
        .cfx-item { display:flex; flex-wrap:wrap; align-items:center; gap:8px; padding:8px 10px; border:1px solid #e6ecf7; border-radius:12px; background:#fbfcff; }
        .cfx-thumbs { display:flex; flex:0 0 auto; }
        .cfx-thumbs img, .cfx-thumbs span { width:34px; height:34px; border-radius:8px; object-fit:cover; border:2px solid #fff; box-shadow:0 2px 6px rgba(0,0,0,.15); margin-left:-8px; background:#fff; display:inline-flex; align-items:center; justify-content:center; font-size:20px; }
        .cfx-thumbs img:first-child, .cfx-thumbs span:first-child { margin-left:0; }
        .cfx-thumbs img.round { border-radius:50%; }
        .cfx-info { flex:1; min-width:120px; display:flex; flex-direction:column; }
        .cfx-info strong { white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .cfx-info span { font-size:.75rem; color:#64748b; }
        .cfx-form { border-top:1px solid #edf1f8; padding-top:14px; }
        .cfx-keep, .cfx-new { display:flex; flex-wrap:wrap; gap:8px; }
        .cfx-keep-item { position:relative; margin:0; cursor:pointer; }
        .cfx-keep-item input { position:absolute; top:4px; left:4px; }
        .cfx-keep-item img, .cfx-new img { width:64px; height:64px; object-fit:cover; border-radius:10px; border:2px solid #e2e8f0; }
        .cfx-keep-item input:not(:checked) + img { opacity:.35; filter:grayscale(1); }
        html.it-dark .cfx-item { background:#232838; border-color:#343a4f; }
        .sched-badge { display:inline-block; padding:1px 8px; border-radius:999px; font-size:.7rem; font-weight:700; }
        .sched-badge.live { background:#d1fae5; color:#047857; } .sched-badge.up { background:#e0e7ff; color:#3730a3; }
        .sched-badge.past { background:#f3f4f6; color:#6b7280; } .sched-badge.off { background:#fee2e2; color:#b91c1c; }
        .btn-howto { background:#f26522; color:#fff; font-weight:800; border-radius:999px; animation:howtoGlow 1.8s infinite; }
        .btn-howto:hover { color:#fff; filter:brightness(1.06); }
        @keyframes howtoGlow { 0% { box-shadow:0 0 0 0 rgba(242,101,34,.55); } 70% { box-shadow:0 0 0 10px rgba(242,101,34,0); } 100% { box-shadow:0 0 0 0 rgba(242,101,34,0); } }
        .theme-preview-strip { height:64px; border-radius:10px; display:flex; align-items:center; justify-content:center; gap:10px; font-size:1.3rem; font-weight:700; color:#fff; padding:0 12px; text-align:center; }
        /* step-by-step tutorial */
        .tour-ring { position:absolute; z-index:1070; border:3px solid #f26522; border-radius:10px; box-shadow:0 0 0 4000px rgba(17,24,39,.45); pointer-events:none; transition:all .25s ease; }
        .tour-tip { position:absolute; z-index:1071; width:320px; max-width:calc(100vw - 24px); background:#fff; color:#1f2937; border-radius:12px; padding:14px 16px;
                    box-shadow:0 14px 40px rgba(0,0,0,.3); border-top:5px solid #f26522; }
        .tour-tip h6 { font-weight:800; color:#f26522; margin:0 0 6px; }
        .tour-tip p { font-size:.86rem; margin:0 0 10px; }
        .tour-tip .tour-n { font-size:.72rem; color:#6b7280; }
        html.it-dark .tour-tip { background:#1e2230; color:#e5e7eb; }
        html.it-dark .fx-play { background:#262b3b; }
    </style>
</head>

<body id="page-top">

    <div id="wrapper">
        <?php
        $activePage = 'system';
        $systemSubPage = 'themes';
        require_once __DIR__ . '/../../partials/admin/sidebar_topbar.php';
        ?>

        <div class="container-fluid admin-users-page">

            <div class="page-hero hero-accounts">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <h1><i class="fas fa-palette mr-2"></i>Seasonal Themes</h1>
                        <p>Give the whole system a seasonal look (Halloween, Christmas, summer...) with its own screen effects.
                            Add your own themes, preview them here (only you see the preview), then set the release date and time.</p>
                    </div>
                    <div class="col-lg-4">
                        <div class="hero-stat mt-3 mt-lg-0">
                            <div class="stat-value" style="font-size:1.2rem"><?= $h($current['name'] ?? 'None') ?></div>
                            <div class="stat-label">Live now</div>
                        </div>
                    </div>
                </div>
            </div>

            <?php if (!empty($setupMissing)): ?>
                <div class="alert alert-warning"><i class="fas fa-database mr-1"></i>
                    The database update has not been run yet. Import the update SQL in phpMyAdmin first.</div>
            <?php endif; ?>

            <div class="d-flex flex-wrap align-items-center mb-3" style="gap:8px">
                <h5 class="mb-0 mr-2 font-weight-bold text-gray-800">Theme list</h5>
                <a class="btn btn-sm btn-primary" id="addThemeBtn" href="<?= $h($base) ?>/admin/themes?new_theme=1#theme-form">
                    <i class="fas fa-plus mr-1"></i>Add a theme</a>
                <button type="button" class="btn btn-sm btn-howto" id="tourBtn"><i class="fas fa-play-circle mr-1"></i>How to add a theme</button>
                <a class="btn btn-sm btn-outline-danger ml-auto" href="<?= $h($base) ?>/admin/themes?birthday_preview=1">
                    <i class="fas fa-birthday-cake mr-1"></i>Test the birthday greeting</a>
            </div>
            <div class="row" id="themeList">
                <?php foreach ($themes as $key => $theme):
                    $range = SeasonalThemes::suggestedRange($theme);
                    $isLive = ($current['key'] ?? '') === $key;
                ?>
                    <div class="col-md-6 col-xl-3 mb-4" id="theme-<?= $h($key) ?>">
                        <div class="card shadow theme-card position-relative">
                            <div class="theme-swatch" style="background:linear-gradient(135deg, <?= $h($theme['colors'][0]) ?>, <?= $h($theme['colors'][1]) ?>)">
                                <?php foreach (array_slice($theme['items'], 0, 3) as $item): ?><span aria-hidden="true"><?= $h($item) ?></span><?php endforeach; ?>
                            </div>
                            <?php if ($isLive): ?><span class="theme-live">LIVE</span><?php endif; ?>
                            <?php if (empty($theme['builtin'])): ?><span class="theme-own">Added by Admin</span><?php endif; ?>
                            <div class="card-body">
                                <h6 class="font-weight-bold mb-1"><i class="fas <?= $h($theme['icon']) ?> mr-1"></i><?= $h($theme['name']) ?></h6>
                                <p class="small text-muted mb-2"><?= $h($theme['message']) ?></p>
                                <div class="small font-weight-bold text-gray-700 mb-1">Effects</div>
                                <ol class="fx-list">
                                    <?php foreach ($theme['effects'] as $effect): ?>
                                        <li><?= $h($effectLabel($effect)) ?>
                                            <button type="button" class="fx-play js-fx" data-theme="<?= $h($key) ?>" data-effect="<?= $h($effect) ?>"
                                                    title="Test: <?= $h(SeasonalThemes::EFFECTS[$effect][1] ?? 'Your own effect') ?>" aria-label="Test the <?= $h($effectLabel($effect)) ?> effect">
                                                <i class="fas fa-play"></i></button></li>
                                    <?php endforeach; ?>
                                </ol>
                                <p class="small mb-3">
                                    <?php if ($range): ?>
                                        Suggested: <strong><?= $h(date('M j', strtotime($range['from']))) ?> – <?= $h(date('M j, Y', strtotime($range['to']))) ?></strong>
                                    <?php else: ?>
                                        Any date (company events)
                                    <?php endif; ?>
                                </p>
                                <div class="mt-auto d-flex flex-wrap" style="gap:6px">
                                    <a class="btn btn-sm btn-outline-primary" href="<?= $h($base) ?>/admin/themes?theme_preview=<?= $h($key) ?>">
                                        <i class="fas fa-eye mr-1"></i>Preview</a>
                                    <button type="button" class="btn btn-sm btn-primary js-schedule"
                                            data-key="<?= $h($key) ?>"
                                            data-from="<?= $h($range['from'] ?? '') ?>" data-to="<?= $h($range['to'] ?? '') ?>">
                                        <i class="fas fa-calendar-plus mr-1"></i>Schedule</button>
                                    <a class="btn btn-sm btn-outline-secondary" href="<?= $h($base) ?>/admin/themes?edit_theme=<?= $h($key) ?>#theme-form" title="Edit">
                                        <i class="fas fa-edit"></i></a>
                                    <form method="POST" action="<?= $h($base) ?>/admin/themes" class="d-inline js-delete-theme" data-name="<?= $h($theme['name']) ?>">
                                        <input type="hidden" name="csrf_token" value="<?= $h($csrf_token ?? '') ?>">
                                        <input type="hidden" name="action" value="delete_theme">
                                        <input type="hidden" name="theme_key" value="<?= $h($key) ?>">
                                        <button class="btn btn-sm btn-outline-danger" title="Delete"><i class="fas fa-trash"></i></button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
                <?php if (!$themes): ?>
                    <div class="col-12"><p class="text-muted">No themes. Add one, or restore the built-in themes below.</p></div>
                <?php endif; ?>
            </div>

            <?php if ($formOpen): ?>
                <div class="card form-card shadow mb-4" id="theme-form">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-primary"><i class="fas <?= $form['key'] ? 'fa-edit' : 'fa-plus' ?> mr-1"></i>
                            <?= $form['key'] ? 'Edit theme: ' . $h($form['name']) : 'Add a theme' ?></h6>
                        <a href="<?= $h($base) ?>/admin/themes" class="btn btn-sm btn-light">Cancel</a>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="<?= $h($base) ?>/admin/themes" id="themeForm">
                            <input type="hidden" name="csrf_token" value="<?= $h($csrf_token ?? '') ?>">
                            <input type="hidden" name="action" value="save_theme">
                            <input type="hidden" name="theme_key" id="f_key" value="<?= $h($form['key']) ?>">
                            <div class="form-row">
                                <div class="form-group col-md-5" id="tf-name">
                                    <label for="f_name">1. Theme name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="f_name" name="name" maxlength="60" required value="<?= $h($form['name']) ?>"
                                           placeholder="e.g. Chinese New Year">
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="f_icon">Icon</label>
                                    <select class="form-control" id="f_icon" name="icon">
                                        <?php foreach (SeasonalThemes::ICONS as $iconName): ?>
                                            <option value="<?= $h($iconName) ?>" <?= $form['icon'] === $iconName ? 'selected' : '' ?>><?= $h(str_replace('fa-', '', $iconName)) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-md-4" id="tf-items">
                                    <label for="f_items">2. Emoji <small class="text-muted">(up to 6, with spaces)</small></label>
                                    <input type="text" class="form-control" id="f_items" name="items" maxlength="200" value="<?= $h($form['items']) ?>" placeholder="🧧 🐉 🏮 🎆">
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-2" id="tf-colors">
                                    <label for="f_main">3. Main color</label>
                                    <input type="color" class="form-control" id="f_main" name="color_main" value="<?= $h($form['main']) ?>">
                                </div>
                                <div class="form-group col-md-2">
                                    <label for="f_accent">Second color</label>
                                    <input type="color" class="form-control" id="f_accent" name="color_accent" value="<?= $h($form['accent']) ?>">
                                </div>
                                <div class="form-group col-md-8" id="tf-message">
                                    <label for="f_message">4. Greeting <small class="text-muted">(shown in a ribbon under the header)</small></label>
                                    <input type="text" class="form-control" id="f_message" name="message" maxlength="200" value="<?= $h($form['message']) ?>"
                                           placeholder="e.g. Kung Hei Fat Choi from StorageMart! 🧧">
                                </div>
                            </div>
                            <div class="theme-preview-strip mb-3" id="f_strip" aria-hidden="true"></div>
                            <div class="form-group" id="tf-effects">
                                <label class="d-block">5. Effects <small class="text-muted">(tick one or more; press <i class="fas fa-play"></i> to see each one)</small></label>
                                <div class="fx-grid">
                                    <?php foreach (SeasonalThemes::EFFECTS as $effect => [$label, $desc]): ?>
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="fx_<?= $h($effect) ?>" name="effects[]" value="<?= $h($effect) ?>"
                                                <?= in_array($effect, $form['effects'], true) ? 'checked' : '' ?>>
                                            <label class="custom-control-label small" for="fx_<?= $h($effect) ?>" title="<?= $h($desc) ?>"><?= $h($label) ?></label>
                                            <button type="button" class="fx-play js-fx-form" data-effect="<?= $h($effect) ?>" aria-label="Test the <?= $h($label) ?> effect"><i class="fas fa-play"></i></button>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <?php if ($customEffects): ?>
                                    <div class="small font-weight-bold text-primary mt-2 mb-1"><i class="fas fa-star mr-1"></i>Your own effects <a href="#custom-effects" class="font-weight-normal">(make more below)</a></div>
                                    <div class="fx-grid">
                                        <?php foreach ($customEffects as $effect => $custom): ?>
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" id="fx_<?= $h($effect) ?>" name="effects[]" value="<?= $h($effect) ?>"
                                                    <?= in_array($effect, $form['effects'], true) ? 'checked' : '' ?>>
                                                <label class="custom-control-label small" for="fx_<?= $h($effect) ?>" title="<?= $h(CustomEffects::STYLES[$custom['style']][1] ?? '') ?>"><?= $h($custom['name']) ?></label>
                                                <button type="button" class="fx-play js-fx-form" data-effect="<?= $h($effect) ?>" aria-label="Test the <?= $h($custom['name']) ?> effect"><i class="fas fa-play"></i></button>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="form-row" id="tf-dates">
                                <div class="form-group col-sm-4">
                                    <label for="f_from">6. Suggested start <small class="text-muted">(optional, any year)</small></label>
                                    <input type="date" class="form-control" id="f_from" name="date_from" value="<?= $h($form['from']) ?>">
                                </div>
                                <div class="form-group col-sm-4">
                                    <label for="f_to">Suggested end</label>
                                    <input type="date" class="form-control" id="f_to" name="date_to" value="<?= $h($form['to']) ?>">
                                </div>
                            </div>
                            <div class="d-flex flex-wrap" style="gap:8px">
                                <button type="submit" class="btn btn-primary" id="tf-save"><i class="fas fa-save mr-1"></i>7. Save theme</button>
                                <button type="button" class="btn btn-outline-primary" id="tf-test"><i class="fas fa-magic mr-1"></i>Test all ticked effects</button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endif; ?>

            <div class="card form-card shadow mb-4" id="custom-effects">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap" style="gap:8px">
                    <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-star mr-1"></i>Custom effects <small class="text-muted font-weight-normal">(your own pictures or emoji)</small></h6>
                    <?php if (!$effectFormOpen && !empty($customReady)): ?>
                        <a href="<?= $h($base) ?>/admin/themes?new_effect=1#custom-effects" class="btn btn-sm btn-primary"><i class="fas fa-plus"></i> Add an effect</a>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <?php if (empty($customReady)): ?>
                        <p class="text-muted mb-0">Please import the database update (update O) first.</p>
                    <?php else: ?>
                        <p class="small text-muted">Make a funny effect, for example <b>Fireworks</b> where every burst throws out the IT team's faces.
                            Upload the pictures (faces look best cropped close), choose the style, press <b>Preview</b>, then <b>Save</b>.
                            Then tick it under <b>5. Effects</b> of a theme.</p>
                        <?php if ($customEffects): ?>
                            <div class="cfx-list mb-3">
                                <?php foreach ($customEffects as $key => $custom): ?>
                                    <div class="cfx-item">
                                        <div class="cfx-thumbs">
                                            <?php foreach (array_slice($custom['images'], 0, 4) as $img): ?>
                                                <img src="<?= $h($effectImageUrl($img)) ?>" alt="" class="<?= $custom['round'] ? 'round' : '' ?>">
                                            <?php endforeach; ?>
                                            <?php foreach (array_slice($custom['emoji'], 0, 4 - min(4, count($custom['images']))) as $em): ?>
                                                <span><?= $h($em) ?></span>
                                            <?php endforeach; ?>
                                        </div>
                                        <div class="cfx-info"><strong><?= $h($custom['name']) ?></strong><span><?= $h(CustomEffects::STYLES[$custom['style']][0] ?? $custom['style']) ?></span></div>
                                        <button type="button" class="btn btn-sm btn-outline-primary js-fx-custom" data-effect="<?= $h($key) ?>"><i class="fas fa-play"></i> Preview</button>
                                        <a class="btn btn-sm btn-light" href="<?= $h($base) ?>/admin/themes?edit_effect=<?= (int) $custom['id'] ?>#custom-effects" title="Edit"><i class="fas fa-pen"></i></a>
                                        <form method="POST" action="<?= $h($base) ?>/admin/themes" class="d-inline" data-confirm="Delete this effect? Themes that use it stop playing it.">
                                            <input type="hidden" name="csrf_token" value="<?= $h($csrf_token ?? '') ?>">
                                            <input type="hidden" name="action" value="delete_effect">
                                            <input type="hidden" name="effect_id" value="<?= (int) $custom['id'] ?>">
                                            <button class="btn btn-sm btn-outline-danger" title="Delete"><i class="fas fa-trash"></i></button>
                                        </form>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($effectFormOpen): ?>
                            <form method="POST" action="<?= $h($base) ?>/admin/themes" enctype="multipart/form-data" id="effectForm" class="cfx-form">
                                <input type="hidden" name="csrf_token" value="<?= $h($csrf_token ?? '') ?>">
                                <input type="hidden" name="action" value="save_effect">
                                <input type="hidden" name="effect_id" value="<?= (int) $ef['id'] ?>">
                                <h6 class="font-weight-bold mb-3"><?= $ef['id'] ? 'Edit the effect' : 'New effect' ?></h6>
                                <div class="form-row">
                                    <div class="form-group col-md-4">
                                        <label for="e_name">1. Name</label>
                                        <input type="text" class="form-control" id="e_name" name="effect_name" maxlength="60" required value="<?= $h($ef['name']) ?>" placeholder="e.g. IT team fireworks">
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label for="e_style">2. Style</label>
                                        <select class="custom-select" id="e_style" name="effect_style">
                                            <?php foreach (CustomEffects::STYLES as $style => [$label, $desc]): ?>
                                                <option value="<?= $h($style) ?>"<?= $ef['style'] === $style ? ' selected' : '' ?>><?= $h($label . ' - ' . $desc) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label for="e_emoji">Emoji <small class="text-muted">(optional, up to 6)</small></label>
                                        <input type="text" class="form-control" id="e_emoji" name="effect_emoji" value="<?= $h($ef['emoji']) ?>" placeholder="🎉 😂 💥">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="e_images">3. Pictures <small class="text-muted">(up to <?= CustomEffects::MAX_IMAGES ?>; JPG, PNG, GIF or WebP; max 3 MB each)</small></label>
                                    <?php if ($ef['images']): ?>
                                        <div class="cfx-keep mb-2">
                                            <?php foreach ($ef['images'] as $i => $img): ?>
                                                <label class="cfx-keep-item" title="Untick to remove this picture">
                                                    <input type="checkbox" name="keep_images[]" value="<?= $h($img) ?>" checked data-url="<?= $h($effectImageUrl($img)) ?>">
                                                    <img src="<?= $h($effectImageUrl($img)) ?>" alt="Picture <?= $i + 1 ?>">
                                                </label>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                    <input type="file" class="form-control-file" id="e_images" name="effect_images[]" accept="image/jpeg,image/png,image/gif,image/webp" multiple>
                                    <div class="cfx-new mt-2" id="e_new"></div>
                                </div>
                                <div class="form-row align-items-end">
                                    <div class="form-group col-md-4">
                                        <label for="e_size">4. Size <small class="text-muted" id="e_size_v"><?= (int) $ef['size'] ?> px</small></label>
                                        <input type="range" class="custom-range" id="e_size" name="effect_size" min="30" max="140" value="<?= (int) $ef['size'] ?>">
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label for="e_amount">How many <small class="text-muted" id="e_amount_v"><?= (int) $ef['amount'] ?></small></label>
                                        <input type="range" class="custom-range" id="e_amount" name="effect_amount" min="4" max="40" value="<?= (int) $ef['amount'] ?>">
                                    </div>
                                    <div class="form-group col-md-4">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="e_round" name="effect_round" value="1"<?= $ef['round'] ? ' checked' : '' ?>>
                                            <label class="custom-control-label" for="e_round">Round pictures (best for faces)</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex flex-wrap" style="gap:8px">
                                    <button type="button" class="btn btn-outline-primary" id="e_preview"><i class="fas fa-play"></i> 5. Preview</button>
                                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> 6. Save effect</button>
                                    <a href="<?= $h($base) ?>/admin/themes#custom-effects" class="btn btn-light">Cancel</a>
                                </div>
                            </form>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="row">
                <div class="col-xl-5">
                    <div class="card form-card shadow mb-4" id="schedule-form">
                        <div class="card-header">
                            <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-calendar-alt mr-1"></i>Schedule a release</h6>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="<?= $h($base) ?>/admin/themes">
                                <input type="hidden" name="csrf_token" value="<?= $h($csrf_token ?? '') ?>">
                                <input type="hidden" name="action" value="schedule" id="t_action">
                                <div class="form-group">
                                    <label for="t_key">Theme</label>
                                    <select class="form-control" id="t_key" name="theme_key" required>
                                        <?php foreach ($themes as $key => $theme): ?>
                                            <option value="<?= $h($key) ?>"><?= $h($theme['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-row">
                                    <div class="form-group col-sm-6">
                                        <label for="t_from">Release date / time</label>
                                        <input type="datetime-local" class="form-control" id="t_from" name="starts_at">
                                    </div>
                                    <div class="form-group col-sm-6">
                                        <label for="t_to">Ends</label>
                                        <input type="datetime-local" class="form-control" id="t_to" name="ends_at">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="t_msg">Greeting <small class="text-muted">(optional; the theme's greeting when empty)</small></label>
                                    <input type="text" class="form-control" id="t_msg" name="message" maxlength="200" placeholder="e.g. Happy Halloween, team!">
                                </div>
                                <div class="d-flex flex-wrap align-items-center" style="gap:8px">
                                    <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i>Save schedule</button>
                                    <span class="text-muted small">or</span>
                                    <div class="input-group input-group-sm" style="width:auto">
                                        <select class="custom-select custom-select-sm" name="days" aria-label="Days">
                                            <option value="1">1 day</option><option value="3">3 days</option>
                                            <option value="7" selected>7 days</option><option value="14">14 days</option><option value="30">30 days</option>
                                        </select>
                                        <div class="input-group-append">
                                            <button type="submit" class="btn btn-outline-success" onclick="document.getElementById('t_action').value='now'">
                                                Start now</button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                            <hr>
                            <form method="POST" action="<?= $h($base) ?>/admin/themes" data-confirm="Add back any built-in theme that was deleted?">
                                <input type="hidden" name="csrf_token" value="<?= $h($csrf_token ?? '') ?>">
                                <input type="hidden" name="action" value="restore_builtin">
                                <button class="btn btn-sm btn-link px-0"><i class="fas fa-undo mr-1"></i>Restore the built-in themes</button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-xl-7">
                    <div class="card data-list-card shadow mb-4">
                        <div class="card-header">
                            <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-list mr-1"></i>Live, upcoming and past</h6>
                        </div>
                        <div class="card-body p-0">
                            <?php if (!$schedules): ?>
                                <p class="text-muted p-4 mb-0">Nothing scheduled yet.</p>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead><tr><th>Theme</th><th>From</th><th>To</th><th>Status</th><th></th></tr></thead>
                                        <tbody>
                                            <?php foreach ($schedules as $s):
                                                $name = $themes[$s['theme_key']]['name'] ?? ($s['title'] ?: $s['theme_key']);
                                                if ((int) $s['is_active'] !== 1) {
                                                    $status = ['off', 'Off'];
                                                } elseif ((int) $s['is_live'] === 1) {
                                                    $status = ['live', 'Live now'];
                                                } elseif ((int) $s['is_upcoming'] === 1) {
                                                    $status = ['up', 'Upcoming'];
                                                } else {
                                                    $status = ['past', 'Ended'];
                                                }
                                            ?>
                                                <tr>
                                                    <td><strong><?= $h($name) ?></strong>
                                                        <?php if (!empty($s['message'])): ?><div class="small text-muted"><?= $h($s['message']) ?></div><?php endif; ?></td>
                                                    <td class="small"><?= $h($fmt($s['starts_at'])) ?></td>
                                                    <td class="small"><?= $h($fmt($s['ends_at'])) ?></td>
                                                    <td><span class="sched-badge <?= $status[0] ?>"><?= $status[1] ?></span></td>
                                                    <td class="text-nowrap text-right">
                                                        <?php if ($status[0] === 'live'): ?>
                                                            <form method="POST" action="<?= $h($base) ?>/admin/themes" class="d-inline">
                                                                <input type="hidden" name="csrf_token" value="<?= $h($csrf_token ?? '') ?>">
                                                                <input type="hidden" name="action" value="stop">
                                                                <input type="hidden" name="schedule_id" value="<?= (int) $s['schedule_id'] ?>">
                                                                <button class="btn btn-sm btn-outline-warning" title="End now"><i class="fas fa-stop"></i></button>
                                                            </form>
                                                        <?php endif; ?>
                                                        <?php if ($status[0] !== 'past'): ?>
                                                            <form method="POST" action="<?= $h($base) ?>/admin/themes" class="d-inline">
                                                                <input type="hidden" name="csrf_token" value="<?= $h($csrf_token ?? '') ?>">
                                                                <input type="hidden" name="action" value="toggle">
                                                                <input type="hidden" name="schedule_id" value="<?= (int) $s['schedule_id'] ?>">
                                                                <button class="btn btn-sm btn-outline-secondary" title="<?= (int) $s['is_active'] === 1 ? 'Turn off' : 'Turn on' ?>">
                                                                    <i class="fas <?= (int) $s['is_active'] === 1 ? 'fa-toggle-on' : 'fa-toggle-off' ?>"></i></button>
                                                            </form>
                                                        <?php endif; ?>
                                                        <form method="POST" action="<?= $h($base) ?>/admin/themes" class="d-inline" data-confirm="Delete this schedule?">
                                                            <input type="hidden" name="csrf_token" value="<?= $h($csrf_token ?? '') ?>">
                                                            <input type="hidden" name="action" value="delete">
                                                            <input type="hidden" name="schedule_id" value="<?= (int) $s['schedule_id'] ?>">
                                                            <button class="btn btn-sm btn-outline-danger" title="Delete"><i class="fas fa-trash"></i></button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
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
        var THEMES = <?= json_encode($effectsJs, JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) ?>;
        function play(keys, theme, btn) {
            if (btn && window.TMSCooldown && !window.TMSCooldown.start(btn, 6)) return;
            if (window.TMSEffects) window.TMSEffects.play(keys, theme, 8);
        }

        // Schedule buttons on the theme cards.
        document.querySelectorAll('.js-schedule').forEach(function (btn) {
            btn.addEventListener('click', function () {
                document.getElementById('t_key').value = btn.getAttribute('data-key');
                document.getElementById('t_from').value = btn.getAttribute('data-from');
                document.getElementById('t_to').value = btn.getAttribute('data-to');
                document.getElementById('schedule-form').scrollIntoView({ behavior: 'smooth', block: 'start' });
                document.getElementById('t_from').focus();
            });
        });
        document.querySelectorAll('.js-delete-theme').forEach(function (form) {
            form.setAttribute('data-confirm', 'Delete the ' + form.getAttribute('data-name') + ' theme and its schedules?');
        });
        // Custom effects: preview a saved one, or the one in the form (with the pictures just chosen).
        var CUSTOM = <?= json_encode((object) $customJs, JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) ?>;
        document.querySelectorAll('.js-fx-custom').forEach(function (btn) {
            btn.addEventListener('click', function () {
                play([btn.getAttribute('data-effect')], { custom: CUSTOM, colors: ['#f85f00', '#19398a'] }, btn);
            });
        });
        var effectForm = document.getElementById('effectForm');
        if (effectForm) {
            var fileInput = document.getElementById('e_images');
            var newBox = document.getElementById('e_new');
            var localUrls = [];
            var showNew = function () {
                localUrls.forEach(function (u) { URL.revokeObjectURL(u); });
                localUrls = Array.prototype.slice.call(fileInput.files || [], 0, <?= CustomEffects::MAX_IMAGES ?>).map(function (f) { return URL.createObjectURL(f); });
                newBox.innerHTML = '';
                localUrls.forEach(function (u) {
                    var img = document.createElement('img');
                    img.src = u;
                    img.alt = '';
                    newBox.appendChild(img);
                });
            };
            fileInput.addEventListener('change', showNew);
            ['size', 'amount'].forEach(function (k) {
                var input = document.getElementById('e_' + k);
                input.addEventListener('input', function () { document.getElementById('e_' + k + '_v').textContent = input.value + (k === 'size' ? ' px' : ''); });
            });
            document.getElementById('e_preview').addEventListener('click', function () {
                var kept = Array.prototype.map.call(effectForm.querySelectorAll('input[name="keep_images[]"]:checked'), function (c) { return c.getAttribute('data-url'); });
                var def = {
                    style: document.getElementById('e_style').value,
                    images: kept.concat(localUrls),
                    emoji: document.getElementById('e_emoji').value.trim().split(/[\s,]+/).filter(Boolean),
                    round: document.getElementById('e_round').checked,
                    size: parseInt(document.getElementById('e_size').value, 10),
                    amount: parseInt(document.getElementById('e_amount').value, 10)
                };
                if (!def.images.length && !def.emoji.length) { window.alert('Choose at least one picture or type an emoji first.'); return; }
                play(['__preview'], { custom: { __preview: def }, colors: ['#f85f00', '#19398a'] }, document.getElementById('e_preview'));
            });
            setTimeout(function () { document.getElementById('custom-effects').scrollIntoView({ behavior: 'smooth', block: 'start' }); }, 200);
        }

        // Test one effect of a theme.
        document.querySelectorAll('.js-fx').forEach(function (btn) {
            btn.addEventListener('click', function () {
                play([btn.getAttribute('data-effect')], THEMES[btn.getAttribute('data-theme')] || {}, btn);
            });
        });

        // Theme form: live preview strip and effect tests with the colors / emoji typed in.
        var form = document.getElementById('themeForm');
        if (form) {
            var strip = document.getElementById('f_strip');
            var formTheme = function () {
                return {
                    items: document.getElementById('f_items').value.trim().split(/[\s,]+/).filter(Boolean),
                    colors: [document.getElementById('f_main').value, document.getElementById('f_accent').value],
                    custom: CUSTOM,
                };
            };
            var paint = function () {
                var t = formTheme();
                strip.style.background = 'linear-gradient(135deg, ' + t.colors[0] + ', ' + t.colors[1] + ')';
                strip.textContent = (t.items.slice(0, 4).join('  ') || '✨') + '   ' + (document.getElementById('f_message').value || '');
            };
            ['f_items', 'f_main', 'f_accent', 'f_message'].forEach(function (id) { document.getElementById(id).addEventListener('input', paint); });
            paint();
            form.querySelectorAll('.js-fx-form').forEach(function (btn) {
                btn.addEventListener('click', function () { play([btn.getAttribute('data-effect')], formTheme(), btn); });
            });
            document.getElementById('tf-test').addEventListener('click', function () {
                var keys = Array.prototype.map.call(form.querySelectorAll('input[name="effects[]"]:checked'), function (c) { return c.value; });
                play(keys, formTheme(), document.getElementById('tf-test'));
            });
            setTimeout(function () { document.getElementById('theme-form').scrollIntoView({ behavior: 'smooth', block: 'start' }); }, 200);
        }

        // ---------- "How to add a theme" step by step ----------
        var steps = [
            { el: '#addThemeBtn', title: 'Add a theme', text: 'Click "Add a theme" to open the form. You can also edit (pencil) or delete (trash) any theme in the list.' },
            { el: '#tf-name', title: '1. Name', text: 'Give the theme a name, for example "Chinese New Year". The icon next to it shows on its card.' },
            { el: '#tf-items', title: '2. Emoji', text: 'Type up to 6 emoji with spaces between them. They show on the card, in the greeting ribbon and in the "Theme icons" effect.' },
            { el: '#tf-colors', title: '3. Colors', text: 'Pick the main and the second color. The header line and the greeting ribbon use them. The strip below shows how they look.' },
            { el: '#tf-message', title: '4. Greeting', text: 'Write the greeting everyone sees under the header while the theme is live.' },
            { el: '#tf-effects', title: '5. Effects', text: 'Tick the effects to play (for example Rain and Zombies for Halloween). Press the small play button to see each one first.' },
            { el: '#tf-dates', title: '6. Suggested dates', text: 'Optional: the usual dates of the event. They fill in the schedule form when you press Schedule on the card.' },
            { el: '#tf-save', title: '7. Save', text: 'Save the theme. It is added to the theme list. Nobody sees it yet.' },
            { el: '#themeList', title: 'Preview', text: 'On the theme card, press Preview to see the whole look with its effects. Only you see the preview.' },
            { el: '#schedule-form', title: 'Release it', text: 'Press Schedule on the card (or choose the theme here), set the release date and time and the end, then Save schedule. Everyone sees it during that time.' },
        ];
        var TOUR_KEY = 'tms_theme_tour_seen';
        var ring = null, tip = null, index = 0;
        function stopTour() {
            if (ring) ring.remove();
            if (tip) tip.remove();
            ring = tip = null;
            window.removeEventListener('resize', place);
            try { localStorage.setItem(TOUR_KEY, '1'); } catch (e) {}
        }
        function place() {
            var target = document.querySelector(steps[index].el);
            if (!target || !ring) return;
            var r = target.getBoundingClientRect(), sx = window.pageXOffset, sy = window.pageYOffset;
            ring.style.left = (r.left + sx - 6) + 'px';
            ring.style.top = (r.top + sy - 6) + 'px';
            ring.style.width = (r.width + 12) + 'px';
            ring.style.height = Math.min(r.height + 12, 520) + 'px';
            var tipTop = r.bottom + sy + 14;
            if (r.bottom + tip.offsetHeight + 30 > window.innerHeight && r.top > tip.offsetHeight + 30) tipTop = r.top + sy - tip.offsetHeight - 14;
            tip.style.top = tipTop + 'px';
            tip.style.left = Math.max(12, Math.min(r.left + sx, sx + document.documentElement.clientWidth - tip.offsetWidth - 12)) + 'px';
        }
        function show(i) {
            index = i;
            var step = steps[i];
            var target = document.querySelector(step.el);
            if (!target) {
                // The form steps need the form: open "Add a theme" and continue there.
                try { sessionStorage.setItem('tms_theme_tour_step', String(i)); } catch (e) {}
                window.location.href = <?= json_encode($base . '/admin/themes?new_theme=1#theme-form') ?>;
                return;
            }
            if (!ring) {
                ring = document.createElement('div'); ring.className = 'tour-ring'; document.body.appendChild(ring);
                tip = document.createElement('div'); tip.className = 'tour-tip'; tip.setAttribute('role', 'dialog'); document.body.appendChild(tip);
                window.addEventListener('resize', place);
            }
            tip.textContent = '';
            var h6 = document.createElement('h6'); h6.textContent = step.title;
            var p = document.createElement('p'); p.textContent = step.text;
            var bar = document.createElement('div'); bar.className = 'd-flex align-items-center';
            var n = document.createElement('span'); n.className = 'tour-n mr-auto'; n.textContent = (i + 1) + ' of ' + steps.length;
            var close = document.createElement('button'); close.type = 'button'; close.className = 'btn btn-sm btn-light mr-1'; close.textContent = 'Close';
            var back = document.createElement('button'); back.type = 'button'; back.className = 'btn btn-sm btn-outline-primary mr-1'; back.textContent = 'Back';
            var next = document.createElement('button'); next.type = 'button'; next.className = 'btn btn-sm btn-primary';
            next.textContent = i === steps.length - 1 ? 'Done' : 'Next';
            back.disabled = i === 0;
            close.onclick = stopTour;
            back.onclick = function () { show(i - 1); };
            next.onclick = function () { if (i === steps.length - 1) stopTour(); else show(i + 1); };
            bar.appendChild(n); bar.appendChild(close); bar.appendChild(back); bar.appendChild(next);
            tip.appendChild(h6); tip.appendChild(p); tip.appendChild(bar);
            target.scrollIntoView({ behavior: 'smooth', block: 'center' });
            setTimeout(place, 400);
            next.focus({ preventScroll: true });
        }
        document.getElementById('tourBtn').addEventListener('click', function () { show(0); });
        // Continue after the form opened, or start the first time an Admin opens "Add a theme".
        var resume = null;
        try { resume = sessionStorage.getItem('tms_theme_tour_step'); sessionStorage.removeItem('tms_theme_tour_step'); } catch (e) {}
        var seen = false;
        try { seen = localStorage.getItem(TOUR_KEY) === '1'; } catch (e) {}
        // Wait until the sign-in popups (announcements, birthdays) are closed.
        var afterPopups = function (fn) {
            var started = false;
            // Then also wait for the birthday greeting and the first-time guide to be closed.
            var run = function () {
                if (started) return;
                if (document.querySelector('.tmsw-modal, .tmsw-bday')) { setTimeout(run, 800); return; }
                started = true;
                setTimeout(fn, 400);
            };
            // The birthday greeting and the guide open a moment after the announcements close.
            var later = function () { setTimeout(run, 1200); };
            if (window.__tmsAnnDone) { later(); return; }
            document.addEventListener('tms:announcements-done', later);
            setTimeout(function () { if (!document.querySelector('.tmsw-ann-modal')) run(); }, 15000);
        };
        if (resume !== null && form) afterPopups(function () { show(parseInt(resume, 10) || 1); });
        else if (form && !seen && !document.getElementById('f_key').value) afterPopups(function () { show(1); });
    })();
    </script>
    <?php require __DIR__ . '/../../partials/flash_modal.php'; ?>
</body>

</html>
