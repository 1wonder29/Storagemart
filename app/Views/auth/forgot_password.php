<?php
$base = rtrim(BASE_URL, '/');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Storage Mart TMS Forgot Password</title>

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
                    Account Recovery
                </div>
                <h1>Reset your <span class="highlight">password</span></h1>
                <p>Send a reset request to IT. They will verify that it's you and give you a temporary password.</p>
            </div>

            <p class="auth-brand-footer">&copy; <?= date('Y') ?> Storage Mart</p>
        </aside>

        <main class="auth-form-panel">
            <div class="auth-form-header">
                <h2>Forgot password</h2>
                <p>Enter your username and registered email. IT will contact you to reset your password.</p>
            </div>

            <?php if (isset($forgotMessage) && $forgotMessage): ?>
                <div class="auth-alert" role="alert"><?= $forgotMessage ?></div>
            <?php endif; ?>

            <form class="auth-form auth-form--compact" action="<?= htmlspecialchars($base) ?>/forgot-password" method="POST" autocomplete="off">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['forgot_csrf'] ?? '') ?>">

                <div class="auth-field">
                    <label for="username">Username</label>
                    <div class="auth-input-wrap">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                            <circle cx="12" cy="7" r="4"/>
                        </svg>
                        <input
                            type="text"
                            id="username"
                            name="username"
                            placeholder="Enter your username"
                            value="<?= htmlspecialchars($oldUsername ?? '') ?>"
                            required
                            autofocus
                        >
                    </div>
                </div>

                <div class="auth-field">
                    <label for="email">Registered Email</label>
                    <div class="auth-input-wrap">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                            <polyline points="22,6 12,13 2,6"/>
                        </svg>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            placeholder="Enter your registered email"
                            value="<?= htmlspecialchars($oldEmail ?? '') ?>"
                            required
                        >
                    </div>
                </div>

                <div class="auth-field">
                    <label for="note">How can IT reach you? <span class="auth-optional">(optional)</span></label>
                    <div class="auth-input-wrap">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.79 19.79 0 0 1 2.12 4.18 2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.91.34 1.85.57 2.81.7A2 2 0 0 1 22 16.92z"/>
                        </svg>
                        <input
                            type="text"
                            id="note"
                            name="note"
                            maxlength="255"
                            placeholder="Mobile number, branch, or local"
                        >
                    </div>
                </div>

                <button type="submit" class="auth-submit">Send request to IT</button>

                <div class="auth-footer-link">
                    <a href="<?= htmlspecialchars($base) ?>/login">Back to login</a>
                </div>
            </form>
        </main>
    </div>

    <script src="<?= htmlspecialchars($base) ?>/assets/js/auth-login.js"></script>
    <?php require_once __DIR__ . '/../partials/dev_livereload.php'; ?>
</body>
</html>
