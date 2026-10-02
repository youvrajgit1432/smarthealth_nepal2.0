<?php
/**
 * Admin API - Send SMS
 * --------------------
 * Sends a free-text SMS to a patient from the token-management panel.
 * Uses the configured SMS provider (defaults to the local 'debug' provider,
 * which simply records the message in the runtime log).
 */

require_once __DIR__ . '/../../backend/init.php';
require_once __DIR__ . '/../../backend/helpers/CsrfHelper.php';
require_once __DIR__ . '/../../backend/services/SparrowSMSService.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_verify()) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid request. Please refresh the page and try again.']);
    exit;
}

$phone   = preg_replace('/[^0-9+]/', '', $_POST['phone'] ?? '');
$message = trim($_POST['message'] ?? '');

if ($phone === '' || $message === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Phone number and message are required.']);
    exit;
}

try {
    $sms = new SparrowSMSService($db);
    $result = $sms->sendSMS($phone, $message);
    $ok = is_array($result) ? ($result['success'] ?? false) : (bool) $result;

    echo json_encode([
        'success' => $ok,
        'message' => $ok ? 'SMS sent successfully' : 'The SMS gateway did not confirm delivery',
    ]);
} catch (Throwable $e) {
    error_log('Admin send_sms error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Could not send the SMS at this time.']);
}
