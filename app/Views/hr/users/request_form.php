<?php
$base = rtrim(BASE_URL, '/');
$h = static fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
$old = $old ?? [];
$v = static fn(string $key) => htmlspecialchars((string) ($old[$key] ?? ''), ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Storage Mart | Request a New User</title>
    <link href="<?= $h($base) ?>/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900" rel="stylesheet">
    <link rel="icon" href="<?= $h($base) ?>/assets/img/sm-icon-32.png?v=20261016" type="image/x-icon">
    <link href="<?= $h($base) ?>/assets/css/storagemart.css" rel="stylesheet">
    <link href="<?= $h($base) ?>/assets/css/hr-dashboard.css?v=20261007" rel="stylesheet">
    <link href="<?= $h($base) ?>/assets/css/hr-pages.css" rel="stylesheet">
    <style>
        .ur-steps { display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 1.25rem; }
        .ur-step { flex: 1 1 200px; display: flex; gap: 10px; align-items: flex-start; background: #fff; border: 1px solid #e6ecf7; border-radius: .85rem; padding: 10px 12px; font-size: .85rem; color: #475569; }
        .ur-step b { color: #19398a; display: block; }
        .ur-step .n { width: 26px; height: 26px; border-radius: 50%; background: #f85f00; color: #fff; font-weight: 800; display: inline-flex; align-items: center; justify-content: center; flex: 0 0 auto; }
        .ur-section { font-weight: 800; color: #19398a; font-size: .95rem; margin: 6px 0 10px; }
        html.it-dark .ur-step { background: #232838; border-color: #343a4f; color: #cbd5e1; }
    </style>
</head>
<body id="page-top">
<div id="wrapper">
    <?php
    $activePage = 'user-requests';
    require_once dirname(dirname(__DIR__)) . '/partials/hr/sidebar_topbar.php';
    ?>
    <div class="container-fluid hr-dashboard-page">
        <div class="page-hero">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h1><i class="fas fa-user-plus mr-2"></i>Request a New User</h1>
                    <p>Fill in the new employee's account and details. The account is created only after the HR Head and the Admin approve.</p>
                </div>
                <div class="col-lg-4 text-lg-right mt-3 mt-lg-0">
                    <a href="<?= $h($base) ?>/hr/user-requests" class="btn btn-light btn-sm"><i class="fas fa-arrow-left"></i> My requests</a>
                </div>
            </div>
        </div>

        <div class="ur-steps">
            <div class="ur-step"><span class="n">1</span><span><b>HR fills in this form</b>Login, role, branch, department and personal details.</span></div>
            <div class="ur-step"><span class="n">2</span><span><b>The HR Head approves</b>Skipped when the HR Head sends the request.</span></div>
            <div class="ur-step"><span class="n">3</span><span><b>The Admin approves</b>The account is created. Give the first password to the person.</span></div>
        </div>

        <?php if (!empty($_SESSION['flash_error'])): ?>
            <div class="alert alert-danger"><i class="fas fa-exclamation-circle mr-1"></i> <?= $h($_SESSION['flash_error']) ?></div>
            <?php unset($_SESSION['flash_error']); ?>
        <?php endif; ?>

        <div class="card shadow mb-4">
            <div class="card-body">
                <form method="POST" action="<?= $h($base) ?>/hr/user-requests/new" autocomplete="off">
                    <input type="hidden" name="csrf_token" value="<?= $h($_SESSION['csrf_token'] ?? '') ?>">

                    <div class="ur-section"><i class="fas fa-key mr-1"></i> Account</div>
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label class="form-label" for="username">Username (company email)</label>
                            <input type="text" class="form-control" id="username" name="username" value="<?= $v('username') ?>" placeholder="storagemart.firstlast@gmail.com" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="form-label" for="password">First password</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="password" name="password" required autocomplete="new-password" placeholder="For example the SURNAME in capitals">
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary" type="button" onclick="var p=document.getElementById('password');p.type=p.type==='password'?'text':'password';" aria-label="Show or hide the password"><i class="fas fa-eye"></i></button>
                                </div>
                            </div>
                            <small class="form-text text-muted">Give it to the employee after the account is approved. They change it after signing in.</small>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="form-label" for="usertype">User type</label>
                            <select class="custom-select" id="usertype" name="usertype" required>
                                <option value="">-- Choose --</option>
                                <?php foreach ($roleOptions as $code => $label): ?>
                                    <option value="<?= $h($code) ?>"<?= ($old['usertype'] ?? '') === $code ? ' selected' : '' ?>><?= $h($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <small class="form-text text-muted">IT and Admin accounts are made by the Admin.</small>
                        </div>
                    </div>

                    <div class="ur-section mt-2"><i class="fas fa-id-badge mr-1"></i> Employee details</div>
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label class="form-label" for="employee_id">Employee ID</label>
                            <input type="text" class="form-control" id="employee_id" name="employee_id" value="<?= $v('employee_id') ?>" inputmode="numeric" pattern="[0-9]+" maxlength="11" title="Numbers only" placeholder="Numbers only" required data-numbers-only>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="form-label" for="branch_id">Branch</label>
                            <select class="custom-select" id="branch_id" name="branch_id" required>
                                <option value="">-- Choose --</option>
                                <?php foreach ($branches as $b): ?>
                                    <option value="<?= (int) $b['branch_id'] ?>"<?= (int) ($old['branch_id'] ?? 0) === (int) $b['branch_id'] ? ' selected' : '' ?>><?= $h($b['branchName']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="form-label" for="lastname">Last name</label>
                            <input type="text" class="form-control" id="lastname" name="lastname" value="<?= $v('lastname') ?>" required>
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="form-label" for="firstname">First name</label>
                            <input type="text" class="form-control" id="firstname" name="firstname" value="<?= $v('firstname') ?>" required>
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="form-label" for="middlename">Middle name</label>
                            <input type="text" class="form-control" id="middlename" name="middlename" value="<?= $v('middlename') ?>">
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="form-label" for="department">Department</label>
                            <select class="custom-select" id="department" name="department" required>
                                <option value="">-- Choose --</option>
                                <?php foreach ($departments as $dept): ?>
                                    <option value="<?= $h($dept['code']) ?>"<?= ($old['department'] ?? '') === (string) $dept['code'] ? ' selected' : '' ?>><?= $h($dept['label']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="form-label" for="position">Position</label>
                            <input type="text" class="form-control" id="position" name="position" value="<?= $v('position') ?>" required>
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="form-label" for="email">Email</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?= $v('email') ?>" pattern=".+@.+\..+" title="A full email address with @" placeholder="storagemart.firstnamelastname@gmail.com" data-company-email data-first="#firstname" data-last="#lastname">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label class="form-label" for="gmail_password">Gmail password <span class="text-muted small">(company Gmail, optional)</span></label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="gmail_password" name="gmail_password" minlength="8" maxlength="100" autocomplete="new-password" spellcheck="false" placeholder="Middle name + Employee ID" data-gmail-formula>
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-outline-secondary" data-gmail-formula-btn="#gmail_password" title="Middle name + Employee ID"><i class="fas fa-calculator"></i> Formula</button>
                                </div>
                            </div>
                            <small class="form-text text-muted">Formula from the corporate email list: middle name + Employee ID (e.g. C.2600192); it fills in by itself. Not the TMS password. After the account is made, Admin and HR see it on the Company Gmail page (with a QR code to create the Gmail on a phone).</small>
                        </div>
                    </div>

                    <?php require dirname(__DIR__, 2) . '/admin/account/_personal_fields.php'; ?>

                    <div class="form-group mt-3">
                        <label class="form-label" for="reason">Note for the approvers <span class="text-muted small">(optional)</span></label>
                        <input type="text" class="form-control" id="reason" name="reason" maxlength="255" value="<?= $v('reason') ?>" placeholder="e.g. New hire, starts on Monday at Pasig branch">
                    </div>

                    <div class="d-flex justify-content-end flex-wrap" style="gap:8px">
                        <a href="<?= $h($base) ?>/hr/user-requests" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Send for approval</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
</div>
<script src="<?= $h($base) ?>/assets/vendor/jquery/jquery.min.js"></script>
<script src="<?= $h($base) ?>/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="<?= $h($base) ?>/assets/js/storagemart.min.js"></script>
<script src="<?= $h($base) ?>/assets/js/user-form.js?v=20261016"></script>
</body>
</html>
