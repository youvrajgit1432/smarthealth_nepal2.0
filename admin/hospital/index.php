<?php
/**
 * Hospital Portal Entry Point
 * ---------------------------
 * Makes the documented /smarthealth_nepal/admin/hospital/ URL work. It
 * forwards logged-in hospital admins to their dashboard and everyone else
 * to the hospital login page.
 */

session_start();

if (isset($_SESSION['admin_id'])) {
    header('Location: /smarthealth_nepal/admin/hospital/dashboard/');
} else {
    header('Location: /smarthealth_nepal/admin/hospital/login.php');
}
exit;
?>
