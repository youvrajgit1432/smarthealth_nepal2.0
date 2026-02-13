<?php
// Admin action: Mark token as completed
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

// Check if admin is superadmin
$is_superadmin = $_SESSION['admin_role'] === 'superadmin';
$admin_hospital_id = $_SESSION['hospital_id'] ?? null;

// Build hospital filter based on role
$hospital_filter = '';
if (!$is_superadmin && $admin_hospital_id) {
    $hospital_filter = " AND d.hospital_id = " . (int)$admin_hospital_id;
}

// Get token details with hospital validation
$sql = "SELECT t.id, t.token_number, t.status, t.department_id, d.hospital_id
        FROM tokens t
        LEFT JOIN departments d ON t.department_id = d.id
        WHERE t.id = ? AND DATE(t.created_at) = CURDATE()" . $hospital_filter;

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $token_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Token not found or access denied']);
    exit;
}

$token = $result->fetch_assoc();

// Update token status to Completed
$sql_update = "UPDATE tokens SET status = 'Completed', completed_at = NOW(), completed_by = ? WHERE id = ?";
$admin_id = $_SESSION['admin_id'] ?? null;
$stmt_update = $conn->prepare($sql_update);
$stmt_update->bind_param('ii', $admin_id, $token_id);
$stmt_update->execute();

// Decrement department current_load
$sql_load = "UPDATE departments SET current_load = GREATEST(0, current_load - 1) WHERE id = ?";
$stmt_load = $conn->prepare($sql_load);
$stmt_load->bind_param('i', $token['department_id']);
$stmt_load->execute();

echo json_encode([
    'success' => true,
    'message' => 'Token marked as completed',
    'token' => [
        'id' => $token['id'],
        'number' => $token['token_number'],
        'status' => 'Completed',
        'completed_at' => date('Y-m-d H:i:s')
    ]
]);

$stmt->close();
$stmt_update->close();
$stmt_load->close();
$conn->close();
?>
