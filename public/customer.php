<?php
require_once __DIR__ . '/_init.php';

// ---- Logic ----
Auth::requireRole('customer');

$bookings = $bookingController->customerBookings();

// Map booking status to a badge style class defined in style.css
function statusBadgeClass($status) {
    switch ($status) {
        case 'Upcoming':
        case 'Confirmed':
            return 'badge blue';
        case 'In Progress':
            return 'badge yellow';
        case 'Completed':
            return 'badge'; // default badge is green
        case 'Canceled':
            return 'badge yellow';
        default:
            return 'badge';
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Customer Dashboard | MSBMS</title>
    <link rel="stylesheet" href="assets/styled.css">
</head>
<body>
<div class="app">

    <aside class="sidebar">
        <div class="logo"><span class="logo-mark">✦</span> MSBMS</div>
        <nav class="nav">
            <a class="active" href="index.php"><span class="icon">🏠</span>Dashboard</a>
            <a href="booking_create.php"><span class="icon">📅</span>Create Booking</a>
            <a href="vehicles.php"><span class="icon">🚗</span>My Vehicles</a>
            <a href="vehicle_create.php"><span class="icon">➕</span>Add Vehicle</a>
            <a href="services.php"><span class="icon">🔧</span>Services</a>
            <a href="history.php"><span class="icon">🔧</span>Service History</a>
            <a href="notifications.php"><span class="icon">🔔</span>Notifications (<?= $notificationController->unread() ?>)</a>
             <a href="profile.php"><span class="icon">⚙️</span>Profile Settings</a>
            <?php if (Session::userId()): ?>
                <a href="logout.php"><span class="icon">↪</span>Logout</a>
            <?php endif; ?>
        </nav>
    </aside>

    <main class="main">
        <div class="topbar">
            <div class="title">
                <h1>Customer Dashboard</h1>
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
            <h2>My Bookings</h2>
            <div class="table-card">
                <table class="table">
                    <tr>
                        <th>ID</th>
                        <th>Vehicle</th>
                        <th>Service</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                    <?php foreach ($bookings as $b): ?>
                        <tr>
                            <td><?= e($b['booking_id']) ?></td>
                            <td><?= e($b['registration_number']) ?></td>
                            <td><?= e($b['service_name']) ?></td>
                            <td><?= e($b['booking_date']) ?></td>
                            <td><?= e($b['booking_time']) ?></td>
                            <td><span class="<?= statusBadgeClass($b['status']) ?>"><?= e($b['status']) ?></span></td>
                            <td>
                                <div class="actions">
                                    <?php if ($b['status'] !== 'Canceled' && $b['status'] !== 'Completed'): ?>
                                        <a class="btn secondary" href="booking_reschedule.php?id=<?= $b['booking_id'] ?>">Reschedule</a>
                                        <a class="btn danger" href="booking_cancel.php?id=<?= $b['booking_id'] ?>">Cancel</a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        </div>

        <div style="margin-top:28px;color:#a0a8b5;font-size:10px">
            &copy; <?= date('Y') ?> MSBMS &middot; Motor Service Booking Management System
        </div>
    </main>

</div>
</body>
</html>