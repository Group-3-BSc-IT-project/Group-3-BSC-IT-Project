<?php
class BookingRepository
{
    public function __construct(private PDO $db) {}

    public function slotTaken(string $date, string $time, ?int $ignoreId = null): bool
    {
        $sql = "SELECT booking_id FROM bookings WHERE booking_date=? AND booking_time=? AND status IN ('Pending','Confirmed')";
        $args = [$date, $time];
        if ($ignoreId) { $sql .= ' AND booking_id <> ?'; $args[] = $ignoreId; }
        $s = $this->db->prepare($sql);
        $s->execute($args);
        return (bool)$s->fetch();
    }

    public function create(Booking $b): bool
    {
        $s = $this->db->prepare('INSERT INTO bookings(customer_id,vehicle_id,service_id,booking_date,booking_time,status,notes) VALUES(?,?,?,?,?,?,?)');
        return $s->execute([$b->customerId(), $b->vehicleId(), $b->serviceId(), $b->date(), $b->time(), $b->status(), $b->notes()]);
    }

    public function cancel(int $id, int $customerId): bool
    {
        $s = $this->db->prepare("UPDATE bookings SET status='Canceled' WHERE booking_id=? AND customer_id=? AND status IN ('Pending','Confirmed')");
        $s->execute([$id, $customerId]);
        return $s->rowCount() > 0;
    }

    public function findForCustomer(int $id): array
    {
        $s = $this->db->prepare("SELECT b.*,v.registration_number,v.make,v.model,s.service_name,s.price,m.full_name mechanic_name FROM bookings b JOIN vehicles v ON b.vehicle_id=v.vehicle_id JOIN services s ON b.service_id=s.service_id LEFT JOIN users m ON b.mechanic_id=m.user_id WHERE b.customer_id=? AND b.status <> 'Completed' ORDER BY b.booking_date DESC,b.booking_time DESC");
        $s->execute([$id]);
        return $s->fetchAll();
    }

    public function completedForCustomer(int $customerId): array
    {
        $s = $this->db->prepare("SELECT b.booking_id, COALESCE(DATE_FORMAT(b.completed_at, '%Y-%m-%d'), b.booking_date) AS repair_date,
                v.registration_number, v.make, v.model,
                COALESCE(NULLIF(b.custom_service, ''), s.service_name) AS service_name,
                COALESCE((SELECT GROUP_CONCAT(r.description ORDER BY r.created_at SEPARATOR '; ')
                    FROM repairs r WHERE r.booking_id=b.booking_id), 'Service completed') AS description,
                b.status, COALESCE(b.final_price, (SELECT SUM(r.cost) FROM repairs r WHERE r.booking_id=b.booking_id)) AS cost
            FROM bookings b
            JOIN vehicles v ON b.vehicle_id=v.vehicle_id
            JOIN services s ON b.service_id=s.service_id
            WHERE b.customer_id=? AND b.status='Completed'
            ORDER BY b.completed_at DESC, b.booking_date DESC, b.booking_time DESC");
        $s->execute([$customerId]);
        return $s->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findAll(): array
    {
        return $this->db->query("SELECT b.*,c.full_name customer_name,v.registration_number,v.make,v.model,s.service_name,m.full_name mechanic_name FROM bookings b JOIN users c ON b.customer_id=c.user_id JOIN vehicles v ON b.vehicle_id=v.vehicle_id JOIN services s ON b.service_id=s.service_id LEFT JOIN users m ON b.mechanic_id=m.user_id ORDER BY b.booking_date DESC,b.booking_time DESC")->fetchAll();
    }

    public function forAdminDashboard(): array
    {
        return $this->db->query("SELECT b.*,c.full_name customer_name,v.registration_number,v.make,v.model,s.service_name,m.full_name mechanic_name FROM bookings b JOIN users c ON b.customer_id=c.user_id JOIN vehicles v ON b.vehicle_id=v.vehicle_id JOIN services s ON b.service_id=s.service_id LEFT JOIN users m ON b.mechanic_id=m.user_id WHERE b.status <> 'Completed' ORDER BY b.booking_date DESC,b.booking_time DESC")->fetchAll();
    }

    public function find(int $id): ?array
    {
        $s = $this->db->prepare("SELECT b.*,c.full_name customer_name,v.registration_number,v.make,v.model,s.service_name FROM bookings b JOIN users c ON b.customer_id=c.user_id JOIN vehicles v ON b.vehicle_id=v.vehicle_id JOIN services s ON b.service_id=s.service_id WHERE b.booking_id=?");
        $s->execute([$id]);
        return $s->fetch() ?: null;
    }

    public function findForCustomerById(int $id, int $customerId): ?array
    {
        $s = $this->db->prepare('SELECT * FROM bookings WHERE booking_id=? AND customer_id=?');
        $s->execute([$id, $customerId]);
        return $s->fetch() ?: null;
    }

    public function reschedule(int $id, int $customerId, string $date, string $time): bool
    {
        $s = $this->db->prepare("UPDATE bookings SET booking_date=?,booking_time=?,status='Pending' WHERE booking_id=? AND customer_id=? AND status <> 'Canceled'");
        $s->execute([$date, $time, $id, $customerId]);
        return $s->rowCount() > 0;
    }

    public function assignMechanic(int $bookingId, int $mechanicId): bool
    {
        $s = $this->db->prepare("UPDATE bookings SET mechanic_id=?,status='Confirmed' WHERE booking_id=?");
        $s->execute([$mechanicId, $bookingId]);
        return $s->rowCount() > 0;
    }

    public function forMechanic(int $id): array
    {
        $s = $this->db->prepare("SELECT b.*,c.full_name customer_name,v.registration_number,v.make,v.model,s.service_name,s.price FROM bookings b JOIN users c ON b.customer_id=c.user_id JOIN vehicles v ON b.vehicle_id=v.vehicle_id JOIN services s ON b.service_id=s.service_id WHERE b.mechanic_id=? ORDER BY b.booking_date,b.booking_time");
        $s->execute([$id]);
        return $s->fetchAll();
    }

    public function forMechanicDashboard(int $id): array
    {
        $s = $this->db->prepare("SELECT b.*,c.full_name customer_name,v.registration_number,v.make,v.model,s.service_name,s.price FROM bookings b JOIN users c ON b.customer_id=c.user_id JOIN vehicles v ON b.vehicle_id=v.vehicle_id JOIN services s ON b.service_id=s.service_id WHERE b.mechanic_id=? AND b.status <> 'Completed' ORDER BY b.booking_date,b.booking_time");
        $s->execute([$id]);
        return $s->fetchAll();
    }

    public function counts(): array
    {
        $q = fn($sql) => (int)$this->db->query($sql)->fetch()['total'];
        return [
            'customers' => $q("SELECT COUNT(*) total FROM users WHERE role='customer'"),
            'mechanics' => $q("SELECT COUNT(*) total FROM users WHERE role='mechanic'"),
            'vehicles'  => $q('SELECT COUNT(*) total FROM vehicles'),
            'bookings'  => $q('SELECT COUNT(*) total FROM bookings'),
            'pending'   => $q("SELECT COUNT(*) total FROM bookings WHERE status='Pending'"),
            'completed' => $q("SELECT COUNT(*) total FROM bookings WHERE status='Completed'"),
        ];
    }

    // Old method (no price). Kept so nothing that calls it breaks.
    public function complete(int $id, int $mechanicId): bool
    {
        $s = $this->db->prepare("UPDATE bookings SET status='Completed' WHERE booking_id=? AND mechanic_id=?");
        $s->execute([$id, $mechanicId]);
        return $s->rowCount() > 0;
    }

    // ---- Custom service, quotes, final price ----

    public function otherServiceId(): ?int
    {
        $r = $this->db->query("SELECT service_id FROM services WHERE service_name='Other' LIMIT 1")->fetch();
        return $r ? (int)$r['service_id'] : null;
    }

    public function vehicleBelongsTo(int $vehicleId, int $customerId): bool
    {
        $s = $this->db->prepare('SELECT 1 FROM vehicles WHERE vehicle_id=? AND customer_id=?');
        $s->execute([$vehicleId, $customerId]);
        return (bool)$s->fetch();
    }

    public function createExtended(int $customerId, int $vehicleId, int $serviceId, string $date, string $time, string $notes, ?string $custom, bool $wantQuote): bool
    {
        $s = $this->db->prepare("INSERT INTO bookings (customer_id,vehicle_id,service_id,booking_date,booking_time,status,notes,custom_service,quote_status) VALUES (?,?,?,?,?,'Pending',?,?,?)");
        return $s->execute([$customerId, $vehicleId, $serviceId, $date, $time, $notes, $custom, $wantQuote ? 'Requested' : 'None']);
    }

    public function quoteRequests(?int $mechanicId): array
    {
        $sql = "SELECT b.booking_id,b.booking_date,b.booking_time,b.notes,b.custom_service,
                       c.full_name customer_name,v.registration_number,v.make,v.model,s.service_name,s.price
                FROM bookings b
                JOIN users c ON b.customer_id=c.user_id
                JOIN vehicles v ON b.vehicle_id=v.vehicle_id
                JOIN services s ON b.service_id=s.service_id
                WHERE b.quote_status='Requested' AND b.status NOT IN ('Canceled','Completed')";
        $args = [];
        if ($mechanicId !== null) { $sql .= ' AND b.mechanic_id=?'; $args[] = $mechanicId; }
        $s = $this->db->prepare($sql . ' ORDER BY b.booking_date, b.booking_time');
        $s->execute($args);
        return $s->fetchAll();
    }

    public function setQuote(int $id, float $price, ?int $mechanicId): bool
    {
        $sql = "UPDATE bookings SET quote_price=?, quote_status='Quoted' WHERE booking_id=? AND quote_status='Requested'";
        $args = [$price, $id];
        if ($mechanicId !== null) { $sql .= ' AND mechanic_id=?'; $args[] = $mechanicId; }
        $s = $this->db->prepare($sql);
        $s->execute($args);
        return $s->rowCount() > 0;
    }

    public function respondToQuote(int $id, int $customerId, bool $accept): bool
    {
        $sql = $accept
            ? "UPDATE bookings SET quote_status='Accepted' WHERE booking_id=? AND customer_id=? AND quote_status='Quoted'"
            : "UPDATE bookings SET quote_status='Declined', status='Canceled' WHERE booking_id=? AND customer_id=? AND quote_status='Quoted'";
        $s = $this->db->prepare($sql);
        $s->execute([$id, $customerId]);
        return $s->rowCount() > 0;
    }

    public function findForMechanicById(int $id, int $mechanicId): ?array
    {
        $s = $this->db->prepare("SELECT b.*, s.service_name, s.price AS quoted_price FROM bookings b JOIN services s ON b.service_id=s.service_id WHERE b.booking_id=? AND b.mechanic_id=?");
        $s->execute([$id, $mechanicId]);
        return $s->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function completeWithPrice(int $id, int $mechanicId, float $price): bool
    {
        $this->db->beginTransaction();

        try {
            $booking = $this->db->prepare("UPDATE bookings SET status='Completed', final_price=?, completed_at=NOW() WHERE booking_id=? AND mechanic_id=? AND status NOT IN ('Completed','Canceled')");
            $booking->execute([$price, $id, $mechanicId]);

            if ($booking->rowCount() === 0) {
                $this->db->rollBack();
                return false;
            }

            $repairs = $this->db->prepare("UPDATE repairs SET status='Completed' WHERE booking_id=? AND status <> 'Completed'");
            $repairs->execute([$id]);

            $this->db->commit();
            return true;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    // ---- Notifications and email data ----

    public function notify(int $userId, string $message, string $type = 'booking'): void
    {
        try {
            $s = $this->db->prepare("INSERT INTO notifications (user_id, notification_type, message) VALUES (?,?,?)");
            $s->execute([$userId, $type, $message]);
        } catch (PDOException $e) {
            error_log('Notify error: ' . $e->getMessage());
        }
    }

    public function customerContact(int $bookingId): ?array
    {
        $s = $this->db->prepare("SELECT b.customer_id, u.email, u.full_name, s.service_name, b.custom_service FROM bookings b JOIN users u ON b.customer_id=u.user_id JOIN services s ON b.service_id=s.service_id WHERE b.booking_id=?");
        $s->execute([$bookingId]);
        return $s->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function emailDetails(int $id): ?array
    {
        $s = $this->db->prepare("SELECT b.booking_id, b.customer_id, b.mechanic_id, b.booking_date, b.booking_time, b.custom_service, b.final_price,
                u.email, u.full_name, s.service_name, v.registration_number, v.make, v.model, m.full_name mechanic_name
            FROM bookings b
            JOIN users u ON b.customer_id=u.user_id
            JOIN services s ON b.service_id=s.service_id
            JOIN vehicles v ON b.vehicle_id=v.vehicle_id
            LEFT JOIN users m ON b.mechanic_id=m.user_id
            WHERE b.booking_id=?");
        $s->execute([$id]);
        return $s->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function findForMechanicDetail(int $id, int $mechanicId): ?array
{
    $s = $this->db->prepare("SELECT b.*, c.full_name customer_name, v.registration_number, v.make, v.model, s.service_name
        FROM bookings b
        JOIN users c ON b.customer_id=c.user_id
        JOIN vehicles v ON b.vehicle_id=v.vehicle_id
        JOIN services s ON b.service_id=s.service_id
        WHERE b.booking_id=? AND b.mechanic_id=?");
    $s->execute([$id, $mechanicId]);
    return $s->fetch(PDO::FETCH_ASSOC) ?: null;
}
}