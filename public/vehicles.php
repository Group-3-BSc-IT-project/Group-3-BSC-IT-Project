<?php
require_once __DIR__ . '/_init.php';

// ---- Logic ----
Auth::requireLogin();

$vehicles = $vehicleController->list();
$role     = strtolower((string) Session::role());
$isAdmin  = $role === 'admin';

// Where "Dashboard" / "Back to Dash" should go for each role
$dashboardLink = match ($role) {
    'customer' => 'customer.php',
    'admin'    => 'admin.php',
    'mechanic' => 'mechanic.php',
    default    => 'login.php',
};
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title><?= $isAdmin ? 'All Vehicles' : 'My Vehicles' ?> | MSBMS</title>
    <link rel="stylesheet" href="assets/styled.css">
</head>
<body>
<div class="app">

    <aside class="sidebar">
        <div class="logo"><span class="logo-mark">✦</span> MSBMS</div>
        <nav class="nav">
            <a href="<?= e($dashboardLink) ?>"><span class="icon">🏠</span>Dashboard</a>
            <a href="booking_create.php"><span class="icon">📅</span>Create Booking</a>
            <a class="active" href="vehicles.php"><span class="icon">🚗</span><?= $isAdmin ? 'All Vehicles' : 'My Vehicles' ?></a>
            <a href="vehicle_create.php"><span class="icon">➕</span>Add Vehicle</a>
            <a href="services.php"><span class="icon">🔧</span>Services</a>
            <a href="notifications.php"><span class="icon">🔔</span>Notifications</a>
            <?php if (Session::userId()): ?>
                <a href="logout.php"><span class="icon">↪</span>Logout</a>
            <?php endif; ?>
        </nav>
    </aside>

    <main class="main">
        <div class="topbar">
            <div class="title">
                <h1><?= $isAdmin ? 'All Vehicles' : 'My Vehicles' ?></h1>
                <p>Motor Service Booking Management System</p>
            </div>
            <?php if (Session::userId()): ?>
                <div class="user">
                    <span><?= e(Session::fullName()) ?> (<?= e(Session::role()) ?>)</span>
                    <span class="avatar"><?= e(strtoupper(substr((string) Session::fullName(), 0, 1))) ?></span>
                </div>
            <?php endif; ?>
        </div>

        <div class="card section">
            <h2><?= $isAdmin ? 'All Vehicles' : 'My Vehicles' ?></h2>
            <p style="margin-bottom:16px">
                <a class="btn" href="vehicle_create.php">Add Vehicle</a>
            </p>
            <div class="table-card">
                <table class="table">
                    <tr>
                        <th>ID</th>
                        <th>Registration</th>
                        <th>Make</th>
                        <th>Model</th>
                        <th>Year</th>
                        <th>VIN</th>
                        <?php if ($isAdmin): ?>
                            <th>Owner</th>
                        <?php endif; ?>
                    </tr>
                    <?php foreach ($vehicles as $v): ?>
                        <tr>
                            <td><?= e($v['vehicle_id']) ?></td>
                            <td><?= e($v['registration_number']) ?></td>
                            <td><?= e($v['make']) ?></td>
                            <td><?= e($v['model']) ?></td>
                            <td><?= e($v['year']) ?></td>
                            <td><?= e($v['vin']) ?></td>
                            <?php if ($isAdmin): ?>
                                <td><?= e($v['owner_name']) ?></td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($vehicles)): ?>
                        <tr>
                            <td colspan="<?= $isAdmin ? 7 : 6 ?>">No vehicles found.</td>
                        </tr>
                    <?php endif; ?>
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