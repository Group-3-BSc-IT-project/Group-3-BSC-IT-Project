<?php
class Repair
{
    public function __construct(private int $bookingId, private int $mechanicId, private string $description, private string $status = 'Pending', private float $cost = 0, private ?string $repairDate = null) {}
    public function bookingId(): int { return $this->bookingId; }
    public function mechanicId(): int { return $this->mechanicId; }
    public function description(): string { return $this->description; }
    public function status(): string { return $this->status; }
    public function cost(): float { return $this->cost; }
    public function repairDate(): ?string { return $this->repairDate; }
}
