<?php
/**
 * Company Gmail step inside Add User (Admin) and New user request (HR). The Gmail is made
 * first; the TMS account can only be sent when every step below is ticked.
 * Google does not allow its sign-up page inside other websites (it sends X-Frame-Options: DENY),
 * so "Open Google sign-up" opens it in a small window beside this page, and the QR code opens it
 * on the employee's phone.
 * Needs: $gmailChecksOld (ticked steps from a form sent back with an error). Uses #email and #gmail_password.
 */
require_once __DIR__ . '/../../../Models/CompanyGmailModel.php';
$gmailChecksOld = is_array($gmailChecksOld ?? null) ? $gmailChecksOld : [];
$gsChecks = CompanyGmailModel::CHECKS;
?>
<style>
    .gs-card { border: 1px solid #dbe4f5; border-radius: 1rem; background: linear-gradient(180deg, #f7f9ff, #fff); padding: 16px; margin: 6px 0 18px; }
    .gs-grid { display: grid; grid-template-columns: 210px minmax(0, 1fr); gap: 18px; }
    @media (max-width: 767.98px) { .gs-grid { grid-template-columns: 1fr; } }
    .gs-side { text-align: center; }
    .gs-qr { width: 170px; height: 170px; margin: 0 auto 6px; padding: 8px; background: #fff; border: 1px solid #e6ecf7; border-radius: 12px; display: flex; align-items: center; justify-content: center; }
    .gs-qr svg { width: 100%; height: auto; display: block; }
    .gs-side small { display: block; color: #64748b; margin-bottom: 10px; }
    .gs-steps { counter-reset: gs; list-style: none; margin: 0 0 12px; padding: 0; }
    .gs-steps li { counter-increment: gs; position: relative; padding: 0 0 8px 34px; font-size: .88rem; color: #334155; }
    .gs-steps li::before { content: counter(gs); position: absolute; left: 0; top: 0; width: 24px; height: 24px; border-radius: 50%; background: #f85f00; color: #fff; font-weight: 800; font-size: .75rem; display: flex; align-items: center; justify-content: center; }
    .gs-value { font-family: Consolas, monospace; background: #eef2fb; border-radius: 6px; padding: 1px 6px; color: #0f2563; word-break: break-all; }
    .gs-copy { border: 0; background: none; color: #2661d4; padding: 0 4px; font-size: .8rem; cursor: pointer; }
    .gs-checks { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 8px; }
    @media (max-width: 991.98px) { .gs-checks { grid-template-columns: 1fr; } }
    .gs-check { display: flex; gap: 10px; align-items: flex-start; padding: 9px 11px; border: 1px solid #e2e8f3; border-radius: .7rem; background: #fff; cursor: pointer; margin: 0; font-size: .87rem; }
    .gs-check input { width: 18px; height: 18px; margin-top: 1px; flex: 0 0 auto; }
    .gs-check.is-done { border-color: #86efac; background: #f0fdf4; }
    .gs-progress { margin-top: 10px; font-size: .85rem; font-weight: 700; color: #c2410c; }
    .gs-progress.is-done { color: #15803d; }
    html.it-dark .gs-card { background: #232838; border-color: #343a4f; }
    html.it-dark .gs-steps li { color: #cbd5e1; }
    html.it-dark .gs-value { background: #1c2130; color: #c7d2fe; }
    html.it-dark .gs-check { background: #1c2130; border-color: #343a4f; color: #e2e5ea; }
    html.it-dark .gs-check.is-done { background: rgba(34, 197, 94, .12); border-color: #22c55e; }
    html.it-dark .gs-copy { color: #8fb0ff; }
</style>
<div class="gs-card" data-gmail-setup>
    <div class="gs-grid">
        <div class="gs-side">
            <div class="gs-qr" data-signup-qr="https://accounts.google.com/signup" role="img" aria-label="QR code that opens Google sign-up"></div>
            <small>Scan with the employee's phone</small>
            <button type="button" class="btn btn-primary btn-sm" data-open-signup="https://accounts.google.com/signup">
                <i class="fab fa-google"></i> Open Google sign-up
            </button>
            <small class="mt-2">Opens in a small window beside this page. Google does not allow its page inside other websites.</small>
        </div>
        <div>
            <ol class="gs-steps">
                <li>Open Google sign-up: scan the QR code with the employee's phone, or click <b>Open Google sign-up</b>.</li>
                <li>Type the first name, last name, birthday and gender.</li>
                <li>Choose <b>Create your own Gmail address</b> and type <span class="gs-value" data-gs-local>storagemart.firstnamelastname</span>
                    <button type="button" class="gs-copy" data-gs-copy="local" title="Copy"><i class="far fa-copy"></i></button></li>
                <li>Type the Gmail password <span class="gs-value" data-gs-password>middle name + Employee ID</span>
                    <button type="button" class="gs-copy" data-gs-copy="password" title="Copy"><i class="far fa-copy"></i></button></li>
                <li>Google checks a phone: on a phone it sends a code by text; on a computer it shows its own QR code, scan it with the same phone.</li>
                <li>Add the recovery email (the employee's personal email) and turn on 2-step verification.</li>
            </ol>
            <div class="gs-checks">
                <?php foreach ($gsChecks as $key => $label): $on = !empty($gmailChecksOld[$key]); ?>
                    <label class="gs-check<?= $on ? ' is-done' : '' ?>">
                        <input type="checkbox" name="gmail_checks[<?= htmlspecialchars($key) ?>]" value="1" required<?= $on ? ' checked' : '' ?>>
                        <span><?= htmlspecialchars($label) ?></span>
                    </label>
                <?php endforeach; ?>
            </div>
            <div class="gs-progress" data-gs-progress aria-live="polite"></div>
        </div>
    </div>
</div>
