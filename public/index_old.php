<?php
require_once __DIR__ . '/_init.php';
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>MSBMS - MSBMS</title>
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
    <h1>Mechanic Service Management System</h1>
    <p>OOP version of your MSBMS.</p>

    <?php if (!Session::userId()): ?>
        <a href="login.php">Login</a> | <a href="register.php">Register</a>
    <?php else: ?>
        <p><a href="dashboard.php">Go to Dashboard</a></p>
    <?php endif; ?>
</main>

</body>
</html>
