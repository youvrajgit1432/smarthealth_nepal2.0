# SmartHealth Nepal 🏥
## A Sustainable Digital Patient Flow & Chronic Care Tracking System for Government Hospitals

### 📋 Project Overview

SmartHealth Nepal is an innovative, bilingual healthcare management system designed for government hospitals in Nepal. It comprehensively addresses critical healthcare challenges including:
- **Overcrowding & Queue Management** - Real-time digital queue with smart routing
- **Poor Chronic Disease Follow-up** - Automated follow-up scheduling with SMS reminders
- **Limited Rural Access** - SMS-based and offline-supported booking system
- **Maternal Health Gaps** - Integrated pregnancy tracking and vaccination reminders

**Built for:** NIST Tech Carnival 2.0 Hackathon | Theme: Sustainable Healthcare Solutions  

**Development Team (CIVIX):**
- **Leader:** Youvraj Syangtan
- **Members:** Narayan Adhikari, Roshan Timalsing, Abiral Chamling Rai

### ✨ Key Features

1. **Digital Pre-Triage** - Quick health assessment before token booking
2. **Smart Queue Management** - Real-time queue status with estimated wait times
3. **Chronic Disease Monitoring** - Automatic follow-up scheduling and SMS reminders
4. **Maternal Health Tracking** - Pregnancy monitoring and vaccination reminders
5. **Offline Support** - Staff-assisted booking for rural and low-tech users
6. **SMS Integration** - No-internet-needed notifications for sustainability
7. **Bilingual Interface** - Complete English and Nepali support throughout
8. **Hospital Dashboard** - Real-time analytics and staff management tools
9. **Patient Tracking** - Status updates and health history
10. **Notification System** - SMS, email, and in-app alerts

### 🛠️ Technology Stack

| Component | Technology |
|-----------|-----------|
| **Frontend** | PHP, HTML5, Bootstrap 5, JavaScript |
| **Backend** | PHP 7.4+ (OOP Architecture) |
| **Database** | MySQL 5.7+ (UTF8MB4 for Nepali) |
| **SMS Service** | Sparrow SMS / Twilio (configurable) |
| **Session Management** | Native PHP Sessions |
| **Language Support** | English (en), Nepali (नेपाली) |


### 📁 Project Structure

```
smarthealth_nepal/
│
├── frontend/                           # Public-facing Patient Application
│   ├── public/
│   │   ├── index.php                  # Frontend entry point
│   │   ├── assets/                    # Static resources
│   │   │   ├── css/                   # Stylesheets
│   │   │   ├── js/                    # Client-side scripts
│   │   │   └── images/                # Images & icons
│   │   └── data/                      # Static JSON data
│   └── views/
│       ├── layouts/                   # Reusable templates (header, footer)
│       ├── home/                      # Home & landing pages
│       ├── auth/                      # Login/registration views
│       ├── token/                     # Token booking & status tracking
│       ├── tracking/                  # Chronic & maternal health tracking
│       ├── services/                  # Available services listing
│       ├── profile/                   # User profile management
│       ├── dashboard/                 # Patient dashboard
│       ├── components/                # Reusable UI components
│       └── offline/                   # SMS-based & assisted booking
│
├── backend/                            # Core Business Logic & APIs
│   ├── init.php                       # System initialization & bootstrap
│   ├── otp_debug.php                  # OTP debugging utilities
│   ├── test_hospitals_api.php         # API testing script
│   ├── config/
│   │   ├── app.php                    # Application settings
│   │   ├── database.php               # Database connection config
│   │   ├── language.php               # Language configuration
│   │   └── sms.php                    # SMS provider settings
│   ├── lang/
│   │   ├── en.php                     # English translations
│   │   └── ne.php                     # Nepali translations (नेपाली)
│   ├── controllers/
│   │   ├── AuthController.php         # Authentication logic
│   │   ├── TokenController.php        # Token management
│   │   ├── UserProfileController.php  # User profile operations
│   │   ├── TrackingController.php     # Health tracking
│   │   ├── ChronicController.php      # Chronic disease management
│   │   ├── MaternalController.php     # Pregnancy & maternal health
│   │   └── OfflineBookingController.php # Offline booking logic
│   ├── models/
│   │   ├── UserModel.php              # User data operations
│   │   ├── TokenModel.php             # Token & queue operations
│   │   ├── DepartmentModel.php        # Department data management
│   │   ├── HealthRecordModel.php      # Health records storage
│   │   ├── HealthAssessmentModel.php  # Triage assessment data
│   │   ├── NotificationModel.php      # Notification management
│   │   └── TokenModel.php             # Token operations
│   ├── helpers/
│   │   ├── AppointmentSlotHelper.php  # Slot generation & management
│   │   ├── HospitalHelper.php         # Hospital-related utilities
│   │   ├── OTPHelper.php              # OTP generation & verification
│   │   ├── SMSHelper.php              # SMS sending utilities
│   │   ├── TimeHelper.php             # Time/date utilities
│   │   └── TokenHelper.php            # Token operations
│   ├── services/
│   │   ├── SparrowSMSService.php      # Sparrow SMS integration
│   │   └── [Other services]           # Future service integrations
│   ├── api/
│   │   ├── send_otp.php               # OTP sending endpoint
│   │   ├── verify_otp.php             # OTP verification endpoint
│   │   ├── get_token_status.php       # Check token status
│   │   ├── complete_booking.php       # Finalize booking
│   │   ├── get_appointment_slots.php  # Get available slots
│   │   ├── suggest_hospitals.php      # Hospital suggestions
│   │   ├── logout.php                 # Session logout
│   │   ├── get_chronic_diseases.php   # Chronic disease list
│   │   ├── get_maternal_status.php    # Pregnancy status API
│   │   ├── mark_followup_completed.php # Follow-up completion
│   │   └── [Other endpoints]          # Additional APIs
│   └── data/
│       ├── hospitals.json             # Hospital data & departments
│       ├── nepal_districts.json       # Nepal district information
│       └── sample_appointment_slots.json # Sample slot data
│
├── admin/                             # Hospital Staff Admin Panel
│   ├── frontend/
│   │   ├── css/
│   │   ├── js/
│   │   ├── layouts/
│   │   └── views/
│   │       ├── dashboard/             # Staff dashboard
│   │       ├── departments/           # Department management
│   │       ├── staff/                 # Staff management
│   │       ├── tokens/                # Token management
│   │       ├── settings/              # Admin settings
│   │       ├── profile/               # Profile management
│   │       ├── reports/               # Reporting & analytics
│   │       ├── assisted-bookings/     # Offline booking interface
│   │       └── [Other views]
│   ├── backend/
│   │   ├── init.php                   # Admin initialization
│   │   ├── test_credentials.php       # Credential testing
│   │   ├── verify_login.php           # Admin login verification
│   │   ├── config/
│   │   │   ├── auth.php               # Admin authentication
│   │   │   ├── database.php           # Database config
│   │   │   └── [Other configs]
│   │   ├── controllers/
│   │   ├── models/
│   │   ├── lang/
│   │   └── api/
│   │       ├── call_token.php         # Call next token
│   │       ├── complete_token.php     # Mark token complete
│   │       ├── miss_token.php         # Handle missed token
│   │       ├── approve_referral.php   # Approve referral
│   │       ├── reject_referral.php    # Reject referral
│   │       ├── forward_referral.php   # Forward to specialist
│   │       ├── delete_service.php     # Remove service
│   │       ├── update_service.php     # Update service info
│   │       └── [Other APIs]
│   └── hospital/
│       ├── about.php
│       ├── help.php
│       ├── login.php
│       ├── logout.php
│       └── [Hospital-specific pages]
│
├── public/                             # Public Static Resources
│   ├── index.php                      # Routing entry point
│   └── assets/
│       ├── css/
│       ├── js/
│       └── images/
│
├── logs/                              # Application Logs
│   ├── sms.log
│   ├── activity.log
│   └── errors.log
│
├── database/
│   └── smarthealth_nepal.sql          # Complete database schema
│
├── .htaccess                          # Apache routing rules
├── index.php                          # Main application entry
├── README.md                          # This documentation
└── [Configuration files]
```

### ⚙️ Installation & Setup

#### 📋 Prerequisites

- **PHP** 7.4 or higher (with extensions: PDO, MySQL, cURL, JSON)
- **MySQL** 5.7 or higher
- **Apache** with mod_rewrite enabled
- **Development Environment:** XAMPP, WAMP, LAMP stack, or similar
- **Text Editor/IDE:** VS Code, PhpStorm, Sublime Text
- **Git** (optional, for version control)

#### 🚀 Quick Start (5 Steps)

##### Step 1: Clone/Copy Project Files

Copy the `smarthealth_nepal` folder to your web root:

**Windows (XAMPP):**
```
C:\xampp\htdocs\smarthealth_nepal\
```

**Windows (WAMP):**
```
C:\wamp\www\smarthealth_nepal\
```

**Linux/Mac:**
```
/var/www/html/smarthealth_nepal/
```

##### Step 2: Create Database

**Option A - Using phpMyAdmin:**
1. Open `http://localhost/phpmyadmin`
2. Click "New" → Database
3. Name: `smarthealth_nepal`
4. Charset: `utf8mb4_unicode_ci`
5. Click "Create"
6. Go to "Import" tab
7. Choose `database/smarthealth_nepal.sql` file
8. Click "Import"

**Option B - Using MySQL Command Line:**
```bash
mysql -u root -p smarthealth_nepal < database/smarthealth_nepal.sql
```

**Option C - Using Terminal:**
```bash
cd c:\xampp\htdocs\smarthealth_nepal
mysql -u root -p < database/smarthealth_nepal.sql
```

##### Step 3: Configure Database Connection

Edit `backend/config/database.php`:

```php
<?php
// Database Configuration
define('DB_HOST', 'localhost');      // Database host
define('DB_USER', 'root');           // Database username
define('DB_PASSWORD', '');           // Database password (empty for default XAMPP)
define('DB_NAME', 'smarthealth_nepal'); // Database name
define('DB_PORT', 3306);             // Database port
define('DB_CHARSET', 'utf8mb4');     // Character set for Nepali support

// Optional: Connection timeout
define('DB_TIMEOUT', 10);
?>
```

##### Step 4: Configure SMS Service (Optional)

Create/Edit `backend/config/sms.php`:

```php
<?php
// SMS Provider Configuration

// Choose SMS provider: 'sparrow', 'twilio', or 'debug'
define('SMS_PROVIDER', 'debug'); // Use 'debug' for testing without actual SMS

// Sparrow SMS Configuration
define('SMS_API_KEY', 'your_sparrow_api_key');
define('SMS_USERNAME', 'your_sparrow_username');

// Twilio Configuration (optional)
define('TWILIO_ACCOUNT_SID', 'your_twilio_sid');
define('TWILIO_AUTH_TOKEN', 'your_twilio_token');
define('TWILIO_PHONE', '+1234567890');

// SMS Settings
define('SMS_DEFAULT_SENDER', 'SmartHealth');
define('SMS_DEBUG_MODE', true); // Log SMS attempts
define('SMS_LOG_FILE', 'logs/sms.log');
?>
```

##### Step 5: Start Application

1. **Start XAMPP/WAMP:**
   - Start Apache service
   - Start MySQL service

2. **Access Application:**
   - **Frontend (Patients):** `http://localhost/smarthealth_nepal/`
   - **Admin Panel:** `http://localhost/smarthealth_nepal/admin/`
   - **Hospital Portal:** `http://localhost/smarthealth_nepal/admin/hospital/`

3. **Verify Installation:**
   - Check if homepage loads
   - Test language switching (English/Nepali)
   - Verify database connection

#### 🔧 Advanced Configuration

**Application Settings** (`backend/config/app.php`):
```php
<?php
// Application Mode
define('APP_ENV', 'development'); // 'development' or 'production'
define('DEBUG_MODE', true);

// Session Configuration
define('SESSION_TIMEOUT', 3600); // 1 hour
define('SESSION_REFRESH', 1800); // 30 minutes

// OTP Settings
define('OTP_LENGTH', 6);
define('OTP_VALIDITY', 600); // 10 minutes

// File Upload Limits
define('MAX_UPLOAD_SIZE', 5242880); // 5MB
define('ALLOWED_EXTENSIONS', ['pdf', 'jpg', 'png', 'doc', 'docx']);
?>
```

**Language Configuration** (`backend/config/language.php`):
```php
<?php
// Default language
define('DEFAULT_LANGUAGE', 'en'); // 'en' for English, 'ne' for Nepali

// Supported languages
define('SUPPORTED_LANGUAGES', ['en' => 'English', 'ne' => 'नेपाली']);

// Character encoding
define('CHARSET', 'UTF-8');
?>
```

### 📱 Usage Guide

#### 👥 For Patients (Public Frontend)

**1. Initial Login:**
- Visit home page: `http://localhost/smarthealth_nepal/`
- Enter phone number (Format: `98XXXXXXXX` or `+977XXXXXXXXXX`)
- Click "Send OTP"
- Receive one-time password via SMS (or check logs in debug mode)
- Enter OTP and verify
- Access patient dashboard

**2. Book Hospital Token:**
- Go to "Book Token" section
- Answer health triage questions (4-5 quick questions)
- Select preferred hospital
- Choose department/service
- Verify appointment details
- Receive token number via SMS and on-screen
- Save token for reference

**3. Track Token Status:**
- View current token status in dashboard
- See current queue position
- Check estimated wait time
- Receive SMS updates as token moves
- Get called notification when approaching counter

**4. Chronic Disease Management:**
- Go to "My Health" or "Chronic Diseases"
- Register existing chronic conditions
- View automatic follow-up schedules
- Receive SMS reminders before follow-up dates
- Mark follow-ups as completed
- Track treatment history

**5. Maternal Health (Pregnancy Tracking):**
- Register pregnancy status
- Input expected delivery date
- Track antenatal appointments
- Receive vaccination reminders
- Get maternal health articles
- Schedule checkups

**6. View Health History:**
- Access complete health records
- View past appointments
- Check previous diagnoses
- Review lab results
- Download health reports

#### 🏥 For Hospital Staff (Admin Panel)

**Access Admin Panel:** `http://localhost/smarthealth_nepal/admin/`

**1. Staff Dashboard:**
- Real-time view of all active tokens
- Department queue status
- Current wait times
- Patient flow analytics
- Quick action buttons

**2. Token Management:**
- **View Active Queue:** See all queued patients
- **Call Token:** Call next patient to counter
- **Mark Complete:** Mark patient service as completed
- **Handle No-Show:** Manage patients who missed their token
- **Reschedule:** Reassign patient to different time/date
- **Print Report:** Generate token usage reports

**3. User & Patient Management:**
- Search patient by phone/ID
- View patient profile and history
- Update patient information
- Access health records
- View appointment history
- Track follow-up status

**4. Service & Department Management:**
- Add/Edit departments
- Set department capacity
- Manage available services
- Update service availability
- View department statistics
- Manage staff assignments

**5. Referral & Forwarding:**
- View pending referrals
- **Approve:** Accept referral to department
- **Reject:** Decline referral with reason
- **Forward:** Send to specialist/different hospital
- Track referral status
- Generate referral reports

**6. Staff Management:**
- Add new staff members
- Assign roles and permissions
- Manage user accounts
- Set working hours
- Track staff activity
- Manage staff passwords

**7. Reports & Analytics:**
- **Daily Reports:** Token count, wait times, no-shows
- **Department Reports:** Busiest times, capacity usage
- **Patient Reports:** Demographics, chronic diseases
- **Revenue Reports:** Service-wise statistics
- Export to CSV/PDF
- Custom date range filtering

**8. Settings:**
- Hospital information
- Department configuration
- Service management
- Staff management
- System preferences
- Backup management

#### 🔄 SMS-Based Booking (For Offline Users)

**Send SMS in format:**
```
BOOK [DEPARTMENT] [HOSPITAL]
Recipient: Hospital Phone Number
```

**Example:**
```
BOOK CARDIOLOGY KATHMANDU_HOSPITAL
```

**System Response:**
- Confirmation SMS
- Token number
- Estimated wait time
- Hospital address

#### 🌐 Language Support

**Bilingual Interface:**
- **English:** Click "EN" button (top right)
- **Nepali:** Click "NE" button for नेपाली

**Supported Areas:**
- All menu items and buttons
- Form labels and placeholders
- Error messages and alerts
- SMS and email content
- Reports and documents

### 🌍 Language Support & Internationalization

**Bilingual System:**
- **English (en):** Complete English interface
- **Nepali (नेपाली - ne):** Full Nepali translation

**Translatable Elements:**
- User interface strings
- Form labels and placeholders
- Error messages and alerts
- Validation messages
- SMS and email templates
- System notifications
- Reports and documents

**Language Files Location:**
```
backend/lang/
├── en.php          # English translations
└── ne.php          # Nepali translations (नेपाली)
```

**Adding New Language (e.g., Spanish):**

1. Create new file: `backend/lang/es.php`
2. Copy structure from `backend/lang/en.php`
3. Translate all key-value pairs
4. Update `backend/config/language.php`:
```php
define('SUPPORTED_LANGUAGES', [
    'en' => 'English',
    'ne' => 'नेपाली',
    'es' => 'Español'  // Add new language
]);
```
5. Restart application

**Language Selection:**
- Default language: English
- User can switch via UI button
- Language preference saved in session
- Automatically respects browser language on first visit

### 💾 Database Schema

**Key Tables & Descriptions:**

| Table | Purpose |
|-------|---------|
| `users` | Patient profiles, contact info, registration |
| `tokens` | Queue tokens with status and timestamps |
| `token_triage` | Health assessment responses from pre-triage |
| `departments` | Hospital departments and specialties |
| `services` | Available services and procedures |
| `chronic_diseases` | Patient chronic disease records |
| `maternal_health` | Pregnancy tracking and vaccination records |
| `appointments` | Scheduled appointments and follow-ups |
| `notifications` | SMS and email notifications sent/pending |
| `referrals` | Patient referrals and forwarding |
| `admins` | Hospital staff accounts and roles |
| `admin_logs` | Admin activity tracking |
| `health_records` | Medical history and diagnosis records |

**Sample Query to View Schema:**
```sql
-- Show all tables
SHOW TABLES FROM smarthealth_nepal;

-- Show users table structure
DESCRIBE users;

-- Check active tokens
SELECT * FROM tokens WHERE status = 'active';

-- View patient tokens by date
SELECT * FROM tokens WHERE DATE(created_at) = CURDATE();
```

### 🔌 API Endpoints Reference

**Authentication Endpoints:**
```
POST /backend/api/send_otp.php
  - Params: phone
  - Response: {status, message, otp_sent}

POST /backend/api/verify_otp.php
  - Params: phone, otp
  - Response: {status, user_id, token}

POST /backend/api/logout.php
  - Response: {status}
```

**Token Management:**
```
GET /backend/api/get_token_status.php?token_id=X
  - Response: {status, position, wait_time}

POST /backend/api/complete_booking.php
  - Params: hospital_id, department_id
  - Response: {token_number, status}
```

**Health Management:**
```
GET /backend/api/get_chronic_diseases.php?user_id=X
  - Response: {diseases, follow_ups}

GET /backend/api/get_maternal_status.php?user_id=X
  - Response: {pregnancy_status, due_date, appointments}

POST /backend/api/mark_followup_completed.php
  - Params: followup_id
  - Response: {status, next_followup_date}
```

**Hospital & Services:**
```
GET /backend/api/suggest_hospitals.php?location=kathmandu
  - Response: {hospitals, distance, services}

GET /backend/api/get_appointment_slots.php?hospital_id=X&date=YYYY-MM-DD
  - Response: {available_slots, doctor_name}
```

**Admin Endpoints:**
```
POST /admin/backend/api/call_token.php
  - Params: token_id
  - Response: {status, patient_info}

POST /admin/backend/api/complete_token.php
  - Params: token_id
  - Response: {status}

POST /admin/backend/api/approve_referral.php
  - Params: referral_id, department_id
  - Response: {status, new_appointment}

POST /admin/backend/api/forward_referral.php
  - Params: referral_id, hospital_id
  - Response: {status, forwarded_to}
```

### ⚡ Configuration Files Reference

**Core Application Configuration:**

| File | Purpose | Location |
|------|---------|----------|
| `server.conf.php` | Server environment settings | Root |
| `database.php` | Database connection details | `backend/config/` |
| `app.php` | Application settings & constants | `backend/config/` |
| `sms.php` | SMS provider configuration | `backend/config/` |
| `language.php` | Language & locale settings | `backend/config/` |

**Admin Configuration:**

| File | Purpose | Location |
|------|---------|----------|
| `auth.php` | Admin authentication settings | `admin/backend/config/` |
| `language.php` | Admin language preferences | `admin/backend/config/` |

**Sample Configuration Values:**

```php
// backend/config/app.php
define('APP_NAME', 'SmartHealth Nepal');
define('APP_VERSION', '1.0');
define('APP_ENV', 'development');
define('SESSION_TIMEOUT', 3600);
define('OTP_VALIDITY', 600);
define('OTP_LENGTH', 6);

// backend/config/language.php
define('DEFAULT_LANGUAGE', 'en');
define('CHARSET', 'UTF-8');

// backend/config/database.php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'smarthealth_nepal');
define('DB_CHARSET', 'utf8mb4');

// backend/config/sms.php
define('SMS_PROVIDER', 'debug');
define('SMS_API_KEY', '');
define('SMS_LOG_FILE', 'logs/sms.log');
```

### 🔐 Security Guidelines

**Before Production Deployment:**

1. **Database Security:**
   ```php
   // backend/config/database.php
   define('DB_USER', 'smarthealth_user');  // Create limited user
   define('DB_PASSWORD', 'strong_password'); // Use strong password
   define('DB_HOST', 'localhost');         // Restrict to localhost
   ```

2. **HTTPS/SSL Configuration:**
   ```apache
   # In .htaccess
   RewriteCond %{HTTPS} off
   RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
   ```

3. **Error Display:**
   ```php
   // backend/init.php
   ini_set('display_errors', 0);           // Disable in production
   error_reporting(E_ALL);                  // Log all errors
   ini_set('log_errors', 1);
   ini_set('error_log', 'logs/errors.log');
   ```

4. **Session Security:**
   ```php
   session_set_cookie_params([
       'secure' => true,      // HTTPS only
       'httponly' => true,    // JavaScript can't access
       'samesite' => 'Strict' // CSRF protection
   ]);
   ```

5. **Password Hashing:**
   ```php
   // Use bcrypt for new passwords
   $hashed = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
   // Verify passwords
   password_verify($input_password, $hashed);
   ```

6. **Input Validation:**
   ```php
   // Validate phone numbers
   $phone = preg_replace('/[^0-9+]/', '', $phone);
   
   // Sanitize input
   $name = trim(htmlspecialchars($name));
   
   // Validate email
   filter_var($email, FILTER_VALIDATE_EMAIL);
   ```

7. **SQL Injection Prevention:**
   ```php
   // Use prepared statements (PDO)
   $stmt = $pdo->prepare("SELECT * FROM users WHERE phone = ?");
   $stmt->execute([$phone]);
   ```

8. **CSRF Token Implementation:**
   ```php
   // Generate token
   $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
   
   // Verify in form
   if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
       die('CSRF token mismatch');
   }
   ```

9. **File Upload Security:**
   ```php
   $allowed = ['pdf', 'jpg', 'png'];
   $ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
   
   if (!in_array(strtolower($ext), $allowed)) {
       die('Invalid file type');
   }
   ```

10. **API Rate Limiting:**
    ```php
    // Limit OTP requests to 3 per 5 minutes
    $attempts = $redis->incr("otp_attempts:$phone");
    if ($attempts > 3) {
        die('Too many attempts');
    }
    $redis->expire("otp_attempts:$phone", 300);
    ```

### 🛠️ Development Tips & Debugging

**Debugging OTP Issues:**
```bash
# Check OTP logs
tail -f logs/sms.log

# View recent OTP attempts
cat logs/otp_debug.log | tail -20
```

**Database Testing:**
```php
// backend/test_credentials.php
// Run this file to verify database connectivity
// Access: http://localhost/smarthealth_nepal/backend/test_credentials.php
```

**SMS Testing:**
```php
// backend/config/sms.php
define('SMS_PROVIDER', 'debug');  // Send to logs instead of SMS gateway
// Check logs/sms.log for SMS attempts
```

**API Testing:**
```bash
# Test OTP endpoint
curl -X POST http://localhost/smarthealth_nepal/backend/api/send_otp.php \
  -d "phone=9841000000"

# Test hospital suggestion
curl "http://localhost/smarthealth_nepal/backend/api/suggest_hospitals.php?location=kathmandu"
```

**Browser Developer Tools:**
- F12 to open Developer Tools
- **Console tab:** Check for JavaScript errors
- **Network tab:** Monitor API requests
- **Storage tab:** View session cookies

**PHP Error Logging:**
```php
// Enable detailed error logging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/errors.log');

// Log messages
error_log("Debug message: " . var_export($data, true));
```

**Common Debug Phone Numbers:**
```
Patient: 9841000000
Admin: 9841111111
Test mode: 9899999999
```

**Fixed OTP for Testing:**
```
In debug mode, OTP is always: 123456
```

### ✅ Testing Checklist

- [ ] Database connection working
- [ ] SMS logging (in debug mode)
- [ ] Patient registration flow
- [ ] Token booking process
- [ ] Token status tracking
- [ ] Admin login and dashboard
- [ ] Token call/complete workflow
- [ ] Language switching (EN/NE)
- [ ] Chronic disease registration
- [ ] Maternal health tracking
- [ ] SMS notifications received
- [ ] Offline booking (SMS)
- [ ] Referral approval/rejection

### 🔧 Troubleshooting Guide

**Issue 1: Database Connection Error**
```
Error: "Could not connect to database"
```
**Solutions:**
- Verify MySQL is running
- Check credentials in `backend/config/database.php`
- Ensure database `smarthealth_nepal` exists
- Run: `mysql -u root -p smarthealth_nepal < database/smarthealth_nepal.sql`
- Test connection: `backend/test_credentials.php`

**Issue 2: Blank Page or 500 Error**
```
Possible causes: PHP errors, missing includes
```
**Solutions:**
- Check PHP error logs: `logs/errors.log`
- Enable error display in `backend/init.php`
- Verify all files are in correct directories
- Check file permissions (should be readable)
- Run on PHP 7.4 or higher

**Issue 3: SMS Not Sending**
```
Error: SMS not received on phone
```
**Solutions:**
- Check SMS configuration: `backend/config/sms.php`
- View SMS logs: `logs/sms.log`
- Verify SMS provider API credentials
- Check SMS provider account balance
- Use debug mode to test: `define('SMS_PROVIDER', 'debug');`
- Whitelist phone numbers in SMS gateway

**Issue 4: Language Switch Not Working**
```
Page stays in English after clicking Nepali button
```
**Solutions:**
- Verify language files exist: `backend/lang/en.php`, `backend/lang/ne.php`
- Check `backend/config/language.php` has both languages
- Clear browser cache
- Check if session is being started: `session_start();`
- Verify $_SESSION['language'] is being set

**Issue 5: Login/OTP Verification fails**
```
Error: "Invalid OTP" or "Phone not found"
```
**Solutions:**
- Use correct phone format: `98XXXXXXXX` or `+977XXXXXXXXXX`
- In debug mode, use: `9841000000` with OTP: `123456`
- Check if user exists in database: 
  ```sql
  SELECT * FROM users WHERE phone = '9841000000';
  ```
- Verify OTP validity time hasn't expired (default: 10 minutes)
- Check `backend/config/app.php` for OTP settings

**Issue 6: CSS/JS Files Not Loading**
```
Page looks broken, no styling
```
**Solutions:**
- Check file paths in HTML: should be `/smarthealth_nepal/...`
- Verify files exist in `frontend/public/assets/`
- Clear browser cache (Ctrl+Shift+Del)
- Check browser console for 404 errors
- Verify Apache mod_rewrite is enabled

**Issue 7: Token Booking/Complete Fails**
```
Error: "Cannot complete booking" or "Token not found"
```
**Solutions:**
- Verify token exists in database
- Check token status: should be 'active'
- Verify hospital and department IDs are correct
- Check token hasn't already been completed
- Review admin logs for detailed error

**Issue 8: Admin Panel Access Denied**
```
Error: "Unauthorized" or "Invalid credentials"
```
**Solutions:**
- Verify admin account exists: `admin/backend/verify_login.php`
- Check username and password are correct
- Verify admin role/permissions in database
- Check session timeout setting
- Clear cookies and try again

**Issue 9: File Upload Fails**
```
Error: "File not uploaded" or "Invalid file type"
```
**Solutions:**
- Check file size is under limit (default: 5MB)
- Verify file format is allowed (pdf, jpg, png, doc, docx)
- Check `logs/` directory has write permissions
- Verify `$_FILES['file']` is not empty
- Check `php.ini` upload settings

**Issue 10: Performance Issues/Slow Loading**
```
Pages load slowly, timeouts
```
**Solutions:**
- Check database performance:
  ```sql
  -- Check for slow queries
  SET GLOBAL slow_query_log = 'ON';
  ```
- Optimize database indexes
- Enable query caching if using MySQL 5.7
- Check server CPU/memory usage
- Minimize concurrent requests
- Compress CSS/JS files

### 🚀 Performance Optimization

**Database Optimization:**
```sql
-- Add indexes for frequently queried fields
CREATE INDEX idx_phone ON users(phone);
CREATE INDEX idx_token_status ON tokens(status);
CREATE INDEX idx_department_id ON tokens(department_id);
CREATE INDEX idx_created_at ON tokens(created_at);

-- Check index usage
SELECT * FROM sys.statements_with_runtimes_in_95th_percentile;
```

**Code Optimization:**
```php
// Cache department data (5 minutes)
$cache_key = 'departments_list';
if (!$departments = apcu_fetch($cache_key)) {
    $departments = $model->getAllDepartments();
    apcu_store($cache_key, $departments, 300);
}

// Batch SMS operations
$phones = ['9841000000', '9841111111'];
foreach ($phones as $phone) {
    queue_sms($phone, $message); // Queue instead of sending immediately
}
```

**Asset Optimization:**
```html
<!-- Minify CSS/JS in production -->
<link rel="stylesheet" href="assets/css/main.min.css">
<script src="assets/js/app.min.js"></script>

<!-- Lazy load images -->
<img src="image.jpg" loading="lazy" alt="Description">

<!-- Enable gzip compression in .htaccess -->
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/plain text/css text/javascript
</IfModule>
```

### 📈 Scaling the System

**For Larger Hospitals (1000+ daily tokens):**

1. **Increase Database Memory:**
   ```
   innodb_buffer_pool_size = 2G
   query_cache_size = 256M
   ```

2. **Use Load Balancer:**
   - Distribute traffic across multiple servers
   - Use Nginx or HAProxy

3. **Implement Caching Layer:**
   - Redis for session caching
   - Memcached for frequently accessed data

4. **Database Replication:**
   - Master-slave MySQL setup
   - Separate read/write operations

5. **Horizontal Scaling:**
   - Multiple web servers behind load balancer
   - Shared database cluster

### 🌟 Future Enhancements & Roadmap

**Phase 2 Features (Under Development):**
- [ ] Mobile App (iOS/Android - React Native)
- [ ] Advanced Analytics Dashboard
- [ ] Telemedicine Consultation Module
- [ ] Video Call Integration
- [ ] Digital Prescription System
- [ ] Integration with Government Health Systems
- [ ] Appointment Scheduling System
- [ ] AI-Based Symptom Checker
- [ ] Patient Education Video Library
- [ ] Multi-Hospital Network Support
- [ ] Doctor Online Portal
- [ ] Medical Record Digitization
- [ ] Insurance Integration
- [ ] Automatic Follow-up Scheduling

**Phase 3 Features (Planned):**
- National Health Records Integration
- Government EHR System Connection
- Health Ministry Reporting Dashboard
- Vaccination Certificate Generation
- Digital Lab Reports
- Prescription QR Codes
- Voice-Based OTP (for illiterate users)
- Chatbot Support (Nepali)
- WhatsApp Integration
- Push Notifications

### 🤝 Contributing to SmartHealth Nepal

We welcome contributions from the community!

**Steps to Contribute:**

1. **Fork the Repository:**
   ```bash
   git clone https://github.com/civix/smarthealth_nepal.git
   cd smarthealth_nepal
   ```

2. **Create Feature Branch:**
   ```bash
   git checkout -b feature/your-feature-name
   ```

3. **Make Your Changes:**
   - Follow existing code style
   - Add comments for complex logic
   - Test your changes thoroughly
   - Update documentation

4. **Commit Changes:**
   ```bash
   git add .
   git commit -m "Add: Description of your changes"
   ```

5. **Push to Branch:**
   ```bash
   git push origin feature/your-feature-name
   ```

6. **Submit Pull Request:**
   - Go to GitHub and create PR
   - Describe your changes
   - Reference any issues

**Contribution Guidelines:**
- Follow PSR-12 PHP coding standards
- Write self-documenting code
- Include comments for non-obvious code
- Test on multiple PHP versions
- Update README.md if adding features
- Respect Nepal's healthcare regulations

**Reporting Issues:**
- Include PHP version and MySQL version
- Provide error messages and screenshots
- List steps to reproduce
- Suggest possible solutions

### 📄 License

This project is developed for educational and humanitarian purposes under the **MIT License**.

```
MIT License

Copyright (c) 2026 CIVIX Team

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
```

**For full license text, see LICENSE file.**

### 📞 Support & Contact

**Getting Help:**

- **Technical Issues:** Open an issue on GitHub
- **Documentation:** Check this README first
- **Email Support:** support@smarthealth-nepal.info
- **Phone Support:** +977-1-XXXXXX (Kathmandu, Nepal)
- **Office:** Nepal Health Tech Center, Kathmandu, Nepal

**Quick Links:**
- [Project Website](#) - Coming Soon
- [Issue Tracker](#) - Report bugs and request features
- [Documentation Wiki](#) - Detailed guides
- [API Documentation](#) - For developers

**Team Contact:**
- **Project Lead (Youvraj Syangtan):** youvraj@smarthealth-nepal.info
- **Technical Lead (Narayan Adhikari):** narayan@smarthealth-nepal.info
- **Support Email:** support@smarthealth-nepal.info

### 🙏 Acknowledgments & Credits

**We acknowledge and thank:**

- **Ministry of Health & Population (MoHP), Nepal** - For healthcare guidelines
- **World Health Organization (WHO)** - Health system frameworks
- **NIST Kathmandu** - For tech carnival platform
- **Nepal Software Association** - Professional guidance
- **Local Healthcare Professionals** - Domain expertise
- **All Beta Testers** - For invaluable feedback
- **Open Source Community** - For excellent libraries and frameworks

**Inspired by:**
- WHO Health System Profile for Nepal
- Successful e-health implementations in SAARC countries
- Patient feedback and healthcare worker insights
- Government health system requirements

### 📊 Project Statistics

- **Development Time:** 3 months (NIST Tech Carnival 2.0)
- **Team Size:** 4 developers
- **Code Lines:** 8,000+ PHP/JavaScript
- **Database Tables:** 12+ tables
- **Languages Supported:** 2 (English, Nepali)
- **API Endpoints:** 20+ endpoints
- **Mobile Responsive:** Yes (Bootstrap 5)
- **Tested on:** Chrome, Firefox, Safari, Edge

### 🔄 Version History

**v1.0 (Current - February 13, 2026):**
- Initial MVP release
- Patient token booking system
- Hospital staff dashboard
- Chronic disease tracking
- Maternal health monitoring
- Bilingual interface (EN/NE)
- SMS integration
- Offline booking support

**v0.9 (Beta - February 11, 2026):**
- Testing phase
- Bug fixes and optimization

**v0.5 (Alpha - February 5, 2026):**
- Core features implementation
- Database schema finalized

### 📚 Documentation Index

- [Installation Guide](#-installation--setup)
- [Configuration](#-configuration-files-reference)
- [Usage Guide](#-usage-guide)
- [API Documentation](#-api-endpoints-reference)
- [Database Schema](#-database-schema)
- [Troubleshooting](#-troubleshooting-guide)
- [Security Guidelines](#-security-guidelines)
- [Performance Tips](#-performance-optimization)

### 🎓 Learning Resources

**For PHP Development:**
- [PHP Official Documentation](https://www.php.net/docs.php)
- [PSR Standards](https://www.php-fig.org/psr/)
- [OOP in PHP](https://www.php.net/manual/en/language.oop5.php)

**For Web Development:**
- [Bootstrap Documentation](https://getbootstrap.com/docs)
- [JavaScript MDN](https://developer.mozilla.org/en-US/docs/Web/JavaScript)
- [RESTful API Best Practices](https://restfulapi.net/)

**For Healthcare IT:**
- [HL7 Standards](https://www.hl7.org/)
- [HIPAA Compliance](https://www.hhs.gov/hipaa/)
- [Nepal Health Policy](https://pib.gov.in/newsite/) - Reference

---

## 📝 README Information

<table>
<tr>
<td><strong>Last Updated</strong></td>
<td>February 13, 2026</td>
</tr>
<tr>
<td><strong>Version</strong></td>
<td>1.0 (MVP)</td>
</tr>
<tr>
<td><strong>Status</strong></td>
<td>✅ Development Complete - Ready for Testing</td>
</tr>
<tr>
<td><strong>Production Ready</strong></td>
<td>⚠️ With Security Configuration (See Security Guidelines)</td>
</tr>
<tr>
<td><strong>Maintained By</strong></td>
<td>CIVIX Team</td>
</tr>
<tr>
<td><strong>License</strong></td>
<td>MIT License</td>
</tr>
</table>

---

## 🎉 Thank You!

Thank you for using SmartHealth Nepal. We hope this system helps improve healthcare access and patient care in Nepal.

**Together, we're making healthcare smarter and more accessible for everyone! 🏥✨**

---

*For the latest updates, feature requests, and community discussions, please visit our GitHub repository.*
