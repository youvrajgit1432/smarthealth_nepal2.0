<?php
/**
 * API - Complete Token Booking After OTP Verification
 * Endpoint: /backend/api/complete_booking.php
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
$departmentId = $input['department_id'] ?? $_POST['department_id'] ?? null;
$otpSessionId = $input['otp_session_id'] ?? $_POST['otp_session_id'] ?? null;
$triageData = $input['triage_data'] ?? $_POST['triage_data'] ?? [];

if (empty($phoneNumber) || empty($departmentId) || empty($otpSessionId)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing required fields']);
    exit;
}

try {
    // Initialize controller
    $tokenController = new TokenController($db);
    
    // Complete booking
    $result = $tokenController->completeBookingAfterOTPVerification(
        $phoneNumber,
        $departmentId,
        $triageData,
        $otpSessionId
    );
    
    header('Content-Type: application/json');
    echo json_encode($result);
    
} catch (Exception $e) {
    error_log("Booking Completion API Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to complete booking. Please try again.'
    ]);
}
?>
