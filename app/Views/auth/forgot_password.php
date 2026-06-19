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
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/auth-login.css">
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
                <p>Verify your account details and set a new password to regain access to TMS.</p>
            </div>

            <p class="auth-brand-footer">&copy; <?= date('Y') ?> Storage Mart</p>
        </aside>

        <main class="auth-form-panel">
            <div class="auth-form-header">
                <h2>Forgot password</h2>
                <p>Enter your username, registered email, and new password</p>
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
                    <label for="new_password">New Password</label>
                    <div class="auth-input-wrap has-toggle">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                        </svg>
                        <input
                            type="password"
                            id="new_password"
                            name="new_password"
                            placeholder="Minimum 8 characters"
                            required
                        >
                        <button type="button" class="auth-toggle-pw" data-target="new_password" aria-label="Show password">
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

                <div class="auth-field">
                    <label for="confirm_password">Confirm New Password</label>
                    <div class="auth-input-wrap has-toggle">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                        </svg>
                        <input
                            type="password"
                            id="confirm_password"
                            name="confirm_password"
                            placeholder="Re-enter new password"
                            required
                        >
                        <button type="button" class="auth-toggle-pw" data-target="confirm_password" aria-label="Show password">
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

                <button type="submit" class="auth-submit">Reset password</button>

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
