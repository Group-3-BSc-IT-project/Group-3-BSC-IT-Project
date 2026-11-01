<?php

require_once __DIR__ . '/Mailer.php';

class BookingMailer
{
    public static function confirmed(BookingRepository $repo, int $bookingId): void
    {
        $d = $repo->emailDetails($bookingId);
        if (!$d) {
            return;
        }

        $service = $d['custom_service'] ?: $d['service_name'];
        $when    = date('D, j M Y', strtotime($d['booking_date'])) . ' at ' . substr($d['booking_time'], 0, 5);

        // In-app
        $repo->notify((int)$d['customer_id'],
            "Booking #{$d['booking_id']} confirmed for $when. Mechanic: " . ($d['mechanic_name'] ?: 'to be assigned') . '.');
        if (!empty($d['mechanic_id'])) {
            $repo->notify((int)$d['mechanic_id'],
                "New job #{$d['booking_id']}: $service on {$d['registration_number']}, $when.");
        }

        // Email
        if (empty($d['email'])) {
            return;
        }
        $body = "Hi {$d['full_name']},\n\nYour booking #{$d['booking_id']} is confirmed.\n\n"
              . "Service:  $service\n"
              . "Vehicle:  {$d['registration_number']} ({$d['make']} {$d['model']})\n"
              . "Date:     " . date('l, j F Y', strtotime($d['booking_date'])) . "\n"
              . "Time:     " . substr($d['booking_time'], 0, 5) . "\n"
              . "Mechanic: " . ($d['mechanic_name'] ?: 'To be assigned') . "\n\n"
              . "Please arrive a few minutes early. To change or cancel, log in here:\n"
              . self::dashboardLink() . "\n\nMSBMS";

        Mailer::send($d['email'], "Booking #{$d['booking_id']} confirmed", $body);
    }

    public static function completed(BookingRepository $repo, int $bookingId): void
{
    $d = $repo->emailDetails($bookingId);
    if (!$d) {
        error_log("completed(): no details for booking $bookingId");
        return;
    }

    $price   = $d['final_price'] !== null ? 'R' . number_format((float)$d['final_price'], 2) : 'see your dashboard';
    $service = $d['custom_service'] ?: $d['service_name'];

    // In-app
    $repo->notify((int)$d['customer_id'],
        "Booking #{$d['booking_id']} is complete. Your vehicle {$d['registration_number']} is ready for collection. Total: $price.");

    // Email
    if (empty($d['email'])) {
        error_log("completed(): booking $bookingId customer has no email");
        return;
    }

    $body = "Hi {$d['full_name']},\n\n"
          . "Good news! The work on your vehicle {$d['registration_number']} ({$d['make']} {$d['model']}) is complete "
          . "and it is ready for collection.\n\n"
          . "Service:     $service\n"
          . "Total price: $price\n\n"
          . "Please come and collect your car during our opening hours and bring this email or your booking number "
          . "(#{$d['booking_id']}).\n\n"
          . "View the details here:\n" . self::dashboardLink() . "\n\nThank you for choosing MSBMS.";

    $sent = Mailer::send($d['email'], "Your vehicle is ready for collection (Booking #{$d['booking_id']})", $body);
    if (!$sent) {
        error_log("completed(): email to {$d['email']} FAILED for booking $bookingId");
    }
}

    private static function dashboardLink(): string
    {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        return $scheme . '://' . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\') . '/customer.php';
    }
}