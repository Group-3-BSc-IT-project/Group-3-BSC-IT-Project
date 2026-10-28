<?php
/**
 * UserController
 *
 * Handles reading and updating the logged-in user's profile.
 *
 * Matches the users table:
 *   user_id, full_name, email, password, role, phone, address
 *
 * Uses UserRepository's:
 *   - findById(int $userId): ?array
 *   - findByEmail(string $email): ?array
 *   - update(int $userId, array $data): bool
 */
class UserController
{
    private UserRepository $userRepo;

    public function __construct(UserRepository $userRepo)
    {
        $this->userRepo = $userRepo;
    }

    /**
     * Return the current user's profile as an associative array.
     */
    public function profile(): array
    {
        $userId = Session::userId();
        $user   = $this->userRepo->findById($userId);

        if (!$user) {
            throw new RuntimeException('User not found.');
        }

        return $user;
    }

    if ($phone !== '' && !preg_match('/^\d{10}$/', $phone)) {
    return [false, 'Phone number must be exactly 10 digits.'];
}


    /**
     * Handle the profile update form submission.
     * Returns [bool $ok, string $message].
     */
    public function updateProfile(): array
    {
        $userId = Session::userId();

        $fullName = trim($_POST['full_name'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $phone    = trim($_POST['phone'] ?? '');
        $address  = trim($_POST['address'] ?? '');

        if ($fullName === '' || $email === '') {
            return [false, 'Full name and email are required.'];
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return [false, 'Please enter a valid email address.'];
        }

        // Make sure the email isn't already used by a different account
        $existing = $this->userRepo->findByEmail($email);
        if ($existing && (int)$existing['user_id'] !== $userId) {
            return [false, 'That email address is already in use.'];
        }

        try {
            $this->userRepo->update($userId, [
                'full_name' => $fullName,
                'email'     => $email,
                'phone'     => $phone,
                'address'   => $address,
            ]);
        } catch (Throwable $e) {
            return [false, 'Could not update profile. Please try again.'];
        }

        // Keep the session's display name in sync, if Session stores one
        if (method_exists('Session', 'setFullName')) {
            Session::setFullName($fullName);
        }

        return [true, 'Profile updated successfully.'];
    }

    /**
     * Handle a password change submission, if the form includes one.
     * Returns [bool $ok, string $message].
     */
    public function updatePassword(): array
    {
        $userId = Session::userId();

        $current = $_POST['current_password'] ?? '';
        $new     = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if ($current === '' || $new === '' || $confirm === '') {
            return [false, 'All password fields are required.'];
        }

        if ($new !== $confirm) {
            return [false, 'New password and confirmation do not match.'];
        }

        if (strlen($new) < 8) {
            return [false, 'New password must be at least 8 characters.'];
        }

        $user = $this->userRepo->findById($userId);
        if (!$user || !password_verify($current, $user['password'])) {
            return [false, 'Current password is incorrect.'];
        }

        try {
            $this->userRepo->update($userId, [
                'password' => password_hash($new, PASSWORD_DEFAULT),
            ]);
        } catch (Throwable $e) {
            return [false, 'Could not update password. Please try again.'];
        }

        return [true, 'Password updated successfully.'];
    }
}
