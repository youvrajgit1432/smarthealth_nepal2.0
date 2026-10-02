# SmartHealth Nepal 2.0 — Screenshot Guide

The canonical screenshot set lives in **`docs/images/smarthealth/`** and is what the
root `README.md` gallery renders on GitHub.

- Desktop captures: **1440×900**
- Mobile capture: **390×844**
- All screens show **fictional seed data only** — no real patients, credentials,
  OTP codes, API keys or debug output are visible.

## Patient app — `docs/images/smarthealth/patient/`

| File | Screen | Caption |
|------|--------|---------|
| `01-home.png` | Patient landing | Bilingual entry point with one-tap token booking and live queue access. |
| `02-login.png` | Login | Phone number + MPIN login. |
| `03-services-hospitals.png` | Services & hospitals | Service catalogue and nearby-hospital directory. |
| `04-pre-triage.png` | Pre-triage | Symptom capture (fever, breathing difficulty, injury, pregnancy, chronic disease, emergency signs). |
| `05-token-booking.png` | Token booking | Location-aware hospital selection and booking details. |
| `06-token-confirmation.png` | Confirmation | Generated token with department, priority and estimated wait. |
| `07-token-status.png` | Live status | Queue position, ETA and progress tracking. |
| `08-profile-health.png` | Health history | Patient health records / history. |
| `09-chronic-care.png` | Chronic care | Chronic disease tracking with follow-up reminders. |
| `10-maternal-care.png` | Maternal care | Pregnancy milestones and vaccination reminders. |
| `11-nepali-ui.png` | Nepali UI | Interface in Nepali (UTF-8) demonstrating bilingual support. |
| `12-mobile.png` | Mobile (390×844) | Responsive mobile home. |

## Hospital portal — `docs/images/smarthealth/hospital/`

| File | Screen | Caption |
|------|--------|---------|
| `01-login.png` | Login | Hospital administrator login. |
| `02-dashboard.png` | Dashboard | Today's tokens, departments, staff and assisted-booking summary. |
| `03-token-management.png` | Token management | Live queue with filters, search and call/complete/miss/reschedule actions. |
| `04-token-details.png` | Token details dialog | Accessible, focus-managed token details modal. |
| `05-assisted-bookings.png` | Assisted bookings | Staff-created bookings for walk-in patients. |
| `06-new-booking-modal.png` | New booking dialog | Assisted-booking creation form. |
| `07-departments.png` | Departments | Department capacity and availability control. |
| `08-department-edit.png` | Department edit dialog | Edit dialog for a hospital department. |
| `09-staff.png` | Staff | Hospital staff records by department. |
| `10-settings.png` | Settings | Hospital settings landing. |
| `11-reports.png` | Reports | Operational reporting view. |

## Super administration — `docs/images/smarthealth/admin/`

| File | Screen | Caption |
|------|--------|---------|
| `01-login.png` | Login | Super admin login. |
| `02-dashboard.png` | Dashboard | National overview of token activity. |
| `03-token-management.png` | Token management | Cross-hospital token oversight. |
| `04-user-management.png` | User management | Search, view, add and edit accounts. |
| `05-service-management.png` | Service management | Service approvals and referral handling. |
| `06-office-management.png` | Office management | Office / department administration. |
| `07-profile-settings.png` | Profile & settings | Admin profile and account settings. |

## How these were captured

Screenshots were produced with `agent-browser` against the running local XAMPP
instance using the fictional demo accounts documented in
[`demo-notes/Demo_Accounts_and_Setup.md`](../demo-notes/Demo_Accounts_and_Setup.md).
Ordinary pages were captured with all dialogs closed; modal screenshots show
exactly one intentionally opened dialog.
