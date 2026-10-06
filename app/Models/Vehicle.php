<?php
class Vehicle
{
    public function __construct(
        private int $customerId, private string $registration, private string $make,
        private string $model, private ?int $year = null, private ?string $vin = null
    ) {}
    public function customerId(): int { return $this->customerId; }
    public function registration(): string { return $this->registration; }
    public function make(): string { return $this->make; }
    public function model(): string { return $this->model; }
    public function year(): ?int { return $this->year; }
    public function vin(): ?string { return $this->vin; }
}
