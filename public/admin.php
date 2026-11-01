<?php
require_once __DIR__ . '/_init.php';

// ---- Logic ----
Auth::requireRole('admin');

$stats    = $adminController->stats();
$bookings = $bookingController->all();

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
    <title>Admin Dashboard | MSBMS</title>
    <link rel="stylesheet" href="assets/styled.css?v=<?php echo filemtime(__DIR__ . '/assets/styled.css'); ?>">
</head>
<body>
<div class="app">

    <aside class="sidebar">
        <div class="logo"><span class="logo-mark"><i class="fa-solid fa-screwdriver-wrench" aria-hidden="true"></i></span> MSBMS</div>
        <nav class="nav">
            <a class="active" href="index.php"><i class="icon fa-solid fa-house" aria-hidden="true"></i>Dashboard</a>
            <a href="mechanic_create.php"><i class="icon fa-solid fa-user-gear" aria-hidden="true"></i>Create Mechanic</a>
            <a href="vehicle_create.php"><i class="icon fa-solid fa-circle-plus" aria-hidden="true"></i>Add Vehicle</a>
            <a href="vehicles.php"><i class="icon fa-solid fa-car-side" aria-hidden="true"></i>Vehicles</a>
            <a href="services.php"><i class="icon fa-solid fa-screwdriver-wrench" aria-hidden="true"></i>Services</a>
            <a href="notifications.php"><i class="icon fa-solid fa-bell" aria-hidden="true"></i>Notifications</a>
            <?php if (Session::userId()): ?>
                <a href="logout.php"><i class="icon fa-solid fa-arrow-right-from-bracket" aria-hidden="true"></i>Logout</a>
            <?php endif; ?>
        </nav>
    </aside>

    <main class="main">
        <div class="topbar">
            <div class="title">
                <h1>Admin Dashboard</h1>
                <p>Motor Service Booking Management System</p>
            </div>
            <?php if (Session::userId()): ?>
                <div class="user">
                    <span><?= e(Session::fullName()) ?> (<?= e(Session::role()) ?>)</span>
                    <span class="avatar"><?= strtoupper(substr(Session::fullName(), 0, 1)) ?></span>
                </div>
            <?php endif; ?>
        </div>

        <div class="stats">
            <div class="card stat blue">
                <p>Customers</p>
                <strong><?= e($stats['customers']) ?></strong>
            </div>
            <div class="card stat blue">
                <p>Mechanics</p>
                <strong><?= e($stats['mechanics']) ?></strong>
            </div>
            <div class="card stat blue">
                <p>Vehicles</p>
                <strong><?= e($stats['vehicles']) ?></strong>
            </div>
            <div class="card stat blue">
                <p>Bookings</p>
                <strong><?= e($stats['bookings']) ?></strong>
            </div>
            <div class="card stat orange">
                <p>Pending</p>
                <strong><?= e($stats['pending']) ?></strong>
            </div>
            <div class="card stat green">
                <p>Completed</p>
                <strong><?= e($stats['completed']) ?></strong>
            </div>
        </div>

        <div class="card section">
            <h2>Quick Actions</h2>
            <div class="actions">
                <a class="btn" href="mechanic_create.php">Create Mechanic</a>
                <a class="btn secondary" href="vehicle_create.php">Add Vehicle</a>
                <a class="btn secondary" href="vehicles.php">Vehicles</a>
                <a class="btn secondary" href="services.php">Services</a>
                <a class="btn secondary" href="notifications.php">Notifications</a>
            </div>
        </div>

        <div class="card section" style="margin-top:18px">
            <h2>Bookings</h2>
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
                        <th>Mechanic</th>
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
                            <td><span class="<?= statusBadgeClass($b['status']) ?>"><?= e($b['status']) ?></span></td>
                            <td><?= e($b['mechanic_name'] ?? 'Unassigned') ?></td>
                            <td>
                                <?php if ($b['status'] !== 'Canceled' && $b['status'] !== 'Completed'): ?>
                                    <a class="btn secondary" href="booking_assign.php?booking_id=<?= $b['booking_id'] ?>">Assign</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        </div>
    </main>

</div>
</body>
</html>