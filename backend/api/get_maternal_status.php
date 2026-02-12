<?php
// Get maternal health status for health dashboard
session_start();

require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'User not authenticated']);
    exit;
}

$user_id = $_SESSION['user_id'];

// Get active maternal health record for user
$sql = "SELECT * FROM maternal_health 
        WHERE user_id = ? AND status = 'Active'
        ORDER BY created_at DESC LIMIT 1";

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => true, 'maternal' => null]);
    $stmt->close();
    $conn->close();
    exit;
}

$row = $result->fetch_assoc();

// Calculate weeks remaining until due date
$now = new DateTime();
$due_date = new DateTime($row['due_date']);
$interval = $now->diff($due_date);
$weeks_remaining = floor($interval->days / 7);
$days_remaining = $interval->days % 7;

// Calculate trimester (40 weeks pregnancy)
$total_weeks = 40;
$weeks_pregnant = $total_weeks - $weeks_remaining;
$trimester = 3;
$trimester_name = 'Third Trimester';

if ($weeks_pregnant < 13) {
    $trimester = 1;
    $trimester_name = 'First Trimester (0-13 weeks)';
} elseif ($weeks_pregnant < 27) {
    $trimester = 2;
    $trimester_name = 'Second Trimester (14-27 weeks)';
}

// Parse antenatal visits (stored as JSON or comma-separated)
$visits = [];
if ($row['antenatal_visits']) {
    $visits = json_decode($row['antenatal_visits'], true) ?? explode(',', $row['antenatal_visits']);
}
$visit_count = count($visits);
$visit_progress = min(100, ($visit_count / 4) * 100);

// Parse vaccinations
$vaccinations = [];
if ($row['vaccinations']) {
    $vaccinations = json_decode($row['vaccinations'], true) ?? explode(',', $row['vaccinations']);
}
$vac_count = count($vaccinations);

// Check for warning signs
$warning_signs = [];
if ($row['warning_signs']) {
    $warning_signs = json_decode($row['warning_signs'], true) ?? explode(',', $row['warning_signs']);
}
$has_warnings = count($warning_signs) > 0;

// Status based on due date
$status = 'Active';
if ($due_date <= $now) {
    $status = 'Due';
} elseif ($weeks_remaining <= 2) {
    $status = 'Due Soon';
}

$maternal = [
    'id' => $row['id'],
    'lmp_date' => $row['lmp_date'],
    'due_date' => $row['due_date'],
    'weeks_pregnant' => $weeks_pregnant,
    'weeks_remaining' => $weeks_remaining,
    'days_remaining' => $days_remaining,
    'trimester' => $trimester,
    'trimester_name' => $trimester_name,
    'status' => $status,
    'antenatal_visits' => [
        'count' => $visit_count,
        'required' => 4,
        'last_visit' => $row['last_antenatal_visit'],
        'visits' => $visits,
        'progress' => round($visit_progress, 1)
    ],
    'vaccinations' => [
        'count' => $vac_count,
        'items' => $vaccinations
    ],
    'warning_signs' => [
        'has_warnings' => $has_warnings,
        'signs' => $warning_signs,
        'common_signs' => ['Severe headache', 'Vaginal bleeding', 'Severe swelling', 'Severe abdominal pain', 'Fluid leakage']
    ],
    'notes' => $row['notes'],
    'created_at' => $row['created_at']
];

echo json_encode(['success' => true, 'maternal' => $maternal]);

$stmt->close();
$conn->close();
?>
