<?php
$h = static function ($value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};
$manualId = (int) $manual['manual_id'];
$externalUrl = trim((string) ($manual['url'] ?? ''));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $h($manual['title']) ?> | StorageMart IT Guide</title>
    <link href="<?= $h($base) ?>/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Nunito:400,700,800" rel="stylesheet">
    <link rel="icon" href="<?= $h($base) ?>/assets/img/sm_favicon.png" type="image/x-icon">
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; background: #e9ecf3; color: #1f2937; font-family: "Nunito", Arial, sans-serif; }
        .bar { position: sticky; top: 0; z-index: 5; display: flex; align-items: center; gap: 10px; flex-wrap: wrap; padding: 10px 16px; background: #1f3a93; color: #fff; }
        .bar strong { margin-right: auto; font-size: 1rem; }
        .btn { display: inline-flex; align-items: center; gap: 6px; border: 0; border-radius: 8px; padding: 8px 14px; font-weight: 700; font-size: .85rem;
               background: #f26522; color: #fff; text-decoration: none; cursor: pointer; font-family: inherit; }
        .btn.ghost { background: rgba(255,255,255,.15); }
        .wrap { max-width: 1000px; margin: 0 auto; padding: 16px 12px 40px; }
        .deck { position: relative; width: 100%; aspect-ratio: 16 / 9; border-radius: 14px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,.18); background: #1f3a93; }
        .slide { position: absolute; inset: 0; display: none; }
        .slide.on { display: block; }
        .slide.cover, .slide.end { background: #1f3a93; color: #fff; padding: 7% 6%; }
        .kicker { color: #fbb48a; font-weight: 800; letter-spacing: .08em; font-size: clamp(.7rem, 1.6vw, 1rem); }
        .accent { width: 12%; height: 6px; background: #f26522; margin: 2.5% 0 4%; }
        .cover h1, .end h1 { margin: 0 0 4%; font-size: clamp(1.6rem, 5.5vw, 3.6rem); line-height: 1.1; }
        .cover p, .end p { margin: 0; font-size: clamp(.95rem, 2.4vw, 1.6rem); color: #dce3f5; max-width: 85%; }
        .foot { position: absolute; left: 6%; bottom: 6%; font-size: clamp(.7rem, 1.4vw, .95rem); color: #afc0ea; }
        .slide.step { background: #fff; }
        .step-head { height: 16%; background: #1f3a93; color: #fff; display: flex; align-items: center; padding: 0 5%; font-weight: 800; font-size: clamp(.8rem, 2vw, 1.3rem); border-bottom: 6px solid #f26522; }
        .step-body { display: flex; align-items: center; gap: 4%; padding: 0 6%; height: 70%; }
        .num { flex: 0 0 auto; width: clamp(56px, 15vw, 150px); aspect-ratio: 1; border-radius: 50%; background: #f26522; color: #fff; display: flex; align-items: center; justify-content: center;
               font-weight: 800; font-size: clamp(1.6rem, 6vw, 4rem); }
        .step-text { font-size: clamp(1rem, 3.3vw, 2.3rem); font-weight: 800; line-height: 1.25; color: #1f2937; }
        .slide.step .foot { color: #6b7280; }
        .nav { display: flex; align-items: center; justify-content: center; gap: 14px; margin: 14px 0; }
        .nav button { width: 44px; height: 44px; border-radius: 50%; border: 0; background: #1f3a93; color: #fff; font-size: 1rem; cursor: pointer; }
        .nav button:disabled { opacity: .35; cursor: default; }
        .dots { display: flex; gap: 6px; }
        .dots span { width: 9px; height: 9px; border-radius: 50%; background: #c3cbe4; }
        .dots span.on { background: #f26522; width: 22px; border-radius: 6px; }
        .card { background: #fff; border-radius: 12px; padding: 14px 16px; margin-top: 14px; box-shadow: 0 2px 8px rgba(0,0,0,.06); font-size: .92rem; }
        .card h2 { font-size: 1rem; margin: 0 0 8px; }
        .card ol { margin: 0; padding-left: 20px; }
        .pdf { width: 100%; height: 75vh; border: 0; border-radius: 10px; margin-top: 10px; background: #fff; }
        .muted { color: #6b7280; font-size: .85rem; }
    </style>
</head>
<body>
<div class="bar">
    <button type="button" class="btn ghost" onclick="history.length > 1 ? history.back() : window.close()"><i class="fas fa-arrow-left"></i> Back</button>
    <strong><i class="fas fa-book-medical"></i> <?= $h($manual['title']) ?></strong>
    <a class="btn" href="<?= $h($base) ?>/manuals/pptx?id=<?= $manualId ?>"><i class="fas fa-file-powerpoint"></i> Download PowerPoint</a>
    <?php if ($hasFile): ?>
        <a class="btn ghost" href="<?= $h($base) ?>/manuals/file?id=<?= $manualId ?>" target="_blank" rel="noopener">
            <i class="fas <?= $isPdf ? 'fa-file-pdf' : 'fa-file-download' ?>"></i> Full manual (<?= $h($manual['file_name']) ?>)</a>
    <?php endif; ?>
</div>

<div class="wrap">
    <div class="deck" id="deck" tabindex="0" aria-roledescription="slideshow" aria-label="<?= $h($manual['title']) ?>">
        <?php foreach ($slides as $i => $slide): ?>
            <?php if ($slide['kind'] === 'step'): ?>
                <section class="slide step" aria-label="Step <?= (int) $slide['number'] ?>">
                    <div class="step-head">Step <?= (int) $slide['number'] ?> of <?= (int) $slide['total'] ?> · <?= $h($slide['title']) ?></div>
                    <div class="step-body">
                        <div class="num"><?= (int) $slide['number'] ?></div>
                        <div class="step-text"><?= $h($slide['text']) ?></div>
                    </div>
                    <div class="foot">StorageMart IT Helpdesk · Try each step, then check if it works.</div>
                </section>
            <?php else: ?>
                <section class="slide <?= $slide['kind'] === 'title' ? 'cover' : 'end' ?>">
                    <div class="kicker"><?= $slide['kind'] === 'title' ? 'STORAGEMART IT HELPDESK' : 'NEED MORE HELP?' ?></div>
                    <div class="accent"></div>
                    <h1><?= $h($slide['title']) ?></h1>
                    <p><?= $h($slide['text']) ?></p>
                    <div class="foot"><?= $slide['kind'] === 'title' ? 'Follow the steps on the next slides.' : 'Thank you!' ?></div>
                </section>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
    <div class="nav">
        <button type="button" id="prev" aria-label="Previous slide"><i class="fas fa-chevron-left"></i></button>
        <div class="dots" id="dots" aria-hidden="true"></div>
        <button type="button" id="next" aria-label="Next slide"><i class="fas fa-chevron-right"></i></button>
    </div>

    <?php if ($isPdf): ?>
        <div class="card">
            <h2><i class="fas fa-file-pdf"></i> Full manual</h2>
            <iframe class="pdf" src="<?= $h($base) ?>/manuals/file?id=<?= $manualId ?>" title="Full manual"></iframe>
        </div>
    <?php endif; ?>
    <?php if ($externalUrl !== ''): ?>
        <p class="muted">Online version: <a href="<?= $h($externalUrl) ?>" target="_blank" rel="noopener noreferrer"><?= $h($externalUrl) ?></a>
            (may ask for a login).</p>
    <?php endif; ?>
</div>

<script>
(function () {
    var slides = Array.prototype.slice.call(document.querySelectorAll('.slide'));
    var dots = document.getElementById('dots');
    var prev = document.getElementById('prev'), next = document.getElementById('next');
    var index = 0;
    slides.forEach(function () { dots.appendChild(document.createElement('span')); });
    function show(i) {
        index = Math.max(0, Math.min(slides.length - 1, i));
        slides.forEach(function (s, k) { s.classList.toggle('on', k === index); });
        Array.prototype.forEach.call(dots.children, function (d, k) { d.classList.toggle('on', k === index); });
        prev.disabled = index === 0;
        next.disabled = index === slides.length - 1;
    }
    prev.addEventListener('click', function () { show(index - 1); });
    next.addEventListener('click', function () { show(index + 1); });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'ArrowRight' || e.key === ' ') { e.preventDefault(); show(index + 1); }
        if (e.key === 'ArrowLeft') { show(index - 1); }
    });
    var deck = document.getElementById('deck'), startX = null;
    deck.addEventListener('touchstart', function (e) { startX = e.touches[0].clientX; }, { passive: true });
    deck.addEventListener('touchend', function (e) {
        if (startX === null) return;
        var dx = e.changedTouches[0].clientX - startX;
        if (Math.abs(dx) > 40) show(index + (dx < 0 ? 1 : -1));
        startX = null;
    });
    deck.addEventListener('click', function (e) {
        var r = deck.getBoundingClientRect();
        show(index + (e.clientX - r.left > r.width / 2 ? 1 : -1));
    });
    show(0);
})();
</script>
</body>
</html>
