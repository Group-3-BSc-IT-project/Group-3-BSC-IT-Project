<?php
class VehicleRepository
{
    public function __construct(private PDO $db) {}
    public function existsForCustomer(int $vehicleId,int $customerId): bool { $s=$this->db->prepare('SELECT vehicle_id FROM vehicles WHERE vehicle_id=? AND customer_id=?'); $s->execute([$vehicleId,$customerId]); return (bool)$s->fetch(); }
    public function registrationExists(string $reg): bool { $s=$this->db->prepare('SELECT vehicle_id FROM vehicles WHERE registration_number=?'); $s->execute([$reg]); return (bool)$s->fetch(); }
    public function create(Vehicle $v): bool { $s=$this->db->prepare('INSERT INTO vehicles(customer_id,registration_number,make,model,year,vin) VALUES(?,?,?,?,?,?)'); return $s->execute([$v->customerId(),$v->registration(),$v->make(),$v->model(),$v->year(),$v->vin()]); }
    public function byCustomer(int $id): array { $s=$this->db->prepare('SELECT v.*,u.full_name owner_name,u.phone owner_phone FROM vehicles v JOIN users u ON v.customer_id=u.user_id WHERE v.customer_id=? ORDER BY v.vehicle_id DESC'); $s->execute([$id]); return $s->fetchAll(); }
    public function all(): array { return $this->db->query('SELECT v.*,u.full_name owner_name,u.phone owner_phone FROM vehicles v JOIN users u ON v.customer_id=u.user_id ORDER BY v.vehicle_id DESC')->fetchAll(); }
    public function forBooking(int $customerId): array { $s=$this->db->prepare('SELECT vehicle_id,registration_number,make,model FROM vehicles WHERE customer_id=? ORDER BY registration_number'); $s->execute([$customerId]); return $s->fetchAll(); }
}
