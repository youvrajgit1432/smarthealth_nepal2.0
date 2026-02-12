<?php
// Admin action: Delete department
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

$dept_id = isset($_POST['dept_id']) ? intval($_POST['dept_id']) : 0;

if (!$dept_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Department ID required']);
    exit;
}

// Verify department exists
$sql_check = "SELECT id, name FROM departments WHERE id = ?";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param('i', $dept_id);
$stmt_check->execute();
$result = $stmt_check->get_result();

if ($result->num_rows === 0) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Department not found']);
    exit;
}

$dept = $result->fetch_assoc();

// Check if department has active tokens
$sql_active = "SELECT COUNT(*) as count FROM tokens WHERE department_id = ? AND status NOT IN ('Completed', 'Cancelled', 'Missed')";
$stmt_active = $conn->prepare($sql_active);
$stmt_active->bind_param('i', $dept_id);
$stmt_active->execute();
$active_result = $stmt_active->get_result()->fetch_assoc();

if ($active_result['count'] > 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Cannot delete department with active tokens']);
    exit;
}

// Start transaction
$conn->begin_transaction();

try {
    // Delete tokens history
    $sql = "DELETE FROM tokens WHERE department_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $dept_id);
    $stmt->execute();
    
    // Delete department
    $sql = "DELETE FROM departments WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $dept_id);
    $stmt->execute();
    
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Department deleted successfully',
        'department' => [
            'id' => $dept['id'],
            'name' => $dept['name'],
            'deleted_at' => date('Y-m-d H:i:s')
        ]
    ]);
    
} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to delete department: ' . $e->getMessage()]);
}

$stmt_check->close();
$stmt_active->close();
$conn->close();
?>
