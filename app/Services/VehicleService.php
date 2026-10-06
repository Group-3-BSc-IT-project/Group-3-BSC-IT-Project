<?php
class VehicleService
{
    public function __construct(private VehicleRepository $vehicles, private UserRepository $users) {}
    public function create(int $customerId,string $registration,string $make,string $model,string $year,string $vin): array {
        foreach([[$registration,'Registration number'],[$make,'Make'],[$model,'Model']] as $x) if(trim($x[0])==='') return[false,$x[1].' is required.'];
        if($year!==''&&!ctype_digit($year))return[false,'Year must be a number.'];
        if($this->vehicles->registrationExists($registration))return[false,'A vehicle with this registration number already exists.'];
        $ok=$this->vehicles->create(new Vehicle($customerId,$registration,$make,$model,$year===''?null:(int)$year,$vin===''?null:$vin));return[$ok,$ok?'Vehicle added successfully.':'Something went wrong while saving the vehicle.'];
    }
}
