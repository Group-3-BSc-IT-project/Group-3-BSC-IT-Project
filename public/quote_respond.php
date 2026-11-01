<?php
require_once __DIR__ . '/_init.php';
Auth::requireRole('customer');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bookingController->respondToQuote(
        (int)($_POST['booking_id'] ?? 0),
        ($_POST['decision'] ?? '') === 'accept'
    );
}

header('Location: customer.php');
exit;