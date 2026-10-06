<?php
require_once __DIR__ . '/_init.php';

// ---- Logic ----
Auth::requireRole('customer');

$id = (int)($_GET['id'] ?? 0);
$ok = $bookingController->cancel($id);

header('Location: customer.php');
exit;
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Cancel Booking - MSBMS</title>
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
</main>

</body>
</html>
