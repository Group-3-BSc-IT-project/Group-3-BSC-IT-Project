<?php
require_once __DIR__ . '/../Services/Mailer.php';
require_once __DIR__ . '/../Services/BookingMailer.php';

class BookingController
{
    public function __construct(
        private BookingService $service,
        private BookingRepository $bookings,
        private VehicleRepository $vehicles,
        private ServiceRepository $services,
        private UserRepository $users
    ) {
    }

    // ---- Actions ----

    public function create(): array
    {
        return $this->service->create(
            Session::userId(),
            (int)$_POST['vehicle_id'],
            (int)$_POST['service_id'],
            $_POST['booking_date'] ?? '',
            $_POST['booking_time'] ?? '',
            trim($_POST['notes'] ?? '')
        );
    }

    public function cancel(int $id): bool
    {
        return $this->bookings->cancel($id, Session::userId());
    }

    public function reschedule(int $id): array
    {
        return $this->service->reschedule(
            $id,
            Session::userId(),
            $_POST['booking_date'] ?? '',
            $_POST['booking_time'] ?? ''
        );
    }

    public function assign(int $id): array
    {
        $result = $this->service->assign($id, (int)$_POST['mechanic_id']);

        if (!empty($result[0])) {
            BookingMailer::confirmed($this->bookings, $id);
        }

        return $result;
    }

    // Old completion without a price. Prefer completeJob().
    public function complete(int $id): bool
    {
        $ok = $this->bookings->complete($id, Session::userId());
        if ($ok) {
            BookingMailer::completed($this->bookings, $id);
        }
        return $ok;
    }

    // ---- Queries ----

    public function customerBookings(): array
    {
        return $this->bookings->findForCustomer(Session::userId());
    }

    public function mechanicBookings(): array
    {
        return $this->bookings->forMechanic(Session::userId());
    }

    public function all(): array
    {
        return $this->bookings->findAll();
    }

    public function find(int $id): ?array
    {
        return $this->bookings->find($id);
    }

    public function customerFind(int $id): ?array
    {
        return $this->bookings->findForCustomerById($id, Session::userId());
    }

    // ---- Lookups for forms ----

    public function vehicles(): array
    {
        return $this->vehicles->forBooking(Session::userId());
    }

    public function services(): array
    {
        return $this->services->all();
    }

    public function mechanics(): array
    {
        return $this->users->findMechanics();
    }

    // ---- Custom service, quotes and final price ----

    public function createCustom(): array
    {
        $customerId = (int)Session::userId();
        $vehicleId  = (int)($_POST['vehicle_id'] ?? 0);
        $choice     = $_POST['service_id'] ?? '';
        $date       = $_POST['booking_date'] ?? '';
        $time       = $_POST['booking_time'] ?? '';
        $notes      = trim($_POST['notes'] ?? '');
        $custom     = trim($_POST['custom_service'] ?? '');
        $wantQuote  = !empty($_POST['want_quote']);

        if (!$this->bookings->vehicleBelongsTo($vehicleId, $customerId)) {
            return [false, 'Please select one of your vehicles.'];
        }

        if ($choice === 'other') {
            if (mb_strlen($custom) < 5) {
                return [false, 'Please describe the service you need.'];
            }
            $serviceId = $this->bookings->otherServiceId();
            if (!$serviceId) {
                return [false, 'Custom services are not set up yet.'];
            }
        } else {
            $serviceId = (int)$choice;
            $custom = '';
            if ($serviceId <= 0) {
                return [false, 'Please select a service.'];
            }
        }

        $d = DateTime::createFromFormat('Y-m-d', $date);
        if (!$d || $d->format('Y-m-d') !== $date || $date < date('Y-m-d')) {
            return [false, 'Choose a valid date that is not in the past.'];
        }
        if (!preg_match('/^\d{2}:\d{2}$/', $time)) {
            return [false, 'Choose a valid time.'];
        }
        if ($this->bookings->slotTaken($date, $time)) {
            return [false, 'That time slot is already taken. Please choose another.'];
        }

        $ok = $this->bookings->createExtended(
            $customerId, $vehicleId, $serviceId, $date, $time, $notes,
            $custom !== '' ? $custom : null, $wantQuote
        );

        return $ok ? [true, 'Booking created.'] : [false, 'Could not create the booking.'];
    }

    public function quoteRequests(): array
    {
        $mechanicId = strtolower((string)Session::role()) === 'mechanic' ? (int)Session::userId() : null;
        return $this->bookings->quoteRequests($mechanicId);
    }

    public function submitQuote(int $bookingId): array
    {
        $raw = trim($_POST['quote_price'] ?? '');
        if (!is_numeric($raw) || (float)$raw <= 0) {
            return [false, 'Enter a valid quote amount.'];
        }
        $mechanicId = strtolower((string)Session::role()) === 'mechanic' ? (int)Session::userId() : null;
        $price = round((float)$raw, 2);
        $ok = $this->bookings->setQuote($bookingId, $price, $mechanicId);

        if ($ok) {
            $c = $this->bookings->customerContact($bookingId);
            if ($c) {
                $service = $c['custom_service'] ?: $c['service_name'];

                // In-app
                $this->bookings->notify((int)$c['customer_id'],
                    "Your quote for \"$service\" is ready: R" . number_format($price, 2) . ". Open your dashboard to accept or decline.",
                    'quote');

                // Email
                if (!empty($c['email'])) {
                    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
                    $link   = $scheme . '://' . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\') . '/customer.php';

                    Mailer::send(
                        $c['email'],
                        'Your MSBMS quote is ready',
                        "Hi {$c['full_name']},\n\nYour quote for \"{$service}\" is R" . number_format($price, 2)
                        . ".\n\nLog in to accept or decline it:\n$link\n\nMSBMS"
                    );
                }
            }
        }

        return $ok ? [true, 'Quote sent to the customer.'] : [false, 'Could not save the quote.'];
    }

    public function respondToQuote(int $bookingId, bool $accept): bool
    {
        $ok = $this->bookings->respondToQuote($bookingId, (int)Session::userId(), $accept);

        if ($ok) {
            $d = $this->bookings->emailDetails($bookingId);
            if ($d && !empty($d['mechanic_id'])) {
                $this->bookings->notify((int)$d['mechanic_id'],
                    'Customer ' . ($accept ? 'accepted' : 'declined') . " the quote for booking #{$bookingId}.",
                    'quote');
            }
        }

        return $ok;
    }

    public function jobForMechanic(int $bookingId): ?array
    {
        return $this->bookings->findForMechanicById($bookingId, (int)Session::userId());
    }

    public function completeJob(int $bookingId): array
    {
        $raw = trim($_POST['final_price'] ?? '');
        if (!is_numeric($raw) || (float)$raw < 0) {
            return [false, 'Enter a valid price (0 or more).'];
        }
        $ok = $this->bookings->completeWithPrice($bookingId, (int)Session::userId(), round((float)$raw, 2));

        if ($ok) {
            BookingMailer::completed($this->bookings, $bookingId);
        }

        return $ok ? [true, 'Job completed.'] : [false, 'Could not complete this job.'];
    }
}