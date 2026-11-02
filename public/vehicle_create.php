<?php
require_once __DIR__ . '/_init.php';

// ---- Logic ----
Auth::requireAnyRole(['customer', 'admin']);

$role     = strtolower((string) Session::role());
$isAdmin  = $role === 'admin';

// Where "Dashboard" should go for each role
$dashboardLink = match ($role) {
    'customer' => 'customer.php',
    'admin'    => 'admin.php',
    'mechanic' => 'mechanic.php',
    default    => 'login.php',
};

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    [$ok, $message] = $vehicleController->create();
    if ($ok) {
        header('Location: vehicles.php');
        exit;
    }
}

$customers = $isAdmin ? $vehicleController->customers() : [];
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Add Vehicle | MSBMS</title>
    <link rel="stylesheet" href="assets/styled.css?v=<?php echo filemtime(__DIR__ . '/assets/styled.css'); ?>">
</head>
<body>
<div class="app">

    <aside class="sidebar">
        <div class="logo"><span class="logo-mark"><i class="fa-solid fa-screwdriver-wrench" aria-hidden="true"></i></span> MSBMS</div>
        <nav class="nav">
            <a href="<?= e($dashboardLink) ?>"><i class="icon fa-solid fa-house" aria-hidden="true"></i>Dashboard</a>
            <?php if ($isAdmin): ?>
                <a href="mechanic_create.php"><i class="icon fa-solid fa-user-gear" aria-hidden="true"></i>Create Mechanic</a>
                <a class="active" href="vehicle_create.php"><i class="icon fa-solid fa-circle-plus" aria-hidden="true"></i>Add Vehicle</a>
                <a href="vehicles.php"><i class="icon fa-solid fa-car-side" aria-hidden="true"></i>Vehicles</a>
                <a href="services.php"><i class="icon fa-solid fa-screwdriver-wrench" aria-hidden="true"></i>Services</a>
                <a href="quotes.php"><i class="icon fa-solid fa-comments" aria-hidden="true"></i>Quote Requests</a>
                <a href="history.php"><i class="icon fa-solid fa-clipboard-list" aria-hidden="true"></i>Service History</a>
            <?php else: ?>
                <a href="booking_create.php"><i class="icon fa-solid fa-calendar-days" aria-hidden="true"></i>Create Booking</a>
                <a href="vehicles.php"><i class="icon fa-solid fa-car-side" aria-hidden="true"></i>My Vehicles</a>
                <a class="active" href="vehicle_create.php"><i class="icon fa-solid fa-circle-plus" aria-hidden="true"></i>Add Vehicle</a>
                <a href="services.php"><i class="icon fa-solid fa-screwdriver-wrench" aria-hidden="true"></i>Services</a>
                <a href="history.php"><i class="icon fa-solid fa-clipboard-list" aria-hidden="true"></i>Service History</a>
            <?php endif; ?>
            <a href="notifications.php"><i class="icon fa-solid fa-bell" aria-hidden="true"></i>Notifications<?php $unread = $notificationController->unread(); if ($unread): ?> (<?= (int)$unread ?>)<?php endif; ?></a>
            <a href="profile.php"><i class="icon fa-solid fa-user" aria-hidden="true"></i>Profile</a>
            <?php if (Session::userId()): ?>
                <a href="logout.php"><i class="icon fa-solid fa-arrow-right-from-bracket" aria-hidden="true"></i>Logout</a>
            <?php endif; ?>
        </nav>
    </aside>

    <main class="main">
        <div class="topbar">
            <div class="title">
                <h1>Add Vehicle</h1>
                <p>Motor Service Booking Management System</p>
            </div>
            <?php if (Session::userId()): ?>
                <div class="user">
                    <span><?= e(Session::fullName()) ?> (<?= e(Session::role()) ?>)</span>
                    <span class="avatar"><?= e(strtoupper(substr((string) Session::fullName(), 0, 1))) ?></span>
                </div>
            <?php endif; ?>
        </div>

        <div class="card form">
            <?php if ($message): ?>
                <p style="color:var(--red);font-size:12px;margin-bottom:16px"><?= e($message) ?></p>
            <?php endif; ?>

            <form method="post">
                <?php if ($isAdmin): ?>
                    <div class="field">
                        <label for="customer_id">Customer</label>
                        <select id="customer_id" name="customer_id" required>
                            <option value="">Select customer</option>
                            <?php foreach ($customers as $c): ?>
                                <option value="<?= e($c['user_id']) ?>"><?= e($c['full_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endif; ?>

                <div class="form-row">
                    <div class="field">
                        <label for="registration_number">Registration Number</label>
                        <input id="registration_number" name="registration_number" placeholder="Registration number" required>
                    </div>
                    <div class="field">
                        <label for="make">Make</label>
                        <input id="make" name="make" placeholder="Make" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="field">
                        <label for="model">Model</label>
                        <input id="model" name="model" placeholder="Model" required>
                    </div>
                    <div class="field">
                        <label for="year">Year</label>
                        <input id="year" name="year" placeholder="Year">
                    </div>
                </div>

                <div class="field">
                    <label for="vin">VIN</label>
                    <input id="vin" name="vin" placeholder="VIN">
                </div>

                <button class="btn" type="submit">Save Vehicle</button>
                <a class="btn secondary" href="vehicles.php">Cancel</a>
            </form>
        </div>
    </main>

</div>
</body>
</html>