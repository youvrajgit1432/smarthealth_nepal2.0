<?php
// Confirm SMS token booking with OTP
session_start();

require_once '../config/database.php';
require_once '../helpers/SMSHelper.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
if ($method !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$token_id = isset($_POST['token_id']) ? intval($_POST['token_id']) : 0;
$otp = isset($_POST['otp']) ? trim($_POST['otp']) : '';
$phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';

if (!$token_id || !$otp) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Token ID and OTP required']);
    exit;
}

// Verify token and OTP
$sql = "SELECT t.id, t.token_number, t.status, t.otp, t.department_id, d.name as dept_name, u.phone, u.language
        FROM tokens t
        LEFT JOIN departments d ON t.department_id = d.id
        LEFT JOIN users u ON t.user_id = u.id
        WHERE t.id = ? AND DATE(t.created_at) = CURDATE()";

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $token_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Token not found']);
    exit;
}

$token = $result->fetch_assoc();

// Verify OTP
if ($token['otp'] !== $otp) {
    // Log failed OTP attempt
    SMSHelper::logSMS($phone, 'OTP_FAILED', "Token: {$token['token_number']}, OTP Attempt: {$otp}", 'INCOMING');
    
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Invalid OTP']);
    exit;
}

// Update token status to Confirmed
$sql_update = "UPDATE tokens SET status = 'Confirmed', confirmed_at = NOW(), otp = NULL WHERE id = ?";
$stmt_update = $conn->prepare($sql_update);
$stmt_update->bind_param('i', $token_id);
$stmt_update->execute();

// Log successful confirmation
SMSHelper::logSMS($token['phone'], 'OTP_CONFIRMED', "Token: {$token['token_number']} confirmed", 'INCOMING');

// Send confirmation SMS
$language = $token['language'] ?? 'en';
if ($language === 'ne') {
    $msg = "SmartHealth: आपको टोकन #{$token['token_number']} सफलतापूर्वक पुष्टि भएको छ। विभाग: {$token['dept_name']}। कृपया शीघ्रै अस्पताल आउनुहोस्। धन्यवाद।";
} else {
    $msg = "SmartHealth: Your token #{$token['token_number']} has been confirmed. Department: {$token['dept_name']}. Please arrive at hospital soon. Thank you.";
}

// In production, integrate actual SMS gateway here
// SMSHelper::sendSMS($token['phone'], $msg);

echo json_encode([
    'success' => true,
    'message' => 'Token confirmed successfully',
    'token' => [
        'number' => $token['token_number'],
        'status' => 'Confirmed',
        'department' => $token['dept_name']
    ],
    'sms_message' => $msg
]);

$stmt->close();
$stmt_update->close();
$conn->close();
?>
