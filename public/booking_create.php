<?php
require_once __DIR__ . '/_init.php';

// ---- Logic ----
// ---- Logic ----
Auth::requireLogin();

$role = strtolower((string) Session::role());

// Where the "Dashboard" link should go for each role
$dashboardLink = match ($role) {
    'customer' => 'customer.php',
    'admin'    => 'admin.php',
    'mechanic' => 'mechanic.php',
    default    => 'login.php', // not logged in or unknown role
};

$vehicles = $bookingController->vehicles();
$services = $bookingController->services();
$message  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    [$ok, $message] = $bookingController->create();
    if ($ok) {
        header('Location: customer.php');
        exit;
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Create Booking | MSBMS</title>
    <link rel="stylesheet" href="assets/styled.css">
</head>
<body>
<div class="app">

    <aside class="sidebar">
        <div class="logo"><span class="logo-mark">✦</span> MSBMS</div>
        <nav class="nav">
            <a href="<?= e($dashboardLink) ?>"><span class="icon">🏠</span>Dashboard</a>
            <a class="active" href="booking_create.php"><span class="icon">📅</span>Create Booking</a>
            <a href="vehicles.php"><span class="icon">🚗</span>My Vehicles</a>
            <a href="vehicle_create.php"><span class="icon">➕</span>Add Vehicle</a>
            <a href="services.php"><span class="icon">🔧</span>Services</a>
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
                <h1>Create Booking</h1>
                <p>Motor Service Booking Management System</p>
            </div>
            <?php if (Session::userId()): ?>
                <div class="user">
                    <span><?= e(Session::fullName()) ?> (<?= e(Session::role()) ?>)</span>
                    <span class="avatar"><?= strtoupper(substr(Session::fullName(), 0, 1)) ?></span>
                </div>
            <?php endif; ?>
        </div>

        <div class="card form">
            <?php if ($message): ?>
                <p style="color:var(--red);font-size:12px;margin-bottom:16px"><?= e($message) ?></p>
            <?php endif; ?>

            <?php if (!$vehicles): ?>
                <p style="font-size:13px;color:var(--muted);margin-bottom:12px">You need to add a vehicle first.</p>
                <a class="btn" href="vehicle_create.php">Add vehicle</a>
            <?php else: ?>
                <form method="post">
                    <div class="form-row">
                        <div class="field">
                            <label for="vehicle_id">Vehicle</label>
                            <select id="vehicle_id" name="vehicle_id" required>
                                <option value="">Select vehicle</option>
                                <?php foreach ($vehicles as $v): ?>
                                    <option value="<?= $v['vehicle_id'] ?>">
                                        <?= e($v['registration_number'] . ' - ' . $v['make'] . ' ' . $v['model']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="field">
                            <label for="service_id">Service</label>
                            <select id="service_id" name="service_id" required>
                                <option value="">Select service</option>
                                <?php foreach ($services as $s): ?>
                                    <option value="<?= $s['service_id'] ?>">
                                        <?= e($s['service_name']) ?> - R<?= number_format((float)$s['price'], 2) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="field">
                            <label for="booking_date">Booking Date</label>
                            <input id="booking_date" type="date" name="booking_date" required>
                        </div>
                        <div class="field">
                            <label for="booking_time">Booking Time</label>
                            <input id="booking_time" type="time" name="booking_time" required>
                        </div>
                    </div>

                    <div class="field">
                        <label for="notes">Notes</label>
                        <textarea id="notes" name="notes" placeholder="Notes" rows="4"
                            style="width:100%;padding:11px 12px;background:#fbfcfe;border:1px solid #dfe4ec;border-radius:7px;font-size:12px;color:#26344c;font-family:inherit;resize:vertical"></textarea>
                    </div>

                    <button class="btn" type="submit">Create Booking</button>
                </form>
            <?php endif; ?>
        </div>
    </main>

</div>
</body>
</html>