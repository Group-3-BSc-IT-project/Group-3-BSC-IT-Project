<?php
require_once __DIR__ . '/_init.php';

// ---- Logic ----
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    [$ok, $message] = $authController->register();
    if ($ok) {
        header('Location: login.php');
        exit;
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Register | MSBMS</title>
    <link rel="stylesheet" href="assets/styled.css">
</head>
<body>

<div class="auth-shell">
    <div class="card auth-card wide form">
        <div class="auth-logo"><span class="logo-mark">✦</span>MSBMS</div>

        <?php if (Session::userId()): ?>
            <p class="session-strip">
                Logged in as <?= e(Session::fullName()) ?> (<?= e(Session::role()) ?>)
                &middot; <a href="logout.php">Logout</a>
            </p>
        <?php endif; ?>

        <h1>Create an account</h1>
        <p class="sub">Register as a customer</p>

        <?php if ($message): ?>
            <p class="badge yellow auth-message"><?= e($message) ?></p>
        <?php endif; ?>

        <form method="post">
            <div class="form-row">
                <div class="field">
                    <label>Full Name:</label>
                    <input name="full_name" required>
                </div>
                <div class="field">
                    <label>Email:</label>
                    <input type="email" name="email" required>
                </div>
            </div>
            <div class="form-row">
                <div class="field">
                    <label>Phone:</label>
                    <input name="phone">
                </div>
                <div class="field">
                    <label>Password:</label>
                    <input type="password" name="password" required>
                </div>
            </div>
            <div class="field">
                <label>Address:</label>
                <input name="address">
            </div>
            <button class="btn">Register</button>
        </form>

        <p class="auth-footer">
            Already have an account? <a href="login.php">Login</a>
        </p>
    </div>
</div>

</body>
</html>
