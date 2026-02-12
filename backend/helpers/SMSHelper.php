<?php
/**
 * SMS Helper
 * Handles SMS sending logic
 */

class SMSHelper {
    private $logFile;
    
    public function __construct() {
        $this->logFile = __DIR__ . '/../../logs/sms.log';
        $this->ensureLogDir();
    }
    
    /**
     * Send SMS
     */
    public function send($phoneNumber, $message, $type = 'notification') {
        // In production, integrate with:
        // - Sparrow SMS (https://www.sparrowsms.com.np/)
        // - Twilio
        // - Other SMS providers
        
        // For now, we'll log it for demonstration
        return $this->logSMS($phoneNumber, $message, $type);
    }
    
    /**
     * Send bulk SMS
     */
    public function sendBulk($phoneNumbers, $message) {
        $success = true;
        
        foreach ($phoneNumbers as $phone) {
            if (!$this->send($phone, $message)) {
                $success = false;
            }
        }
        
        return $success;
    }
    
    /**
     * Log SMS for testing/debugging
     */
    private function logSMS($phoneNumber, $message, $type) {
        $timestamp = date('Y-m-d H:i:s');
        $logEntry = "[$timestamp] [$type] To: $phoneNumber | Message: $message\n";
        
        if (file_put_contents($this->logFile, $logEntry, FILE_APPEND)) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Ensure logs directory exists
     */
    private function ensureLogDir() {
        $logDir = dirname($this->logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
    }
    
    /**
     * Validate phone number
     */
    public function isValidPhone($phoneNumber) {
        // Nepal phone number: starts with 98 or 97 (9 digits) or +977 with 10 digits after
        $patterns = [
            '/^98\d{8}$/',           // 98XXXXXXXX
            '/^97\d{8}$/',           // 97XXXXXXXX
            '/^\+977\d{10}$/',       // +977XXXXXXXXXX
            '/^9[78]\d{8}$/'         // 9XXXXXXXXX
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $phoneNumber)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Format phone number to standard format
     */
    public function formatPhone($phoneNumber) {
        // Remove spaces and special characters
        $phone = preg_replace('/[^0-9+]/', '', $phoneNumber);
        
        // If starts with +977, remove + for Nepal numbers
        if (str_starts_with($phone, '+977')) {
            return substr($phone, 3); // Remove +977, keep just the 10 digits
        }
        
        return $phone;
    }
}

?>
