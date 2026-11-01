<?php

class PasswordPolicy
{
    public const MIN_LENGTH = 8;

    private const COMMON = [
        'password', 'password1', 'password123', '12345678', '123456789',
        'qwerty123', 'iloveyou', 'admin123', 'welcome1', 'letmein123',
    ];

    public const HINT = 'At least 8 characters with an uppercase letter, a lowercase letter, a number and a special character.';

    /** Returns an error message, or null if the password is strong enough. */
    public static function validate(string $password, string $email = ''): ?string
    {
        if (strlen($password) < self::MIN_LENGTH) {
            return 'Password must be at least ' . self::MIN_LENGTH . ' characters.';
        }
        if (strlen($password) > 72) {
            return 'Password must be 72 characters or fewer.';
        }
        if (!preg_match('/[A-Z]/', $password)) {
            return 'Password must contain an uppercase letter.';
        }
        if (!preg_match('/[a-z]/', $password)) {
            return 'Password must contain a lowercase letter.';
        }
        if (!preg_match('/\d/', $password)) {
            return 'Password must contain a number.';
        }
        if (!preg_match('/[^A-Za-z0-9]/', $password)) {
            return 'Password must contain a special character (e.g. ! @ # $ %).';
        }
        if (in_array(strtolower($password), self::COMMON, true)) {
            return 'That password is too common. Choose a different one.';
        }
        if ($email !== '') {
            $local = strtolower(explode('@', $email)[0]);
            if (strlen($local) >= 4 && str_contains(strtolower($password), $local)) {
                return 'Password must not contain your email name.';
            }
        }
        return null;
    }
}