<?php

session_start();

foreach (["config.php", "database.php"] as $configFile) {

    if (file_exists(__DIR__ . "/../config/" . $configFile)) {
        require_once __DIR__ . "/../config/" . $configFile;
    }

}


try {

    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );

} catch (PDOException $e) {

    die("Database connection error: " . $e->getMessage());

}

$base = defined("BASE_URL") ? BASE_URL : "/MSBMS_OOP/public";

$isLoggedIn = isset($_SESSION["user_id"]);

$role      = $isLoggedIn ? ($_SESSION["role"] ?? "") : "";
$full_name = $isLoggedIn ? ($_SESSION["full_name"] ?? "") : "";
$firstName = $full_name !== "" ? explode(" ", $full_name)[0] : "";


$dashboardLink = $base . "/login.php";

if ($role === "customer") {

    $dashboardLink = $base . "/bookings/customer-dashboard.php";

} elseif ($role === "mechanic") {

    $dashboardLink = $base . "/mechanics/dashboard.php";

} elseif ($role === "admin") {

    $dashboardLink = $base . "/admin/dashboard.php";

}



if (!$isLoggedIn) {

    $bookLink = $base . "/login.php";

} elseif ($role === "customer") {

    $bookLink = $base . "/booking_create.php";

} else {

    $bookLink = $dashboardLink;

}


/*
|--------------------------------------------------------------------------
| Unread notifications (logged in users only)
|--------------------------------------------------------------------------
*/

$unreadCount = 0;

if ($isLoggedIn) {

    $countStmt = $pdo->prepare("
        SELECT COUNT(*) AS unread_count

        FROM notifications

        WHERE user_id = ?

        AND is_read = 0
    ");

    $countStmt->execute([$_SESSION["user_id"]]);

    $unreadCount =
        $countStmt->fetch()["unread_count"];

}


/*
|--------------------------------------------------------------------------
| Services from the database
|--------------------------------------------------------------------------
*/

$services = $pdo->query("
    SELECT service_id, service_name, description, price

    FROM services

    ORDER BY service_id ASC
")->fetchAll();


/*
|--------------------------------------------------------------------------
| Pick an icon based on the service name
|--------------------------------------------------------------------------
*/

function serviceIcon($name)
{
    $name = strtolower($name);

    if (strpos($name, "oil") !== false)        return "🛢️";
    if (strpos($name, "brake") !== false)      return "🛑";
    if (strpos($name, "diagnostic") !== false) return "🔍";
    if (strpos($name, "alignment") !== false)  return "🎯";
    if (strpos($name, "battery") !== false)    return "🔋";
    if (strpos($name, "full") !== false)       return "🚗";
    if (strpos($name, "air") !== false)        return "❄️";
    if (strpos($name, "tyre") !== false
        || strpos($name, "tire") !== false)    return "🛞";

    return "🔧";
}

?>
<!DOCTYPE html>
<html lang="en">
<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MSBMS | Mechanic Service Booking & Management System</title>
    <link rel="stylesheet" href="<?php echo $base; ?>/assets/styled.css">

</head>


<body class="landing">

    <header class="navbar" id="navbar">

        <div class="container nav-container">

            <a href="#home" class="logo">
                <span class="logo-icon">⚒</span>
                <span>MSBMS</span>
            </a>


            <div class="nav-menu">

                <nav class="nav-links">

                    <a href="#home" class="active">Home</a>

                    <a href="#services">Services</a>

                    <a href="#how-it-works">How It Works</a>

                    <a href="#about">About Us</a>

                    <a href="#contact">Contact</a>

                </nav>


                <div class="nav-buttons">

                    <?php if ($isLoggedIn): ?>

                        <span class="welcome-text">
                            Hi, <strong><?php echo htmlspecialchars($firstName); ?></strong>
                        </span>

                        <a href="<?php echo $base; ?>/notifications/list.php" class="btn btn-login">

                            Notifications

                            <?php if ($unreadCount > 0): ?>

                                <span class="notif-badge">
                                    <?php echo (int) $unreadCount; ?>
                                </span>

                            <?php endif; ?>

                        </a>


                        <a href="<?php echo $base; ?>/logout.php" class="btn btn-signup">
                            Logout
                        </a>

                    <?php else: ?>

                        <a href="<?php echo $base; ?>/login.php" class="btn btn-login">
                            Login
                        </a>

                        <a href="<?php echo $base; ?>/register.php" class="btn btn-signup">
                            Sign Up
                        </a>

                    <?php endif; ?>

                </div>

            </div>


            <button class="menu-button" id="menuButton" aria-label="Toggle menu">
                ☰
            </button>

        </div>

    </header>

    <main>

        <section class="hero" id="home">

            <div class="container hero-container">

                <div class="hero-content">

                    <span class="hero-label">
                        🚗 SIMPLE • FAST • CONVENIENT
                    </span>


                    <h1>

                        Mechanic Service

                        <span>
                            Booking &amp; Management
                        </span>

                        System

                    </h1>


                    <p>

                        Book your vehicle service online,
                        track your vehicle's service status,
                        and manage your appointments
                        all in one place.

                    </p>


                    <div class="hero-buttons">

                        <a href="<?php echo htmlspecialchars($bookLink); ?>"
                           class="btn btn-primary">

                            Book a Service

                            <span>→</span>

                        </a>


                        <a href="#how-it-works"
                           class="btn btn-secondary">

                            Learn More

                        </a>

                    </div>


                    <div class="hero-trust">

                        <div class="trust-item">

                            <span class="trust-icon">
                                ✓
                            </span>

                            Easy Online Booking

                        </div>


                        <div class="trust-item">

                            <span class="trust-icon">
                                ✓
                            </span>

                            Service Tracking

                        </div>


                        <div class="trust-item">

                            <span class="trust-icon">
                                ✓
                            </span>

                            Digital Service History

                        </div>

                    </div>

                </div>

                <div class="hero-image">

                    <div class="garage-background"></div>


                    <div class="car">

                        <div class="car-roof"></div>

                        <div class="window"></div>

                        <div class="headlight"></div>

                        <div class="wheel wheel-left"></div>

                        <div class="wheel wheel-right"></div>

                    </div>


                    <div class="mechanic">

                        <div class="mechanic-head"></div>

                        <div class="mechanic-body"></div>

                        <div class="mechanic-tool"></div>

                    </div>


                    <div class="hero-image-card">

                        <div class="card-icon">
                            ✓
                        </div>

                        <div>

                            <strong>
                                Service Made Easy
                            </strong>

                            <p>
                                Book your appointment online
                            </p>

                        </div>

                    </div>

                </div>

            </div>

        </section>

        <section class="features">

            <div class="container features-grid">


                <div class="feature-card">

                    <div class="feature-icon">
                        📅
                    </div>

                    <div>

                        <h3>
                            Easy Booking
                        </h3>

                        <p>
                            Book appointments online
                            in just a few clicks.
                        </p>

                    </div>

                </div>



                <div class="feature-card">

                    <div class="feature-icon">
                        📍
                    </div>

                    <div>

                        <h3>
                            Real-Time Updates
                        </h3>

                        <p>
                            Track the progress and status
                            of your vehicle service.
                        </p>

                    </div>

                </div>



                <div class="feature-card">

                    <div class="feature-icon">
                        🛡️
                    </div>

                    <div>

                        <h3>
                            Trusted Service
                        </h3>

                        <p>
                            Keep your vehicle and service
                            information organised.
                        </p>

                    </div>

                </div>

            </div>

        </section>

        <section class="services" id="services">

            <div class="container">


                <div class="section-heading">

                    <span class="section-label">
                        OUR SERVICES
                    </span>

                    <h2>
                        Vehicle Services Made Simple
                    </h2>

                    <p>
                        Select the service your vehicle needs
                        and schedule an appointment at a
                        convenient time.
                    </p>

                </div>



                <div class="services-grid">

                    <?php if (count($services) > 0): ?>

                        <?php foreach ($services as $service): ?>

                            <div class="service-card">

                                <div class="service-icon">
                                    <?php echo serviceIcon($service["service_name"]); ?>
                                </div>

                                <h3>
                                    <?php echo htmlspecialchars($service["service_name"]); ?>
                                </h3>

                                <p>
                                    <?php echo htmlspecialchars($service["description"] ?? ""); ?>
                                </p>

                                <div class="service-price">
                                    From R<?php echo number_format($service["price"], 2); ?>
                                </div>

                                <a href="<?php echo htmlspecialchars($bookLink); ?>">
                                    Book Service →
                                </a>

                            </div>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <p class="no-services">
                            No services are available right now. Please check back soon.
                        </p>

                    <?php endif; ?>

                </div>

            </div>

        </section>

        <section class="how-it-works" id="how-it-works">

            <div class="container">


                <div class="section-heading">

                    <span class="section-label">
                        HOW IT WORKS
                    </span>

                    <h2>
                        Book Your Service in 4 Simple Steps
                    </h2>

                    <p>
                        The MSBMS system makes scheduling
                        your vehicle service quick and convenient.
                    </p>

                </div>



                <div class="steps">


                    <div class="step">

                        <div class="step-number">
                            01
                        </div>

                        <h3>
                            Select a Service
                        </h3>

                        <p>
                            Choose the service your vehicle requires.
                        </p>

                    </div>



                    <div class="step">

                        <div class="step-number">
                            02
                        </div>

                        <h3>
                            Choose Date &amp; Time
                        </h3>

                        <p>
                            Select an available appointment time.
                        </p>

                    </div>



                    <div class="step">

                        <div class="step-number">
                            03
                        </div>

                        <h3>
                            Add Your Vehicle
                        </h3>

                        <p>
                            Provide your vehicle and contact details.
                        </p>

                    </div>



                    <div class="step">

                        <div class="step-number">
                            04
                        </div>

                        <h3>
                            Confirm Booking
                        </h3>

                        <p>
                            Review your details and confirm your appointment.
                        </p>

                    </div>

                </div>

            </div>

        </section>

        <section class="about" id="about">

            <div class="container about-container">


                <div class="about-image"></div>



                <div class="about-content">

                    <span class="section-label">
                        ABOUT MSBMS
                    </span>

                    <h2>
                        A Smarter Way to Manage Vehicle Services
                    </h2>

                    <p>
                        MSBMS provides a digital platform for
                        customers and workshops to manage vehicle
                        service bookings, service information and
                        vehicle history.
                    </p>

                    <p>
                        Instead of relying on phone calls, walk-ins
                        and paper records, customers can use the
                        system to manage their appointments and
                        access service information online.
                    </p>


                    <div class="about-list">

                        <div>
                            <span>✓</span>
                            Online appointment scheduling
                        </div>

                        <div>
                            <span>✓</span>
                            Digital vehicle service history
                        </div>

                        <div>
                            <span>✓</span>
                            Service status updates
                        </div>

                        <div>
                            <span>✓</span>
                            Booking notifications
                        </div>

                    </div>


                    <?php if ($isLoggedIn): ?>

                        <a href="<?php echo htmlspecialchars($dashboardLink); ?>"
                           class="btn btn-primary">

                            Go to My Dashboard

                        </a>

                    <?php else: ?>

                        <a href="<?php echo $base; ?>/register.php"
                           class="btn btn-primary">

                            Create an Account

                        </a>

                    <?php endif; ?>

                </div>

            </div>

        </section>

        <section class="cta">

            <div class="container cta-container">

                <div>

                    <h2>
                        Ready to Book Your Vehicle Service?
                    </h2>

                    <p>
                        Schedule your appointment online and
                        make managing your vehicle service easier.
                    </p>

                </div>


                <a href="<?php echo htmlspecialchars($bookLink); ?>"
                   class="btn btn-white">

                    Book a Service →

                </a>

            </div>

        </section>

    </main>

    <footer class="footer" id="contact">

        <div class="container footer-grid">


            <div class="footer-column">

                <a href="#home" class="footer-logo">
                    ⚒ MSBMS
                </a>

                <p>
                    Mechanic Service Booking &amp;
                    Management System.
                </p>

            </div>



            <div class="footer-column">

                <h3>
                    Quick Links
                </h3>

                <a href="#home">
                    Home
                </a>

                <a href="#services">
                    Services
                </a>

                <a href="#how-it-works">
                    How It Works
                </a>

                <a href="#about">
                    About Us
                </a>

            </div>



            <div class="footer-column">

                <h3>
                    Account
                </h3>

                <?php if ($isLoggedIn): ?>

                    <a href="<?php echo htmlspecialchars($dashboardLink); ?>">
                        My Dashboard
                    </a>

                    <?php if ($role === "customer"): ?>

                        <a href="<?php echo $base; ?>/vehicles/list.php">
                            My Vehicles
                        </a>

                    <?php endif; ?>

                    <a href="<?php echo $base; ?>/logout.php">
                        Logout
                    </a>

                <?php else: ?>

                    <a href="<?php echo $base; ?>/login.php">
                        Login
                    </a>

                    <a href="<?php echo $base; ?>/register.php">
                        Register
                    </a>

                    <a href="<?php echo $base; ?>/login.php">
                        Book a Service
                    </a>

                <?php endif; ?>

            </div>



            <div class="footer-column">

                <h3>
                    Contact
                </h3>

                <p>
                    📧 support@msbms.com
                </p>

                <p>
                    📞 012 345 6789
                </p>

                <p>
                    📍 South Africa
                </p>

            </div>

        </div>



        <div class="footer-bottom">

            <div class="container">

                <p>
                    © <?php echo date("Y"); ?> MSBMS. All rights reserved.
                </p>

            </div>

        </div>

    </footer>

    <script>

        document
            .getElementById("menuButton")
            .addEventListener("click", function () {

                document
                    .getElementById("navbar")
                    .classList
                    .toggle("open");

            });

    </script>


</body>
</html>