<?php

class VehicleController
{
    public function __construct(
        private VehicleService $service,
        private VehicleRepository $vehicles,
        private UserRepository $users
    ) {
    }

    public function create(): array
    {
        // Admins pick the owner from a dropdown; customers own the vehicle themselves.
        if (Session::role() === 'admin') {
            $customerId = (int)($_POST['customer_id'] ?? 0);
        } else {
            $customerId = Session::userId();
        }

        return $this->service->create(
            $customerId,
            trim($_POST['registration_number'] ?? ''),
            trim($_POST['make'] ?? ''),
            trim($_POST['model'] ?? ''),
            trim($_POST['year'] ?? ''),
            trim($_POST['vin'] ?? '')
        );
    }

    public function list(): array
    {
        if (Session::role() === 'admin') {
            return $this->vehicles->all();
        }

        return $this->vehicles->byCustomer(Session::userId());
    }

    public function customers(): array
    {
        return $this->users->findCustomers();
    }
}
