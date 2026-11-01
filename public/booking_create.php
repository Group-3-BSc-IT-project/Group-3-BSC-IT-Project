<?php
require_once __DIR__ . '/_init.php';

// ---- Logic ----
// ---- Logic ----
Auth::requireLogin();

$role = strtolower((string) Session::role());

// Where the "Dashboard" link should go for each role
$dashboardLink = match ($role) {
    'customer' => 'customer.php',
    'admin'    => 'admin.php',
    'mechanic' => 'mechanic.php',
    default    => 'login.php', // not logged in or unknown role
};

$vehicles = $bookingController->vehicles();
$services = $bookingController->services();
$message  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    [$ok, $message] = $bookingController->create();
    if ($ok) {
        header('Location: customer.php');
        exit;
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Create Booking | MSBMS</title>
    <link rel="stylesheet" href="assets/styled.css?v=<?php echo filemtime(__DIR__ . '/assets/styled.css'); ?>">
</head>
<body>
<div class="app">

    <aside class="sidebar">
        <div class="logo"><span class="logo-mark"><i class="fa-solid fa-screwdriver-wrench" aria-hidden="true"></i></span> MSBMS</div>
        <nav class="nav">
            <a href="<?= e($dashboardLink) ?>"><i class="icon fa-solid fa-house" aria-hidden="true"></i>Dashboard</a>
            <a class="active" href="booking_create.php"><i class="icon fa-solid fa-calendar-days" aria-hidden="true"></i>Create Booking</a>
            <a href="vehicles.php"><i class="icon fa-solid fa-car-side" aria-hidden="true"></i>My Vehicles</a>
            <a href="vehicle_create.php"><i class="icon fa-solid fa-circle-plus" aria-hidden="true"></i>Add Vehicle</a>
            <a href="services.php"><i class="icon fa-solid fa-screwdriver-wrench" aria-hidden="true"></i>Services</a>
            <a href="notifications.php"><i class="icon fa-solid fa-bell" aria-hidden="true"></i>Notifications</a>
            <?php if (Session::userId()): ?>
                <a href="logout.php"><i class="icon fa-solid fa-arrow-right-from-bracket" aria-hidden="true"></i>Logout</a>
            <?php endif; ?>
        </nav>
    </aside>

    <main class="main">
        <div class="topbar">
            <div class="title">
                <h1>Create Booking</h1>
                <p>Motor Service Booking Management System</p>
            </div>
            <?php if (Session::userId()): ?>
                <div class="user">
                    <span><?= e(Session::fullName()) ?> (<?= e(Session::role()) ?>)</span>
                    <span class="avatar"><?= strtoupper(substr(Session::fullName(), 0, 1)) ?></span>
                </div>
            <?php endif; ?>
        </div>

        <section class="booking-flow card form" aria-labelledby="booking-flow-title">
            <div class="booking-flow-heading">
                <span class="eyebrow">Service appointment</span>
                <h2 id="booking-flow-title">Book a service</h2>
                <p>Choose the service, vehicle, and time that suits you.</p>
            </div>
            <ol class="progress-steps" aria-label="Booking progress">
                <li class="step active"><span class="step-number">1</span><span>Select service</span></li>
                <li class="step"><span class="step-number">2</span><span>Choose date &amp; time</span></li>
                <li class="step"><span class="step-number">3</span><span>Booking details</span></li>
                <li class="step"><span class="step-number">4</span><span>Confirm</span></li>
            </ol>
            <?php if ($message): ?>
                <p class="form-message" role="alert"><?= e($message) ?></p>
            <?php endif; ?>

            <?php if (!$vehicles): ?>
                <p style="font-size:13px;color:var(--muted);margin-bottom:12px">You need to add a vehicle first.</p>
                <a class="btn" href="vehicle_create.php">Add vehicle</a>
            <?php else: ?>
                <form method="post">
                    <fieldset class="service-picker">
                        <legend>Select a service</legend>
                        <div class="service-grid">
                            <?php foreach ($services as $s): ?>
                                <label class="service-card">
                                    <input type="radio" name="service_id" value="<?= e($s['service_id']) ?>" required>
                                    <span class="service-content">
                                        <strong><?= e($s['service_name']) ?></strong>
                                        <small><?= e($s['description']) ?></small>
                                        <b>From R<?= number_format((float)$s['price'], 2) ?></b>
                                    </span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </fieldset>
                    <div class="booking-details">
                        <h3>Appointment details</h3>
                        <div class="form-row">
                        <div class="field">
                            <label for="vehicle_id">Vehicle</label>
                            <select id="vehicle_id" name="vehicle_id" required>
                                <option value="">Select vehicle</option>
                                <?php foreach ($vehicles as $v): ?>
                                    <option value="<?= $v['vehicle_id'] ?>">
                                        <?= e($v['registration_number'] . ' - ' . $v['make'] . ' ' . $v['model']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        </div>
                        </div>

                        <div class="form-row">
                        <div class="field">
                            <label for="booking_date">Booking Date</label>
                            <input id="booking_date" type="date" name="booking_date" required>
                        </div>
                        <div class="field">
                            <label for="booking_time">Booking Time</label>
                            <input id="booking_time" type="time" name="booking_time" required>
                        </div>
                        </div>

                        <div class="field">
                        <label for="notes">Notes</label>
                        <textarea id="notes" name="notes" placeholder="Notes" rows="4"
                            style="width:100%;padding:11px 12px;background:#fbfcfe;border:1px solid #dfe4ec;border-radius:7px;font-size:12px;color:#26344c;font-family:inherit;resize:vertical"></textarea>
                        </div>

                        <div class="booking-actions">
                            <a class="btn secondary" href="<?= e($dashboardLink) ?>">Back</a>
                            <button class="btn" type="submit">Confirm booking</button>
                        </div>
                    </div>
                </form>
            <?php endif; ?>
        </section>
    </main>

</div>
</body>
</html>
