<?php
/**
 * Sparrow SMS Service
 * Handles SMS sending via Sparrow SMS API
 */

class SparrowSMSService {
    private $apiToken;
    private $senderId;
    private $baseUrl;
    private $enabled;
    private $db;
    
    /**
     * Constructor
     */
    public function __construct($db = null) {
        $this->db = $db;
        if ($db) {
            $this->loadConfigFromDatabase($db);
        }
    }
    
    /**
     * Load SMS configuration from database
     */
    private function loadConfigFromDatabase($db) {
        try {
            $query = "SELECT setting_key, setting_value FROM system_settings 
                     WHERE setting_key IN ('sms_enabled', 'sms_api_url', 'sms_api_key', 'sms_sender_id')";
            $result = $db->query($query);
            
            $settings = [];
            
            // Check if query was successful
            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $settings[$row['setting_key']] = $row['setting_value'];
                }
            }
            
            $this->enabled = ($settings['sms_enabled'] ?? '0') === '1';
            $this->baseUrl = $settings['sms_api_url'] ?? 'http://api.sparrowsms.com/v2/';
            $this->apiToken = $settings['sms_api_key'] ?? '';
            $this->senderId = $settings['sms_sender_id'] ?? 'TheAlert';
            
        } catch (Exception $e) {
            error_log("SMS Config Load Error: " . $e->getMessage());
            // Set defaults if database read fails
            $this->enabled = false;
            $this->baseUrl = 'http://api.sparrowsms.com/v2/';
            $this->apiToken = '';
            $this->senderId = 'TheAlert';
        }
    }
    
    /**
     * Send OTP via Sparrow SMS
     */
    public function sendOTP($phoneNumber, $otpCode, $message = null) {
        try {
            // Check if SMS is enabled
            if (!$this->enabled) {
                return [
                    'success' => false,
                    'error' => 'SMS service is disabled'
                ];
            }
            
            // Validate API configuration
            if (empty($this->apiToken) || empty($this->senderId)) {
                return [
                    'success' => false,
                    'error' => 'SMS configuration incomplete'
                ];
            }
            
            // Create message
            if ($message === null) {
                $message = "SmartHealth OTP: " . $otpCode . " (Expires in 10 minutes). Do not share this code.";
            }
            
            // Send SMS
            $result = $this->sendSMS($phoneNumber, $message);
            
            // Ensure consistent response format
            if (isset($result['success']) && $result['success'] === true) {
                return [
                    'success' => true,
                    'message' => $result['message'] ?? 'OTP sent successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'error' => $result['message'] ?? $result['error'] ?? 'Failed to send OTP'
                ];
            }
            
        } catch (Exception $e) {
            error_log("Sparrow SMS Error: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Send MPIN via Sparrow SMS
     */
    public function sendMPIN($phoneNumber, $mpinCode) {
        $message = "SmartHealth MPIN: " . $mpinCode . " - Use this to log in. Do not share.";
        
        return $this->sendOTP($phoneNumber, $mpinCode, $message);
    }
    
    /**
     * Send SMS via Sparrow SMS
     */
    public function sendSMS($phoneNumber, $message) {
        try {
            // Check if SMS is enabled
            if (!$this->enabled) {
                return [
                    'success' => false,
                    'message' => 'SMS service is disabled'
                ];
            }
            
            // Format phone number - ensure it starts with 977 for Nepal
            $phoneNumber = $this->formatPhoneNumber($phoneNumber);
            
            // Build parameters
            $params = [
                'token' => $this->apiToken,
                'from' => $this->senderId,
                'to' => $phoneNumber,
                'text' => $message
            ];
            
            // Build the complete URL
            $url = $this->baseUrl . 'sms/?' . http_build_query($params);
            
            // Log the API call for debugging
            error_log("Sparrow SMS API Call: " . $url);
            error_log("Sparrow SMS Params - Phone: $phoneNumber, Sender: " . $this->senderId . ", Token Length: " . strlen($this->apiToken));
            
            // Use GET request
            $response = $this->makeGetRequest($url);
            
            if (isset($response['response_code']) && $response['response_code'] == 200) {
                return [
                    'success' => true,
                    'message' => 'SMS sent successfully',
                    'response' => $response,
                    'message_id' => $response['message_id'] ?? null
                ];
            } else {
                $errorMessage = $response['response'] ?? $response['message'] ?? 'Unknown error';
                return [
                    'success' => false,
                    'message' => 'SMS API Error: ' . $errorMessage,
                    'response' => $response
                ];
            }
            
        } catch (Exception $e) {
            error_log("Sparrow SMS Send Error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'SMS sending failed: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Format phone number to international format (977XXXXXXXXX)
     */
    private function formatPhoneNumber($phoneNumber) {
        // Remove any non-numeric characters
        $phoneNumber = preg_replace('/[^0-9]/', '', $phoneNumber);
        
        // If it starts with 977, it's already formatted
        if (substr($phoneNumber, 0, 3) === '977') {
            return $phoneNumber;
        }
        
        // If it starts with 0, remove it and add 977
        if (substr($phoneNumber, 0, 1) === '0') {
            $phoneNumber = substr($phoneNumber, 1);
        }
        
        // Add Nepal country code
        return '977' . $phoneNumber;
    }
    
    /**
     * Check credit balance
     */
    public function checkCredits() {
        try {
            if (!$this->enabled) {
                return [
                    'success' => false,
                    'error' => 'SMS service is disabled'
                ];
            }
            
            $url = $this->baseUrl . 'credit/?token=' . $this->apiToken;
            $response = $this->makeGetRequest($url);
            
            return [
                'success' => true,
                'credits' => $response['credits_available'] ?? 0,
                'response' => $response
            ];
            
        } catch (Exception $e) {
            error_log("Sparrow SMS Balance Check Error: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Make GET request to Sparrow SMS API
     */
    private function makeGetRequest($url) {
        $ch = curl_init();
        
        $headers = [
            'Authorization: Bearer ' . $this->apiToken,
            'Content-Type: application/json'
        ];
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 5,
            CURLOPT_SSL_VERIFYPEER => false,  // Changed to false for local testing
            CURLOPT_SSL_VERIFYHOST => 0,      // Changed to 0 for local testing
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
            CURLOPT_DNS_CACHE_TIMEOUT => 300,
            CURLOPT_HTTPHEADER => $headers
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        if ($curlError) {
            throw new Exception("cURL Error: " . $curlError);
        }
        
        // Log the HTTP response for debugging
        error_log("Sparrow SMS HTTP Response Code: " . $httpCode);
        error_log("Sparrow SMS Response Body: " . substr($response, 0, 500));
        
        if ($httpCode >= 400) {
            throw new Exception("HTTP Error: " . $httpCode . " - Response: " . substr($response, 0, 200));
        }
        
        $decodedResponse = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            if (strpos($response, '<html') !== false) {
                throw new Exception("API returned HTML (likely redirect issue). Check API endpoint.");
            }
            throw new Exception("Invalid JSON response: " . $response);
        }
        
        return $decodedResponse;
    }
    
    /**
     * Check if SMS service is enabled
     */
    public function isEnabled() {
        return $this->enabled;
    }
}
?>
