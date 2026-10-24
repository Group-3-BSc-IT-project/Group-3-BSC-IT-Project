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

$services = $serviceController->all();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Services | MSBMS</title>
    <link rel="stylesheet" href="assets/styled.css">
</head>
<body>
<div class="app">

    <aside class="sidebar">
        <div class="logo"><span class="logo-mark">✦</span> MSBMS</div>
        <nav class="nav">
            <a href="<?= e($dashboardLink) ?>"><span class="icon">🏠</span>Dashboard</a>
            <a href="booking_create.php"><span class="icon">📅</span>Create Booking</a>
            <a href="vehicles.php"><span class="icon">🚗</span>My Vehicles</a>
            <a href="vehicle_create.php"><span class="icon">➕</span>Add Vehicle</a>
            <a class="active" href="services.php"><span class="icon">🔧</span>Services</a>
            <a href="notifications.php"><span class="icon">🔔</span>Notifications</a>
            <a href="profile.php"><span class="icon">⚙️</span>Profile Settings</a>
            <?php if (Session::userId()): ?>
                <a href="logout.php"><span class="icon">↪</span>Logout</a>
            <?php endif; ?>
        </nav>
    </aside>

    <main class="main">
        <div class="topbar">
            <div class="title">
                <h1>Services</h1>
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
            <h2>Available Services</h2>
            <div class="table-card">
                <table class="table">
                    <tr>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Price</th>
                    </tr>
                    <?php foreach ($services as $s): ?>
                        <tr>
                            <td><?= e($s['service_name']) ?></td>
                            <td><?= e($s['description']) ?></td>
                            <td>R<?= number_format((float)$s['price'], 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        </div>
        <p style="margin-top:20px">
            <a class="btn secondary" href="<?= e($dashboardLink) ?>">Back to Dash</a>
        </p>
    </main>
                       
</div>
</body>
</html>