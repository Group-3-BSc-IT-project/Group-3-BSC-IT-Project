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
    <link rel="stylesheet" href="assets/styled.css?v=<?php echo filemtime(__DIR__ . '/assets/styled.css'); ?>">
</head>
<body>
<div class="app">

    <aside class="sidebar">
        <div class="logo"><span class="logo-mark"><i class="fa-solid fa-screwdriver-wrench" aria-hidden="true"></i></span> MSBMS</div>
        <nav class="nav">
            <a class="active" href="customer.php"><i class="icon fa-solid fa-house" aria-hidden="true"></i>Dashboard</a>
            <a href="booking_create.php"><i class="icon fa-solid fa-calendar-days" aria-hidden="true"></i>Create Booking</a>
            <a href="vehicles.php"><i class="icon fa-solid fa-car-side" aria-hidden="true"></i>My Vehicles</a>
            <a href="vehicle_create.php"><i class="icon fa-solid fa-circle-plus" aria-hidden="true"></i>Add Vehicle</a>
            <a href="services.php"><i class="icon fa-solid fa-screwdriver-wrench" aria-hidden="true"></i>Services</a>
            <a href="history.php"><i class="icon fa-solid fa-clipboard-list" aria-hidden="true"></i>Service History</a>
            <a href="notifications.php"><i class="icon fa-solid fa-bell" aria-hidden="true"></i>Notifications (<?= $notificationController->unread() ?>)</a>
            <a href="profile.php"><i class="icon fa-solid fa-user" aria-hidden="true"></i>Profile</a>
            <?php if (Session::userId()): ?>
                <a href="logout.php"><i class="icon fa-solid fa-arrow-right-from-bracket" aria-hidden="true"></i>Logout</a>
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
                        <th>Price / Quote</th>
                        <th>Actions</th>
                    </tr>
                    <?php foreach ($bookings as $b): ?>
                        <tr>
                            <td><?= e($b['booking_id']) ?></td>
                            <td><?= e($b['registration_number']) ?></td>
                            <td><?= e($b['custom_service'] ?: $b['service_name']) ?></td>
                            <td><?= e($b['booking_date']) ?></td>
                            <td><?= e($b['booking_time']) ?></td>
                            <td><span class="<?= statusBadgeClass($b['status']) ?>"><?= e($b['status']) ?></span></td>
                            <td>
                                <?php if ($b['final_price'] !== null): ?>
                                    R<?= number_format((float)$b['final_price'], 2) ?> (final)
                                <?php elseif ($b['quote_status'] === 'Requested'): ?>
                                    <span class="badge yellow">Quote requested</span>
                                <?php elseif ($b['quote_status'] === 'Quoted'): ?>
                                    Quote: <strong>R<?= number_format((float)$b['quote_price'], 2) ?></strong>
                                    <form method="post" action="quote_respond.php" style="display:inline">
                                        <input type="hidden" name="booking_id" value="<?= e($b['booking_id']) ?>">
                                        <button class="btn" name="decision" value="accept">Accept</button>
                                        <button class="btn secondary" name="decision" value="decline">Decline</button>
                                    </form>
                                <?php elseif ($b['quote_status'] === 'Accepted'): ?>
                                    R<?= number_format((float)$b['quote_price'], 2) ?> (quote accepted)
                                <?php else: ?>
                                    R<?= number_format((float)$b['price'], 2) ?> (estimated)
                                <?php endif; ?>
                            </td>
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