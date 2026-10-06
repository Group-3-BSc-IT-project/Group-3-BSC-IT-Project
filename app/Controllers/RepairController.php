<?php

class RepairController
{
    public function __construct(
        private RepairService $service,
        private BookingRepository $bookings
    ) {
    }

    public function create(int $bookingId): array
    {
        return $this->service->create(
            $bookingId,
            Session::userId(),
            trim($_POST['description'] ?? ''),
            $_POST['repair_date'] ?? '',
            trim($_POST['cost'] ?? '')
        );
    }

    public function booking(int $id): ?array
    {
        return $this->bookings->find($id);
    }

    public function all(): array
    {
        return $this->service->all();
    }
}