<?php
/**
 * Accountability form builder: choose what to print, preview it, download PDF / Word.
 * Expected: $employee, $options, $rows. Optional: $formBase (URL without query),
 * $sidebarPartial ('hr' | 'admin'), $backUrl.
 */
$base = rtrim(BASE_URL, '/');
$employeeId = (int) $employee['employee_id'];
$formBase = $formBase ?? ($base . '/hr/employees/accountability/' . $employeeId);
$sidebarPartial = $sidebarPartial ?? 'hr';
$backUrl = $backUrl ?? ($base . '/hr/employees/detail/' . $employeeId);
$query = AccountabilityFormService::query($options);
$employeeName = AccountabilityFormService::employeeName($employee);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Storage Mart | Accountability Form</title>
    <link href="<?= htmlspecialchars($base) ?>/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900" rel="stylesheet">
    <link rel="icon" href="<?= htmlspecialchars($base) ?>/assets/img/sm_favicon.png" type="image/x-icon">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/storagemart.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/hr-dashboard.css?v=20261008" rel="stylesheet">
    <style>
        .acc-options .form-section-label { font-size: .72rem; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; color: #6b7280; margin: 1rem 0 .4rem; }
        .acc-options .custom-control { margin-bottom: .25rem; }
        .acc-preview-frame { width: 100%; height: 78vh; border: 1px solid #e3e6f0; border-radius: .65rem; background: #f3f4f8; }
        .acc-actions .btn { min-width: 9rem; }
    </style>
</head>
<body id="page-top">
<div id="wrapper">
    <?php
    $activePage = $sidebarPartial === 'admin' ? 'users' : 'employees';
    require_once dirname(__DIR__, 2) . '/partials/' . $sidebarPartial . '/sidebar_topbar.php';
    ?>
    <div class="container-fluid hr-dashboard-page">
        <div class="page-hero">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h1><i class="fas fa-file-signature mr-2"></i>Accountability Form</h1>
                    <p><?= htmlspecialchars($employeeName) ?> — choose what to include, check the preview, then download.</p>
                </div>
                <div class="col-lg-4 mt-3 mt-lg-0 text-lg-right">
                    <a href="<?= htmlspecialchars($backUrl) ?>" class="btn btn-light btn-sm"><i class="fas fa-arrow-left"></i> Back</a>
                </div>
            </div>
        </div>

        <?php if (!empty($_SESSION['errorMessage'])): ?>
            <div class="alert alert-danger alert-modern"><?= htmlspecialchars($_SESSION['errorMessage']) ?></div>
            <?php unset($_SESSION['errorMessage']); ?>
        <?php endif; ?>

        <div class="row">
            <div class="col-lg-4 mb-4">
                <div class="card shadow acc-options">
                    <div class="card-body">
                        <form id="accOptions" action="<?= htmlspecialchars($formBase) ?>" method="GET">
                            <div class="form-section-label">Layout</div>
                            <select name="preset" id="accPreset" class="form-control">
                                <?php foreach (AccountabilityFormService::PRESETS as $key => $preset): ?>
                                    <option value="<?= $key ?>" <?= $options['preset'] === $key ? 'selected' : '' ?>><?= htmlspecialchars($preset['label']) ?></option>
                                <?php endforeach; ?>
                                <option value="custom" <?= $options['preset'] === 'custom' ? 'selected' : '' ?>>Custom…</option>
                            </select>

                            <div id="accCustom">
                                <div class="form-section-label">Columns</div>
                                <?php foreach (AccountabilityFormService::COLUMNS as $key => $label): ?>
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="col_<?= $key ?>" name="cols[]" value="<?= $key ?>"
                                            <?= in_array($key, $options['columns'], true) ? 'checked' : '' ?>>
                                        <label class="custom-control-label" for="col_<?= $key ?>"><?= htmlspecialchars($key === 'quantity' ? 'Quantity' : $label) ?></label>
                                    </div>
                                <?php endforeach; ?>

                                <div class="form-section-label">Sections</div>
                                <?php foreach (AccountabilityFormService::PARTS as $key => $label): ?>
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="part_<?= $key ?>" name="parts[]" value="<?= $key ?>"
                                            <?= in_array($key, $options['parts'], true) ? 'checked' : '' ?>>
                                        <label class="custom-control-label" for="part_<?= $key ?>"><?= htmlspecialchars($label) ?></label>
                                    </div>
                                <?php endforeach; ?>

                                <div class="form-section-label">Items to list</div>
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="sec_assets" name="sections[]" value="assets" <?= in_array('assets', $options['sections'], true) ? 'checked' : '' ?>>
                                    <label class="custom-control-label" for="sec_assets">IT assets</label>
                                </div>
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="sec_items" name="sections[]" value="items" <?= in_array('items', $options['sections'], true) ? 'checked' : '' ?>>
                                    <label class="custom-control-label" for="sec_items">Uniforms / issued items</label>
                                </div>
                                <select name="scope" class="form-control form-control-sm mt-2">
                                    <option value="active" <?= $options['scope'] === 'active' ? 'selected' : '' ?>>Currently issued only</option>
                                    <option value="all" <?= $options['scope'] === 'all' ? 'selected' : '' ?>>Include returned (full history)</option>
                                </select>
                            </div>

                            <p class="small text-muted mt-3 mb-0"><span id="accRowCount"><?= count($rows) ?></span> row(s) on the form.</p>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-8 mb-4">
                <div class="d-flex flex-wrap acc-actions mb-3">
                    <a id="accDownloadPdf" class="btn btn-danger mr-2 mb-2" href="<?= htmlspecialchars($formBase . '/download?format=pdf&' . $query) ?>">
                        <i class="fas fa-file-pdf mr-1"></i> Download PDF
                    </a>
                    <a id="accDownloadDocx" class="btn btn-primary mb-2" href="<?= htmlspecialchars($formBase . '/download?format=docx&' . $query) ?>">
                        <i class="fas fa-file-word mr-1"></i> Download Word
                    </a>
                </div>
                <iframe id="accPreview" class="acc-preview-frame" title="Accountability form preview"
                        src="<?= htmlspecialchars($formBase . '/preview?' . $query) ?>"></iframe>
            </div>
        </div>
    </div>
</div>
</div>
<script src="<?= htmlspecialchars($base) ?>/assets/vendor/jquery/jquery.min.js"></script>
<script src="<?= htmlspecialchars($base) ?>/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="<?= htmlspecialchars($base) ?>/assets/js/storagemart.min.js"></script>
<script>
(function ($) {
    var formBase = <?= json_encode($formBase) ?>;
    var presets = <?= json_encode(AccountabilityFormService::PRESETS) ?>;

    function applyPresetToChecks(key) {
        var p = presets[key];
        if (!p) return;
        $('input[name="cols[]"]').each(function () { this.checked = p.columns.indexOf(this.value) !== -1; });
        $('input[name="parts[]"]').each(function () { this.checked = p.parts.indexOf(this.value) !== -1; });
        $('input[name="sections[]"]').prop('checked', true);
        $('select[name="scope"]').val(p.scope);
    }

    function refresh() {
        var query = $('#accOptions').serialize();
        $('#accPreview').attr('src', formBase + '/preview?' + query);
        $('#accDownloadPdf').attr('href', formBase + '/download?format=pdf&' + query);
        $('#accDownloadDocx').attr('href', formBase + '/download?format=docx&' + query);
        if (window.history && history.replaceState) {
            history.replaceState(null, '', formBase + '?' + query);
        }
    }

    $('#accPreset').on('change', function () {
        applyPresetToChecks(this.value);
        refresh();
    });
    // Touching any individual option switches to a custom layout.
    $('#accCustom').on('change', 'input, select', function () {
        $('#accPreset').val('custom');
        refresh();
    });
    $('#accPreview').on('load', function () {
        try {
            var rows = this.contentDocument.querySelectorAll('table.items tbody tr').length;
            $('#accRowCount').text(rows);
        } catch (e) { /* preview from another origin: ignore */ }
    });
})(jQuery);
</script>
</body>
</html>
