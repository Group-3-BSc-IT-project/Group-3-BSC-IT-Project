
## accounts
- Admin: `admin@msbms.com`
- Mechanic: `mechanic@msbms.com`    only the admin can create the mechanic. machanic can login upon creation.
- Customer: `customer@msbms.com`

password to every acount is 1234 for production purposes -------Update before production.

Use the passwords from your existing MSBMS seed data if you already know them. If those seeded hashes do not match your expected password, create a new customer account through Register or change the password using PHP `password_hash()`.

## Architecture
View -> Controller -> Service -> Repository -> MySQL

- `public/`: entry pages/views
- `app/Controllers/`: receives requests
- `app/Services/`: business rules
- `app/Repositories/`: SQL/database operations
- `app/Models/`: domain objects
- `app/Core/`: database, session, auth and validation infrastructure
- `database/`: MySQL schema and seed data
