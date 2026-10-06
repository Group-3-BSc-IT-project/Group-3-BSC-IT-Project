<?php
class User
{
    public function __construct(
        private int $id, private string $fullName, private string $email,
        private string $passwordHash, private string $role, private ?string $phone = null, private ?string $address = null
    ) {}
    public function id(): int { return $this->id; }
    public function fullName(): string { return $this->fullName; }
    public function email(): string { return $this->email; }
    public function passwordHash(): string { return $this->passwordHash; }
    public function role(): string { return $this->role; }
}
