<?php
require_once __DIR__ . '/_init.php';

// ---- Logic ----
Auth::requireLogin();

$role     = strtolower((string) Session::role());
$isAdmin  = $role === 'admin';

// Where "Dashboard" should go for each role
$dashboardLink = match ($role) {
    'customer' => 'customer.php',
    'admin'    => 'admin.php',
    'mechanic' => 'mechanic.php',
    default    => 'login.php',
};

if (isset($_GET['read'])) {
    $notificationController->read((int)$_GET['read']);
    header('Location: notifications.php');
    exit;
}

if (isset($_GET['mark_all'])) {
    $notificationController->readAll();
    header('Location: notifications.php');
    exit;
}

$notifications = $notificationController->all();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Notifications | MSBMS</title>
    <link rel="stylesheet" href="assets/styled.css?v=<?php echo filemtime(__DIR__ . '/assets/styled.css'); ?>">
</head>
<body>
<div class="app">

    <aside class="sidebar">
        <div class="logo"><span class="logo-mark"><i class="fa-solid fa-screwdriver-wrench" aria-hidden="true"></i></span> MSBMS</div>
        <nav class="nav">
            <a href="<?= e($dashboardLink) ?>"><i class="icon fa-solid fa-house" aria-hidden="true"></i>Dashboard</a>
            <a href="booking_create.php"><i class="icon fa-solid fa-calendar-days" aria-hidden="true"></i>Create Booking</a>
            <a href="vehicles.php"><i class="icon fa-solid fa-car-side" aria-hidden="true"></i>My Vehicles</a>
            <a href="vehicle_create.php"><i class="icon fa-solid fa-circle-plus" aria-hidden="true"></i>Add Vehicle</a>
            <a href="services.php"><i class="icon fa-solid fa-screwdriver-wrench" aria-hidden="true"></i>Services</a>
            <a class="active" href="notifications.php"><i class="icon fa-solid fa-bell" aria-hidden="true"></i>Notifications</a>
            <?php if (Session::userId()): ?>
                <a href="logout.php"><i class="icon fa-solid fa-arrow-right-from-bracket" aria-hidden="true"></i>Logout</a>
            <?php endif; ?>
        </nav>
    </aside>

    <main class="main">
        <div class="topbar">
            <div class="title">
                <h1>Notifications</h1>
                <p>Motor Service Booking Management System</p>
            </div>
            <?php if (Session::userId()): ?>
                <div class="user">
                    <span><?= e(Session::fullName()) ?> (<?= e(Session::role()) ?>)</span>
                    <span class="avatar"><?= strtoupper(substr(Session::fullName(), 0, 1)) ?></span>
                </div>
            <?php endif; ?>
        </div>

        <div class="card section">
            <h2>All Notifications</h2>
            <p style="margin-bottom:16px">
                <a class="btn secondary" href="notifications.php?mark_all=1">Mark all as read</a>
            </p>

            <div class="notice-list">
                <?php foreach ($notifications as $n): ?>
                    <div class="notice">
                        <div class="notice-dot"><i class="fa-solid fa-bell" aria-hidden="true"></i></div>
                        <div>
                            <h3><?= e($n['message']) ?></h3>
                            <p>
                                <?= e($n['is_read'] ? 'Read' : 'Unread') ?>
                                <?php if (!$n['is_read']): ?>
                                    &middot; <a href="notifications.php?read=<?= $n['notification_id'] ?>">Mark read</a>
                                <?php endif; ?>
                            </p>
                            <time><?= e($n['created_at']) ?></time>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </main>

</div>
</body>
</html>