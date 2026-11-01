<?php
require_once __DIR__ . '/_init.php';
require_once __DIR__ . '/../app/Repositories/PasswordResetRepository.php';
require_once __DIR__ . '/../app/Controllers/PasswordResetController.php';

$resetController = new PasswordResetController(new PasswordResetRepository(), false);

$token   = $_GET['token'] ?? ($_POST['token'] ?? '');
$valid   = $resetController->validToken($token) !== null;
$message = '';
$done    = false;

if ($valid && $_SERVER['REQUEST_METHOD'] === 'POST') {
    [$done, $message] = $resetController->resetPassword($token);
    if ($done) {
        $valid = false;
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Reset Password | MSBMS</title>
    <link rel="stylesheet" href="assets/styled.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="assets/css/landing.css?v=<?php echo time(); ?>">
</head>
<body>

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
                    <a href="login.php" class="btn btn-login">Login</a>
                    <a href="register.php" class="btn btn-signup">Sign Up</a>
                </div>
            </div>
            <button class="menu-button" id="menuButton" aria-label="Toggle menu">☰</button>
        </div>
    </header>
</div>

<div class="auth-shell" style="min-height:75vh">
    <div class="card auth-card form">
        <div class="auth-logo"><span class="logo-mark">✦</span> MSBMS</div>

        <h1>Set your password</h1>

        <?php if ($message): ?>
            <p class="badge yellow auth-message"><?= e($message) ?></p>
        <?php endif; ?>

        <?php if ($done): ?>
            <p class="sub">Your password has been updated.</p>
            <a class="btn" href="login.php">Go to login</a>

        <?php elseif (!$valid): ?>
            <p class="sub">This link is invalid or has expired.</p>
            <a class="btn" href="forgot_password.php">Request a new link</a>

        <?php else: ?>
            <p class="sub">Choose a strong password.</p>
            <form method="post" id="resetForm">
                <input type="hidden" name="token" value="<?= e($token) ?>">

                <div class="field">
                    <label>New password</label>
                    <div class="password-wrapper">
                        <input type="password" name="password" id="passwordInput"
                               minlength="8" maxlength="72" required autocomplete="new-password">
                        <button type="button" class="btn-toggle-password" id="togglePasswordBtn"
                                aria-label="Toggle password visibility">Show</button>
                    </div>
                    <ul id="pwRules" style="font-size:12px;list-style:none;padding:0;margin:6px 0 0">
                        <li data-rule="len">At least 8 characters</li>
                        <li data-rule="upper">An uppercase letter</li>
                        <li data-rule="lower">A lowercase letter</li>
                        <li data-rule="num">A number</li>
                        <li data-rule="special">A special character</li>
                    </ul>
                </div>

                <div class="field">
                    <label>Confirm password</label>
                    <input type="password" name="confirm_password" id="confirmInput"
                           minlength="8" maxlength="72" required autocomplete="new-password">
                    <p id="matchMsg" style="font-size:12px;margin:6px 0 0"></p>
                </div>

                <button class="btn">Update password</button>
            </form>
        <?php endif; ?>
    </div>
</div>

<div class="landing" style="display:contents">
    <footer class="footer" id="contact">
        <div class="container footer-grid">
            <div class="footer-column">
                <a href="index.php" class="footer-logo">⚒ MSBMS</a>
                <p>Mechanic Service Booking &amp; Management System.</p>
            </div>
            <div class="footer-column">
                <h3>Quick Links</h3>
                <a href="index.php#home">Home</a>
                <a href="index.php#services">Services</a>
                <a href="index.php#how-it-works">How It Works</a>
                <a href="index.php#about">About Us</a>
            </div>
            <div class="footer-column">
                <h3>Account</h3>
                <a href="login.php">Login</a>
                <a href="register.php">Register</a>
            </div>
            <div class="footer-column">
                <h3>Contact</h3>
                <p>📧 support@msbms.com</p>
                <p>📞 012 345 6789</p>
                <p>📍 South Africa</p>
            </div>
        </div>
        <div class="footer-bottom">
            <div class="container">
                <p>© <?php echo date("Y"); ?> MSBMS. All rights reserved.</p>
            </div>
        </div>
    </footer>
</div>

<script>
    // Mobile menu
    document.getElementById("menuButton").addEventListener("click", function () {
        document.getElementById("navbar").classList.toggle("open");
    });

    const passwordInput = document.getElementById("passwordInput");
    const confirmInput  = document.getElementById("confirmInput");

    if (passwordInput) {
        // Show / Hide
        const toggleBtn = document.getElementById("togglePasswordBtn");
        toggleBtn.addEventListener("click", function () {
            const isPassword = passwordInput.type === "password";
            passwordInput.type = isPassword ? "text" : "password";
            confirmInput.type  = isPassword ? "text" : "password";
            this.textContent   = isPassword ? "Hide" : "Show";
        });

        // Live rules checklist
        const tests = {
            len:     v => v.length >= 8,
            upper:   v => /[A-Z]/.test(v),
            lower:   v => /[a-z]/.test(v),
            num:     v => /\d/.test(v),
            special: v => /[^A-Za-z0-9]/.test(v)
        };
        const items = document.querySelectorAll('#pwRules li');
        items.forEach(li => li.dataset.text = li.textContent);

        const matchMsg = document.getElementById("matchMsg");

        function update() {
            items.forEach(function (li) {
                const ok = tests[li.dataset.rule](passwordInput.value);
                li.style.color = ok ? '#16a34a' : '#6b7280';
                li.textContent = (ok ? '✓ ' : '• ') + li.dataset.text;
            });

            if (confirmInput.value === '') {
                matchMsg.textContent = '';
            } else if (confirmInput.value === passwordInput.value) {
                matchMsg.textContent = '✓ Passwords match';
                matchMsg.style.color = '#16a34a';
            } else {
                matchMsg.textContent = 'Passwords do not match';
                matchMsg.style.color = '#dc2626';
            }
        }

        passwordInput.addEventListener('input', update);
        confirmInput.addEventListener('input', update);
        update();

        // Block submit if the rules aren't met (server checks again anyway)
        document.getElementById("resetForm").addEventListener("submit", function (e) {
            const v = passwordInput.value;
            const allOk = Object.values(tests).every(t => t(v));
            if (!allOk || v !== confirmInput.value) {
                e.preventDefault();
                update();
            }
        });
    }
</script>

</body>
</html>