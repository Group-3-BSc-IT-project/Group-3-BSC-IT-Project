<?php
require_once __DIR__ . '/_init.php';

// ---- Logic ----
Auth::requireRole('customer');

$id      = (int)($_GET['id'] ?? 0);
$booking = $bookingController->customerFind($id);

if (!$booking) {
    exit('Booking not found.');
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    [$ok, $message] = $bookingController->reschedule($id);
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
    <title>Reschedule Booking | MSBMS</title>
    <link rel="stylesheet" href="assets/styled.css">
</head>
<body>
<div class="app">

    <aside class="sidebar">
        <div class="logo"><span class="logo-mark">✦</span> MSBMS</div>
        <nav class="nav">
            <a href="customer.php"><span class="icon">🏠</span>Dashboard</a>
            <a href="booking_create.php"><span class="icon">📅</span>My Bookings</a>
            <a href="vehicles.php"><span class="icon">🚗</span>My Vehicles</a>
            <a href="history.php"><span class="icon">🔧</span>Service History</a>
            <a href="notifications.php"><span class="icon">🔔</span>Notifications</a>
            <a href="profile.php"><span class="icon">⚙️</span>Profile</a>
            <?php if (Session::userId()): ?>
                <a href="logout.php"><span class="icon">↪</span>Logout</a>
            <?php endif; ?>
        </nav>
    </aside>

    <main class="main">
        <div class="topbar">
            <div class="title">
                <h1>Reschedule Booking #<?= e($id) ?></h1>
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
                <div class="form-row">
                    <div class="field">
                        <label for="booking_date">New Date</label>
                        <input type="date" id="booking_date" name="booking_date"
                               value="<?= e($booking['booking_date']) ?>" required>
                    </div>
                    <div class="field">
                        <label for="booking_time">New Time</label>
                        <input type="time" id="booking_time" name="booking_time"
                               value="<?= e(substr($booking['booking_time'], 0, 5)) ?>" required>
                    </div>
                </div>

                <button class="btn" type="submit">Reschedule</button>
                <a class="btn secondary" href="customer.php">Cancel</a>
            </form>
        </div>
    </main>

</div>
</body>
</html>