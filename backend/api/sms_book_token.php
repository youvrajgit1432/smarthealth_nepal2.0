<?php
// SMS-based token booking for offline users
session_start();

require_once '../config/database.php';
require_once '../helpers/TokenHelper.php';
require_once '../helpers/SMSHelper.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
if ($method !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
$command = isset($_POST['command']) ? strtoupper(trim($_POST['command'])) : '';

// Validate phone number format (10 digits for Nepal)
if (!preg_match('/^\d{10}$/', $phone)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid phone number format']);
    exit;
}

// Find user by phone
$sql = "SELECT id, name, language FROM users WHERE phone = ? LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $phone);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Create new user with empty name (will be updated later)
    $lang = 'en';
    $sql_insert = "INSERT INTO users (phone, name, language, created_at) VALUES (?, ?, ?, NOW())";
    $stmt_insert = $conn->prepare($sql_insert);
    $default_name = 'SMS User';
    $stmt_insert->bind_param('sss', $phone, $default_name, $lang);
    $stmt_insert->execute();
    $user_id = $conn->insert_id;
    $language = 'en';
} else {
    $user = $result->fetch_assoc();
    $user_id = $user['id'];
    $language = $user['language'] ?? 'en';
}

// Parse command to determine department and priority
$booking_type = 'General';
$department_id = 1; // Default general department
$priority = 'Normal';

// Map commands to departments
$command_map = [
    'FEVER' => ['dept_id' => 2, 'type' => 'Fever/Infection'],
    'BREATHING' => ['dept_id' => 3, 'type' => 'Respiratory Issue'],
    'INJURY' => ['dept_id' => 4, 'type' => 'Injury/Trauma'],
    'CHRONIC' => ['dept_id' => 5, 'type' => 'Chronic Disease', 'priority' => 'Chronic'],
    'MATERNAL' => ['dept_id' => 6, 'type' => 'Maternal Health', 'priority' => 'Priority'],
    'GENERAL' => ['dept_id' => 1, 'type' => 'General Checkup']
];

// Extract booking type from command (BOOK FEVER, BOOK BREATHING, etc.)
$parsed = false;
foreach ($command_map as $keyword => $config) {
    if (strpos($command, $keyword) !== false) {
        $booking_type = $config['type'];
        $department_id = $config['dept_id'];
        $priority = $config['priority'] ?? 'Normal';
        $parsed = true;
        break;
    }
}

if (!$parsed) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid booking command']);
    exit;
}

// Generate token number
$token_number = TokenHelper::generateTokenNumber();

// Check if user already has token today for this department
$sql_check = "SELECT id FROM tokens WHERE user_id = ? AND department_id = ? AND DATE(created_at) = CURDATE() AND status != 'Cancelled'";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param('ii', $user_id, $department_id);
$stmt_check->execute();

if ($stmt_check->get_result()->num_rows > 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'You already have a token for this department today']);
    exit;
}

// Create token record
$sql_token = "INSERT INTO tokens (token_number, user_id, department_id, status, priority, booking_type, created_at) 
              VALUES (?, ?, ?, 'Active', ?, ?, NOW())";
$stmt_token = $conn->prepare($sql_token);
$stmt_token->bind_param('siiss', $token_number, $user_id, $department_id, $priority, $booking_type);

if (!$stmt_token->execute()) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to create token']);
    exit;
}

$token_id = $conn->insert_id;

// Generate OTP (4-6 digits)
$otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);

// Store OTP in token record (in production, use a separate OTP table with expiry)
$sql_otp = "UPDATE tokens SET otp = ? WHERE id = ?";
$stmt_otp = $conn->prepare($sql_otp);
$stmt_otp->bind_param('si', $otp, $token_id);
$stmt_otp->execute();

// Get department details
$sql_dept = "SELECT name FROM departments WHERE id = ?";
$stmt_dept = $conn->prepare($sql_dept);
$stmt_dept->bind_param('i', $department_id);
$stmt_dept->execute();
$dept_result = $stmt_dept->get_result();
$dept = $dept_result->fetch_assoc();
$dept_name = $dept['name'] ?? 'General';

// Log SMS booking attempt
SMSHelper::logSMS($phone, 'BOOKING_INITIATED', "Booking Type: {$booking_type}, OTP: {$otp}", 'OUTGOING');

// Prepare response message
if ($language === 'ne') {
    $response_msg = "SmartHealth: आपको टोकन #{$token_number} तयार भएको छ। विभाग: {$dept_name}। कृपया SMS मार्फत CONFIRM {$otp} पठाउनुहोस्। धन्यवाद।";
} else {
    $response_msg = "SmartHealth: Your token #{$token_number} is ready. Department: {$dept_name}. Please reply CONFIRM {$otp} to confirm. Thank you.";
}

echo json_encode([
    'success' => true,
    'message' => 'Token created successfully',
    'token' => [
        'number' => $token_number,
        'id' => $token_id,
        'department' => $dept_name,
        'type' => $booking_type,
        'status' => 'Active',
        'otp' => $otp, // In production, hide this from response and send via SMS
        'priority' => $priority
    ],
    'sms_message' => $response_msg,
    'phone' => $phone,
    'user_id' => $user_id
]);

$stmt->close();
$stmt_check->close();
$stmt_token->close();
$stmt_otp->close();
$stmt_dept->close();
$conn->close();
?>
