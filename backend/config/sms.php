<?php
/**
 * SmartHealth Nepal - SMS Configuration
 * --------------------------------------
 * Default provider is 'debug': no paid SMS gateway is required to
 * demonstrate or test the application locally. In debug mode OTP/MPIN
 * codes are stored in `otp_sessions` and can be viewed at:
 *     /smarthealth_nepal/backend/otp_debug.php
 *
 * Switch SMS_PROVIDER to 'sparrow' (and fill in real credentials) only
 * when a live gateway is genuinely wanted. Never commit real secrets.
 */

if (!defined('SMS_PROVIDER'))        define('SMS_PROVIDER', 'debug'); // 'debug' | 'sparrow' | 'twilio'
if (!defined('SMS_DEFAULT_SENDER'))  define('SMS_DEFAULT_SENDER', 'SmartHealth');
if (!defined('SMS_DEBUG_MODE'))      define('SMS_DEBUG_MODE', true);
if (!defined('SMS_LOG_FILE'))        define('SMS_LOG_FILE', __DIR__ . '/../../logs/sms.log');

// Sparrow SMS (only used when SMS_PROVIDER = 'sparrow')
if (!defined('SMS_API_KEY'))         define('SMS_API_KEY', '');
if (!defined('SMS_USERNAME'))        define('SMS_USERNAME', '');

// Twilio (optional)
if (!defined('TWILIO_ACCOUNT_SID'))  define('TWILIO_ACCOUNT_SID', '');
if (!defined('TWILIO_AUTH_TOKEN'))   define('TWILIO_AUTH_TOKEN', '');
if (!defined('TWILIO_PHONE'))        define('TWILIO_PHONE', '');
?>
