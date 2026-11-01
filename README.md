# MSBMS: Mechanic Service Booking & Management System

A PHP (OOP) web application for booking and managing vehicle services. Customers book services, mechanics carry out the work and enter the final price, and admins manage everything. Users are kept informed through in-app notifications and email.

## Features

**Customer**
- Register, log in, reset a forgotten password, change password
- Add vehicles and book a service (10 predefined services, or "Other" with a typed description)
- Request a price quote before work starts, then accept or decline it
- Reschedule or cancel bookings
- See the final price, service history and notifications

**Mechanic**
- Sees only bookings assigned to them
- Logs repairs, sends quotes for their own jobs, completes a job with the final price
- Receives notifications and emails for new assignments, reschedules and cancellations

**Admin**
- Sees all bookings, customers and mechanics, with dashboard totals
- Assigns mechanics to bookings
- Sends quotes for any request
- Creates mechanic accounts (the mechanic receives an email to set their own password)

**Notifications**
- In-app notifications with an unread counter
- Emails for: booking confirmed, quote ready, job completed (ready for collection), password reset, mechanic invite

## Technology

- PHP 8.2 or newer
- MySQL / MariaDB (database name `msbms`)
- Apache (XAMPP)
- PDO with prepared statements
- PHPMailer (installed with Composer) for sending email

## Project structure

```
MSBMS_OOP/
├── app/
│   ├── Controllers/        BookingController, RepairController, PasswordResetController, ...
│   ├── Services/           BookingService, RepairService, Mailer, BookingMailer, PasswordPolicy
│   ├── Repositories/       BookingRepository, RepairRepository, NotificationRepository, ...
│   └── config/             mail.php (not committed), mail.example.php
├── database/
│   └── schema.sql          Database structure
├── public/                 Web root: all pages (login.php, customer.php, ...)
│   ├── _init.php           Bootstraps the app and creates the controllers
│   └── assets/             CSS and images
├── vendor/                 Composer packages (not committed)
├── composer.json
└── README.md
```

## Requirements

- [XAMPP](https://www.apachefriends.org/) with Apache and MySQL (PHP 8.2+)
- [Composer](https://getcomposer.org/download/)
- A Gmail account with an app password, or another SMTP service, for sending email
- [Git](https://git-scm.com/download/win) (only if you are cloning the project)

## Installation

### 1. Get the project

```
cd C:\xampp\htdocs
git clone https://github.com/YOUR-USERNAME/MSBMS_OOP.git
```

Or copy the `MSBMS_OOP` folder into `C:\xampp\htdocs\`.

### 2. Start XAMPP

Open the XAMPP Control Panel and start **Apache** and **MySQL**.

### 3. Enable the PHP zip extension (needed by Composer)

1. Open `C:\xampp\php\php.ini` (the "Configuration settings" file named `php`).
2. Find `;extension=zip` and remove the semicolon so it reads `extension=zip`.
3. Make sure `extension=openssl` is also not commented out (needed for sending email).
4. Save, restart Apache, and reopen your terminal.

### 4. Install dependencies

```
cd C:\xampp\htdocs\MSBMS_OOP
composer install
```

If you are setting up from scratch without a `composer.json`, run `composer require phpmailer/phpmailer`.

### 5. Create the database

1. Go to http://localhost/phpmyadmin.
2. Create a database named `msbms` (collation `utf8mb4_general_ci`).
3. Select it, open **Import**, and import `database/schema.sql`.

### 6. Add the extra tables and columns

If your `schema.sql` does not already include them, run this in the **SQL** tab of the `msbms` database.

```sql
-- Password reset tokens
CREATE TABLE IF NOT EXISTS password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token_hash CHAR(64) NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY (token_hash),
    INDEX (user_id)
) ENGINE=InnoDB;

-- Custom services, quotes and final price
ALTER TABLE bookings
    ADD COLUMN custom_service VARCHAR(255) NULL,
    ADD COLUMN quote_status ENUM('None','Requested','Quoted','Accepted','Declined') NOT NULL DEFAULT 'None',
    ADD COLUMN quote_price DECIMAL(10,2) NULL,
    ADD COLUMN final_price DECIMAL(10,2) NULL,
    ADD COLUMN completed_at DATETIME NULL;

-- "Other" service used for custom requests
INSERT INTO services (service_name, price) VALUES ('Other', 0);

-- Notifications
ALTER TABLE notifications
    ADD COLUMN notification_type VARCHAR(50) NOT NULL DEFAULT 'general' AFTER user_id;

-- No duplicate emails
ALTER TABLE users ADD UNIQUE (email);
```

If a statement fails with "Duplicate column" or "Unknown column", that change is already in place, so skip it.

### 7. Configure the database connection

Set the database host, name, user and password where the PDO connection is created (check `public/_init.php` and the `app/` folder for `new PDO`). Default XAMPP values are:

| Setting  | Value       |
|----------|-------------|
| Host     | `localhost` |
| Database | `msbms`     |
| User     | `root`      |
| Password | (empty)     |

`PasswordResetRepository.php` currently opens its own connection with these values. Update it there too if you change them.

### 8. Configure email

1. Turn on 2-Step Verification on the Google account that will send email.
2. Create an app password at https://myaccount.google.com/apppasswords (16 characters, no spaces). Use this, not your normal Gmail password.
3. Copy the example config and fill it in:

```
copy app\config\mail.example.php app\config\mail.php
```

```php
<?php
return [
    'username'  => 'yourname@gmail.com',
    'password'  => 'your16letterapppassword',
    'from'      => 'yourname@gmail.com',
    'from_name' => 'MSBMS',
];
```

`app/config/mail.php` is listed in `.gitignore`, so your password is never uploaded.

### 9. Create the first admin

Register a customer account on `register.php`, then promote it in phpMyAdmin:

```sql
UPDATE users SET role = 'admin' WHERE email = 'your@email.com';
```

Log in as that user. The admin creates mechanics from **Create Mechanic**; each mechanic receives an email link to set their own password.

### 10. Open the app

http://localhost/MSBMS_OOP/public/

## How it works

1. A customer adds a vehicle and creates a booking. They can pick a standard service or "Other" and optionally request a quote.
2. If a quote was requested, the admin or the assigned mechanic enters a price, and the customer accepts or declines.
3. The admin assigns a mechanic, and the booking becomes **Confirmed**. The customer and mechanic are notified.
4. The mechanic logs repairs under **Add Repair**.
5. The mechanic presses **Complete Job**, enters the final price (pre-filled from repair costs or the accepted quote), and the booking is **Completed**.
6. The customer receives an in-app notification and an email saying the vehicle is ready for collection.

## Password policy

Passwords must be 8 to 72 characters with an uppercase letter, a lowercase letter, a number and a special character. Common passwords and passwords containing the email name are rejected. The rules are enforced on the server in `app/Services/PasswordPolicy.php`.

## Security notes

- All database access uses PDO prepared statements.
- Output is escaped with `e()`.
- Passwords are stored with `password_hash()`.
- Reset tokens are random, stored only as SHA-256 hashes, single-use and expire after 60 minutes (48 hours for mechanic invites).
- Pages check the user's role on the server with `Auth::requireRole()`, and queries filter by owner.
- Before going live:
  - Create a MySQL user with only SELECT, INSERT, UPDATE and DELETE on `msbms`, and stop using `root`.
  - Set a password on the MySQL `root` account.
  - Use HTTPS and set `display_errors = Off` in `php.ini`.
  - Add CSRF tokens to forms and limit login attempts.
  - Back up the database regularly with `mysqldump`.

## Troubleshooting

| Problem | Fix |
|---|---|
| `composer` is not recognised | Install Composer, then reopen the terminal |
| Composer: "zip extension missing" | Enable `extension=zip` in `C:\xampp\php\php.ini` |
| MySQL error 1932 "doesn't exist in engine" | InnoDB lost the table. Run `DROP TABLE IF EXISTS tablename;`, then recreate it |
| `Unknown column ...` | The table is missing a column from step 6. Compare with `SHOW COLUMNS FROM tablename;` |
| Email: "Username and Password not accepted" | Use a Gmail **app password**, not your normal password |
| Email: "Could not connect to SMTP host" | Enable `extension=openssl`, restart Apache, check that your firewall allows port 587 |
| Reset link says expired immediately | PHP and MySQL clocks differ. Set `date_default_timezone_set('Africa/Johannesburg');` in `_init.php` |
| Reset or job pages give 404 | Check the file exists in `public/` with the exact name |
| No emails arrive | Check spam, confirm `users.email` is a real inbox, and read `C:\xampp\apache\logs\error.log` for `Mail error:` |

## Development tips

- Pass `true` as the second argument of `new PasswordResetController(new PasswordResetRepository(), true)` to show reset links on screen instead of emailing them (testing only).
- Never commit `app/config/mail.php`, `vendor/`, or any file containing passwords.
- Delete test files such as `test_mail.php` when you are done.

## Updating and pushing to GitHub

```
git add .
git commit -m "Describe what changed"
git push
```

## Licence

For educational use. Add your preferred licence here.
