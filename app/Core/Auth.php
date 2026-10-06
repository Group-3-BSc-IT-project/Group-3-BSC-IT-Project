<?php

class Auth
{
    public static function requireLogin(): void
    {
        if (Session::userId() === null) {
            header('Location: ' . BASE_URL . '/login.php');
            exit;
        }
    }

    public static function requireRole(string $role): void
    {
        self::requireLogin();
        if (Session::role() !== $role) {
            http_response_code(403);
            exit('Access denied.');
        }
    }

    public static function requireAnyRole(array $roles): void
    {
        self::requireLogin();
        if (!in_array(Session::role(), $roles, true)) {
            http_response_code(403);
            exit('Access denied.');
        }
    }
}
