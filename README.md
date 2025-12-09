# Event Management System (EMS)

A lightweight PHP/MySQL event management example application designed for local development (XAMPP). It includes user signup/login, event creation (pending approval), admin approval UI, booking requests, and basic admin tools.

---

## Quick features
- User signup / login (passwords are hashed)
- Create events (users submit; admin approves before they appear)
- Admin dashboard to approve/reject events and bookings
- Booking flow where users can request to book events (status: pending → approved/rejected)
- Small utilities in `tools/` (CLI tools) for inspection and tests

---

## Requirements
- PHP (bundled with XAMPP recommended)
- MySQL or MariaDB (XAMPP)
- A webserver like Apache (XAMPP)

This project was developed and tested locally on Windows + XAMPP.

---

## Quick local setup (Windows + XAMPP)
1. Make sure Apache + MySQL are running in XAMPP.
2. Copy this repo into your webroot (for example `C:\xampp\htdocs\EMS`).
3. Import the DB schema:

   - Open phpMyAdmin or MySQL CLI and run the SQL file at `sql/event_management.sql`.

4. Configure database connection (if needed):

   - The app uses `includes/db.php`. For local XAMPP, the default config is `user: root` with an empty password. Update `includes/db.php` if your environment differs.

5. Create an admin account (optional) — recommended to do this via CLI helper for security:

   - CLI helper (only available via the command line):

     ```powershell
     C:\xampp\php\php.exe tools\create_admin.php admin@example.com StrongP@ssw0rd
     ```

   - NOTE: The helper `tools/create_admin.php` is intentionally set to refuse web execution — it must be run from the CLI. When finished, move or remove this helper so it is not present in a public webroot.

6. Visit the app in a browser:

   - http://localhost/EMS/login.php (regular login)
   - http://localhost/EMS/admin_login.php (admin-only sign-in)

Example dev admin credentials used during development (please change on first use):

```
Username/email: admin@example.com
Password: 1234
```

---

## Useful developer tools (in `tools/`)
- `tools/check_headers.php` — scanner for files that output content before headers are sent (helps avoid "headers already sent" problems)
- `tools/create_admin.php` — CLI helper for creating/updating admin users (CLI-only)
- `tools/inspect_users.php` — quick database inspector for the `users` table
- `tools/test_login.php` and `tools/test_admin_login.php` — small CLI tests to verify logins/password verification


---

## Security / Next steps
- Convert all raw SQL queries to prepared statements (some places still use string interpolation in SQL; this creates an SQL injection risk). I already converted several pages, but a small audit remains.
- Consider adding CSRF protection to forms (admin actions and password resets)
- Move sensitive configuration (DB credentials) out of the repo for production (use `.env`, environment variables, or server config files)
- Add logging/auditing for admin actions

---

