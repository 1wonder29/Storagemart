<?php
$h = static function ($value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};
$manualId = (int) $manual['manual_id'];
$externalUrl = trim((string) ($manual['url'] ?? ''));
$total = count($slides);
$blocksHtml = static function (array $blocks) use ($h): string {
    $html = '';
    $inList = false;
    foreach ($blocks as $block) {
        if ($block['type'] === 'li' && !$inList) {
            $html .= '<ul>';
            $inList = true;
        } elseif ($block['type'] !== 'li' && $inList) {
            $html .= '</ul>';
            $inList = false;
        }
        $html .= $block['type'] === 'li' ? '<li>' . $h($block['text']) . '</li>' : '<p>' . $h($block['text']) . '</p>';
    }
    return $html . ($inList ? '</ul>' : '');
};
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $h($manual['title']) ?> | StorageMart IT Manuals</title>
    <link href="<?= $h($base) ?>/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;700;800&display=swap" rel="stylesheet">
    <link rel="icon" href="<?= $h($base) ?>/assets/img/sm_favicon.png" type="image/x-icon">
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; background: #eef0f6; color: #1f2937; font-family: "Montserrat", "Century Gothic", Arial, sans-serif; }
        .bar { position: sticky; top: 0; z-index: 5; display: flex; align-items: center; gap: 8px; flex-wrap: wrap; padding: 10px 16px; background: #1F3A93; color: #fff; }
        .bar strong { margin-right: auto; font-size: .95rem; min-width: 0; }
        .btn { display: inline-flex; align-items: center; gap: 6px; border: 0; border-radius: 8px; padding: 8px 12px; font-weight: 700; font-size: .8rem;
               background: #F26522; color: #fff; text-decoration: none; cursor: pointer; font-family: inherit; white-space: nowrap; }
        .btn.ghost { background: rgba(255,255,255,.16); }
        .wrap { max-width: 1100px; margin: 0 auto; padding: 16px 12px 40px; }
        .deck { position: relative; width: 100%; aspect-ratio: 16 / 9; border-radius: 12px; overflow: hidden; background: #fff;
                box-shadow: 0 10px 30px rgba(31,58,147,.18); cursor: pointer; }
        .deck:fullscreen { border-radius: 0; background: #111; }
        .stage { position: absolute; left: 0; top: 0; width: 1280px; height: 720px; transform-origin: 0 0; }

        /* One slide = 1280 x 720 px, same positions as the PowerPoint file. */
        .slide { position: absolute; left: 0; top: 0; width: 1280px; height: 720px; overflow: hidden; background: #fff; display: none;
                 color: #1F3A93; font-family: "Montserrat", "Century Gothic", Arial, sans-serif; }
        .slide.on { display: block; }
        .slide > * { position: absolute; }
        .pink { left: 965px; top: 330px; width: 420px; height: 420px; border-radius: 50%; background: linear-gradient(135deg, #FFF5F7 0%, #FBD3DA 50%, #F49A88 100%); }
        .orb { left: 32px; top: 160px; width: 322px; height: 322px; border-radius: 50%;
               background: linear-gradient(90deg, #F26522 0%, rgba(249,179,148,.6) 50%, rgba(255,255,255,0) 100%); }
        .logo { left: 64px; top: 34px; width: 232px; height: 58px; }
        .kicker { left: 830px; top: 66px; width: 330px; height: 30px; line-height: 30px; text-align: right; font-size: 16px; font-weight: 700; letter-spacing: .02em; }
        .menu { left: 1194px; top: 74px; width: 30px; height: 4px; background: #1F3A93; }
        .menu::after { content: ""; position: absolute; left: 0; top: 10px; width: 22px; height: 4px; background: #1F3A93; }
        .hline { left: 64px; top: 117px; width: 1060px; height: 2px; background: linear-gradient(90deg, #1F3A93 0%, rgba(31,58,147,.7) 50%, rgba(255,255,255,0) 100%); }
        .accent { left: 1132px; top: 208px; width: 72px; height: 6px; background: linear-gradient(90deg, #F26522, #FBD3C2); }
        .pg { left: 64px; top: 650px; height: 30px; line-height: 30px; font-size: 16px; font-weight: 700; color: #F26522; }
        .next { left: 1206px; top: 638px; width: 30px; height: 30px; border-radius: 50%; background: #4C3D99; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 13px; }
        .cover .next, .section .next { background: #1F3A93; }
        .h { left: 64px; top: 126px; width: 1060px; height: 80px; display: flex; align-items: center; font-size: 56px; font-weight: 800; color: #F26522; line-height: 1; }
        .h.long { font-size: 40px; }
        .body { left: 64px; top: 224px; width: 1140px; height: 390px; overflow: hidden; text-align: justify; line-height: 1.3; font-weight: 500; }
        .body p { margin: 0 0 .4em; }
        .body ul { margin: 0 0 .4em; padding-left: 1.1em; }
        .body li { margin: .1em 0; }
        .art { left: 125px; top: 205px; width: 500px; height: 430px; }
        .cover-title { left: 660px; top: 200px; width: 560px; height: 400px; display: flex; align-items: center; justify-content: center; text-align: center;
                       font-weight: 800; text-transform: uppercase; line-height: 1.05; overflow: hidden; }
        .issue-art { left: 120px; top: 230px; width: 400px; height: 330px; }
        .issue .h { left: 640px; top: 222px; width: 564px; height: 76px; justify-content: flex-end; }
        .issue .body { left: 640px; top: 306px; width: 564px; height: 300px; text-align: right; }
        .photo-box { left: 64px; top: 160px; width: 770px; height: 432px; border: 2px solid #1F3A93; background: #fff; display: flex; align-items: center; justify-content: center; padding: 4px; }
        .photo-box img { max-width: 100%; max-height: 100%; object-fit: contain; }
        .with-photo .h { left: 860px; top: 222px; width: 344px; height: 80px; justify-content: flex-end; font-size: 61px; }
        .with-photo .body { left: 870px; top: 312px; width: 334px; height: 300px; }
        .ty { left: 240px; width: 800px; height: 84px; line-height: 84px; text-align: center; font-size: 72px; }
        .ty.outline { color: transparent; -webkit-text-stroke: 2px #F26522; font-weight: 800; }
        .ty.mid b { font-weight: 800; } .ty.mid span { font-weight: 400; }

        .nav { display: flex; align-items: center; justify-content: center; gap: 14px; margin: 14px 0; }
        .nav button { width: 44px; height: 44px; border-radius: 50%; border: 0; background: #1F3A93; color: #fff; font-size: 1rem; cursor: pointer; }
        .nav button:disabled { opacity: .35; cursor: default; }
        .count { min-width: 70px; text-align: center; font-weight: 700; color: #1F3A93; }
        .card { background: #fff; border-radius: 12px; padding: 14px 16px; margin-top: 14px; box-shadow: 0 2px 8px rgba(0,0,0,.06); font-size: .92rem; }
        .card h2 { font-size: 1rem; margin: 0 0 8px; color: #1F3A93; }
        .pdf { width: 100%; height: 75vh; border: 0; border-radius: 10px; margin-top: 10px; background: #fff; }
        .muted { color: #6b7280; font-size: .85rem; }
        .hint { text-align: center; color: #6b7280; font-size: .78rem; margin: 0; }
    </style>
</head>
<body>
<?= ManualPresentation::svgDefs() ?>
<div class="bar">
    <button type="button" class="btn ghost" onclick="history.length > 1 ? history.back() : window.close()"><i class="fas fa-arrow-left"></i> Back</button>
    <strong><i class="fas fa-book-medical"></i> <?= $h($manual['title']) ?></strong>
    <a class="btn ghost" href="<?= $h($base) ?>/manuals"><i class="fas fa-th-large"></i> All manuals</a>
    <button type="button" class="btn ghost" id="fullBtn"><i class="fas fa-expand"></i> Full screen</button>
    <a class="btn" href="<?= $h($base) ?>/manuals/pptx?id=<?= $manualId ?>"><i class="fas fa-file-powerpoint"></i> Download PowerPoint</a>
    <?php if ($hasFile): ?>
        <a class="btn ghost" href="<?= $h($base) ?>/manuals/file?id=<?= $manualId ?>" target="_blank" rel="noopener">
            <i class="fas <?= $isPdf ? 'fa-file-pdf' : 'fa-file-download' ?>"></i> <?= $h($manual['file_name']) ?></a>
    <?php endif; ?>
</div>

<div class="wrap">
    <div class="deck" id="deck" tabindex="0" aria-roledescription="slideshow" aria-label="<?= $h($manual['title']) ?>">
        <div class="stage" id="stage">
            <?php foreach ($slides as $i => $slide):
                $kind = $slide['kind'];
                $hasPhoto = $kind === 'step' && !empty($slide['photo']);
                $classes = 'slide ' . $kind . ($hasPhoto ? ' with-photo' : '');
            ?>
                <section class="<?= $classes ?>" aria-label="<?= $h($slide['page']) ?>">
                    <div class="pink"></div>
                    <?php if ($kind === 'text' || ($kind === 'step' && !$hasPhoto)): ?><div class="orb"></div><?php endif; ?>
                    <img class="logo" src="<?= $h($base) ?>/assets/img/storagemart-logo-slide.png" alt="StorageMart">
                    <div class="kicker">IT DEPARTMENT MANUALS</div>
                    <div class="menu"></div>
                    <div class="hline"></div>

                    <?php if ($kind === 'cover' || $kind === 'section'): ?>
                        <div class="art"><?= ManualPresentation::monitorSvg() ?></div>
                        <div class="cover-title fit" style="font-size: <?= (int) $slide['size'] ?>px"><?= $h($slide['title']) ?></div>
                    <?php elseif ($kind === 'issue'): ?>
                        <div class="issue-art"><?= ManualPresentation::issueSvg() ?></div>
                        <div class="accent"></div>
                        <div class="h"><?= $h($slide['heading']) ?></div>
                        <div class="body fit" style="font-size: <?= (int) $slide['size'] ?>px"><?= $blocksHtml($slide['blocks']) ?></div>
                    <?php elseif ($kind === 'step'): ?>
                        <div class="accent"></div>
                        <?php if ($hasPhoto): ?>
                            <div class="photo-box"><img src="<?= $h($base) ?>/manuals/photo?id=<?= $manualId ?>&amp;step=<?= (int) $slide['index'] ?>"
                                                        alt="Photo for step <?= (int) $slide['number'] ?>" loading="lazy"></div>
                        <?php endif; ?>
                        <div class="h">STEP <?= (int) $slide['number'] ?></div>
                        <div class="body fit" style="font-size: <?= (int) $slide['size'] ?>px"><?= $blocksHtml($slide['blocks']) ?></div>
                    <?php elseif ($kind === 'text'): ?>
                        <div class="accent"></div>
                        <div class="h<?= mb_strlen($slide['heading']) > 34 ? ' long' : '' ?>"><?= $h($slide['heading']) ?></div>
                        <div class="body fit" style="font-size: <?= (int) $slide['size'] ?>px"><?= $blocksHtml($slide['blocks']) ?></div>
                    <?php else: ?>
                        <div class="ty outline" style="top: 250px">Thank You.</div>
                        <div class="ty mid" style="top: 332px"><b>Thank</b><span> You.</span></div>
                        <div class="ty outline" style="top: 414px">Thank You.</div>
                    <?php endif; ?>

                    <div class="pg"><?= $h($slide['page']) ?></div>
                    <?php if ($i < $total - 1): ?><div class="next" aria-hidden="true"><i class="fas fa-chevron-right"></i></div><?php endif; ?>
                </section>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="nav">
        <button type="button" id="prev" aria-label="Previous slide"><i class="fas fa-chevron-left"></i></button>
        <span class="count" id="count" aria-live="polite">1 / <?= $total ?></span>
        <button type="button" id="next" aria-label="Next slide"><i class="fas fa-chevron-right"></i></button>
    </div>
    <p class="hint">Tap the right side of the slide (or swipe) for the next page.</p>

    <?php if ($isPdf): ?>
        <div class="card">
            <h2><i class="fas fa-file-pdf"></i> Full manual (PDF)</h2>
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
    var deck = document.getElementById('deck'), stage = document.getElementById('stage');
    var slides = Array.prototype.slice.call(stage.querySelectorAll('.slide'));
    var prev = document.getElementById('prev'), next = document.getElementById('next'), count = document.getElementById('count');
    var index = 0;

    function scale() {
        var w = deck.clientWidth, h = deck.clientHeight;
        var k = Math.min(w / 1280, h / 720);
        stage.style.transform = 'translate(' + ((w - 1280 * k) / 2) + 'px,' + ((h - 720 * k) / 2) + 'px) scale(' + k + ')';
    }
    // Shrink the text of a slide until it fits its box.
    function fit(slide) {
        Array.prototype.forEach.call(slide.querySelectorAll('.fit'), function (box) {
            var size = parseFloat(box.style.fontSize) || 24;
            while (box.scrollHeight > box.clientHeight + 1 && size > 12) {
                size -= 1;
                box.style.fontSize = size + 'px';
            }
        });
    }
    function show(i) {
        index = Math.max(0, Math.min(slides.length - 1, i));
        slides.forEach(function (s, k) { s.classList.toggle('on', k === index); });
        fit(slides[index]);
        prev.disabled = index === 0;
        next.disabled = index === slides.length - 1;
        count.textContent = (index + 1) + ' / ' + slides.length;
    }
    prev.addEventListener('click', function () { show(index - 1); });
    next.addEventListener('click', function () { show(index + 1); });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'ArrowRight' || e.key === ' ' || e.key === 'PageDown') { e.preventDefault(); show(index + 1); }
        if (e.key === 'ArrowLeft' || e.key === 'PageUp') { show(index - 1); }
    });
    var startX = null;
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
    document.getElementById('fullBtn').addEventListener('click', function () {
        if (document.fullscreenElement) { document.exitFullscreen(); return; }
        if (deck.requestFullscreen) deck.requestFullscreen();
    });
    document.addEventListener('fullscreenchange', scale);
    window.addEventListener('resize', scale);
    scale();
    show(0);
    if (document.fonts && document.fonts.ready) {
        document.fonts.ready.then(function () { slides.forEach(function (s) { s.removeAttribute('data-fitted'); }); fit(slides[index]); });
    }
})();
</script>
</body>
</html>
