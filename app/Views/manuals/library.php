<?php
$h = static function ($value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};
$icons = [
    'Printer Issues' => 'fa-print',
    'PC and Laptop Issues' => 'fa-desktop',
    'Internet and Network Issues' => 'fa-wifi',
    'Email and Software Issues' => 'fa-envelope',
    'CCTV Issues' => 'fa-video',
    'Company Phone Issues' => 'fa-mobile-alt',
    'PTI System Issues' => 'fa-door-closed',
    'Basic Device Terms and Definitions' => 'fa-microchip',
    'IT Admin and Setup Guides' => 'fa-user-shield',
    'PMS and Maintenance Guides' => 'fa-tools',
    'General' => 'fa-book',
];
$total = array_sum(array_map('count', $groups));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>IT Manuals | StorageMart</title>
    <link href="<?= $h($base) ?>/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;700;800&display=swap" rel="stylesheet">
    <link rel="icon" href="<?= $h($base) ?>/assets/img/sm_favicon.png" type="image/x-icon">
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; background: #f4f5fa; color: #1f2937; font-family: "Montserrat", "Century Gothic", Arial, sans-serif; }
        .top { background: #fff; border-bottom: 2px solid #1F3A93; }
        .top-in { max-width: 1150px; margin: 0 auto; padding: 14px 16px; display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
        .top img { height: 40px; width: auto; }
        .top .kicker { margin-left: auto; color: #1F3A93; font-weight: 800; letter-spacing: .04em; font-size: .85rem; }
        .btn { display: inline-flex; align-items: center; gap: 6px; border: 0; border-radius: 8px; padding: 8px 12px; font-weight: 700; font-size: .8rem;
               background: #1F3A93; color: #fff; text-decoration: none; cursor: pointer; font-family: inherit; white-space: nowrap; }
        .btn.orange { background: #F26522; }
        .wrap { max-width: 1150px; margin: 0 auto; padding: 18px 16px 50px; }
        h1 { color: #F26522; font-weight: 800; font-size: clamp(1.6rem, 4vw, 2.4rem); margin: 6px 0 4px; }
        .lead { color: #1F3A93; margin: 0 0 16px; font-weight: 500; }
        .tools { display: flex; gap: 10px; flex-wrap: wrap; align-items: center; margin-bottom: 18px; }
        .search { flex: 1; min-width: 220px; position: relative; }
        .search input { width: 100%; padding: 11px 14px 11px 38px; border: 1px solid #c7cde3; border-radius: 10px; font: inherit; font-size: .95rem; }
        .search i { position: absolute; left: 13px; top: 50%; transform: translateY(-50%); color: #8a94b8; }
        .chips { display: flex; gap: 6px; flex-wrap: wrap; margin-bottom: 18px; }
        .chip { border: 1px solid #c7cde3; background: #fff; color: #1F3A93; border-radius: 999px; padding: 6px 12px; font: inherit; font-size: .78rem; font-weight: 700; cursor: pointer; }
        .chip.on { background: #1F3A93; color: #fff; border-color: #1F3A93; }
        .group { margin-bottom: 26px; }
        .group h2 { display: flex; align-items: center; gap: 10px; font-size: 1.05rem; color: #1F3A93; margin: 0 0 10px; }
        .group h2 .ico { width: 34px; height: 34px; border-radius: 50%; background: #F26522; color: #fff; display: inline-flex; align-items: center; justify-content: center; font-size: .9rem; }
        .group h2 small { color: #8a94b8; font-weight: 600; }
        .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 12px; }
        .card { position: relative; display: flex; flex-direction: column; background: #fff; border-radius: 14px; overflow: hidden; text-decoration: none; color: inherit;
                box-shadow: 0 2px 10px rgba(31,58,147,.08); border: 1px solid #e3e7f3; transition: transform .12s, box-shadow .12s; }
        .card:hover, .card:focus { transform: translateY(-2px); box-shadow: 0 8px 22px rgba(31,58,147,.16); outline: none; }
        .thumb { position: relative; aspect-ratio: 16 / 9; background: #fff; overflow: hidden; border-bottom: 1px solid #eef0f6; }
        .thumb .pink { position: absolute; right: -18%; bottom: -30%; width: 55%; aspect-ratio: 1; border-radius: 50%; background: linear-gradient(135deg, #FFF5F7, #FBD3DA 50%, #F49A88); }
        .thumb .logo { position: absolute; left: 5%; top: 6%; width: 30%; }
        .thumb .k { position: absolute; right: 5%; top: 9%; font-size: .5rem; font-weight: 800; color: #1F3A93; letter-spacing: .03em; }
        .thumb .line { position: absolute; left: 5%; right: 12%; top: 19%; height: 1px; background: linear-gradient(90deg, #1F3A93, rgba(31,58,147,0)); }
        .thumb .t { position: absolute; left: 8%; right: 8%; top: 28%; bottom: 14%; display: flex; align-items: center; justify-content: center; text-align: center;
                    color: #1F3A93; font-weight: 800; text-transform: uppercase; font-size: .82rem; line-height: 1.15; }
        .thumb .p { position: absolute; left: 5%; bottom: 5%; font-size: .5rem; font-weight: 700; color: #F26522; }
        .meta { padding: 10px 12px 12px; }
        .meta strong { display: block; font-size: .9rem; color: #1F3A93; }
        .meta span { display: block; font-size: .76rem; color: #6b7280; margin-top: 4px; }
        .badge { position: absolute; top: 8px; right: 8px; z-index: 2; background: #1F3A93; color: #fff; font-size: .62rem; font-weight: 800; border-radius: 999px; padding: 3px 8px; }
        .empty { display: none; text-align: center; color: #6b7280; padding: 30px 0; }
    </style>
</head>
<body>
<div class="top">
    <div class="top-in">
        <img src="<?= $h($base) ?>/assets/img/storagemart-logo-header.png" alt="StorageMart">
        <span class="kicker">IT DEPARTMENT MANUALS</span>
        <button type="button" class="btn" onclick="history.length > 1 ? history.back() : (location.href = '<?= $h($base) ?>/')"><i class="fas fa-arrow-left"></i> Back</button>
        <?php if ($canManage): ?>
            <a class="btn orange" href="<?= $h($base . $manageUrl) ?>"><i class="fas fa-edit"></i> Manage guides</a>
        <?php endif; ?>
    </div>
</div>
<div class="wrap">
    <h1>IT Manuals</h1>
    <p class="lead">Step-by-step guides from the IT Department. Open a guide to see its slides, or download it as PowerPoint.</p>

    <div class="tools">
        <label class="search"><i class="fas fa-search"></i>
            <input type="search" id="q" placeholder="Search, e.g. printer jam, no internet, Outlook" aria-label="Search the manuals" autocomplete="off">
        </label>
    </div>
    <div class="chips" id="chips">
        <button type="button" class="chip on" data-group="">All (<?= (int) $total ?>)</button>
        <?php foreach ($groups as $group => $items): ?>
            <button type="button" class="chip" data-group="<?= $h($group) ?>"><?= $h($group) ?> (<?= count($items) ?>)</button>
        <?php endforeach; ?>
    </div>

    <?php foreach ($groups as $group => $items): ?>
        <section class="group" data-group="<?= $h($group) ?>">
            <h2><span class="ico"><i class="fas <?= $icons[$group] ?? 'fa-book' ?>"></i></span><?= $h($group) ?> <small><?= count($items) ?></small></h2>
            <div class="grid">
                <?php foreach ($items as $manual):
                    $summary = trim((string) ($manual['main_issue'] ?? '')) ?: trim((string) ($manual['description'] ?? '')) ?: trim((string) ($manual['intro'] ?? ''));
                    $summary = ManualPresentation::plain(mb_strlen($summary) > 140 ? mb_substr($summary, 0, 137) . '...' : $summary);
                    $search = strtolower($manual['title'] . ' ' . ($manual['keywords'] ?? '') . ' ' . ($manual['categories'] ?? '') . ' ' . $group . ' ' . $summary);
                ?>
                    <a class="card" href="<?= $h($base) ?>/manuals/view?id=<?= (int) $manual['manual_id'] ?>" data-search="<?= $h($search) ?>">
                        <?php if (!empty($manual['it_only'])): ?><span class="badge">IT only</span><?php endif; ?>
                        <div class="thumb" aria-hidden="true">
                            <div class="pink"></div>
                            <img class="logo" src="<?= $h($base) ?>/assets/img/storagemart-logo-header.png" alt="">
                            <span class="k">IT DEPARTMENT MANUALS</span>
                            <span class="line"></span>
                            <span class="t"><?= $h($manual['title']) ?></span>
                            <span class="p">Page 01</span>
                        </div>
                        <div class="meta">
                            <strong><?= $h($manual['title']) ?></strong>
                            <?php if ($summary !== ''): ?><span><?= $h($summary) ?></span><?php endif; ?>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endforeach; ?>
    <p class="empty" id="empty">No guide matches your search. Try other words, or file a ticket and IT will help you.</p>
</div>
<script>
(function () {
    var q = document.getElementById('q'), chips = document.getElementById('chips'), empty = document.getElementById('empty');
    var group = '';
    function apply() {
        var words = q.value.toLowerCase().split(/\s+/).filter(Boolean), shown = 0;
        document.querySelectorAll('.group').forEach(function (sec) {
            var inGroup = !group || sec.getAttribute('data-group') === group, any = false;
            sec.querySelectorAll('.card').forEach(function (card) {
                var text = card.getAttribute('data-search');
                var ok = inGroup && words.every(function (w) { return text.indexOf(w) !== -1; });
                card.style.display = ok ? '' : 'none';
                if (ok) { any = true; shown++; }
            });
            sec.style.display = any ? '' : 'none';
        });
        empty.style.display = shown ? 'none' : 'block';
    }
    q.addEventListener('input', apply);
    chips.addEventListener('click', function (e) {
        var chip = e.target.closest('.chip');
        if (!chip) return;
        group = chip.getAttribute('data-group');
        chips.querySelectorAll('.chip').forEach(function (c) { c.classList.toggle('on', c === chip); });
        apply();
    });
})();
</script>
</body>
</html>
