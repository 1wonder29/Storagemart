<?php
$base = rtrim(BASE_URL, '/');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Storage Mart TMS Login</title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="<?= htmlspecialchars($base) ?>/assets/img/favicon.png">
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/style.css">
</head>

<body>
    <!-- Header -->
    <header class="storagemart-header">
        <img src="<?= htmlspecialchars($base) ?>/assets/img/storagemart-logo.png" alt="storagemart Logo" />
    </header>

    <!-- Main Content -->
    <main class="index-main-content">
        <div class="login-box">
            <div class="logo-banner">
                <span class="logo-white">TMS</span><span class="logo-orange">mart</span>
            </div>

            <?php if(isset($loginMessage)) : ?>
                <div class="message"><?= $loginMessage ?></div>
            <?php endif; ?>

            <!-- Use the base path so action will be correct even when project is in a subfolder -->
            <form action="<?= htmlspecialchars($base) ?>/login-post" method="POST">
                <label for="username">Username</label>
                <input 
                    type="text"
                    id="txtUsername"
                    name="txtUsername"
                    placeholder="Enter your username"
                    required
                >

                <label for="password">Password</label>
                <input 
                    type="password"
                    id="txtPassword"
                    name="txtPassword"
                    placeholder="Enter your password"
                    required
                >

                <button type="submit" name="btnLogin">LOG IN</button>

                <div class="forgot-password">
                    <a href="#">Forgot password?</a>
                </div>
            </form>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <p>&copy; 2025 storagemart. All rights reserved. For Internal Use Only.</p>
    </footer>
    <script src="<?= htmlspecialchars($base) ?>/assets/author/ouaaa.js"></script>
</body>
</html>
