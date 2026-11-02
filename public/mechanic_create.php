<?php
require_once __DIR__ . '/_init.php';
require_once __DIR__ . '/../app/Repositories/PasswordResetRepository.php';
require_once __DIR__ . '/../app/Controllers/PasswordResetController.php';

Auth::requireRole('admin');

$inviteController = new PasswordResetController(new PasswordResetRepository($db), false);

$message = '';
$ok = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    [$ok, $message] = $inviteController->inviteMechanic();
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Create Mechanic | MSBMS</title>
    <link rel="stylesheet" href="assets/styled.css?v=<?php echo filemtime(__DIR__ . '/assets/styled.css'); ?>">
</head>
<body>
<div class="app">

    <aside class="sidebar">
        <div class="logo"><span class="logo-mark"><i class="fa-solid fa-screwdriver-wrench" aria-hidden="true"></i></span> MSBMS</div>
        <nav class="nav">
            <a href="admin.php"><i class="icon fa-solid fa-house" aria-hidden="true"></i>Dashboard</a>
            <a class="active" href="mechanic_create.php"><i class="icon fa-solid fa-user-gear" aria-hidden="true"></i>Create Mechanic</a>
            <a href="vehicle_create.php"><i class="icon fa-solid fa-circle-plus" aria-hidden="true"></i>Add Vehicle</a>
            <a href="vehicles.php"><i class="icon fa-solid fa-car-side" aria-hidden="true"></i>Vehicles</a>
            <a href="services.php"><i class="icon fa-solid fa-screwdriver-wrench" aria-hidden="true"></i>Services</a>
            <a href="quotes.php"><i class="icon fa-solid fa-comments" aria-hidden="true"></i>Quote Requests</a>
            <a href="history.php"><i class="icon fa-solid fa-clipboard-list" aria-hidden="true"></i>Service History</a>
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
                <h1>Create Mechanic</h1>
                <p>The mechanic will receive an email to set their own password.</p>
            </div>
            <?php if (Session::userId()): ?>
                <div class="user">
                    <span><?= e(Session::fullName()) ?> (<?= e(Session::role()) ?>)</span>
                    <span class="avatar"><?= strtoupper(substr(Session::fullName(), 0, 1)) ?></span>
                </div>
            <?php endif; ?>
        </div>

        <div class="card section form">
            <?php if ($message): ?>
                <p class="badge <?= $ok ? '' : 'yellow' ?>"><?= e($message) ?></p>
            <?php endif; ?>

            <form method="post">
                <div class="form-row">
                    <div class="field">
                        <label>Full name</label>
                        <input name="full_name" required>
                    </div>
                    <div class="field">
                        <label>Email</label>
                        <input type="email" name="email" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="field">
                        <label>Phone</label>
                        <input type="tel" name="phone" pattern="^(\+27|0)[6-8][0-9]{8}$" placeholder="e.g. 0821234567">
                    </div>
                    <div class="field">
                        <label>Address</label>
                        <input name="address">
                    </div>
                </div>
                <button class="btn">Create Mechanic &amp; Send Invite</button>
            </form>
        </div>
    </main>

</div>
</body>
</html>