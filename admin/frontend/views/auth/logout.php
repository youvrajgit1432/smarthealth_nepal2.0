<?php
/**
 * Admin Logout
 */
session_start();

// Clear admin session
$_SESSION = array();
if (session_id() != "") {
    setcookie(session_name(), '', time() - 2592000, '/');
}
session_destroy();

// Redirect to login page
header('Location: /smarthealth_nepal/admin/frontend/views/auth/login.php');
exit;
?>
