<?php
class RepairService
{
    public function __construct(private RepairRepository $repairs, private BookingRepository $bookings) {}

    public function create(int $bookingId, int $mechanicId, string $description, string $date, string $cost): array
    {
        if (!$this->repairs->bookingBelongsToMechanic($bookingId, $mechanicId)) {
            return [false, 'Booking not found or not assigned to you.'];
        }
        if (trim($description) === '') {
            return [false, 'Please enter a repair description.'];
        }
        if ($cost !== '' && (!is_numeric($cost) || (float)$cost < 0)) {
            return [false, 'Cost must be a number (0 or more).'];
        }

        $ok = $this->repairs->create(
            new Repair($bookingId, $mechanicId, $description, 'In Progress', (float)($cost ?: 0), $date ?: null)
        );

        // Saving a repair no longer completes the booking.
        // The mechanic finishes the job on job_complete.php, where the final price is entered.

        return [$ok, $ok ? 'Repair record added successfully.' : 'Failed to save repair record.'];
    }

    public function all(): array
    {
        return $this->repairs->all();
    }

    public function totalCost(int $bookingId, int $mechanicId): float
    {
    return $this->repairs->totalCost($bookingId, $mechanicId);
    }
}