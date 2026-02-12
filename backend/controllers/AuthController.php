<?php
/**
 * Authentication Controller
 */

require_once __DIR__ . '/../../backend/init.php';

class AuthController {
    private $db;
    private $userModel;
    
    public function __construct($connection) {
        $this->db = $connection;
        require_once __DIR__ . '/../models/UserModel.php';
        $this->userModel = new UserModel($connection);
    }
    
    /**
     * Send OTP via SMS
     */
    public function sendOTP($phoneNumber) {
        require_once __DIR__ . '/../helpers/SMSHelper.php';
        $smsHelper = new SMSHelper();
        
        // Validate phone
        if (!$smsHelper->isValidPhone($phoneNumber)) {
            return [
                'success' => false,
                'message' => isset($lang) ? $lang['invalid_otp'] : 'Invalid phone number'
            ];
        }
        
        // Format phone
        $phoneNumber = $smsHelper->formatPhone($phoneNumber);
        
        // Generate OTP
        $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        
        // Store in session
        $_SESSION['otp'] = $otp;
        $_SESSION['otp_phone'] = $phoneNumber;
        $_SESSION['otp_timestamp'] = time();
        $_SESSION['otp_attempts'] = 0;
        
        // Send SMS (in real app)
        $smsHelper->send($phoneNumber, "Your SmartHealth Nepal OTP is: $otp. Valid for 10 minutes.");
        
        return [
            'success' => true,
            'message' => 'OTP sent to your phone',
            'debug_otp' => $otp // Remove in production
        ];
    }
    
    /**
     * Verify OTP
     */
    public function verifyOTP($phoneNumber, $otp) {
        global $lang;
        
        // Check session OTP
        if (!isset($_SESSION['otp'])) {
            return [
                'success' => false,
                'message' => $lang['otp_expired'] ?? 'OTP expired or not sent'
            ];
        }
        
        // Check OTP expiry (10 minutes)
        if (time() - $_SESSION['otp_timestamp'] > 600) {
            unset($_SESSION['otp']);
            return [
                'success' => false,
                'message' => $lang['otp_expired'] ?? 'OTP expired'
            ];
        }
        
        // Verify OTP
        if ($_SESSION['otp'] !== $otp) {
            $_SESSION['otp_attempts'] = ($_SESSION['otp_attempts'] ?? 0) + 1;
            
            if ($_SESSION['otp_attempts'] > 3) {
                unset($_SESSION['otp']);
                return [
                    'success' => false,
                    'message' => 'Too many failed attempts'
                ];
            }
            
            return [
                'success' => false,
                'message' => $lang['invalid_otp'] ?? 'Invalid OTP'
            ];
        }
        
        // Get or create user
        $user = $this->userModel->findOrCreateByPhone($phoneNumber);
        
        if ($user) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['phone_number'] = $phoneNumber;
            
            // Clear OTP
            unset($_SESSION['otp']);
            unset($_SESSION['otp_timestamp']);
            unset($_SESSION['otp_attempts']);
            
            return [
                'success' => true,
                'message' => $lang['account_created'] ?? 'Authentication successful',
                'user_id' => $user['id']
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Failed to create/retrieve user'
        ];
    }
    
    /**
     * Logout
     */
    public function logout() {
        session_destroy();
        return [
            'success' => true,
            'message' => 'Logged out successfully'
        ];
    }
    
    /**
     * Check if user is logged in
     */
    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }
    
    /**
     * Get current user
     */
    public function getCurrentUser() {
        if ($this->isLoggedIn()) {
            return $this->userModel->getUserById($_SESSION['user_id']);
        }
        return null;
    }
}

?>
