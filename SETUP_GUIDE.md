# SmartHealth Nepal — Installation & Quick Start Guide

## Overview

SmartHealth Nepal is a digital healthcare queue-management and records system
built with PHP, MySQL/MariaDB and Apache. It provides real-time token/queue
management, digital pre-triage, chronic-disease follow-up tracking, maternal
health monitoring and offline (assisted / SMS) booking.

The system has three portals:

| Portal | Purpose |
| --- | --- |
| Patient | Book and track tokens, health tracking, chronic & maternal care |
| Hospital | Manage the queue, assisted bookings, departments, staff, reports |
| Super Admin | Manage users, services, offices, tokens and referrals |

## Requirements

- PHP 8.0 or newer (developed and tested on PHP 8.2)
- MySQL 5.7+ / MariaDB 10.4+
- Apache with `mod_rewrite` enabled
- [XAMPP](https://www.apachefriends.org/) is the simplest way to get all three

The application uses **no Composer dependencies** — everything runs on a stock
XAMPP install.

## Installation (Windows + XAMPP)

1. **Place the project in the web root**

   Clone or copy the project so that it lives at:

   ```
   C:\xampp\htdocs\smarthealth_nepal
   ```

2. **Start Apache and MySQL**

   Use the XAMPP Control Panel (or `C:\xampp\apache_start.bat` and
   `C:\xampp\mysql_start.bat`).

3. **Create the database and import the schema + demo data**

   The single canonical file is `database/smarthealth.sql`. It creates the
   `smarthealth` database, all 26 tables, the `nearby_hospitals_view` view and
   fictional demo data (27 database objects in total).

   Using phpMyAdmin:

   - Open <http://localhost/phpmyadmin>
   - Click **Import** → choose `database/smarthealth.sql` → **Go**

   Or from the command line:

   ```bash
   C:\xampp\mysql\bin\mysql.exe -u root < database/smarthealth.sql
   ```

4. **Check the database connection (usually no change needed)**

   Defaults in `backend/config/database.php` match a stock XAMPP install:

   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');
   define('DB_PASSWORD', '');
   define('DB_NAME', 'smarthealth');
   define('DB_PORT', 3306);
   ```

   If your MySQL root account has a password, set `DB_PASSWORD` accordingly.

5. **Open the application**

   - Patient portal: <http://localhost/smarthealth_nepal/>
   - Hospital portal: <http://localhost/smarthealth_nepal/admin/hospital/>
   - Super Admin: <http://localhost/smarthealth_nepal/admin/>

   The root and `/admin/` URLs redirect automatically to the correct login or
   dashboard.

## Demo Accounts (LOCAL DEVELOPMENT ONLY)

These accounts are re-created by `database/smarthealth.sql` and use **fictional**
data. They are for **local demonstration only** — delete or change them before
any real deployment.

| Portal | Login | Password / MPIN |
| --- | --- | --- |
| Super Admin | `admin@smarthealth.local` (or username `admin`) | `admin123` |
| Hospital Admin (Bir Hospital) | `bir_admin` | `hospital123` |
| Hospital Admin (Patan Hospital) | `patan_admin` | `hospital123` |
| Patient | phone `9777770001` | MPIN `3684` |

> ⚠️ Never ship these credentials to a production environment.

## Project Structure

```
smarthealth_nepal/
├── admin/                       # Admin + hospital portal
│   ├── backend/                 # APIs, config, controllers, models, lang
│   ├── frontend/                # Super-admin UI (views, layouts, css, js)
│   └── hospital/                # Hospital staff portal
├── backend/                     # Shared backend (config, controllers, models,
│   │                            # helpers, api, lang, services)
├── frontend/                    # Patient UI
│   ├── public/                  #   public entry point (hospitals directory)
│   └── views/                   #   page templates (auth, home, token, profile …)
├── public/                      # Public hospital directory pages
├── database/
│   ├── smarthealth.sql          # ← canonical schema + demo seed
│   └── archive/                 # older recovery dumps (provenance only)
├── docs/images/smarthealth/     # product screenshots (patient/hospital/admin)
├── logs/                        # runtime logs (SMS, activity) — not tracked
├── portfolio-materials/         # portfolio notes and screenshot captions
├── index.php                    # root entry point (redirects to patient app)
└── README.md
```

## Access Points

**Patient portal** — <http://localhost/smarthealth_nepal/>

- Book tokens and digital pre-triage
- Real-time token status tracking
- Chronic disease and maternal health tracking
- Assisted / offline / SMS booking
- English ⇄ Nepali interface

**Hospital portal** — <http://localhost/smarthealth_nepal/admin/hospital/>

- Dashboard with today's queue and KPIs
- Token management (call / complete / miss / reschedule)
- Assisted bookings, departments, staff
- Reports and hospital settings

**Super Admin** — <http://localhost/smarthealth_nepal/admin/>

- Dashboard and token management
- User, service, office and hospital management
- Referral routing

## Troubleshooting

### Database connection failed

- Confirm MySQL/MariaDB is running.
- Confirm the `smarthealth` database exists and was imported from
  `database/smarthealth.sql`.
- Check the credentials in `backend/config/database.php`.

### 403 / directory listing error

The project relies on `DirectoryIndex`. Make sure each entry point directory
contains its `index.php` and that Apache's `AllowOverride` permits the root
`.htaccess`.

### Blank page / error after login

- Set `APP_ENV` to `development` in `backend/config/app.php` to surface errors
  locally (it is already the default for demo use).
- Check `C:\xampp\apache\logs\error.log`.

### Nepali text shows as boxes

- The schema is `utf8mb4`. Ensure your MySQL server and connection use
  `utf8mb4` (the app sets this automatically).

## Notes on the Database

`database/smarthealth.sql` is the **only** schema you need. The files in
`database/archive/` are previous partial recovery dumps kept for provenance;
do **not** import them.

There is intentionally **no** public `setup.php` installer — importing the SQL
file is the supported and safe setup path. A web-reachable database-reset
endpoint would be a security risk.

## License

SmartHealth Nepal — Healthcare Queue Management System.
© 2026. All rights reserved.
