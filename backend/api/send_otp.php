<?php
/**
 * API - Send OTP for Token Booking
 * Endpoint: /backend/api/send_otp.php
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

if (empty($phoneNumber)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Phone number is required']);
    exit;
}

try {
    // Initialize controller
    $tokenController = new TokenController($db);
    
    // Send OTP
    $result = $tokenController->sendOTPForBooking($phoneNumber);
    
    header('Content-Type: application/json');
    echo json_encode($result);
    
} catch (Exception $e) {
    error_log("OTP API Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to send OTP. Please try again.'
    ]);
}
?>
