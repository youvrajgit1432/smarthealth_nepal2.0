# SmartHealth Nepal - Installation & Quick Start Guide

## Overview
SmartHealth Nepal is a comprehensive digital healthcare management system designed for government hospitals in Nepal. It provides real-time queue management, chronic disease tracking, maternal health monitoring, and offline SMS-based booking.

## Features
- **Queue Management**: Real-time token tracking with priority-based system
- **Bilingual Support**: English and Nepali language support throughout
- **Chronic Disease Tracking**: Follow-up management for chronic conditions
- **Maternal Health Monitoring**: Pregnancy tracking with antenatal visit scheduling
- **Offline SMS Booking**: SMS-based token booking for areas without internet
- **Admin Dashboard**: Comprehensive management interface for hospital staff
- **Referral Management**: Patient referral routing between departments

## System Requirements
- PHP 7.4 or higher
- MySQL/MariaDB 5.7 or higher
- Apache with mod_rewrite enabled
- 500MB disk space minimum

## Installation Steps

### 1. Database Setup
Run the setup script to initialize the database:
```
http://localhost/smarthealth_nepal/setup.php
```

This will:
- Create the `smarthealth` database
- Create all required tables
- Insert sample data
- Create demo admin user

**Demo Admin Credentials:**
- Email: `admin@smarthealth.local`
- Password: `admin123`

### 2. Access Points

**Public Patient Portal:**
- URL: `http://localhost/smarthealth_nepal/`
- Features:
  - Book tokens for departments
  - Check real-time token status
  - Track chronic disease follow-ups
  - Monitor maternal health

**Admin Panel:**
- URL: `http://localhost/smarthealth_nepal/admin/`
- Features:
  - Dashboard with statistics
  - Active token management
  - User management
  - Department/office management
  - Service and referral management

## Project Structure

```
smarthealth_nepal/
├── admin/                          # Admin system
│   ├── backend/                   # Admin backend logic
│   │   ├── api/                  # Admin API endpoints
│   │   ├── config/               # Admin configuration
│   │   ├── controllers/          # Admin controllers
│   │   ├── lang/                 # Admin language files
│   │   ├── models/               # Admin data models
│   │   └── init.php              # Admin initialization
│   └── frontend/                 # Admin UI
│       ├── css/                  # Admin styles
│       ├── js/                   # Admin scripts
│       └── views/                # Admin pages
│
├── backend/                        # Public backend
│   ├── api/                       # Public API endpoints
│   ├── config/                    # Database & language config
│   ├── controllers/               # Public controllers
│   ├── helpers/                   # Utility helpers
│   ├── lang/                      # Language files
│   └── models/                    # Data models
│
├── frontend/                       # Public UI
│   ├── css/                       # Stylesheets
│   ├── js/                        # JavaScript files
│   ├── public/                    # Public entry point
│   └── views/                     # Page templates
│
├── database/                       # Database schema
│   └── smarthealth_nepal.sql
│
└── setup.php                       # Setup/installation script
```

## API Endpoints

### Public API
- `/backend/api/get_token_status.php` - Real-time token tracking
- `/backend/api/get_chronic_diseases.php` - Chronic disease data
- `/backend/api/get_maternal_status.php` - Pregnancy monitoring
- `/backend/api/sms_book_token.php` - SMS token booking
- `/backend/api/mark_followup_completed.php` - Complete follow-ups

### Admin API
- `/admin/backend/api/call_token.php` - Call next patient
- `/admin/backend/api/complete_token.php` - Mark token complete
- `/admin/backend/api/miss_token.php` - Mark token missed
- `/admin/backend/api/delete_user.php` - Remove user
- `/admin/backend/api/approve_referral.php` - Approve referral
- `/admin/backend/api/reject_referral.php` - Reject referral
- `/admin/backend/api/forward_referral.php` - Forward referral

## Database Tables

1. **users** - Patient/public user accounts
2. **admins** - Hospital staff accounts
3. **tokens** - Queue tokens and appointments
4. **departments** - Hospital departments
5. **chronic_diseases** - Chronic disease tracking
6. **maternal_health** - Pregnancy monitoring
7. **services** - Hospital services
8. **referrals** - Inter-hospital referrals
9. **notifications** - System notifications
10. **health_records** - Patient medical records
11. **offline_bookings** - SMS-based bookings
12. **sms_log** - SMS transaction log

## Key Features

### Real-time Token Tracking (30-second refresh)
- Current token status
- Queue position
- Estimated wait time
- Department load percentage
- Call alerts with notifications

### Health Dashboard
- Chronic disease cards with follow-up tracking
- Progress indicators for disease management
- Maternal health with pregnancy tracking
- Warning sign alerts for high-risk conditions

### Admin Functions
- Active queue management (call, complete, miss)
- Missed token rescheduling
- User profile management
- Department capacity management
- Service approval and referral routing
- Performance analytics and statistics

### Offline Features
- SMS-based token booking
- BOOK FEVER / BREATHING / INJURY / CHRONIC / MATERNAL / GENERAL commands
- OTP-based confirmation
- SMS history and tracking

## Language Support

The system supports two languages:
- **English (en)** - Default language
- **Nepali (ne)** - Full Nepali translation

Language preference can be set during:
- User registration
- Admin login
- Personal settings

## Security Features

- Password hashing with bcrypt
- Prepared statements for SQL injection prevention
- Session-based authentication
- Role-based access control (for future versions)
- OTP verification for SMS bookings
- CSRF protection in forms

## Troubleshooting

### 500 Internal Server Error

1. Check database connection:
   - Verify MySQL is running
   - Check credentials in `backend/config/database.php`
   - Ensure database `smarthealth` exists

2. Check file permissions:
   - Ensure all PHP files are readable
   - Ensure `backend/config/` is accessible

3. Check PHP error logs:
   - Look in Apache error log
   - Check PHP error_log file

### White screen or missing page

1. Verify mod_rewrite is enabled in Apache
2. Check `.htaccess` files exist in root and frontend directories
3. Verify web server document root points to correct directory

### Session not persisting

1. Check session save path is writable
2. Verify cookies are enabled in browser
3. Check session timeout in PHP config (default: 24 minutes)

## Development Notes

### Adding New Pages

1. Create view file in appropriate directory
2. Require init.php at top for database and language access
3. Check admin authentication if admin page
4. Include layout files (header.php, sidebar.php, footer.php)
5. Use `$lang` array for all user-facing strings

### Adding New API Endpoints

1. Create PHP file in respectiveapi directory
2. Set header: `header('Content-Type: application/json');`
3. Return JSON response: `{success: true/false, message: '...', data: {}}`
4. Use prepared statements for database queries
5. Implement proper error handling

### Adding New Features

1. Create model class in `models/` for database operations
2. Create controller in `controllers/` for business logic
3. Create/update views for UI
4. Create API endpoints for AJAX calls
5. Add JavaScript handlers in respective `js/` file
6. Update language files with new strings

## Support & Contact

For issues, feature requests, or contributions:
- Check database is initialized (run setup.php)
- Verify all required files exist
- Check PHP error logs for detailed errors
- Ensure Apache mod_rewrite is enabled

## License

SmartHealth Nepal - Healthcare Queue Management System
© 2026. All rights reserved.

## Changelog

### Version 1.0 (Initial Release)
- Complete queue management system
- Public patient portal
- Admin management interface
- Real-time token tracking
- Chronic disease tracking
- Maternal health monitoring
- Offline SMS booking support
- Bilingual interface (EN/NE)
