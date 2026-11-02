<?php
require_once __DIR__ . '/_init.php';
Auth::requireLogin();

$role = strtolower((string)Session::role());
if (!in_array($role, ['admin', 'mechanic', 'customer'], true)) {
    header('Location: login.php');
    exit;
}
$isAdmin       = ($role === 'admin');
$isMechanic    = ($role === 'mechanic');
$isCustomer    = ($role === 'customer');
$dashboardLink = $isAdmin ? 'admin.php' : ($isMechanic ? 'mechanic.php' : 'customer.php');

// ---- Load the records each role is allowed to see ----
$setupNote = '';
if ($isAdmin) {
    // Admin: every repair record
    $repairs = $repairController->all();
} elseif ($isMechanic) {
    // Mechanic: only the repairs they carried out
    if (method_exists($repairController, 'forMechanic')) {
        $repairs = $repairController->forMechanic((int)Session::userId());
    } else {
        $repairs = array_values(array_filter(
            $repairController->all(),
            fn($r) => (int)$r['mechanic_id'] === (int)Session::userId()
        ));
    }
} else {
    // Customer: show completed bookings for their own vehicles.
    $repairs = $bookingController->completedForCustomer();
}

// Page wording per role
if ($isCustomer) {
    $pageTitle    = 'Service History';
    $pageSubtitle = 'Past services and repairs on your vehicles';
    $tableTitle   = 'Your Services';
} elseif ($isMechanic) {
    $pageTitle    = 'Repair History';
    $pageSubtitle = 'Cars you have repaired';
    $tableTitle   = 'Your Repairs';
} else {
    $pageTitle    = 'Service History';
    $pageSubtitle = 'All repair records across every booking';
    $tableTitle   = 'Repairs';
}

// Map repair status to a badge style class defined in styled.css
function repairStatusBadgeClass($status) {
    switch ($status) {
        case 'Completed':
            return 'badge'; // default badge is green
        case 'In Progress':
        case 'Pending':
        case 'Canceled':
            return 'badge yellow';
        default:
            return 'badge blue';
    }
}

$colCount = $isAdmin ? 8 : 6;
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title><?= e($pageTitle) ?> | MSBMS</title>
    <link rel="stylesheet" href="assets/styled.css?v=<?php echo filemtime(__DIR__ . '/assets/styled.css'); ?>">
</head>
<body>
<div class="app">

    <aside class="sidebar">
        <div class="logo"><span class="logo-mark"><i class="fa-solid fa-screwdriver-wrench" aria-hidden="true"></i></span> MSBMS</div>
        <nav class="nav">
            <a href="<?= e($dashboardLink) ?>"><i class="icon fa-solid fa-house" aria-hidden="true"></i>Dashboard</a>
            <?php if ($isCustomer): ?>
                <a href="booking_create.php"><i class="icon fa-solid fa-calendar-days" aria-hidden="true"></i>Create Booking</a>
                <a href="vehicles.php"><i class="icon fa-solid fa-car-side" aria-hidden="true"></i>My Vehicles</a>
                <a href="vehicle_create.php"><i class="icon fa-solid fa-circle-plus" aria-hidden="true"></i>Add Vehicle</a>
                <a href="services.php"><i class="icon fa-solid fa-screwdriver-wrench" aria-hidden="true"></i>Services</a>
                <a class="active" href="history.php"><i class="icon fa-solid fa-clipboard-list" aria-hidden="true"></i>Service History</a>
            <?php elseif ($isAdmin): ?>
                <a href="mechanic_create.php"><i class="icon fa-solid fa-user-gear" aria-hidden="true"></i>Create Mechanic</a>
                <a href="vehicle_create.php"><i class="icon fa-solid fa-circle-plus" aria-hidden="true"></i>Add Vehicle</a>
                <a href="vehicles.php"><i class="icon fa-solid fa-car-side" aria-hidden="true"></i>Vehicles</a>
                <a href="services.php"><i class="icon fa-solid fa-screwdriver-wrench" aria-hidden="true"></i>Services</a>
               
                <a class="active" href="history.php"><i class="icon fa-solid fa-clipboard-list" aria-hidden="true"></i>Service History</a>
            <?php elseif ($isMechanic): ?>
                
                <a class="active" href="history.php"><i class="icon fa-solid fa-clipboard-list" aria-hidden="true"></i>service History</a>
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
                <h1><?= e($pageTitle) ?></h1>
                <p><?= e($pageSubtitle) ?></p>
            </div>
            <?php if (Session::userId()): ?>
                <div class="user">
                    <span><?= e(Session::fullName()) ?> (<?= e(Session::role()) ?>)</span>
                    <span class="avatar"><?= strtoupper(substr(Session::fullName(), 0, 1)) ?></span>
                </div>
            <?php endif; ?>
        </div>

        <div class="card section">
            <h2><?= e($tableTitle) ?></h2>
            <?php if ($setupNote): ?>
                <p class="badge yellow"><?= e($setupNote) ?></p>
            <?php endif; ?>
            <div class="table-card">
                <table class="table">
                    <?php if ($isAdmin): ?>
                        <tr>
                            <th>Repair ID</th>
                            <th>Booking ID</th>
                            <th>Mechanic ID</th>
                            <th>Description</th>
                            <th>Status</th>
                            <th>Cost</th>
                            <th>Repair Date</th>
                            <th>Created At</th>
                        </tr>
                    <?php elseif ($isMechanic): ?>
                        <tr>
                            <th>Repair ID</th>
                            <th>Vehicle</th>
                            <th>Description</th>
                            <th>Status</th>
                            <th>Cost</th>
                            <th>Repair Date</th>
                        </tr>
                    <?php else: ?>
                        <tr>
                            <th>Date</th>
                            <th>Vehicle</th>
                            <th>Service</th>
                            <th>Description</th>
                            <th>Status</th>
                            <th>Cost</th>
                        </tr>
                    <?php endif; ?>

                    <?php foreach ($repairs as $r): ?>
                        <?php
                        $vehicle = $r['registration_number'] ?? '—';
                        $date    = $r['repair_date'] ?? ($r['created_at'] ?? '—');
                        $cost    = ($r['cost'] ?? null) !== null ? 'R' . number_format((float)$r['cost'], 2) : '—';
                        ?>
                        <?php if ($isAdmin): ?>
                            <tr>
                                <td><?= e($r['repair_id']) ?></td>
                                <td><?= e($r['booking_id']) ?></td>
                                <td><?= e($r['mechanic_id']) ?></td>
                                <td><?= e($r['description']) ?></td>
                                <td><span class="<?= repairStatusBadgeClass($r['status']) ?>"><?= e($r['status']) ?></span></td>
                                <td><?= $cost ?></td>
                                <td><?= e($r['repair_date'] ?? 'NULL') ?></td>
                                <td><?= e($r['created_at']) ?></td>
                            </tr>
                        <?php elseif ($isMechanic): ?>
                            <tr>
                                <td><?= e($r['repair_id']) ?></td>
                                <td><?= e($vehicle) ?></td>
                                <td><?= e($r['description']) ?></td>
                                <td><span class="<?= repairStatusBadgeClass($r['status']) ?>"><?= e($r['status']) ?></span></td>
                                <td><?= $cost ?></td>
                                <td><?= e($date) ?></td>
                            </tr>
                        <?php else: ?>
                            <tr>
                                <td><?= e($date) ?></td>
                                <td><?= e($vehicle) ?></td>
                                <td><?= e($r['service_name'] ?? '—') ?></td>
                                <td><?= e($r['description']) ?></td>
                                <td><span class="<?= repairStatusBadgeClass($r['status']) ?>"><?= e($r['status']) ?></span></td>
                                <td><?= $cost ?></td>
                            </tr>
                        <?php endif; ?>
                    <?php endforeach; ?>

                    <?php if (empty($repairs)): ?>
                        <tr>
                            <td colspan="<?= $colCount ?>" style="text-align:center;color:var(--muted)">
                                <?= $isMechanic ? 'No repairs recorded yet.' : 'No service history yet.' ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                </table>
            </div>
        </div>
    </main>

</div>
</body>
</html>