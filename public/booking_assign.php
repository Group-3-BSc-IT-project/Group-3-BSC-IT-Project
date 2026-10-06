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
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Assign Mechanic - MSBMS</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>

<nav>
    <a href="index.php">MSBMS</a>
    <?php if (Session::userId()): ?>
        <span> | <?= e(Session::fullName()) ?> (<?= e(Session::role()) ?>)</span>
        <a href="logout.php">Logout</a>
    <?php endif; ?>
</nav>

<main>
    <h1>Assign Mechanic</h1>

    <p>Booking #<?= e($id) ?> — <?= e($booking['customer_name']) ?> — <?= e($booking['service_name']) ?></p>

    <?php if ($message): ?>
        <p class="error"><?= e($message) ?></p>
    <?php endif; ?>

    <form method="post">
        <select name="mechanic_id" required>
            <option value="">Select mechanic</option>
            <?php foreach ($mechanics as $m): ?>
                <option value="<?= $m['user_id'] ?>"><?= e($m['full_name'] . ' ' . $m['phone']) ?></option>
            <?php endforeach; ?>
        </select>
        <button>Assign Mechanic</button>
    </form>
</main>

</body>
</html>
