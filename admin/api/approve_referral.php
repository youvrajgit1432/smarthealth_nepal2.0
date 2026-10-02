<?php
/**
 * Admin API - Approve Referral
 * ----------------------------
 * Marks a pending referral as Approved.
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
if ($referralId <= 0) {
    http_response_code(400);
    exit('Invalid referral.');
}

$stmt = $db->prepare("UPDATE referrals SET status = 'Approved' WHERE id = ? AND status = 'Pending'");
if ($stmt) {
    $stmt->bind_param('i', $referralId);
    $stmt->execute();
    $stmt->close();
}

header('Location: /smarthealth_nepal/admin/frontend/views/service_management/referral.php');
exit;
