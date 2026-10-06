<?php
require_once __DIR__ . '/../app/bootstrap.php';

// ---- Repositories ----
$userRepo         = new UserRepository($db);
$vehicleRepo      = new VehicleRepository($db);
$serviceRepo      = new ServiceRepository($db);
$bookingRepo      = new BookingRepository($db);
$repairRepo       = new RepairRepository($db);
$notificationRepo = new NotificationRepository($db);

// ---- Services ----
$authService    = new AuthService($userRepo);
$bookingService = new BookingService($bookingRepo, $vehicleRepo, $serviceRepo, $userRepo, $notificationRepo);
$vehicleService = new VehicleService($vehicleRepo, $userRepo);
$repairService  = new RepairService($repairRepo, $bookingRepo);
$userController = new UserController($userRepo);

// ---- Controllers ----
$authController         = new AuthController($authService);
$bookingController      = new BookingController($bookingService, $bookingRepo, $vehicleRepo, $serviceRepo, $userRepo);
$vehicleController      = new VehicleController($vehicleService, $vehicleRepo, $userRepo);
$repairController       = new RepairController($repairService, $bookingRepo);
$adminController        = new AdminController($bookingRepo, $userRepo);
$notificationController = new NotificationController($notificationRepo);
$serviceController      = new ServiceController($serviceRepo);
$userController         = new UserController($userRepo); 

// ---- Helpers ----
function e(mixed $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
