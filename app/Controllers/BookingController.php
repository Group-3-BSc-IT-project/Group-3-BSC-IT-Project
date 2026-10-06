<?php

class BookingController
{
    public function __construct(
        private BookingService $service,
        private BookingRepository $bookings,
        private VehicleRepository $vehicles,
        private ServiceRepository $services,
        private UserRepository $users
    ) {
    }

    // ---- Actions ----

    public function create(): array
    {
        return $this->service->create(
            Session::userId(),
            (int)$_POST['vehicle_id'],
            (int)$_POST['service_id'],
            $_POST['booking_date'] ?? '',
            $_POST['booking_time'] ?? '',
            trim($_POST['notes'] ?? '')
        );
    }

    public function cancel(int $id): bool
    {
        return $this->bookings->cancel($id, Session::userId());
    }

    public function reschedule(int $id): array
    {
        return $this->service->reschedule(
            $id,
            Session::userId(),
            $_POST['booking_date'] ?? '',
            $_POST['booking_time'] ?? ''
        );
    }

    public function assign(int $id): array
    {
        return $this->service->assign($id, (int)$_POST['mechanic_id']);
    }

    public function complete(int $id): bool
    {
        return $this->bookings->complete($id, Session::userId());
    }

    // ---- Queries ----

    public function customerBookings(): array
    {
        return $this->bookings->findForCustomer(Session::userId());
    }

    public function mechanicBookings(): array
    {
        return $this->bookings->forMechanic(Session::userId());
    }

    public function all(): array
    {
        return $this->bookings->findAll();
    }

    public function find(int $id): ?array
    {
        return $this->bookings->find($id);
    }

    public function customerFind(int $id): ?array
    {
        return $this->bookings->findForCustomerById($id, Session::userId());
    }

    // ---- Lookups for forms ----

    public function vehicles(): array
    {
        return $this->vehicles->forBooking(Session::userId());
    }

    public function services(): array
    {
        return $this->services->all();
    }

    public function mechanics(): array
    {
        return $this->users->findMechanics();
    }
}
