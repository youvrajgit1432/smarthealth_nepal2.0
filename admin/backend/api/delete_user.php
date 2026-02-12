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
