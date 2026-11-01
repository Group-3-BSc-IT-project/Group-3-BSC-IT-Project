<?php

require_once __DIR__ . '/../Services/Mailer.php';
require_once __DIR__ . '/../Services/PasswordPolicy.php';

class PasswordResetController
{
    private const INVITE_TTL_MINUTES = 2880; // 48 hours
    private const TOKEN_TTL_MINUTES = 60;

    public function __construct(
        private PasswordResetRepository $repo,
        private bool $devMode = true   // set to false in production
    ) {}

    /**
     * Step 1: user submits their email.
     * Returns [message, devLink]. The message is always the same so
     * attackers can't discover which emails are registered.
     */
    public function requestReset(): array
    {
        $email = trim($_POST['email'] ?? '');
        $message = 'If that email is registered, a reset link has been sent.';
        $devLink = null;

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return [$message, null];
        }

        $user = $this->repo->findUserByEmail($email);
        if (!$user) {
            return [$message, null];
        }

        $token   = bin2hex(random_bytes(32));
        $hash    = hash('sha256', $token);

$this->repo->create((int)$user['id'], $hash, self::TOKEN_TTL_MINUTES);

        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $base   = $scheme . '://' . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
        $link   = $base . '/reset_password.php?token=' . $token;

        if ($this->devMode) {
            // XAMPP can't send mail by default, so show the link on screen
            $devLink = $link;
        } else {
    $subject = 'MSBMS password reset';
    $body    = "Hi,\n\nClick the link below to reset your password (valid for "
             . self::TOKEN_TTL_MINUTES . " minutes):\n\n$link\n\n"
             . "If you didn't request this, you can ignore this email.";

    Mailer::send($email, $subject, $body);
}

        return [$message, $devLink];
    }

    /** Check a token from the URL */
    public function validToken(string $token): ?array
    {
        if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
            return null;
        }
        return $this->repo->findValid(hash('sha256', $token));
    }

    /** Step 2: user submits a new password. Returns [ok, message]. */
    public function resetPassword(string $token): array
    {
        $reset = $this->validToken($token);
        if (!$reset) {
            return [false, 'This reset link is invalid or has expired.'];
        }

        $password = $_POST['password'] ?? '';
        $confirm  = $_POST['confirm_password'] ?? '';

        $error = PasswordPolicy::validate($password);
        if ($error !== null) {
            return [false, $error];
        }
        if ($password !== $confirm) {
            return [false, 'Passwords do not match.'];
        }

        $userId = (int)$reset['user_id'];
        $this->repo->updatePassword($userId, password_hash($password, PASSWORD_DEFAULT));
        $this->repo->deleteForUser($userId); // token can't be reused

        return [true, 'Password updated. You can now log in.'];
    }

    public function inviteMechanic(): array
{
    $name    = trim($_POST['full_name'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $phone   = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');

    if (mb_strlen($name) < 2) {
        return [false, 'Enter the mechanic\'s full name.'];
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return [false, 'Enter a valid email address.'];
    }
    if ($phone !== '' && !preg_match('/^(\+27|0)[6-8][0-9]{8}$/', $phone)) {
        return [false, 'Enter a valid South African phone number.'];
    }
    if ($this->repo->emailExists($email)) {
        return [false, 'That email is already registered.'];
    }

    $userId = $this->repo->createMechanic($name, $email, $phone ?: null, $address ?: null);

    $token = bin2hex(random_bytes(32));
    $this->repo->create($userId, hash('sha256', $token), self::INVITE_TTL_MINUTES);

    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $base   = $scheme . '://' . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
    $link   = $base . '/reset_password.php?token=' . $token;

    if ($this->devMode) {
        return [true, "Mechanic created. Dev mode, setup link: $link"];
    }

    $sent = Mailer::send(
        $email,
        'Your MSBMS mechanic account',
        "Hi $name,\n\nAn MSBMS mechanic account has been created for you.\n\n"
        . "Set your password here (valid for 48 hours):\n$link\n\n"
        . "If you weren't expecting this, ignore this email."
    );

    return $sent
        ? [true, "Mechanic created. A setup link was emailed to $email."]
        : [true, 'Mechanic created, but the email could not be sent. Ask them to use "Forgot password" on the login page.'];
    }
}