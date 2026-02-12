<?php
/**
 * SmartHealth Nepal - Backend Initialization
 * Load all required configurations and utilities
 */

// Enable error reporting for development (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Start session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Load database configuration
require_once __DIR__ . '/config/database.php';

// Load language configuration
require_once __DIR__ . '/config/language.php';

// Set default language
if (!isset($_SESSION['language'])) {
    $_SESSION['language'] = defined('DEFAULT_LANGUAGE') ? DEFAULT_LANGUAGE : 'en';
}

// Load language file
$langFile = __DIR__ . '/lang/' . $_SESSION['language'] . '.php';
if (file_exists($langFile)) {
    require_once $langFile;
} else {
    require_once __DIR__ . '/lang/en.php';
}

// Global variables
global $db;
global $lang;

// Ensure database connection is set
if (!isset($db) || $db->connect_error) {
    die(json_encode([
        'success' => false,
        'message' => 'Database connection not established'
    ]));
}

// Helper functions
/**
 * Generate unique token number based on department and current queue
 */
function generateTokenNumber($departmentId) {
    global $db;
    $date = date('Ymd');
    
    $result = $db->query("SELECT MAX(token_number) as max_token FROM tokens 
                         WHERE department_id = $departmentId 
                         AND DATE(created_at) = '$date'");
    $row = $result->fetch_assoc();
    
    return ($row['max_token'] ?? 0) + 1;
}

/**
 * Calculate estimated wait time
 */
function calculateWaitTime($departmentId) {
    global $db;
    
    $result = $db->query("SELECT 
                         COUNT(*) as queue_count,
                         d.avg_service_time
                         FROM tokens t
                         JOIN departments d ON t.department_id = d.id
                         WHERE t.department_id = $departmentId 
                         AND t.status = 'Active'
                         AND DATE(t.created_at) = CURDATE()");
    
    $row = $result->fetch_assoc();
    $queueCount = $row['queue_count'] ?? 0;
    $avgServiceTime = $row['avg_service_time'] ?? 30;
    
    return $queueCount * $avgServiceTime;
}

/**
 * Classify priority based on triage
 */
function classifyPriority($triageData) {
    $priority = 'Normal';
    
    if ($triageData['emergency_signs'] ?? false) {
        $priority = 'Emergency';
    } elseif ($triageData['have_fever'] ?? false || $triageData['difficulty_breathing'] ?? false) {
        $priority = 'Priority';
    } elseif ($triageData['chronic_disease'] ?? false) {
        $priority = 'Chronic';
    }
    
    return $priority;
}

/**
 * Send SMS notification (simulate)
 */
function sendSMS($phoneNumber, $message) {
    // In production, integrate with actual SMS provider (Sparrow SMS, Twilio, etc.)
    // For now, we'll log it
    $logFile = __DIR__ . '/../logs/sms.log';
    $logMessage = date('Y-m-d H:i:s') . " | $phoneNumber | $message\n";
    
    if (!is_dir(__DIR__ . '/../logs')) {
        mkdir(__DIR__ . '/../logs', 0755, true);
    }
    
    file_put_contents($logFile, $logMessage, FILE_APPEND);
    return true;
}

/**
 * Generate OTP code
 */
function generateOTP() {
    return str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
}

/**
 * API Response formatter
 */
function apiResponse($success, $message = '', $data = []) {
    header('Content-Type: application/json');
    return json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
}

/**
 * Log activity
 */
function logActivity($userId, $activity, $details = '') {
    $logFile = __DIR__ . '/../logs/activity.log';
    $logMessage = date('Y-m-d H:i:s') . " | User: $userId | Activity: $activity | Details: $details\n";
    
    if (!is_dir(__DIR__ . '/../logs')) {
        mkdir(__DIR__ . '/../logs', 0755, true);
    }
    
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}

?>
