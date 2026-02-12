<?php
/**
 * Forgot MPIN API
 * Generates and sends new MPIN to user
 * Old MPIN is stored in database
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../init.php';
require_once __DIR__ . '/../helpers/OTPHelper.php';
require_once __DIR__ . '/../services/SparrowSMSService.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }
    
    $phoneNumber = $_POST['phone_number'] ?? '';
    
    if (empty($phoneNumber)) {
        throw new Exception('Phone number is required');
    }
    
    // Validate phone number format
    require_once __DIR__ . '/../helpers/SMSHelper.php';
    $smsHelper = new SMSHelper();
    
    if (!$smsHelper->isValidPhone($phoneNumber)) {
        throw new Exception('Invalid phone number format');
    }
    
    // Format phone to standard format
    $phoneNumber = $smsHelper->formatPhone($phoneNumber);
    
    // Initialize OTP Helper
    $smsService = new SparrowSMSService($db);
    $otpHelper = new OTPHelper($db, $smsService);
    
    // Call forgot MPIN method
    $result = $otpHelper->forgotMPIN($phoneNumber);
    
    if ($result['success']) {
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'New MPIN sent to your phone. Please check your SMS.',
            'type' => 'success'
        ]);
    } else {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => $result['error'] ?? 'Failed to process forgot MPIN',
            'type' => 'error'
        ]);
    }
    
} catch (Exception $e) {
    error_log("Forgot MPIN Error: " . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'type' => 'error'
    ]);
}
?>
