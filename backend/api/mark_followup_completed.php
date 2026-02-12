<?php
// Mark chronic disease follow-up as completed
session_start();

require_once '../config/database.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
if ($method !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'User not authenticated']);
    exit;
}

$disease_id = isset($_POST['disease_id']) ? intval($_POST['disease_id']) : 0;
$completed_date = isset($_POST['completed_date']) ? $_POST['completed_date'] : date('Y-m-d');

if (!$disease_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Disease ID required']);
    exit;
}

// Verify disease belongs to user
$sql_check = "SELECT id, user_id FROM chronic_diseases WHERE id = ? AND user_id = ?";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param('ii', $disease_id, $_SESSION['user_id']);
$stmt_check->execute();

if ($stmt_check->get_result()->num_rows === 0) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Disease not found or unauthorized']);
    exit;
}

// Update last_visit and set next_followup to 30 days from now
$next_followup = date('Y-m-d', strtotime('+30 days'));

$sql_update = "UPDATE chronic_diseases 
               SET last_visit = ?, next_followup = ?, status = 'Controlled'
               WHERE id = ?";

$stmt_update = $conn->prepare($sql_update);
$stmt_update->bind_param('ssi', $completed_date, $next_followup, $disease_id);

if (!$stmt_update->execute()) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to update follow-up']);
    exit;
}

// Log action
$sql_log = "INSERT INTO notifications (user_id, type, title, message, created_at) 
            VALUES (?, 'FOLLOWUP', 'Follow-up Completed', 'Your follow-up visit was recorded', NOW())";
$stmt_log = $conn->prepare($sql_log);
$stmt_log->bind_param('i', $_SESSION['user_id']);
$stmt_log->execute();

echo json_encode([
    'success' => true,
    'message' => 'Follow-up marked as completed',
    'disease' => [
        'id' => $disease_id,
        'last_visit' => $completed_date,
        'next_followup' => $next_followup,
        'status' => 'Controlled'
    ]
]);

$stmt_check->close();
$stmt_update->close();
$stmt_log->close();
$conn->close();
?>
