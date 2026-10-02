<?php
/**
 * Admin Panel Entry Point
 * -----------------------
 * Makes the documented /smarthealth_nepal/admin/ URL work. It forwards
 * logged-in admins to the dashboard and everyone else to the login page.
 */

session_start();

if (isset($_SESSION['admin_id'])) {
    header('Location: /smarthealth_nepal/admin/frontend/views/dashboard/');
} else {
    header('Location: /smarthealth_nepal/admin/frontend/views/auth/login.php');
}
exit;
?>
