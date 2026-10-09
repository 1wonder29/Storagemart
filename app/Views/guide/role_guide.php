<?php
/**
 * "How to use the TMS" for one role (slides from TmsGuide::slides), in the IT Department's guide
 * design with StorageMart's navy and orange. Shown the first time someone signs in (?embed=1
 * inside the welcome window), from the "?" button in the header and from the manuals library.
 * Expects $base, $embed, $role (guide shown), $viewerRole, $home, $roles (guides this person may open).
 */
$h = static function ($value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};
$info = TmsGuide::ROLES[$role];
$slides = TmsGuide::slides($role);
$total = count($slides);
$ownGuide = $role === $viewerRole;
$num = static function (?int $n): string {
    return $n ? '<b class="g-pin">' . $n . '</b>' : '';
};
// [label, pin] or 'label' -> [label, pin]
$item = static function ($value): array {
    return is_array($value) ? [(string) $value[0], isset($value[1]) ? (int) $value[1] : null] : [(string) $value, null];
};
$footer = static function (int $n) use ($h, $info): string {
    return '<div class="g-foot"><span>TMS User Guide · ' . $h($info['label']) . ' · StorageMart IT Department</span><span>' . $n . '</span></div>';
};
$try = static function (?array $link) use ($h, $base, $embed, $ownGuide): string {
    if (!$link || !$ownGuide || strpos($link[0], '…') !== false) {
        return '';
    }
    return '<a class="g-try" href="' . $h($base . $link[0]) . '"' . ($embed ? ' target="_top"' : '') . '><i class="fas fa-external-link-alt"></i> ' . $h($link[1]) . '</a>';
};

/** Picture of a TMS screen with numbered pins that match the steps. */
$screen = static function (array $sc) use ($h, $num, $item, $role): string {
    $url = 'tms.storagemart.com.ph' . ($sc['url'] ?? '/');
    $out = '<div class="g-browser"><div class="g-browser-bar"><span></span><span></span><span></span><em>' . $h($url) . '</em>'
        . (!empty($sc['login']) ? $num(1) : '') . '</div><div class="g-browser-body">';
    if (!empty($sc['login'])) {
        $out .= '<div class="m-login"><div class="m-logo">STORAGE<b>MART</b></div><div class="m-sub">Ticketing Management System</div>'
            . '<div class="m-label">Username ' . $num(2) . '</div><div class="m-input">storagemart.juandelacruz@gmail.com</div>'
            . '<div class="m-label">Password ' . $num(3) . '</div><div class="m-input">••••••••</div>'
            . '<div class="m-btn m-btn-wide">Sign in ' . $num(4) . '</div><div class="m-forgot">Forgot password?</div></div>';
        return $out . '</div></div>';
    }
    $top = $sc['top'] ?? [];
    $out .= '<div class="m-app"><div class="m-side"><div class="m-user"><i class="fas fa-user-circle"></i><span>Your name</span><small>Change password · Logout</small></div>';
    foreach (TmsGuide::MENUS[$role] as $menu) {
        $active = isset($sc['menu']) && $sc['menu'] === $menu;
        $out .= '<div class="m-menu' . ($active ? ' on' : '') . '">' . $h($menu) . '</div>';
    }
    $out .= '</div><div class="m-main"><div class="m-top"><span class="m-ico"><i class="fas fa-bars"></i>' . $num($top['menu'] ?? null) . '</span>'
        . '<span class="m-brand">STORAGE<b>MART</b></span><span class="m-icons">';
    foreach (['bell' => 'fa-bell', 'moon' => 'fa-moon', 'bullhorn' => 'fa-bullhorn', 'chat' => 'fa-comment-dots', 'question' => 'fa-question-circle'] as $key => $fa) {
        $out .= '<span class="m-ico' . (isset($top[$key]) ? ' hot' : '') . '"><i class="fas ' . $fa . '"></i>' . $num($top[$key] ?? null) . '</span>';
    }
    $out .= '</span></div>';
    $out .= '<div class="m-hero"><span>' . $h($sc['title'] ?? '') . '</span>';
    if (!empty($sc['buttons'])) {
        $out .= '<div class="m-hero-btns">';
        foreach ($sc['buttons'] as $button) {
            [$label, $pin] = $item($button);
            $out .= '<span class="m-hbtn">' . $h($label) . $num($pin) . '</span>';
        }
        $out .= '</div>';
    }
    $out .= '</div>';
    foreach ($sc['blocks'] ?? [] as $block) {
        $kind = $block[0];
        $pin = isset($block[2]) && !is_array($block[2]) ? (int) $block[2] : null;
        switch ($kind) {
            case 'filters':
                $out .= '<div class="m-row m-filters">';
                foreach ($block[1] as $f) {
                    [$label, $p] = $item($f);
                    $out .= '<div class="m-filter"><div class="m-label">' . $h($label) . $num($p) . '</div><div class="m-select"></div></div>';
                }
                $out .= $num($pin) . '</div>';
                break;
            case 'table':
                $out .= '<div class="m-table"><div class="m-th">';
                foreach ($block[1] as $col) {
                    $out .= '<span>' . $h($col) . '</span>';
                }
                $out .= $num($pin) . '</div>';
                for ($r = 0; $r < 3; $r++) {
                    $out .= '<div class="m-tr">' . str_repeat('<span><i></i></span>', count($block[1])) . '</div>';
                }
                $out .= '</div>';
                break;
            case 'form':
                $out .= '<div class="m-form">' . ($pin ? '<div class="m-form-pin">' . $num($pin) . '</div>' : '');
                foreach ($block[1] as $f) {
                    [$label, $p] = $item($f);
                    $out .= '<div class="m-label">' . $h($label) . $num($p) . '</div><div class="m-input"></div>';
                }
                $out .= '</div>';
                break;
            case 'cards':
                $out .= '<div class="m-row">';
                foreach ($block[1] as $i => $label) {
                    $out .= '<div class="m-card"><b>' . ($label === '' ? '&nbsp;' : ($i + 1) * 3) . '</b>' . $h($label) . ($i === 0 ? $num($pin) : '') . '</div>';
                }
                $out .= '</div>';
                break;
            case 'tiles':
                $out .= '<div class="m-row m-tiles">';
                foreach ($block[1] as $i => $label) {
                    $out .= '<div class="m-card m-tile"><i class="fas fa-file-powerpoint"></i>' . $h($label) . ($i === 0 ? $num($pin) : '') . '</div>';
                }
                $out .= '</div>';
                break;
            case 'buttons':
                $out .= '<div class="m-btns">';
                foreach ($block[1] as $b) {
                    [$label, $p] = $item($b);
                    $out .= '<span class="m-btn">' . $h($label) . $num($p) . '</span>';
                }
                $out .= $num($pin) . '</div>';
                break;
            case 'chat':
                $p = isset($block[1]) ? (int) $block[1] : null;
                $out .= '<div class="m-chat"><div class="m-bubble">Hi! Is the printer showing an error code? ' . $num($p) . '</div>'
                    . '<div class="m-bubble me">Yes: Paper Jam. Photo attached.</div><div class="m-input">Type your message… (Ctrl+V pastes a screenshot)</div></div>';
                break;
            case 'note':
                $out .= '<div class="m-note">' . $h($block[1]) . $num($pin) . '</div>';
                break;
            case 'switch':
                $out .= '<div class="m-switch"><span class="m-toggle"></span>' . $h($block[1]) . $num($pin) . '</div>';
                break;
            case 'list':
                $out .= '<div class="m-list">';
                foreach ($block[1] as $line) {
                    $out .= '<div>' . $h($line) . '</div>';
                }
                $out .= $num($pin) . '</div>';
                break;
        }
    }
    return $out . '</div></div></div></div>';
};

$statuses = [
    ['Open', 'p-open', 'Just filed; waiting to be assigned to an IT person.'],
    ['In Progress', 'p-prog', 'Assigned to an IT person, who is working on it.'],
    ['Pending', 'p-pend', 'On hold, for example waiting for a part, a supplier or your reply.'],
    ['Resolved', 'p-res', 'IT finished. Sign the report and rate the service.'],
    ['Closed', 'p-closed', 'Finished and closed; no more changes.'],
    ['Cancelled', 'p-cancel', 'Cancelled with a reason; no work needed.'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $h(TmsGuide::title($role)) ?> | StorageMart</title>
    <link href="<?= $h($base) ?>/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="icon" href="<?= $h($base) ?>/assets/img/sm_favicon.png" type="image/x-icon">
    <style>
        :root { --navy: #19398a; --navy-dark: #0f2563; --blue: #2661d4; --orange: #f85f00; --orange-soft: #fff1e8; --ink: #1f2937; --muted: #4b5563; }
        * { box-sizing: border-box; }
        html, body { margin: 0; height: 100%; }
        body { background: #eef1f8; color: var(--ink); font-family: "Poppins", Arial, sans-serif; display: flex; flex-direction: column; }
        .bar { display: flex; align-items: center; flex-wrap: wrap; gap: 10px; padding: 10px 16px; background: var(--navy); color: #fff; border-bottom: 4px solid var(--orange); }
        .bar strong { margin-right: auto; }
        .bar a, .bar button, .bar select { color: #fff; background: rgba(255,255,255,.15); border: 0; border-radius: 8px; padding: 7px 12px; font: inherit; font-size: .82rem; font-weight: 600; text-decoration: none; cursor: pointer; }
        .bar select option { color: #111; }
        .wrap { flex: 1; min-height: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 12px; }
        .deck { position: relative; width: 100%; max-width: 1280px; aspect-ratio: 16 / 9; max-height: calc(100% - 64px); overflow: hidden; border-radius: 12px; background: #fff;
                box-shadow: 0 10px 30px rgba(25, 57, 138, .18); }
        .stage { position: absolute; left: 0; top: 0; width: 1280px; height: 720px; transform-origin: 0 0; }
        .nav { display: flex; align-items: center; justify-content: center; gap: 14px; padding: 10px 0 0; }
        .nav button { width: 42px; height: 42px; border-radius: 50%; border: 0; background: var(--navy); color: #fff; cursor: pointer; font-size: 1rem; }
        .nav button:disabled { opacity: .35; cursor: default; }
        .nav .count { min-width: 64px; text-align: center; font-weight: 800; color: var(--navy); }

        /* ---- slides (1280 x 720) ---- */
        .s { position: absolute; inset: 0; display: none; background: #fff; padding: 52px 64px 64px; overflow: hidden; }
        .s.on { display: block; }
        .s::before { content: ""; position: absolute; left: 0; top: 0; width: 100%; height: 8px; background: linear-gradient(90deg, var(--navy) 0 70%, var(--orange) 70%); }
        .s h2 { margin: 0 0 6px; font-size: 40px; font-weight: 800; color: var(--navy); letter-spacing: -.01em; }
        .s .lead { margin: 0 0 22px; font-size: 20px; color: #374151; }
        .g-foot { position: absolute; left: 64px; right: 64px; bottom: 22px; display: flex; justify-content: space-between; font-size: 13px; color: #6b7280; }
        .g-grid { display: grid; gap: 20px; }
        .g-card { background: #f2f5fb; border-radius: 16px; padding: 22px; border: 2px solid transparent; }
        .g-card.me { border-color: var(--orange); background: #fff; box-shadow: 0 10px 24px rgba(248, 95, 0, .15); }
        .g-card h4 { margin: 12px 0 8px; font-size: 20px; color: var(--navy); }
        .g-card p { margin: 0; font-size: 15px; line-height: 1.45; color: #374151; }
        .g-ico { width: 52px; height: 52px; border-radius: 50%; display: flex; align-items: center; justify-content: center; background: var(--navy); color: #fff; font-size: 21px; }
        .g-ico.o { background: var(--orange); }
        .g-n { width: 34px; height: 34px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; background: var(--orange); color: #fff; font-weight: 800; flex: 0 0 auto; }
        .g-steps { display: flex; flex-direction: column; gap: 14px; }
        .g-step { display: flex; gap: 14px; align-items: flex-start; }
        .g-step h5 { margin: 3px 0 3px; font-size: 19px; color: var(--navy); }
        .g-step p { margin: 0; font-size: 15px; color: #374151; line-height: 1.42; }
        .g-steps.many { gap: 9px; } .g-steps.many h5 { font-size: 17px; margin-top: 4px; } .g-steps.many p { font-size: 14px; }
        .g-note { margin-top: 16px; padding: 11px 15px; border-radius: 12px; background: var(--orange-soft); color: #9a3412; font-size: 14.5px; }
        .g-try { display: inline-flex; align-items: center; gap: 6px; margin-top: 14px; font-size: 15px; font-weight: 700; color: var(--navy); }
        .g-two { display: grid; grid-template-columns: 1fr 1.08fr; gap: 36px; align-items: start; }
        .g-pills { display: flex; flex-wrap: wrap; gap: 10px; }
        .g-pill { border-radius: 999px; padding: 7px 16px; font-weight: 800; font-size: 15px; color: #fff; white-space: nowrap; }
        .p-open { background: #2661d4; } .p-prog { background: #19398a; } .p-pend { background: #f5a623; color: #3b2a00; }
        .p-res { background: #1a7f37; } .p-closed { background: #4b5563; } .p-cancel { background: #c0392b; }
        .g-status { display: grid; grid-template-columns: 200px 1fr; gap: 20px 26px; align-items: center; font-size: 22px; margin-top: 10px; }
        .g-status .g-pill { font-size: 18px; padding: 9px 22px; }

        /* screen pictures */
        .g-browser { border-radius: 14px; overflow: hidden; background: #fff; box-shadow: 0 14px 34px rgba(25, 57, 138, .2); border: 1px solid #dfe3ee; }
        .g-browser-bar { display: flex; align-items: center; gap: 6px; padding: 8px 12px; background: #eceff6; }
        .g-browser-bar span { width: 10px; height: 10px; border-radius: 50%; background: #c9cfdd; }
        .g-browser-bar em { margin-left: 10px; flex: 1; background: #fff; border-radius: 999px; padding: 4px 12px; font-style: normal; font-size: 12px; color: #6b7280; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .g-browser-body { position: relative; min-height: 380px; max-height: 452px; overflow: hidden; background: #f6f7fb; font-size: 11px; color: #4b5563; }
        .g-pin { display: inline-flex; width: 21px; height: 21px; margin: 0 3px; border-radius: 50%; background: var(--orange); color: #fff; font-size: 11.5px; font-style: normal; font-weight: 800;
                 align-items: center; justify-content: center; vertical-align: middle; box-shadow: 0 0 0 3px rgba(248, 95, 0, .28); flex: 0 0 auto; }
        .m-app { display: flex; min-height: 380px; }
        .m-side { width: 128px; flex: 0 0 auto; background: linear-gradient(185deg, var(--navy-dark), var(--navy)); color: rgba(255,255,255,.85); padding: 8px 6px; }
        .m-user { display: flex; flex-direction: column; align-items: center; text-align: center; padding: 4px 0 8px; margin-bottom: 6px; border-bottom: 1px solid rgba(255,255,255,.15); }
        .m-user i { font-size: 22px; } .m-user span { font-weight: 700; font-size: 10.5px; } .m-user small { font-size: 8.5px; opacity: .8; }
        .m-menu { padding: 5px 7px; border-radius: 6px; font-size: 10px; margin-bottom: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .m-menu.on { background: rgba(255,255,255,.18); color: #fff; font-weight: 700; box-shadow: inset 3px 0 0 var(--orange); }
        .m-main { flex: 1; min-width: 0; padding: 0 10px 10px; }
        .m-top { display: flex; align-items: center; gap: 6px; height: 34px; margin: 0 -10px 8px; padding: 0 8px; background: #fff; border-bottom: 2px solid var(--orange); }
        .m-brand { font-weight: 800; color: var(--navy); font-size: 11px; letter-spacing: .02em; } .m-brand b { color: var(--orange); }
        .m-icons { margin-left: auto; display: flex; gap: 2px; }
        .m-ico { display: inline-flex; align-items: center; color: #9aa1b2; font-size: 12px; padding: 0 3px; }
        .m-ico.hot { color: var(--navy); }
        .m-hero { background: linear-gradient(135deg, var(--navy-dark), var(--navy) 55%, var(--blue)); color: #fff; border-radius: 9px; padding: 9px 11px; font-weight: 800; font-size: 13px; margin-bottom: 8px; border-bottom: 3px solid var(--orange); }
        .m-hero-btns { display: flex; flex-wrap: wrap; gap: 5px; margin-top: 6px; }
        .m-hbtn { display: inline-flex; align-items: center; background: rgba(255,255,255,.18); border-radius: 999px; padding: 3px 9px; font-size: 9.5px; font-weight: 700; }
        .m-row { display: flex; gap: 7px; margin-bottom: 8px; align-items: flex-end; }
        .m-card { flex: 1; background: #fff; border-radius: 8px; padding: 7px 8px; box-shadow: 0 2px 6px rgba(0,0,0,.05); font-size: 9.5px; color: #6b7280; min-width: 0; }
        .m-card b { display: block; font-size: 16px; color: var(--navy); }
        .m-btns { display: flex; flex-wrap: wrap; gap: 5px; margin-bottom: 8px; align-items: center; }
        .m-btn { display: inline-flex; align-items: center; border-radius: 999px; padding: 4px 10px; font-size: 10px; font-weight: 700; color: #fff; background: var(--navy); }
        .m-btns .m-btn:nth-child(2n) { background: var(--orange); }
        .m-btn-wide { display: flex; justify-content: center; margin-top: 8px; background: var(--orange); }
        .m-filter { flex: 1; min-width: 0; }
        .m-select { height: 20px; background: #fff; border: 1px solid #d6dbe7; border-radius: 6px; }
        .m-label { display: flex; align-items: center; font-size: 9px; font-weight: 800; color: #6b7280; text-transform: uppercase; letter-spacing: .04em; margin: 4px 0 3px; }
        .m-input { background: #fff; border: 1px solid #d6dbe7; border-radius: 6px; padding: 5px 8px; min-height: 20px; font-size: 10px; color: #6b7280; margin-bottom: 4px; }
        .m-form { position: relative; background: #fff; border-radius: 9px; padding: 6px 10px 8px; margin-bottom: 8px; box-shadow: 0 2px 6px rgba(0,0,0,.05); }
        .m-form-pin { position: absolute; right: 6px; top: 6px; }
        .m-table { background: #fff; border-radius: 9px; padding: 6px; margin-bottom: 8px; box-shadow: 0 2px 6px rgba(0,0,0,.05); }
        .m-th, .m-tr { display: flex; gap: 6px; align-items: center; padding: 4px 4px; }
        .m-th { font-weight: 800; color: var(--navy); font-size: 9.5px; border-bottom: 1px solid #e5e7eb; }
        .m-th span, .m-tr span { flex: 1; min-width: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .m-tr i { display: block; height: 7px; border-radius: 4px; background: #e3e7f0; }
        .m-chat { background: #fff; border-radius: 9px; padding: 8px; margin-bottom: 8px; box-shadow: 0 2px 6px rgba(0,0,0,.05); }
        .m-bubble { display: inline-flex; align-items: center; max-width: 85%; background: #eef2ff; border-radius: 10px; padding: 5px 9px; margin-bottom: 5px; font-size: 10px; }
        .m-bubble.me { display: flex; width: fit-content; margin-left: auto; background: var(--navy); color: #fff; }
        .m-note { display: flex; align-items: center; justify-content: space-between; gap: 6px; background: #fff; border-left: 4px solid var(--orange); border-radius: 8px; padding: 9px 10px; margin-bottom: 8px; font-weight: 700; color: var(--ink); font-size: 10.5px; }
        .m-switch { display: flex; align-items: center; gap: 8px; background: #fff; border-radius: 8px; padding: 8px 10px; margin-bottom: 8px; font-weight: 700; color: var(--navy); }
        .m-toggle { width: 26px; height: 14px; border-radius: 7px; background: #1a7f37; position: relative; }
        .m-toggle::after { content: ""; position: absolute; right: 2px; top: 2px; width: 10px; height: 10px; border-radius: 50%; background: #fff; }
        .m-list { background: #fff; border-radius: 9px; padding: 6px 10px; margin-bottom: 8px; }
        .m-list div { padding: 4px 0; border-bottom: 1px dashed #e5e7eb; font-size: 10px; }
        .m-list div:last-of-type { border-bottom: 0; }
        .m-login { width: 270px; margin: 30px auto; background: #fff; border-radius: 14px; padding: 18px 20px; box-shadow: 0 10px 24px rgba(25,57,138,.12); }
        .m-logo { font-weight: 800; color: var(--navy); font-size: 20px; text-align: center; } .m-logo b { color: var(--orange); }
        .m-sub { text-align: center; font-size: 10px; margin-bottom: 10px; }
        .m-forgot { text-align: center; font-size: 10px; color: var(--blue); margin-top: 8px; }
        .m-tile { display: flex; flex-direction: column; gap: 4px; min-height: 58px; font-weight: 700; color: var(--navy); }
        .m-tile i { color: var(--orange); font-size: 14px; }

        /* cover / thanks */
        .s.cover, .s.thanks { background: linear-gradient(135deg, var(--navy-dark), var(--navy) 60%, var(--blue)); color: #fff; }
        .s.cover { padding: 100px 90px; }
        .cover .kick { color: #ffb38a; font-weight: 800; letter-spacing: .06em; font-size: 17px; }
        .cover h1 { font-size: 60px; line-height: 1.05; margin: 16px 0 10px; font-weight: 800; max-width: 680px; }
        .cover .role { display: inline-block; background: var(--orange); border-radius: 10px; padding: 8px 18px; font-weight: 800; font-size: 24px; margin-bottom: 20px; }
        .cover p { font-size: 23px; max-width: 600px; margin: 0 0 30px; color: #e5e7f5; }
        .cover .url, .thanks .url { display: inline-flex; align-items: center; gap: 10px; background: rgba(255,255,255,.14); border-radius: 999px; padding: 11px 24px; font-weight: 700; font-size: 19px; }
        .cover .by { margin-top: 28px; font-size: 16px; opacity: .85; }
        .cover .circle { position: absolute; right: 120px; top: 150px; width: 300px; height: 300px; border-radius: 50%; background: var(--orange); display: flex; align-items: center; justify-content: center; font-size: 120px;
                         box-shadow: 0 0 0 26px rgba(255,255,255,.08); }
        .thanks { text-align: center; padding-top: 200px !important; }
        .thanks h1 { font-size: 76px; margin: 0 0 14px; }
        .thanks p { font-size: 25px; color: #e5e7f5; margin: 0 0 28px; }
        .thanks .go { display: inline-block; margin-top: 28px; border: 0; border-radius: 999px; background: var(--orange); color: #fff; padding: 14px 34px; font: inherit; font-weight: 800; font-size: 21px; cursor: pointer; }
        .bad, .good { border-radius: 14px; padding: 16px 18px; }
        .bad { background: #fdecea; } .good { background: #e8f6ee; }
        .bad h5, .good h5 { margin: 0 0 6px; font-size: 18px; }
        .bad h5 { color: #b91c1c; } .good h5 { color: #13855c; }
        .chk { font-size: 15px; margin: 4px 0; }
        .flow { display: grid; grid-template-columns: repeat(5, 1fr); gap: 14px; margin-top: 30px; }
        .flow .g-card { position: relative; padding-top: 26px; }
        .flow .who { font-size: 12px; font-weight: 800; letter-spacing: .05em; color: var(--orange); text-transform: uppercase; }
        .flow .g-n { position: absolute; top: -16px; left: 20px; }

        @media print {
            @page { size: 1280px 720px; margin: 0; }
            html, body { height: auto; background: #fff; display: block; }
            .bar, .nav { display: none !important; }
            .wrap { display: block; padding: 0; }
            .deck { max-width: none; width: 1280px; aspect-ratio: auto; max-height: none; overflow: visible; box-shadow: none; border-radius: 0; }
            .stage { position: static; transform: none !important; height: auto; }
            .s { display: block !important; position: relative; width: 1280px; height: 720px; page-break-after: always; break-after: page; }
            * { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body>
<?php if (!$embed): ?>
    <div class="bar">
        <strong><i class="fas fa-question-circle"></i> <?= $h(TmsGuide::title($role)) ?></strong>
        <?php if (count($roles) > 1): ?>
            <select id="roleSwitch" aria-label="Guide for another role">
                <?php foreach ($roles as $key): ?>
                    <option value="<?= $h($key) ?>" <?= $key === $role ? 'selected' : '' ?>><?= $h(TmsGuide::ROLES[$key]['label']) ?></option>
                <?php endforeach; ?>
            </select>
        <?php endif; ?>
        <button type="button" onclick="window.print()"><i class="fas fa-print"></i> Print / Save as PDF</button>
        <a href="<?= $h($base) ?>/manuals"><i class="fas fa-book"></i> Manuals library</a>
        <a href="<?= $h($base . $home) ?>"><i class="fas fa-home"></i> My dashboard</a>
    </div>
<?php endif; ?>
<div class="wrap">
    <div class="deck" id="deck">
        <div class="stage" id="stage">
<?php foreach ($slides as $index => $slide):
    $n = $index + 1;
    $type = $slide['type'];
?>
<?php if ($type === 'cover'): ?>
            <section class="s cover<?= $n === 1 ? ' on' : '' ?>">
                <div class="kick">STORAGEMART · IT DEPARTMENT</div>
                <h1>Ticketing Management System</h1>
                <div class="role"><i class="fas <?= $h($info['icon']) ?>"></i> <?= $h($info['label']) ?></div>
                <p><?= $h($slide['subtitle']) ?></p>
                <span class="url"><i class="fas fa-globe"></i> tms.storagemart.com.ph</span>
                <div class="by">Presented by the IT Team</div>
                <div class="circle"><i class="fas <?= $h($info['icon']) ?>"></i></div>
            </section>
<?php elseif ($type === 'cards'): ?>
            <section class="s">
                <h2><?= $h($slide['title']) ?></h2>
                <p class="lead"><?= $h($slide['lead']) ?></p>
                <div class="g-grid" style="grid-template-columns:repeat(<?= count($slide['cards']) ?>,1fr)">
                    <?php foreach ($slide['cards'] as $card): ?>
                        <div class="g-card"><div class="g-ico<?= $card[3] ? ' o' : '' ?>"><i class="fas <?= $h($card[0]) ?>"></i></div><h4><?= $h($card[1]) ?></h4><p><?= $h($card[2]) ?></p></div>
                    <?php endforeach; ?>
                </div>
                <div class="g-note" style="margin-top:28px"><i class="fas fa-lightbulb"></i> Each part has its own slide in this guide, with the steps and a picture of the screen. The orange numbers on the pictures match the steps.</div>
                <?= $footer($n) ?>
            </section>
<?php elseif ($type === 'roles'): ?>
            <section class="s">
                <h2>Who uses the TMS?</h2>
                <p class="lead">Every account has a role. The menu and this guide follow your role.</p>
                <div class="g-grid" style="grid-template-columns:repeat(4,1fr);gap:14px">
                    <?php
                    $roleTexts = [
                        'EMPLOYEE' => 'Files tickets, tracks them, signs the technical report and rates the service.',
                        'HEAD' => 'Files tickets, follows the department\'s tickets and posts department announcements.',
                        'HR' => 'Manages employee records, item inventory and accountability forms; files tickets.',
                        'IT' => 'Works on assigned tickets, chats, sends guides and makes the technical report.',
                        'AOM' => 'Oversees the employees and tickets of assigned branches; files for employees.',
                        'OM' => 'Follows operations tickets, employees, assets and AOM branches.',
                        'HOM' => 'Leads all operations: branches, transfers, tickets and announcements.',
                        'ADMIN' => 'Manages accounts, assets, ticket rules and the system; reviews the audit trail.',
                    ];
                    foreach (TmsGuide::ROLES as $key => $r): ?>
                        <div class="g-card<?= $key === $role ? ' me' : '' ?>" style="padding:16px">
                            <div class="g-ico<?= $key === $role ? ' o' : '' ?>" style="width:42px;height:42px;font-size:17px"><i class="fas <?= $h($r['icon']) ?>"></i></div>
                            <h4 style="font-size:16.5px;margin:8px 0 5px"><?= $h($r['label']) ?><?= $key === $role ? ' (you)' : '' ?></h4>
                            <p style="font-size:13px"><?= $h($roleTexts[$key]) ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?= $footer($n) ?>
            </section>
<?php elseif ($type === 'flow'): ?>
            <section class="s">
                <h2>How a ticket moves</h2>
                <p class="lead">From the problem to the signed report.</p>
                <div class="flow">
                    <?php foreach ([
                        ['Requester', 'File a ticket', 'The ticket is saved, and a troubleshooting guide is sent in its chat.'],
                        ['TMS / Admin', 'Assign', 'It goes to the IT person of the branch\'s zone, or the Admin assigns it.'],
                        ['IT Personnel', 'Work on it', 'In Progress: IT chats with the requester and fixes the problem.'],
                        ['IT Personnel', 'Resolve', 'IT clicks Mark as Done; the technical report is made.'],
                        ['Requester', 'Close the loop', 'The requester signs the report and rates the service.'],
                    ] as $i => $step): ?>
                        <div class="g-card"><span class="g-n"><?= $i + 1 ?></span><div class="who"><?= $h($step[0]) ?></div><h4><?= $h($step[1]) ?></h4><p><?= $h($step[2]) ?></p></div>
                    <?php endforeach; ?>
                </div>
                <div class="g-note" style="margin-top:30px"><i class="fas fa-bell"></i> You get a notification (bell) at every step: assigned, new chat messages, resolved.</div>
                <?= $footer($n) ?>
            </section>
<?php elseif ($type === 'statuses'): ?>
            <section class="s">
                <h2>Ticket statuses</h2>
                <p class="lead">The color of a ticket tells you where it stands.</p>
                <div class="g-status">
                    <?php foreach ($statuses as $st): ?>
                        <span><span class="g-pill <?= $st[1] ?>"><?= $h($st[0]) ?></span></span><span><?= $h($st[2]) ?></span>
                    <?php endforeach; ?>
                </div>
                <?= $footer($n) ?>
            </section>
<?php elseif ($type === 'priority'): ?>
            <section class="s">
                <h2>Choose the right priority</h2>
                <p class="lead">IT works on the most urgent tickets first.</p>
                <div class="g-grid" style="grid-template-columns:repeat(4,1fr)">
                    <?php foreach ([
                        ['Low', '#1a7f37', 'Not urgent; can wait for regular maintenance.', ['Faded or streaky prints', 'A slow PC that still works']],
                        ['Medium', '#f5a623', 'Normal problem that slows your work.', ['Outlook won\'t send or receive', 'Printer offline']],
                        ['High', '#f85f00', 'Urgent; work is stopped.', ['No internet at the branch', 'PTI / StorLogix offline']],
                        ['Critical', '#c0392b', 'Big outage hitting operations or many departments.', ['Network down in the office', 'A system down for many people']],
                    ] as $p): ?>
                        <div class="g-card" style="border-top:6px solid <?= $p[1] ?>">
                            <h4 style="color:<?= $p[1] ?>;margin-top:0"><?= $h($p[0]) ?></h4><p><?= $h($p[2]) ?></p>
                            <p style="margin-top:12px;font-size:12px;font-weight:800;color:#6b7280">EXAMPLES</p>
                            <?php foreach ($p[3] as $ex): ?><p>• <?= $h($ex) ?></p><?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="g-note"><i class="fas fa-info-circle"></i> Be honest with the level. If everything is High or Critical, the truly urgent problems wait longer. IT may change the priority.</div>
                <?= $footer($n) ?>
            </section>
<?php elseif ($type === 'write'): ?>
            <section class="s">
                <h2>Write a clear subject and description</h2>
                <p class="lead">IT can help faster when the ticket answers four questions.</p>
                <div class="g-two" style="align-items:stretch">
                    <div>
                        <div class="bad"><h5><i class="fas fa-times-circle"></i> Too vague</h5><p style="margin:0;font-size:17px">"Printer not working."</p>
                            <p class="chk" style="color:#b91c1c">✗ Which printer &nbsp; ✗ Where &nbsp; ✗ What error &nbsp; ✗ Since when</p></div>
                        <div class="good" style="margin-top:16px"><h5><i class="fas fa-check-circle"></i> Clear and complete</h5>
                            <p style="margin:0 0 4px;font-size:15px"><b>Subject:</b> Brother MFC-T810W at the front desk shows Paper Jam</p>
                            <p style="margin:0;font-size:15px"><b>Description:</b> Since 9 AM. I opened the tray and removed the paper, but the error is still there. Photo attached.</p></div>
                    </div>
                    <div class="g-steps">
                        <?php foreach ([['What is wrong?', 'Describe the problem and copy any error message exactly.'],
                            ['Which device, where?', 'Brand / model and location, e.g. front desk, 2nd floor.'],
                            ['Since when?', 'When it started or how often it happens.'],
                            ['What did you try?', 'Restarting, checking cables, the guide\'s steps.']] as $i => $q): ?>
                            <div class="g-step"><span class="g-n"><?= $i + 1 ?></span><div><h5><?= $h($q[0]) ?></h5><p><?= $h($q[1]) ?></p></div></div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?= $footer($n) ?>
            </section>
<?php elseif ($type === 'steps'):
    $hasScreen = !empty($slide['screen']);
    $many = count($slide['steps']) > 4;
?>
            <section class="s">
                <h2><?= $h($slide['title']) ?></h2>
                <p class="lead"><?= $h($slide['lead']) ?></p>
                <div class="<?= $hasScreen ? 'g-two' : '' ?>">
                    <div>
                        <div class="g-steps<?= $many ? ' many' : '' ?>">
                            <?php foreach ($slide['steps'] as $i => $step): ?>
                                <div class="g-step"><span class="g-n"><?= $i + 1 ?></span><div><h5><?= $h($step[0]) ?></h5><p><?= $h($step[1]) ?></p></div></div>
                            <?php endforeach; ?>
                        </div>
                        <?php if ($slide['note'] !== ''): ?><div class="g-note"><i class="fas fa-lightbulb"></i> <?= $h($slide['note']) ?></div><?php endif; ?>
                        <?= $try($slide['try']) ?>
                    </div>
                    <?php if ($hasScreen): ?><div><?= $screen($slide['screen']) ?></div><?php endif; ?>
                </div>
                <?= $footer($n) ?>
            </section>
<?php elseif ($type === 'recap'): ?>
            <section class="s">
                <h2>Quick recap</h2>
                <p class="lead">The four things to remember.</p>
                <div class="g-grid" style="grid-template-columns:repeat(4,1fr)">
                    <?php foreach ($slide['items'] as $i => $r): ?>
                        <div class="g-card"><span class="g-n"><?= $i + 1 ?></span><h4><?= $h($r[0]) ?></h4><p><?= $h($r[1]) ?></p></div>
                    <?php endforeach; ?>
                </div>
                <div class="g-note" style="margin-top:28px"><i class="fas fa-question-circle"></i> Open this guide again any time with the <b>?</b> button at the top of every page.</div>
                <?= $footer($n) ?>
            </section>
<?php elseif ($type === 'thanks'): ?>
            <section class="s thanks">
                <h1>Thank you!</h1>
                <p>Questions about the TMS? Reach out to the IT Department.</p>
                <span class="url"><i class="fas fa-globe"></i> tms.storagemart.com.ph</span><br>
                <button type="button" class="go" id="goBtn"><?= $ownGuide ? 'Start using the TMS' : 'Close the guide' ?></button>
            </section>
<?php endif; ?>
<?php endforeach; ?>
        </div>
    </div>
    <div class="nav">
        <button type="button" id="prev" aria-label="Previous slide"><i class="fas fa-chevron-left"></i></button>
        <span class="count" id="count">1 / <?= $total ?></span>
        <button type="button" id="next" aria-label="Next slide"><i class="fas fa-chevron-right"></i></button>
    </div>
</div>
<script>
(function () {
    var embed = <?= $embed ? 'true' : 'false' ?>;
    var deck = document.getElementById('deck'), stage = document.getElementById('stage');
    var slides = Array.prototype.slice.call(stage.querySelectorAll('.s'));
    var prev = document.getElementById('prev'), next = document.getElementById('next'), count = document.getElementById('count');
    var index = 0;
    function scale() {
        var w = deck.clientWidth, h = deck.clientHeight;
        var k = Math.min(w / 1280, h / 720);
        stage.style.transform = 'translate(' + ((w - 1280 * k) / 2) + 'px,' + ((h - 720 * k) / 2) + 'px) scale(' + k + ')';
    }
    function show(i) {
        index = Math.max(0, Math.min(slides.length - 1, i));
        slides.forEach(function (s, k) { s.classList.toggle('on', k === index); });
        prev.disabled = index === 0;
        next.disabled = index === slides.length - 1;
        count.textContent = (index + 1) + ' / ' + slides.length;
    }
    function finish() {
        if (embed && window.parent !== window) window.parent.postMessage('tms-guide-done', window.location.origin);
        else window.location.href = <?= json_encode($base . $home) ?>;
    }
    prev.addEventListener('click', function () { show(index - 1); });
    next.addEventListener('click', function () { show(index + 1); });
    document.getElementById('goBtn').addEventListener('click', function (e) { e.stopPropagation(); finish(); });
    document.addEventListener('keydown', function (e) {
        if (e.target && /^(SELECT|INPUT|TEXTAREA)$/.test(e.target.tagName)) return;
        if (e.key === 'ArrowRight' || e.key === 'PageDown') { show(index + 1); }
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
        if (e.target.closest('a, button')) return;
        var r = deck.getBoundingClientRect();
        show(index + (e.clientX - r.left > r.width / 2 ? 1 : -1));
    });
    var roleSwitch = document.getElementById('roleSwitch');
    if (roleSwitch) roleSwitch.addEventListener('change', function () {
        window.location.href = <?= json_encode($base . '/guide?role=') ?> + encodeURIComponent(roleSwitch.value);
    });
    window.addEventListener('resize', scale);
    scale();
    // ?slide=5 opens that slide.
    show((parseInt(new URLSearchParams(window.location.search).get('slide'), 10) || 1) - 1);
})();
</script>
</body>
</html>
