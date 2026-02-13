<?php
// Admin action: Delete user and related records
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

$user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;

if (!$user_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'User ID required']);
    exit;
}

// Check if admin is superadmin
$is_superadmin = $_SESSION['admin_role'] === 'superadmin';
$admin_hospital_id = $_SESSION['hospital_id'] ?? null;

// Verify user exists
$sql_check = "SELECT id, phone, name FROM users WHERE id = ?";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param('i', $user_id);
$stmt_check->execute();
$result = $stmt_check->get_result();

if ($result->num_rows === 0) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'User not found']);
    exit;
}

$user = $result->fetch_assoc();

// If not superadmin, verify user belongs to their hospital
if (!$is_superadmin && $admin_hospital_id) {
    $sql_verify = "SELECT COUNT(*) as count FROM tokens t 
                   JOIN departments d ON t.department_id = d.id 
                   WHERE t.user_id = ? AND d.hospital_id = ?";
    $stmt_verify = $conn->prepare($sql_verify);
    $stmt_verify->bind_param('ii', $user_id, $admin_hospital_id);
    $stmt_verify->execute();
    $verify_result = $stmt_verify->get_result()->fetch_assoc();
    
    if ($verify_result['count'] === 0) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'User not found or access denied']);
        exit;
    }
}

// Start transaction
$conn->begin_transaction();

try {
    // Delete related records in order of dependencies
    // 1. Delete tokens
    $sql = "DELETE FROM tokens WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    
    // 2. Delete chronic diseases
    $sql = "DELETE FROM chronic_diseases WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    
    // 3. Delete maternal health records
    $sql = "DELETE FROM maternal_health WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    
    // 4. Delete health records
    $sql = "DELETE FROM health_records WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    
    // 5. Delete notifications
    $sql = "DELETE FROM notifications WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    
    // 6. Delete user
    $sql = "DELETE FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'User deleted successfully',
        'user' => [
            'id' => $user['id'],
            'name' => $user['name'],
            'phone' => $user['phone'],
            'deleted_at' => date('Y-m-d H:i:s')
        ]
    ]);
    
} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to delete user: ' . $e->getMessage()]);
}

$stmt_check->close();
$conn->close();
?>
