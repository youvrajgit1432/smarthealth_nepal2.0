# SmartHealth Nepal 2.0 — Portfolio Profile

---

## 1. Project Title

**SmartHealth Nepal 2.0 — Digital Patient Flow, Token & Chronic Care Management System**

---

## 2. One-Line Project Summary

A PHP/MySQL healthcare workflow platform that lets patients book hospital queue
tokens (with a pre-triage assessment) and track chronic/maternal care, while
hospital staff and administrators manage live queues, departments, services and
reports through dedicated web portals.

---

## 3. Short Portfolio Description

SmartHealth Nepal 2.0 is a multi-portal hospital-flow web application built with
PHP and MySQL. Patients register with a phone number and MPIN, answer a short
pre-triage (health assessment) form, are suggested appropriate hospitals and
departments, and receive a queue token with position and estimated wait time.
Hospitals manage their live queues, staff and departments from a dedicated
portal, while a central admin area manages users, services and offices. The
system includes chronic-disease and maternal-health tracking, referral handling,
assisted/offline and SMS booking, and a bilingual English/Nepali interface with
full UTF-8 (utf8mb4) Nepali text support.

---

## 4. Detailed Portfolio Description

SmartHealth Nepal 2.0 addresses a common problem in resource-constrained
healthcare settings: long physical queues and opaque waiting times at
outpatient departments. The system separates the patient experience from the
hospital back-office and the system-administration layer, giving each audience
its own focused interface.

**Patient-facing application**
- Home / landing page with service highlights, bilingual navigation and quick
  booking actions.
- Token booking flow with a **pre-triage health assessment** (fever, duration,
  difficulty breathing, injury, pregnancy, chronic disease, emergency signs,
  notes) that feeds the booking and triage logic.
- Location-aware **hospital suggestion** engine (district → municipality) that
  recommends hospitals and departments, with a public "Find Nearby Hospitals"
  directory of 24 hospitals.
- **Appointment slot** selection (preferred date + time window, booking type).
- **Queue status** screen showing token number, status, department, hospital
  contact, number of people ahead, estimated wait time and current department
  load.
- Patient account area: profile, bookings, chronic-disease records, health
  history, notifications.
- **Chronic disease tracking** (diagnosis date, last visit, next follow-up with
  "due soon" indicators and follow-up booking).
- **Maternal health tracking** (LMP/expected due date, antenatal visits,
  vaccinations, warning signs).
- Offline / **assisted booking** and **SMS booking** interfaces for
  reception/staff-assisted use cases.

**Hospital portal** (`admin/hospital/`)
- Hospital dashboard with live token summary.
- Token queue management, assisted bookings, departments, staff, reports,
  account/profile and notification settings.

**Administration panel** (`admin/frontend/`)
- Super-admin dashboard with active-token and department-load statistics.
- Token management (active / missed / reschedule), user management
  (list/add/edit/view), service management (approve/forward/referral), office
  (department) management, and admin profile/settings.

**Platform qualities**
- REST-style JSON API endpoints used by the front-end via `fetch()`.
- Bilingual UI (English / Nepali) with UTF-8 `utf8mb4` storage.
- Session-based auth for patients (phone + MPIN, OTP-assisted) and separate
  session auth for administrators and hospital staff.
- Development-mode SMS/OTP handling so the flow can be demonstrated without a
  paid SMS gateway.

---

## 5. Problem Solved

Outpatient departments in busy hospitals suffer from physical crowding,
unclear waiting times, and no visibility for staff into demand per department.
Patients — especially those with chronic or maternal conditions — also lack a
simple digital record of follow-up dates and history. SmartHealth Nepal 2.0
demonstrates how a lightweight, low-bandwidth PHP/MySQL system can:

- move token issuance and pre-triage online / to reception,
- give patients a real-time queue position and estimated wait,
- give hospitals a live view of queues, department load and staff,
- keep structured chronic and maternal follow-up records,
- operate in the local language and on modest hosting.

---

## 6. Main Features

- Phone + MPIN patient authentication with OTP-assisted booking and auto-login.
- Pre-triage health assessment feeding priority/triage logic.
- Symptom- and location-aware hospital and department suggestion.
- Appointment date / time-window slot selection.
- Queue token generation with position, estimated wait time and department load.
- Live queue tracking by token number or phone number.
- Chronic disease tracking and follow-up reminders.
- Maternal health tracking.
- Health history and notifications.
- Referrals between departments / services.
- Assisted (offline) and SMS booking flows.
- Hospital portal: dashboard, tokens, assisted bookings, departments, staff,
  reports, settings.
- Admin panel: token management (active/missed/reschedule), user management,
  service management (approve/forward/referral), office/department management.
- Reporting / statistics endpoints (daily summary, department and staff stats).
- Bilingual English/Nepali interface (utf8mb4).
- Responsive, mobile-friendly Bootstrap 5 layout.

---

## 7. Technology Stack

| Layer | Technology |
|-------|-----------|
| Backend language | PHP 8.2 (originally targeted PHP 7.4+) |
| Database | MySQL / MariaDB 10.4, InnoDB, utf8mb4 / utf8mb4_unicode_ci |
| DB access | MySQLi and PDO (prepared statements) |
| Web server | Apache 2.4 (XAMPP) with `.htaccess` |
| Front-end | HTML5, CSS3, vanilla JavaScript, Bootstrap 5.3, Font Awesome 6 |
| APIs | JSON endpoints consumed via `fetch()` |
| Sessions | PHP native sessions (separate patient / admin / hospital contexts) |
| i18n | PHP language files (English / Nepali), UTF-8 |
| Tooling | Git, Composer-less deployment, XAMPP stack |

---

## 8. My Development Contribution

This is my own project. I designed and built the application end-to-end:

- Database schema design (26 tables + 1 view) covering users, tokens,
  departments, services, chronic and maternal health, referrals, notifications,
  hospitals, hospital departments/staff, appointment slots, health assessments,
  offline bookings, OTP sessions and admin logs.
- Backend architecture: config layer, models, controllers, helpers, services and
  JSON API endpoints.
- Patient token-booking and pre-triage flow with OTP/MPIN authentication.
- Hospital suggestion and appointment-slot logic.
- Patient, hospital and admin user interfaces (bilingual, responsive).
- Queue management, chronic/maternal tracking and reporting features.
- Local development setup, seed/demo dataset and demo accounts.

---

## 9. Technical Architecture Summary

```
smarthealth_nepal/
├─ index.php                 → entry redirect to patient app
├─ backend/
│   ├─ config/               → database.php, app.php, sms.php, language.php
│   ├─ controllers/          → User, Token, Tracking, Auth, Office, ...
│   ├─ models/               → UserModel, TokenModel, DepartmentModel,
│   │                          HealthAssessmentModel, ChronicDiseaseModel, ...
│   ├─ api/                  → JSON endpoints (booking, OTP, tokens, slots,
│   │                          hospital suggestion, status, chronic/maternal)
│   ├─ helpers/              → OTPHelper, SMSHelper, TokenHelper, HospitalHelper,
│   │                          AppointmentSlotHelper
│   ├─ services/             → SparrowSMSService (config-driven, mock-able)
│   ├─ lang/                 → en.php, ne.php
│   └─ data/                 → nepal_districts.json (location data)
├─ frontend/
│   ├─ public/               → patient public entry point + assets
│   └─ views/                → auth, home, token, tracking, profile, services,
│                              offline (assisted/SMS booking), layouts
├─ admin/
│   ├─ frontend/             → super-admin panel (dashboard, token/user/service/
│   │                          office management, settings)
│   ├─ hospital/             → hospital staff portal (dashboard, tokens,
│   │                          assisted bookings, departments, staff, reports)
│   └─ backend/              → admin auth config, controllers, hospital API
├─ public/                   → standalone public pages (hospitals, detail)
└─ database/                 → schema + seed SQL
```

The request flow is a lean MVC-style split: view/page → controller or API
endpoint → model / prepared SQL → MySQL. Front-end pages call JSON APIs with
`fetch()`; the admin and hospital areas use server-rendered PHP views backed by
controllers and a dedicated hospital API.

---

## 10. Database / Features Summary

- **26 base tables + 1 view (27 objects)**, InnoDB, charset `utf8mb4`, collation
  `utf8mb4_unicode_ci`.
- Core tables: `users`, `tokens`, `departments`, `services`, `admins`,
  `admin_logs`, `health_assessments`, `health_records`, `chronic_diseases`,
  `maternal_health`, `referrals`, `notifications`, `appointment_slots`,
  `offline_bookings`, `booking_history`, `otp_sessions`, `system_settings`,
  `symptom_hospital_mapping`, `triage_responses`, `user_locations`.
- Hospital tables: `hospital_locations`, `hospital_departments`,
  `hospital_staff`, `hospital_statistics`, `assisted_bookings`.
- View: `nearby_hospitals_view`.
- Referential integrity via foreign keys; token / queue state machine
  (`Active`, `Called`, `Completed`, `Missed`).
- Data is stored as UTF-8 so Nepali (`सामान्य चिकित्सा`) renders correctly.

---

## 11. Key Challenges Solved

- **Consistent identifiers across code and schema** — the codebase referenced
  several naming conventions; the schema was normalised with compatibility
  columns so every layer reads/writes consistently.
- **Queue state and priority logic** — integrating pre-triage answers (fever,
  breathing difficulty, injury, pregnancy, emergency signs) with token priority.
- **Location-aware hospital suggestion** — district/municipality data driving
  hospital and department recommendations.
- **Multi-portal sessions** — separating patient, hospital-staff and
  super-admin authentication and navigation.
- **Demonstrable SMS/OTP without a paid gateway** — a debug OTP path so the
  booking flow is fully testable offline.
- **Bilingual UTF-8 content** — end-to-end utf8mb4 handling for Nepali text.

---

## 12. Fiverr-Ready Description

> **SmartHealth Nepal 2.0 — Hospital Token & Patient Flow System (PHP/MySQL)**
>
> A complete multi-portal healthcare workflow app built with PHP & MySQL.
> Patients book queue tokens, complete a short pre-triage health assessment,
> get hospital/department suggestions based on symptoms and location, and track
> their queue position with an estimated wait time. Includes chronic-disease and
> maternal health tracking, referrals, assisted/SMS booking, and a bilingual
> English/Nepali interface. Hospitals get their own portal (dashboard, live
> queue, staff, departments, reports) and a super-admin area manages users,
> services and offices. Clean MVC-style PHP codebase, MySQL schema, responsive
> Bootstrap UI. Perfect for clinics and hospitals that need affordable digital
> patient flow.

---

## 13. Upwork-Ready Description

> **SmartHealth Nepal 2.0** is a PHP/MySQL hospital patient-flow platform I
> designed and built end-to-end. It provides three role-based experiences —
> patient, hospital staff, and system administrator — over a shared MySQL
> schema (26 tables + 1 view, utf8mb4). Key capabilities include phone+MPIN
> authentication with OTP-assisted booking, a pre-triage health assessment,
> symptom/location-aware hospital and department suggestion, appointment slot
> selection, queue tokens with real-time position / estimated wait / department
> load, chronic and maternal care tracking, referrals, and JSON APIs consumed
> by a responsive Bootstrap 5 front-end. The admin and hospital portals cover
> queue operations, user management, service approval, departments, staff and
> reporting. The interface is bilingual (English/Nepali) and fully UTF-8.

---

## 14. Freelancer-Ready Description

> **SmartHealth Nepal 2.0** — a digital patient flow and hospital queue system
> built with PHP, MySQL, Bootstrap 5 and vanilla JavaScript. Patients register
> with a phone number and MPIN, complete a pre-triage questionnaire, choose a
> suggested hospital/department and an appointment slot, then receive a token
> with live queue position and estimated wait time. Chronic-disease and maternal
> health modules keep follow-up records; referrals, notifications and
> assisted/SMS booking round out the workflow. Two back-office portals support
> daily operations: a hospital portal (dashboard, queue, staff, departments,
> reports) and a super-admin panel (tokens, users, services, offices). The app
> is bilingual (English/Nepali) with full UTF-8 support and a responsive,
> mobile-friendly UI.

---

## 15. Suggested Project Skills / Tags

`PHP` · `MySQL` · `MariaDB` · `PDO` · `MySQLi` · `Apache` · `XAMPP` ·
`Bootstrap 5` · `JavaScript` · `HTML5` · `CSS3` · `REST API` · `JSON` ·
`MVC` · `Session Management` · `Authentication` · `OTP` · `Healthcare
Software` · `Hospital Management System` · `Queue / Token System` ·
`Patient Management` · `e-Health` · `Multilingual (i18n)` · `UTF-8` ·
`Chronic Care` · `Maternal Health` · `Responsive Design`

---

## 16. Screenshot Captions

| File | Caption |
|------|---------|
| `01-home.png` | Patient landing page with bilingual navigation and quick booking actions. |
| `02-pre-triage.png` | Pre-triage health assessment captured during token booking (fever, breathing difficulty, injury, pregnancy, chronic disease, emergency signs). |
| `03-hospital-selection.png` | "Find Nearby Hospitals" directory — 24 hospitals filterable by district with departments and specialities. |
| `04-token-booking.png` | Appointment scheduling step — preferred date, time window and booking type. |
| `05-patient-dashboard.png` | Logged-in patient home dashboard. |
| `06-queue-status.png` | Live queue status — token number, department, hospital contact, people ahead, estimated wait time and department load. |
| `07-health-tracking.png` | Chronic disease tracking with diagnosis dates, last visit and next follow-up reminders. |
| `08-admin-dashboard.png` | Super-admin dashboard with active tokens, total patients, completed-today count and department-load table. |
| `09-token-management.png` | Admin token management — live department queues. |
| `10-hospital-portal.png` | Hospital staff portal dashboard. |
| `11-reports.png` | Hospital reports / statistics view. |
| `12-nepali-interface.png` | Patient interface switched to Nepali (UTF-8) to demonstrate bilingual support. |
| `mobile-01-home.png` | Responsive mobile view — patient home. |
| `mobile-02-token-booking.png` | Responsive mobile view — token booking. |

---

*SmartHealth Nepal 2.0 is a demonstration / portfolio build of a healthcare
workflow system. It is not a production deployment and is not affiliated with,
deployed at, or used by any real hospital; all data shown is fictional demo
data.*
