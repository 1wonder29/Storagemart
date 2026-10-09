<?php
/**
 * "How to use the TMS" (TMS User Guide, Employee - StorageMart IT Department), as slides.
 * Shown to employees the first time they sign in (?embed=1 inside the welcome window) and from
 * the "?" button in the header. Expects $base, $embed, $role, $home.
 */
$h = static function ($value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};
$isEmployee = $role === 'EMPLOYEE';
$try = static function (string $path, string $label) use ($h, $base, $isEmployee, $embed): string {
    if (!$isEmployee) {
        return '';
    }
    return '<a class="g-try" href="' . $h($base . $path) . '"' . ($embed ? ' target="_top"' : '') . '><i class="fas fa-external-link-alt"></i> ' . $h($label) . '</a>';
};
$footer = static function (int $n): string {
    return '<div class="g-foot"><span>TMS User Guide · StorageMart IT Department</span><span>' . $n . '</span></div>';
};
// Numbered marker next to an item in a screen picture (matches the numbered steps).
$num = static function (int $n): string {
    return '<b class="g-pin">' . $n . '</b>';
};
// Small "browser window" used for the screen pictures.
$browser = static function (string $url, string $inner, ?int $urlPin = null) use ($h, $num): string {
    return '<div class="g-browser"><div class="g-browser-bar"><span></span><span></span><span></span><em>' . $h($url) . '</em>'
        . ($urlPin ? $num($urlPin) : '') . '</div>'
        . '<div class="g-browser-body">' . $inner . '</div></div>';
};
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>How to use the TMS | StorageMart</title>
    <link href="<?= $h($base) ?>/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="icon" href="<?= $h($base) ?>/assets/img/sm_favicon.png" type="image/x-icon">
    <style>
        * { box-sizing: border-box; }
        html, body { margin: 0; height: 100%; }
        body { background: #eef0f6; color: #1f2937; font-family: "Inter", Arial, sans-serif; display: flex; flex-direction: column; }
        .bar { display: flex; align-items: center; gap: 10px; padding: 10px 16px; background: #2b2f7a; color: #fff; }
        .bar strong { margin-right: auto; }
        .bar a, .bar button { color: #fff; background: rgba(255,255,255,.15); border: 0; border-radius: 8px; padding: 7px 12px; font: inherit; font-size: .85rem; font-weight: 700; text-decoration: none; cursor: pointer; }
        .wrap { flex: 1; min-height: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 12px; }
        .deck { position: relative; width: 100%; max-width: 1280px; aspect-ratio: 16 / 9; max-height: calc(100% - 64px); overflow: hidden; border-radius: 12px; background: #fff;
                box-shadow: 0 10px 30px rgba(43, 47, 122, .18); }
        .stage { position: absolute; left: 0; top: 0; width: 1280px; height: 720px; transform-origin: 0 0; }
        .nav { display: flex; align-items: center; justify-content: center; gap: 14px; padding: 10px 0 0; }
        .nav button { width: 42px; height: 42px; border-radius: 50%; border: 0; background: #2b2f7a; color: #fff; cursor: pointer; font-size: 1rem; }
        .nav button:disabled { opacity: .35; cursor: default; }
        .nav .count { min-width: 64px; text-align: center; font-weight: 800; color: #2b2f7a; }
        .nav .start { width: auto; border-radius: 999px; padding: 0 18px; background: #f07024; font-weight: 800; font-family: inherit; }

        /* ---- slides (1280 x 720) ---- */
        .s { position: absolute; inset: 0; display: none; background: #fff; padding: 58px 70px 70px; }
        .s.on { display: block; }
        .s h2 { margin: 0 0 6px; font-size: 44px; font-weight: 800; color: #1f2a6b; letter-spacing: -.01em; }
        .s .lead { margin: 0 0 26px; font-size: 21px; color: #374151; }
        .g-foot { position: absolute; left: 70px; right: 70px; bottom: 26px; display: flex; justify-content: space-between; font-size: 13px; color: #6b7280; }
        .g-grid { display: grid; gap: 22px; }
        .g-card { background: #f1f4fa; border-radius: 16px; padding: 22px; }
        .g-card h4 { margin: 12px 0 8px; font-size: 21px; color: #1f2a6b; }
        .g-card p { margin: 0; font-size: 16px; line-height: 1.45; color: #374151; }
        .g-ico { width: 52px; height: 52px; border-radius: 50%; display: flex; align-items: center; justify-content: center; background: #1f2a6b; color: #fff; font-size: 21px; }
        .g-ico.o { background: #f07024; }
        .g-n { width: 34px; height: 34px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; background: #f07024; color: #fff; font-weight: 800; flex: 0 0 auto; }
        .g-steps { display: flex; flex-direction: column; gap: 18px; }
        .g-step { display: flex; gap: 14px; align-items: flex-start; }
        .g-step h5 { margin: 3px 0 4px; font-size: 20px; color: #1f2a6b; }
        .g-step p { margin: 0; font-size: 16px; color: #374151; line-height: 1.4; }
        .g-note { margin-top: 18px; padding: 12px 16px; border-radius: 12px; background: #fff4ec; color: #9a3412; font-size: 15px; }
        .g-try { display: inline-flex; align-items: center; gap: 6px; margin-top: 16px; font-size: 15px; font-weight: 700; color: #2b2f7a; }
        .g-two { display: grid; grid-template-columns: 1fr 1.12fr; gap: 40px; align-items: center; }
        .g-pills { display: flex; flex-wrap: wrap; gap: 10px; }
        .g-pill { border-radius: 999px; padding: 7px 16px; font-weight: 800; font-size: 15px; color: #fff; }
        .p-open { background: #3b82f6; } .p-prog { background: #3b4fd6; } .p-pend { background: #f5a623; color: #3b2a00; }
        .p-res { background: #1a7f37; } .p-closed { background: #4b5563; } .p-cancel { background: #c0392b; }

        /* screen pictures */
        .g-browser { border-radius: 14px; overflow: hidden; background: #fff; box-shadow: 0 14px 34px rgba(31, 42, 107, .2); border: 1px solid #dfe3ee; }
        .g-browser-bar { display: flex; align-items: center; gap: 6px; padding: 9px 12px; background: #eceff6; }
        .g-browser-bar span { width: 10px; height: 10px; border-radius: 50%; background: #c9cfdd; }
        .g-browser-bar em { margin-left: 10px; flex: 1; background: #fff; border-radius: 999px; padding: 4px 12px; font-style: normal; font-size: 12px; color: #6b7280; }
        .g-browser-body { position: relative; padding: 16px; min-height: 330px; background: #f6f7fb; }
        .g-pin { display: inline-flex; width: 22px; height: 22px; margin: 0 4px; border-radius: 50%; background: #f07024; color: #fff; font-size: 12px; font-style: normal;
                 align-items: center; justify-content: center; vertical-align: middle; box-shadow: 0 0 0 3px rgba(240, 112, 36, .25); flex: 0 0 auto; }
        .m-row { display: flex; gap: 10px; margin-bottom: 10px; }
        .m-card { flex: 1; background: #fff; border-radius: 10px; padding: 10px; box-shadow: 0 2px 6px rgba(0,0,0,.05); font-size: 12px; color: #6b7280; }
        .m-card b { display: block; font-size: 20px; color: #1f2a6b; }
        .m-btn { display: inline-block; border-radius: 999px; padding: 6px 12px; font-size: 12px; font-weight: 700; color: #fff; background: #3b4fd6; margin: 0 6px 6px 0; }
        .m-line { height: 10px; border-radius: 5px; background: #dfe3ee; margin: 8px 0; }
        .m-input { background: #fff; border: 1px solid #d6dbe7; border-radius: 8px; padding: 8px 10px; font-size: 12px; color: #6b7280; margin-bottom: 8px; }
        .m-label { font-size: 10px; font-weight: 800; color: #6b7280; text-transform: uppercase; letter-spacing: .04em; margin-bottom: 3px; }
        .m-hero { background: linear-gradient(135deg, #2b2f7a, #4e3fc1); color: #fff; border-radius: 10px; padding: 12px 14px; font-weight: 800; margin-bottom: 10px; }
        .m-table div { display: grid; grid-template-columns: 1.4fr 1fr .8fr; gap: 8px; background: #fff; border-radius: 8px; padding: 8px 10px; margin-bottom: 6px; font-size: 12px; }

        /* cover */
        .s.cover { background: #2b2a6e; color: #fff; padding: 110px 90px; }
        .cover .kick { color: #f07024; font-weight: 800; letter-spacing: .04em; font-size: 18px; }
        .cover h1 { font-size: 64px; line-height: 1.05; margin: 16px 0 22px; font-weight: 800; max-width: 640px; }
        .cover p { font-size: 25px; max-width: 560px; margin: 0 0 34px; color: #e5e7f5; }
        .cover .url { display: inline-flex; align-items: center; gap: 10px; background: #f07024; border-radius: 999px; padding: 12px 26px; font-weight: 800; font-size: 20px; }
        .cover .by { margin-top: 32px; font-size: 17px; }
        .cover .circle { position: absolute; right: 120px; top: 120px; width: 280px; height: 280px; border-radius: 50%; background: #f07024; display: flex; align-items: center; justify-content: center; font-size: 120px; }
        .cover .pills { position: absolute; right: 50px; top: 450px; width: 400px; height: 120px; }
        .cover .pills .g-pill { position: absolute; font-size: 19px; padding: 10px 26px; }
        .s.thanks { background: #2b2a6e; color: #fff; text-align: center; padding-top: 210px; }
        .thanks h1 { font-size: 80px; margin: 0 0 16px; }
        .thanks p { font-size: 26px; color: #e5e7f5; margin: 0 0 30px; }
        .thanks .url { display: inline-flex; gap: 10px; align-items: center; background: #f07024; border-radius: 999px; padding: 12px 26px; font-weight: 800; font-size: 20px; }
        .thanks .go { display: inline-block; margin-top: 30px; border: 0; border-radius: 999px; background: #fff; color: #2b2a6e; padding: 14px 32px; font: inherit; font-weight: 800; font-size: 21px; cursor: pointer; }
        .bad, .good { border-radius: 14px; padding: 16px 18px; }
        .bad { background: #fdecea; } .good { background: #e8f6ee; }
        .bad h5, .good h5 { margin: 0 0 6px; font-size: 18px; }
        .bad h5 { color: #b91c1c; } .good h5 { color: #13855c; }
        .chk { font-size: 15px; margin: 4px 0; }
    </style>
</head>
<body>
<?php if (!$embed): ?>
    <div class="bar">
        <strong><i class="fas fa-question-circle"></i> How to use the TMS</strong>
        <a href="<?= $h($base . $home) ?>"><i class="fas fa-home"></i> My dashboard</a>
    </div>
<?php endif; ?>
<div class="wrap">
    <div class="deck" id="deck">
        <div class="stage" id="stage">

            <!-- 1 -->
            <section class="s cover on">
                <div class="kick">STORAGEMART · IT DEPARTMENT</div>
                <h1>Ticketing Management System</h1>
                <p>A user guide for filing, tracking, and closing your IT concerns</p>
                <span class="url"><i class="fas fa-globe"></i> tms.storagemart.com.ph</span>
                <div class="by">Presented by IT TEAM</div>
                <div class="circle"><i class="fas fa-ticket-alt"></i></div>
                <div class="pills">
                    <span class="g-pill p-pend" style="left:0;top:30px">Pending</span>
                    <span class="g-pill p-prog" style="left:120px;top:72px">In Progress</span>
                    <span class="g-pill p-res" style="left:200px;top:0">Resolved</span>
                </div>
            </section>

            <!-- 2 -->
            <section class="s">
                <h2>What is the TMS?</h2>
                <p class="lead"><strong>One place to report IT concerns and follow them until they're fixed.</strong>
                    The Ticketing Management System is StorageMart's web portal for IT support. Each concern you file becomes a ticket that
                    the IT Department reviews, assigns, and resolves, and you can check its status at any time.</p>
                <div class="g-grid" style="grid-template-columns:repeat(4,1fr)">
                    <div class="g-card"><div class="g-ico o"><i class="fas fa-plus"></i></div><h4>File tickets</h4><p>Report any IT concern and set its category and priority level.</p></div>
                    <div class="g-card"><div class="g-ico"><i class="fas fa-tasks"></i></div><h4>Track progress</h4><p>See pending and resolved tickets on your dashboard, plus each ticket's history.</p></div>
                    <div class="g-card"><div class="g-ico"><i class="fas fa-laptop"></i></div><h4>View your assets</h4><p>Check the IT equipment assigned to you, with model and serial number.</p></div>
                    <div class="g-card"><div class="g-ico o"><i class="fas fa-star"></i></div><h4>Give feedback</h4><p>Rate the IT personnel who handled your concern and leave comments.</p></div>
                </div>
                <?= $footer(2) ?>
            </section>

            <!-- 3 -->
            <section class="s">
                <h2>Who uses the TMS?</h2>
                <div class="g-grid" style="grid-template-columns:repeat(3,1fr);margin-top:24px">
                    <div class="g-card" style="background:#fff;border:2px solid #f07024"><div class="g-ico o"><i class="fas fa-user"></i></div><h4>Employee (you)</h4><p>Files tickets, tracks their status, signs or uploads the technical report, and rates the service.</p></div>
                    <div class="g-card"><div class="g-ico"><i class="fas fa-user-tie"></i></div><h4>Operations Manager (OM)</h4><p>Files and monitors tickets for the assets assigned to them, with filters for easier tracking.</p></div>
                    <div class="g-card"><div class="g-ico"><i class="fas fa-users"></i></div><h4>Area Operations Manager (AOM)</h4><p>Oversees employees and tickets across assigned branches, and can file tickets for employees.</p></div>
                    <div class="g-card"><div class="g-ico"><i class="fas fa-id-badge"></i></div><h4>Human Resources (HR)</h4><p>Manages employee records, uniform inventory, and accountability forms, and can file tickets too.</p></div>
                    <div class="g-card"><div class="g-ico"><i class="fas fa-tools"></i></div><h4>IT Personnel</h4><p>Works on assigned tickets, updates their status, and generates the technical report when resolved.</p></div>
                    <div class="g-card"><div class="g-ico"><i class="fas fa-user-shield"></i></div><h4>Administrator</h4><p>Manages accounts, branches, assets and the ticket rules, and reviews the audit trail.</p></div>
                </div>
                <p style="font-style:italic;color:#6b7280;margin-top:18px">This guide focuses on the employee view. Each role has its own TMS manual from the IT Department.</p>
                <?= $footer(3) ?>
            </section>

            <!-- 4 -->
            <section class="s">
                <h2>How a ticket moves</h2>
                <div class="g-grid" style="grid-template-columns:repeat(5,1fr);margin-top:22px">
                    <?php foreach ([
                        ['You', 'File a ticket', 'Your ticket is saved with the status Open.'],
                        ['TMS / Admin', 'Assign', 'It goes to the IT person for your branch\'s zone (or the Admin assigns it).'],
                        ['IT Personnel', 'Work on it', 'In Progress: IT works on the concern and chats with you on the ticket.'],
                        ['IT Personnel', 'Resolve', 'IT marks it Resolved and generates the Technical Report.'],
                        ['You', 'Close the loop', 'You sign the report, rate the service, and comment.'],
                    ] as $i => $step): ?>
                        <div class="g-card" style="padding:18px">
                            <div style="font-weight:800;color:#f07024;font-size:14px">STEP <?= $i + 1 ?></div>
                            <div style="font-size:13px;color:#6b7280;margin-top:4px"><?= $h($step[0]) ?></div>
                            <h4 style="margin-top:6px"><?= $h($step[1]) ?></h4>
                            <p><?= $h($step[2]) ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
                <h4 style="color:#1f2a6b;font-size:22px;margin:34px 0 12px">Statuses you'll see</h4>
                <div class="g-pills">
                    <span class="g-pill p-open">Open</span><span class="g-pill p-prog">In Progress</span><span class="g-pill p-pend">Pending</span>
                    <span class="g-pill p-res">Resolved</span><span class="g-pill p-closed">Closed</span><span class="g-pill p-cancel">Cancelled</span>
                </div>
                <?= $footer(4) ?>
            </section>

            <!-- 5 -->
            <section class="s">
                <div class="g-two">
                    <div>
                        <h2>Step 1 · Log in to the TMS</h2>
                        <div class="g-steps" style="margin-top:24px">
                            <div class="g-step"><span class="g-n">1</span><div><h5>Open the TMS website</h5><p>Go to tms.storagemart.com.ph/login in your web browser.</p></div></div>
                            <div class="g-step"><span class="g-n">2</span><div><h5>Enter your credentials</h5><p>Type the username and password for your TMS account.</p></div></div>
                            <div class="g-step"><span class="g-n">3</span><div><h5>Click Sign in</h5><p>You'll land on your Dashboard.</p></div></div>
                        </div>
                        <div class="g-note"><i class="fas fa-key"></i> Forgot your password? Use the "Forgot password?" link under the Sign in button.</div>
                    </div>
                    <?= $browser('tms.storagemart.com.ph/login',
                        '<div style="max-width:300px;margin:30px auto;background:#fff;border-radius:14px;padding:22px;box-shadow:0 6px 18px rgba(0,0,0,.08)">'
                        . '<div style="text-align:center;margin-bottom:14px"><img src="' . $h($base) . '/assets/img/storagemart-logo-header.png" alt="" style="height:30px"></div>'
                        . '<div class="m-label">Username ' . $num(2) . '</div><div class="m-input">juan.delacruz</div>'
                        . '<div class="m-label">Password</div><div class="m-input">••••••••</div>'
                        . '<div class="m-btn" style="display:block;text-align:center;margin:10px 0 0">Sign in ' . $num(3) . '</div></div>', 1) ?>
                </div>
                <?= $footer(5) ?>
            </section>

            <!-- 6 -->
            <section class="s">
                <div class="g-two">
                    <?= $browser('tms.storagemart.com.ph/employee/dashboard',
                        '<div class="m-hero" style="display:flex;justify-content:space-between;align-items:center">Dashboard <span><i class="fas fa-moon"></i>' . $num(4) . '</span></div>'
                        . '<div class="m-row" style="align-items:center">' . $num(1) . '<div class="m-card"><b>2</b>My assets</div><div class="m-card"><b>5</b>Total tickets</div><div class="m-card"><b>1</b>Pending</div><div class="m-card"><b>4</b>Resolved</div></div>'
                        . '<div>' . $num(2) . '<span class="m-btn">My Assets</span><span class="m-btn">My Tickets</span><span class="m-btn" style="background:#f07024">Create Ticket</span><span class="m-btn">My Profile</span></div>'
                        . '<div class="m-row" style="align-items:center">' . $num(3) . '<div class="m-card" style="height:110px"><div class="m-line" style="width:60%"></div><div style="width:80px;height:80px;border-radius:50%;border:14px solid #3b4fd6;border-right-color:#1a7f37;margin:6px auto"></div></div><div class="m-card" style="height:110px"><div class="m-line" style="width:50%"></div><div class="m-line"></div><div class="m-line" style="width:70%"></div></div></div>') ?>
                    <div>
                        <h2>Your Dashboard at a glance</h2>
                        <div class="g-steps" style="margin-top:24px">
                            <div class="g-step"><span class="g-n">1</span><div><h5>Summary cards</h5><p>Counts of your assets, total tickets, pending, and resolved tickets.</p></div></div>
                            <div class="g-step"><span class="g-n">2</span><div><h5>Quick actions</h5><p>Shortcuts to My Assets, My Tickets, Create Ticket, and My Profile.</p></div></div>
                            <div class="g-step"><span class="g-n">3</span><div><h5>Ticket charts</h5><p>Status and resolution charts fill in once you have tickets.</p></div></div>
                            <div class="g-step"><span class="g-n">4</span><div><h5>Light or dark</h5><p>Click the moon icon at the top to switch to dark mode.</p></div></div>
                        </div>
                        <?= $try('/employee/dashboard', 'Open my dashboard') ?>
                    </div>
                </div>
                <?= $footer(6) ?>
            </section>

            <!-- 7 -->
            <section class="s">
                <h2>Step 2 · File a new ticket</h2>
                <div class="g-two" style="grid-template-columns:1fr 1fr;gap:30px;margin-top:12px">
                    <div class="g-steps" style="gap:12px">
                        <div class="g-step"><span class="g-n">1</span><div><h5>Click "Create Ticket"</h5><p>In the sidebar, on your Dashboard, or + Create New Ticket in My Tickets.</p></div></div>
                        <div class="g-step"><span class="g-n">2</span><div><h5>Check your details</h5><p>Your employee ID, name, department, and branch are filled in for you.</p></div></div>
                        <div class="g-step"><span class="g-n">3</span><div><h5>Write a Subject</h5><p>A short line about the issue, like the printer example on the right.</p></div></div>
                        <div class="g-step"><span class="g-n">4</span><div><h5>Describe the issue</h5><p>Add error messages, what you tried, and photos if you can (up to 1,000 characters).</p></div></div>
                        <div class="g-step"><span class="g-n">5</span><div><h5>Pick a Category and Priority</h5><p>Use the priority guide on the form. See the next slide for help.</p></div></div>
                        <div class="g-step"><span class="g-n">6</span><div><h5>Click Submit Ticket</h5><p>Check the Live Preview first. New tickets start with the status Open.</p></div></div>
                    </div>
                    <?= $browser('tms.storagemart.com.ph/employee/tickets/create',
                        '<div class="m-hero" style="display:flex;justify-content:space-between">File Ticket <span style="background:#f07024;border-radius:999px;padding:0 10px;font-size:11px;line-height:20px">How to file a ticket</span></div>'
                        . '<div class="m-row"><div style="flex:1"><div class="m-label">Employee ID ' . $num(2) . '</div><div class="m-input">2600180</div></div><div style="flex:1"><div class="m-label">Branch</div><div class="m-input">Head Office</div></div></div>'
                        . '<div class="m-label">Subject * ' . $num(3) . '</div><div class="m-input">Brother printer at the front desk shows Paper Jam</div>'
                        . '<div class="m-label">Description * ' . $num(4) . '</div><div class="m-input" style="height:54px">Since 9 AM. Tray checked, error still there.</div>'
                        . '<div class="m-row"><div style="flex:1"><div class="m-label">Category ' . $num(5) . '</div><div class="m-input">Printer Issues</div></div><div style="flex:1"><div class="m-label">Priority</div><div class="m-input">Medium</div></div></div>'
                        . '<span class="m-btn" style="background:#f07024">Submit Ticket</span>' . $num(6)) ?>
                </div>
                <?= $try('/employee/tickets/create', 'Open Create Ticket') ?>
                <?= $footer(7) ?>
            </section>

            <!-- 8 -->
            <section class="s">
                <h2>Choose the right priority</h2>
                <div class="g-grid" style="grid-template-columns:repeat(4,1fr);margin-top:24px">
                    <?php foreach ([
                        ['Low', '#1a7f37', 'Non-urgent; can wait for regular maintenance.', ['Faded or streaky prints', 'A slow PC that still works']],
                        ['Medium', '#f5a623', 'Standard-priority issue.', ['Outlook won\'t send or receive', 'Printer offline or stuck print jobs']],
                        ['High', '#f07024', 'Urgent; requires immediate attention.', ['No internet at the branch', 'PTI / StorLogix offline']],
                        ['Critical', '#c0392b', 'Major outage affecting critical operations or multiple departments; needs an immediate response.', ['Network down across the office', 'A system outage hitting several departments']],
                    ] as $p): ?>
                        <div class="g-card" style="border-top:6px solid <?= $p[1] ?>">
                            <h4 style="margin-top:0;color:<?= $p[1] ?>"><?= $h($p[0]) ?></h4>
                            <p><?= $h($p[2]) ?></p>
                            <div style="font-size:12px;font-weight:800;color:#6b7280;margin:14px 0 6px">EXAMPLES</div>
                            <?php foreach ($p[3] as $ex): ?><p style="font-size:15px">• <?= $h($ex) ?></p><?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="g-note"><i class="fas fa-balance-scale"></i> Set the level honestly. If everything is marked High or Critical, truly urgent concerns end up waiting longer.</div>
                <?= $footer(8) ?>
            </section>

            <!-- 9 -->
            <section class="s">
                <h2>Write a clear subject and description</h2>
                <div class="g-two" style="grid-template-columns:1fr 1fr;gap:30px;margin-top:20px;align-items:start">
                    <div>
                        <div class="bad"><h5><i class="fas fa-times-circle"></i> Too vague</h5><div style="font-size:18px">"Printer not working."</div>
                            <div class="chk">✗ No location &nbsp; ✗ No error details &nbsp; ✗ No timeline</div></div>
                        <div class="good" style="margin-top:14px"><h5><i class="fas fa-check-circle"></i> Clear and complete</h5>
                            <div style="font-size:13px;font-weight:800;color:#6b7280">SUBJECT</div><div style="font-size:16px">Brother MFC-T810W printer is not working</div>
                            <div style="font-size:13px;font-weight:800;color:#6b7280;margin-top:6px">DESCRIPTION</div><div style="font-size:16px">Paper Jam in the Admin office since 9 AM. Tray checked, error still there.</div>
                            <div class="chk">✓ What's wrong &nbsp; ✓ Which device &nbsp; ✓ Since when &nbsp; ✓ What was tried</div></div>
                    </div>
                    <div class="g-steps" style="gap:14px">
                        <h4 style="margin:0;color:#1f2a6b;font-size:21px">A good description answers:</h4>
                        <div class="g-step"><span class="g-n"><i class="fas fa-question"></i></span><div><h5>What's wrong?</h5><p>Describe the problem and copy any error message exactly.</p></div></div>
                        <div class="g-step"><span class="g-n"><i class="fas fa-desktop" style="font-size:13px"></i></span><div><h5>Which device?</h5><p>Name the equipment and where it is, or file it from My Assets.</p></div></div>
                        <div class="g-step"><span class="g-n"><i class="fas fa-clock" style="font-size:13px"></i></span><div><h5>Since when?</h5><p>Say when it started or how often it happens.</p></div></div>
                        <div class="g-step"><span class="g-n"><i class="fas fa-redo" style="font-size:13px"></i></span><div><h5>What did you try?</h5><p>Mention steps you already took, like restarting.</p></div></div>
                    </div>
                </div>
                <div class="g-note">Be specific (up to 1,000 characters), and file one concern per ticket.</div>
                <?= $footer(9) ?>
            </section>

            <!-- 10 -->
            <section class="s">
                <div class="g-two">
                    <div>
                        <h2>Step 3 · Track your tickets</h2>
                        <div class="g-steps" style="margin-top:24px">
                            <div class="g-step"><span class="g-n">1</span><div><h5>Status cards</h5><p>Counts for Open, In Progress, Pending, Resolved, Closed, and Cancelled.</p></div></div>
                            <div class="g-step"><span class="g-n">2</span><div><h5>Search &amp; filter</h5><p>Filter by status or priority, or search by ticket number.</p></div></div>
                            <div class="g-step"><span class="g-n">3</span><div><h5>All Tickets</h5><p>Every ticket you file is listed here. Open one to see its details.</p></div></div>
                        </div>
                        <?= $try('/employee/tickets', 'Open My Tickets') ?>
                    </div>
                    <?= $browser('tms.storagemart.com.ph/employee/tickets',
                        '<div class="m-row" style="align-items:center">' . $num(1) . '<div class="m-card"><b>1</b>Open</div><div class="m-card"><b>1</b>In Progress</div><div class="m-card"><b>0</b>Pending</div><div class="m-card"><b>3</b>Resolved</div></div>'
                        . '<div class="m-row" style="align-items:center">' . $num(2) . '<div class="m-input" style="flex:1;margin:0">Search ticket number…</div><div class="m-input" style="width:110px;margin:0">All statuses</div></div>'
                        . '<div class="m-label">' . $num(3) . ' All tickets</div>'
                        . '<div class="m-table"><div><b>STM-20261009-0004</b><span>Printer Issues</span><span class="g-pill p-prog" style="font-size:10px;padding:2px 8px">In Progress</span></div>'
                        . '<div><b>STM-20261006-0002</b><span>Network</span><span class="g-pill p-res" style="font-size:10px;padding:2px 8px">Resolved</span></div>'
                        . '<div><b>STM-20261002-0001</b><span>Hardware</span><span class="g-pill p-res" style="font-size:10px;padding:2px 8px">Resolved</span></div></div>') ?>
                </div>
                <?= $footer(10) ?>
            </section>

            <!-- 11 -->
            <section class="s">
                <div class="g-two">
                    <?= $browser('tms.storagemart.com.ph',
                        '<div class="m-hero">' . $num(1) . ' STM-20261009-0004 · Head Office</div>'
                        . '<div class="m-row"><div class="m-card"><div class="m-label">Priority</div>Low</div><div class="m-card"><div class="m-label">Status</div>In Progress</div><div class="m-card"><div class="m-label">Filed</div>Oct 9, 2026</div></div>'
                        . '<div class="m-card" style="margin-bottom:10px"><div class="m-label">' . $num(2) . ' Ticket history</div><div class="m-line" style="width:80%"></div><div class="m-line" style="width:60%"></div></div>'
                        . '<div class="m-card" style="margin-bottom:10px"><div class="m-label">' . $num(3) . ' Comments</div><div class="m-input">Hi! Please try restarting the printer.</div><div class="m-input" style="text-align:right">Done, still jammed.</div></div>'
                        . $num(4) . '<span class="m-btn" style="background:#f5a623;color:#3b2a00">Rate Ticket</span><span class="m-btn">Technical Report</span>') ?>
                    <div>
                        <h2>Inside a ticket</h2>
                        <div class="g-steps" style="margin-top:24px">
                            <div class="g-step"><span class="g-n">1</span><div><h5>Ticket details</h5><p>Employee, branch, priority, status, filed date, and remarks.</p></div></div>
                            <div class="g-step"><span class="g-n">2</span><div><h5>Ticket history</h5><p>Every update on your ticket, in order, so you always know where it stands.</p></div></div>
                            <div class="g-step"><span class="g-n">3</span><div><h5>Comments and chat</h5><p>Answer IT's questions on the ticket or in the chat bubble. You can send photos and screenshots (Ctrl+V).</p></div></div>
                            <div class="g-step"><span class="g-n">4</span><div><h5>Report and rating</h5><p>The technical report and Rate Ticket buttons appear when the ticket is resolved. See the next slide.</p></div></div>
                        </div>
                    </div>
                </div>
                <?= $footer(11) ?>
            </section>

            <!-- 12 -->
            <section class="s">
                <h2>Step 4 · Close the loop when it's resolved</h2>
                <div class="g-two" style="margin-top:14px">
                    <div class="g-steps">
                        <div class="g-step"><span class="g-n">1</span><div><h5>Open the resolved ticket</h5><p>You get a notification when IT marks your ticket Resolved.</p></div></div>
                        <div class="g-step"><span class="g-n">2</span><div><h5>Review and sign the report</h5><p>Click Sign report to sign the technical report on the screen, or download it, sign it on paper and upload it.</p></div></div>
                        <div class="g-step"><span class="g-n">3</span><div><h5>Upload the signed copy (paper)</h5><p>Under Technical Report, choose the file (PDF, DOCX, DOC, JPG or PNG, up to 10MB) and click Upload Report.</p></div></div>
                        <div class="g-step"><span class="g-n">4</span><div><h5>Rate the service</h5><p>Click Rate Ticket to score the IT personnel who handled your concern. Ratings help IT improve its support.</p></div></div>
                    </div>
                    <?= $browser('Ticket details',
                        '<div class="m-card" style="margin-bottom:10px"><div class="m-label">Summary</div>'
                        . '<div style="display:flex;align-items:center;margin:4px 0 8px"><span class="g-pill p-res" style="font-size:11px;padding:3px 10px">Resolved</span>' . $num(1) . '</div>'
                        . '<div style="display:flex;flex-wrap:wrap;align-items:center;gap:6px 0">'
                        . '<span class="m-btn" style="margin:0 6px 0 0">Download Technical Report</span>'
                        . '<span class="m-btn" style="background:#f07024;margin:0">Sign report</span>' . $num(2)
                        . '<span class="m-btn" style="background:#f5a623;color:#3b2a00;margin:0">Rate Ticket</span>' . $num(4) . '</div></div>'
                        . '<div class="m-card"><div class="m-label">Technical Report</div><div class="m-input">Choose File &nbsp; No file chosen</div>'
                        . '<div style="display:flex;align-items:center"><span class="m-btn" style="margin:0">Upload Report</span>' . $num(3) . '</div></div>') ?>
                </div>
                <?= $footer(12) ?>
            </section>

            <!-- 13 -->
            <section class="s">
                <div class="g-two">
                    <div>
                        <h2>My Assets</h2>
                        <div class="g-steps" style="margin-top:24px">
                            <div class="g-step"><span class="g-n">1</span><div><h5>Your assigned equipment</h5><p>Assigned and Laptops counts, plus the list of every item IT has assigned to you.</p></div></div>
                            <div class="g-step"><span class="g-n">2</span><div><h5>Report a problem with an item</h5><p>View an item's details and file a support ticket from here when it needs attention.</p></div></div>
                        </div>
                        <?= $try('/employee/assets', 'Open My Assets') ?>
                    </div>
                    <?= $browser('tms.storagemart.com.ph/employee/assets',
                        '<div class="m-row" style="align-items:center">' . $num(1) . '<div class="m-card"><b>2</b>Assigned</div><div class="m-card"><b>1</b>Laptops</div></div>'
                        . '<div class="m-table"><div><b>IT-0029-HO014</b><span>Laptop</span><span><span class="m-btn" style="margin:0;font-size:10px;padding:3px 8px">File ticket</span>' . $num(2) . '</span></div>'
                        . '<div><b>IT-0031-HO014</b><span>Monitor</span><span class="m-btn" style="margin:0;font-size:10px;padding:3px 8px">File ticket</span></div></div>') ?>
                </div>
                <?= $footer(13) ?>
            </section>

            <!-- 14 -->
            <section class="s">
                <h2>Your profile and logging out</h2>
                <div class="g-two" style="margin-top:18px">
                    <div class="g-steps">
                        <div class="g-step"><span class="g-n">1</span><div><h5>Open your profile</h5><p>Click your name in the card at the top of the sidebar. Change password is right under it.</p></div></div>
                        <div class="g-step"><span class="g-n">2</span><div><h5>Log out</h5><p>Click Logout in the same card, then Sign out to leave (or Stay signed in to cancel).</p></div></div>
                        <div class="g-note"><i class="fas fa-users"></i> Shared computer? Always log out when you use a shared computer.</div>
                    </div>
                    <?= $browser('tms.storagemart.com.ph/employee/dashboard',
                        '<div style="width:230px;background:linear-gradient(180deg,#3b2fa0,#4a1fb0);border-radius:12px;padding:16px;color:#fff;text-align:center">'
                        . '<div style="width:56px;height:56px;border-radius:50%;background:#fff;margin:0 auto 8px"></div>'
                        . '<div style="font-weight:800">Juan Dela Cruz ' . $num(1) . '</div><div style="font-size:11px;opacity:.85">juan@storagemart.com.ph</div>'
                        . '<div style="font-size:11px;margin:6px 0">🔑 Change password</div>'
                        . '<div style="border:1px solid rgba(255,255,255,.5);border-radius:8px;padding:6px;font-size:12px;font-weight:700">Logout ' . $num(2) . '</div></div>') ?>
                </div>
                <?= $footer(14) ?>
            </section>

            <!-- 15 -->
            <section class="s">
                <h2>Quick recap</h2>
                <div class="g-grid" style="grid-template-columns:repeat(4,1fr);margin-top:30px">
                    <div class="g-card"><span class="g-n">1</span><h4>Log in</h4><p>Sign in at tms.storagemart.com.ph with your TMS account.</p></div>
                    <div class="g-card"><span class="g-n">2</span><h4>File it right</h4><p>Clear subject and description, the right category, honest priority.</p></div>
                    <div class="g-card"><span class="g-n">3</span><h4>Track it</h4><p>Follow the status, history, and chat in My Tickets.</p></div>
                    <div class="g-card"><span class="g-n">4</span><h4>Close the loop</h4><p>Sign the technical report and rate the service.</p></div>
                </div>
                <?= $footer(15) ?>
            </section>

            <!-- 16 -->
            <section class="s thanks">
                <h1>Thank you!</h1>
                <p>Questions about the TMS? Reach out to the IT Department.</p>
                <span class="url"><i class="fas fa-globe"></i> tms.storagemart.com.ph</span><br>
                <button type="button" class="go" id="goBtn">Start using the TMS <i class="fas fa-arrow-right"></i></button>
            </section>
        </div>
    </div>
    <div class="nav">
        <button type="button" id="prev" aria-label="Previous slide"><i class="fas fa-chevron-left"></i></button>
        <span class="count" id="count" aria-live="polite">1 / 16</span>
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
    window.addEventListener('resize', scale);
    scale();
    show(0);
})();
</script>
</body>
</html>
