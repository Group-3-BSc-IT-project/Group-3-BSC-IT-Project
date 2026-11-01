<?php
require_once __DIR__ . '/_init.php';
Auth::requireLogin();

$role = strtolower((string)Session::role());
if (!in_array($role, ['admin', 'mechanic'], true)) {
    header('Location: login.php');
    exit;
}
$dashboardLink = $role === 'admin' ? 'admin.php' : 'mechanic.php';
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
    <link rel="stylesheet" href="assets/styled.css">
</head>
<body>
<div class="app">
    <aside class="sidebar">
        <div class="logo"><span class="logo-mark">✦</span> MSBMS</div>
        <nav class="nav">
            <a href="<?= e($dashboardLink) ?>"><span class="icon">🏠</span>Dashboard</a>
            <a class="active" href="quotes.php"><span class="icon">💬</span>Quote Requests</a>
            <a href="notifications.php"><span class="icon">🔔</span>Notifications</a>
            <a href="logout.php"><span class="icon">↪</span>Logout</a>
        </nav>
    </aside>

    <main class="main">
        <div class="topbar">
            <div class="title"><h1>Quote Requests</h1><p>Send customers a price for the work</p></div>
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