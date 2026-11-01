<?php

class PasswordResetRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = new PDO(
            'mysql:host=localhost;dbname=msbms;charset=utf8mb4',
            'root',
            '',
            [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]
        );
    }

    public function findUserByEmail(string $email): ?array
    {
    $stmt = $this->pdo->prepare(
        'SELECT user_id AS id, email FROM users WHERE email = ? LIMIT 1'
    );
    $stmt->execute([$email]);
    return $stmt->fetch() ?: null;
    }

    public function create(int $userId, string $tokenHash, int $minutes): void
{
    $this->pdo->prepare('DELETE FROM password_resets WHERE user_id = ?')->execute([$userId]);

    $minutes = (int)$minutes;
    $stmt = $this->pdo->prepare(
        "INSERT INTO password_resets (user_id, token_hash, expires_at)
         VALUES (?, ?, DATE_ADD(NOW(), INTERVAL $minutes MINUTE))"
    );
    $stmt->execute([$userId, $tokenHash]);
}

    public function findValid(string $tokenHash): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM password_resets WHERE token_hash = ? AND expires_at > NOW() LIMIT 1'
        );
        $stmt->execute([$tokenHash]);
        return $stmt->fetch() ?: null;
    }

    public function updatePassword(int $userId, string $passwordHash): void
{
    $stmt = $this->pdo->prepare('UPDATE users SET password = ? WHERE user_id = ?');
    $stmt->execute([$passwordHash, $userId]);
}

    public function deleteForUser(int $userId): void
    {
        $this->pdo->prepare('DELETE FROM password_resets WHERE user_id = ?')->execute([$userId]);
    }

    public function emailExists(string $email): bool
{
    $stmt = $this->pdo->prepare('SELECT 1 FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    return (bool)$stmt->fetch();
}

public function createMechanic(string $name, string $email, ?string $phone, ?string $address): int
{
    // Random password that nobody knows; the mechanic sets their own via the emailed link
    $unusable = password_hash(bin2hex(random_bytes(32)), PASSWORD_DEFAULT);

    $stmt = $this->pdo->prepare(
        "INSERT INTO users (full_name, email, phone, address, password, role)
         VALUES (?, ?, ?, ?, ?, 'mechanic')"
    );
    $stmt->execute([$name, $email, $phone, $address, $unusable]);
    return (int)$this->pdo->lastInsertId();
}
}