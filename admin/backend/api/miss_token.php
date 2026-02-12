<?php
// Admin action: Mark token as missed
session_start();

require_once '../config/database.php';
require_once '../config/init.php';
require_once '../controllers/AdminAuthController.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
if ($method !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Verify admin authentication
$auth = new AdminAuthController();
if (!$auth->isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Admin not authenticated']);
    exit;
}

$token_id = isset($_POST['token_id']) ? intval($_POST['token_id']) : 0;

if (!$token_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Token ID required']);
    exit;
}

// Get token details
$sql = "SELECT t.id, t.token_number, t.status, t.department_id, t.user_id, u.phone
        FROM tokens t
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

if ($token['status'] === 'Completed' || $token['status'] === 'Cancelled') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Cannot mark completed or cancelled token as missed']);
    exit;
}

// Update token status to Missed
$sql_update = "UPDATE tokens SET status = 'Missed', missed_at = NOW(), missed_by = ? WHERE id = ?";
$admin_id = $_SESSION['admin_id'] ?? null;
$stmt_update = $conn->prepare($sql_update);
$stmt_update->bind_param('ii', $admin_id, $token_id);
$stmt_update->execute();

// Send notification to user
require_once '../helpers/SMSHelper.php';
SMSHelper::logSMS($token['phone'], 'TOKEN_MISSED', "Token: {$token['token_number']} marked as missed", 'OUTGOING');

// Create notification record
$sql_notif = "INSERT INTO notifications (user_id, type, title, message, created_at) 
              VALUES (?, 'MISSED_TOKEN', 'Token Missed', 'Your token was marked as missed. Please book a new token', NOW())";
$stmt_notif = $conn->prepare($sql_notif);
$stmt_notif->bind_param('i', $token['user_id']);
$stmt_notif->execute();

echo json_encode([
    'success' => true,
    'message' => 'Token marked as missed',
    'token' => [
        'id' => $token['id'],
        'number' => $token['token_number'],
        'status' => 'Missed',
        'missed_at' => date('Y-m-d H:i:s')
    ]
]);

$stmt->close();
$stmt_update->close();
$stmt_notif->close();
$conn->close();
?>
