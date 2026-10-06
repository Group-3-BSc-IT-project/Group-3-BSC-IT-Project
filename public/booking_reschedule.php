<?php
require_once __DIR__ . '/_init.php';

// ---- Logic ----
Auth::requireRole('customer');

$id      = (int)($_GET['id'] ?? 0);
$booking = $bookingController->customerFind($id);

if (!$booking) {
    exit('Booking not found.');
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    [$ok, $message] = $bookingController->reschedule($id);
    if ($ok) {
        header('Location: customer.php');
        exit;
    }
}
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Reschedule Booking - MSBMS</title>
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
    <h1>Reschedule Booking #<?= e($id) ?></h1>

    <?php if ($message): ?>
        <p class="error"><?= e($message) ?></p>
    <?php endif; ?>

    <form method="post">
        <input type="date" name="booking_date" value="<?= e($booking['booking_date']) ?>" required>
        <input type="time" name="booking_time" value="<?= e(substr($booking['booking_time'], 0, 5)) ?>" required>
        <button>Reschedule</button>
    </form>
</main>

</body>
</html>
