<?php
class RepairRepository
{
    public function __construct(private PDO $db) {}
    public function bookingBelongsToMechanic(int $bookingId,int $mechanicId): bool { $s=$this->db->prepare('SELECT booking_id FROM bookings WHERE booking_id=? AND mechanic_id=?');$s->execute([$bookingId,$mechanicId]);return(bool)$s->fetch(); }
    public function create(Repair $r): bool { $s=$this->db->prepare('INSERT INTO repair_history(booking_id,mechanic_id,description,status,cost,repair_date) VALUES(?,?,?,?,?,?)');return$s->execute([$r->bookingId(),$r->mechanicId(),$r->description(),$r->status(),$r->cost(),$r->repairDate()]); }
    public function all(): array { $s=$this->db->query('SELECT repair_id,booking_id,mechanic_id,description,status,cost,repair_date,created_at FROM repair_history ORDER BY created_at DESC');return$s->fetchAll(PDO::FETCH_ASSOC); }
}