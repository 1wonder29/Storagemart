<?php
$base = rtrim(BASE_URL, '/');
require_once __DIR__ . '/../../Helpers/PasswordPolicy.php';

$passwordFields = [
    'current_password' => ['Current Password', 'Enter your current password'],
    'new_password'     => ['New Password', 'At least 8 characters'],
    'confirm_password' => ['Confirm New Password', 'Re-enter the new password'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Storage Mart TMS Change Password</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="<?= htmlspecialchars($base) ?>/assets/img/favicon.png">
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/auth-login.css?v=20261005">
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/ui-readonly-interaction.css?v=2">
</head>

<body class="auth-page">
    <div class="auth-ambient" aria-hidden="true">
        <span class="auth-ambient-orb auth-ambient-orb--a"></span>
        <span class="auth-ambient-orb auth-ambient-orb--b"></span>
        <span class="auth-ambient-orb auth-ambient-orb--c"></span>
    </div>

    <div class="auth-shell">
        <aside class="auth-brand" aria-hidden="true">
            <div class="auth-brand-logo">
                <img src="<?= htmlspecialchars($base) ?>/assets/img/storagemart-logo.png" alt="StorageMart" />
            </div>

            <div class="auth-brand-content">
                <div class="auth-brand-badge">
                    <span></span>
                    Account Security
                </div>
                <h1>Change your <span class="highlight">password</span></h1>
                <p>If IT gave you a temporary password, replace it with one only you know.</p>
            </div>

            <p class="auth-brand-footer">&copy; <?= date('Y') ?> Storage Mart</p>
        </aside>

        <main class="auth-form-panel">
            <div class="auth-form-header">
                <h2>Change password</h2>
                <p><?= htmlspecialchars(PasswordPolicy::HINT) ?></p>
            </div>

            <?php if (!empty($changeDone)): ?>
                <div class="auth-alert" role="status"><span style="color:green">Your password has been changed.</span></div>
                <a class="auth-submit auth-submit--link" href="<?= htmlspecialchars($base . $homePath) ?>">Back to dashboard</a>
            <?php else: ?>
                <?php if (!empty($changeMessage)): ?>
                    <div class="auth-alert" role="alert"><span style="color:red"><?= htmlspecialchars($changeMessage) ?></span></div>
                <?php endif; ?>

                <form class="auth-form auth-form--compact" action="<?= htmlspecialchars($base) ?>/change-password" method="POST" autocomplete="off">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

                    <?php foreach ($passwordFields as $field => [$label, $placeholder]): ?>
                        <div class="auth-field">
                            <label for="<?= $field ?>"><?= $label ?></label>
                            <div class="auth-input-wrap has-toggle">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                                    <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                                </svg>
                                <input type="password" id="<?= $field ?>" name="<?= $field ?>" placeholder="<?= $placeholder ?>" required
                                    <?= $field === 'current_password' ? 'autofocus' : 'minlength="8"' ?>>
                                <button type="button" class="auth-toggle-pw" data-target="<?= $field ?>" aria-label="Show password">
                                    <svg class="icon-eye" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                        <circle cx="12" cy="12" r="3"/>
                                    </svg>
                                    <svg class="icon-eye-off" hidden xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/>
                                        <path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/>
                                        <line x1="1" y1="1" x2="23" y2="23"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <button type="submit" class="auth-submit">Change password</button>

                    <div class="auth-footer-link">
                        <a href="<?= htmlspecialchars($base . $homePath) ?>">Back to dashboard</a>
                    </div>
                </form>
            <?php endif; ?>
        </main>
    </div>

    <script src="<?= htmlspecialchars($base) ?>/assets/js/auth-login.js"></script>
    <?php require_once __DIR__ . '/../partials/dev_livereload.php'; ?>
</body>
</html>
