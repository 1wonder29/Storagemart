<?php
$base = rtrim(BASE_URL, '/');
$isIt = ($role ?? '') === 'IT';
$manuals = $manuals ?? [];
$source = $old ?: ($editing ?: []);
$formId = (int) ($old['manual_id'] ?? ($editing['manual_id'] ?? 0));
$selectedCategories = isset($old['categories'])
    ? (array) $old['categories']
    : array_filter(array_map('trim', explode(',', (string) ($editing['categories'] ?? ''))));
$h = static function ($value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};
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
        .manual-steps { margin:0; padding-left:18px; font-size:.82rem; color:#4b5563; }
        .manual-default { background:#fff7ed; color:#c2410c; }
        .manual-match { border-left:4px solid #f26522; padding:8px 12px; background:#fffaf5; border-radius:6px; margin-bottom:8px; }
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
                        <p>After a ticket is filed, the best-matching guide is sent in the ticket chat automatically.
                            Matching uses the ticket category first, then keywords in the subject and description.</p>
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
                <div class="col-xl-5">
                    <div class="card form-card shadow mb-4" id="manual-form">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="fas <?= $formId ? 'fa-edit' : 'fa-plus' ?> mr-1"></i><?= $formId ? 'Edit guide' : 'Add a guide' ?>
                            </h6>
                            <?php if ($formId): ?>
                                <a href="<?= $h($base . $home) ?>" class="btn btn-sm btn-light">Cancel</a>
                            <?php endif; ?>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="<?= $h($base . $home) ?>">
                                <input type="hidden" name="csrf_token" value="<?= $h($csrf_token ?? '') ?>">
                                <input type="hidden" name="action" value="save">
                                <input type="hidden" name="manual_id" value="<?= $formId ?>">

                                <div class="form-group">
                                    <label for="m_title">Title <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="m_title" name="title" maxlength="150" required
                                           value="<?= $h($source['title'] ?? '') ?>" placeholder="e.g. Printer not printing">
                                </div>
                                <div class="form-group">
                                    <label for="m_desc">Short description</label>
                                    <input type="text" class="form-control" id="m_desc" name="description" maxlength="255"
                                           value="<?= $h($source['description'] ?? '') ?>" placeholder="What problems this guide covers">
                                </div>
                                <div class="form-group">
                                    <label for="m_steps">Steps <small class="text-muted">(one step per line)</small></label>
                                    <textarea class="form-control" id="m_steps" name="steps" rows="6"
                                              placeholder="Turn the printer off and on.&#10;Remove stuck paper.&#10;Print a test page."><?= $h($source['steps'] ?? '') ?></textarea>
                                </div>
                                <div class="form-group">
                                    <label for="m_url">Link to the full manual <small class="text-muted">(Canva, Google Drive, PDF...)</small></label>
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
                                           value="<?= $h($source['keywords'] ?? '') ?>" placeholder="printer, paper jam, toner">
                                </div>
                                <?php if (!$isIt): ?>
                                    <div class="custom-control custom-switch mb-3">
                                        <input type="checkbox" class="custom-control-input" id="m_default" name="is_default" value="1"
                                            <?= !empty($source['is_default']) ? 'checked' : '' ?>>
                                        <label class="custom-control-label" for="m_default">Default guide (its link is sent with every ticket)</label>
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

                <div class="col-xl-7">
                    <div class="card data-list-card shadow mb-4">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-list mr-1"></i>Guides</h6>
                        </div>
                        <div class="card-body p-0">
                            <?php if (!$manuals): ?>
                                <p class="text-muted p-4 mb-0">No guides yet.</p>
                            <?php endif; ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($manuals as $manual):
                                    $steps = TicketManuals::steps((string) ($manual['steps'] ?? ''));
                                    $cats = array_filter(array_map('trim', explode(',', (string) ($manual['categories'] ?? ''))));
                                    $kws = array_filter(array_map('trim', explode(',', (string) ($manual['keywords'] ?? ''))));
                                ?>
                                    <div class="list-group-item">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="pr-3">
                                                <h6 class="mb-1 font-weight-bold">
                                                    <?= $h($manual['title']) ?>
                                                    <?php if ((int) $manual['is_default'] === 1): ?>
                                                        <span class="manual-chip manual-default">Default</span>
                                                    <?php endif; ?>
                                                </h6>
                                                <?php if (!empty($manual['description'])): ?>
                                                    <div class="small text-muted mb-1"><?= $h($manual['description']) ?></div>
                                                <?php endif; ?>
                                                <div>
                                                    <?php foreach ($cats as $cat): ?><span class="manual-chip"><?= $h($cat) ?></span><?php endforeach; ?>
                                                    <?php foreach (array_slice($kws, 0, 8) as $kw): ?><span class="manual-chip kw"><?= $h($kw) ?></span><?php endforeach; ?>
                                                </div>
                                                <?php if ($steps): ?>
                                                    <ol class="manual-steps mt-1">
                                                        <?php foreach ($steps as $step): ?><li><?= $h($step) ?></li><?php endforeach; ?>
                                                    </ol>
                                                <?php endif; ?>
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
    <?php require __DIR__ . '/../partials/flash_modal.php'; ?>
</body>

</html>
