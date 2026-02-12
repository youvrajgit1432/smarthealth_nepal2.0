<?php
/**
 * API - Verify OTP for Token Booking
 * Endpoint: /backend/api/verify_otp.php
 */

require_once __DIR__ . '/../init.php';
require_once __DIR__ . '/../controllers/TokenController.php';

// Ensure it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);
$phoneNumber = $input['phone_number'] ?? $_POST['phone_number'] ?? '';
$otpCode = $input['otp_code'] ?? $_POST['otp_code'] ?? '';

if (empty($phoneNumber) || empty($otpCode)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Phone number and OTP code are required']);
    exit;
}

try {
    // Initialize controller
    $tokenController = new TokenController($db);
    
    // Verify OTP
    $result = $tokenController->verifyOTPForBooking($phoneNumber, $otpCode);
    
    header('Content-Type: application/json');
    echo json_encode($result);
    
} catch (Exception $e) {
    error_log("OTP Verification API Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to verify OTP. Please try again.'
    ]);
}
?>
