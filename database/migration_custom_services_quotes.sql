USE msbms;

ALTER TABLE bookings
    ADD COLUMN custom_service VARCHAR(255) NULL,
    ADD COLUMN quote_status ENUM('None','Requested','Quoted','Accepted','Declined') NOT NULL DEFAULT 'None',
    ADD COLUMN quote_price DECIMAL(10,2) NULL,
    ADD COLUMN final_price DECIMAL(10,2) NULL,
    ADD COLUMN completed_at DATETIME NULL;

CREATE TABLE IF NOT EXISTS password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token_hash CHAR(64) NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_token (token_hash),
    INDEX idx_user (user_id),
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT INTO services (service_name, description, price)
SELECT 'Other', 'Custom request described by the customer (quote required)', 0
WHERE NOT EXISTS (
    SELECT 1 FROM services WHERE service_name = 'Other'
);