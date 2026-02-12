<?php
/**
 * Admin Public Entry Point
 */

session_start();

// If already logged in, redirect to dashboard
if (isset($_SESSION['admin_id'])) {
    header('Location: /smarthealth_nepal/admin/frontend/views/dashboard/');
    exit;
}

// Otherwise redirect to login
header('Location: /smarthealth_nepal/admin/frontend/views/auth/login.php');
exit;
?>
