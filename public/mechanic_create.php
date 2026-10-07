<?php
require_once __DIR__ . '/_init.php';

// ---- Logic ----
Auth::requireRole('admin');

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    [$ok, $message] = $authController->createMechanic();
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Create Mechanic | MSBMS</title>
    <link rel="stylesheet" href="assets/styled.css">
</head>
<body>

<div class="auth-shell">
    <div class="card auth-card wide form">
        <div class="auth-logo"><span class="logo-mark">✦</span>MSBMS</div>

        

        <h1>Create Mechanic</h1>
        <p class="sub">Add a new mechanic account</p>

        <?php if ($message): ?>
            <p class="badge yellow auth-message"><?= e($message) ?></p>
        <?php endif; ?>

        <form method="post">
            <div class="form-row">
                <div class="field">
                    <label>Full name</label>
                    <input name="full_name" required>
                </div>
                <div class="field">
                    <label>Email</label>
                    <input type="email" name="email" required>
                </div>
            </div>
            <div class="form-row">
                <div class="field">
                    <label>Phone</label>
                    <input name="phone">
                </div>
                <div class="field">
                    <label>Password</label>
                    <input type="password" name="password" required>
                </div>
            </div>
            <div class="field">
                <label>Address</label>
                <input name="address">
            </div>
            <button class="btn">Create Mechanic</button>
        </form>

        <p class="auth-footer">
            <a href="index.php">Back to Dashboard</a>
        </p>
    </div>
</div>

</body>
</html>