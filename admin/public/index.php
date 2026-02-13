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

// Otherwise redirect to login we face one problem whatever user login in admin panel it shows all token according to the users show that their own not all user if user is 

header('Location: /smarthealth_nepal/admin/frontend/views/auth/login.php');
exit;
?>
