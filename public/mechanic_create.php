<?php
require_once __DIR__ . '/_init.php';
require_once __DIR__ . '/../app/Repositories/PasswordResetRepository.php';
require_once __DIR__ . '/../app/Controllers/PasswordResetController.php';

Auth::requireRole('admin');

$inviteController = new PasswordResetController(new PasswordResetRepository(), false);

$message = '';
$ok = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    [$ok, $message] = $inviteController->inviteMechanic();
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Create Mechanic | MSBMS</title>
    <link rel="stylesheet" href="assets/styled.css?v=<?php echo filemtime(__DIR__ . '/assets/styled.css'); ?>">
</head>
<body>

<div class="auth-shell">
    <div class="card auth-card wide form">
        <div class="auth-logo"><span class="logo-mark"><i class="fa-solid fa-screwdriver-wrench" aria-hidden="true"></i></span>MSBMS</div>

        <h1>Create Mechanic</h1>
        <p class="sub">The mechanic will receive an email to set their own password.</p>

        <?php if ($message): ?>
            <p class="badge <?= $ok ? '' : 'yellow' ?> auth-message"><?= e($message) ?></p>
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
                    <input type="tel" name="phone" pattern="^(\+27|0)[6-8][0-9]{8}$" placeholder="e.g. 0821234567">
                </div>
                <div class="field">
                    <label>Address</label>
                    <input name="address">
                </div>
            </div>
            <button class="btn">Create Mechanic &amp; Send Invite</button>
        </form>

        <p class="auth-footer"><a href="admin.php">Back to Dashboard</a></p>
    </div>
</div>

</body>
</html>