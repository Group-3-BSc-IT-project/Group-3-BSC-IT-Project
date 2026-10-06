<?php
class ServiceRepository
{
    public function __construct(private PDO $db) {}
    public function all(): array { return $this->db->query('SELECT * FROM services ORDER BY service_name')->fetchAll(); }
    public function exists(int $id): bool { $s=$this->db->prepare('SELECT service_id FROM services WHERE service_id=?'); $s->execute([$id]); return (bool)$s->fetch(); }
}
