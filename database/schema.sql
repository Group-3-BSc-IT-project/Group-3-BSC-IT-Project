-- MSBMS database schema (fresh install)
-- WARNING: this drops and recreates every table. Do NOT run it on a database
-- that holds data you want to keep. Use migration.sql for that.

CREATE DATABASE IF NOT EXISTS msbms CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE msbms;

DROP TABLE IF EXISTS password_resets, payments, notifications, repairs, bookings, vehicles, services, users;

CREATE TABLE users (
    user_id    INT AUTO_INCREMENT PRIMARY KEY,
    full_name  VARCHAR(150) NOT NULL,
    email      VARCHAR(150) NOT NULL UNIQUE,
    password   VARCHAR(255) NOT NULL,
    role       ENUM('customer','mechanic','admin') NOT NULL,
    phone      VARCHAR(30),
    address    VARCHAR(255),
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE services (
    service_id   INT AUTO_INCREMENT PRIMARY KEY,
    service_name VARCHAR(150) NOT NULL,
    description  TEXT,
    price        DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE vehicles (
    vehicle_id          INT AUTO_INCREMENT PRIMARY KEY,
    customer_id         INT NOT NULL,
    registration_number VARCHAR(50) NOT NULL UNIQUE,
    make                VARCHAR(100) NOT NULL,
    model               VARCHAR(100) NOT NULL,
    year                INT NULL,
    vin                 VARCHAR(100) UNIQUE NULL,
    created_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE bookings (
    booking_id     INT AUTO_INCREMENT PRIMARY KEY,
    customer_id    INT NOT NULL,
    vehicle_id     INT NOT NULL,
    service_id     INT NOT NULL,
    mechanic_id    INT NULL,
    booking_date   DATE NOT NULL,
    booking_time   TIME NOT NULL,
    status         ENUM('Pending','Confirmed','Canceled','Completed') NOT NULL DEFAULT 'Pending',
    notes          TEXT,
    custom_service VARCHAR(255) NULL,                 -- what the customer typed for "Other"
    quote_status   ENUM('None','Requested','Quoted','Accepted','Declined') NOT NULL DEFAULT 'None',
    quote_price    DECIMAL(10,2) NULL,
    final_price    DECIMAL(10,2) NULL,                -- entered by the mechanic at the end
    completed_at   DATETIME NULL,
    created_at     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_slot (booking_date, booking_time, status),
    FOREIGN KEY (customer_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (vehicle_id)  REFERENCES vehicles(vehicle_id) ON DELETE CASCADE,
    FOREIGN KEY (service_id)  REFERENCES services(service_id),
    FOREIGN KEY (mechanic_id) REFERENCES users(user_id)
) ENGINE=InnoDB;

CREATE TABLE repairs (
    repair_id   INT AUTO_INCREMENT PRIMARY KEY,
    booking_id  INT NOT NULL,
    mechanic_id INT NOT NULL,
    description TEXT NOT NULL,
    status      ENUM('Pending','In Progress','Completed') NOT NULL DEFAULT 'Pending',
    cost        DECIMAL(10,2) DEFAULT 0.00,
    repair_date DATE NULL,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id)  REFERENCES bookings(booking_id) ON DELETE CASCADE,
    FOREIGN KEY (mechanic_id) REFERENCES users(user_id)
) ENGINE=InnoDB;

CREATE TABLE notifications (
    notification_id   INT AUTO_INCREMENT PRIMARY KEY,
    user_id           INT NOT NULL,
    message           TEXT NOT NULL,
    notification_type VARCHAR(50) NOT NULL DEFAULT 'general',
    is_read           TINYINT(1) NOT NULL DEFAULT 0,
    created_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_read (user_id, is_read),
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Single-use tokens for "forgot password" and mechanic invites (stored hashed)
CREATE TABLE password_resets (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT NOT NULL,
    token_hash CHAR(64) NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_token (token_hash),
    INDEX idx_user (user_id),
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Not used by the app yet
CREATE TABLE payments (
    payment_id     INT AUTO_INCREMENT PRIMARY KEY,
    booking_id     INT NOT NULL,
    amount         DECIMAL(10,2) NOT NULL,
    payment_method ENUM('Cash','Card','Online') NOT NULL DEFAULT 'Cash',
    status         ENUM('Pending','Paid','Refunded') NOT NULL DEFAULT 'Pending',
    payment_date   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(booking_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------
-- Sample data (for development only)
-- All sample accounts share one password hash. Change them before real use,
-- or reset them with "Forgot password".
-- ---------------------------------------------------------------
INSERT INTO users (full_name, email, password, role, phone, address) VALUES
('System Administrator', 'admin@msbms.com',    '$2y$10$ZaPVagvCjpdtW5S1vFlmfeodfAHrHCFmhB2L5Bo8QNHxux/Rvjhs.', 'admin',    '0710000000', 'Durban'),
('Matt Armstrong',       'mechanic@msbms.com', '$2y$10$ZaPVagvCjpdtW5S1vFlmfeodfAHrHCFmhB2L5Bo8QNHxux/Rvjhs.', 'mechanic', '0720000000', 'Durban'),
('Lionel Messi',         'customer@msbms.com', '$2y$10$ZaPVagvCjpdtW5S1vFlmfeodfAHrHCFmhB2L5Bo8QNHxux/Rvjhs.', 'customer', '0730000000', 'Durban'),
('Lewis Hamilton',       'sarah@msbms.com',    '$2y$10$ZaPVagvCjpdtW5S1vFlmfeodfAHrHCFmhB2L5Bo8QNHxux/Rvjhs.', 'customer', '0740000000', 'Durban');

INSERT INTO services (service_name, description, price) VALUES
('Oil Change',              'Engine oil and oil filter replacement',            650),
('Brake Service',           'Brake inspection and brake pad replacement',      1200),
('Engine Diagnostic',       'Full engine computer diagnostic',                  500),
('Wheel Alignment',         'Four-wheel alignment and adjustment',              450),
('Battery Replacement',     'Vehicle battery testing and replacement',         1500),
('Full Service',            'Complete vehicle service and inspection',         2500),
('Air Conditioning Service','Vehicle air conditioning inspection and recharge', 900),
('Tyre Replacement',        'Tyre inspection and replacement',                 2000),
('Other',                   'Custom request described by the customer (quote required)', 0);

INSERT INTO vehicles (customer_id, registration_number, make, model, year, vin) VALUES
(3, 'ND123456', 'Toyota', 'Etios',  2017, 'AHT123456789001'),
(3, 'ND654321', 'Ford',   'Ranger', 2015, 'AHT123456789002'),
(4, 'ND789012', 'BMW',    '318i',   1995, 'AHT123456789003');

INSERT INTO bookings (customer_id, vehicle_id, service_id, mechanic_id, booking_date, booking_time, status, notes) VALUES
(3, 1, 1, NULL, DATE_ADD(CURDATE(), INTERVAL 2 DAY), '09:00:00', 'Pending',   'Regular oil change'),
(3, 2, 6, 2,    DATE_ADD(CURDATE(), INTERVAL 3 DAY), '10:00:00', 'Confirmed', 'Full vehicle service'),
(4, 3, 3, 2,    DATE_ADD(CURDATE(), INTERVAL 4 DAY), '11:00:00', 'Confirmed', 'Engine diagnostic required');

INSERT INTO repairs (booking_id, mechanic_id, description, status, cost, repair_date) VALUES
(2, 2, 'Vehicle inspection completed. Oil and filters checked.', 'In Progress', 2500, NULL);

INSERT INTO notifications (user_id, message, notification_type, is_read) VALUES
(3, 'Your booking has been created successfully.',       'booking', 0),
(3, 'Your vehicle service booking has been confirmed.',  'booking', 0),
(4, 'Your engine diagnostic booking has been confirmed.','booking', 0),
(2, 'You have been assigned a new mechanic job.',        'booking', 0);

INSERT INTO payments (booking_id, amount, payment_method, status) VALUES (2, 2500, 'Cash', 'Pending');
