<?php
/**
 * Company Gmail of one employee (Admin: /admin/company-gmail, HR: /hr/company-gmail).
 * Needs $area, $employee, $gmail, $email, $suggested, $checks, $ready, $updatedBy.
 */
$base = rtrim(BASE_URL, '/');
$h = static fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
$isAdminArea = $area === 'admin';
$name = trim($employee['firstname'] . ' ' . $employee['lastname']);
$gmail = $gmail ?? null;
$hasPassword = !empty($gmail['has_password']);
$done = 0;
foreach (array_keys($checks) as $key) {
    $done += !empty($gmail[$key]) ? 1 : 0;
}
$signupUrl = 'https://accounts.google.com/signup';
$localPart = strpos($email, '@') !== false ? substr($email, 0, strpos($email, '@')) : $email;
// Default Gmail password from the corporate email list formula: middle name + Employee ID.
$formula = CompanyGmailModel::formulaPassword((string) ($employee['middlename'] ?? ''), $employee['employee_id'], (string) ($employee['lastname'] ?? ''));
$backUrl = $isAdminArea
    ? $base . '/admin/account/edit?account_id=' . (int) $employee['account_id']
    : $base . '/hr/employees/detail/' . (int) $employee['employee_id'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Storage Mart | Company Gmail</title>
    <link href="<?= $h($base) ?>/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900" rel="stylesheet">
    <link rel="icon" href="<?= $h($base) ?>/assets/img/sm-icon-32.png?v=20261016" type="image/png">
    <link href="<?= $h($base) ?>/assets/css/storagemart.css" rel="stylesheet">
    <?php if ($isAdminArea): ?>
        <link href="<?= $h($base) ?>/assets/css/admin-users.css" rel="stylesheet">
    <?php else: ?>
        <link href="<?= $h($base) ?>/assets/css/hr-dashboard.css?v=20261007" rel="stylesheet">
    <?php endif; ?>
    <style>
        .cg-grid { display: grid; grid-template-columns: minmax(0, 1.15fr) minmax(0, 1fr); gap: 1.25rem; }
        @media (max-width: 991.98px) { .cg-grid { grid-template-columns: 1fr; } }
        .cg-card { background: #fff; border: 1px solid #e6ecf7; border-radius: 1rem; box-shadow: 0 6px 22px -12px rgba(15, 37, 99, .28); padding: 18px 20px; }
        .cg-card h2 { font-size: 1.05rem; font-weight: 800; color: #19398a; margin: 0 0 12px; display: flex; align-items: center; gap: 8px; }
        .cg-field label { font-size: .72rem; font-weight: 800; letter-spacing: .05em; text-transform: uppercase; color: #64748b; margin-bottom: 4px; }
        .cg-copy { white-space: nowrap; }
        .cg-qr { width: 200px; max-width: 100%; aspect-ratio: 1; margin: 4px auto 10px; padding: 10px; background: #fff; border-radius: 14px; border: 1px solid #e6ecf7; display: flex; align-items: center; justify-content: center; }
        .cg-qr svg, .cg-qr img { width: 100%; height: auto; display: block; }
        .cg-steps { counter-reset: s; list-style: none; padding: 0; margin: 0; }
        .cg-steps li { counter-increment: s; position: relative; padding: 0 0 10px 38px; font-size: .9rem; color: #334155; }
        .cg-steps li::before { content: counter(s); position: absolute; left: 0; top: 0; width: 26px; height: 26px; border-radius: 50%; background: #f85f00; color: #fff; font-weight: 800; font-size: .8rem; display: flex; align-items: center; justify-content: center; }
        .cg-check { display: flex; align-items: center; gap: 10px; padding: 8px 10px; border: 1px solid #e6ecf7; border-radius: .7rem; margin-bottom: 8px; cursor: pointer; }
        .cg-check input { width: 18px; height: 18px; }
        .cg-progress { height: 8px; border-radius: 99px; background: #eef2f9; overflow: hidden; }
        .cg-progress span { display: block; height: 100%; background: linear-gradient(90deg, #22c55e, #16a34a); }
        .cg-note { font-size: .8rem; color: #64748b; }
        html.it-dark .cg-card { background: #232838; border-color: #343a4f; }
        html.it-dark .cg-card h2 { color: #a5b8ff; }
        html.it-dark .cg-steps li, html.it-dark .cg-note { color: #cbd5e1; }
        html.it-dark .cg-check { border-color: #343a4f; }
        html.it-dark .cg-progress { background: #1c2130; }
    </style>
</head>
<body id="page-top">
<div id="wrapper">
    <?php
    if ($isAdminArea) {
        $activePage = 'users';
        $userSubPage = 'accounts';
        require_once dirname(__DIR__) . '/partials/admin/sidebar_topbar.php';
    } else {
        $activePage = 'employees';
        require_once dirname(__DIR__) . '/partials/hr/sidebar_topbar.php';
    }
    ?>
    <div class="container-fluid <?= $isAdminArea ? 'admin-users-page' : 'hr-dashboard-page' ?>">
        <div class="page-hero <?= $isAdminArea ? 'hero-accounts' : '' ?>">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h1><i class="fab fa-google mr-2"></i>Company Gmail</h1>
                    <p class="mb-2"><?= $h($name) ?> &middot; <?= $h($employee['position'] ?? '') ?><?= !empty($employee['branchName']) ? ' &middot; ' . $h($employee['branchName']) : '' ?></p>
                    <a href="<?= $h($backUrl) ?>" class="btn btn-light btn-sm"><i class="fas fa-arrow-left"></i> Back to the employee</a>
                </div>
                <div class="col-lg-4 mt-3 mt-lg-0">
                    <div class="hero-stat"><div class="stat-value"><?= $done ?> / <?= count($checks) ?></div><div class="stat-label">Set-up steps done</div></div>
                </div>
            </div>
        </div>

        <?php foreach (['flash_success' => 'success', 'flash_error' => 'danger'] as $key => $type): ?>
            <?php if (!empty($_SESSION[$key])): ?>
                <div class="alert alert-<?= $type ?>"><?= $h($_SESSION[$key]) ?></div>
                <?php unset($_SESSION[$key]); ?>
            <?php endif; ?>
        <?php endforeach; ?>
        <?php if (!empty($sameEmail)): ?>
            <div class="alert alert-warning"><i class="fas fa-exclamation-triangle mr-1"></i>
                This Gmail address is also used by <?= $h(implode(', ', array_map(static fn($o) => $o['name'] . ' (Employee ID ' . $o['employee_id'] . ')', $sameEmail))) ?>.
                Each employee needs their own company Gmail: change the address here and in Edit User.</div>
        <?php endif; ?>
        <?php if (!$ready): ?>
            <div class="alert alert-warning">Please import the database update (update P) first. Until then nothing on this page can be saved.</div>
        <?php endif; ?>

        <div class="cg-grid">
            <form class="cg-card" method="POST" action="<?= $h($base) ?>/<?= $h($area) ?>/company-gmail/save" autocomplete="off">
                <input type="hidden" name="csrf_token" value="<?= $h($_SESSION['csrf_token'] ?? '') ?>">
                <input type="hidden" name="employee_id" value="<?= (int) $employee['employee_id'] ?>">
                <h2><i class="fas fa-envelope"></i> Gmail account</h2>

                <div class="form-group cg-field">
                    <label for="gmailEmail">Gmail address</label>
                    <div class="input-group">
                        <input type="email" class="form-control" id="gmailEmail" name="gmail_email" value="<?= $h($email) ?>"
                               placeholder="storagemart.firstnamelastname@gmail.com" pattern=".+@.+\..+" required>
                        <div class="input-group-append">
                            <button type="button" class="btn btn-outline-secondary cg-copy" data-copy="#gmailEmail"><i class="far fa-copy"></i> Copy</button>
                        </div>
                    </div>
                    <?php if ($suggested !== '' && strcasecmp($suggested, $email) !== 0): ?>
                        <small class="form-text text-muted">Company format: <a href="#" class="cg-use" data-value="<?= $h($suggested) ?>"><?= $h($suggested) ?></a></small>
                    <?php endif; ?>
                    <?php if (strcasecmp((string) $employee['email'], $email) !== 0 && $employee['email'] !== ''): ?>
                        <small class="form-text text-muted">Email in the TMS: <?= $h($employee['email']) ?></small>
                    <?php endif; ?>
                </div>

                <div class="form-group cg-field">
                    <label for="gmailPassword">Gmail password</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="gmailPassword" name="gmail_password" value="<?= $hasPassword ? '' : $h($formula) ?>"
                               placeholder="<?= $hasPassword ? '•••••••••• (saved, click Show)' : 'Middle name + Employee ID' ?>"
                               minlength="8" maxlength="100" autocomplete="new-password" spellcheck="false">
                        <div class="input-group-append">
                            <?php if ($hasPassword): ?>
                                <button type="button" class="btn btn-outline-secondary" id="cgShow"><i class="fas fa-eye"></i> Show</button>
                            <?php endif; ?>
                            <button type="button" class="btn btn-outline-secondary" id="cgFormula" data-value="<?= $h($formula) ?>" title="Middle name + Employee ID"><i class="fas fa-calculator"></i> Formula</button>
                            <button type="button" class="btn btn-outline-secondary cg-copy" data-copy="#gmailPassword"><i class="far fa-copy"></i></button>
                        </div>
                    </div>
                    <small class="form-text text-muted">
                        Formula from the corporate email list: <strong>middle name + Employee ID</strong> (=CONCATENATE(middle name, FORMAT))<?= $formula !== '' ? ', here <code>' . $h($formula) . '</code>' : '' ?>.
                        For older staff whose FORMAT was another number, type their password.
                        Admin and HR can see this Gmail password. It is <strong>not</strong> the TMS password: the TMS password is never shown to anyone.
                        <?= $hasPassword ? 'Leave it empty to keep the saved one.' : '' ?>
                    </small>
                </div>

                <h2 class="mt-4"><i class="fas fa-tasks"></i> Set-up checklist</h2>
                <div class="cg-progress mb-3" aria-hidden="true"><span style="width: <?= (int) round($done / max(1, count($checks)) * 100) ?>%"></span></div>
                <?php foreach ($checks as $key => $label): ?>
                    <label class="cg-check">
                        <input type="checkbox" name="checks[<?= $h($key) ?>]" value="1"<?= !empty($gmail[$key]) ? ' checked' : '' ?>>
                        <span><?= $h($label) ?></span>
                    </label>
                <?php endforeach; ?>
                <div class="form-group cg-field mt-3">
                    <label for="cgNotes">Notes</label>
                    <input type="text" class="form-control" id="cgNotes" name="notes" maxlength="255" value="<?= $h($gmail['notes'] ?? '') ?>"
                           placeholder="e.g. recovery email used, phone number used">
                </div>
                <?php if (!empty($gmail['updated_at'])): ?>
                    <p class="cg-note mb-2">Last saved <?= $h(date('M j, Y g:i A', strtotime((string) $gmail['updated_at']))) ?><?= $updatedBy !== '' ? ' by ' . $h($updatedBy) : '' ?>.</p>
                <?php endif; ?>
                <button type="submit" class="btn btn-primary"<?= $ready ? '' : ' disabled' ?>><i class="fas fa-save"></i> Save</button>
            </form>

            <div class="cg-card">
                <h2><i class="fas fa-qrcode"></i> Create it on a phone</h2>
                <p class="cg-note mb-2">
                    Google does not let other systems create Gmail accounts, so the account is made on Google's sign-up page.
                    Scan this QR code with the employee's phone camera to open it there.
                </p>
                <div class="cg-qr" id="cgQr" data-url="<?= $h($signupUrl) ?>" role="img" aria-label="QR code that opens Google sign-up">
                    <span class="cg-note">Loading QR code&hellip;</span>
                </div>
                <p class="text-center mb-1"><button type="button" class="btn btn-primary btn-sm" id="cgOpenSignup" data-url="<?= $h($signupUrl) ?>"><i class="fab fa-google"></i> Open Google sign-up beside this page</button></p>
                <p class="cg-note text-center mb-3">Google does not allow its sign-up page inside other websites, so it opens in a small window next to this one.</p>
                <ol class="cg-steps">
                    <li>Scan the QR code with the employee's phone. Google's <strong>Create a Google Account</strong> page opens.</li>
                    <li>Type the first name, last name, birthday and gender.</li>
                    <li>Choose <strong>Create your own Gmail address</strong> and type <strong><?= $h($localPart !== '' ? $localPart : 'storagemart.firstnamelastname') ?></strong>.</li>
                    <li>Type the Gmail password from the left.</li>
                    <li>Google checks the phone number: on the phone it sends a code by text. If you use a computer, Google shows its own QR code; scan it with the same phone and send the text it prepares.</li>
                    <li>Add the recovery email and turn on 2-step verification, then tick the checklist and click Save.</li>
                </ol>
            </div>
        </div>
    </div>
</div>
</div>
<script src="<?= $h($base) ?>/assets/vendor/jquery/jquery.min.js"></script>
<script src="<?= $h($base) ?>/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="<?= $h($base) ?>/assets/js/storagemart.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcode-generator/1.4.4/qrcode.min.js"></script>
<script>
(function () {
    var base = <?= json_encode($base) ?>;
    var area = <?= json_encode($area) ?>;
    var csrf = <?= json_encode((string) ($_SESSION['csrf_token'] ?? '')) ?>;
    var employeeId = <?= (int) $employee['employee_id'] ?>;
    var pw = document.getElementById('gmailPassword');

    // QR code for Google sign-up (drawn in the browser; nothing is sent anywhere).
    var box = document.getElementById('cgQr');
    if (window.qrcode) {
        var qr = window.qrcode(0, 'M');
        qr.addData(box.getAttribute('data-url'));
        qr.make();
        box.innerHTML = qr.createSvgTag(6, 0);
    } else {
        box.innerHTML = '<span class="cg-note">The QR code could not load. Use the link below instead.</span>';
    }

    function flash(btn, text) {
        var old = btn.innerHTML;
        btn.innerHTML = text;
        setTimeout(function () { btn.innerHTML = old; }, 1400);
    }
    document.querySelectorAll('.cg-copy').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var input = document.querySelector(btn.getAttribute('data-copy'));
            var value = input ? input.value : '';
            if (!value) { flash(btn, 'Empty'); return; }
            var done = function () { flash(btn, '<i class="fas fa-check"></i> Copied'); };
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(value).then(done, function () { input.select(); document.execCommand('copy'); done(); });
            } else {
                input.select();
                document.execCommand('copy');
                done();
            }
        });
    });
    document.querySelectorAll('.cg-use').forEach(function (a) {
        a.addEventListener('click', function (e) {
            e.preventDefault();
            document.getElementById('gmailEmail').value = a.getAttribute('data-value');
        });
    });
    document.getElementById('cgOpenSignup').addEventListener('click', function () {
        var url = this.getAttribute('data-url');
        var w = Math.min(520, screen.availWidth), h = Math.min(820, screen.availHeight - 40);
        var win = window.open(url, 'tmsGoogleSignup', 'width=' + w + ',height=' + h + ',left=' + ((screen.availLeft || 0) + screen.availWidth - w - 10)
            + ',top=' + ((screen.availTop || 0) + 20) + ',resizable=yes,scrollbars=yes');
        if (win) { win.focus(); } else { window.open(url, '_blank', 'noopener'); }
    });
    document.getElementById('cgFormula').addEventListener('click', function () {
        pw.value = this.getAttribute('data-value') || '';
        pw.focus();
        pw.select();
    });
    var show = document.getElementById('cgShow');
    if (show) {
        show.addEventListener('click', function () {
            var body = new FormData();
            body.append('csrf_token', csrf);
            body.append('employee_id', employeeId);
            show.disabled = true;
            fetch(base + '/' + area + '/company-gmail/reveal', { method: 'POST', body: body, credentials: 'same-origin' })
                .then(function (r) { return r.json(); })
                .then(function (res) {
                    show.disabled = false;
                    if (!res.success) { alert(res.message || 'Could not show the password.'); return; }
                    pw.value = res.password;
                    pw.placeholder = '';
                })
                .catch(function () { show.disabled = false; alert('Could not show the password.'); });
        });
    }
})();
</script>
</body>
</html>
