<?php
// Admin action: Forward referral to another hospital
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

$referral_id = isset($_POST['referral_id']) ? intval($_POST['referral_id']) : 0;
$to_hospital = isset($_POST['to_hospital']) ? trim($_POST['to_hospital']) : '';
$forwarding_notes = isset($_POST['notes']) ? trim($_POST['notes']) : '';

if (!$referral_id || !$to_hospital) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Referral ID and destination hospital required']);
    exit;
}

// Check if admin is superadmin
$is_superadmin = $_SESSION['admin_role'] === 'superadmin';
$admin_hospital_id = $_SESSION['hospital_id'] ?? null;

// Build hospital filter
$hospital_filter = '';
if (!$is_superadmin && $admin_hospital_id) {
    $hospital_filter = " AND r.to_hospital_id = " . (int)$admin_hospital_id;
}

// Get referral details
$sql = "SELECT r.* FROM referrals r WHERE r.id = ?" . $hospital_filter;
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $referral_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Referral not found']);
    exit;
}

$referral = $result->fetch_assoc();

if ($referral['status'] !== 'Approved') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Only approved referrals can be forwarded']);
    exit;
}

// Update referral status
$sql_update = "UPDATE referrals SET status = 'Forwarded', forwarded_to = ?, forwarding_notes = ?, forwarded_at = NOW(), forwarded_by = ? WHERE id = ?";
$admin_id = $_SESSION['admin_id'] ?? null;
$stmt_update = $conn->prepare($sql_update);
$stmt_update->bind_param('ssii', $to_hospital, $forwarding_notes, $admin_id, $referral_id);
$stmt_update->execute();

// Create notification
$sql_notif = "INSERT INTO notifications (user_id, type, title, message, created_at) 
              VALUES (?, 'REFERRAL_FORWARDED', 'Referral Forwarded', ?, NOW())";
$notif_msg = 'Your referral has been forwarded to ' . $to_hospital;
$stmt_notif = $conn->prepare($sql_notif);
$stmt_notif->bind_param('is', $referral['user_id'], $notif_msg);
$stmt_notif->execute();

echo json_encode([
    'success' => true,
    'message' => 'Referral forwarded successfully',
    'referral' => [
        'id' => $referral['id'],
        'status' => 'Forwarded',
        'forwarded_to' => $to_hospital,
        'forwarded_at' => date('Y-m-d H:i:s')
    ]
]);

$stmt->close();
$stmt_update->close();
$stmt_notif->close();
$conn->close();
?>
