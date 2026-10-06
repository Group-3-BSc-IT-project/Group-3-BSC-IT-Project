<?php
class Booking
{
    public function __construct(
        private int $customerId, private int $vehicleId, private int $serviceId,
        private string $date, private string $time, private ?string $notes = null, private string $status = 'Pending'
    ) {}
    public function customerId(): int { return $this->customerId; }
    public function vehicleId(): int { return $this->vehicleId; }
    public function serviceId(): int { return $this->serviceId; }
    public function date(): string { return $this->date; }
    public function time(): string { return $this->time; }
    public function notes(): ?string { return $this->notes; }
    public function status(): string { return $this->status; }
}
