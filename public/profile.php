<?php
require_once __DIR__ . '/_init.php';

// ---- Logic ----
Auth::requireLogin();

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    [$ok, $message] = $userController->updateProfile();
}


$user = $userController->profile();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Profile | MSBMS</title>
    <link rel="stylesheet" href="assets/styled.css">
</head>
<body>
<div class="app">

    <aside class="sidebar">
        <div class="logo"><span class="logo-mark">✦</span> MSBMS</div>
        <nav class="nav">
            <a href="index.php"><span class="icon">🏠</span>Dashboard</a>
            <a href="booking_create.php"><span class="icon">📅</span>My Bookings</a>
            <a href="vehicles.php"><span class="icon">🚗</span>My Vehicles</a>
            <a href="history.php"><span class="icon">🔧</span>Service History</a>
            <a href="notifications.php"><span class="icon">🔔</span>Notifications</a>
            <a href="profile.php"><span class="icon">⚙️</span>Profile Settings</a>
            <a href="logout.php"><span class="icon">↪</span>Logout</a>
        </nav>
    </aside>

    <main class="main">

        <div class="topbar">
            <div class="title">
                <h1>Profile</h1>
                <p>Motor Service Booking Management System</p>
            </div>
            <div class="user">
                <span><?= e($user['full_name']) ?></span>
                <span class="avatar"><?= strtoupper(substr($user['full_name'], 0, 1)) ?></span>
            </div>
        </div>

        <div class="card form">
            <?php if ($message): ?>
                <p style="color:var(--red);font-size:12px;margin-bottom:16px"><?= e($message) ?></p>
            <?php endif; ?>

            <form method="post">
                <div class="form-row">
                    <div class="field">
                        <label for="full_name">Full Name</label>
                        <input id="full_name" name="full_name" value="<?= e($user['full_name']) ?>" required>
                    </div>
                    <div class="field">
                        <label for="email">Email</label>
                        <input id="email" type="email" name="email" value="<?= e($user['email']) ?>" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="field">
                        <label for="phone">Phone</label>
                        <input id="phone" name="phone" value="<?= e($user['phone']) ?>">
                    </div>
                    <div class="field">
                        <label for="address">Address</label>
                        <input id="address" name="address" value="<?= e($user['address']) ?>">
                    </div>
                </div>

                <button class="btn" type="submit">Save Changes</button>
            </form>
            <br>
            <a href="customer.php" style="color:var(--blue);font-size:13px;font-weight:600">&larr; Back to Dashboard</a>
            
        </div>

        <div style="margin-top:28px;color:#a0a8b5;font-size:10px">
            &copy; <?= date('Y') ?> MSBMS &middot; Motor Service Booking Management System
            
        </div>
    </main>
    
</div>


</body>
</html>