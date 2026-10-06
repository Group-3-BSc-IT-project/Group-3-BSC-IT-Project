<?php
require_once __DIR__ . '/_init.php';

// ---- Logic ----
Auth::requireRole('mechanic');

$bookings = $bookingController->mechanicBookings();
$unread   = $notificationController->unread();

// Map booking status to a badge style class defined in styled.css
function mechanicStatusBadgeClass($status) {
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
    <title>Mechanic Dashboard | MSBMS</title>
    <link rel="stylesheet" href="assets/styled.css">
</head>
<body>
<div class="app">

    <aside class="sidebar">
        <div class="logo"><span class="logo-mark">✦</span> MSBMS</div>
        <nav class="nav">
            <a class="active" href="mechanic.php"><span class="icon">🏠</span>Dashboard</a>
            <a href="notifications.php"><span class="icon">🔔</span>Notifications<?php if ($unread): ?> (<?= e($unread) ?>)<?php endif; ?></a>
            <?php if (Session::userId()): ?>
                <a href="logout.php"><span class="icon">↪</span>Logout</a>
            <?php endif; ?>
        </nav>
    </aside>

    <main class="main">
        <div class="topbar">
            <div class="title">
                <h1>Mechanic Dashboard</h1>
                <p>Your assigned bookings and repairs</p>
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
                        <th>Customer</th>
                        <th>Vehicle</th>
                        <th>Service</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                    <?php foreach ($bookings as $b): ?>
                        <tr>
                            <td><?= e($b['booking_id']) ?></td>
                            <td><?= e($b['customer_name']) ?></td>
                            <td><?= e($b['registration_number']) ?></td>
                            <td><?= e($b['service_name']) ?></td>
                            <td><?= e($b['booking_date']) ?></td>
                            <td><?= e($b['booking_time']) ?></td>
                            <td><span class="<?= mechanicStatusBadgeClass($b['status']) ?>"><?= e($b['status']) ?></span></td>
                            <td>
                                <?php if ($b['status'] !== 'Completed'): ?>
                                    <a class="btn secondary" href="repair_create.php?booking_id=<?= $b['booking_id'] ?>">Add Repair</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($bookings)): ?>
                        <tr>
                            <td colspan="8" style="text-align:center;color:var(--muted)">No bookings assigned yet.</td>
                        </tr>
                    <?php endif; ?>
                </table>
            </div>
        </div>
    </main>

</div>
</body>
</html>