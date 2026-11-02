<?php
require_once __DIR__ . '/_init.php';
Auth::requireLogin();

$role = strtolower((string)Session::role());
if (!in_array($role, ['admin', 'mechanic'], true)) {
    header('Location: login.php');
    exit;
}
$isAdmin       = ($role === 'admin');
$dashboardLink = $isAdmin ? 'admin.php' : 'mechanic.php';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    [$ok, $message] = $bookingController->submitQuote((int)($_POST['booking_id'] ?? 0));
}
$requests = $bookingController->quoteRequests();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Quote Requests | MSBMS</title>
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
                <a href="vehicle_create.php"><i class="icon fa-solid fa-circle-plus" aria-hidden="true"></i>Add Vehicle</a>
                <a href="vehicles.php"><i class="icon fa-solid fa-car-side" aria-hidden="true"></i>Vehicles</a>
                <a href="services.php"><i class="icon fa-solid fa-screwdriver-wrench" aria-hidden="true"></i>Services</a>
            <?php endif; ?>
            <a class="active" href="quotes.php"><i class="icon fa-solid fa-comments" aria-hidden="true"></i>Quote Requests</a>
            <?php if ($isAdmin): ?>
                <a href="history.php"><i class="icon fa-solid fa-clipboard-list" aria-hidden="true"></i>Service History</a>
            <?php endif; ?>
            <a href="notifications.php"><i class="icon fa-solid fa-bell" aria-hidden="true"></i>Notifications</a>
            <a href="profile.php"><i class="icon fa-solid fa-user" aria-hidden="true"></i>Profile</a>
            <?php if (Session::userId()): ?>
                <a href="logout.php"><i class="icon fa-solid fa-arrow-right-from-bracket" aria-hidden="true"></i>Logout</a>
            <?php endif; ?>
        </nav>
    </aside>

    <main class="main">
        <div class="topbar">
            <div class="title">
                <h1>Quote Requests</h1>
                <p>Send customers a price for the work</p>
            </div>
            <?php if (Session::userId()): ?>
                <div class="user">
                    <span><?= e(Session::fullName()) ?> (<?= e(Session::role()) ?>)</span>
                    <span class="avatar"><?= strtoupper(substr(Session::fullName(), 0, 1)) ?></span>
                </div>
            <?php endif; ?>
        </div>

        <div class="card section">
            <?php if ($message): ?>
                <p class="badge yellow"><?= e($message) ?></p>
            <?php endif; ?>
            <div class="table-card">
                <table class="table">
                    <tr>
                        <th>ID</th><th>Customer</th><th>Vehicle</th><th>Service</th>
                        <th>Date</th><th>Notes</th><th>Quote (R)</th>
                    </tr>
                    <?php foreach ($requests as $r): ?>
                        <tr>
                            <td><?= e($r['booking_id']) ?></td>
                            <td><?= e($r['customer_name']) ?></td>
                            <td><?= e($r['registration_number']) ?></td>
                            <td><?= e($r['custom_service'] ?: $r['service_name']) ?></td>
                            <td><?= e($r['booking_date'] . ' ' . $r['booking_time']) ?></td>
                            <td><?= e($r['notes']) ?></td>
                            <td>
                                <form method="post" style="display:flex;gap:6px">
                                    <input type="hidden" name="booking_id" value="<?= e($r['booking_id']) ?>">
                                    <input type="number" name="quote_price" step="0.01" min="0.01"
                                           value="<?= $r['price'] > 0 ? e($r['price']) : '' ?>" required style="width:100px">
                                    <button class="btn" type="submit">Send</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$requests): ?>
                        <tr><td colspan="7" style="text-align:center;color:var(--muted)">No quote requests.</td></tr>
                    <?php endif; ?>
                </table>
            </div>
        </div>
    </main>

</div>
</body>
</html>