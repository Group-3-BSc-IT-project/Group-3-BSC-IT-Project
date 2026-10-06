<?php
require_once __DIR__ . '/_init.php';

// ---- Logic ----
Auth::requireLogin();

$target = Session::role() === 'admin'
    ? 'admin.php'
    : (Session::role() === 'mechanic' ? 'mechanic.php' : 'customer.php');

header('Location: ' . $target);
exit;
?>

<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Dashboard - MSBMS</title>
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
