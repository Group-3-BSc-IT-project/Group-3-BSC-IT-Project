<?php
class UserRepository
{
    public function __construct(private PDO $db) {}
    public function findByEmail(string $email): ?array {
        $s=$this->db->prepare('SELECT * FROM users WHERE email=? LIMIT 1'); $s->execute([$email]); return $s->fetch() ?: null;
    }
    public function emailExists(string $email): bool { return $this->findByEmail($email)!==null; }
    public function create(string $name,string $email,string $phone,string $address,string $hash,string $role): bool {
        $s=$this->db->prepare('INSERT INTO users(full_name,email,phone,address,password,role) VALUES(?,?,?,?,?,?)'); return $s->execute([$name,$email,$phone?:null,$address?:null,$hash,$role]);
    }
    public function findCustomers(): array { $s=$this->db->query("SELECT user_id,full_name FROM users WHERE role='customer' ORDER BY full_name"); return $s->fetchAll(); }
    public function findMechanics(): array { $s=$this->db->query("SELECT user_id,full_name,phone FROM users WHERE role='mechanic' ORDER BY full_name"); return $s->fetchAll(); }
    public function isMechanic(int $id): bool { $s=$this->db->prepare("SELECT user_id FROM users WHERE user_id=? AND role='mechanic'"); $s->execute([$id]); return (bool)$s->fetch(); }

    /**
     * Find a single user by their user_id.
     */
    public function findById(int $userId): ?array {
        $s = $this->db->prepare('SELECT * FROM users WHERE user_id = ? LIMIT 1');
        $s->execute([$userId]);
        return $s->fetch() ?: null;
    }

    /**
     * Update one or more columns for a user.
     * $data is an associative array of column => value,
     * e.g. ['full_name' => 'Jane Doe', 'email' => 'jane@example.com'].
     */
    public function update(int $userId, array $data): bool {
        if (empty($data)) {
            return true;
        }

        $allowed = ['full_name', 'email', 'phone', 'address', 'password', 'role'];
        $fields  = [];
        $values  = [];

        foreach ($data as $column => $value) {
            if (!in_array($column, $allowed, true)) {
                continue; // ignore unexpected columns for safety
            }
            $fields[] = "$column = ?";
            $values[] = $value;
        }

        if (empty($fields)) {
            return true;
        }

        $values[] = $userId;

        $sql = 'UPDATE users SET ' . implode(', ', $fields) . ' WHERE user_id = ?';
        $s   = $this->db->prepare($sql);
        return $s->execute($values);
    }
}