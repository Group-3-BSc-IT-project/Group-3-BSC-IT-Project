<?php
require_once __DIR__ . '/_init.php';

// Normalise the role once and reuse it
$role = strtolower((string) Session::role());

// Where the "Dashboard" link should go for each role
$dashboardLink = match ($role) {
    'customer' => 'customer.php',
    'admin'    => 'admin.php',
    'mechanic' => 'mechanic.php',
    default    => 'login.php', // not logged in or unknown role
};

// Guard: this page is the customer dashboard.
// Anyone else is sent to their own dashboard (or login).
if ($role !== 'customer') {
    header('Location: ' . $dashboardLink);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>MSBMS | Dashboard</title>
  <link rel="stylesheet" href="assets/styled.css">
</head>
<body>
  <div class="app">
    <aside class="sidebar">
      <div class="logo"><span class="logo-mark">✦</span> MSBMS</div>
      <nav class="nav">
        <a class="active" href="<?= e($dashboardLink) ?>"><span class="icon">🏠</span>Dashboard</a>
        <a class="" href="booking_create.php"><span class="icon">📅</span>My Bookings</a>
        <a class="" href="vehicles.php"><span class="icon">🚗</span>My Vehicles</a>
        <a class="" href="history.php"><span class="icon">🔧</span>Service History</a>
        <a class="" href="notifications.php"><span class="icon">🔔</span>Notifications</a>
        <a class="" href="profile.php"><span class="icon">👤</span>Profile</a>
        <a class="" href="logout.php"><span class="icon">↪</span>Logout</a>
      </nav>
    </aside>

    <main class="main">
      <div class="topbar">
        <div class="title">
          <h1>Dashboard</h1>
          <p>Motor Service Booking Management System</p>
        </div>
        <div class="user">
          <?php if (Session::userId()): ?>
            <span> | <?= e(Session::fullName()) ?> (<?= e(Session::role()) ?>)</span>
          <?php endif; ?>

          <span class="avatar">C</span>
        </div>
      </div>

      <div class="stats">
        <div class="card stat blue">
          <p>Total Bookings</p>
          <strong>25</strong>
        </div>
        <div class="card stat blue">
          <p>Upcoming</p>
          <strong>12</strong>
        </div>
        <div class="card stat green">
          <p>In Progress</p>
          <strong>8</strong>
        </div>
        <div class="card stat orange">
          <p>Completed</p>
          <strong>18</strong>
        </div>
      </div>

      <div class="admin-grid">
        <div class="card section">
          <h2>Upcoming Services</h2>
          <div class="table-card">
            <table class="table">
              <tr>
                <th>Header coming soon</th>
                <th>Header coming soon</th>
                <th>Header coming soon</th>
                <th>Header coming soon</th>
              </tr>
              <tr>
                <td>td coming soon</td>
                <td>td coming soon</td>
                <td>td coming soon</td>
                <td><span class="badge blue">td coming soon</span></td>
              </tr>
              <tr>
                <td>td coming soon</td>
                <td>td coming soon</td>
                <td>td coming soon</td>
                <td><span class="badge blue">Upcoming</span></td>
              </tr>
            </table>
          </div>
        </div>

        <div class="card section">
          <h2>Quick Actions</h2>
          <a class="btn" href="booking_create.php">Book a Service</a>
          <br><br>
          <a class="btn secondary" href="vehicles.php">Manage Vehicles</a>
        </div>
      </div>

      <div style="margin-top:28px;color:#a0a8b5;font-size:10px">
        © 2024 MSBMS · Motor Service Booking Management System
      </div>
    </main>
  </div>
</body>
</html>