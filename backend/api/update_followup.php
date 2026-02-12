<?php
// Update chronic disease follow-up date
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
$new_followup = isset($_POST['followup_date']) ? $_POST['followup_date'] : '';

if (!$disease_id || !$new_followup) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Disease ID and follow-up date required']);
    exit;
}

// Validate date format
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $new_followup)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid date format (use YYYY-MM-DD)']);
    exit;
}

// Verify disease belongs to user
$sql_check = "SELECT id, user_id, disease_name FROM chronic_diseases WHERE id = ? AND user_id = ?";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param('ii', $disease_id, $_SESSION['user_id']);
$stmt_check->execute();
$check_result = $stmt_check->get_result();

if ($check_result->num_rows === 0) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Disease not found or unauthorized']);
    exit;
}

$disease = $check_result->fetch_assoc();

// Update next_followup date
$sql_update = "UPDATE chronic_diseases 
               SET next_followup = ?
               WHERE id = ?";

$stmt_update = $conn->prepare($sql_update);
$stmt_update->bind_param('si', $new_followup, $disease_id);

if (!$stmt_update->execute()) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to update follow-up date']);
    exit;
}

// Create notification
$sql_notif = "INSERT INTO notifications (user_id, type, title, message, created_at) 
              VALUES (?, 'FOLLOWUP_UPDATED', ?, ?, NOW())";
$title = "Follow-up Updated: {$disease['disease_name']}";
$message = "Your follow-up appointment has been rescheduled to {$new_followup}";
$stmt_notif = $conn->prepare($sql_notif);
$stmt_notif->bind_param('iss', $_SESSION['user_id'], $title, $message);
$stmt_notif->execute();

echo json_encode([
    'success' => true,
    'message' => 'Follow-up date updated successfully',
    'disease' => [
        'id' => $disease_id,
        'name' => $disease['disease_name'],
        'next_followup' => $new_followup
    ]
]);

$stmt_check->close();
$stmt_update->close();
$stmt_notif->close();
$conn->close();
?>
