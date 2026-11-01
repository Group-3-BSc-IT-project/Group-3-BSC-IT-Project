<?php
require_once __DIR__ . '/_init.php';

Auth::requireRole('mechanic');

$bookingId = (int)($_GET['booking_id'] ?? $_POST['booking_id'] ?? 0);
$job       = $bookingController->jobForMechanic($bookingId);
$message   = '';

if (!$job) {
    header('Location: mechanic.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    [$ok, $message] = $bookingController->completeJob($bookingId);
    if ($ok) {
        header('Location: mechanic.php');
        exit;
    }
}

// Suggested price: repair total, else accepted quote, else standard price
$repairTotal = $repairController->totalCost($bookingId);
$suggested   = $repairTotal > 0
    ? $repairTotal
    : (($job['quote_status'] === 'Accepted' && $job['quote_price'] !== null)
        ? $job['quote_price']
        : $job['quoted_price']);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Complete Job | MSBMS</title>
    <link rel="stylesheet" href="assets/styled.css">
</head>
<body>
<div class="app">

    <aside class="sidebar">
        <div class="logo"><span class="logo-mark">✦</span> MSBMS</div>
        <nav class="nav">
            <a href="mechanic.php"><span class="icon">🏠</span>Dashboard</a>
            <a href="notifications.php"><span class="icon">🔔</span>Notifications</a>
            <a href="quotes.php"><span class="icon">💬</span>Quote Requests</a>
            <a href="profile.php"><span class="icon">⚙️</span>Profile Settings</a>
            <a href="logout.php"><span class="icon">↪</span>Logout</a>
        </nav>
    </aside>

    <main class="main">
        <div class="topbar">
            <div class="title">
                <h1>Complete Job</h1>
                <p>Enter the final price for the work done</p>
            </div>
            <div class="user">
                <span><?= e(Session::fullName()) ?> (<?= e(Session::role()) ?>)</span>
                <span class="avatar"><?= strtoupper(substr(Session::fullName(), 0, 1)) ?></span>
            </div>
        </div>

        <div class="card form">
            <?php if ($message): ?>
                <p style="color:var(--red);font-size:12px;margin-bottom:16px"><?= e($message) ?></p>
            <?php endif; ?>

            <p style="font-size:13px;margin-bottom:4px"><strong>Booking #<?= e($job['booking_id']) ?></strong></p>
            <p style="font-size:13px;color:var(--muted);margin-bottom:16px">
                <?= e($job['custom_service'] ?: $job['service_name']) ?>
                &middot; Standard price: R<?= number_format((float)$job['quoted_price'], 2) ?>
                <?php if ($job['quote_status'] === 'Accepted'): ?>
                    &middot; Accepted quote: R<?= number_format((float)$job['quote_price'], 2) ?>
                <?php endif; ?>
            </p>

            <?php if ($job['status'] === 'Completed'): ?>
                <p class="badge">Already completed. Final price: R<?= number_format((float)$job['final_price'], 2) ?></p>
                <p style="margin-top:12px"><a class="btn secondary" href="mechanic.php">Back</a></p>

            <?php elseif ($job['status'] === 'Canceled'): ?>
                <p class="badge yellow">This booking was canceled.</p>
                <p style="margin-top:12px"><a class="btn secondary" href="mechanic.php">Back</a></p>

            <?php else: ?>
                <?php if ($repairTotal > 0): ?>
                    <p style="font-size:12px;color:var(--muted);margin-bottom:8px">
                        Repairs logged: R<?= number_format($repairTotal, 2) ?>. Adjust if the final price differs.
                    </p>
                <?php endif; ?>

                <form method="post">
                    <input type="hidden" name="booking_id" value="<?= e($job['booking_id']) ?>">
                    <div class="field">
                        <label for="final_price">Final price (R)</label>
                        <input id="final_price" type="number" name="final_price" step="0.01" min="0"
                               value="<?= e(number_format((float)$suggested, 2, '.', '')) ?>" required>
                    </div>
                    <button class="btn" type="submit">Mark as Completed</button>
                    <a class="btn secondary" href="mechanic.php">Cancel</a>
                </form>
            <?php endif; ?>
        </div>
    </main>

</div>
</body>
</html>