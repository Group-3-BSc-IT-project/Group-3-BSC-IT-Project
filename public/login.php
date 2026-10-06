<?php require_once __DIR__ . '/_init.php'; 
// ---- Login Logic ---- 
$message = ''; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') { 

    [$ok, $message] = $authController->login();

    if ($ok) { $role = Session::role();
        if ($role === 'admin') { $target = 'admin.php'; 
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
    <link rel="stylesheet" href="assets/styled.css">
</head>
<body>

<div class="auth-shell">

    <div class="card auth-card form">

        <div class="auth-logo">
            <span class="logo-mark">✦</span>
            MSBMS
        </div>

        <h1>Welcome back</h1> 
        <p class="sub"> Sign in to your MSBMS account </p>
        

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

</body>
</html>