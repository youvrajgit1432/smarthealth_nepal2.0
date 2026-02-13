<?php
// Admin action: Reject referral
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
$rejection_reason = isset($_POST['reason']) ? trim($_POST['reason']) : '';

if (!$referral_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Referral ID required']);
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

if ($referral['status'] !== 'Pending') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Only pending referrals can be rejected']);
    exit;
}

// Update referral status
$sql_update = "UPDATE referrals SET status = 'Rejected', rejection_reason = ?, rejected_at = NOW(), rejected_by = ? WHERE id = ?";
$admin_id = $_SESSION['admin_id'] ?? null;
$stmt_update = $conn->prepare($sql_update);
$stmt_update->bind_param('sii', $rejection_reason, $admin_id, $referral_id);
$stmt_update->execute();

// Create notification
$sql_notif = "INSERT INTO notifications (user_id, type, title, message, created_at) 
              VALUES (?, 'REFERRAL_REJECTED', 'Referral Rejected', ?, NOW())";
$notif_msg = 'Your referral has been rejected. Reason: ' . $rejection_reason;
$stmt_notif = $conn->prepare($sql_notif);
$stmt_notif->bind_param('is', $referral['user_id'], $notif_msg);
$stmt_notif->execute();

echo json_encode([
    'success' => true,
    'message' => 'Referral rejected successfully',
    'referral' => [
        'id' => $referral['id'],
        'status' => 'Rejected',
        'reason' => $rejection_reason,
        'rejected_at' => date('Y-m-d H:i:s')
    ]
]);

$stmt->close();
$stmt_update->close();
$stmt_notif->close();
$conn->close();
?>
