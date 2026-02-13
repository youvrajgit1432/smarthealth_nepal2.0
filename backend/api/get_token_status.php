<?php
// Get current token status for real-time tracking
session_start();

require_once '../config/database.php';
require_once '../helpers/TokenHelper.php';

header('Content-Type: application/json');

if (!isset($_GET['token'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Token number required']);
    exit;
}

$token_number = $_GET['token'];

// Get token details with user and department info
$sql = "SELECT t.*, u.phone, u.name, d.name as dept_name, d.current_load, d.capacity
        FROM tokens t
        LEFT JOIN users u ON t.user_id = u.id
        LEFT JOIN departments d ON t.department_id = d.id
        WHERE t.token_number = ? AND DATE(t.created_at) = CURDATE()";

$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $token_number);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Token not found']);
    exit;
}

$token = $result->fetch_assoc();

// Calculate queue position (number of active/called tokens before this one)
$priority = TokenHelper::getPriority($token['priority']);
$sql_pos = "SELECT COUNT(*) as position FROM tokens 
            WHERE department_id = ? AND DATE(created_at) = CURDATE() 
            AND created_at < ? 
            AND status NOT IN ('Missed', 'Cancelled', 'Completed')
            ORDER BY FIELD(priority, 'Emergency', 'Priority', 'Chronic', 'Normal') ASC, created_at ASC";

$stmt_pos = $conn->prepare($sql_pos);
$stmt_pos->bind_param('is', $token['department_id'], $token['created_at']);
$stmt_pos->execute();
$pos_result = $stmt_pos->get_result()->fetch_assoc();
$queue_position = $pos_result['position'] + 1;

// Calculate estimated wait time - simplified: each person ahead = ~10 minutes
// This gives realistic estimates (3 people = ~30 min, 1 person = ~10 min)
$wait_time = ($queue_position - 1) * 10;
if ($token['status'] === 'Called') {
    $wait_time = 0;
} elseif ($token['status'] === 'Completed' || $token['status'] === 'Missed') {
    $wait_time = null;
}

// Calculate department load percentage
$load_percent = 0;
if ($token['capacity']) {
    $load_percent = round(($token['current_load'] / $token['capacity']) * 100, 1);
}

$response = [
    'success' => true,
    'token' => [
        'number' => $token['token_number'],
        'status' => $token['status'],
        'priority' => $token['priority'],
        'dept_name' => $token['dept_name'] ?? 'General',
        'estimated_wait_time' => $wait_time,
        'patient_name' => $token['name'],
        'patient_phone' => $token['phone'],
        'created_at' => $token['created_at'],
        'called_at' => $token['called_at']
    ],
    'queue_position' => $queue_position,
    'department' => [
        'name' => $token['dept_name'] ?? 'General',
        'load' => $token['current_load'],
        'capacity' => $token['capacity'],
        'percentage' => $load_percent
    ]
];

echo json_encode($response);
$stmt->close();
$stmt_pos->close();
$conn->close();
?>
