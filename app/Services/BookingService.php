<?php
class BookingService
{
    public function __construct(private BookingRepository $bookings, private VehicleRepository $vehicles, private ServiceRepository $services, private UserRepository $users, private NotificationRepository $notifications) {}
    public function create(int $customerId,int $vehicleId,int $serviceId,string $date,string $time,string $notes): array {
        if(!$this->vehicles->existsForCustomer($vehicleId,$customerId)) return[false,'Invalid vehicle selected.'];
        if(!$this->services->exists($serviceId)) return[false,'Invalid service selected.'];
        if($date < date('Y-m-d')) return[false,'You cannot book a date in the past.'];
        if(!$time) return[false,'Booking time is required.'];
        if($this->bookings->slotTaken($date,$time)) return[false,'That time slot is already booked.'];
        $ok=$this->bookings->create(new Booking($customerId,$vehicleId,$serviceId,$date,$time,$notes));
        if($ok)$this->notifications->create(new Notification($customerId,'Your booking has been created successfully.'),'booking');
        return[$ok,$ok?'Booking created successfully.':'Failed to create booking.'];
    }
    public function reschedule(int $id,int $customerId,string $date,string $time): array {
        if($date<date('Y-m-d'))return[false,'You cannot select a past date.'];
        if($this->bookings->slotTaken($date,$time,$id))return[false,'That time slot is already booked.'];
        $ok=$this->bookings->reschedule($id,$customerId,$date,$time);return[$ok,$ok?'Booking rescheduled successfully.':'Failed to reschedule booking.'];
    }
    public function assign(int $bookingId,int $mechanicId): array {
        if(!$this->users->isMechanic($mechanicId))return[false,'Invalid mechanic selected.'];
        $b=$this->bookings->find($bookingId);if(!$b)return[false,'Booking not found.'];
        $ok=$this->bookings->assignMechanic($bookingId,$mechanicId);
        if($ok)$this->notifications->create(new Notification($mechanicId,'You have been assigned to booking #'.$bookingId.' for '.$b['booking_date'].' at '.$b['booking_time']),'booking');
        return[$ok,$ok?'Mechanic assigned successfully.':'Failed to assign mechanic.'];
    }
}
