<?php
require_once __DIR__ . '/_init.php';

// ---- Logic ----
Auth::requireRole('admin');

$id      = (int)($_GET['booking_id'] ?? 0);
$booking = $bookingController->find($id);

if (!$booking) {
    exit('Booking not found.');
}

$mechanics = $bookingController->mechanics();
$message   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    [$ok, $message] = $bookingController->assign($id);
    if ($ok) {
        header('Location: admin.php');
        exit;
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Assign Mechanic | MSBMS</title>
    <link rel="stylesheet" href="assets/styled.css">
</head>
<body>
<div class="app">

    <aside class="sidebar">
        <div class="logo"><span class="logo-mark">✦</span> MSBMS</div>
        <nav class="nav">
            <a href="admin.php"><span class="icon">🏠</span>Dashboard</a>
            <a href="booking_create.php"><span class="icon">📅</span>Create Booking</a>
            <a href="vehicles.php"><span class="icon">🚗</span>All Vehicles</a>
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
                <h1>Assign Mechanic</h1>
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
            <p style="margin-bottom:16px">
                 Booking #<?= e($id) ?><br>
                 Customer: <?= e($booking['customer_name']) ?><br>
                 Service: <?= e($booking['service_name']) ?>
            </p>

            <?php if ($message): ?>
                <p style="color:var(--red);font-size:12px;margin-bottom:16px"><?= e($message) ?></p>
            <?php endif; ?>

            <form method="post">
                <div class="field">
                    <label for="mechanic_id">Mechanic</label>
                    <select id="mechanic_id" name="mechanic_id" required>
                        <option value="">Select mechanic</option>
                        <?php foreach ($mechanics as $m): ?>
                            <option value="<?= e($m['user_id']) ?>"><?= e($m['full_name'] . ' ' . $m['phone']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button class="btn" type="submit">Assign Mechanic</button>
                <a class="btn secondary" href="admin.php">Cancel</a>
        
            </form>
        </div>
    </main>

</div>
</body>
</html>