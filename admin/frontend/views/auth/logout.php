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

// Redirect to login or home
header('Location: /smarthealth_nepal/');
exit;
?>
