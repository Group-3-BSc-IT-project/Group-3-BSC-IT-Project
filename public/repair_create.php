<?php
require_once __DIR__ . '/_init.php';

// ---- Logic ----
Auth::requireRole('mechanic');

$id      = (int)($_GET['booking_id'] ?? 0);
$booking = $repairController->booking($id);

if (!$booking) {
    exit('Booking not found.');
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    [$ok, $message] = $repairController->create($id);
    if ($ok) {
        header('Location: mechanic.php');
        exit;
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Add Repair | MSBMS</title>
    <link rel="stylesheet" href="assets/styled.css">
</head>
<body>
<div class="app">

    <aside class="sidebar">
        <div class="logo"><span class="logo-mark">✦</span> MSBMS</div>
        <nav class="nav">
            <a href="mechanic.php"><span class="icon">🏠</span>Dashboard</a>
            <a href="notifications.php"><span class="icon">🔔</span>Notifications</a>
            <?php if (Session::userId()): ?>
                <a href="logout.php"><span class="icon">↪</span>Logout</a>
            <?php endif; ?>
        </nav>
    </aside>

    <main class="main">
        <div class="topbar">
            <div class="title">
                <h1>Add Repair Record</h1>
                <p>Log the work done for this booking</p>
            </div>
            <?php if (Session::userId()): ?>
                <div class="user">
                    <span><?= e(Session::fullName()) ?> (<?= e(Session::role()) ?>)</span>
                    <span class="avatar"><?= strtoupper(substr(Session::fullName(), 0, 1)) ?></span>
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
                <p class="badge yellow auth-message"><?= e($message) ?></p>
            <?php endif; ?>

            <form method="post">
                <div class="field">
                    <label>Repair Description</label>
                    <textarea name="description" rows="4" placeholder="Describe the work carried out" required style="width:100%;padding:11px 12px;background:#fbfcfe;border:1px solid #dfe4ec;border-radius:7px;font-size:12px;color:#26344c;outline:none;font-family:inherit;resize:vertical"></textarea>
                </div>
                <div class="form-row">
                    <div class="field">
                        <label>Cost</label>
                        <input type="number" step="0.01" name="cost" placeholder="0.00">
                    </div>
                    <div class="field">
                        <label>Repair Date</label>
                        <input type="date" name="repair_date" value="<?= date('Y-m-d') ?>">
                    </div>
                </div>
                <button class="btn">Save Repair</button>
            </form>
        </div>
    </main>

</div>
</body>
</html>