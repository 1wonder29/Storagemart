<?php
$base = rtrim(BASE_URL, '/');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Storage Mart TMS Forgot Password</title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="<?= htmlspecialchars($base) ?>/assets/img/favicon.png">
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/style.css">
</head>

<body>
    <header class="storagemart-header">
        <img src="<?= htmlspecialchars($base) ?>/assets/img/storagemart-logo.png" alt="storagemart Logo" />
    </header>

    <main class="index-main-content">
        <div class="login-box">
            <div class="logo-banner">
                <span class="logo-white">Reset</span>
                <span class="logo-orange">Password</span>
            </div>

            <?php if (isset($forgotMessage) && $forgotMessage): ?>
                <div class="message"><?= $forgotMessage ?></div>
            <?php endif; ?>

            <form action="<?= htmlspecialchars($base) ?>/forgot-password" method="POST" autocomplete="off">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['forgot_csrf'] ?? '') ?>">

                <label for="username">Username</label>
                <input
                    type="text"
                    id="username"
                    name="username"
                    placeholder="Enter your username"
                    value="<?= htmlspecialchars($oldUsername ?? '') ?>"
                    required
                >

                <label for="email">Registered Email</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    placeholder="Enter your registered email"
                    value="<?= htmlspecialchars($oldEmail ?? '') ?>"
                    required
                >

                <label for="new_password">New Password</label>
                <input
                    type="password"
                    id="new_password"
                    name="new_password"
                    placeholder="Minimum 8 characters"
                    required
                >

                <label for="confirm_password">Confirm New Password</label>
                <input
                    type="password"
                    id="confirm_password"
                    name="confirm_password"
                    placeholder="Re-enter new password"
                    required
                >

                <button type="submit">RESET PASSWORD</button>

                <div class="forgot-password">
                    <a href="<?= htmlspecialchars($base) ?>/login">Back to login</a>
                </div>
            </form>
        </div>
    </main>

    <footer class="footer">
        <p>&copy; 2025 storagemart. All rights reserved. For Internal Use Only.</p>
    </footer>
</body>
</html>
