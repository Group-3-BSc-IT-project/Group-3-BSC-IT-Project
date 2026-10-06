<?php

class AdminController
{
    public function __construct(
        private BookingRepository $bookings,
        private UserRepository $users
    ) {
    }

    public function stats(): array
    {
        return $this->bookings->counts();
    }

    public function mechanics(): array
    {
        return $this->users->findMechanics();
    }
}
