<?php
/**
 * OTP Helper - Handles OTP/MPIN generation and verification
 */

class OTPHelper {
    private $db;
    private $smsService;
    
    /**
     * Constructor
     */
    public function __construct($db, $smsService = null) {
        $this->db = $db;
        $this->smsService = $smsService;
    }
    
    /**
     * Generate and send OTP to phone number
     * Check if OTP already exists first - don't generate multiple times
     */
    public function generateAndSendOTP($phoneNumber, $bookingData = null) {
        try {
            $phoneNumber = $this->db->real_escape_string($phoneNumber);
            
            // CHECK IF OTP ALREADY EXISTS - if yes, just resend it
            $checkQuery = "SELECT * FROM otp_sessions 
                          WHERE phone_number = '$phoneNumber'
                          AND status IN ('Pending', 'Verified')
                          AND created_at > DATE_SUB(NOW(), INTERVAL 30 MINUTE)
                          ORDER BY created_at DESC 
                          LIMIT 1";
            
            $checkResult = $this->db->query($checkQuery);
            
            if ($checkResult && $checkResult->num_rows > 0) {
                // OTP already exists - use the existing one
                $existingSession = $checkResult->fetch_assoc();
                $otp_session_id = $existingSession['id'];
                $otp = $existingSession['otp_code'];
                $mpin = $existingSession['mpin'];
                
                error_log("Reusing existing OTP for phone: $phoneNumber | OTP: $otp | MPIN: $mpin");
                
                // Resend the existing OTP via SMS
                $smsSent = false;
                $smsError = null;
                
                if ($this->smsService) {
                    $smsResult = $this->smsService->sendOTP($phoneNumber, $otp);
                    
                    if ($smsResult['success']) {
                        $smsSent = true;
                        // Update SMS sent timestamp
                        $this->db->query("UPDATE otp_sessions SET sms_sent = 1, sms_sent_at = NOW() WHERE id = $otp_session_id");
                    } else {
                        $smsSent = false;
                        $smsError = $smsResult['error'] ?? 'Unknown SMS error';
                        error_log("SMS Resend Failed: " . $smsError);
                    }
                }
                
                return [
                    'success' => $smsSent,
                    'message' => $smsSent ? 'OTP sent successfully via SMS' : 'OTP generated but SMS delivery failed (check IP whitelist)',
                    'sms_sent' => $smsSent,
                    'sms_error' => $smsError,
                    'otp_session_id' => $otp_session_id,
                    'otp_code' => $otp,
                    'mpin' => $mpin,
                    'is_existing' => true,
                    'expires_at' => 'NEVER - Permanent for login'
                ];
            }
            
            // Check if too many OTP attempts
            if (!$this->checkOTPSendAttempts($phoneNumber)) {
                return [
                    'success' => false,
                    'error' => 'Too many OTP requests. Please try again later.'
                ];
            }
            
            // GENERATE NEW OTP - only if one doesn't exist
            $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            
            // Generate 4-digit MPIN (acts as password for future login)
            $mpin = str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT);
            
            // Store OTP session
            $bookingDataJson = $bookingData ? json_encode($bookingData) : null;
            
            // Properly escape the booking data JSON
            $bookingDataJson = $bookingDataJson ? $this->db->real_escape_string($bookingDataJson) : null;
            $otp = $this->db->real_escape_string($otp);
            $mpin = $this->db->real_escape_string($mpin);
            
            // Build the insert query (no expiry - permanent for login)
            if ($bookingDataJson) {
                $query = "INSERT INTO otp_sessions 
                          (phone_number, otp_code, mpin, booking_data, status) 
                          VALUES ('$phoneNumber', '$otp', '$mpin', '$bookingDataJson', 'Pending')";
            } else {
                $query = "INSERT INTO otp_sessions 
                          (phone_number, otp_code, mpin, status) 
                          VALUES ('$phoneNumber', '$otp', '$mpin', 'Pending')";
            }
            
            if (!$this->db->query($query)) {
                throw new Exception("Failed to store OTP session: " . $this->db->error);
            }
            
            $otp_session_id = $this->db->insert_id;
            $smsSent = false;
            $smsError = null;
            
            // Send OTP via SMS if service is available
            if ($this->smsService) {
                $smsResult = $this->smsService->sendOTP($phoneNumber, $otp);
                
                if ($smsResult['success']) {
                    $smsSent = true;
                    // Mark in DB that SMS was sent
                    $this->db->query("UPDATE otp_sessions SET sms_sent = 1, sms_sent_at = NOW() WHERE id = $otp_session_id");
                } else {
                    $smsSent = false;
                    $smsError = $smsResult['error'] ?? 'Unknown SMS error';
                    error_log("SMS Send Failed: " . $smsError);
                }
            }
            
            error_log("New OTP generated for phone: $phoneNumber | OTP: $otp | MPIN: $mpin");
            
            return [
                'success' => $smsSent,
                'message' => $smsSent ? 'OTP sent successfully via SMS' : 'OTP generated but SMS delivery failed (check IP whitelist)',
                'sms_sent' => $smsSent,
                'sms_error' => $smsError,
                'otp_session_id' => $otp_session_id,
                'otp_code' => $otp,
                'mpin' => $mpin,
                'is_existing' => false,
                'expires_at' => 'NEVER - Permanent for login'
            ];
            
        } catch (Exception $e) {
            error_log("OTP Generation Error: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Verify OTP and auto-create/login user
     */
    public function verifyOTP($phoneNumber, $otpCode) {
        try {
            // Get the latest OTP session - NO EXPIRY CHECK (OTP permanent for login)
            $phoneNumber = $this->db->real_escape_string($phoneNumber);
            $query = "SELECT * FROM otp_sessions 
                     WHERE phone_number = '$phoneNumber'
                     AND status IN ('Pending', 'Verified')
                     ORDER BY created_at DESC 
                     LIMIT 1";
            
            $result = $this->db->query($query);
            
            if (!$result || $result->num_rows === 0) {
                return [
                    'success' => false,
                    'error' => 'No OTP found for this phone number. Please request OTP first.'
                ];
            }
            
            $session = $result->fetch_assoc();
            
            // Check if max attempts exceeded
            $maxAttempts = $this->getSettingValue('max_otp_attempts', 3);
            if ($session['attempt_count'] >= $maxAttempts) {
                // Mark session as expired
                $sessionId = (int)$session['id'];
                $this->db->query("UPDATE otp_sessions SET status = 'Expired' WHERE id = $sessionId");
                
                return [
                    'success' => false,
                    'error' => 'Maximum OTP attempts exceeded. Please request a new OTP.'
                ];
            }
            
            // Verify OTP code
            if ($session['otp_code'] !== $otpCode) {
                // Increment attempt count
                $sessionId = (int)$session['id'];
                $this->db->query("UPDATE otp_sessions SET attempt_count = attempt_count + 1 WHERE id = $sessionId");
                
                $remainingAttempts = $maxAttempts - ($session['attempt_count'] + 1);
                
                return [
                    'success' => false,
                    'error' => 'Invalid OTP. ' . $remainingAttempts . ' attempts remaining.'
                ];
            }
            
            // OTP is valid - mark as verified
            $sessionId = (int)$session['id'];
            $this->db->query("UPDATE otp_sessions SET is_verified = 1, status = 'Verified', verified_at = NOW() WHERE id = $sessionId");
            
            // CHECK IF USER EXISTS
            $userQuery = "SELECT id, phone_number, first_name, last_name, mpin FROM users WHERE phone_number = '$phoneNumber' LIMIT 1";
            $userResult = $this->db->query($userQuery);
            
            $userId = null;
            $userName = null;
            $userExists = false;
            
            if ($userResult && $userResult->num_rows > 0) {
                // User exists - this is a login
                $userData = $userResult->fetch_assoc();
                $userId = $userData['id'];
                $userName = $userData['first_name'] . ' ' . $userData['last_name'];
                $userExists = true;
                
                error_log("OTP Verified for existing user: $userName | Phone: $phoneNumber");
            } else {
                // New user - will be created during booking with the MPIN
                $userName = "Guest User";
                error_log("OTP Verified for new user | Phone: $phoneNumber | Will be created with MPIN: " . $session['mpin']);
            }
            
            return [
                'success' => true,
                'message' => 'OTP verified successfully!',
                'session_id' => $session['id'],
                'otp_code' => $session['otp_code'],
                'mpin' => $session['mpin'],  // This is the permanent MPIN for login/booking
                'booking_data' => $session['booking_data'] ? json_decode($session['booking_data'], true) : null,
                'user_id' => $userId,
                'user_name' => $userName,
                'user_exists' => $userExists,
                'phone_number' => $phoneNumber
            ];
            
        } catch (Exception $e) {
            error_log("OTP Verification Error: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Generate and send MPIN to user
     * Stores old MPIN before replacing with new one
     */
    public function generateAndSendMPIN($phoneNumber, $userId) {
        try {
            $userId = (int)$userId;
            
            // Get current MPIN before replacing
            $currentQuery = "SELECT mpin FROM users WHERE id = $userId LIMIT 1";
            $currentResult = $this->db->query($currentQuery);
            $oldMpin = null;
            
            if ($currentResult && $currentResult->num_rows > 0) {
                $userData = $currentResult->fetch_assoc();
                $oldMpin = $userData['mpin'];  // Store old MPIN
            }
            
            // Generate new 4-digit MPIN
            $newMpin = str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT);
            $newMpinEscaped = $this->db->real_escape_string($newMpin);
            $oldMpinEscaped = $oldMpin ? $this->db->real_escape_string($oldMpin) : null;
            
            // Update user MPIN and store old MPIN in old_mpin column
            if ($oldMpinEscaped) {
                $query = "UPDATE users SET mpin = '$newMpinEscaped', old_mpin = '$oldMpinEscaped' WHERE id = $userId";
            } else {
                $query = "UPDATE users SET mpin = '$newMpinEscaped' WHERE id = $userId";
            }
            
            if (!$this->db->query($query)) {
                throw new Exception("Failed to update MPIN: " . $this->db->error);
            }
            
            // Send new MPIN via SMS if service is available
            $smsSent = false;
            if ($this->smsService) {
                $smsResult = $this->smsService->sendMPIN($phoneNumber, $newMpin);
                
                if ($smsResult['success']) {
                    $smsSent = true;
                } else {
                    error_log("MPIN SMS Send Failed: " . $smsResult['error']);
                    // Continue even if SMS fails
                }
            }
            
            error_log("MPIN Updated for user $userId | Old: $oldMpin | New: $newMpin");
            
            return [
                'success' => true,
                'message' => 'New MPIN sent to your phone',
                'mpin' => $newMpin,  // Only for testing/logging
                'sms_sent' => $smsSent
            ];
            
        } catch (Exception $e) {
            error_log("MPIN Generation Error: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Handle forgot MPIN - generate and send new MPIN
     * Stores current MPIN as old_mpin
     */
    public function forgotMPIN($phoneNumber) {
        try {
            // Find user by phone number
            $phoneNumber = $this->db->real_escape_string($phoneNumber);
            $query = "SELECT id FROM users WHERE phone_number = '$phoneNumber' LIMIT 1";
            $result = $this->db->query($query);
            
            if (!$result || $result->num_rows === 0) {
                return [
                    'success' => false,
                    'error' => 'No account found with this phone number'
                ];
            }
            
            $user = $result->fetch_assoc();
            $userId = $user['id'];
            
            // Use the generateAndSendMPIN method to handle the reset
            return $this->generateAndSendMPIN($phoneNumber, $userId);
            
        } catch (Exception $e) {
            error_log("Forgot MPIN Error: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Check OTP send attempts (rate limiting)
     */
    private function checkOTPSendAttempts($phoneNumber) {
        try {
            $maxAttempts = $this->getSettingValue('max_otp_send_attempts', 5);
            $cooldownSeconds = $this->getSettingValue('otp_resend_cooldown', 60);
            
            // Count OTP requests from this number in the last cooldown period
            $phoneNumber = $this->db->real_escape_string($phoneNumber);
            $query = "SELECT COUNT(*) as count FROM otp_sessions 
                     WHERE phone_number = '$phoneNumber'
                     AND created_at > DATE_SUB(NOW(), INTERVAL $cooldownSeconds SECOND)";
            
            $result = $this->db->query($query);
            
            // Check if query was successful
            if (!$result) {
                return true; // Allow if check fails
            }
            
            $row = $result->fetch_assoc();
            
            return $row['count'] < $maxAttempts;
            
        } catch (Exception $e) {
            error_log("OTP Attempts Check Error: " . $e->getMessage());
            return true; // Allow if check fails
        }
    }
    
    /**
     * Get setting value from database
     */
    private function getSettingValue($key, $default = null) {
        try {
            $key = $this->db->real_escape_string($key);
            $query = "SELECT setting_value FROM system_settings WHERE setting_key = '$key'";
            $result = $this->db->query($query);
            
            if ($result && $result->num_rows > 0) {
                $row = $result->fetch_assoc();
                return $row['setting_value'];
            }
            
            return $default;
            
        } catch (Exception $e) {
            error_log("Setting Value Error: " . $e->getMessage());
            return $default;
        }
    }
    
    /**
     * Expire old OTP sessions
     */
    public function cleanupExpiredSessions() {
        try {
            $query = "UPDATE otp_sessions SET status = 'Expired' 
                     WHERE status IN ('Pending', 'Verified') 
                     AND expires_at < NOW()";
            
            return $this->db->query($query);
            
        } catch (Exception $e) {
            error_log("OTP Cleanup Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get OTP session details
     */
    public function getOTPSession($phoneNumber, $sessionId = null) {
        try {
            $phoneNumber = $this->db->real_escape_string($phoneNumber);
            
            if ($sessionId) {
                $sessionId = (int)$sessionId;
                $query = "SELECT * FROM otp_sessions WHERE id = $sessionId AND phone_number = '$phoneNumber'";
            } else {
                $query = "SELECT * FROM otp_sessions 
                         WHERE phone_number = '$phoneNumber'
                         ORDER BY created_at DESC 
                         LIMIT 1";
            }
            
            $result = $this->db->query($query);
            
            if ($result && $result->num_rows > 0) {
                return $result->fetch_assoc();
            }
            
            return null;
            
        } catch (Exception $e) {
            error_log("Get OTP Session Error: " . $e->getMessage());
            return null;
        }
    }
}
?>
