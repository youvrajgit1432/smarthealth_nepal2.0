<?php
// Admin action: Call next token in queue
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
$sql = "SELECT t.id, t.token_number, t.status, t.department_id, t.user_id, u.phone, u.name 
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

if ($token['status'] !== 'Active' && $token['status'] !== 'Confirmed') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Token cannot be called in current status: ' . $token['status']]);
    exit;
}

// Update token status to Called
$sql_update = "UPDATE tokens SET status = 'Called', called_at = NOW(), called_by = ? WHERE id = ?";
$admin_id = $_SESSION['admin_id'] ?? null;
$stmt_update = $conn->prepare($sql_update);
$stmt_update->bind_param('ii', $admin_id, $token_id);
$stmt_update->execute();

// Create notification for user
require_once '../helpers/SMSHelper.php';
SMSHelper::logSMS($token['phone'], 'TOKEN_CALLED', "Token: {$token['token_number']} has been called", 'OUTGOING');

echo json_encode([
    'success' => true,
    'message' => 'Token called successfully',
    'token' => [
        'id' => $token['id'],
        'number' => $token['token_number'],
        'status' => 'Called',
        'patient' => $token['name'],
        'phone' => $token['phone'],
        'called_at' => date('Y-m-d H:i:s')
    ]
]);

$stmt->close();
$stmt_update->close();
$conn->close();
?>
