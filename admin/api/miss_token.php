<?php
/**
 * Admin API - Miss Token
 * Mark a token as missed/no-show
 */

require_once __DIR__ . '/../../backend/init.php';

// Check admin login
if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Log API call
error_log("Miss Token API - Admin ID: {$_SESSION['admin_id']}, Request: " . print_r($_REQUEST, true));

global $db;
$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    $response['message'] = 'Method not allowed';
    echo json_encode($response);
    exit;
}

$token_id = isset($_POST['token_id']) ? intval($_POST['token_id']) : 0;

if ($token_id <= 0) {
    http_response_code(400);
    $response['message'] = 'Invalid token ID';
    echo json_encode($response);
    exit;
}

// Check if token exists and belongs to this admin's hospital (if hospital admin)
$query = "SELECT t.id, t.status, t.token_number FROM tokens t";

if ($_SESSION['admin_role'] !== 'SuperAdmin' && isset($_SESSION['hospital_id'])) {
    $query .= " INNER JOIN departments d ON t.department_id = d.id WHERE t.id = ? AND d.hospital_id = ?";
} else {
    $query .= " WHERE t.id = ?";
}

$stmt = $db->prepare($query);

if (!$stmt) {
    http_response_code(500);
    $response['message'] = 'Database error: ' . $db->error;
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

if ($_SESSION['admin_role'] !== 'SuperAdmin' && isset($_SESSION['hospital_id'])) {
    $stmt->bind_param("ii", $token_id, $_SESSION['hospital_id']);
} else {
    $stmt->bind_param("i", $token_id);
}

if (!$stmt->execute()) {
    http_response_code(500);
    $response['message'] = 'Database error: ' . $stmt->error;
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(404);
    $response['message'] = 'Token not found';
    echo json_encode($response);
    exit;
}

$token = $result->fetch_assoc();

// Check if token is in called or active status
if (!in_array($token['status'], ['Active', 'Called'])) {
    http_response_code(400);
    $response['message'] = 'Token cannot be marked as missed from current status';
    echo json_encode($response);
    exit;
}

// Update token status to Missed
$update_stmt = $db->prepare("UPDATE tokens SET status = 'Missed', missed_at = NOW() WHERE id = ?");

if (!$update_stmt) {
    http_response_code(500);
    $response['message'] = 'Database error: ' . $db->error;
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

$update_stmt->bind_param("i", $token_id);

if (!$update_stmt->execute()) {
    http_response_code(500);
    $response['message'] = 'Failed to mark token as missed: ' . $update_stmt->error;
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// Log the action
$log_stmt = $db->prepare("INSERT INTO admin_logs (admin_id, action, token_id, details, created_at) VALUES (?, 'MISS_TOKEN', ?, ?, NOW())");
$details = "Marked missed token: {$token['token_number']}";
$log_stmt->bind_param("iss", $_SESSION['admin_id'], $token_id, $details);
$log_stmt->execute();

$response['success'] = true;
$response['message'] = 'Token marked as missed successfully';
http_response_code(200);

header('Content-Type: application/json');
echo json_encode($response);
exit;
?>
