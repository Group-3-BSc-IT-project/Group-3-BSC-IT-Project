<?php
class RepairService
{
    public function __construct(private RepairRepository $repairs, private BookingRepository $bookings) {}
    public function create(int $bookingId,int $mechanicId,string $description,string $date,string $cost): array {
        if(!$this->repairs->bookingBelongsToMechanic($bookingId,$mechanicId))return[false,'Booking not found or not assigned to you.'];
        if(trim($description)==='')return[false,'Please enter a repair description.'];
        if($cost!==''&&!is_numeric($cost))return[false,'Cost must be a number.'];
        $ok=$this->repairs->create(new Repair($bookingId,$mechanicId,$description,'In Progress',(float)($cost?:0),$date?:null));
        if($ok)$this->bookings->complete($bookingId,$mechanicId);
        return[$ok,$ok?'Repair record added successfully.':'Failed to save repair record.'];
    }
    public function all(): array { return $this->repairs->all(); }
}