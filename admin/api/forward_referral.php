<?php
/**
 * Admin API - Forward Referral
 * ----------------------------
 * Forwards a referral to another hospital and records the notes.
 */

require_once __DIR__ . '/../../backend/init.php';
require_once __DIR__ . '/../../backend/helpers/CsrfHelper.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: /smarthealth_nepal/admin/frontend/views/auth/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_verify()) {
    http_response_code(400);
    exit('Invalid request. Please refresh the page and try again.');
}

$referralId = (int) ($_POST['referral_id'] ?? 0);
$toHospital = trim($_POST['to_hospital'] ?? '');
$notes      = trim($_POST['notes'] ?? '');

if ($referralId <= 0 || $toHospital === '') {
    http_response_code(400);
    exit('A referral and destination hospital are required.');
}

$stmt = $db->prepare(
    "UPDATE referrals
        SET to_hospital = ?, notes = ?, status = 'Forwarded'
      WHERE id = ?"
);
if ($stmt) {
    $stmt->bind_param('ssi', $toHospital, $notes, $referralId);
    $stmt->execute();
    $stmt->close();
}

header('Location: /smarthealth_nepal/admin/frontend/views/service_management/forward.php');
exit;
