<?php require_once __DIR__ . '/_init.php';

// ---- Login Logic ----
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    [$ok, $message] = $authController->login();

    if ($ok) {

        $role = Session::role();

        if ($role === 'admin') {
            $target = 'admin.php';
        } elseif ($role === 'mechanic') {
            $target = 'mechanic.php';
        } else {
            $target = 'customer.php';
        }

        header('Location: ' . $target);
        exit;
    }
}
?>


<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Login | MSBMS</title>

    <!-- Dashboard / auth styles -->
    <link rel="stylesheet" href="assets/styled.css?v=<?php echo time(); ?>">

    <!-- Landing navbar + footer styles (scoped under .landing) -->
    <link rel="stylesheet" href="assets/css/landing.css?v=<?php echo time(); ?>">
</head>
<body>


<!--
    The navbar and footer are wrapped in a .landing wrapper so the landing
    styles only affect them. The login card sits outside the wrapper, so it
    keeps the normal dashboard / auth styling.
    display:contents keeps the sticky navbar working.
-->

<div class="landing" style="display:contents">

    <header class="navbar" id="navbar">

        <div class="container nav-container">

            <a href="index.php" class="logo">

                <span class="logo-icon">⚒</span>

                <span>MSBMS</span>

            </a>


            <div class="nav-menu">

                <nav class="nav-links">

                    <a href="index.php#home">Home</a>

                    <a href="index.php#services">Services</a>

                    <a href="index.php#how-it-works">How It Works</a>

                    <a href="index.php#about">About Us</a>

                    <a href="index.php#contact">Contact</a>

                </nav>


                <div class="nav-buttons">

                    <a href="login.php" class="btn btn-login">
                        Login
                    </a>

                    <a href="register.php" class="btn btn-signup">
                        Sign Up
                    </a>

                </div>

            </div>


            <button class="menu-button" id="menuButton" aria-label="Toggle menu">
                ☰
            </button>

        </div>

    </header>

</div>


<div class="auth-shell" style="min-height:75vh">

    <div class="card auth-card form">

        <div class="auth-logo">
            <span class="logo-mark">✦</span>
            MSBMS
        </div>

        <h1>Welcome back</h1>
        <p class="sub">Sign in to your MSBMS account</p>


        <?php if ($message): ?>
            <p class="badge yellow auth-message"><?= e($message) ?></p>
        <?php endif; ?>

        <form method="post">
            <div class="field">
                <label>Email</label>
                <input type="email" name="email" required>
            </div>
            <div class="field">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            <button class="btn">Login</button>
        </form>

        <p class="auth-footer">
            Don't have an account?
            <a href="register.php">Create customer account</a>
        </p>
    </div>
</div>


<div class="landing" style="display:contents">

    <footer class="footer" id="contact">

        <div class="container footer-grid">


            <div class="footer-column">

                <a href="index.php" class="footer-logo">
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

                <a href="index.php#home">
                    Home
                </a>

                <a href="index.php#services">
                    Services
                </a>

                <a href="index.php#how-it-works">
                    How It Works
                </a>

                <a href="index.php#about">
                    About Us
                </a>

            </div>



            <div class="footer-column">

                <h3>
                    Account
                </h3>

                <a href="login.php">
                    Login
                </a>

                <a href="register.php">
                    Register
                </a>

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

</div>


<!-- Mobile menu toggle -->

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