# MSBMS Project File Guide

## What The Project Does

MSBMS is a PHP/MySQL vehicle service booking and management application. The customer, mechanic, and administrator screens are plain PHP pages in `public/`. Each page is both a request handler and its HTML view: it checks access, calls application classes, then renders the result.

There is no central router or frontend build pipeline. The cooperating files turn a browser request into database work and then into a rendered page.

## How The Files Work Together

```text
Browser request
  -> public page (e.g. booking_create.php)
  -> public/_init.php
  -> app/bootstrap.php -> config/config.php -> Database.php
  -> page calls a Controller
  -> Controller calls a Service for business rules
  -> Service calls Repository objects for SQL
  -> MySQL returns data / confirms writes
  -> result flows back to the page and becomes HTML
```

The usual path for a page is `public page -> Controller -> Service -> Repository -> database`. Simple lookups may skip the service and call a controller/repository directly. The controller and service are PHP classes, not separate HTTP endpoints.

### Example: A Customer Books A Service

1. `public/booking_create.php` includes `_init.php`, checks that a user is logged in, gets the customer's vehicles and the service list, and renders the form.
2. On form submission the page calls `BookingController::create()`.
3. `BookingController` reads the submitted IDs/date/time/notes and passes them, along with `Session::userId()`, to `BookingService`.
4. `BookingService` verifies the vehicle belongs to that customer, the service exists, the date is not in the past, and the requested slot is not taken.
5. `BookingRepository` inserts the `Booking` model into `bookings`. `NotificationRepository` creates a confirmation notification.
6. The controller returns a success/message pair. The page redirects to `customer.php`, where `BookingRepository` reads the customer's bookings and the page renders them.

The same division of work appears in login, vehicle registration, mechanic assignment, and repair recording. Shared files are loaded once per request; the database contains the records shared between requests.

## Entry And Shared Setup Files

| File | What it is responsible for |
| --- | --- |
| `public/_init.php` | Included by most application pages. Loads bootstrap, creates repository/service/controller objects and defines `e()` for HTML escaping. This is the dependency wiring point. |
| `app/bootstrap.php` | Loads config, autoloads classes from the Core/Models/Repositories/Services/Controllers folders, starts the session, and creates `$db`. |
| `config/config.php` | Defines MySQL connection constants and `BASE_URL`, which authentication redirects and page links use. |
| `app/Core/Database.php` | Builds the PDO MySQL connection with exceptions, associative fetches, and native prepared statements. |
| `app/Core/Session.php` | Starts and reads/writes the login session: user ID, role, and display name. Regenerates the session ID at login and clears it at logout. |
| `app/Core/Auth.php` | Page access guards. Requires a login, a specific role, or one of a set of roles; redirects or returns 403 when access is not allowed. |
| `app/Core/Validator.php` | Contains small required-field/date helpers. Current services perform their own checks; this class is not used by the active workflows. |

## Browser Pages In `public/`

All the following PHP pages are direct browser entry points unless noted. They render the interface and coordinate the relevant controller calls.

### Landing And Account Pages

| File | Responsibility and connections |
| --- | --- |
| `public/index.php` | Public landing page and service listing. Unlike the app pages, it opens its own PDO connection and reads services/notifications directly instead of using `_init.php` and repositories. Some dashboard/booking links in it target older nested paths, not the current top-level pages. |
| `public/register.php` | Customer registration form. Posts to `AuthController`, then redirects to login on success. |
| `public/login.php` | Login form. Calls `AuthController`, then redirects admins to `admin.php`, mechanics to `mechanic.php`, and customers to `customer.php`. |
| `public/logout.php` | Clears the session through `Session::logout()` and redirects to `index.php`. |
| `public/dashboard.php` | Login-protected role router: sends the current user to the correct role dashboard. It does not display a dashboard itself. |

### Booking Pages

| File | Responsibility and connections |
| --- | --- |
| `public/booking_create.php` | Displays the service/vehicle/date form and submits a new booking through `BookingController` and `BookingService`. |
| `public/booking_reschedule.php` | Customer-only form. Confirms the booking belongs to the customer, then delegates slot/date validation and update to the booking controller/service/repository. |
| `public/booking_cancel.php` | Customer-only action. Asks the booking controller/repository to cancel that customer's eligible booking, then redirects to `customer.php`. The current dashboard invokes it with a GET link. |
| `public/booking_assign.php` | Admin-only form. Loads one booking and mechanic choices; posts the assignment through `BookingController` and `BookingService`. |

### Role Dashboards And Operations

| File | Responsibility and connections |
| --- | --- |
| `public/customer.php` | Customer dashboard. Gets only the signed-in customer's bookings from `BookingController` and links to booking actions. |
| `public/admin.php` | Admin dashboard. Gets system counts through `AdminController` and all bookings through `BookingController`; links to mechanic creation and booking assignment. |
| `public/mechanic.php` | Mechanic dashboard. Lists only that mechanic's assigned bookings and displays notification count. |
| `public/mechanic_create.php` | Admin-only mechanic account form. Posts to `AuthController::createMechanic()`. |
| `public/repair_create.php` | Mechanic-only repair form. Loads a booking and submits its repair details through `RepairController` and `RepairService`. |

### Shared Data Pages

| File | Responsibility and connections |
| --- | --- |
| `public/vehicle_create.php` | Customer/admin vehicle form. Customers add their own vehicle; admins can select a customer owner. Uses `VehicleController` and `VehicleService`. |
| `public/vehicles.php` | Lists vehicles for the current customer, or all vehicles for an admin, using `VehicleController` and `VehicleRepository`. |
| `public/services.php` | Reads the service catalog through `ServiceController` and `ServiceRepository`. It displays services; it is not an admin service editor. |
| `public/notifications.php` | Lists the current user's notifications, displays unread state, and delegates mark-read actions to notification controller/repository. |
| `public/profile.php` | Displays/updates the signed-in user's name, email, phone, and address through `UserController`. Password-update logic exists in the controller, but this page currently only presents profile fields. |
| `public/history.php` | Displays repair records for customer/admin roles through `RepairController`. Its repository query expects a `repair_history` database view that is not created by the supplied base schema. The page also links to `repair_edit.php`, `repair_copy.php`, and `repair_delete.php`; those files are not present in this project. |

## Application Classes

### Controllers: Connect Pages To Application Logic

Controllers accept values from the page/session and call services or repositories. They generally return data or a success/message result; the page decides what HTML or redirect to show.

| File | Responsibility |
| --- | --- |
| `app/Controllers/AuthController.php` | Reads login/register/mechanic form values and delegates to `AuthService`. |
| `app/Controllers/AdminController.php` | Provides admin dashboard counts and mechanic choices from repositories. |
| `app/Controllers/BookingController.php` | Coordinates booking create/cancel/reschedule/assign/complete operations, customer/mechanic/admin booking lists, and form lookups. |
| `app/Controllers/VehicleController.php` | Determines vehicle owner based on session role and coordinates create/list/customer lookups. |
| `app/Controllers/RepairController.php` | Passes repair form data to `RepairService` and exposes booking/history queries to pages. |
| `app/Controllers/NotificationController.php` | Provides the current user's notifications, unread count, and mark-read actions. |
| `app/Controllers/ServiceController.php` | Exposes the service catalog to pages. |
| `app/Controllers/UserController.php` | Reads and updates the signed-in user's profile and contains password update logic. |

### Services: Business Rules

| File | Responsibility |
| --- | --- |
| `app/Services/AuthService.php` | Checks passwords, starts login sessions, creates customers/mechanics, and hashes newly created passwords. |
| `app/Services/BookingService.php` | Validates booking ownership, service, date and slot; coordinates booking writes and notifications; validates mechanic assignment. |
| `app/Services/VehicleService.php` | Validates required vehicle fields, numeric year, and unique registration before creating a vehicle. |
| `app/Services/RepairService.php` | Verifies the booking is assigned to the mechanic, validates description/cost, saves repair data, and marks the booking completed. |

### Repositories: SQL And Persistence

Repositories receive the PDO connection from `_init.php`. They contain the SQL that reads/writes MySQL rows.

| File | Main database responsibility |
| --- | --- |
| `app/Repositories/UserRepository.php` | Find/create/update users; list customers/mechanics; check mechanic role. |
| `app/Repositories/VehicleRepository.php` | Check ownership/registration, create vehicles, and list customer/all/form vehicles. |
| `app/Repositories/ServiceRepository.php` | List services and check whether a service ID exists. |
| `app/Repositories/BookingRepository.php` | Check time-slot conflicts; create, cancel, reschedule, assign, complete, and query bookings; compute admin counts. |
| `app/Repositories/RepairRepository.php` | Check mechanic assignment and create repair rows. Its all-history query reads `repair_history`. |
| `app/Repositories/NotificationRepository.php` | Create notifications, query a user's notifications/unread count, and mark one/all as read. |

### Models: Structured Records

These small classes hold domain values passed to repository write methods. Query methods mostly return associative arrays rather than model objects.

| File | Represents |
| --- | --- |
| `app/Models/User.php` | User identity and account fields. The active user repository/auth flows mostly use associative arrays, so this model is not central to the current path. |
| `app/Models/Vehicle.php` | Vehicle owner, registration, make/model, optional year and VIN. |
| `app/Models/Booking.php` | Customer, vehicle, service, date/time, notes, and initial status for a booking write. |
| `app/Models/Repair.php` | Booking/mechanic IDs, repair description/status, cost, and optional repair date. |
| `app/Models/Notification.php` | Recipient user ID and notification message. |

## Database And Presentation Files

| File | Responsibility |
| --- | --- |
| `database/msbms_mysql.sql` | Defines users, services, vehicles, bookings, repairs, notifications, and payments, then inserts sample records. It drops/recreates tables when rerun. |
| `database/migration_custom_services_quotes.sql` | Adds proposed custom-service/quote fields and password-reset storage. The current pages do not implement those features. |
| `public/assets/styled.css` | Main styles used by the current dashboard and form pages. |
| `public/assets/style1.css` | Additional/older stylesheet; no current PHP page references this filename. |
| `README.md` | Existing short project notes. It contains merge-conflict markers and account information that conflicts with the seeded SQL, so it is not a dependable description of current behavior. |

The `payments` table exists in the schema, but no current controller, service, repository, or page implements payments. The schema migration adds quote/reset-related storage, but there are no active PHP flows for those features either.

## Important Runtime Caveats

- The base SQL creates `repairs`, but `RepairRepository::all()` selects from `repair_history`; no view of that name is defined in the supplied SQL. This affects the history page unless the running database has an additional view.
- `history.php` allows both customers and admins, but calls the unfiltered `RepairRepository::all()`. As written, it is not restricted to the signed-in customer's repair records.
- The landing page uses its own database setup and contains links aimed at a previous nested-path layout. The current application workflow pages are the top-level scripts documented above.
- `history.php` refers to repair edit/copy/delete pages that are absent from this workspace.
- `public/assets/style1.css` is not linked by the current pages; `login.php` and `register.php` also refer to `assets/css/landing.css`, which is not present in the listed asset tree.
- `Validator.php` and the `User` model exist but are not used in the active request path described here.