<?php

class Session
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
    }

    public static function userId(): ?int
    {
        self::start();
        return isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
    }

    public static function role(): ?string
    {
        self::start();
        return $_SESSION['role'] ?? null;
    }

    public static function fullName(): ?string
    {
        self::start();
        return $_SESSION['full_name'] ?? null;
    }

    public static function login(array $user): void
    {
        self::start();
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int)$user['user_id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['full_name'] = $user['full_name'];
    }

    public static function logout(): void
    {
        self::start();
        $_SESSION = [];
        session_destroy();
    }
}
