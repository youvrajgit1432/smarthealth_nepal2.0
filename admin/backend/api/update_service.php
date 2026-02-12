<?php
// Admin action: Update service
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
$service_name = isset($_POST['service_name']) ? trim($_POST['service_name']) : '';
$description = isset($_POST['description']) ? trim($_POST['description']) : '';
$department_id = isset($_POST['department_id']) ? intval($_POST['department_id']) : 0;
$status = isset($_POST['status']) ? trim($_POST['status']) : 'Active';

if (!$service_id || !$service_name) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Service ID and name required']);
    exit;
}

// Verify service exists
$sql_check = "SELECT id FROM services WHERE id = ?";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param('i', $service_id);
$stmt_check->execute();

if ($stmt_check->get_result()->num_rows === 0) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Service not found']);
    exit;
}

// Update service
$sql_update = "UPDATE services SET service_name = ?, description = ?, department_id = ?, status = ? WHERE id = ?";
$stmt_update = $conn->prepare($sql_update);
$stmt_update->bind_param('ssisi', $service_name, $description, $department_id, $status, $service_id);

if (!$stmt_update->execute()) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to update service']);
    exit;
}

echo json_encode([
    'success' => true,
    'message' => 'Service updated successfully',
    'service' => [
        'id' => $service_id,
        'name' => $service_name,
        'description' => $description,
        'department_id' => $department_id,
        'status' => $status
    ]
]);

$stmt_check->close();
$stmt_update->close();
$conn->close();
?>
