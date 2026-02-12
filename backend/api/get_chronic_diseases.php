<?php
// Get chronic diseases for health dashboard
session_start();

require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'User not authenticated']);
    exit;
}

$user_id = $_SESSION['user_id'];

$sql = "SELECT * FROM chronic_diseases 
        WHERE user_id = ? AND status IN ('Active', 'In Progress')
        ORDER BY next_followup ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();

$diseases = [];
$now = new DateTime();

while ($row = $result->fetch_assoc()) {
    // Calculate days until follow-up
    $next_followup = new DateTime($row['next_followup']);
    $days_remaining = $now->diff($next_followup)->days;
    if ($next_followup < $now) {
        $days_remaining = -$days_remaining; // negative means overdue
    }
    
    // Calculate progress (30-day cycle)
    $total_days = 30;
    $progress = max(0, min(100, ((30 - $days_remaining) / 30) * 100));
    
    // Determine status color
    $status_color = 'success'; // default: Controlled
    if ($row['status'] === 'Active') {
        $status_color = 'danger';
    } elseif ($row['status'] === 'In Progress') {
        $status_color = 'warning';
    }
    
    // Determine urgency based on days remaining
    $urgency = 'success';
    if ($days_remaining < 0) {
        $urgency = 'danger'; // overdue
    } elseif ($days_remaining < 7) {
        $urgency = 'warning'; // due soon
    }
    
    $diseases[] = [
        'id' => $row['id'],
        'name' => $row['disease_name'],
        'diagnosis_date' => $row['diagnosis_date'],
        'status' => $row['status'],
        'status_color' => $status_color,
        'next_followup' => $row['next_followup'],
        'last_visit' => $row['last_visit'],
        'days_remaining' => $days_remaining,
        'urgency' => $urgency,
        'progress' => round($progress, 1),
        'notes' => $row['notes'],
        'medication' => $row['medication']
    ];
}

echo json_encode(['success' => true, 'diseases' => $diseases, 'count' => count($diseases)]);

$stmt->close();
$conn->close();
?>
