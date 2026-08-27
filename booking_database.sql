PRAGMA foreign_keys = ON;

BEGIN TRANSACTION;

DROP VIEW IF EXISTS available_spaces;
DROP TABLE IF EXISTS bookings;
DROP TABLE IF EXISTS customers;
DROP TABLE IF EXISTS spaces;

CREATE TABLE spaces (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL UNIQUE,
    description TEXT,
    capacity INTEGER NOT NULL CHECK (capacity > 0),
    hourly_rate_cents INTEGER NOT NULL DEFAULT 0 CHECK (hourly_rate_cents >= 0),
    active INTEGER NOT NULL DEFAULT 1 CHECK (active IN (0, 1)),
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE customers (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    first_name TEXT NOT NULL,
    last_name TEXT NOT NULL,
    email TEXT NOT NULL UNIQUE,
    phone TEXT,
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE bookings (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    space_id INTEGER NOT NULL,
    customer_id INTEGER NOT NULL,
    starts_at TEXT NOT NULL,
    ends_at TEXT NOT NULL,
    status TEXT NOT NULL DEFAULT 'confirmed'
        CHECK (status IN ('held', 'confirmed', 'cancelled', 'expired')),
    hold_expires_at TEXT,
    notes TEXT,
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CHECK (datetime(ends_at) > datetime(starts_at)),
    FOREIGN KEY (space_id) REFERENCES spaces(id) ON DELETE RESTRICT,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE RESTRICT
);

CREATE INDEX idx_bookings_space_time
ON bookings(space_id, starts_at, ends_at);

CREATE INDEX idx_bookings_status
ON bookings(status);

CREATE INDEX idx_bookings_customer
ON bookings(customer_id);

CREATE TRIGGER bookings_updated_at
AFTER UPDATE ON bookings
FOR EACH ROW
BEGIN
    UPDATE bookings
    SET updated_at = CURRENT_TIMESTAMP
    WHERE id = OLD.id;
END;


  A space is available when it is active and has no overlapping
  confirmed booking or unexpired hold.
*/
CREATE VIEW available_spaces AS
SELECT
    s.id,
    s.name,
    s.description,
    s.capacity,
    s.hourly_rate_cents
FROM spaces AS s
WHERE s.active = 1;

 Sample spaces */
INSERT INTO spaces (name, description, capacity, hourly_rate_cents) VALUES
('Space A', 'Quiet workspace with natural light', 4, 2500),
('Space B', 'Meeting room with display screen', 8, 4000),
('Space C', 'Large room suitable for workshops', 20, 7500),
('Space D', 'Private office space', 2, 1800);

 Sample customers */
INSERT INTO customers (first_name, last_name, email, phone) VALUES
('Jane', 'Smith', 'jane.smith@example.com', '+27 82 000 0001'),
('John', 'Williams', 'john.williams@example.com', '+27 82 000 0002');

 Sample bookings */
INSERT INTO bookings (
    space_id,
    customer_id,
    starts_at,
    ends_at,
    status,
    notes
) VALUES
(
    (SELECT id FROM spaces WHERE name = 'Space A'),
    (SELECT id FROM customers WHERE email = 'jane.smith@example.com'),
    '2026-08-28 09:00:00',
    '2026-08-28 10:00:00',
    'confirmed',
    'Sample confirmed booking'
),
(
    (SELECT id FROM spaces WHERE name = 'Space B'),
    (SELECT id FROM customers WHERE email = 'john.williams@example.com'),
    '2026-08-28 11:00:00',
    '2026-08-28 12:30:00',
    'confirmed',
    'Sample confirmed booking'
);

COMMIT;


  Availability query example.
  Replace the two datetime values with the requested period.

  SELECT s.*
  FROM spaces AS s
  WHERE s.active = 1
    AND NOT EXISTS (
      SELECT 1
      FROM bookings AS b
      WHERE b.space_id = s.id
        AND b.status IN ('held', 'confirmed')
        AND (b.status <> 'held' OR datetime(b.hold_expires_at) > CURRENT_TIMESTAMP)
        AND datetime(b.starts_at) < datetime('2026-08-28 10:00:00')
        AND datetime(b.ends_at) > datetime('2026-08-28 09:00:00')
    );


  Safe booking insert pattern.
  The application should execute this inside a transaction and verify
  that the INSERT succeeds before confirming the booking.

  INSERT INTO bookings (
      space_id, customer_id, starts_at, ends_at, status, hold_expires_at
  )
  SELECT
      :space_id,
      :customer_id,
      :starts_at,
      :ends_at,
      'held',
      datetime('now', '+5 minutes')
  WHERE NOT EXISTS (
      SELECT 1
      FROM bookings
      WHERE space_id = :space_id
        AND status IN ('held', 'confirmed')
        AND (status <> 'held' OR datetime(hold_expires_at) > CURRENT_TIMESTAMP)
        AND datetime(starts_at) < datetime(:ends_at)
        AND datetime(ends_at) > datetime(:starts_at)
  );
*/
