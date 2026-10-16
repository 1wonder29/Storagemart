<?php
$base = rtrim(BASE_URL, '/');
$h = static function ($value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};
// Scope the report's own CSS to the paper so the page keeps its look (same rules as the PDF).
$paperCss = preg_replace('/@page\s*\{[^}]*\}/', '', $report['css']);
$paperCss = preg_replace_callback('/([^{}]+)\{([^}]*)\}/', static function ($m) {
    $selectors = array_map(static function ($s) {
        $s = trim($s);
        return $s === 'body' ? '.paper' : '.paper ' . $s;
    }, explode(',', $m[1]));
    return implode(', ', $selectors) . ' {' . $m[2] . '}';
}, $paperCss);
$slotNames = TicketSignatures::SLOTS;
$mine = array_values(array_filter($slots, static fn($s) => isset($slotNames[$s])));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Storage Mart | Sign Technical Report <?= $h($facts['ticket_number']) ?></title>
    <link href="<?= $h($base) ?>/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Nunito:400,600,700,800" rel="stylesheet">
    <link rel="icon" href="<?= $h($base) ?>/assets/img/sm-icon-32.png?v=20261016" type="image/x-icon">
    <style><?= $paperCss ?></style>
    <link href="<?= $h($base) ?>/assets/css/ticket-signature.css?v=<?= (int) @filemtime(__DIR__ . '/../../../public/assets/css/ticket-signature.css') ?>" rel="stylesheet">
</head>
<body>
<header class="sig-toolbar">
    <a href="<?= $h($backUrl) ?>" class="sig-btn sig-btn-ghost"><i class="fas fa-arrow-left"></i> Back to ticket</a>
    <div class="sig-title">
        <strong>Technical Summary Report</strong>
        <span><?= $h($facts['ticket_number']) ?></span>
    </div>
    <div class="sig-actions">
        <a class="sig-btn" href="<?= $h($base) ?>/tickets/signature/pdf?id=<?= (int) $facts['ticket_id'] ?>"><i class="fas fa-file-pdf"></i> Download PDF</a>
        <button type="button" class="sig-btn" onclick="window.print()"><i class="fas fa-print"></i> Print</button>
    </div>
</header>

<main class="sig-main">
    <section class="sig-help" aria-live="polite">
        <?php if (!$tableReady): ?>
            <p class="sig-warn"><i class="fas fa-database"></i> Digital signing needs the latest database update. You can still print the report.</p>
        <?php elseif ($mine): ?>
            <p><strong>Sign without printing:</strong> click <em>Sign here</em> in your box
                (<?= $h(implode(', ', array_map(static fn($s) => $slotNames[$s], $mine))) ?>), draw your signature or upload a photo of it,
                drag it into place, then save. Prefer paper? Use <em>Print</em> and sign by hand.</p>
        <?php else: ?>
            <p>You can view, download or print this report. Only the people named in a box can sign it.</p>
        <?php endif; ?>
        <ul class="sig-status">
            <?php foreach ($slotNames as $slot => $label): $signed = $signatures[$slot] ?? null; ?>
                <li class="<?= $signed ? 'done' : '' ?>">
                    <i class="fas <?= $signed ? 'fa-check-circle' : 'fa-circle' ?>"></i>
                    <?= $h($label) ?>: <?= $signed ? 'signed by ' . $h($signed['signer_name']) : 'not signed yet' ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </section>

    <article class="paper" id="sigPaper"
             data-ticket-id="<?= (int) $facts['ticket_id'] ?>"
             data-slots="<?= $h(implode(',', $tableReady ? $mine : [])) ?>"
             data-signed="<?= $h(implode(',', array_keys($signatures))) ?>"
             data-csrf="<?= $h($csrf_token) ?>"
             data-base="<?= $h($base) ?>"
             data-area-w="<?= TicketSignatures::AREA_W ?>" data-area-h="<?= TicketSignatures::AREA_H ?>"
             data-base-width="<?= TicketSignatures::BASE_WIDTH ?>">
        <?= $report['body'] ?>
    </article>

    <div class="sig-place" id="sigPlace" hidden>
        <span><i class="fas fa-arrows-alt"></i> Drag your signature to place it.</span>
        <label>Size <input type="range" id="sigScale" min="50" max="140" value="100"></label>
        <button type="button" class="sig-btn" id="sigSave"><i class="fas fa-save"></i> Save signature</button>
        <button type="button" class="sig-btn sig-btn-ghost" id="sigCancel">Cancel</button>
    </div>
</main>

<div class="sig-modal" id="sigModal" hidden role="dialog" aria-modal="true" aria-labelledby="sigModalTitle">
    <div class="sig-dialog">
        <div class="sig-dialog-head">
            <h2 id="sigModalTitle">Your signature</h2>
            <button type="button" class="sig-x" id="sigClose" aria-label="Close">&times;</button>
        </div>
        <div class="sig-tabs" role="tablist">
            <button type="button" class="sig-tab active" data-tab="draw" role="tab" aria-selected="true"><i class="fas fa-pen-nib"></i> Draw</button>
            <button type="button" class="sig-tab" data-tab="upload" role="tab" aria-selected="false"><i class="fas fa-upload"></i> Upload</button>
        </div>
        <div class="sig-pad-wrap">
            <canvas id="sigPad" width="560" height="200" aria-label="Signature pad"></canvas>
            <div class="sig-pad-hint" id="sigPadHint">Sign here with your mouse or finger</div>
        </div>
        <div class="sig-upload" id="sigUploadRow" hidden>
            <input type="file" id="sigFile" accept="image/*">
            <small>A photo or scan of your signature on white paper. The background is cleaned automatically.</small>
        </div>
        <div class="sig-dialog-actions">
            <button type="button" class="sig-btn sig-btn-ghost" id="sigClear"><i class="fas fa-eraser"></i> Clear</button>
            <button type="button" class="sig-btn" id="sigUse"><i class="fas fa-check"></i> Use this signature</button>
        </div>
        <p class="sig-error" id="sigError" hidden></p>
    </div>
</div>

<script src="<?= $h($base) ?>/assets/js/tms-dialog.js?v=20261016"></script>
<script src="<?= $h($base) ?>/assets/js/ticket-signature.js?v=<?= (int) @filemtime(__DIR__ . '/../../../public/assets/js/ticket-signature.js') ?>"></script>
</body>
</html>
