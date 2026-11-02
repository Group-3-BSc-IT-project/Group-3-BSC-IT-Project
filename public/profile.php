<?php
require_once __DIR__ . '/_init.php';

// ---- Logic ----
Auth::requireLogin();
$role = strtolower((string)Session::role());
$dashboardLink = match ($role) {
    'admin' => 'admin.php',
    'mechanic' => 'mechanic.php',
    default => 'customer.php',
};

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
    <link rel="stylesheet" href="assets/styled.css?v=<?php echo filemtime(__DIR__ . '/assets/styled.css'); ?>">
</head>
<body>
<div class="app">

    <aside class="sidebar">
        <div class="logo"><span class="logo-mark"><i class="fa-solid fa-screwdriver-wrench" aria-hidden="true"></i></span> MSBMS</div>
        <nav class="nav">
            <a href="<?= e($dashboardLink) ?>"><i class="icon fa-solid fa-house" aria-hidden="true"></i>Dashboard</a>
            <?php if ($role === 'customer'): ?>
                <a href="booking_create.php"><i class="icon fa-solid fa-calendar-days" aria-hidden="true"></i>Create Booking</a>
                <a href="vehicles.php"><i class="icon fa-solid fa-car-side" aria-hidden="true"></i>My Vehicles</a>
                <a href="vehicle_create.php"><i class="icon fa-solid fa-circle-plus" aria-hidden="true"></i>Add Vehicle</a>
                <a href="services.php"><i class="icon fa-solid fa-screwdriver-wrench" aria-hidden="true"></i>Services</a>
                <a href="history.php"><i class="icon fa-solid fa-clipboard-list" aria-hidden="true"></i>Service History</a>
            <?php elseif ($role === 'admin'): ?>
                <a href="mechanic_create.php"><i class="icon fa-solid fa-user-gear" aria-hidden="true"></i>Create Mechanic</a>
                <a href="vehicle_create.php"><i class="icon fa-solid fa-circle-plus" aria-hidden="true"></i>Add Vehicle</a>
                <a href="vehicles.php"><i class="icon fa-solid fa-car-side" aria-hidden="true"></i>Vehicles</a>
                <a href="services.php"><i class="icon fa-solid fa-screwdriver-wrench" aria-hidden="true"></i>Services</a>
                <a href="quotes.php"><i class="icon fa-solid fa-comments" aria-hidden="true"></i>Quote Requests</a>
                <a href="history.php"><i class="icon fa-solid fa-clipboard-list" aria-hidden="true"></i>Service History</a>
            <?php elseif ($role === 'mechanic'): ?>
                <a href="history.php"><i class="icon fa-solid fa-clipboard-list" aria-hidden="true"></i>Service History</a>
            <?php endif; ?>
            <a href="notifications.php"><i class="icon fa-solid fa-bell" aria-hidden="true"></i>Notifications<?php $unread = $notificationController->unread(); if ($unread): ?> (<?= (int)$unread ?>)<?php endif; ?></a>
            <a class="active" href="profile.php"><i class="icon fa-solid fa-user" aria-hidden="true"></i>Profile</a>
            <?php if (Session::userId()): ?>
                <a href="logout.php"><i class="icon fa-solid fa-arrow-right-from-bracket" aria-hidden="true"></i>Logout</a>
            <?php endif; ?>
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
            <a href="<?= e($dashboardLink) ?>" style="color:var(--blue);font-size:13px;font-weight:600">&larr; Back to Dashboard</a>
        </div>

        <div style="margin-top:28px;color:#a0a8b5;font-size:10px">
            &copy; <?= date('Y') ?> MSBMS &middot; Motor Service Booking Management System
        </div>
    </main>
</div>

</body>
</html>