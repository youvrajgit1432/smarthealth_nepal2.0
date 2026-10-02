<?php
/**
 * SmartHealth Nepal - Application Configuration
 * ------------------------------------------------
 * These settings mirror the original project's documented options in README.md.
 * The application is intentionally kept in DEVELOPMENT mode for local
 * demonstration/portfolio use.
 */

// Environment: 'development' or 'production'
if (!defined('APP_ENV'))       define('APP_ENV', 'development');
if (!defined('DEBUG_MODE'))    define('DEBUG_MODE', APP_ENV === 'development');

// Base URL (no trailing slash)
if (!defined('BASE_URL'))      define('BASE_URL', '/smarthealth_nepal');

// Session configuration
if (!defined('SESSION_TIMEOUT')) define('SESSION_TIMEOUT', 3600); // 1 hour
if (!defined('SESSION_REFRESH')) define('SESSION_REFRESH', 1800); // 30 minutes

// OTP settings
if (!defined('OTP_LENGTH'))    define('OTP_LENGTH', 6);
if (!defined('OTP_VALIDITY'))  define('OTP_VALIDITY', 600); // 10 minutes

// File upload limits
if (!defined('MAX_UPLOAD_SIZE'))     define('MAX_UPLOAD_SIZE', 5242880); // 5MB
if (!defined('ALLOWED_EXTENSIONS'))  define('ALLOWED_EXTENSIONS', ['pdf', 'jpg', 'png', 'doc', 'docx']);

/**
 * In development mode we surface PHP errors so they can be fixed at the
 * root cause instead of being silently hidden.
 */
if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
    ini_set('display_errors', 0);
}
ini_set('log_errors', 1);

date_default_timezone_set('Asia/Kathmandu');
?>
