<?php
// Admin action: Delete service
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

$service_id = isset($_POST['service_id']) ? intval($_POST['service_id']) : 0;

if (!$service_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Service ID required']);
    exit;
}

// Verify service exists
$sql_check = "SELECT id, service_name FROM services WHERE id = ?";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param('i', $service_id);
$stmt_check->execute();
$result = $stmt_check->get_result();

if ($result->num_rows === 0) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Service not found']);
    exit;
}

$service = $result->fetch_assoc();

// Start transaction
$conn->begin_transaction();

try {
    // Delete related referrals
    $sql = "DELETE FROM referrals WHERE service_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $service_id);
    $stmt->execute();
    
    // Delete service
    $sql = "DELETE FROM services WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $service_id);
    $stmt->execute();
    
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Service deleted successfully',
        'service' => [
            'id' => $service['id'],
            'name' => $service['service_name'],
            'deleted_at' => date('Y-m-d H:i:s')
        ]
    ]);
    
} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to delete service: ' . $e->getMessage()]);
}

$stmt_check->close();
$conn->close();
?>
