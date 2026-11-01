<?php
require_once __DIR__ . '/_init.php';

// ---- Logic ----
Auth::requireLogin();

$role = strtolower((string) Session::role());

$dashboardLink = match ($role) {
    'customer' => 'customer.php',
    'admin'    => 'admin.php',
    'mechanic' => 'mechanic.php',
    default    => 'login.php',
};

// Return to the page the user came from (dashboard or notifications).
// Only same-site pages from this whitelist are allowed, so it can't redirect elsewhere.
function notificationReturnPage(string $dashboardLink): string
{
    $allowed = ['customer.php', 'mechanic.php', 'admin.php', 'notifications.php'];
    $ref     = $_SERVER['HTTP_REFERER'] ?? '';
    $host    = parse_url($ref, PHP_URL_HOST);

    if ($host && $host === ($_SERVER['HTTP_HOST'] ?? '') || ($host && $host === explode(':', $_SERVER['HTTP_HOST'] ?? '')[0])) {
        $page = basename((string) parse_url($ref, PHP_URL_PATH));
        if (in_array($page, $allowed, true)) {
            return $page;
        }
    }
    return 'notifications.php';
}

if (isset($_GET['read'])) {
    $notificationController->read((int)$_GET['read']);
    header('Location: ' . notificationReturnPage($dashboardLink));
    exit;
}

if (isset($_GET['mark_all'])) {
    $notificationController->readAll();
    header('Location: ' . notificationReturnPage($dashboardLink));
    exit;
}

$notifications = $notificationController->all();
$unreadCount   = 0;
foreach ($notifications as $n) {
    if (!$n['is_read']) { $unreadCount++; }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Notifications | MSBMS</title>
    <link rel="stylesheet" href="assets/styled.css">
</head>
<body>
<div class="app">

    <aside class="sidebar">
        <div class="logo"><span class="logo-mark">✦</span> MSBMS</div>
        <nav class="nav">
            <a href="<?= e($dashboardLink) ?>"><span class="icon">🏠</span>Dashboard</a>

            <?php if ($role === 'customer'): ?>
                <a href="booking_create.php"><span class="icon">📅</span>Create Booking</a>
                <a href="vehicles.php"><span class="icon">🚗</span>My Vehicles</a>
                <a href="vehicle_create.php"><span class="icon">➕</span>Add Vehicle</a>
                <a href="services.php"><span class="icon">🔧</span>Services</a>
                <a href="history.php"><span class="icon">🔧</span>Service History</a>
            <?php elseif ($role === 'mechanic'): ?>
                <a href="quotes.php"><span class="icon">💬</span>Quote Requests</a>
            <?php elseif ($role === 'admin'): ?>
                <a href="quotes.php"><span class="icon">💬</span>Quote Requests</a>
                <a href="create_mechanic.php"><span class="icon">🧰</span>Create Mechanic</a>
            <?php endif; ?>

            <a class="active" href="notifications.php"><span class="icon">🔔</span>Notifications<?php if ($unreadCount): ?> (<?= (int)$unreadCount ?>)<?php endif; ?></a>
            <a href="profile.php"><span class="icon">⚙️</span>Profile Settings</a>
            <?php if (Session::userId()): ?>
                <a href="logout.php"><span class="icon">↪</span>Logout</a>
            <?php endif; ?>
        </nav>
    </aside>

    <main class="main">
        <div class="topbar">
            <div class="title">
                <h1>Notifications</h1>
                <p>Motor Service Booking Management System</p>
            </div>
            <?php if (Session::userId()): ?>
                <div class="user">
                    <span><?= e(Session::fullName()) ?> (<?= e(Session::role()) ?>)</span>
                    <span class="avatar"><?= strtoupper(substr(Session::fullName(), 0, 1)) ?></span>
                </div>
            <?php endif; ?>
        </div>

        <div class="card section">
            <h2>
                All Notifications
                <?php if ($unreadCount): ?>
                    <span class="badge yellow" style="margin-left:8px"><?= (int)$unreadCount ?> new</span>
                <?php endif; ?>
            </h2>

            <p style="margin-bottom:16px">
                <?php if ($unreadCount): ?>
                    <a class="btn secondary" href="notifications.php?mark_all=1">Mark all as read</a>
                <?php endif; ?>
                <a class="btn secondary" href="<?= e($dashboardLink) ?>">Back to Dashboard</a>
            </p>

            <div class="notice-list">
                <?php foreach ($notifications as $n): ?>
                    <div class="notice" <?= !$n['is_read'] ? 'style="border-left:3px solid #2563eb"' : '' ?>>
                        <div class="notice-dot">🔔</div>
                        <div>
                            <h3><?= e($n['message']) ?></h3>
                            <p>
                                <?= $n['is_read'] ? 'Read' : '<strong>Unread</strong>' ?>
                                <?php if (!$n['is_read']): ?>
                                    &middot; <a href="notifications.php?read=<?= (int)$n['notification_id'] ?>">Mark read</a>
                                <?php endif; ?>
                            </p>
                            <time><?= e($n['created_at']) ?></time>
                        </div>
                    </div>
                <?php endforeach; ?>

                <?php if (empty($notifications)): ?>
                    <p style="color:var(--muted)">No notifications yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </main>

</div>
</body>
</html>