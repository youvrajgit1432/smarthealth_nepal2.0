<?php
/**
 * API - Get current token status for real-time tracking
 * Endpoint: /backend/api/get_token_status.php?token=NNN
 */

require_once __DIR__ . '/../init.php';

header('Content-Type: application/json');

if (!isset($_GET['token']) || $_GET['token'] === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Token number required']);
    exit;
}

$token_number = $_GET['token'];

// Get token details with user and department info
$sql = "SELECT t.*, u.phone, u.name, d.name_en as dept_name, d.current_load, d.max_capacity as capacity
        FROM tokens t
        LEFT JOIN users u ON t.user_id = u.id
        LEFT JOIN departments d ON t.department_id = d.id
        WHERE t.token_number = ? AND DATE(t.created_at) = CURDATE()";

$stmt = $db->prepare($sql);
$stmt->bind_param('s', $token_number);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Token not found']);
    exit;
}

$token = $result->fetch_assoc();

// Queue position: count valid tokens created before this one in the same dept
$sql_pos = "SELECT COUNT(*) as position FROM tokens
            WHERE department_id = ?
            AND DATE(created_at) = CURDATE()
            AND created_at < ?
            AND status IN ('Active','Called')";
$stmt_pos = $db->prepare($sql_pos);
$stmt_pos->bind_param('is', $token['department_id'], $token['created_at']);
$stmt_pos->execute();
$queue_position = ($stmt_pos->get_result()->fetch_assoc()['position'] ?? 0) + 1;

// Estimated wait: ~10 minutes per person ahead
$wait_time = ($queue_position - 1) * 10;
if ($token['status'] === 'Called') {
    $wait_time = 0;
} elseif (in_array($token['status'], ['Completed', 'Missed', 'Cancelled', 'Rescheduled'], true)) {
    $wait_time = null;
}

$load_percent = 0;
if (!empty($token['capacity'])) {
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
        'patient_name' => $token['name'] ?? $token['full_name'] ?? 'N/A',
        'patient_phone' => $token['phone'] ?? $token['phone_number'] ?? '',
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
?>
