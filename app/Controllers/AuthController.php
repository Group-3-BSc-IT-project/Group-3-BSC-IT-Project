<?php

class AuthController
{
    public function __construct(
        private AuthService $service
    ) {
    }

    public function login(): array
    {
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($this->service->login($email, $password)) {
            return [true, ''];
        }

        return [false, 'Incorrect email or password.'];
    }

    public function register(): array
    {
        return $this->service->register(
            trim($_POST['full_name'] ?? ''),
            trim($_POST['email'] ?? ''),
            trim($_POST['phone'] ?? ''),
            trim($_POST['address'] ?? ''),
            $_POST['password'] ?? ''
        );
    }

    public function createMechanic(): array
    {
        return $this->service->createMechanic(
            trim($_POST['full_name'] ?? ''),
            trim($_POST['email'] ?? ''),
            trim($_POST['phone'] ?? ''),
            trim($_POST['address'] ?? ''),
            $_POST['password'] ?? ''
        );
    }
}
