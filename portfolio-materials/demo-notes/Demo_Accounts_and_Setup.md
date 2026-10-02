# SmartHealth Nepal 2.0 — Demo Accounts & Local Setup Notes

> All accounts and data below are **fictional local demo data** created for
> development and portfolio demonstration only. They are not real users and are
> not affiliated with any real hospital.

---

## Running the app locally

- Stack: XAMPP (Apache 2.4 + PHP 8.2 + MariaDB 10.4) on Windows.
- Project path: `C:\xampp\htdocs\smarthealth_nepal`
- Database: `smarthealth` (MySQL/MariaDB, utf8mb4)
- DB connection: host `localhost`, user `root`, empty password, port `3306`
  (see `backend/config/database.php`).

Start Apache and MySQL from the XAMPP control panel, then open:

| Portal | URL |
|--------|-----|
| Patient application | http://localhost/smarthealth_nepal/ |
| Admin panel | http://localhost/smarthealth_nepal/admin/ |
| Hospital portal | http://localhost/smarthealth_nepal/admin/hospital/ |
| OTP debug (dev only) | http://localhost/smarthealth_nepal/backend/otp_debug.php |

---

## Demo accounts

### Administrator (super-admin panel)
| Email | Password | Role |
|-------|----------|------|
| `admin@smarthealth.local` | `admin123` | SuperAdmin |
| `superadmin@smarthealth.local` | `admin123` | SuperAdmin |

### Hospital staff portal
| Username | Password | Hospital |
|----------|----------|----------|
| `bir_admin` | `hospital123` | Bir Hospital, Kathmandu (id 12) |
| `patan_admin` | `hospital123` | Patan Hospital (id 13) |
| `bhaktapur_admin` | `hospital123` | Bhaktapur Cancer Hospital (id 1) |

### Officer
| Username | Password |
|----------|----------|
| `medicine_officer` | `staff123` |

### Patients (phone number + MPIN)
| Name | Phone | MPIN | Notes |
|------|-------|------|-------|
| Aarav Sharma | `9803962360` | `3736` | Has chronic-disease records |
| Bikash Gurung | `9812345678` | `8142` | Has active queue tokens |
| Priya Thapa | `9844634579` | `9599` | Maternal health record |
| Sunita Rai | `9856789012` | `7788` | |
| Ramesh Karki | `9861112233` | `4455` | |
| Anita Maharjan | `9872223344` | `3322` | |
| Deepak Shrestha | `9883334455` | `9900` | |
| Manisha Tamang | `9894445566` | `6644` | |
| Nabin Demo Patient | `9777770001` | `3684` | |

Patient login is a two-step flow: **phone number** → **MPIN**.

---

## SMS / OTP in local mode

The booking flow supports OTP verification. A paid SMS gateway is **not**
required for demonstration:

- SMS is disabled by default via `system_settings.sms_enabled = '0'`.
- Generated OTPs can be viewed at
  `http://localhost/smarthealth_nepal/backend/otp_debug.php` (development only).
- The booking UI tolerates `sms_sent: false` and continues to OTP entry.

To enable a real gateway, configure the SMS provider in `system_settings` and
`backend/config/sms.php`. No real credentials are included in this repository.

---

## Database

- Canonical schema + demo seed: `database/smarthealth.sql` **(import this one only)**
- Schema: 26 tables + 1 view (`nearby_hospitals_view`) = 27 database objects, utf8mb4.
- The other files under `database/archive/` are historical recovery artifacts for
  provenance only — do **not** import them.
- Approximate demo row counts: 9 users, 15+ tokens, 10 departments, 6 services,
  24 hospitals, 23 hospital departments, 10 hospital staff, 5 chronic records,
  2 maternal records, 8 health assessments, 110 appointment slots, 5
  notifications, 3 referrals.

To re-import:

```bash
mysql -u root --default-character-set=utf8mb4 < database/smarthealth.sql
```

---

## Notes

- Keep the application in development mode for demonstration
  (`backend/config/app.php`: `APP_ENV=development`).
- Do not commit real SMS credentials.
- Screenshots live in `../../docs/images/smarthealth/` (canonical, rendered by the
  root README) and were captured with a clean 1440×900 viewport (and 390×844 for
  mobile) containing only fictional demo data.
