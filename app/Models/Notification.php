<?php
class Notification
{
    public function __construct(private int $userId, private string $message) {}
    public function userId(): int { return $this->userId; }
    public function message(): string { return $this->message; }
}
