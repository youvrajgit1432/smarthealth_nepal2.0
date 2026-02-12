# SmartHealth Nepal
## A Sustainable Digital Patient Flow & Chronic Care Tracking System for Government Hospitals

### Project Overview

SmartHealth Nepal is an innovative healthcare management system designed for government hospitals in Nepal. It addresses critical healthcare challenges including overcrowding, inefficient queue management, poor chronic disease follow-up, and limited rural access through a sustainable, bilingual digital solution.

**Built for:** NIST Tech Carnival 2.0 Hackathon | Theme: Sustainable Healthcare Solutions  
**Team:** CIVIX
- **Leader:** Youvraj Syangtan
- **Members:** Narayan Adhikari, Roshan Timalsing, Abiral Chamling Rai

### Key Features

1. **Digital Pre-Triage** - Quick health assessment before token booking
2. **Smart Queue Management** - Real-time queue status and estimated wait times
3. **Chronic Disease Monitoring** - Automatic follow-up scheduling and SMS reminders
4. **Maternal Health Tracking** - Pregnancy monitoring and vaccination reminders
5. **Offline Support** - Staff-assisted booking for rural and low-tech users
6. **SMS Integration** - No-internet-needed notifications for sustainability
7. **Bilingual Interface** - Full English and Nepali support throughout

### Technology Stack

- **Frontend:** PHP, HTML5, Bootstrap 5, JavaScript
- **Backend:** PHP (OOP)
- **Database:** MySQL (utf8mb4 for Nepali support)
- **SMS:** Sparrow SMS / Twilio (configurable)
- **Language Support:** English (en), Nepali (ne)

### Project Structure

```
smarthealth_nepal/
├── frontend/                           # Public-facing application
│   ├── public/
│   │   ├── index.php                  # Entry point
│   │   └── assets/
│   │       ├── css/main.css           # Main styles
│   │       ├── js/                    # Client-side scripts
│   │       └── images/
│   └── views/
│       ├── layouts/                   # Reusable header/footer
│       ├── home/                      # Home page
│       ├── auth/                      # Login/authentication
│       ├── token/                     # Token booking & status
│       ├── tracking/                  # Chronic & maternal tracking
│       ├── services/                  # Health services
│       └── offline/                   # SMS and assisted booking
│
├── backend/                            # Core business logic
│   ├── init.php                       # Initialization & helpers
│   ├── config/                        # Configuration files
│   ├── lang/                          # Language translation files
│   ├── controllers/                   # Business logic
│   ├── models/                        # Database models
│   ├── helpers/                       # Utility functions
│   └── services/                      # External services (SMS)
│
├── admin/                             # Admin panel for hospital staff
│   ├── frontend/
│   │   └── views/                     # Admin UI pages
│   └── backend/
│       ├── config/                    # Admin config
│       └── lang/                      # Admin translations
│
├── database/
│   └── smarthealth_nepal.sql          # Complete database schema
│
└── README.md                           # This file
```

### Installation & Setup

#### Prerequisites

- PHP 7.4 or higher
- MySQL 5.7 or higher
- XAMPP / WAMP / LAMP stack
- Apache with mod_rewrite enabled

#### Step 1: Clone/Copy Project

Copy the entire `smarthealth_nepal` folder to your web root:
- XAMPP: `C:/xampp/htdocs/smarthealth_nepal/`
- WAMP: `C:/wamp/www/smarthealth_nepal/`
- Linux: `/var/www/html/smarthealth_nepal/`

#### Step 2: Create Database

1. Open phpMyAdmin or MySQL command line
2. Create a new database: `CREATE DATABASE smarthealth;`
3. Import the SQL schema:
   ```sql
   mysql -u root -p smarthealth < database/smarthealth_nepal.sql
   ```
4. Or import via phpMyAdmin: Database → Import → Select `smarthealth_nepal.sql`

#### Step 3: Configure Database Connection

Edit `backend/config/database.php`:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');           // Your database user
define('DB_PASSWORD', '');            // Your database password
define('DB_NAME', 'smarthealth');
define('DB_PORT', 3306);
```

#### Step 4: Configure SMS (Optional)

Edit `backend/config/sms.php` (create if not exists):

```php
<?php
// SMS Configuration - Integrate with Sparrow SMS or Twilio
define('SMS_PROVIDER', 'sparrow'); // or 'twilio'
define('SMS_API_KEY', 'your_api_key');
define('SMS_USERNAME', 'your_username');
?>
```

#### Step 5: Start Application

1. Start Apache and MySQL services
2. Access the application:
   - Frontend: `http://localhost/smarthealth_nepal/`
   - Admin: `http://localhost/smarthealth_nepal/admin/`

### Usage Guide

#### For Patients (Public)

1. **Login:**
   - Visit home page
   - Enter phone number (Nepal format: 98xxxxxxxx)
   - Receive OTP via SMS
   - Verify and access account

2. **Book Token:**
   - Go to "Book Token"
   - Answer health triage questions
   - Select department
   - Receive token number and estimated wait time

3. **Track Status:**
   - View active token status
   - See queue position and wait time
   - Receive SMS updates

4. **Chronic Disease Management:**
   - Register chronic diseases
   - View follow-up dates
   - Receive reminder notifications

5. **Maternal Health:**
   - Track pregnancy status
   - Schedule antenatal checkups
   - Receive vaccination reminders

#### For Hospital Staff (Admin)

1. **Dashboard:**
   - Real-time view of all active tokens
   - Department load indicators
   - Quick access to management functions

2. **Token Management:**
   - View active queue
   - Mark tokens as called/completed
   - Handle missed tokens and rescheduling

3. **User Management:**
   - Search and view patient records
   - Update patient information
   - Track patient history

4. **Service Management:**
   - Approve/forward patient requests
   - Handle referrals
   - Manage service allocations

### Language Support

The system supports dynamic language switching:

- **English (en):** Default language
- **Nepali (ne):** नेपाली

**Supported Areas:**
- All user-facing strings
- Error messages and alerts
- Form labels and buttons
- Email/SMS templates

**To add new language:**
1. Create new file: `backend/lang/xx.php`
2. Add all language keys from `en.php`
3. Update `backend/config/language.php` with new language code

### Database Schema

**Key Tables:**

- **users** - Patient profiles and metadata
- **tokens** - Queue tokens with priority and status
- **departments** - Hospital departments with capacity
- **chronic_diseases** - Patient chronic disease records
- **maternal_health** - Pregnancy and maternal tracking
- **notifications** - SMS and system notifications
- **admins** - Hospital staff accounts
- **tokens_triage** - Health assessment responses

### API Endpoints (Future Enhancement)

RESTful APIs available at:
- `/backend/api/auth/`
- `/backend/api/tokens/`
- `/backend/api/tracking/`
- `/backend/api/departments/`

### Configuration Files

**Main Config:**
- `backend/config/app.php` - Application settings
- `backend/config/database.php` - Database connection
- `backend/config/sms.php` - SMS provider settings
- `backend/config/language.php` - Default language

**Admin Config:**
- `admin/backend/config/auth.php` - Admin authentication
- `admin/backend/config/language.php` - Admin language settings

### Security Note

Before deploying to production:

1. **Set secure database credentials** in `backend/config/database.php`
2. **Enable HTTPS** - Uncomment in `.htaccess`
3. **Disable debug output** - Set `ini_set('display_errors', 0)` 
4. **Configure proper SMS provider** - Remove simulation mode
5. **Set strong admin passwords** - Hash with bcrypt
6. **Enable CSRF protection** - Add token validation in forms
7. **Implement rate limiting** - For OTP requests

### Development Tips

**Debugging:**
- Check SMS logs: `logs/sms.log`
- Check activity logs: `logs/activity.log`
- Enable error reporting on localhost only

**Testing:**
- Default OTP for testing: 123456
- Test phone: 9841000000
- Test with different browsers for bilingual support

### Performance Optimization

- Database indexes on frequently queried fields
- Cached department load data (5-minute TTL)
- Lazy loading for large datasets
- CSS/JS minification in production

### Troubleshooting

**Issue:** "Database connection not established"
- **Solution:** Check database credentials in `backend/config/database.php`

**Issue:** SMS not sending
- **Solution:** Configure SMS provider in `backend/config/sms.php`

**Issue:** Language switch not working
- **Solution:** Ensure session is started and language files exist

**Issue:** Phone login fails
- **Solution:** Check Nepali phone format: 98XXXXXXXX or +977XXXXXXXXXX

### Future Enhancements

1. Mobile app version (React Native)
2. Advanced analytics dashboard
3. Integration with government health systems
4. Telemedicine consultation module
5. Appointment scheduling system
6. Digital prescription management
7. Patient education video library
8. Multi-hospital network support

### Contributing

For improvements and bug fixes:
1. Fork the project
2. Create feature branch
3. Commit changes
4. Submit pull request

### License

This project is developed for educational and humanitarian purposes.

### Support

For technical support, contact:
- **Email:** support@smarthealth-nepal.info
- **Phone:** +977-1-XXXXXX
- **Location:** Kathmandu, Nepal

### Acknowledgments

- Ministry of Health & Population (MoHP), Nepal
- WHO Nepal Health System Profile
- NIST Tech Carnival Organizers
- All healthcare professionals and patients who inspired this solution

---

**Last Updated:** February 11, 2026  
**Version:** 1.0 (MVP)  
**Status:** Development Complete
