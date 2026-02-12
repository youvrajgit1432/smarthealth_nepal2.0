<?php
// Admin action: Approve referral
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

if (!$referral_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Referral ID required']);
    exit;
}

// Get referral details
$sql = "SELECT r.* FROM referrals r WHERE r.id = ?";
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
    echo json_encode(['success' => false, 'message' => 'Only pending referrals can be approved']);
    exit;
}

// Update referral status
$sql_update = "UPDATE referrals SET status = 'Approved', approved_at = NOW(), approved_by = ? WHERE id = ?";
$admin_id = $_SESSION['admin_id'] ?? null;
$stmt_update = $conn->prepare($sql_update);
$stmt_update->bind_param('ii', $admin_id, $referral_id);
$stmt_update->execute();

// Create notification
require_once '../helpers/SMSHelper.php';
$sql_notif = "INSERT INTO notifications (user_id, type, title, message, created_at) 
              VALUES (?, 'REFERRAL_APPROVED', 'Referral Approved', 'Your referral has been approved', NOW())";
$stmt_notif = $conn->prepare($sql_notif);
$stmt_notif->bind_param('i', $referral['user_id']);
$stmt_notif->execute();

echo json_encode([
    'success' => true,
    'message' => 'Referral approved successfully',
    'referral' => [
        'id' => $referral['id'],
        'status' => 'Approved',
        'approved_at' => date('Y-m-d H:i:s')
    ]
]);

$stmt->close();
$stmt_update->close();
$stmt_notif->close();
$conn->close();
?>
